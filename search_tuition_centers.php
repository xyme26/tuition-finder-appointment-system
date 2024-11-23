<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

try {
    // Get the name and location from the query parameters
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    
    // Get the user's latitude and longitude from the session
    $user_lat = $_SESSION['user_lat'] ?? 0;
    $user_lon = $_SESSION['user_lon'] ?? 0;

    // Prepare the SQL query for distance calculation
    $sql = "SELECT tc.*, 
           (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(latitude)))) AS distance,
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM tuition_centers tc
    LEFT JOIN reviews r ON tc.id = r.tuition_center_id";

    // Prepare the parameters and types
    $params = [$user_lat, $user_lon, $user_lat];
    $types = "ddd";

    // Add conditions if the name or location is not empty
    if (!empty($name) || !empty($location)) {
        $sql .= " WHERE 1=1";
        
        // Add condition for the name if it is not empty
        if (!empty($name)) {
            $sql .= " AND tc.name LIKE ?";
            $params[] = "%$name%";
            $types .= "s";
        }

        // Add condition for the location if it is not empty
        if (!empty($location)) {
            $sql .= " AND (tc.city LIKE ? OR tc.address LIKE ?)";
            $params[] = "%$location%";
            $params[] = "%$location%";
            $types .= "ss";
        }
    }

    // Add the order by clause for distance
    $sql .= " GROUP BY tc.id ORDER BY distance ASC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to store the centers
    $centers = array();

    // Fetch the results and format the distance and rating
    while ($row = $result->fetch_assoc()) {
        // Format the distance
        $row['distance'] = number_format($row['distance'], 1);
        // Format the rating
        $row['avg_rating'] = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 0;
        $centers[] = $row;
    }

    // Encode the centers array as JSON and echo it
    echo json_encode($centers);

} catch (Exception $e) {
    // Encode the error message as JSON and echo it
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
