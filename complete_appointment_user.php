<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json'); // Set the content type to JSON

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the appointment ID and user ID
    $appointmentId = $_POST['appointment_id']; // Get from the POST request
    $userId = $_SESSION['user_id']; // Get user ID from the session

    // Start transaction
    $conn->begin_transaction();

    try {
        // First check if the appointment exists and is eligible for completion
        $checkQuery = "SELECT id, status 
                      FROM appointments 
                      WHERE id = ? 
                      AND user_id = ? 
                      AND status NOT IN ('completed', 'cancelled')"; // SQL query to check appointment status

        // Prepare the statement    
        $checkStmt = $conn->prepare($checkQuery);
        // Bind the parameters
        $checkStmt->bind_param("ii", $appointmentId, $userId);
        // Execute the statement
        $checkStmt->execute();
        // Get the result
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Appointment not found or already completed/cancelled');
        }

        // Update appointment status to completed
        $updateQuery = "UPDATE appointments 
                       SET status = 'completed',
                           completed_at = NOW() 
                       WHERE id = ? 
                       AND user_id = ? 
                       AND status NOT IN ('completed', 'cancelled')"; // SQL query to update appointment status
        
        // Prepare the statement    
        $updateStmt = $conn->prepare($updateQuery);
        // Bind the parameters
        $updateStmt->bind_param("ii", $appointmentId, $userId);
        // Execute the statement
        $updateStmt->execute();

        if ($updateStmt->affected_rows === 0) {
            throw new Exception('Failed to update appointment status'); // Throw an error if update fails
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    // Close statements
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
}

// Close the connection
$conn->close();
?>

