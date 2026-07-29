<?php
/**
 * Backend/api/mechanic.php
 * ------------------------------------------------------------------
 *   GET    mechanic.php                     -> list every mechanic
 *   GET    mechanic.php?mechanic_id=XXX     -> one mechanic
 *   POST   mechanic.php                     -> create
 *   PUT    mechanic.php?mechanic_id=XXX     -> update
 *   DELETE mechanic.php?mechanic_id=XXX     -> delete
 *
 * Required fields for POST: workshop_id, full_name
 * (Mechanic_ID is AUTO_INCREMENT, don't send it)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['mechanic_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Mechanic WHERE Mechanic_ID = ?');
            $stmt->execute([$_GET['mechanic_id']]);
            $row = $stmt->fetch();
            json_response($row ?: ['error' => 'Mechanic not found'], $row ? 200 : 404);
        }
        json_response($pdo->query('SELECT * FROM Mechanic ORDER BY Full_Name')->fetchAll());
        break;

    case 'POST':
        $data = get_request_body();
        $missing = missing_fields($data, ['workshop_id', 'full_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'INSERT INTO Mechanic (Workshop_ID, Full_Name) VALUES (?, ?)',
            [$data['workshop_id'], $data['full_name']], 'Mechanic created', 201);
        break;

    case 'PUT':
        if (!isset($_GET['mechanic_id'])) {
            json_response(['error' => 'Missing ?mechanic_id= in URL'], 422);
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['workshop_id', 'full_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        run_write($pdo, 'UPDATE Mechanic SET Workshop_ID = ?, Full_Name = ? WHERE Mechanic_ID = ?',
            [$data['workshop_id'], $data['full_name'], $_GET['mechanic_id']], 'Mechanic updated');
        break;

    case 'DELETE':
        if (!isset($_GET['mechanic_id'])) {
            json_response(['error' => 'Missing ?mechanic_id= in URL'], 422);
        }
        run_write($pdo, 'DELETE FROM Mechanic WHERE Mechanic_ID = ?', [$_GET['mechanic_id']], 'Mechanic deleted');
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
