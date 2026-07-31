<?php
/**
 * Backend/api/supplier.php
 * ------------------------------------------------------------------
 *   GET    supplier.php                  -> list every supplier
 *   GET    supplier.php?supplier_id=XXX  -> one supplier
 *   POST   supplier.php                  -> create
 *   PUT    supplier.php?supplier_id=XXX  -> update
 *   DELETE supplier.php?supplier_id=XXX  -> delete
 *
 * Required fields for POST: supplier_name, phone_number
 * Optional: contact_name, email_address, address, delivery_time
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['supplier_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Supplier WHERE Supplier_ID = ?');
            $stmt->execute([$_GET['supplier_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Supplier not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Supplier ORDER BY Supplier_Name')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['supplier_name', 'phone_number']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            INSERT INTO Supplier (Supplier_Name, Contact_Name, Phone_Number, Email_Address, Address, Delivery_Time)
            VALUES (?, ?, ?, ?, ?, ?)
        ', [
            $data['supplier_name'],
            $data['contact_name'] ?? null,
            $data['phone_number'],
            $data['email_address'] ?? null,
            $data['address'] ?? null,
            $data['delivery_time'] ?? null,
        ], 'Supplier created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['supplier_id'])) {
            json_response(['error' => 'Missing ?supplier_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['supplier_name', 'phone_number']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            UPDATE Supplier
            SET Supplier_Name = ?, Contact_Name = ?, Phone_Number = ?, Email_Address = ?, Address = ?, Delivery_Time = ?
            WHERE Supplier_ID = ?
        ', [
            $data['supplier_name'],
            $data['contact_name'] ?? null,
            $data['phone_number'],
            $data['email_address'] ?? null,
            $data['address'] ?? null,
            $data['delivery_time'] ?? null,
            $_GET['supplier_id'],
        ], 'Supplier updated');
        break;

    case 'DELETE':
        if (!isset($_GET['supplier_id'])) {
            json_response(['error' => 'Missing ?supplier_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Supplier WHERE Supplier_ID = ?', [$_GET['supplier_id']], 'Supplier deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
