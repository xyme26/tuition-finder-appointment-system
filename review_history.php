<?php
session_start();
include 'connection.php';
$current_page = 'profile';
$user_id = $_SESSION['user_id'];

// Fetch reviews from the database
$items_per_page = 5;

// Get the current page number from the URL, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the current page
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM reviews r 
                WHERE r.user_id = ?";

// Prepare the statement
$stmt = $conn->prepare($count_query);

// Bind the user ID parameter to the statement
$stmt->bind_param("i", $user_id);

// Execute the statement
$stmt->execute();

// Get the total number of items
$total_items = $stmt->get_result()->fetch_assoc()['total'];

// Calculate the total number of pages
$total_pages = ceil($total_items / $items_per_page);


// Modified main query with LIMIT
$query = "SELECT r.tuition_center_id, r.comment AS review, r.created_at, r.liked_by_admin, r.reply,
          tc.name AS tuition_center_name 
          FROM reviews r 
          JOIN tuition_centers tc ON r.tuition_center_id = tc.id 
          WHERE r.user_id = ?
          ORDER BY r.created_at DESC
          LIMIT ? OFFSET ?";

// Prepare the statement
$stmt = $conn->prepare($query);

// Bind the parameters to the statement
$stmt->bind_param("iii", $user_id, $items_per_page, $offset);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Details</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">

<style>
    /* Container for the whole profile section */
    .profile-container {
        display:flex;
        padding: 20px;
        width:70%;
    }

    /* Profile details design */
    .profile-details {
        background-color: #f4db7d;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        margin-left: 20px;
        color: #1a2238;
    }

    .profile-details h2 {
        color: #1a2238;
    }

    .profile-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #1a2238;
    }

    .profile-item span {
        width: 40%;
    }

    .profile-item a {
        color: #ff6a3d;
        text-decoration: none;
        font-weight: bold;
    }

    .profile-item a:hover {
        color: #1a2238;
    }

    /* Left navigation design */
    .left-nav {
        background-color: #1a2238;
        padding: 20px;
        border-radius: 10px;
        color: white;
        width: 250px;
    }

    .left-nav ul {
        list-style-type: none;
        padding-left: 0;
    }

    .left-nav ul li {
        margin: 15px 0;
    }

    .left-nav ul li a {
        color: #f4db7d;
        text-decoration: none;
        font-weight: bold;
    }

    .left-nav ul li a:hover {
        color: #ff6a3d;
    }

    .left-nav ul li a.active{
        color: #ff6a3d;
        border-radius: 4px;
        border-color: #ffffff;
        border-style: solid;
    }

    button {
        background-color: #ff6a3d;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #1a2238;
    }

    .review-card {
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .review-date {
        color: #666;
        font-size: 0.9rem;
    }

    .review-text {
        margin-bottom: 15px;
    }

    .admin-like {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .admin-reply {
        background-color: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px;
    }

    .reply-header {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #1a2238;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .reply-text {
        color: #444;
        margin: 0;
        padding-left: 24px;
    }

    hr {
        margin: 20px 0;
        border-color: #eee;
    }

    @media screen and (max-width: 992px) {
    .profile-container {
        width: 90%;
        flex-direction: column;
        padding: 10px;
    }

    .left-nav {
        width: 100%;
        margin-bottom: 20px;
        position: relative;
    }

    .left-nav ul {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .left-nav ul li {
        margin: 5px;
    }

    .left-nav ul li a {
        display: block;
        padding: 8px 15px;
        text-align: center;
        background-color: rgba(244, 219, 125, 0.1);
        border-radius: 4px;
    }

    .profile-details {
        margin-left: 0;
        padding: 15px;
    }
}

@media screen and (max-width: 768px) {
    .profile-container {
        width: 95%;
    }

    .left-nav ul {
        flex-direction: column;
    }

    .left-nav ul li a {
        width: 100%;
    }

    .profile-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .profile-item span {
        width: 100%;
        margin-bottom: 5px;
    }

    /* Specific to favorite_history.php */
    .card {
        margin-bottom: 15px;
    }

    /* Specific to review_history.php */
    .review-item {
        flex-direction: column;
    }

    .review-item > div {
        width: 100%;
        margin-bottom: 10px;
        padding: 10px;
    }

    /* Specific to notifications.php */
    .form-check {
        margin-left: 0;
    }

    .form-check-label {
        font-size: 14px;
    }
}

</style>

<body>
    <?php include 'header.php'; ?>
    <br><br>

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Left navigation with custom design -->
        <div class="left-nav">
            <ul>
                <li><a href="profile.php">Personal Details</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_history.php" class="active">Review History</a></li>
                <li><a href="favorite_history.php">Favorite History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Profile details -->
        <div class="profile-details">
            <h2>Your Review History</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="reviews-container">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <h5><?php echo htmlspecialchars($row['tuition_center_name']); ?></h5>
                                <span class="review-date">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </span>
                            </div>
                            
                            <p class="review-text"><?php echo htmlspecialchars($row['review']); ?></p>
                            
                            <!-- Admin Like Status -->
                            <?php if ($row['liked_by_admin']): ?>
                                <div class="admin-like">
                                    <i class="fas fa-heart text-danger"></i>
                                    <span>Admin liked</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Admin Reply Section -->
                            <?php if (!empty($row['reply'])): ?>
                                <div class="admin-reply">
                                    <div class="reply-header">
                                        <i class="fas fa-reply"></i>
                                        <span>Admin Reply:</span>
                                    </div>
                                    <p class="reply-text"><?php echo htmlspecialchars($row['reply']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                    <?php endwhile; ?>
                    
                    <!-- Pagination Controls -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-container mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No reviews found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Check if there are stored survey answers
        const storedSurveyAnswers = sessionStorage.getItem('surveyAnswers');
        if (storedSurveyAnswers && new URLSearchParams(window.location.search).get('survey') === 'completed') {
            // Parse the stored answers
            const surveyAnswers = JSON.parse(storedSurveyAnswers);
            
            // Update recommendations with the stored answers
            updateRecommendations(surveyAnswers);
            
            // Clear the stored answers
            sessionStorage.removeItem('surveyAnswers');
            
            // Scroll to recommendations
            document.getElementById('recommendation-grid').scrollIntoView({ behavior: 'smooth' });
        }
    });
    </script>
</body>
</html>
