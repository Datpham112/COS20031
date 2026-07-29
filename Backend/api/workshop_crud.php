<?php
/**
 * Backend/api/workshop_crud.php
 * ------------------------------------------------------------------
 * NOTE: named "_crud" to avoid clashing with the existing
 * Backend/api/workshop.php (which powers the Workshop Hub dashboard view).
 *
 *   GET    workshop_crud.php                    -> list every workshop
 *   GET    workshop_crud.php?workshop_id=XXX     -> one workshop
 *   POST   workshop_crud.php                     -> create (one per depot)
 *   PUT    workshop_crud.php?workshop_id=XXX      -> update (change depot)
 *   DELETE workshop_crud.php?workshop_id=XXX      -> delete
 *
 * Required fields for POST: depot_id
 * (Workshop_ID is AUTO_INCREMENT, don't send it)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['workshop_id'])) {
            $stmt = $pdo->prepare('
                SELECT w.*, d.Location_Name FROM Workshop w
                JOIN Depot d ON d.Depot_ID = w.Depot_ID
                WHERE w.Workshop_ID = ?
            ');
            $stmt->execute([$_GET['workshop_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Workshop not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('
            SELECT w.*, d.Location_Name FROM Workshop w
            JOIN Depot d ON d.Depot_ID = w.Depot_ID
            ORDER BY d.Location_Name
        ')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['depot_id']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'INSERT INTO Workshop (Depot_ID) VALUES (?)', [$data['depot_id']], 'Workshop created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['workshop_id'])) {
            json_response(['error' => 'Missing ?workshop_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['depot_id']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'UPDATE Workshop SET Depot_ID = ? WHERE Workshop_ID = ?',
            [$data['depot_id'], $_GET['workshop_id']], 'Workshop updated');
        break;

    case 'DELETE':
        if (!isset($_GET['workshop_id'])) {
            json_response(['error' => 'Missing ?workshop_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Workshop WHERE Workshop_ID = ?', [$_GET['workshop_id']], 'Workshop deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
