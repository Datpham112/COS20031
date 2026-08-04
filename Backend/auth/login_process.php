<?php
/**
 * Backend/auth/login_process.php
 * ------------------------------------------------------------------
 * Handles the POST from Frontend/login.html.
 *
 * Rewritten from the original version, which had two bugs:
 *  1. Used mysqli ($conn->prepare) but config/database.php only
 *     provides a PDO connection (get_db_connection()) -- $conn never
 *     existed, so every login attempt fatally errored.
 *  2. Queried a `users` table with plaintext passwords -- the real
 *     table is `Staff`, with `Password_Hash` (bcrypt via
 *     password_hash()), not a `users` table with plaintext.
 * ------------------------------------------------------------------
 */

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
    // Show the real reason on the login page instead of a raw JSON
    // error page -- this is almost always a local setup problem
    // (MySQL not running, wrong DB_NAME/DB_USER/DB_PASS in
    // Backend/config/database.php, or the schema/tables not imported
    // yet), not a bug in the login form itself.
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

// Login OK -- store only what the frontend needs to know "who is this
// and what can they see". Never store the password hash in session.
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
    $landing = 'workload.html';
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
