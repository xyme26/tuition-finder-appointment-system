<?php
session_start();
include 'connection.php';
$current_page = 'profile';
$user_id = $_SESSION['user_id']; // Get the user ID from the session

// Add pagination variables
$items_per_page = 9; // Number of items to display per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get the current page from the URL or default to 1
$offset = ($current_page - 1) * $items_per_page; // Calculate the offset for the SQL query

// Modified query to include LIMIT and get total count
$count_query = "SELECT COUNT(*) as total FROM tuition_centers tc
                JOIN favorites f ON tc.id = f.tuition_center_id
                WHERE f.user_id = ?";
                
// Prepare the count query
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_items = $stmt->get_result()->fetch_assoc()['total']; // Get the total number of items
$total_pages = ceil($total_items / $items_per_page); // Calculate total pages

// Modified main query with LIMIT
$query = "SELECT tc.* FROM tuition_centers tc
          JOIN favorites f ON tc.id = f.tuition_center_id
          WHERE f.user_id = ?
          LIMIT ? OFFSET ?"; // Get favorite tuition centers with pagination

// Prepare the main query
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$favorite_centers = $result->fetch_all(MYSQLI_ASSOC); // Fetch all favorite centers as an associative array

// Close the statement
$stmt->close();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Tuition Centers</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <!-- Inline styling (for quick customizations) -->
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

        .col-md-4 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .favorite-card {
            height: 220px; /* Consistent height */
        }
        
        .favorite-card-image {
            height: 100px; /* Consistent image height */
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

        /* Specific to notifications.php */
        .form-check {
            margin-left: 0;
        }

        .form-check-label {
            font-size: 14px;
        }

        .col-md-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .favorite-card {
            height: 220px; /* Consistent height */
        }
        
        .favorite-card-image {
            height: 100px; /* Consistent image height */
        }
        
        .row {
            margin: 0 5px;
        }
    }

    /* Card grid layout */
    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding: 10px;
    }

    /* Card styles */
    .favorite-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        height: 220px; /* Reduced from 260px */
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .favorite-card-image {
        width: 100%;
        height: 100px; /* Reduced from 130px */
        object-fit: cover;
        object-position: center;
    }

    .favorite-card-body {
        padding: 10px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .favorite-card-title {
        font-size: 0.9rem; /* Smaller font size */
        margin-bottom: 8px;
        font-weight: 500;
        color: #1a2238;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <br><br>

    <!-- Main Content -->
    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Left navigation with custom design -->
        <div class="left-nav">
            <ul>
                <li><a href="profile.php">Personal Details</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_history.php">Review History</a></li>
                <li><a href="favorite_history.php" class="active">Favorite History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="profile-details">
            <h2>Your Favorite Tuition Centers</h2>
            <div class="row">
                <!-- Loop through each favorite center -->
                <?php foreach ($favorite_centers as $center): ?>
                    <div class="col-md-4 mb-3">
                        <div class="favorite-card">
                            <div class="favorite-btn-container">
                                <button class="favorite-btn active" data-center-id="<?php echo $center['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            <img src="<?php echo htmlspecialchars($center['image']); ?>" 
                                class="favorite-card-image" 
                                alt="<?php echo htmlspecialchars($center['name']); ?>">
                            <div class="favorite-card-body">
                                <h5 class="favorite-card-title"><?php echo htmlspecialchars($center['name']); ?></h5>
                                <a href="tuition_details.php?id=<?php echo $center['id']; ?>" 
                                class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            
            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
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
