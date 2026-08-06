<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Vehicle', 'read');

        if (isset($_GET['vin'])) {
            $stmt = $pdo->prepare('SELECT * FROM Vehicle WHERE Vin = ?');
            $stmt->execute([$_GET['vin']]);
            $row = $stmt->fetch();
            if ($row && $staff['role_type'] !== 'Head Manager' && (int) $row['Depot_ID'] !== (int) $staff['depot_id']) {
                json_response(['error' => 'Vehicle not found'], 404);
            }
            json_response($row ?: ['error' => 'Vehicle not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Vehicle ORDER BY Registration_Number')->fetchAll());
        }
        $stmt = $pdo->prepare('SELECT * FROM Vehicle WHERE Depot_ID = ? ORDER BY Registration_Number');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Vehicle', 'write');
        $data = get_request_body();
        $required = ['vin', 'depot_id', 'registration_number', 'vehicle_category', 'manufacturer_and_model', 'year_of_manufacture', 'operational_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You can only add vehicles to your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Vehicle (Vin, Depot_ID, Registration_Number, Vehicle_Category, Manufacturer_and_Model, Year_of_Manufacture, Current_Odometer, Operational_Status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['vin'],
            $data['depot_id'],
            $data['registration_number'],
            $data['vehicle_category'],
            $data['manufacturer_and_model'],
            $data['year_of_manufacture'],
            $data['current_odometer'] ?? 0,
            $data['operational_status'],
        ], 'Vehicle created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle', 'action' => 'CREATE',
            'summary' => $data['registration_number'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Vehicle', 'write');
        if (!isset($_GET['vin'])) {
            json_response(['error' => 'Missing ?vin= in URL'], 422);
        }
        if (!vehicle_in_own_depot($pdo, $_GET['vin'], $staff)) {
            json_fail(403, 'You can only edit vehicles in your own depot.');
        }
        $data = get_request_body();
        $required = ['depot_id', 'registration_number', 'vehicle_category', 'manufacturer_and_model', 'year_of_manufacture', 'current_odometer', 'operational_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You cannot move a vehicle to another depot.');
        }

        run_write($pdo, '
            UPDATE Vehicle
            SET Depot_ID = ?, Registration_Number = ?, Vehicle_Category = ?, Manufacturer_and_Model = ?,
                Year_of_Manufacture = ?, Current_Odometer = ?, Operational_Status = ?
            WHERE Vin = ?
        ', [
            $data['depot_id'],
            $data['registration_number'],
            $data['vehicle_category'],
            $data['manufacturer_and_model'],
            $data['year_of_manufacture'],
            $data['current_odometer'],
            $data['operational_status'],
            $_GET['vin'],
        ], 'Vehicle updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle', 'action' => 'UPDATE',
            'summary' => $data['registration_number'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Vehicle', 'write');
        if (!isset($_GET['vin'])) {
            json_response(['error' => 'Missing ?vin= in URL'], 422);
        }
        if (!vehicle_in_own_depot($pdo, $_GET['vin'], $staff)) {
            json_fail(403, 'You can only delete vehicles in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Vehicle WHERE Vin = ?', [$_GET['vin']], 'Vehicle deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Vehicle', 'action' => 'DELETE',
            'summary' => $_GET['vin'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

/** Head Manager can touch any vehicle; everyone else only their own depot's. */
function vehicle_in_own_depot(PDO $pdo, string $vin, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') {
        return true;
    }
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Vehicle WHERE Vin = ?');
    $stmt->execute([$vin]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
