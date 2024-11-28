<?php
session_start();
include 'connection.php';

// Check database connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Not logged in']));
}

// Get the user ID from the session 
$user_id = $_SESSION['user_id'];

// Initialize the response array
$response = ['success' => false];

// Get the POST data for the updates
$updates = []; // Array to hold the update statements
$types = ""; // Array to hold the values for the parameters
$values = []; // Array to hold the values for the parameters

// Handle the first name update
if (isset($_POST['first_name'])) {
    $updates[] = "first_name = ?";
    $types .= "s";
    $values[] = $_POST['first_name'];
}

// Handle the last name update
if (isset($_POST['last_name'])) {
    $updates[] = "last_name = ?";
    $types .= "s";
    $values[] = $_POST['last_name'];
}

// Handle the email update
if (isset($_POST['email'])) {
    $updates[] = "email = ?";
    $types .= "s";
    $values[] = $_POST['email'];
}

// Handle the phone number update
if (isset($_POST['phone_number'])) {
    $updates[] = "phone_number = ?";
    $types .= "s"; // Append string type for phone number
    $values[] = $_POST['phone_number'];
}

// Handle the address update
if (isset($_POST['address'])) {
    $updates[] = "address = ?";
    $types .= "s"; // Append string type for address
    $values[] = $_POST['address'];
}

// Handle the username update
if (isset($_POST['username'])) {
    $updates[] = "username = ?";
    $types .= "s"; // Append string type for username
    $values[] = $_POST['username'];
}

// Check if there are any updates to be made
if (!empty($updates)) {
    // Add the user ID to the values array
    $values[] = $user_id;
    // Add an "i" to the types string to indicate an integer
    $types .= "i";
    // Construct the SQL query
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    
    // Prepare the SQL statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters to the statement
        $stmt->bind_param($types, ...$values);
        // Execute the statement
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = "Update failed: " . $stmt->error; // Capture SQL error
        }
        // Close the statement
        $stmt->close();
    } else {
        $response['error'] = "Prepare failed: " . $conn->error; // Capture prepare error
    }
} else {
    $response['error'] = "No updates provided.";
}

// Close the database connection
$conn->close();

// Return the response as a JSON object (to be used by the frontend)
echo json_encode($response);
?>
