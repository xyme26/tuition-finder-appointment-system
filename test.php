<?php
session_start();
include 'connection.php';

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Page</h2>";

// Test 1: Database Connection
echo "<h3>Testing Database Connection</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Test 2: Review System
echo "<h3>Testing Review System</h3>";
try {
    $sql = "SELECT COUNT(*) as count FROM reviews";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "✅ Total reviews in database: " . $row['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error counting reviews: " . $e->getMessage() . "<br>";
}

// Test 3: Average Ratings
echo "<h3>Testing Average Ratings</h3>";
try {
    $sql = "SELECT tc.name, AVG(r.rating) as avg_rating 
            FROM tuition_centers tc 
            LEFT JOIN reviews r ON tc.id = r.tuition_center_id 
            GROUP BY tc.id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "✅ " . $row['name'] . ": " . number_format($row['avg_rating'], 1) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching ratings: " . $e->getMessage() . "<br>";
}

// Test 4: Distance Calculation
echo "<h3>Testing Distance Calculation</h3>";
$test_lat = 3.1390; // Example latitude
$test_lon = 101.6869; // Example longitude
try {
    $sql = "SELECT name, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(latitude)))) AS distance 
            FROM tuition_centers 
            ORDER BY distance 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddd", $test_lat, $test_lon, $test_lat);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "✅ " . $row['name'] . ": " . number_format($row['distance'], 2) . " km<br>";
    }
} catch (Exception $e) {
    echo "❌ Error calculating distances: " . $e->getMessage() . "<br>";
}

// Test 5: Image Paths
echo "<h3>Testing Image Paths</h3>";
try {
    $sql = "SELECT id, image FROM tuition_centers LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (file_exists($row['image'])) {
            echo "✅ Image exists for ID " . $row['id'] . ": " . $row['image'] . "<br>";
        } else {
            echo "❌ Image missing for ID " . $row['id'] . ": " . $row['image'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking images: " . $e->getMessage() . "<br>";
}

// Test 6: Favorites System
echo "<h3>Testing Favorites System</h3>";
try {
    $sql = "SELECT COUNT(*) as count FROM favorites";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "✅ Total favorites saved: " . $row['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error checking favorites: " . $e->getMessage() . "<br>";
}

// Test 7: Notification System
echo "<h3>Testing Notification System</h3>";

// 7.1 Test notification counts
try {
    $sql = "SELECT COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
            FROM notifications";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "✅ Total notifications: " . $row['total'] . "<br>";
    echo "✅ Unread notifications: " . $row['unread'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error checking notifications: " . $e->getMessage() . "<br>";
}

// 7.2 Test notification creation
try {
    $test_user_id = 1; // Replace with a valid user ID from your database
    $test_message = "Test notification from test.php";
    
    $sql = "INSERT INTO notifications (user_id, message, notification_type) 
            VALUES (?, ?, 'favorite')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $test_user_id, $test_message);
    
    if ($stmt->execute()) {
        echo "✅ Test notification created successfully<br>";
        $new_notification_id = $stmt->insert_id;
    } else {
        echo "❌ Failed to create test notification<br>";
    }
} catch (Exception $e) {
    echo "❌ Error creating notification: " . $e->getMessage() . "<br>";
}

// 7.3 Test notification retrieval
if (isset($new_notification_id)) {
    try {
        $sql = "SELECT * FROM notifications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $new_notification_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "✅ Retrieved test notification: " . $row['message'] . "<br>";
        } else {
            echo "❌ Could not retrieve test notification<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error retrieving notification: " . $e->getMessage() . "<br>";
    }
}

// 7.4 Test notification preferences
try {
    $sql = "SELECT COUNT(*) as users_with_prefs FROM users 
            WHERE fav_update = 1 
            OR upcoming_appointment = 1 
            OR appointment_confirmation = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "✅ Users with notification preferences: " . $row['users_with_prefs'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error checking notification preferences: " . $e->getMessage() . "<br>";
}

// 7.5 Test notification marking as read
if (isset($new_notification_id)) {
    try {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $new_notification_id);
        
        if ($stmt->execute()) {
            echo "✅ Successfully marked notification as read<br>";
        } else {
            echo "❌ Failed to mark notification as read<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error marking notification as read: " . $e->getMessage() . "<br>";
    }
}

// 7.6 Clean up test notification
if (isset($new_notification_id)) {
    try {
        $sql = "DELETE FROM notifications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $new_notification_id);
        
        if ($stmt->execute()) {
            echo "✅ Test notification cleaned up successfully<br>";
        } else {
            echo "❌ Failed to clean up test notification<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error cleaning up notification: " . $e->getMessage() . "<br>";
    }
}

// Test 8: Form Validation
echo "<h3>Testing Form Validation</h3>";
$test_email = "test@example.com";
$test_phone = "1234567890";
echo "✅ Email validation: " . (filter_var($test_email, FILTER_VALIDATE_EMAIL) ? "Valid" : "Invalid") . "<br>";
echo "✅ Phone validation: " . (preg_match("/^[0-9]{10}$/", $test_phone) ? "Valid" : "Invalid") . "<br>";

// Test 9: Database Performance
echo "<h3>Testing Database Performance</h3>";
try {
    $start_time = microtime(true);
    $sql = "SELECT COUNT(*) FROM tuition_centers";
    $conn->query($sql);
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
    echo "✅ Query execution time: " . number_format($execution_time, 2) . " ms<br>";
} catch (Exception $e) {
    echo "❌ Error testing performance: " . $e->getMessage() . "<br>";
}

// Add styling for better readability
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    h2 {
        color: #1a2238;
    }
    h3 {
        color: #1a2238;
        margin-top: 20px;
    }
    .success {
        color: green;
    }
    .error {
        color: red;
    }
</style>
";

$conn->close();
?>
