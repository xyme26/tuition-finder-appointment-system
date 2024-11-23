<?php
// This script updates the user's latitude and longitude in the session and database based on the provided POST data.

session_start();
include 'connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get latitude and longitude from the POST data
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null; // Get latitude
    $lon = isset($_POST['lon']) ? floatval($_POST['lon']) : null; // Get longitude

    // Validate the received data
    if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon)) {
        // Store the location in the session
        $_SESSION['user_lat'] = $lat;
        $_SESSION['user_lon'] = $lon;

        // Update the user's location in the database (assuming there's a users table)
        if (isset($_SESSION['user_id'])) {
            // Get the user ID from the session
            $user_id = $_SESSION['user_id'];
            // Prepare the SQL statement
            $stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
            // Bind the parameters to the statement
            $stmt->bind_param("ddi", $lat, $lon, $user_id);
            // Execute the statement
            $stmt->execute();
            // Close the statement
            $stmt->close();
        }

        // Log the updated location for debugging
        error_log("User location updated: Lat: $lat, Lon: $lon");

        // Send a success response
        echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
    } else {
        // Send an error response if the data is invalid
        echo json_encode(['success' => false, 'error' => 'Invalid location data']);
    }
} else {
    // Send an error response for invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>