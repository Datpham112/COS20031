<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Driver', 'read');

        // Driver Portal: returns everything required by driver.html
        if (isset($_GET['action']) && $_GET['action'] === 'profile') {
            if ($staff['role_type'] !== 'Driver') {
                json_fail(403, 'Only drivers can access this page.');
            }
            $driverId = $staff['linked_driver_id'];

            $stmt = $pdo->prepare('SELECT d.*, depot.Location_Name FROM Driver d JOIN Depot depot ON d.Depot_ID = depot.Depot_ID WHERE d.Driver_ID = ?');
            $stmt->execute([$driverId]);
            $profile = $stmt->fetch();

            $stmt = $pdo->prepare('SELECT Certification_Name, Expiry_Date FROM Driver_Certification WHERE Driver_ID = ? ORDER BY Expiry_Date');
            $stmt->execute([$driverId]);
            $certifications = $stmt->fetchAll();

            $stmt = $pdo->prepare('SELECT Month, Year, Score FROM Driver_Safety_Score WHERE Driver_ID = ? ORDER BY Year DESC, Month DESC');
            $stmt->execute([$driverId]);
            $scores = $stmt->fetchAll();

            $stmt = $pdo->prepare('
                SELECT a.Assignment_ID, a.Start_Date, a.End_Date, v.VIN, v.Registration_Number, v.Manufacturer_and_Model, v.Vehicle_Category
                FROM Vehicle_Driver_Assignment a
                JOIN Vehicle v ON a.VIN = v.VIN
                WHERE a.Driver_ID = ?
                ORDER BY a.Start_Date DESC
            ');
            $stmt->execute([$driverId]);
            $vehicles = $stmt->fetchAll();

            $stmt = $pdo->prepare('
                SELECT e.Event_ID, e.Timestamp, e.Event_Type, e.Severity_Level, e.Odometer_At_Event, e.Review_Comments, v.Registration_Number
                FROM Safety_Event e
                JOIN Vehicle v ON e.VIN = v.VIN
                WHERE e.Driver_ID = ?
                ORDER BY e.Timestamp DESC
            ');
            $stmt->execute([$driverId]);
            $events = $stmt->fetchAll();

            json_response([
                'profile' => $profile,
                'certifications' => $certifications,
                'scores' => $scores,
                'vehicles' => $vehicles,
                'events' => $events,
            ]);
        }

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
