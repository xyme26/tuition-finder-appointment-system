<?php
session_start(); // Start the session to access session variables
include 'connection.php'; // Include your database connection

// Check if user is logged in and has a user_id, then display an error message if not
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Add error logging
error_log('Received review data: ' . print_r($data, true));

// Check if the data exists and is valid
if (isset($data['tuition_center_id'], $data['rating'], $data['comment'])) {
    $user_id = $_SESSION['user_id'];
    $tuition_center_id = $data['tuition_center_id'];
    $rating = intval($data['rating']);
    $comment = $data['comment'];

    try {
        // Check if user has already reviewed
        $check_sql = "SELECT id FROM reviews WHERE user_id = ? AND tuition_center_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $tuition_center_id);
        $check_stmt->execute();
        
        // Check if the user has already submitted a review for the tuition center
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode([
                // Display an error message if the user has already submitted a review
                'success' => false, 
                'message' => 'You have already submitted a review for this tuition center'
            ]);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Prepare the statement for inserting a new review
        $insert_sql = "INSERT INTO reviews (user_id, tuition_center_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiis", $user_id, $tuition_center_id, $rating, $comment);
        
        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            echo json_encode([
                // Display a success message if the review submission is successful
                'success' => true, 
                'message' => 'Review submitted successfully'
            ]);
        } else {
            // Display an error message if the review submission fails
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to submit review'
            ]);
        }
        $stmt->close();

    } catch (Exception $e) {
        // Log the exception message
        error_log('Exception: ' . $e->getMessage());
        // Display an error message if there is a server error
        echo json_encode([
            'success' => false, 
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    // Display an error message if the required data is missing
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required data'
    ]);
}

// Close the connection
$conn->close();
?>
