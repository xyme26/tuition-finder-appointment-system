<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Check if the appointment ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No appointment ID provided']);
    exit;
}

// Get the appointment ID from the query parameter
$appointment_id = intval($_GET['id']);

// Fetch the cancellation reason without checking session
$stmt = $conn->prepare("SELECT cancellation_reason FROM appointments WHERE id = ?");
// Bind the appointment ID to the statement
$stmt->bind_param("i", $appointment_id);
// Execute the statement
$stmt->execute();
// Get the result
$result = $stmt->get_result();

// Fetch the result
if ($row = $result->fetch_assoc()) {
    // Return a success response with the cancellation reason
    echo json_encode([
        'success' => true,
        'reason' => $row['cancellation_reason'] ?: 'No reason provided'
    ]);
} else {
    // Return an error message if the appointment is not found
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
