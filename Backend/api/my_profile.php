<?php
/**
 * GET /Backend/api/my_profile.php
 * ------------------------------------------------------------------
 * For a logged-in Driver account: bundles everything on the "Driver"
 * row of the permissions matrix (View personal profile, vehicle
 * assignments, certifications, safety score and incident history)
 * into one response, always scoped to THEIR OWN Driver_ID -- never
 * takes a driver_id from the request, so there's no way to fetch
 * someone else's data through this endpoint.
 *
 * Response:
 * {
 *   "profile": {...Driver row...},
 *   "vehicleAssignments": [...],
 *   "certifications": [...],
 *   "safetyScores": [...],
 *   "incidents": [...Safety_Event rows...]
 * }
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$staff = require_login();

if ($staff['role_type'] !== 'Driver') {
    json_fail(403, 'This page is for Driver accounts only.');
}
if (empty($staff['linked_driver_id'])) {
    json_fail(409, 'Your account is not linked to a Driver record yet. Ask a Driver Manager to set this up.');
}

$driverId = $staff['linked_driver_id'];
$pdo = get_db_connection();

$profileStmt = $pdo->prepare('SELECT * FROM Driver WHERE Driver_ID = ?');
$profileStmt->execute([$driverId]);
$profile = $profileStmt->fetch();

$assignStmt = $pdo->prepare('
    SELECT vda.*, v.Registration_Number, v.Vehicle_Category
    FROM Vehicle_Driver_Assignment vda
    JOIN Vehicle v ON v.Vin = vda.VIN
    WHERE vda.Driver_ID = ?
    ORDER BY vda.Start_Date DESC
');
$assignStmt->execute([$driverId]);

$certStmt = $pdo->prepare('SELECT * FROM Driver_Certification WHERE Driver_ID = ? ORDER BY Expiry_Date');
$certStmt->execute([$driverId]);

$scoreStmt = $pdo->prepare('SELECT * FROM Driver_Safety_Score WHERE Driver_ID = ? ORDER BY Year DESC, Month DESC');
$scoreStmt->execute([$driverId]);

$eventStmt = $pdo->prepare('
    SELECT se.*, v.Registration_Number
    FROM Safety_Event se
    JOIN Vehicle v ON v.Vin = se.VIN
    WHERE se.Driver_ID = ?
    ORDER BY se.Timestamp DESC
');
$eventStmt->execute([$driverId]);

json_response([
    'profile'            => $profile ?: null,
    'vehicleAssignments' => $assignStmt->fetchAll(),
    'certifications'     => $certStmt->fetchAll(),
    'safetyScores'       => $scoreStmt->fetchAll(),
    'incidents'          => $eventStmt->fetchAll(),
]);
