<?php
session_start();
require_once 'connection.php';

// Set the content type to JSON (JavaScript Object Notation)
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the appointment ID
    $appointmentId = $_POST['appointment_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Delete the appointment
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Commit transaction
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No appointment found with this ID');
        }

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    // Close statement
    if (isset($stmt)) $stmt->close();
}

// Close the connection
$conn->close();
?> 