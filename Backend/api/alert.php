<?php
/**
 * Backend/api/alert.php
 * ------------------------------------------------------------------
 *   GET    alert.php               -> list every alert
 *   GET    alert.php?alert_id=XXX  -> one alert
 *   POST   alert.php               -> create
 *   PUT    alert.php?alert_id=XXX  -> update
 *   DELETE alert.php?alert_id=XXX  -> delete
 *
 * Required fields for POST: vin, depot_id, alert_type, action_taken
 * Optional: severity_level (defaults to 'Medium'), raised_at (defaults to now)
 * alert_type must be one of: Brake Wear, Overheating Risk,
 *   Battery Degradation, Engine Fault, Tyre Pressure
 * action_taken must be one of: Acknowledged, Scheduled Repair,
 *   Emergency Repair, Resolved
 * severity_level must be one of: Low, Medium, High, Critical
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['alert_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Predictive_Alert WHERE Alert_ID = ?');
            $stmt->execute([$_GET['alert_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Alert not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Predictive_Alert ORDER BY Alert_ID DESC')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'depot_id', 'alert_type', 'action_taken']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        $fields = ['VIN', 'Depot_ID', 'Alert_Type', 'Action_Taken'];
        $values = [$data['vin'], $data['depot_id'], $data['alert_type'], $data['action_taken']];
        $placeholders = ['?', '?', '?', '?'];

        if (!empty($data['severity_level'])) {
            $fields[] = 'Severity_Level';
            $values[] = $data['severity_level'];
            $placeholders[] = '?';
        }
        if (!empty($data['raised_at'])) {
            $fields[] = 'Raised_At';
            $values[] = $data['raised_at'];
            $placeholders[] = '?';
        }

        $sql = 'INSERT INTO Predictive_Alert (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        run_write($pdo, $sql, $values, 'Alert created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['alert_id'])) {
            json_response(['error' => 'Missing ?alert_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'depot_id', 'alert_type', 'action_taken']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            UPDATE Predictive_Alert
            SET VIN = ?, Depot_ID = ?, Alert_Type = ?, Action_Taken = ?,
                Severity_Level = COALESCE(?, Severity_Level),
                Raised_At = COALESCE(?, Raised_At)
            WHERE Alert_ID = ?
        ', [
            $data['vin'],
            $data['depot_id'],
            $data['alert_type'],
            $data['action_taken'],
            $data['severity_level'] ?? null,
            $data['raised_at'] ?? null,
            $_GET['alert_id'],
        ], 'Alert updated');
        break;

    case 'DELETE':
        if (!isset($_GET['alert_id'])) {
            json_response(['error' => 'Missing ?alert_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Predictive_Alert WHERE Alert_ID = ?', [$_GET['alert_id']], 'Alert deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
