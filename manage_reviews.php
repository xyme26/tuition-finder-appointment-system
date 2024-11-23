<?php
session_start();
$current_page = 'manage_reviews';

// Helper functions
function getUserIdFromReviewId($review_id, $conn) {
    // Gets the user ID associated with a specific review
    // Used for sending notifications when admin replies to a review
    $stmt = $conn->prepare("SELECT user_id FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['user_id'];
}

function sendNotification($user_id, $message, $conn) {
    // Sends a notification to a user
    // Used when admin takes action on a review (reply, approve, etc.)
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
}

// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connection.php';

// Main Functionality:
// 1. Fetches all reviews from database with user and tuition center information
// 2. Allows admin to like reviews
// 3. Allows admin to reply to reviews
// 4. Allows admin to delete reviews
// 5. Sends notifications to users when admin takes action on their review


// Handle deletion if requested
if (isset($_GET['delete'])) {
    $review_id = intval($_GET['delete']);

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Review deleted successfully.";
    } else {
        $error = "Failed to delete review.";
    }

    $stmt->close();
}

// Handle approval if requested
if (isset($_GET['approve'])) {
    $review_id = intval($_GET['approve']);

    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Review approved successfully.";
    } else {
        $error = "Failed to approve review.";
    }

    $stmt->close();
}

// Handle like action
if (isset($_GET['like'])) {
    $review_id = intval($_GET['like']);
    
    // Prepare and execute update statement for like
    $stmt = $conn->prepare("UPDATE reviews SET liked_by_admin = 1 WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Review liked successfully.";
        // Send notification to user
        $user_id = getUserIdFromReviewId($review_id, $conn);
        sendNotification($user_id, "Admin liked your review.", $conn);
    } else {
        $error = "Failed to like review.";
    }

    $stmt->close();
}

// Handle reply action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'], $_POST['review_id'])) {
    $reply = trim($_POST['reply']);
    $review_id = intval($_POST['review_id']);

    // Prepare and execute update statement for reply
    $stmt = $conn->prepare("UPDATE reviews SET reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply, $review_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Reply submitted successfully.";
        // Send notification to user
        $user_id = getUserIdFromReviewId($review_id, $conn);
        sendNotification($user_id, "An admin has replied to your review.", $conn);
    } else {
        $error = "Failed to submit reply.";
    }

    $stmt->close();
}

// Fetch all reviews
$query = "
    SELECT r.*, u.username, t.name AS tuition_center, r.approved
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN tuition_centers t ON r.tuition_center_id = t.id
    ORDER BY r.created_at DESC
";


$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin</title>
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
    <br><br><br>
    <div class="container-fluid admin-dashboard-container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Manage Reviews</h2>

                <!-- Display success or error messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Reviews Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Tuition Center</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Approved</th>
                                <th>Created At</th>
                                <th>Actions</th>
                                <th>Reply</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($review = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($review['id']); ?></td>
                                        <td><?php echo htmlspecialchars($review['username']); ?></td>
                                        <td><?php echo htmlspecialchars($review['tuition_center']); ?></td>
                                        <td><?php echo htmlspecialchars($review['rating']); ?></td>
                                        <td><?php echo htmlspecialchars($review['comment']); ?></td>
                                        <td><?php echo isset($review['approved']) ? ($review['approved'] ? 'Yes' : 'No') : 'No'; ?></td>
                                        <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                                        <td>
                                            <?php if (!isset($review['approved']) || !$review['approved']): ?>
                                                <a href="manage_reviews.php?approve=<?php echo $review['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                            <?php endif; ?>
                                            <?php if (!$review['liked_by_admin']): ?>
                                                <a href="manage_reviews.php?like=<?php echo $review['id']; ?>" class="btn btn-sm btn-primary">Like</a>
                                            <?php endif; ?>
                                            <a href="manage_reviews.php?delete=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                                        </td>
                                        <td>
                                            <?php if (empty($review['reply'])): ?>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <textarea name="reply" required></textarea>
                                                    <button type="submit" class="btn btn-sm btn-primary">Reply</button>
                                                </form>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($review['reply']); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No reviews found.</td>
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
