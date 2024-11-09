<?php
include 'connection.php';

// Get the tuition center ID from the query parameter
$tuition_center_id = $_GET['tuition_center_id'];

// Prepare the statement
$stmt = $conn->prepare("SELECT id, appointment_datetime, status FROM appointments WHERE tuition_center_id = ?");
// Bind the tuition center ID to the statement
$stmt->bind_param("i", $tuition_center_id);
// Execute the statement
$stmt->execute();
// Get the result
$result = $stmt->get_result();

// Initialize the events array
$events = [];

// Fetch the results
while ($row = $result->fetch_assoc()) {
    // Add the event to the events array
    $events[] = [
        // Add the ID to the event
        'id' => $row['id'],
        // Add the title to the event
        'title' => 'Booked',
        // Add the start date and time to the event
        'start' => $row['appointment_datetime'],
        // Add the color to the event based on the status
        'color' => ($row['status'] == 'pending') ? 'yellow' : 'green'
    ];
}

// Return the events as JSON
echo json_encode($events);

// Close the statement and connection
$stmt->close();
$conn->close();
?>