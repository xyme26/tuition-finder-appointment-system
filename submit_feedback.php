<?php
session_start();
include 'connection.php';

// Get the data from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Allow null user_id for non-logged in users
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get the rating and comment from the data
$rating = $data['rating'];
$comment = $data['comment'];

// Prepare the statement for inserting feedback
$stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $rating, $comment);

// Execute the statement and check if it was successful
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>