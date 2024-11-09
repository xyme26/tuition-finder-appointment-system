<?php
session_start();
require 'connection.php'; // Include database connection file

header('Content-Type: application/json'); // Set content type to JSON

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the user ID
$user_id = $_SESSION['user_id'];

// Fetch upcoming appointments (not completed)
$sqlUpcoming = "SELECT a.id, a.appointment_datetime, a.reason, a.status, tc.name AS tuition_name
                FROM appointments a
                JOIN tuition_centers tc ON a.tuition_center_id = tc.id
                WHERE a.user_id = ? 
                AND a.appointment_datetime >= NOW()
                AND a.status != 'completed'
                ORDER BY a.appointment_datetime ASC";

// Prepare the statement
$stmtUpcoming = $conn->prepare($sqlUpcoming);
// Bind the user ID to the statement
$stmtUpcoming->bind_param("i", $user_id);
// Execute the statement
$stmtUpcoming->execute();
// Get the result
$resultUpcoming = $stmtUpcoming->get_result();
// Fetch all the upcoming appointments
$upcomingAppointments = $resultUpcoming->fetch_all(MYSQLI_ASSOC);

// Fetch past appointments (including completed ones)
$sqlPast = "SELECT a.id, a.appointment_datetime, a.reason, a.status, tc.name AS tuition_name
            FROM appointments a
            JOIN tuition_centers tc ON a.tuition_center_id = tc.id
            WHERE a.user_id = ? 
            AND (a.appointment_datetime < NOW() OR a.status = 'completed')
            AND a.appointment_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY a.appointment_datetime DESC";

// Prepare the statement
$stmtPast = $conn->prepare($sqlPast);
// Bind the user ID to the statement    
$stmtPast->bind_param("i", $user_id);
// Execute the statement
$stmtPast->execute();
// Get the result
$resultPast = $stmtPast->get_result();
// Fetch all the past appointments
$pastAppointments = $resultPast->fetch_all(MYSQLI_ASSOC);

// Return the data as JSON
echo json_encode([
    'success' => true,
    'upcoming' => $upcomingAppointments,
    'past' => $pastAppointments
]);

// Close the statements and connection
$stmtUpcoming->close();
$stmtPast->close();
$conn->close();
?>
