<?php
/**
 * Backend/api/recalculate_safety_scores.php
 * ------------------------------------------------------------------
 *   POST recalculate_safety_scores.php -> runs Procedure/Update_Monthly_Safety_Scores.sql
 *
 * Driver_Safety_Score is a derived table, not something entered by
 * hand: UpdateMonthlySafetyScores() wipes it and rebuilds it from
 * Safety_Event joined to Event_Penalty (100 minus that month's total
 * penalty points, floored at 0). Call this after logging new Safety
 * Events so the Safety Command Center / driver scores reflect them.
 *
 * Permissions: same as Driver_Safety_Score 'write' -- Driver Manager
 * (Head Manager is read-only everywhere, so deliberately excluded here
 * even though it can read the table).
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$staff = require_role(['Driver Manager']);
$pdo = get_db_connection();

try {
    $pdo->exec('CALL UpdateMonthlySafetyScores()');
    write_audit_log($pdo, [
        'staff_id' => $staff['staff_id'], 'table' => 'Driver_Safety_Score', 'action' => 'UPDATE',
        'summary' => 'Recalculated all monthly safety scores',
    ]);
    json_response(['message' => 'Safety scores recalculated']);
} catch (PDOException $e) {
    json_response(['error' => 'Recalculation failed', 'detail' => $e->getMessage()], 500);
}
