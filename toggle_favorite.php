<?php
session_start();
include 'connection.php';

// Check if the user is logged in and has a user_id, then display an error message if not
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get the user_id from the session
$user_id = $_SESSION['user_id'];
// Get the center_id from the POST request
$center_id = $_POST['center_id'];

// Check if the favorite already exists in the database
$check_stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
$check_stmt->bind_param("ii", $user_id, $center_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

// If the favorite exists, remove it
if ($result->num_rows > 0) {
    // Prepare the statement for deleting the favorite
    $delete_stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
    // Bind the parameters to the statement
    $delete_stmt->bind_param("ii", $user_id, $center_id);
    // Execute the statement and check if it was successful
    $success = $delete_stmt->execute();
    // Set the action to removed
    $action = 'removed';
} else {
    // Favorite doesn't exist, so add it
    $insert_stmt = $conn->prepare("INSERT INTO favorites (user_id, tuition_center_id) VALUES (?, ?)");
    // Bind the parameters to the statement
    $insert_stmt->bind_param("ii", $user_id, $center_id);
    // Execute the statement and check if it was successful
    $success = $insert_stmt->execute();
    // Set the action to added
    $action = 'added';
}

// If the favorite was successfully added or removed, display a success message
if ($success) {
    // Display a success message with the action
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    // Display an error message if the database error
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

// Close the connection
$conn->close();
?>