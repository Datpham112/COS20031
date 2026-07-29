<?php
/**
 * Backend/lib/api_helpers.php
 * ------------------------------------------------------------------
 * Small shared toolkit so every CRUD endpoint in Backend/api/ looks
 * the same and doesn't repeat itself. Include this AFTER config/database.php.
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
 * Wraps a PDO write (INSERT/UPDATE/DELETE) so every endpoint reports
 * database errors (e.g. CHECK constraint violations, FK violations,
 * duplicate unique keys) the same friendly way instead of a 500 crash.
 */
function run_write(PDO $pdo, string $sql, array $params, string $successMessage, int $successCode = 200): void
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
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
