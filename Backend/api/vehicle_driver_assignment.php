<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Vehicle_Driver_Assignment', 'read');

        if (isset($_GET['assignment_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Vehicle_Driver_Assignment WHERE Assignment_ID = ?');
            $stmt->execute([$_GET['assignment_id']]);
            $row = $stmt->fetch();
            if ($row && !assignment_row_visible($pdo, $row, $staff)) {
                json_response(['error' => 'Assignment not found'], 404);
            }
            json_response($row ?: ['error' => 'Assignment not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Vehicle_Driver_Assignment ORDER BY Start_Date DESC')->fetchAll());
        }
        if ($staff['role_type'] === 'Driver') {
            $stmt = $pdo->prepare('SELECT * FROM Vehicle_Driver_Assignment WHERE Driver_ID = ? ORDER BY Start_Date DESC');
            $stmt->execute([$staff['linked_driver_id']]);
            json_response($stmt->fetchAll());
        }
        // Depot Manager / Driver Manager -> own depot only (via the driver)
        $stmt = $pdo->prepare('
            SELECT vda.* FROM Vehicle_Driver_Assignment vda
            JOIN Driver d ON d.Driver_ID = vda.Driver_ID
            WHERE d.Depot_ID = ?
            ORDER BY vda.Start_Date DESC
        ');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Vehicle_Driver_Assignment', 'write');
        $data = get_request_body();
        $required = ['driver_id', 'vin', 'start_date'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if (!driver_in_own_depot_vda($pdo, $data['driver_id'], $staff)) {
            json_fail(403, 'You can only assign vehicles to drivers in your own depot.');
        }
        if (!vehicle_in_own_depot_vda($pdo, $data['vin'], $staff)) {
            json_fail(403, 'You can only assign vehicles from your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Vehicle_Driver_Assignment (Driver_ID, VIN, Start_Date, End_Date)
            VALUES (?, ?, ?, ?)
        ', [
            $data['driver_id'],
            $data['vin'],
            $data['start_date'],
            $data['end_date'] ?? null,
        ], 'Assignment created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle_Driver_Assignment', 'action' => 'CREATE',
            'summary' => $data['driver_id'] . ' -> ' . $data['vin'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Vehicle_Driver_Assignment', 'write');
        if (!isset($_GET['assignment_id'])) {
            json_response(['error' => 'Missing ?assignment_id= in URL'], 422);
        }
        if (!assignment_in_own_depot($pdo, $_GET['assignment_id'], $staff)) {
            json_fail(403, 'You can only edit assignments in your own depot.');
        }
        $data = get_request_body();
        $required = ['driver_id', 'vin', 'start_date'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if (!driver_in_own_depot_vda($pdo, $data['driver_id'], $staff)) {
            json_fail(403, 'You can only assign vehicles to drivers in your own depot.');
        }
        if (!vehicle_in_own_depot_vda($pdo, $data['vin'], $staff)) {
            json_fail(403, 'You can only assign vehicles from your own depot.');
        }

        run_write($pdo, '
            UPDATE Vehicle_Driver_Assignment
            SET Driver_ID = ?, VIN = ?, Start_Date = ?, End_Date = ?
            WHERE Assignment_ID = ?
        ', [
            $data['driver_id'],
            $data['vin'],
            $data['start_date'],
            $data['end_date'] ?? null,
            $_GET['assignment_id'],
        ], 'Assignment updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle_Driver_Assignment', 'action' => 'UPDATE',
            'summary' => $data['driver_id'] . ' -> ' . $data['vin'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Vehicle_Driver_Assignment', 'write');
        if (!isset($_GET['assignment_id'])) {
            json_response(['error' => 'Missing ?assignment_id= in URL'], 422);
        }
        if (!assignment_in_own_depot($pdo, $_GET['assignment_id'], $staff)) {
            json_fail(403, 'You can only delete assignments in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Vehicle_Driver_Assignment WHERE Assignment_ID = ?', [$_GET['assignment_id']], 'Assignment deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle_Driver_Assignment', 'action' => 'DELETE',
            'summary' => (string) $_GET['assignment_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function assignment_row_visible(PDO $pdo, array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Driver') return $row['Driver_ID'] === $staff['linked_driver_id'];
    return driver_in_own_depot_vda($pdo, $row['Driver_ID'], $staff);
}

function assignment_in_own_depot(PDO $pdo, string $assignmentId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Driver_ID FROM Vehicle_Driver_Assignment WHERE Assignment_ID = ?');
    $stmt->execute([$assignmentId]);
    $driverId = $stmt->fetchColumn();
    return $driverId !== false && driver_in_own_depot_vda($pdo, $driverId, $staff);
}

function driver_in_own_depot_vda(PDO $pdo, string $driverId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}

function vehicle_in_own_depot_vda(PDO $pdo, string $vin, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Vehicle WHERE Vin = ?');
    $stmt->execute([$vin]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
