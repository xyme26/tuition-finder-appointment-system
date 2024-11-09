<?php
include 'connection.php';

// Get the data from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is set
if (isset($data['id']) && isset($data['latitude']) && isset($data['longitude'])) {
    // Prepare the SQL statement
    $sql = "UPDATE tuition_centers 
            SET latitude = ?, longitude = ? 
            WHERE id = ?";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    // Bind the parameters to the statement
    $stmt->bind_param("ddi", 
        // Bind the latitude, longitude, and ID to the statement
        $data['latitude'], 
        $data['longitude'], 
        $data['id']
    );
    // Execute the statement
    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    // Return an error response if the required data is missing
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
}
?>
