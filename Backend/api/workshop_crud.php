<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Workshop', 'read');
        if (isset($_GET['workshop_id'])) {
            $stmt = $pdo->prepare('
                SELECT w.*, d.Location_Name FROM Workshop w
                JOIN Depot d ON d.Depot_ID = w.Depot_ID
                WHERE w.Workshop_ID = ?
            ');
            $stmt->execute([$_GET['workshop_id']]);
            $row = $stmt->fetch();
            if ($row && $staff['role_type'] !== 'Head Manager' && (int) $row['Depot_ID'] !== (int) $staff['depot_id']) {
                json_response(['error' => 'Workshop not found'], 404);
            }
            json_response($row ?: ['error' => 'Workshop not found'], $row ? 200 : 404);
        }
        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('
                SELECT w.*, d.Location_Name FROM Workshop w
                JOIN Depot d ON d.Depot_ID = w.Depot_ID
                ORDER BY d.Location_Name
            ')->fetchAll());
        }
        $stmt = $pdo->prepare('
            SELECT w.*, d.Location_Name FROM Workshop w
            JOIN Depot d ON d.Depot_ID = w.Depot_ID
            WHERE w.Depot_ID = ?
        ');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Workshop', 'write');
        $data = get_request_body();
        $missing = missing_fields($data, ['depot_id']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'INSERT INTO Workshop (Depot_ID) VALUES (?)', [$data['depot_id']], 'Workshop created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Workshop', 'action' => 'CREATE', 'summary' => 'Depot ' . $data['depot_id'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Workshop', 'write');
        if (!isset($_GET['workshop_id'])) {
            json_response(['error' => 'Missing ?workshop_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['depot_id']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'UPDATE Workshop SET Depot_ID = ? WHERE Workshop_ID = ?',
            [$data['depot_id'], $_GET['workshop_id']], 'Workshop updated', 200, [
                'staff_id' => $staff['staff_id'], 'table' => 'Workshop', 'action' => 'UPDATE', 'summary' => $_GET['workshop_id'],
            ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Workshop', 'write');
        if (!isset($_GET['workshop_id'])) {
            json_response(['error' => 'Missing ?workshop_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Workshop WHERE Workshop_ID = ?', [$_GET['workshop_id']], 'Workshop deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Workshop', 'action' => 'DELETE', 'summary' => $_GET['workshop_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
