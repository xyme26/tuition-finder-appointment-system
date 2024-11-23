<?php
session_start();
require_once 'connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id']; // Get the appointment ID from the POST request
    
    try {
        // Check current status first
        $checkStmt = $conn->prepare("SELECT status FROM appointments WHERE id = ?");
        $checkStmt->bind_param("i", $appointmentId);
        $checkStmt->execute();
        $result = $checkStmt->get_result(); // Get the result set
        $appointment = $result->fetch_assoc(); // Fetch the appointment data

        // Check if the appointment is already completed
        if ($appointment['status'] === 'completed') {
            echo json_encode(['success' => false, 'message' => 'Appointment is already completed']);
            exit;
        }

        // Update appointment status to completed
        $updateStmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND status NOT IN ('completed', 'cancelled')"); // Prepare SQL statement to update appointment status
        $updateStmt->bind_param("i", $appointmentId);
        $updateStmt->execute();

        // Check if the appointment was updated
        if ($updateStmt->affected_rows > 0) {
            // Get user_id for notification
            $userStmt = $conn->prepare("SELECT user_id, appointment_datetime FROM appointments WHERE id = ?");
            $userStmt->bind_param("i", $appointmentId);
            $userStmt->execute();
            $result = $userStmt->get_result();
            $appointment = $result->fetch_assoc();

            // Create notification
            if ($appointment) {
                $notificationMsg = "Your appointment on " . date('Y-m-d H:i', strtotime($appointment['appointment_datetime'])) . " has been marked as completed."; // Prepare notification message
                $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())"); // Prepare SQL statement to insert notification
                $notifyStmt->bind_param("is", $appointment['user_id'], $notificationMsg);
                $notifyStmt->execute();
            }

            // Return success
            echo json_encode(['success' => true]);
        } else {
            // Return error if no appointment was updated
            echo json_encode(['success' => false, 'message' => 'No appointment was updated']);
        }

    } catch (Exception $e) {
        // Return error if there is an exception
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    // Close all statements
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($userStmt)) $userStmt->close();
    if (isset($notifyStmt)) $notifyStmt->close();
}

// Close the connection
$conn->close();
?>
