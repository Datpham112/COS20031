<?php
/**
 * Backend/lib/api_helpers.php
 * ------------------------------------------------------------------
 * Small shared toolkit so every CRUD endpoint in Backend/api/ looks
 * the same and doesn't repeat itself. Include this AFTER config/database.php
 * and Backend/auth/auth_check.php.
 * ------------------------------------------------------------------
 */

/**
 * Reads the request body regardless of how the frontend sent it:
 * - JSON body (fetch with Content-Type: application/json)  -> most common
 * - classic HTML <form> POST (application/x-www-form-urlencoded)
 * Returns an associative array either way.
 */
function get_request_body(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);

    if (is_array($json)) {
        return $json;
    }

    // Fall back to normal $_POST for classic <form method="post"> submissions
    return $_POST;
}

/**
 * Sends a JSON response and stops execution.
 */
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Checks that every field in $required is present and non-empty in $data.
 * Returns an array of missing field names (empty array = all good).
 */
function missing_fields(array $data, array $required): array
{
    $missing = [];
    foreach ($required as $field) {
        if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Writes one row into Audit_Log. $audit must have keys:
 * staff_id, table, action ('CREATE'|'UPDATE'|'DELETE'), summary.
 * Failures here are logged but never block the actual request.
 */
function write_audit_log(PDO $pdo, array $audit): void
{
    try {
        $stmt = $pdo->prepare('
            INSERT INTO Audit_Log (Staff_ID, Table_Name, Action_Type, Record_Summary)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$audit['staff_id'], $audit['table'], $audit['action'], $audit['summary'] ?? null]);
    } catch (PDOException $e) {
        error_log('Audit log write failed: ' . $e->getMessage());
    }
}

/**
 * Wraps a single PDO write (INSERT/UPDATE/DELETE) so every endpoint
 * reports database errors (CHECK/FK/UNIQUE violations) the same
 * friendly way instead of a 500 crash, and optionally records an
 * Audit_Log entry when $audit is provided.
 */
function run_write(PDO $pdo, string $sql, array $params, string $successMessage, int $successCode = 200, ?array $audit = null): void
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($audit !== null) {
            write_audit_log($pdo, $audit);
        }

        json_response(['message' => $successMessage], $successCode);
    } catch (PDOException $e) {
        // 23000 = integrity constraint violation (FK, UNIQUE, CHECK, NOT NULL)
        $isConstraint = $e->getCode() === '23000';
        json_response([
            'error'  => $isConstraint ? 'Constraint violation - check the values you entered' : 'Database error',
            'detail' => $e->getMessage(),
        ], $isConstraint ? 409 : 500);
    }
}

/**
 * Same idea as run_write(), but for operations that need MULTIPLE
 * statements to succeed or fail together inside one transaction
 * (e.g. creating a Driver row + its linked Staff login account).
 *
 * $work receives the PDO connection and should run its own
 * prepare()/execute() calls. Throw to abort - everything rolls back.
 */
function run_multi_write(PDO $pdo, callable $work, string $successMessage, int $successCode = 200, ?array $audit = null): void
{
    try {
        $pdo->beginTransaction();
        $work($pdo);

        if ($audit !== null) {
            write_audit_log($pdo, $audit);
        }

        $pdo->commit();
        json_response(['message' => $successMessage], $successCode);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $isConstraint = $e->getCode() === '23000';
        json_response([
            'error'  => $isConstraint ? 'Constraint violation - check the values you entered' : 'Database error',
            'detail' => $e->getMessage(),
        ], $isConstraint ? 409 : 500);
    }
}
