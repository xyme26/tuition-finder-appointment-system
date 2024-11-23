<?php
include 'connection.php';

// Get user location from the request (latitude and longitude)
$user_lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
$user_lng = isset($_POST['lon']) ? floatval($_POST['lon']) : 0;

// Query to get tuition center information along with the average rating and distance
// Calculate distance using Haversine formula
$sql = "SELECT t.id, t.name, t.image, t.latitude, t.longitude, 
               COALESCE(t.city, '') as city, 
               COALESCE(t.address, '') as address,
               IFNULL(AVG(r.rating), 0) AS avg_rating,
               (6371 * acos(cos(radians(?)) * cos(radians(t.latitude)) * cos(radians(t.longitude) - radians(?)) + sin(radians(?)) * sin(radians(t.latitude)))) AS distance
        FROM tuition_centers t
        LEFT JOIN reviews r ON t.id = r.tuition_center_id
        GROUP BY t.id
        ORDER BY distance";

// Prepare the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ddd", $user_lat, $user_lng, $user_lat);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize the tuition centers array
$tuition_centers = array();

// Fetch the results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format the distance
        $row['distance'] = $row['distance'] ? number_format($row['distance'], 2) . ' km' : 'N/A';
        $tuition_centers[] = $row;
    }
}

// Return tuition center data in JSON format
echo json_encode($tuition_centers);

// Function to calculate the distance between two points (Haversine Formula)
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    $latFrom = deg2rad($latitudeFrom); // Convert latitude from degrees to radians
    $lonFrom = deg2rad($longitudeFrom); // Convert longitude from degrees to radians
    $latTo = deg2rad($latitudeTo); // Convert latitude to radians
    $lonTo = deg2rad($longitudeTo); // Convert longitude to radians

    $latDelta = $latTo - $latFrom; // Calculate the difference in latitude
    $lonDelta = $lonTo - $lonFrom; // Calculate the difference in longitude

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + // Haversine formula calculation
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius; // Return the distance in kilometers
}
?>
