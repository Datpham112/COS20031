<?php

session_start();

require "../config/database.php";

// Get the submitted username and password
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Look up the user by username
$sql = "SELECT * FROM users WHERE username = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 1) {

    $user = $result->fetch_assoc();

    // Compare plain text passwords
    if ($password === $user['password']) {

        // Store user information in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['depot'] = $user['depot'];

        // Redirect to dashboard
        header("Location: ../dashboard.php");
        exit();

    } else {

        // Incorrect password
        echo "<script>
                alert('Incorrect password.');
                window.location='../login.html';
              </script>";
        exit();

    }

} else {

    // Username not found
    echo "<script>
            alert('Username not found.');
            window.location='../login.html';
          </script>";
    exit();

}

$stmt->close();
$conn->close();

?>
