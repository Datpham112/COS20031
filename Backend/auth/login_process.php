<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    redirect_with_error('Please enter both username and password.');
}

try {
    $pdo = open_db_connection();
} catch (PDOException $e) {

    redirect_with_error('Database connection failed: ' . $e->getMessage());
}

$stmt = $pdo->prepare('SELECT * FROM Staff WHERE Username = ?');
$stmt->execute([$username]);
$staff = $stmt->fetch();

if (!$staff) {
    redirect_with_error('Username not found.');
}

if ($staff['Password_Hash'] === null || !password_verify($password, $staff['Password_Hash'])) {
    redirect_with_error('Incorrect password.');
}


$_SESSION['staff_id']         = $staff['Staff_ID'];
$_SESSION['full_name']        = $staff['Full_Name'];
$_SESSION['role_type']        = $staff['Role_Type'];
$_SESSION['depot_id']         = $staff['Depot_ID'];
$_SESSION['linked_driver_id']   = $staff['Linked_Driver_ID'] ?? null;
$_SESSION['linked_mechanic_id'] = $staff['Linked_Mechanic_ID'] ?? null;

$landing = 'dashboard.html';
if ($staff['Role_Type'] === 'Driver') {
    $landing = 'driver.html';
} elseif ($staff['Role_Type'] === 'Mechanic') {
    $landing = 'mechanic.html';
} elseif ($staff['Role_Type'] === 'Driver Manager') {
    $landing = 'manage_data.html';
} elseif ($staff['Role_Type'] === 'Inventory Manager') {
    $landing = 'inventory.html';

}


header('Location: ../../Frontend/' . $landing);
exit;

function redirect_with_error(string $message): void
{
    header('Location: ../../Frontend/login.html?error=' . urlencode($message));
    exit;
}
