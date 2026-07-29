<?php
/**
 * Backend/api/driver.php
 * ------------------------------------------------------------------
 *   GET    driver.php                 -> list every driver
 *   GET    driver.php?driver_id=XXX   -> one driver
 *   POST   driver.php                 -> create
 *   PUT    driver.php?driver_id=XXX   -> update
 *   DELETE driver.php?driver_id=XXX   -> delete
 *
 * Required fields for POST: driver_id, depot_id, full_name,
 * contact_information, emergency_contact, license_type,
 * license_expiry_date, employment_status
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['driver_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Driver WHERE Driver_ID = ?');
            $stmt->execute([$_GET['driver_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Driver not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Driver ORDER BY Full_Name')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $required = ['driver_id', 'depot_id', 'full_name', 'contact_information', 'emergency_contact', 'license_type', 'license_expiry_date', 'employment_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
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
        ], 'Driver created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['driver_id'])) {
            json_response(['error' => 'Missing ?driver_id= in URL'], 422);
        }
        $data = get_request_body();
        $required = ['depot_id', 'full_name', 'contact_information', 'emergency_contact', 'license_type', 'license_expiry_date', 'employment_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
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
        ], 'Driver updated');
        break;

    case 'DELETE':
        if (!isset($_GET['driver_id'])) {
            json_response(['error' => 'Missing ?driver_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Driver WHERE Driver_ID = ?', [$_GET['driver_id']], 'Driver deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
