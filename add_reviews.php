<?php
session_start();
include 'connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in to submit a review.";
        exit;
    }

    // Get user ID
    $user_id = $_SESSION['user_id'];
    // Get center ID from POST data
    $tuition_center_id = $_POST['tuition_center_id'];
    // Get rating from POST data
    $rating = $_POST['rating'];
    // Get review text from POST data
    $review_text = $_POST['review_text'];

    // Prepare and execute SQL statement to insert review
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, tuition_center_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $tuition_center_id, $rating, $review_text); // Bind parameters (user_id, tuition_center_id, rating, review_text)

    // Check if insertion was successful
    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}
?>
