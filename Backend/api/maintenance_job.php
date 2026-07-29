<?php
/**
 * Backend/api/maintenance_job.php
 * ------------------------------------------------------------------
 *   GET    maintenance_job.php               -> list every job
 *   GET    maintenance_job.php?job_id=XXX     -> one job
 *   POST   maintenance_job.php                -> create
 *   PUT    maintenance_job.php?job_id=XXX      -> update
 *   DELETE maintenance_job.php?job_id=XXX      -> delete
 *
 * Required fields for POST: vin, workshop_id, date_opened
 * Optional: linked_alert_id, date_closed, downtime_hours, total_cost
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['job_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Maintenance_Job WHERE Job_ID = ?');
            $stmt->execute([$_GET['job_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Job not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Maintenance_Job ORDER BY Date_Opened DESC')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'workshop_id', 'date_opened']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            INSERT INTO Maintenance_Job (VIN, Workshop_ID, Linked_Alert_ID, Date_Opened, Date_Closed, Downtime_Hours, Total_Cost)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['vin'],
            $data['workshop_id'],
            $data['linked_alert_id'] ?? null,
            $data['date_opened'],
            $data['date_closed'] ?? null,
            $data['downtime_hours'] ?? null,
            $data['total_cost'] ?? null,
        ], 'Maintenance job created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['job_id'])) {
            json_response(['error' => 'Missing ?job_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'workshop_id', 'date_opened']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            UPDATE Maintenance_Job
            SET VIN = ?, Workshop_ID = ?, Linked_Alert_ID = ?, Date_Opened = ?,
                Date_Closed = ?, Downtime_Hours = ?, Total_Cost = ?
            WHERE Job_ID = ?
        ', [
            $data['vin'],
            $data['workshop_id'],
            $data['linked_alert_id'] ?? null,
            $data['date_opened'],
            $data['date_closed'] ?? null,
            $data['downtime_hours'] ?? null,
            $data['total_cost'] ?? null,
            $_GET['job_id'],
        ], 'Maintenance job updated');
        break;

    case 'DELETE':
        if (!isset($_GET['job_id'])) {
            json_response(['error' => 'Missing ?job_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Maintenance_Job WHERE Job_ID = ?', [$_GET['job_id']], 'Maintenance job deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
