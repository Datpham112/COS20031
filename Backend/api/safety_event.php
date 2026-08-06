<?php
/**
 * Backend/api/safety_event.php
 * ------------------------------------------------------------------
 *   GET    safety_event.php                    -> list (scoped by role)
 *   GET    safety_event.php?event_id=XXX        -> one record
 *   POST   safety_event.php                     -> create
 *   PUT    safety_event.php?event_id=XXX         -> update
 *   DELETE safety_event.php?event_id=XXX         -> delete
 *
 * Required fields for POST: driver_id, vin, timestamp, event_type,
 * severity_level, odometer_at_event
 * (Depot_ID is filled in automatically from the driver's own depot --
 * not accepted from the frontend, so a Driver Manager can't log an
 * event against another depot. Event_ID is AUTO_INCREMENT, don't send it.)
 *
 * Permissions (kept in sync with Backend/auth/auth_check.php ->
 * TABLE_PERMISSIONS['Safety_Event']):
 *   Read:  Head Manager (all), Driver Manager (own depot), Driver (own record only)
 *   Write: Driver Manager (own depot only) -- the driver and vehicle
 *          must both belong to the manager's own depot.
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Safety_Event', 'read');

        if (isset($_GET['event_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Safety_Event WHERE Event_ID = ?');
            $stmt->execute([$_GET['event_id']]);
            $row = $stmt->fetch();
            if ($row && !event_row_visible($row, $staff)) {
                json_response(['error' => 'Safety event not found'], 404);
            }
            json_response($row ?: ['error' => 'Safety event not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Safety_Event ORDER BY Timestamp DESC')->fetchAll());
        }
        if ($staff['role_type'] === 'Driver') {
            $stmt = $pdo->prepare('SELECT * FROM Safety_Event WHERE Driver_ID = ? ORDER BY Timestamp DESC');
            $stmt->execute([$staff['linked_driver_id']]);
            json_response($stmt->fetchAll());
        }
        // Driver Manager -> own depot only
        $stmt = $pdo->prepare('SELECT * FROM Safety_Event WHERE Depot_ID = ? ORDER BY Timestamp DESC');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Safety_Event', 'write');
        $data = get_request_body();
        $required = ['driver_id', 'vin', 'timestamp', 'event_type', 'severity_level', 'odometer_at_event'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        $depotId = driver_depot_se($pdo, $data['driver_id']);
        if ($depotId === null || (int) $depotId !== (int) $staff['depot_id']) {
            json_fail(403, 'You can only log safety events for drivers in your own depot.');
        }
        if (!vehicle_in_own_depot_se($pdo, $data['vin'], $staff)) {
            json_fail(403, 'You can only log safety events for vehicles in your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Safety_Event (Driver_ID, VIN, Depot_ID, Timestamp, Event_Type, Severity_Level, Odometer_At_Event, Review_Comments)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['driver_id'],
            $data['vin'],
            $depotId,
            $data['timestamp'],
            $data['event_type'],
            $data['severity_level'],
            $data['odometer_at_event'],
            $data['review_comments'] ?? null,
        ], 'Safety event created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Safety_Event', 'action' => 'CREATE',
            'summary' => $data['driver_id'] . ' - ' . $data['event_type'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Safety_Event', 'write');
        if (!isset($_GET['event_id'])) {
            json_response(['error' => 'Missing ?event_id= in URL'], 422);
        }
        if (!event_in_own_depot($pdo, $_GET['event_id'], $staff)) {
            json_fail(403, 'You can only edit safety events in your own depot.');
        }
        $data = get_request_body();
        $required = ['driver_id', 'vin', 'timestamp', 'event_type', 'severity_level', 'odometer_at_event'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        $depotId = driver_depot_se($pdo, $data['driver_id']);
        if ($depotId === null || (int) $depotId !== (int) $staff['depot_id']) {
            json_fail(403, 'You can only assign safety events to drivers in your own depot.');
        }
        if (!vehicle_in_own_depot_se($pdo, $data['vin'], $staff)) {
            json_fail(403, 'You can only log safety events for vehicles in your own depot.');
        }

        run_write($pdo, '
            UPDATE Safety_Event
            SET Driver_ID = ?, VIN = ?, Depot_ID = ?, Timestamp = ?, Event_Type = ?, Severity_Level = ?, Odometer_At_Event = ?, Review_Comments = ?
            WHERE Event_ID = ?
        ', [
            $data['driver_id'],
            $data['vin'],
            $depotId,
            $data['timestamp'],
            $data['event_type'],
            $data['severity_level'],
            $data['odometer_at_event'],
            $data['review_comments'] ?? null,
            $_GET['event_id'],
        ], 'Safety event updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Safety_Event', 'action' => 'UPDATE',
            'summary' => $data['driver_id'] . ' - ' . $data['event_type'],
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Safety_Event', 'write');
        if (!isset($_GET['event_id'])) {
            json_response(['error' => 'Missing ?event_id= in URL'], 422);
        }
        if (!event_in_own_depot($pdo, $_GET['event_id'], $staff)) {
            json_fail(403, 'You can only delete safety events in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Safety_Event WHERE Event_ID = ?', [$_GET['event_id']], 'Safety event deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Safety_Event', 'action' => 'DELETE',
            'summary' => (string) $_GET['event_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function event_row_visible(array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Driver') return $row['Driver_ID'] === $staff['linked_driver_id'];
    return (int) $row['Depot_ID'] === (int) $staff['depot_id'];
}

function event_in_own_depot(PDO $pdo, string $eventId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Safety_Event WHERE Event_ID = ?');
    $stmt->execute([$eventId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}

function driver_depot_se(PDO $pdo, string $driverId): ?int
{
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false ? (int) $depotId : null;
}

function vehicle_in_own_depot_se(PDO $pdo, string $vin, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Vehicle WHERE Vin = ?');
    $stmt->execute([$vin]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}
