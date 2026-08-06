<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Predictive_Alert', 'read');

        if (isset($_GET['alert_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Predictive_Alert WHERE Alert_ID = ?');
            $stmt->execute([$_GET['alert_id']]);
            $row = $stmt->fetch();
            if ($row && $staff['role_type'] !== 'Head Manager' && (int) $row['Depot_ID'] !== (int) $staff['depot_id']) {
                json_response(['error' => 'Alert not found'], 404);
            }
            json_response($row ?: ['error' => 'Alert not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Predictive_Alert ORDER BY Alert_ID DESC')->fetchAll());
        }
        $stmt = $pdo->prepare('SELECT * FROM Predictive_Alert WHERE Depot_ID = ? ORDER BY Alert_ID DESC');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Predictive_Alert', 'write');
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'depot_id', 'alert_type', 'action_taken']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You can only create alerts for your own depot.');
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
        run_write($pdo, $sql, $values, 'Alert created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Predictive_Alert', 'action' => 'CREATE', 'summary' => $data['alert_type'] . ' - ' . $data['vin'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Predictive_Alert', 'write');
        if (!isset($_GET['alert_id'])) {
            json_response(['error' => 'Missing ?alert_id= in URL'], 422);
        }
        if (!alert_in_own_depot($pdo, $_GET['alert_id'], $staff)) {
            json_fail(403, 'You can only edit alerts in your own depot.');
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['vin', 'depot_id', 'alert_type', 'action_taken']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['depot_id'] !== (int) $staff['depot_id']) {
            json_fail(403, 'You cannot move an alert to another depot.');
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
        ], 'Alert updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Predictive_Alert', 'action' => 'UPDATE', 'summary' => 'Alert ' . $_GET['alert_id'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Predictive_Alert', 'write');
        if (!isset($_GET['alert_id'])) {
            json_response(['error' => 'Missing ?alert_id= in URL'], 422);
        }
        if (!alert_in_own_depot($pdo, $_GET['alert_id'], $staff)) {
            json_fail(403, 'You can only delete alerts in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Predictive_Alert WHERE Alert_ID = ?', [$_GET['alert_id']], 'Alert deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Predictive_Alert', 'action' => 'DELETE', 'summary' => 'Alert ' . $_GET['alert_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function alert_in_own_depot(PDO $pdo, string $alertId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Predictive_Alert WHERE Alert_ID = ?');
    $stmt->execute([$alertId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
