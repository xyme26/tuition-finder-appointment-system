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

// Check if the appointment ID and cancellation reason are set
if (!isset($_POST['appointmentId']) || !isset($_POST['cancelReason'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Get the appointment ID and cancellation reason
$appointmentId = intval($_POST['appointmentId']); // Convert appointment ID to an integer
$cancelReason = $_POST['cancelReason']; // Get the cancellation reason

// Get the new date and time
$newDate = $_POST['newDate'] ?? null; // Use null if not set
$newTime = $_POST['newTime'] ?? null;

// Begin a transaction
$conn->begin_transaction();

try {
    // Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ? WHERE id = ?"); // SQL statement to update appointment
    $stmt->bind_param("si", $cancelReason, $appointmentId);
    $stmt->execute();

    // Get user_id for the appointment
    $stmt = $conn->prepare("SELECT user_id FROM appointments WHERE id = ?"); // SQL statement to get user ID
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $userId = $appointment['user_id'];

    // Create notification for the user
    $notificationMessage = "Your appointment has been cancelled by the admin. Reason: " . $cancelReason; // Prepare notification message
    if ($newDate && $newTime) {
        $newDateTime = $newDate . ' ' . $newTime;
        $stmt = $conn->prepare("UPDATE appointments SET appointment_datetime = ? WHERE id = ?"); // SQL statement to update appointment date/time
        $stmt->bind_param("si", $newDateTime, $appointmentId);
        $stmt->execute();
        $notificationMessage .= " A new date/time has been suggested: " . $newDate . " at " . $newTime; // Append new date/time to notification
    }

    // Create notification for the user
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)"); // SQL statement to insert notification
    $stmt->bind_param("is", $userId, $notificationMessage);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment: ' . $e->getMessage()]);
}

// Close the statement
$stmt->close();
// Close the connection
$conn->close();
?>
