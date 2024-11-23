<?php
require_once 'connection.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Handle different data types for Google Charts
$type = $_GET['type'] ?? ''; // Get the type of data requested

// Initialize the response array
$response = [];

// Fetch top 5 tuition centers by average rating
if ($type == 'tuition_centers') {
    $query = "SELECT 
                t.name AS center,
                ROUND(AVG(r.rating), 1) as avg_rating
              FROM tuition_centers t
              LEFT JOIN reviews r ON t.id = r.tuition_center_id
              GROUP BY t.id, t.name
              HAVING avg_rating > 0
              ORDER BY avg_rating DESC
              LIMIT 5"; // Query to get top 5 tuition centers by average rating
    
    try {
        // Prepare the statement
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch the results
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'center' => $row['center'],
                'avg_rating' => (float)$row['avg_rating']
            ];
        }
    } catch (Exception $e) {
        // Log the error
        error_log("Error fetching tuition centers: " . $e->getMessage());
        // Set the HTTP response code to 500 (Internal Server Error)
        http_response_code(500);
        // Return the error message as JSON
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Update the appointments chart to include review data
if ($type == 'appointments') {
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y'); // Get the year from the request or use current year
    $month = isset($_GET['month']) && $_GET['month'] !== 'all' ? intval($_GET['month']) : null; // Get the month if specified

    // Prepare the query
    $query = "SELECT 
                " . ($month ? "DAY(COALESCE(a.appointment_datetime, r.created_at)) as day" 
                          : "MONTH(COALESCE(a.appointment_datetime, r.created_at)) as month") . ",
                COUNT(DISTINCT a.id) as appointments,
                COUNT(DISTINCT r.id) as reviews
              FROM (
                SELECT id, appointment_datetime FROM appointments
                UNION ALL
                SELECT id, created_at FROM reviews
              ) combined_dates
              LEFT JOIN appointments a ON a.id = combined_dates.id
              LEFT JOIN reviews r ON r.id = combined_dates.id
              WHERE YEAR(COALESCE(a.appointment_datetime, r.created_at)) = ?"; // Query to get appointments and reviews

    // Bind the parameters
    $params = [$year];
    $types = "i";

    // Add the month condition if it's not null
    if ($month !== null) {
        $query .= " AND MONTH(COALESCE(a.appointment_datetime, r.created_at)) = ?";
        $params[] = $month;
        $types .= "i"; // Update parameter types
    }

    // Add the group by and order by clauses
    $query .= " GROUP BY " . ($month ? "day" : "month");
    $query .= " ORDER BY " . ($month ? "day" : "month");

    try {
        // Prepare the statement
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch the results
        while ($row = $result->fetch_assoc()) {
            $period = $month ? $row['day'] : $row['month']; // Determine if fetching day or month
            $response[] = [
                ($month ? 'day' : 'month') => (int)$period, // Add period to response
                'appointments' => (int)$row['appointments'], // Number of appointment
                'reviews' => (int)$row['reviews'] // Number of reviews
            ];
        }
        // Log the combined data
        error_log("Combined data: " . json_encode($response));
    } catch (Exception $e) {
        // Log the error
        error_log("Error fetching combined data: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Update user accounts chart to include review activity
if ($type == 'user_accounts') {
    // Get monthly user registrations for current year
    $year = date('Y');
    
    // Prepare the query
    $query = "SELECT 
                MONTH(created_at) as month,
                COUNT(*) as registrations
              FROM users
              WHERE YEAR(created_at) = ?
              GROUP BY MONTH(created_at)
              ORDER BY month"; // Query to get user registrations by month

    try {
        // Prepare the statement
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize all months with 0
        $monthlyData = array_fill(1, 12, 0); // Create an array for each month initialized to 0

        // Fetch the results
        while ($row = $result->fetch_assoc()) {
            $monthlyData[$row['month']] = (int)$row['registrations']; // Store registration counts by month
        }

        // Convert to response format
        foreach ($monthlyData as $month => $count) {
            $response[] = [
                'month' => $month,
                'registrations' => $count
            ];
        }
    } catch (Exception $e) {
        // Log the error
        error_log("Error fetching user accounts: " . $e->getMessage());
        http_response_code(500); // Set HTTP response code to 500
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Return the response as JSON
echo json_encode($response); // Output the response
?>
