<?php

define('DB_HOST', '127.0.0.1');      
define('DB_PORT', '3306');
define('DB_NAME', 'fleet_management'); 
define('DB_USER', 'root');        
define('DB_PASS', '');             


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
