<?php
/**
 * database.php
 * ------------------------------------------------------------------
 * Single shared PDO connection for every API endpoint in Backend/api/.
 *
 * >>> EDIT THE 4 CONSTANTS BELOW TO MATCH YOUR OWN MYSQL SERVER <<<
 * ------------------------------------------------------------------
 */

define('DB_HOST', '127.0.0.1');      // e.g. '127.0.0.1' or 'localhost'
define('DB_PORT', '3306');
define('DB_NAME', 'COS20031'); // the schema you ran the DDL/DML into
define('DB_USER', 'root');           // your MySQL username
define('DB_PASS', '');               // your MySQL password

/**
 * Opens (or reuses) the shared PDO connection. Throws PDOException on
 * failure instead of printing anything -- used by callers that need to
 * show their own error UI (e.g. the login page) rather than a raw JSON
 * blob.
 */
function open_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/**
 * Returns a shared PDO instance. Dies with a clean JSON error instead of
 * leaking credentials/stack traces if the connection fails. Use this
 * from Backend/api/*.php (they always return JSON anyway).
 */
function get_db_connection(): PDO
{
    try {
        return open_db_connection();
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error'   => 'Database connection failed',
            // Keep this detail while developing locally; remove/hide it
            // once the project goes anywhere near production.
            'detail'  => $e->getMessage(),
        ]);
        exit;
    }
}
