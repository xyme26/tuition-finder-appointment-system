<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Get center ID from POST data
$center_id = $_POST['center_id'];

// Prepare and execute SQL statement to insert favorite
$stmt = $conn->prepare("INSERT INTO favorites (user_id, tuition_center_id) VALUES (?, ?)"); //SQL query to insert favorite
$stmt->bind_param("ii", $user_id, $center_id); 

// Check if insertion was successful
if ($stmt->execute()) {
    echo json_encode(['success' => true]); //Return success
} else {
    echo json_encode(['error' => 'Failed to add favorite']); //Return error
}

// Close the statement and connection
$stmt->close(); 
$conn->close();
