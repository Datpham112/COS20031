<?php
/**
 * Backend/api/staff.php
 * ------------------------------------------------------------------
 *   GET    staff.php                -> list every staff member (no password hash returned)
 *   GET    staff.php?staff_id=XXX   -> one staff member
 *   POST   staff.php                -> create
 *   PUT    staff.php?staff_id=XXX   -> update
 *   DELETE staff.php?staff_id=XXX   -> delete
 *
 * Required fields for POST: staff_id, full_name, role_type, contact_info, username, password
 * depot_id is required UNLESS role_type is 'Head Manager' or 'Inventory Manager'
 * (matches the chk_staff_depot_scope constraint in the DDL)
 *
 * The plain "password" field you send is hashed with PHP's password_hash()
 * before being stored - the database never stores or returns plain text.
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

// Roles that are NOT tied to a specific depot (must have Depot_ID = NULL)
const DEPOT_LESS_ROLES = ['Head Manager', 'Inventory Manager'];

switch ($method) {

    case 'GET':
        // Password_Hash is deliberately excluded from every SELECT below.
        if (isset($_GET['staff_id'])) {
            $stmt = $pdo->prepare('SELECT Staff_ID, Full_Name, Role_Type, Depot_ID, Contact_Info, Username FROM Staff WHERE Staff_ID = ?');
            $stmt->execute([$_GET['staff_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Staff not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT Staff_ID, Full_Name, Role_Type, Depot_ID, Contact_Info, Username FROM Staff ORDER BY Full_Name')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['staff_id', 'full_name', 'role_type', 'contact_info', 'username', 'password']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        $depotId = in_array($data['role_type'], DEPOT_LESS_ROLES, true) ? null : ($data['depot_id'] ?? null);
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        run_write($pdo, '
            INSERT INTO Staff (Staff_ID, Full_Name, Role_Type, Depot_ID, Contact_Info, Username, Password_Hash)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['staff_id'],
            $data['full_name'],
            $data['role_type'],
            $depotId,
            $data['contact_info'],
            $data['username'],
            $passwordHash,
        ], 'Staff created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['staff_id'])) {
            json_response(['error' => 'Missing ?staff_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['full_name', 'role_type', 'contact_info', 'username']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        $depotId = in_array($data['role_type'], DEPOT_LESS_ROLES, true) ? null : ($data['depot_id'] ?? null);

        // Only re-hash + update the password if one was actually submitted.
        if (!empty($data['password'])) {
            $stmt = $pdo->prepare('
                UPDATE Staff SET Full_Name = ?, Role_Type = ?, Depot_ID = ?, Contact_Info = ?, Username = ?, Password_Hash = ?
                WHERE Staff_ID = ?
            ');
            try {
                $stmt->execute([
                    $data['full_name'], $data['role_type'], $depotId, $data['contact_info'],
                    $data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $_GET['staff_id'],
                ]);
                json_response(['message' => 'Staff updated (with new password)']);
            } catch (PDOException $e) {
                json_response(['error' => 'Database error', 'detail' => $e->getMessage()], 500);
            }
        } else {
            run_write($pdo, '
                UPDATE Staff SET Full_Name = ?, Role_Type = ?, Depot_ID = ?, Contact_Info = ?, Username = ?
                WHERE Staff_ID = ?
            ', [
                $data['full_name'], $data['role_type'], $depotId, $data['contact_info'],
                $data['username'], $_GET['staff_id'],
            ], 'Staff updated');
        }
        break;

    case 'DELETE':
        if (!isset($_GET['staff_id'])) {
            json_response(['error' => 'Missing ?staff_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Staff WHERE Staff_ID = ?', [$_GET['staff_id']], 'Staff deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
