<?php
// logout.php
// Start the session
session_start();
// Unset all session variables
session_unset();
// Destroy the session
session_destroy();

// Clear "Remember Me" cookie if exists
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/"); // Expire cookie
}

// Redirect to login page
header("Location: login.php");
// Stop execution
exit();
?>
