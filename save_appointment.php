<?php
// save_appointment.php

include 'connection.php';

// Start the session
session_start();

// Get data from the AJAX request
$selectedDate = $_POST['date'];
// Get the available time from the POST data
$availableTime = $_POST['time'];
// Get the user ID from the session
$userId = $_SESSION['user_id'];
// Get the tuition center ID from the POST data
$tuitionCenterId = $_POST['tuition_center_id'];

// Combine date and time
$appointmentDateTime = $selectedDate . ' ' . $availableTime;

// Prepare the SQL statement
$sql = "INSERT INTO appointments (appointment_datetime, status, user_id, tuition_center_id) VALUES (?, 'pending', ?, ?)";
$stmt = $conn->prepare($sql);

// Bind the parameters to the statement
$stmt->bind_param("sii", $appointmentDateTime, $userId, $tuitionCenterId);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
