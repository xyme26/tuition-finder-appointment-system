<?php
// Establish a connection to the MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tuition_finder_db";

// Create a new MySQLi object
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection failed and display an error message
if ($conn->connect_error) {
    // Log the error and display a generic message
    error_log("Database Connection Failed: " . $conn->connect_error);
    // Return a generic error message
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}
?>
