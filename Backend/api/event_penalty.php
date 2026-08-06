<?php
/**
 * Backend/api/event_penalty.php
 * ------------------------------------------------------------------
 *   GET event_penalty.php -> list of { Event_Type, Penalty_Points }
 *
 * Read-only reference data (which event types exist and how many
 * points each costs). Not sensitive, so any logged-in staff member
 * can read it -- this just powers the Event Type dropdown on the
 * Safety Event form so entries always match a real penalty category
 * (free-text event types silently fail to match in
 * UpdateMonthlySafetyScores() / Calculate_Monthly_Driver_Safety_Score,
 * since the join is on an exact Event_Type string).
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

$pdo = get_db_connection();
json_response($pdo->query('SELECT Event_Type, Penalty_Points FROM Event_Penalty ORDER BY Event_Type')->fetchAll());
