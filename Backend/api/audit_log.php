<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

require_table_permission('Audit_Log', 'read');

$pdo = get_db_connection();

$sql = '
    SELECT al.Log_ID, al.Staff_ID, s.Full_Name, s.Role_Type, al.Table_Name,
           al.Action_Type, al.Record_Summary, al.Created_At
    FROM Audit_Log al
    JOIN Staff s ON s.Staff_ID = al.Staff_ID
    WHERE 1=1
';
$params = [];

if (!empty($_GET['table'])) {
    $sql .= ' AND al.Table_Name = ?';
    $params[] = $_GET['table'];
}
if (!empty($_GET['staff_id'])) {
    $sql .= ' AND al.Staff_ID = ?';
    $params[] = $_GET['staff_id'];
}
if (!empty($_GET['action'])) {
    $sql .= ' AND al.Action_Type = ?';
    $params[] = $_GET['action'];
}

$sql .= ' ORDER BY al.Created_At DESC LIMIT 500';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
json_response($stmt->fetchAll());
