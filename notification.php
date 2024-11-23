<!-- This file handles the notification settings for the user -->
<!-- The purpose of this file is to update the notification settings for the user in the database -->
<?php
// Start session
session_start();

// Include your database connection file
include('connection.php');

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login or show an error
    die("User not logged in.");
}

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

// If form is submitted to update notification settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $fav_update = isset($_POST['fav_update']) ? 1 : 0;
    $upcoming_appointment = isset($_POST['upcoming_appointment']) ? 1 : 0;
    $appointment_confirmation = isset($_POST['appointment_confirmation']) ? 1 : 0;
    $subscribe_all = isset($_POST['subscribe_all']) ? 1 : 0;

    // Prepare the SQL statement
    $update_query = "UPDATE notifications SET fav_update=?, upcoming_appointment=?, appointment_confirmation=?, subscribe_all=? WHERE user_id=?";

    // Initialize prepared statement
    if ($stmt = mysqli_prepare($conn, $update_query)) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "iiiii", $fav_update, $upcoming_appointment, $appointment_confirmation, $subscribe_all, $user_id);
        
        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect or set a success message
            echo "Notification settings updated";
        } else {
            // Handle error (consider logging instead of echoing directly)
            echo "Error updating settings: " . mysqli_stmt_error($stmt);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Handle error in preparing the statement
        echo "Error preparing the statement: " . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>
