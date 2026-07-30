<?php
/**
 * Backend/auth/logout.php
 * ------------------------------------------------------------------
 * Destroys the session, then shows the existing logout.html page.
 * Link to THIS file from the sidebar (not directly to logout.html),
 * otherwise the session is never actually cleared.
 * ------------------------------------------------------------------
 */

session_start();
$_SESSION = [];
session_destroy();

header('Location: ../../Frontend/logout.html');
exit;
