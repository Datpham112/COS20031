<?php
/**
 * Backend/api/driver.php
 * ------------------------------------------------------------------
 *   GET    driver.php                 -> list drivers (scoped by role)
 *   GET    driver.php?driver_id=XXX   -> one driver
 *   POST   driver.php                 -> create
 *   PUT    driver.php?driver_id=XXX   -> update
 *   DELETE driver.php?driver_id=XXX   -> delete
 *
 * Required fields for POST: driver_id, depot_id, full_name,
 * contact_information, emergency_contact, license_type,
 * license_expiry_date, employment_status
 *
 * Permissions:
 *   Read:  Head Manager (all), Depot Manager (own depot),
 *          Driver Manager (own depot), Driver (own record only)
 *   Write: Driver Manager (own depot only)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Driver', 'read');

        if (isset($_GET['driver_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Driver WHERE Driver_ID = ?');
            $stmt->execute([$_GET['driver_id']]);
            $row = $stmt->fetch();
            if ($row && !driver_row_visible($row, $staff)) {
                json_response(['error' => 'Driver not found'], 404);
            }
            json_response($row ?: ['error' => 'Driver not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Driver ORDER BY Full_Name')->fetchAll());
        }
        if ($staff['role_type'] === 'Driver') {
            $stmt = $pdo->prepare('SELECT * FROM Driver WHERE Driver_ID = ?');
            $stmt->execute([$staff['linked_driver_id']]);
            json_response($stmt->fetchAll());
        }
        // Depot Manager / Driver Manager -> own depot only
        $stmt = $pdo->prepare('SELECT * FROM Driver WHERE Depot_ID = ? ORDER BY Full_Name');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Driver', 'write');
        $data = get_request_body();
        $required = ['driver_id', 'depot_id', 'full_name', 'contact_information', 'emergency_contact', 'license_type', 'license_expiry_date', 'employment_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You can only add drivers to your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Driver (Driver_ID, Depot_ID, Full_Name, Contact_Information, Emergency_Contact, License_Type, License_Expiry_Date, Employment_Status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['driver_id'],
            $data['depot_id'],
            $data['full_name'],
            $data['contact_information'],
            $data['emergency_contact'],
            $data['license_type'],
            $data['license_expiry_date'],
            $data['employment_status'],
        ], 'Driver created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver', 'action' => 'CREATE',
            'summary' => $data['full_name'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Driver', 'write');
        if (!isset($_GET['driver_id'])) {
            json_response(['error' => 'Missing ?driver_id= in URL'], 422);
        }
        if (!driver_in_own_depot($pdo, $_GET['driver_id'], $staff)) {
            json_fail(403, 'You can only edit drivers in your own depot.');
        }
        $data = get_request_body();
        $required = ['depot_id', 'full_name', 'contact_information', 'emergency_contact', 'license_type', 'license_expiry_date', 'employment_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You cannot move a driver to another depot.');
        }

        run_write($pdo, '
            UPDATE Driver
            SET Depot_ID = ?, Full_Name = ?, Contact_Information = ?, Emergency_Contact = ?,
                License_Type = ?, License_Expiry_Date = ?, Employment_Status = ?
            WHERE Driver_ID = ?
        ', [
            $data['depot_id'],
            $data['full_name'],
            $data['contact_information'],
            $data['emergency_contact'],
            $data['license_type'],
            $data['license_expiry_date'],
            $data['employment_status'],
            $_GET['driver_id'],
        ], 'Driver updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver', 'action' => 'UPDATE',
            'summary' => $data['full_name'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Driver', 'write');
        if (!isset($_GET['driver_id'])) {
            json_response(['error' => 'Missing ?driver_id= in URL'], 422);
        }
        if (!driver_in_own_depot($pdo, $_GET['driver_id'], $staff)) {
            json_fail(403, 'You can only delete drivers in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Driver WHERE Driver_ID = ?', [$_GET['driver_id']], 'Driver deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver', 'action' => 'DELETE',
            'summary' => $_GET['driver_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function driver_row_visible(array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Driver') return $row['Driver_ID'] === $staff['linked_driver_id'];
    return (int) $row['Depot_ID'] === (int) $staff['depot_id']; // Depot Manager / Driver Manager
}

function driver_in_own_depot(PDO $pdo, string $driverId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
