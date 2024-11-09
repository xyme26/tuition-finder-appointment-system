<?php
// get_available_slots.php

// Include the database connection
include 'connection.php';

// Get the selected date from the frontend
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['date'])) {
    echo json_encode(['success' => false, 'message' => 'Date is required.']);
    exit;
}

$date = $input['date'];

// Fetch all available time slots for the selected date (assuming slots table exists)
$sql = "SELECT time FROM available_slots WHERE date = ? AND status = 'available'";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);
// Bind the date to the statement
$stmt->bind_param("s", $date);
// Execute the statement
$stmt->execute();
// Get the result
$result = $stmt->get_result();

// Fetch all the available slots
$availableSlots = [];

// Fetch each row from the result
while ($row = $result->fetch_assoc()) {
    // Add the time to the available slots array
    $availableSlots[] = $row['time'];
}

// If no available slots found, return a message
if (empty($availableSlots)) {
    echo json_encode(['success' => false, 'message' => 'No available time slots for this date.']);
} else {
    echo json_encode(['success' => true, 'slots' => $availableSlots]);
}

$stmt->close();
$conn->close();
?>
