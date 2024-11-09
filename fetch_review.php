<?php
include 'connection.php';

// Fetch tuition centers with their average rating
$sql = "SELECT t.id, t.name, t.address, t.image, IFNULL(AVG(r.rating), 0) AS avg_rating
        FROM tuition_centers t
        LEFT JOIN reviews r ON t.id = r.tuition_center_id
        GROUP BY t.id";
$result = $conn->query($sql);

// Initialize the tuition centers array
$tuition_centers = array();

// Fetch the results
while ($row = $result->fetch_assoc()) {
    $tuition_centers[] = $row;
}

// Return the tuition centers as JSON
echo json_encode($tuition_centers);
?>
