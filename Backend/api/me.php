<?php
/**
 * GET /Backend/api/me.php
 * ------------------------------------------------------------------
 * Tells the frontend who is currently logged in (if anyone), so pages
 * can show/hide sidebar items and filter data by role/depot without
 * needing a full page reload per role.
 *
 * Response when logged in (200):
 *   { "loggedIn": true, "staffId": "S-001", "fullName": "...",
 *     "roleType": "Depot Manager", "depotId": 2, "linkedDriverId": null,
 *     "isManagement": true, "isField": false }
 *
 * Response when not logged in (200 -- this is a normal, expected
 * state, not an error):
 *   { "loggedIn": false }
 * ------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../auth/auth_check.php';

$staff = current_staff();

if ($staff === null) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

echo json_encode([
    'loggedIn'       => true,
    'staffId'        => $staff['staff_id'],
    'fullName'       => $staff['full_name'],
    'roleType'       => $staff['role_type'],
    'depotId'        => $staff['depot_id'],
    'linkedDriverId' => $staff['linked_driver_id'],
    'linkedMechanicId' => $staff['linked_mechanic_id'],
    'isManagement'   => in_array($staff['role_type'], MANAGEMENT_ROLES, true),
    'isField'        => in_array($staff['role_type'], FIELD_ROLES, true),
]);
