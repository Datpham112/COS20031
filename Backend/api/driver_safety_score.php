<?php
/**
 * Backend/api/driver_safety_score.php
 * ------------------------------------------------------------------
 *   GET    driver_safety_score.php                  -> list (scoped by role)
 *   GET    driver_safety_score.php?score_id=XXX       -> one record
 *   POST   driver_safety_score.php                    -> create
 *   PUT    driver_safety_score.php?score_id=XXX        -> update
 *   DELETE driver_safety_score.php?score_id=XXX        -> delete
 *
 * Required fields for POST: driver_id, month, year, score
 * (Score_ID is AUTO_INCREMENT, don't send it.)
 *
 * Permissions (kept in sync with Backend/auth/auth_check.php ->
 * TABLE_PERMISSIONS['Driver_Safety_Score']):
 *   Read:  Head Manager (all), Driver Manager (own depot), Driver (own record only)
 *   Write: Driver Manager (own depot only)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Driver_Safety_Score', 'read');

        if (isset($_GET['score_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Driver_Safety_Score WHERE Score_ID = ?');
            $stmt->execute([$_GET['score_id']]);
            $row = $stmt->fetch();
            if ($row && !score_row_visible($pdo, $row, $staff)) {
                json_response(['error' => 'Safety score not found'], 404);
            }
            json_response($row ?: ['error' => 'Safety score not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Driver_Safety_Score ORDER BY Year DESC, Month DESC')->fetchAll());
        }
        if ($staff['role_type'] === 'Driver') {
            $stmt = $pdo->prepare('SELECT * FROM Driver_Safety_Score WHERE Driver_ID = ? ORDER BY Year DESC, Month DESC');
            $stmt->execute([$staff['linked_driver_id']]);
            json_response($stmt->fetchAll());
        }
        // Driver Manager -> own depot only (via the driver)
        $stmt = $pdo->prepare('
            SELECT dss.* FROM Driver_Safety_Score dss
            JOIN Driver d ON d.Driver_ID = dss.Driver_ID
            WHERE d.Depot_ID = ?
            ORDER BY dss.Year DESC, dss.Month DESC
        ');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Driver_Safety_Score', 'write');
        $data = get_request_body();
        $required = ['driver_id', 'month', 'year', 'score'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if (!driver_in_own_depot_score($pdo, $data['driver_id'], $staff)) {
            json_fail(403, 'You can only add safety scores for drivers in your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Driver_Safety_Score (Driver_ID, Month, Year, Score)
            VALUES (?, ?, ?, ?)
        ', [
            $data['driver_id'],
            $data['month'],
            $data['year'],
            $data['score'],
        ], 'Safety score created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver_Safety_Score', 'action' => 'CREATE',
            'summary' => $data['driver_id'] . ' - ' . $data['month'] . '/' . $data['year'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Driver_Safety_Score', 'write');
        if (!isset($_GET['score_id'])) {
            json_response(['error' => 'Missing ?score_id= in URL'], 422);
        }
        if (!score_in_own_depot($pdo, $_GET['score_id'], $staff)) {
            json_fail(403, 'You can only edit safety scores in your own depot.');
        }
        $data = get_request_body();
        $required = ['driver_id', 'month', 'year', 'score'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if (!driver_in_own_depot_score($pdo, $data['driver_id'], $staff)) {
            json_fail(403, 'You can only assign safety scores to drivers in your own depot.');
        }

        run_write($pdo, '
            UPDATE Driver_Safety_Score
            SET Driver_ID = ?, Month = ?, Year = ?, Score = ?
            WHERE Score_ID = ?
        ', [
            $data['driver_id'],
            $data['month'],
            $data['year'],
            $data['score'],
            $_GET['score_id'],
        ], 'Safety score updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver_Safety_Score', 'action' => 'UPDATE',
            'summary' => $data['driver_id'] . ' - ' . $data['month'] . '/' . $data['year'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Driver_Safety_Score', 'write');
        if (!isset($_GET['score_id'])) {
            json_response(['error' => 'Missing ?score_id= in URL'], 422);
        }
        if (!score_in_own_depot($pdo, $_GET['score_id'], $staff)) {
            json_fail(403, 'You can only delete safety scores in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Driver_Safety_Score WHERE Score_ID = ?', [$_GET['score_id']], 'Safety score deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver_Safety_Score', 'action' => 'DELETE',
            'summary' => (string) $_GET['score_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function score_row_visible(PDO $pdo, array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Driver') return $row['Driver_ID'] === $staff['linked_driver_id'];
    return driver_in_own_depot_score($pdo, $row['Driver_ID'], $staff);
}

function score_in_own_depot(PDO $pdo, string $scoreId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Driver_ID FROM Driver_Safety_Score WHERE Score_ID = ?');
    $stmt->execute([$scoreId]);
    $driverId = $stmt->fetchColumn();
    return $driverId !== false && driver_in_own_depot_score($pdo, $driverId, $staff);
}

function driver_in_own_depot_score(PDO $pdo, string $driverId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
