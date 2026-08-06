<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

// Roles that are NOT tied to a specific depot (must have Depot_ID = NULL)
const DEPOT_LESS_ROLES = ['Head Manager', 'Inventory Manager'];

switch ($method) {

    case 'GET':
        require_table_permission('Staff', 'read');
        // Password_Hash is deliberately excluded from every SELECT below.
        if (isset($_GET['staff_id'])) {
            $stmt = $pdo->prepare('SELECT Staff_ID, Full_Name, Role_Type, Depot_ID, Linked_Driver_ID, Contact_Info, Username FROM Staff WHERE Staff_ID = ?');
            $stmt->execute([$_GET['staff_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Staff not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT Staff_ID, Full_Name, Role_Type, Depot_ID, Linked_Driver_ID, Contact_Info, Username FROM Staff ORDER BY Full_Name')->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Staff', 'write');
        $data = get_request_body();
        $missing = missing_fields($data, ['staff_id', 'full_name', 'role_type', 'contact_info', 'username', 'password']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        $depotId = in_array($data['role_type'], DEPOT_LESS_ROLES, true) ? null : ($data['depot_id'] ?? null);
        $linkedDriverId = $data['role_type'] === 'Driver' ? ($data['linked_driver_id'] ?? null) : null;
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        run_write($pdo, '
            INSERT INTO Staff (Staff_ID, Full_Name, Role_Type, Depot_ID, Linked_Driver_ID, Contact_Info, Username, Password_Hash)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['staff_id'],
            $data['full_name'],
            $data['role_type'],
            $depotId,
            $linkedDriverId,
            $data['contact_info'],
            $data['username'],
            $passwordHash,
        ], 'Staff created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Staff', 'action' => 'CREATE',
            'summary' => $data['full_name'] . ' (' . $data['role_type'] . ')',
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Staff', 'write');
        if (!isset($_GET['staff_id'])) {
            json_response(['error' => 'Missing ?staff_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['full_name', 'role_type', 'contact_info', 'username']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        $depotId = in_array($data['role_type'], DEPOT_LESS_ROLES, true) ? null : ($data['depot_id'] ?? null);
        $linkedDriverId = $data['role_type'] === 'Driver' ? ($data['linked_driver_id'] ?? null) : null;
        $audit = ['staff_id' => $staff['staff_id'], 'table' => 'Staff', 'action' => 'UPDATE', 'summary' => $data['full_name']];

        // Only re-hash + update the password if one was actually submitted.
        if (!empty($data['password'])) {
            $stmt = $pdo->prepare('
                UPDATE Staff SET Full_Name = ?, Role_Type = ?, Depot_ID = ?, Linked_Driver_ID = ?, Contact_Info = ?, Username = ?, Password_Hash = ?
                WHERE Staff_ID = ?
            ');
            try {
                $stmt->execute([
                    $data['full_name'], $data['role_type'], $depotId, $linkedDriverId, $data['contact_info'],
                    $data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $_GET['staff_id'],
                ]);
                log_audit($pdo, $audit['staff_id'], $audit['table'], $audit['action'], $audit['summary']);
                json_response(['message' => 'Staff updated (with new password)']);
            } catch (PDOException $e) {
                json_response(['error' => 'Database error', 'detail' => $e->getMessage()], 500);
            }
        } else {
            run_write($pdo, '
                UPDATE Staff SET Full_Name = ?, Role_Type = ?, Depot_ID = ?, Linked_Driver_ID = ?, Contact_Info = ?, Username = ?
                WHERE Staff_ID = ?
            ', [
                $data['full_name'], $data['role_type'], $depotId, $linkedDriverId, $data['contact_info'],
                $data['username'], $_GET['staff_id'],
            ], 'Staff updated', 200, $audit);
        }
        break;

    case 'DELETE':
        $staff = require_table_permission('Staff', 'write');
        if (!isset($_GET['staff_id'])) {
            json_response(['error' => 'Missing ?staff_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Staff WHERE Staff_ID = ?', [$_GET['staff_id']], 'Staff deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Staff', 'action' => 'DELETE',
            'summary' => $_GET['staff_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
