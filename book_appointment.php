<?php
// book_appointment.php
session_start();

// Error reporting
error_reporting(E_ALL);

// Display errors
ini_set('display_errors', 1);

// Enable CORS if needed (modify according to your security requirements)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include the database connection
include 'connection.php';

// Log the received data
$input = json_decode(file_get_contents("php://input"), true); // Decode the JSON input
error_log("Received appointment data: " . print_r($input, true)); // Log the received data for debugging

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the user ID
$userId = $_SESSION['user_id'];
// Get the tuition center ID
$tuition_center_id = $input['tuition_center_id'] ?? null;
// Get the date
$date = $input['date'] ?? null;
// Get the time
$time = $input['time'] ?? null;
// Get the reason
$reason = $input['reason'] ?? null;

// Validate input
if (!$tuition_center_id || !$date || !$time || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']); // Return error if any required field is missing
    exit;
}

// Validate date and time
try {
    $dateTime = new DateTime($date . ' ' . $time); // Create a DateTime object from the date and time
} catch (Exception $e) {
    error_log("Invalid date/time format: " . $e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'message' => 'Invalid date or time format']); // Return error if date/time is invalid
    exit;
}

// Get the day of the week
$dayOfWeek = $dateTime->format('N'); // 1 (Monday) to 7 (Sunday)
// Get the hour
$hour = $dateTime->format('G');
// Get the minute
$minute = $dateTime->format('i');

// Check if the day of the week is a weekday
if ($dayOfWeek > 5) {
    echo json_encode(['success' => false, 'message' => 'Appointments are only available on weekdays']);
    exit;
}

// Check if the hour is between 11 AM and 5 PM
if ($hour < 11 || $hour >= 17 || ($hour == 16 && $minute > 30)) {
    echo json_encode(['success' => false, 'message' => 'Appointments are only available between 11 AM and 5 PM']);
    exit;
}

// Check if the minute is either 00 or 30
if ($minute != '00' && $minute != '30') {
    echo json_encode(['success' => false, 'message' => 'Appointments must be booked on the hour or half-hour']);
    exit;
}

// Format the date and time
$appointmentDateTime = $dateTime->format('Y-m-d H:i:s');

// Check for existing appointments at the same time
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE tuition_center_id = ? AND appointment_datetime = ?");
if ($stmt === false) {
    error_log("Prepare failed for checking existing appointments: " . $conn->error); // Log error if preparation fails
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for checking existing appointments']);
    exit;
}

// Bind the parameters
$stmt->bind_param("is", $tuition_center_id, $appointmentDateTime);
$stmt->execute(); // Execute the statement
$stmt->bind_result($count); // Bind the result to a variable
$stmt->fetch(); // Fetch the result
$stmt->close();

// Check if the time slot is already booked
if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'This time slot is already booked']);
    exit;
}

// Prepare the statement to insert the appointment
$stmt = $conn->prepare("INSERT INTO appointments (user_id, tuition_center_id, appointment_datetime, reason, status) VALUES (?, ?, ?, ?, 'pending')");
if ($stmt === false) {
    error_log("Prepare failed for inserting appointment: " . $conn->error); // Log error if preparation fails
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for inserting appointment']);
    exit;
}

// Bind the parameters
$stmt->bind_param("iiss", $userId, $tuition_center_id, $appointmentDateTime, $reason);

// Log the attempt to insert the appointment
error_log("Attempting to insert appointment: User ID: $userId, Tuition Center ID: $tuition_center_id, DateTime: $appointmentDateTime, Reason: $reason");

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment successfully booked.']);
} else {
    error_log("Execute failed: " . $stmt->error); // Log error if execution fails
    echo json_encode(['success' => false, 'message' => 'Failed to book appointment: ' . $stmt->error]);
}

// Close the statement
$stmt->close();
// Close the connection
$conn->close();
?>
