<?php
/**
 * GET /Backend/api/workshop.php
 * ------------------------------------------------------------------
 * Feeds the Workshop Hub page (Vehicles / Alerts / Jobs tabs).
 * Filtering/searching still happens client-side in script.js, so this
 * endpoint simply returns the full current dataset in the exact shape
 * the frontend's `workshopData` object already expects.
 *
 * Response shape:
 * {
 *   "depots":    [{ "id": 1, "name": "Ha Noi" }, ...],
 *   "workshops": [{ "id": 1, "depotId": 1, "name": "Workshop HN-01" }, ...],
 *   "vehicles":  [{ "plate", "vin", "category", "depot", "status", "odometer", "alerts" }, ...],
 *   "alerts":    [{ "title", "vehicle", "severity", "depot", "raised", "action" }, ...],
 *   "jobs":      [{ "job", "vehicle", "workshop", "opened", "closed", "downtime", "cost", "status" }, ...]
 * }
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = get_db_connection();

    // ---------------------------------------------------------------
    // Depots
    // ---------------------------------------------------------------
    $depotsStmt = $pdo->query('
        SELECT Depot_ID AS id, Location_Name AS name
        FROM Depot
        ORDER BY Location_Name
    ');
    $depots = $depotsStmt->fetchAll();

    // ---------------------------------------------------------------
    // Workshops (one workshop per depot, named "Workshop <CODE>-01"
    // to match the existing frontend convention)
    // ---------------------------------------------------------------
    $workshopsStmt = $pdo->query('
        SELECT w.Workshop_ID AS id, w.Depot_ID AS depotId, d.Location_Name AS depotName
        FROM Workshop w
        JOIN Depot d ON w.Depot_ID = d.Depot_ID
        ORDER BY w.Workshop_ID
    ');
    $workshops = array_map(function ($row) {
        return [
            'id'      => (int) $row['id'],
            'depotId' => (int) $row['depotId'],
            'name'    => 'Workshop ' . depot_code($row['depotName']) . '-01',
        ];
    }, $workshopsStmt->fetchAll());

    // ---------------------------------------------------------------
    // Vehicles (+ live count of open predictive alerts per vehicle)
    // ---------------------------------------------------------------
    $vehiclesStmt = $pdo->query('
        SELECT
            v.Registration_Number   AS plate,
            v.Vin                   AS vin,
            v.Vehicle_Category      AS category,
            d.Location_Name         AS depot,
            v.Operational_Status    AS status,
            v.Current_Odometer      AS odometer,
            (
                SELECT COUNT(*) FROM Predictive_Alert pa WHERE pa.VIN = v.Vin
            ) AS alertCount
        FROM Vehicle v
        JOIN Depot d ON v.Depot_ID = d.Depot_ID
        ORDER BY v.Registration_Number
    ');
    $vehicles = array_map(function ($row) {
        return [
            'plate'    => $row['plate'],
            'vin'      => $row['vin'],
            'category' => $row['category'],
            'depot'    => $row['depot'],
            'status'   => $row['status'],
            'odometer' => number_format((float) $row['odometer'], 2) . ' km',
            'alerts'   => (int) $row['alertCount'],
        ];
    }, $vehiclesStmt->fetchAll());

    // ---------------------------------------------------------------
    // Alerts
    // Requires migration Backend/migrations/001_add_alert_severity_and_timestamp.sql
    // to have been run (adds Severity_Level and Raised_At columns).
    // ---------------------------------------------------------------
    $alertsStmt = $pdo->query('
        SELECT
            pa.Alert_Type     AS title,
            v.Registration_Number AS vehicle,
            pa.Severity_Level AS severity,
            d.Location_Name   AS depot,
            pa.Raised_At      AS raised,
            pa.Action_Taken   AS action
        FROM Predictive_Alert pa
        JOIN Vehicle v ON pa.VIN = v.Vin
        JOIN Depot d ON pa.Depot_ID = d.Depot_ID
        ORDER BY pa.Raised_At DESC
    ');
    $alerts = array_map(function ($row) {
        return [
            'title'    => $row['title'],
            'vehicle'  => $row['vehicle'],
            'severity' => $row['severity'],
            'depot'    => $row['depot'],
            'raised'   => substr($row['raised'], 0, 10), // YYYY-MM-DD
            'action'   => $row['action'],
        ];
    }, $alertsStmt->fetchAll());

    // ---------------------------------------------------------------
    // Maintenance jobs
    // ---------------------------------------------------------------
    $jobsStmt = $pdo->query('
        SELECT
            mj.Job_ID           AS jobId,
            v.Registration_Number AS vehicle,
            w.Workshop_ID        AS workshopId,
            d.Location_Name      AS depotName,
            mj.Date_Opened       AS opened,
            mj.Date_Closed        AS closed,
            mj.Downtime_Hours    AS downtime,
            mj.Total_Cost        AS cost
        FROM Maintenance_Job mj
        JOIN Vehicle v ON mj.VIN = v.Vin
        JOIN Workshop w ON mj.Workshop_ID = w.Workshop_ID
        JOIN Depot d ON w.Depot_ID = d.Depot_ID
        ORDER BY mj.Date_Opened DESC
    ');
    $jobs = array_map(function ($row) {
        return [
            'job'      => 'JOB-' . str_pad($row['jobId'], 4, '0', STR_PAD_LEFT),
            'vehicle'  => $row['vehicle'],
            'workshop' => 'Workshop ' . depot_code($row['depotName']) . '-01',
            'opened'   => $row['opened'],
            'closed'   => $row['closed'],
            'downtime' => number_format((float) $row['downtime'], 2) . 'h',
            'cost'     => number_format((float) $row['cost'], 0) . ' VND',
            'status'   => $row['closed'] === null ? 'Open' : 'Closed',
        ];
    }, $jobsStmt->fetchAll());

    echo json_encode([
        'depots'    => $depots,
        'workshops' => $workshops,
        'vehicles'  => $vehicles,
        'alerts'    => $alerts,
        'jobs'      => $jobs,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'detail' => $e->getMessage()]);
}

/**
 * Turns "Ha Noi" -> "HN", "Da Nang" -> "DN", "Ho Chi Minh City" -> "HCMC",
 * "Can Tho" -> "CT". Mirrors the workshop-naming convention already used
 * throughout the original frontend sample data.
 */
function depot_code(string $locationName): string
{
    $map = [
        'Ha Noi'             => 'HN',
        'Da Nang'            => 'DN',
        'Ho Chi Minh City'   => 'HCMC',
        'Can Tho'            => 'CT',
    ];

    if (isset($map[$locationName])) {
        return $map[$locationName];
    }

    // Fallback for any depot not in the map above: initials of each word.
    $initials = '';
    foreach (preg_split('/\s+/', trim($locationName)) as $word) {
        $initials .= mb_strtoupper(mb_substr($word, 0, 1));
    }
    return $initials !== '' ? $initials : 'DP';
}
