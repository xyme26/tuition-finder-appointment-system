<?php
session_start();
include 'connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Prepare the statement
$stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize the notifications array
$notifications = [];

// Fetch the results
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return the notifications as JSON
echo json_encode($notifications);

// Close the statement and connection
$stmt->close();
$conn->close();
?>