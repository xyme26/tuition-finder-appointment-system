<?php
// admin_logout.php
session_start();
// Unset all session variables
session_unset();
// Destroy the session
session_destroy();

// Clear "Remember Me" cookie if exists
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/"); // Expire cookie
}

// Redirect to the login page
header("Location: login_admin.php?logged_out=1");
exit();
?>
