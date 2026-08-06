<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

function sendJson($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function latestScoreSubquery($columns = 'Driver_ID, Score, Month, Year') {
    return "(
        SELECT {$columns}
        FROM (
            SELECT ds.Driver_ID, ds.Score, ds.Month, ds.Year,
                   ROW_NUMBER() OVER (
                       PARTITION BY ds.Driver_ID
                       ORDER BY ds.Year DESC, ds.Month DESC, ds.Score_ID DESC
                   ) AS rn
            FROM Driver_Safety_Score ds
        ) ranked_scores
        WHERE rn = 1
    )";
}

try {
    $pdo = get_db_connection();
    $latestScore = latestScoreSubquery();
    $latestScoreOnly = latestScoreSubquery('Driver_ID, Score');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            sendJson(['error' => 'Invalid JSON request'], 400);
        }

        $action = $input['action'] ?? '';

        if ($action !== 'save_review') {
            sendJson(['error' => 'Unknown action'], 400);
        }

        $eventId = (int) ($input['eventId'] ?? 0);
        $reviewComments = trim((string) ($input['reviewComments'] ?? ''));

        if ($eventId <= 0) {
            sendJson(['error' => 'Invalid event ID'], 400);
        }

        $stmt = $pdo->prepare("UPDATE Safety_Event SET Review_Comments = :reviewComments WHERE Event_ID = :eventId");
        $stmt->execute([':reviewComments' => $reviewComments, ':eventId' => $eventId]);

        if ($stmt->rowCount() === 0) {
            $checkStmt = $pdo->prepare("SELECT Event_ID FROM Safety_Event WHERE Event_ID = :eventId");
            $checkStmt->execute([':eventId' => $eventId]);

            if (!$checkStmt->fetch()) {
                sendJson(['error' => 'Safety event not found'], 404);
            }
        }

        sendJson(['success' => true, 'message' => 'Review comments saved successfully']);
    }

    $driverStmt = $pdo->query(" 
        SELECT
            d.Driver_ID,
            d.Full_Name,
            d.Contact_Information,
            d.Emergency_Contact,
            d.License_Type,
            d.License_Expiry_Date,
            d.Employment_Status,
            dep.Depot_ID,
            dep.Location_Name AS Depot_Name,
            latest.Score AS Safety_Score,
            latest.Month AS Score_Month,
            latest.Year AS Score_Year,
            (SELECT COUNT(*) FROM Safety_Event se WHERE se.Driver_ID = d.Driver_ID) AS Incident_Count
        FROM Driver d
        JOIN Depot dep ON d.Depot_ID = dep.Depot_ID
        LEFT JOIN {$latestScore} latest ON latest.Driver_ID = d.Driver_ID
        ORDER BY d.Full_Name ASC
    ");

    $drivers = [];

    while ($row = $driverStmt->fetch(PDO::FETCH_ASSOC)) {
        $driverId = $row['Driver_ID'];

        $certStmt = $pdo->prepare("SELECT Certification_Name, Expiry_Date FROM Driver_Certification WHERE Driver_ID = :driverId ORDER BY Expiry_Date ASC");
        $certStmt->execute([':driverId' => $driverId]);

        $certifications = [];

        while ($cert = $certStmt->fetch(PDO::FETCH_ASSOC)) {
            $expiryDate = new DateTime($cert['Expiry_Date']);
            $today = new DateTime();
            $daysLeft = (int) $today->diff($expiryDate)->format('%r%a');
            $validity = $daysLeft < 0 ? 'Expired' : ($daysLeft <= 30 ? 'Expiring' : 'Valid');

            $certifications[] = [
                'name' => $cert['Certification_Name'],
                'expiryDate' => $cert['Expiry_Date'],
                'daysLeft' => $daysLeft,
                'validity' => $validity
            ];
        }

        $scoreStmt = $pdo->prepare("SELECT Month, Year, Score FROM Driver_Safety_Score WHERE Driver_ID = :driverId ORDER BY Year ASC, Month ASC, Score_ID ASC");
        $scoreStmt->execute([':driverId' => $driverId]);

        $scoreTrend = [];

        while ($score = $scoreStmt->fetch(PDO::FETCH_ASSOC)) {
            $scoreTrend[] = [
                'month' => (int) $score['Month'],
                'year' => (int) $score['Year'],
                'score' => (float) $score['Score']
            ];
        }

        $incidentStmt = $pdo->prepare(" 
            SELECT
                se.Event_ID,
                se.Timestamp,
                se.Event_Type,
                se.Severity_Level,
                se.VIN,
                se.Odometer_At_Event,
                se.Review_Comments,
                v.Registration_Number,
                v.Vehicle_Category,
                v.Manufacturer_and_Model
            FROM Safety_Event se
            JOIN Vehicle v ON se.VIN = v.VIN
            WHERE se.Driver_ID = :driverId
            ORDER BY se.Timestamp DESC
        ");
        $incidentStmt->execute([':driverId' => $driverId]);

        $incidentHistory = [];

        while ($incident = $incidentStmt->fetch(PDO::FETCH_ASSOC)) {
            $incidentHistory[] = [
                'eventId' => (int) $incident['Event_ID'],
                'timestamp' => $incident['Timestamp'],
                'eventType' => $incident['Event_Type'],
                'severity' => $incident['Severity_Level'],
                'vin' => $incident['VIN'],
                'registrationNumber' => $incident['Registration_Number'],
                'vehicleCategory' => $incident['Vehicle_Category'],
                'vehicleModel' => $incident['Manufacturer_and_Model'],
                'odometer' => (float) $incident['Odometer_At_Event'],
                'reviewComments' => $incident['Review_Comments']
            ];
        }

        $certificationStatus = 'None';

        if ($certifications) {
            $hasExpired = false;
            $hasExpiring = false;

            foreach ($certifications as $cert) {
                if ($cert['validity'] === 'Expired') {
                    $hasExpired = true;
                } elseif ($cert['validity'] === 'Expiring') {
                    $hasExpiring = true;
                }
            }

            $certificationStatus = $hasExpired ? 'Expired' : ($hasExpiring ? 'Expiring' : 'Valid');
        }

        $drivers[] = [
            'driverId' => $driverId,
            'name' => $row['Full_Name'],
            'contact' => $row['Contact_Information'],
            'emergencyContact' => $row['Emergency_Contact'],
            'licenseType' => $row['License_Type'],
            'licenseExpiryDate' => $row['License_Expiry_Date'],
            'employmentStatus' => $row['Employment_Status'],
            'depotId' => (int) $row['Depot_ID'],
            'depot' => $row['Depot_Name'],
            'safetyScore' => $row['Safety_Score'] !== null ? (float) $row['Safety_Score'] : null,
            'scoreMonth' => $row['Score_Month'] !== null ? (int) $row['Score_Month'] : null,
            'scoreYear' => $row['Score_Year'] !== null ? (int) $row['Score_Year'] : null,
            'incidentCount' => (int) $row['Incident_Count'],
            'certificationStatus' => $certificationStatus,
            'certifications' => $certifications,
            'scoreTrend' => $scoreTrend,
            'incidentHistory' => $incidentHistory
        ];
    }

    $incidentStmt = $pdo->query(" 
        SELECT
            se.Event_ID,
            se.Driver_ID,
            se.VIN,
            se.Depot_ID,
            se.Timestamp,
            se.Event_Type,
            se.Severity_Level,
            se.Odometer_At_Event,
            se.Review_Comments,
            d.Full_Name AS Driver_Name,
            v.Registration_Number,
            v.Vehicle_Category,
            v.Manufacturer_and_Model,
            dep.Location_Name AS Depot_Name
        FROM Safety_Event se
        JOIN Driver d ON se.Driver_ID = d.Driver_ID
        JOIN Vehicle v ON se.VIN = v.VIN
        JOIN Depot dep ON se.Depot_ID = dep.Depot_ID
        ORDER BY se.Timestamp DESC
    ");

    $incidents = [];

    while ($row = $incidentStmt->fetch(PDO::FETCH_ASSOC)) {
        $incidents[] = [
            'eventId' => (int) $row['Event_ID'],
            'driverId' => $row['Driver_ID'],
            'driverName' => $row['Driver_Name'],
            'vin' => $row['VIN'],
            'registrationNumber' => $row['Registration_Number'],
            'vehicleCategory' => $row['Vehicle_Category'],
            'vehicleModel' => $row['Manufacturer_and_Model'],
            'depotId' => (int) $row['Depot_ID'],
            'depot' => $row['Depot_Name'],
            'timestamp' => $row['Timestamp'],
            'eventType' => $row['Event_Type'],
            'severity' => $row['Severity_Level'],
            'odometer' => (float) $row['Odometer_At_Event'],
            'reviewComments' => $row['Review_Comments']
        ];
    }

    $averageSafetyScore = (float) $pdo->query("SELECT ROUND(AVG(latest.Score), 1) FROM {$latestScore} latest")->fetchColumn();
    $criticalEvents = (int) $pdo->query("SELECT COUNT(*) FROM Safety_Event WHERE Severity_Level = 'Critical'")->fetchColumn();
    $highRiskDrivers = (int) $pdo->query("SELECT COUNT(*) FROM {$latestScoreOnly} latest WHERE latest.Score <= 75")->fetchColumn();
    $repeatOffenders = (int) $pdo->query("SELECT COUNT(*) FROM (SELECT Driver_ID FROM Safety_Event GROUP BY Driver_ID HAVING COUNT(*) >= 2) repeat_drivers")->fetchColumn();

    $depotComparison = [];
    $depotStmt = $pdo->query(" 
        SELECT
            dep.Depot_ID,
            dep.Location_Name AS depot,
            COALESCE(ROUND(AVG(latest.Score), 1), 0) AS averageScore,
            COUNT(CASE WHEN se.Severity_Level = 'Critical' THEN 1 END) AS criticalEvents
        FROM Depot dep
        LEFT JOIN Driver d ON d.Depot_ID = dep.Depot_ID
        LEFT JOIN {$latestScore} latest ON latest.Driver_ID = d.Driver_ID
        LEFT JOIN Safety_Event se ON se.Depot_ID = dep.Depot_ID
        GROUP BY dep.Depot_ID, dep.Location_Name
        ORDER BY dep.Location_Name
    ");

    while ($row = $depotStmt->fetch(PDO::FETCH_ASSOC)) {
        $depotComparison[] = [
            'depot' => $row['depot'],
            'averageScore' => (float) $row['averageScore'],
            'criticalEvents' => (int) $row['criticalEvents']
        ];
    }

    $repeatOffenderList = [];
    $repeatListStmt = $pdo->query(" 
        SELECT
            d.Driver_ID,
            d.Full_Name AS name,
            COUNT(se.Event_ID) AS events,
            latest.Score AS score
        FROM Driver d
        JOIN Safety_Event se ON se.Driver_ID = d.Driver_ID
        LEFT JOIN {$latestScoreOnly} latest ON latest.Driver_ID = d.Driver_ID
        GROUP BY d.Driver_ID, d.Full_Name, latest.Score
        HAVING COUNT(se.Event_ID) >= 2
        ORDER BY events DESC, score ASC
        LIMIT 10
    ");

    while ($row = $repeatListStmt->fetch(PDO::FETCH_ASSOC)) {
        $repeatOffenderList[] = [
            'driverId' => $row['Driver_ID'],
            'name' => $row['name'],
            'score' => $row['score'] !== null ? (float) $row['score'] : null,
            'events' => (int) $row['events']
        ];
    }

    $trainingRequired = [];
    $trainingStmt = $pdo->query(" 
        SELECT
            d.Driver_ID,
            d.Full_Name AS name,
            dep.Location_Name AS depot,
            latest.Score AS score
        FROM Driver d
        JOIN Depot dep ON d.Depot_ID = dep.Depot_ID
        JOIN {$latestScoreOnly} latest ON latest.Driver_ID = d.Driver_ID
        WHERE latest.Score <= 75
        ORDER BY latest.Score ASC
    ");

    while ($row = $trainingStmt->fetch(PDO::FETCH_ASSOC)) {
        $score = (float) $row['score'];
        $trainingRequired[] = [
            'driverId' => $row['Driver_ID'],
            'name' => $row['name'],
            'depot' => $row['depot'],
            'score' => $score,
            'trainingStatus' => $score <= 50 ? 'Required' : 'Pending'
        ];
    }

    $blockedDrivers = [];
    $blockedStmt = $pdo->query(" 
        SELECT
            d.Driver_ID,
            d.Full_Name AS name,
            dep.Location_Name AS depot,
            latest.Score AS score,
            critical_events.Critical_Count
        FROM Driver d
        JOIN Depot dep ON d.Depot_ID = dep.Depot_ID
        LEFT JOIN {$latestScoreOnly} latest ON latest.Driver_ID = d.Driver_ID
        LEFT JOIN (
            SELECT Driver_ID, COUNT(*) AS Critical_Count
            FROM Safety_Event
            WHERE Severity_Level = 'Critical'
            GROUP BY Driver_ID
        ) critical_events ON critical_events.Driver_ID = d.Driver_ID
        WHERE COALESCE(latest.Score, 100) <= 50 OR COALESCE(critical_events.Critical_Count, 0) > 0
        ORDER BY latest.Score ASC
    ");

    while ($row = $blockedStmt->fetch(PDO::FETCH_ASSOC)) {
        $score = $row['score'] !== null ? (float) $row['score'] : null;
        $criticalCount = (int) ($row['Critical_Count'] ?? 0);
        $reason = $score !== null && $score <= 50 ? 'Safety score ' . $score : 'Critical safety event';

        $blockedDrivers[] = [
            'driverId' => $row['Driver_ID'],
            'name' => $row['name'],
            'depot' => $row['depot'],
            'score' => $score,
            'criticalEvents' => $criticalCount,
            'reason' => $reason,
            'status' => 'Blocked'
        ];
    }

    sendJson([
        'success' => true,
        'drivers' => $drivers,
        'incidents' => $incidents,
        'analytics' => [
            'averageSafetyScore' => $averageSafetyScore,
            'criticalEvents' => $criticalEvents,
            'highRiskDrivers' => $highRiskDrivers,
            'repeatOffenders' => $repeatOffenders,
            'depotComparison' => $depotComparison,
            'repeatOffenderList' => $repeatOffenderList
        ],
        'coaching' => [
            'trainingRequired' => $trainingRequired,
            'blockedDrivers' => $blockedDrivers
        ]
    ]);
} catch (PDOException $e) {
    sendJson([
        'success' => false,
        'error' => 'Database query failed',
        'detail' => $e->getMessage()
    ], 500);
}
