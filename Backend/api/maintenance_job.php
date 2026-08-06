<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Maintenance_Job', 'read');

        if (isset($_GET['job_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Maintenance_Job WHERE Job_ID = ?');
            $stmt->execute([$_GET['job_id']]);
            $row = $stmt->fetch();
            if ($row && !job_visible($pdo, $row, $staff)) {
                json_response(['error' => 'Job not found'], 404);
            }
            json_response($row ?: ['error' => 'Job not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Maintenance_Job ORDER BY Date_Opened DESC')->fetchAll());
        }
        if ($staff['role_type'] === 'Mechanic') {
            $stmt = $pdo->prepare('
                SELECT DISTINCT mj.* FROM Maintenance_Job mj
                JOIN Maintenance_Activity ma ON ma.Job_ID = mj.Job_ID
                JOIN Activity_Mechanic_Assignment ama ON ama.Activity_ID = ma.Activity_ID
                WHERE ama.Mechanic_ID = ?
                ORDER BY mj.Date_Opened DESC
            ');
            $stmt->execute([$staff['linked_mechanic_id']]);
            json_response($stmt->fetchAll());
        }
        // Workshop Manager -> own workshop only
        $stmt = $pdo->prepare('
            SELECT mj.* FROM Maintenance_Job mj
            JOIN Workshop w ON w.Workshop_ID = mj.Workshop_ID
            WHERE w.Depot_ID = ?
            ORDER BY mj.Date_Opened DESC
        ');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Maintenance_Job', 'write');
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'workshop_id', 'date_opened']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['workshop_id'] !== own_workshop_id($pdo, $staff)) {
            json_fail(403, 'You can only create jobs at your own workshop.');
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
        ], 'Maintenance job created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Maintenance_Job', 'action' => 'CREATE', 'summary' => $data['vin'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Maintenance_Job', 'write');
        if (!isset($_GET['job_id'])) {
            json_response(['error' => 'Missing ?job_id= in URL'], 422);
        }
        if (!job_in_own_workshop($pdo, $_GET['job_id'], $staff)) {
            json_fail(403, 'You can only edit jobs at your own workshop.');
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'workshop_id', 'date_opened']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['workshop_id'] !== own_workshop_id($pdo, $staff)) {
            json_fail(403, 'You cannot move a job to another workshop.');
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
        ], 'Maintenance job updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Maintenance_Job', 'action' => 'UPDATE', 'summary' => 'Job ' . $_GET['job_id'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Maintenance_Job', 'write');
        if (!isset($_GET['job_id'])) {
            json_response(['error' => 'Missing ?job_id= in URL'], 422);
        }
        if (!job_in_own_workshop($pdo, $_GET['job_id'], $staff)) {
            json_fail(403, 'You can only delete jobs at your own workshop.');
        }
        run_write($pdo, 'DELETE FROM Maintenance_Job WHERE Job_ID = ?', [$_GET['job_id']], 'Maintenance job deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Maintenance_Job', 'action' => 'DELETE', 'summary' => 'Job ' . $_GET['job_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function own_workshop_id(PDO $pdo, array $staff): ?int
{
    $stmt = $pdo->prepare('SELECT Workshop_ID FROM Workshop WHERE Depot_ID = ?');
    $stmt->execute([$staff['depot_id']]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
}

function job_in_own_workshop(PDO $pdo, string $jobId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Workshop_ID FROM Maintenance_Job WHERE Job_ID = ?');
    $stmt->execute([$jobId]);
    $workshopId = $stmt->fetchColumn();
    return $workshopId !== false && (int) $workshopId === own_workshop_id($pdo, $staff);
}

function job_visible(PDO $pdo, array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Mechanic') {
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM Maintenance_Activity ma
            JOIN Activity_Mechanic_Assignment ama ON ama.Activity_ID = ma.Activity_ID
            WHERE ma.Job_ID = ? AND ama.Mechanic_ID = ?
        ');
        $stmt->execute([$row['Job_ID'], $staff['linked_mechanic_id']]);
        return (int) $stmt->fetchColumn() > 0;
    }
    return (int) $row['Workshop_ID'] === own_workshop_id($pdo, $staff); // Workshop Manager
}
