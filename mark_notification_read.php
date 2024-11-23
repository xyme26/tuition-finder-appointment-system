<!-- This file marks a notification as read -->
<?php
session_start();
include 'connection.php';

// Check if the user is logged in and the notification ID is provided
if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    // Return an error response if the request is invalid
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
// Get the notification ID from the POST data
$notification_id = intval($_POST['notification_id']);

// Prepare and execute the SQL query to mark the notification as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);
$stmt->execute();

// Check if the query affected any rows
if ($stmt->affected_rows > 0) {
    // Return a success response
    echo json_encode(['success' => true]);
} else {
    // Return an error response
    echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>