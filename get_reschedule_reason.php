<?php
session_start();
include 'connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the admin is logged in
if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'message' => 'Admin not logged in']);
    exit;
}

// Check if the appointment ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Get the appointment ID from the query parameter
$appointmentId = intval($_GET['id']);

// Prepare the statement
$stmt = $conn->prepare("SELECT reschedule_reason FROM appointments WHERE id = ?");
// Bind the appointment ID to the statement (Bind is used to bind the parameters to the statement)
$stmt->bind_param("i", $appointmentId);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Fetch the result
if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'reason' => $row['reschedule_reason']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
