<?php
/**
 * GET /Backend/api/dashboard.php
 * ------------------------------------------------------------------
 * Feeds the Dashboard overview page: hero band, 4 KPI cards, and the
 * 5 panels (key alerts, high-risk drivers, vehicles by depot, open
 * maintenance jobs, certifications expiring soon).
 *
 * Response shape:
 * {
 *   "hero": { "vehiclesReady": n, "inMaintenance": n, "criticalAlerts": n },
 *   "kpis": {
 *     "totalVehicles": n,
 *     "avgSafetyScore": n,
 *     "driversNeedingTraining": n,
 *     "maintenanceCostThisMonth": n
 *   },
 *   "keyAlerts":        [{ "title", "meta", "raised" }, ...],
 *   "highRiskDrivers":  [{ "name", "depot", "score", "status" }, ...],
 *   "vehiclesByDepot":  [{ "depot", "ready", "total" }, ...],
 *   "openJobs":         [{ "job", "workshop", "status" }, ...],
 *   "certsExpiring":    [{ "driver", "type", "daysLeft" }, ...]
 * }
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = get_db_connection();

    // ---------------------------------------------------------------
    // Hero band
    // ---------------------------------------------------------------
    $vehiclesReady = (int) $pdo->query("
        SELECT COUNT(*) FROM Vehicle WHERE Operational_Status IN ('Active','Available')
    ")->fetchColumn();

    $inMaintenance = (int) $pdo->query("
        SELECT COUNT(*) FROM Vehicle WHERE Operational_Status = 'Under Maintenance'
    ")->fetchColumn();

    $criticalAlerts = (int) $pdo->query("
        SELECT COUNT(*) FROM Predictive_Alert WHERE Severity_Level = 'Critical'
    ")->fetchColumn();

    // ---------------------------------------------------------------
    // KPI cards
    // ---------------------------------------------------------------
    $totalVehicles = (int) $pdo->query('SELECT COUNT(*) FROM Vehicle')->fetchColumn();

    // Average of each driver's most recent monthly safety score.
    $avgSafetyScore = (float) $pdo->query('
        SELECT ROUND(AVG(latest.Score), 1)
        FROM (
            SELECT ds.Driver_ID, ds.Score,
                   ROW_NUMBER() OVER (
                       PARTITION BY ds.Driver_ID
                       ORDER BY ds.Year DESC, ds.Month DESC
                   ) AS rn
            FROM Driver_Safety_Score ds
        ) latest
        WHERE latest.rn = 1
    ')->fetchColumn();

    $driversNeedingTraining = (int) $pdo->query("
        SELECT COUNT(*)
        FROM (
            SELECT ds.Driver_ID, ds.Score,
                   ROW_NUMBER() OVER (
                       PARTITION BY ds.Driver_ID
                       ORDER BY ds.Year DESC, ds.Month DESC
                   ) AS rn
            FROM Driver_Safety_Score ds
        ) latest
        WHERE latest.rn = 1 AND latest.Score <= 75
    ")->fetchColumn();

    $maintenanceCostThisMonth = (float) $pdo->query('
        SELECT COALESCE(SUM(Total_Cost), 0)
        FROM Maintenance_Job
        WHERE YEAR(Date_Opened) = YEAR(CURDATE()) AND MONTH(Date_Opened) = MONTH(CURDATE())
    ')->fetchColumn();

    // ---------------------------------------------------------------
    // Panel: Key alerts (4 most recent)
    // ---------------------------------------------------------------
    $keyAlertsStmt = $pdo->query('
        SELECT pa.Alert_Type AS title, v.Vehicle_Category AS category,
               d.Location_Name AS depot, pa.Severity_Level AS severity,
               pa.Raised_At AS raised
        FROM Predictive_Alert pa
        JOIN Vehicle v ON pa.VIN = v.Vin
        JOIN Depot d ON pa.Depot_ID = d.Depot_ID
        ORDER BY pa.Raised_At DESC
        LIMIT 4
    ');
    $keyAlerts = array_map(function ($row) {
        return [
            'title' => $row['title'],
            'meta'  => $row['category'] . ' · ' . $row['depot'] . ' Depot · ' . $row['severity'],
            'raised' => $row['raised'],
        ];
    }, $keyAlertsStmt->fetchAll());

    // ---------------------------------------------------------------
    // Panel: High-risk drivers (lowest current scores first)
    // ---------------------------------------------------------------
    $highRiskStmt = $pdo->query('
        SELECT d.Full_Name AS name, dep.Location_Name AS depot, latest.Score AS score
        FROM (
            SELECT ds.Driver_ID, ds.Score,
                   ROW_NUMBER() OVER (
                       PARTITION BY ds.Driver_ID
                       ORDER BY ds.Year DESC, ds.Month DESC
                   ) AS rn
            FROM Driver_Safety_Score ds
        ) latest
        JOIN Driver d ON latest.Driver_ID = d.Driver_ID
        JOIN Depot dep ON d.Depot_ID = dep.Depot_ID
        WHERE latest.rn = 1 AND latest.Score <= 75
        ORDER BY latest.Score ASC
        LIMIT 5
    ');
    $highRiskDrivers = array_map(function ($row) {
        $score = (float) $row['score'];
        return [
            'name'   => $row['name'],
            'depot'  => $row['depot'],
            'score'  => $score,
            'status' => $score <= 50 ? 'Suspended' : 'Needs training',
        ];
    }, $highRiskStmt->fetchAll());

    // ---------------------------------------------------------------
    // Panel: Vehicles by depot
    // ---------------------------------------------------------------
    $byDepotStmt = $pdo->query("
        SELECT d.Location_Name AS depot,
               COUNT(*) AS total,
               SUM(CASE WHEN v.Operational_Status IN ('Active','Available') THEN 1 ELSE 0 END) AS ready
        FROM Depot d
        LEFT JOIN Vehicle v ON v.Depot_ID = d.Depot_ID
        GROUP BY d.Depot_ID, d.Location_Name
        ORDER BY d.Location_Name
    ");
    $vehiclesByDepot = array_map(function ($row) {
        return [
            'depot' => $row['depot'],
            'ready' => (int) $row['ready'],
            'total' => (int) $row['total'],
        ];
    }, $byDepotStmt->fetchAll());

    // ---------------------------------------------------------------
    // Panel: Open maintenance jobs (most recently opened, 5 max)
    // ---------------------------------------------------------------
    $openJobsStmt = $pdo->query("
        SELECT mj.Job_ID AS jobId, d.Location_Name AS depotName
        FROM Maintenance_Job mj
        JOIN Workshop w ON mj.Workshop_ID = w.Workshop_ID
        JOIN Depot d ON w.Depot_ID = d.Depot_ID
        WHERE mj.Date_Closed IS NULL
        ORDER BY mj.Date_Opened DESC
        LIMIT 5
    ");
    $openJobs = array_map(function ($row) {
        return [
            'job'      => 'JOB-' . str_pad($row['jobId'], 4, '0', STR_PAD_LEFT),
            'workshop' => $row['depotName'],
            'status'   => 'Open',
        ];
    }, $openJobsStmt->fetchAll());

    // ---------------------------------------------------------------
    // Panel: Certifications expiring soon (next 30 days, 5 max)
    // ---------------------------------------------------------------
    $certsStmt = $pdo->query("
        SELECT d.Full_Name AS driver, dc.Certification_Name AS type,
               DATEDIFF(dc.Expiry_Date, CURDATE()) AS daysLeft
        FROM Driver_Certification dc
        JOIN Driver d ON dc.Driver_ID = d.Driver_ID
        WHERE dc.Expiry_Date >= CURDATE()
        ORDER BY dc.Expiry_Date ASC
        LIMIT 5
    ");
    $certsExpiring = array_map(function ($row) {
        return [
            'driver'   => $row['driver'],
            'type'     => $row['type'],
            'daysLeft' => (int) $row['daysLeft'],
        ];
    }, $certsStmt->fetchAll());

    echo json_encode([
        'hero' => [
            'vehiclesReady'  => $vehiclesReady,
            'inMaintenance'  => $inMaintenance,
            'criticalAlerts' => $criticalAlerts,
        ],
        'kpis' => [
            'totalVehicles'            => $totalVehicles,
            'avgSafetyScore'           => $avgSafetyScore,
            'driversNeedingTraining'   => $driversNeedingTraining,
            'maintenanceCostThisMonth' => $maintenanceCostThisMonth,
        ],
        'keyAlerts'       => $keyAlerts,
        'highRiskDrivers' => $highRiskDrivers,
        'vehiclesByDepot' => $vehiclesByDepot,
        'openJobs'        => $openJobs,
        'certsExpiring'   => $certsExpiring,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'detail' => $e->getMessage()]);
}
