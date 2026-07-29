<?php
/**
 * Backend/api/part.php
 * ------------------------------------------------------------------
 *   GET    part.php              -> list every part
 *   GET    part.php?part_id=XXX  -> one part
 *   POST   part.php              -> create
 *   PUT    part.php?part_id=XXX  -> update
 *   DELETE part.php?part_id=XXX  -> delete
 *
 * Required fields for POST: part_name
 * Optional: part_category, brand, unit_price, reorder_level
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['part_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Part WHERE Part_ID = ?');
            $stmt->execute([$_GET['part_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Part not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Part ORDER BY Part_Name')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['part_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            INSERT INTO Part (Part_Name, Part_Category, Brand, Unit_Price, Reorder_Level)
            VALUES (?, ?, ?, ?, ?)
        ', [
            $data['part_name'],
            $data['part_category'] ?? null,
            $data['brand'] ?? null,
            $data['unit_price'] ?? null,
            $data['reorder_level'] ?? null,
        ], 'Part created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['part_id'])) {
            json_response(['error' => 'Missing ?part_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['part_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, '
            UPDATE Part
            SET Part_Name = ?, Part_Category = ?, Brand = ?, Unit_Price = ?, Reorder_Level = ?
            WHERE Part_ID = ?
        ', [
            $data['part_name'],
            $data['part_category'] ?? null,
            $data['brand'] ?? null,
            $data['unit_price'] ?? null,
            $data['reorder_level'] ?? null,
            $_GET['part_id'],
        ], 'Part updated');
        break;

    case 'DELETE':
        if (!isset($_GET['part_id'])) {
            json_response(['error' => 'Missing ?part_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Part WHERE Part_ID = ?', [$_GET['part_id']], 'Part deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
