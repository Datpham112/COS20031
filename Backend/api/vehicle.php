<?php
/**
 * Backend/api/vehicle.php
 * ------------------------------------------------------------------
 * Full CRUD for the Vehicle table.
 *
 *   GET    vehicle.php            -> list every vehicle
 *   GET    vehicle.php?vin=XXX    -> one vehicle
 *   POST   vehicle.php            -> create (body: JSON or form fields below)
 *   PUT    vehicle.php?vin=XXX    -> update
 *   DELETE vehicle.php?vin=XXX    -> delete
 *
 * Required fields for POST: vin, depot_id, registration_number,
 * vehicle_category, manufacturer_and_model, year_of_manufacture,
 * operational_status. (current_odometer is optional, defaults to 0)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['vin'])) {
            $stmt = $pdo->prepare('SELECT * FROM Vehicle WHERE Vin = ?');
            $stmt->execute([$_GET['vin']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Vehicle not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Vehicle ORDER BY Registration_Number')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $required = ['vin', 'depot_id', 'registration_number', 'vehicle_category', 'manufacturer_and_model', 'year_of_manufacture', 'operational_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
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
        ], 'Vehicle created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['vin'])) {
            json_response(['error' => 'Missing ?vin= in URL'], 422);
        }
        $data = get_request_body();
        $required = ['depot_id', 'registration_number', 'vehicle_category', 'manufacturer_and_model', 'year_of_manufacture', 'current_odometer', 'operational_status'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
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
        ], 'Vehicle updated');
        break;

    case 'DELETE':
        if (!isset($_GET['vin'])) {
            json_response(['error' => 'Missing ?vin= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Vehicle WHERE Vin = ?', [$_GET['vin']], 'Vehicle deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
