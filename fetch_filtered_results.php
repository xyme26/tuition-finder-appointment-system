<?php
session_start();
include 'connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get parameters
    // Get the name parameter
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    // Get the location parameter
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
    // Get the minimum rating parameter
    $minRating = isset($_GET['minRating']) ? floatval($_GET['minRating']) : 0;
    // Get the sort by parameter
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'name_asc';
    // Get the subjects parameter
    $subjects = isset($_GET['subjects']) ? json_decode($_GET['subjects']) : [];
    // Get the languages parameter
    $languages = isset($_GET['languages']) ? json_decode($_GET['languages']) : [];
    // Get the page parameter
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    // Set the limit to 9 items per page
    $limit = 9;
    // Calculate the offset
    $offset = ($page - 1) * $limit;

    // Base query
    $sql = "SELECT SQL_CALC_FOUND_ROWS tc.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM tuition_centers tc
    LEFT JOIN reviews r ON tc.id = r.tuition_center_id";

    // Initialize parameters array
    $params = [];
    $types = "";

    // Build WHERE clause
    $whereConditions = [];

    // Add name filter
    if (!empty($name)) {
        $whereConditions[] = "tc.name LIKE ?";
        $params[] = "%$name%";
        $types .= "s";
    }

    // Add location filter
    if (!empty($location)) {
        $whereConditions[] = "(tc.address LIKE ? OR tc.city LIKE ?)";
        $params[] = "%$location%";
        $params[] = "%$location%";
        $types .= "ss";
    }

    // Add subject filter
    if (!empty($subjects)) {
        $subjectConditions = [];
        foreach ($subjects as $subject) {
            $subjectConditions[] = "tc.course_tags LIKE ?";
            $params[] = "%$subject%";
            $types .= "s";
        }
        if (!empty($subjectConditions)) {
            $whereConditions[] = "(" . implode(" OR ", $subjectConditions) . ")";
        }
    }

    // Add language filter
    if (!empty($languages)) {
        $languageConditions = [];
        foreach ($languages as $language) {
            $languageConditions[] = "FIND_IN_SET(?, tc.teaching_language)";
            $params[] = $language;
            $types .= "s";
        }
        if (!empty($languageConditions)) {
            $whereConditions[] = "(" . implode(" OR ", $languageConditions) . ")";
        }
    }

    // Add WHERE clause if conditions exist
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Group by
    $sql .= " GROUP BY tc.id";

    // Add having clause for rating filter
    if ($minRating > 0) {
        $sql .= " HAVING avg_rating >= $minRating";
    }

    // Order by
    switch ($sortBy) {
        case 'rating_desc':
            $sql .= " ORDER BY avg_rating DESC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY tc.name DESC";
            break;
        default:
            $sql .= " ORDER BY tc.name ASC";
    }

    // Add LIMIT and OFFSET at the end
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);

    // Bind the parameters
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();
    $centers = [];

    // Fetch the results
    while ($row = $result->fetch_assoc()) {
        $centers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $row['image'] ?? 'images/default-tuition.jpg',
            'avg_rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
            'review_count' => $row['review_count'],
            'price_range' => $row['price_range'],
            'address' => $row['address'],
            'course_tags' => $row['course_tags'] ? explode(',', $row['course_tags']) : [],
            'teaching_language' => explode(',', $row['teaching_language'] ?? 'English,Bahasa Malaysia,Chinese')
        ];
    }
    
    // Get total count
    $totalResult = $conn->query("SELECT FOUND_ROWS() as total");
    // Fetch the total count
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];
    // Calculate the total number of pages
    $totalPages = ceil($total / $limit);

    // Prepare the response
    $response = [
        'centers' => $centers,
        'totalCount' => $total,
        'currentPage' => $page,
        'itemsPerPage' => 9
    ];

    // Return the response as JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Log the error
    error_log("Error in fetch_filtered_results.php: " . $e->getMessage());
    // Set the HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return the error message as JSON
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>