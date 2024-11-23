<?php
session_start();
$current_page = 'manage_feedback';
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

// Check if the session has timed out
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Unset all session variables
    session_unset();
    // Destroy the session
    session_destroy();
    // Redirect to login page with timeout parameter
    header("Location: login_admin.php?timeout=1");
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    // Redirect to login page if not logged in
    header("Location: login_admin.php");
    exit();
}

// Include the database connection
require_once 'connection.php';

// Fetch all feedback
$query = "
    SELECT f.*, u.username
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
";

// Execute the query
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Feedback - Admin</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br>
    <!-- Container for the feedback table -->
    <div class="container-fluid admin-dashboard-container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Users Feedback</h2>

                <!-- Feedback Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Check if there are any feedback records -->
                            <?php if ($result->num_rows > 0): ?>
                                <!-- Loop through each feedback record -->
                                <?php while ($feedback = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                                        <td><?php 
                                            // Check if the username exists
                                            if ($feedback['username']) {
                                                echo htmlspecialchars($feedback['username']);
                                            } else {
                                                echo '<span class="text-muted">Guest User</span>';
                                            }
                                        ?></td>
                                        <td>
                                            <?php 
                                                $ratingIcon = [
                                                    'good' => '😃',
                                                    'neutral' => '😐',
                                                    'bad' => '😞'
                                                ];
                                                echo $ratingIcon[$feedback['rating']] ?? '';
                                                echo ' ' . htmlspecialchars($feedback['rating']); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($feedback['comment'] ?? 'No comment'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($feedback['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Display a message if there are no feedback records -->
                                <tr>
                                    <td colspan="5" class="text-center">No feedback found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>
</body>
</html>
