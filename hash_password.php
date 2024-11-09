<?php
// Define the password you want to hash
$password = "testing"; // Replace with the actual password

// Hash the password using PASSWORD_DEFAULT
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Output the hashed password
echo "Hashed Password: " . $hashed_password;
?>
