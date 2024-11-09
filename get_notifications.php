<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Get user's notification preferences
$pref_stmt = $conn->prepare("SELECT fav_update, upcoming_appointment, appointment_confirmation FROM users WHERE id = ?");
$pref_stmt->bind_param("i", $user_id);
$pref_stmt->execute();
$preferences = $pref_stmt->get_result()->fetch_assoc();
$pref_stmt->close();

// Build the WHERE clause based on preferences
$where_conditions = ["user_id = ?"];
$types = "i"; // Add types for the user ID parameter
$params = [$user_id];

// Add conditions based on user preferences
if (!$preferences['fav_update']) {
    // Add a condition to exclude favorite notifications
    $where_conditions[] = "notification_type != 'favorite'";
}
if (!$preferences['upcoming_appointment']) {
    // Add a condition to exclude upcoming appointment notifications
    $where_conditions[] = "notification_type != 'upcoming'";
}
if (!$preferences['appointment_confirmation']) {
    // Add a condition to exclude appointment confirmation notifications
    $where_conditions[] = "notification_type != 'confirmation'";
}

// Combine the conditions into a WHERE clause
$where_clause = implode(" AND ", $where_conditions);

// Build the query
$query = "SELECT id, message, is_read, created_at 
          FROM notifications 
          WHERE {$where_clause} 
          ORDER BY created_at DESC LIMIT 5";

// Prepare the statement
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return the notifications as JSON
echo json_encode($notifications);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
