<?php
session_start();
include 'connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if the appointment ID and cancellation reason are set
if (!isset($_POST['appointmentId']) || !isset($_POST['reasons'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Get the appointment ID and cancellation reason
$appointmentId = intval($_POST['appointmentId']); // Convert appointment ID to an integer
$reasons = implode(', ', $_POST['reasons']); // Convert reasons array to a comma-separated string

// Update the appointment status instead of deleting
$stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $reasons, $appointmentId, $_SESSION['user_id']);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
}

// Close the statement
$stmt->close();
// Close the connection
$conn->close();
?>
