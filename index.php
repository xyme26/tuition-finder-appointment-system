<?php
session_start();
include 'connection.php';
$current_page = 'home';

// Get user's location from session or set default values
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_lon = $_SESSION['user_lon'] ?? 0;

// Query to fetch nearest tuition centers based on user location
// Uses Haversine formula to calculate distance
$nearest_query = "
    SELECT tc.*, 
           (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(latitude)))) AS distance,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.id) as review_count
    FROM tuition_centers tc
    LEFT JOIN reviews r ON tc.id = r.tuition_center_id
    WHERE tc.latitude IS NOT NULL 
    AND tc.longitude IS NOT NULL
    GROUP BY tc.id
    HAVING distance IS NOT NULL
    ORDER BY distance ASC
    LIMIT 5";

// Prepare and execute the nearest centers query
$stmt = $conn->prepare($nearest_query);
$stmt->bind_param("ddd", $user_lat, $user_lon, $user_lat);
$stmt->execute();
$nearest_result = $stmt->get_result();

if (!$nearest_result) {
    die("Query failed: " . $conn->error);
}

// Query to fetch top-rated tuition centers
$top_query = "
    SELECT tc.id, tc.name, tc.image, 
           AVG(r.rating) AS avg_rating,
           COUNT(r.id) as review_count,
           (6371 * acos(cos(radians($user_lat)) * cos(radians(tc.latitude)) * 
            cos(radians(tc.longitude) - radians($user_lon)) + 
            sin(radians($user_lat)) * sin(radians(tc.latitude)))) AS distance
    FROM tuition_centers tc
    LEFT JOIN reviews r ON tc.id = r.tuition_center_id
    GROUP BY tc.id
    ORDER BY avg_rating DESC
    LIMIT 5";
$top_result = $conn->query($top_query);

if (!$top_result) {
    die("Query failed: " . $conn->error);
}

// Check if it's the user's first visit and set session variable
$first_visit = !isset($_SESSION['has_seen_survey']);
if ($first_visit) {
    $_SESSION['has_seen_survey'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuition Finder</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    <style>
        /* Search section styles */
        .search-section {
            position: relative;
        }

        .input-group {
            position: relative;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input-container {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: relative;
            color: #666;
            margin-right: 8px;
        }

        .input-group input[type="text"] {
            width: 100%;
            height: 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0 12px;
        }

        .search-section button {
            min-width: 120px;
            margin: 0 !important;
            background-color: #1a2238;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-section button:hover {
            background-color: #5c79ca;
            transform: translateY(-1px);
        }

        .search-section button:active {
        transform: translateY(1px);
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
                gap: 15px;
            }

            .search-input-container {
                width: 100%;
            }

            .input-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                margin-right: 0;
            }

            .input-group input[type="text"] {
                padding-left: 35px;
            }

            .search-section button:hover {
                transform: none;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .input-group {
                flex-direction: column;
                gap: 15px;
            }

            .search-input-container {
                width: 100%;
            }

            /* Adjust icon positioning for stacked inputs */
            .input-group .fa-location-dot {
                left: 12px; /* Reset to same position as search icon when stacked */
            }

            .search-section button {
                width: 100%;
                margin-top: 5px !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <br><br>

    <main class="main-content">
        <!-- Search Bar -->
        <div class="search-section">
            <form id="searchForm" action="results.php" method="GET">
                <div class="input-group">
                    <div class="search-input-container">
                        <i class="fas fa-search input-icon"></i>
                        <input type="text" name="name" placeholder="Search by name" id="searchName">
                    </div>
                    <div class="search-input-container">
                        <i class="fas fa-location-dot input-icon"></i>
                        <input type="text" name="location" placeholder="Search by location" id="searchLocation">
                    </div>
                    <button type="submit" id="searchButton">Search</button>
                </div>
            </form>
        </div>
        <br>
        <!-- Nearest Section -->
        <section class="tuition-section">
            <h2 class="section-title">
                <i class="fas fa-location-crosshairs"></i>
                Nearest Tuition Centers
            </h2>
            <div class="tuition-center-grid" id="recommendation-grid">
                <?php if ($nearest_result->num_rows > 0): ?>
                    <?php while ($center = $nearest_result->fetch_assoc()): ?>
                        <div class="tuition-center-card" data-center-id="<?php echo $center['id']; ?>">
                            <img src="<?php echo htmlspecialchars($center['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($center['name']); ?>">
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($center['name']); ?></h3>
                                <p class="distance">
                                    <i class="fas fa-location-dot"></i> 
                                    <?php echo number_format($center['distance'], 1); ?> km
                                </p>
                                <p class="rating">
                                    <i class="<?php echo ($center['avg_rating'] > 0) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php echo number_format($center['avg_rating'], 1); ?>/5
                                    <span class="review-count">
                                        <i class="fas fa-comment"></i>
                                        <?php echo (int)$center['review_count']; ?>
                                    </span>
                                </p>
                                <a href="tuition_details.php?id=<?php echo $center['id']; ?>" 
                                   class="btn btn-primary details-btn">Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-results">No tuition centers found nearby.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Top Rated Section -->
        <section class="tuition-section">
            <h2 class="section-title">
                <i class="fas fa-star"></i> 
                Top Rated Centers
            </h2>
            <div class="tuition-center-grid">
                <?php while ($center = $top_result->fetch_assoc()): ?>
                    <div class="tuition-center-card">
                        <!-- Display the tuition center image -->
                        <img src="<?php echo htmlspecialchars($center['image']); ?>" 
                             alt="<?php echo htmlspecialchars($center['name']); ?>">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($center['name']); ?></h3>
                            <!-- Display the rating -->
                            <div class="rating-container">
                                <?php
                                $rating = round($center['avg_rating'], 1);
                                $fullStars = floor($rating);
                                $halfStar = $rating - $fullStars >= 0.5;
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                
                                // Loop through the full stars
                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fas fa-star"></i>';
                                }
                                // Display a half star if applicable
                                if ($halfStar) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                }
                                // Loop through the empty stars
                                for ($i = 0; $i < $emptyStars; $i++) {
                                    echo '<i class="far fa-star"></i>';
                                }
                                ?>
                                <span class="rating-text"><?php echo number_format($rating, 1); ?></span>
                            </div>
                            <span class="review-count">
                                <i class="fas fa-comment"></i>
                                <?php echo (int)$center['review_count']; ?> reviews
                            </span>
                            <!-- Link to the tuition center details page -->
                            <a href="tuition_details.php?id=<?php echo $center['id']; ?>" 
                               class="btn btn-primary details-btn">Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>

    <script>
    $(document).ready(function() {
        
        // Function to handle adding/removing favorites
        function initializeFavoriteButtons() {
            $('.favorite-btn').click(function(e) {
                e.preventDefault();
                
                // Check if user is logged in
                if ($(this).attr('data-guest')) {
                    alert('Please login to add favorites!');
                    return;
                }

                var centerId = $(this).data('center-id');
                var $button = $(this);

                // Send an AJAX request to toggle the favorite status (AJAX means asynchronous JavaScript and XML)
                $.ajax({
                    // URL of the PHP script that handles the favorite toggle
                    url: 'toggle_favorite.php',
                    // HTTP method to use for the request
                    type: 'POST',
                    // Data to send to the server
                    data: { center_id: centerId },
                    // Expected data type from the server
                    dataType: 'json',
                    success: function(response) {
                        // Check if the favorite toggle was successful
                        if (response.success) {
                            // Toggle the active class on the button
                            $button.toggleClass('active');
                            // Display a message based on the action
                            if (response.action === 'added') {
                                alert('Added to favorites!');
                            } else {
                                alert('Removed from favorites!');
                            }
                        } else {
                            // Display an error message if the favorite toggle fails
                            alert('Error: ' + response.error);
                        }
                    },
                    // Handle any errors
                    error: function() {
                        alert('Error toggling favorite status.');
                    }
                });
            });
        }

        // Initialize favorite buttons
        initializeFavoriteButtons();
    });

    // Lightbox functions
    function closeLightbox() {
        document.getElementById("helpLightbox").style.display = "none";
    }

    // Function to fetch notifications
    function fetchNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                // Get the notification list and count elements
                const notificationsList = document.getElementById('notificationsList');
                const notificationCount = document.getElementById('notificationCount');
                // Clear the notification list
                notificationsList.innerHTML = '';
                // Initialize unread count
                let unreadCount = 0;

                // Check if there are no notifications
                if (data.length === 0) {
                    notificationsList.innerHTML = '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                } else {
                    // Loop through each notification
                    data.forEach(notification => {
                        const li = document.createElement('li');
                        li.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
                        li.innerHTML = `
                            <a class="dropdown-item" href="#" data-notification-id="${notification.id}">
                                ${notification.message}
                            </a>
                        `;
                        // Append the notification item to the list
                        notificationsList.appendChild(li);
                        // Increment unread count if the notification is unread
                        if (!notification.is_read) {
                            unreadCount++;
                        }
                    });
                }
                // Update the notification count
                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount;
                    notificationCount.style.display = 'inline';
                } else {
                    notificationCount.style.display = 'none';
                }

                // Add click event listeners to mark notifications as read
                notificationsList.querySelectorAll('.notification-item a').forEach(item => {
                    item.addEventListener('click', markAsRead);
                });
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Function to mark a notification as read
    function markAsRead(event) {
        event.preventDefault();
        const notificationId = event.target.dataset.notificationId;
    
        // Send a POST request to mark the notification as read
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            // Check if the notification was marked as read successfully
            if (data.success) {
                event.target.parentElement.classList.remove('unread');
                fetchNotifications(); // Refresh the notifications
            } else {
                console.error('Error marking notification as read:', data.error);
            }
        })
        // Handle any errors
        .catch(error => console.error('Error marking notification as read:', error));
    }

    // Fetch notifications every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Initial fetch
    document.addEventListener('DOMContentLoaded', fetchNotifications);

    // Set the user's location in the search bar
    document.addEventListener('DOMContentLoaded', function() {
        if (navigator.geolocation) {
            // Get the user's current position
            navigator.geolocation.getCurrentPosition(function(position) {
                // Reverse geocode the user's position to get the city name
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                    .then(response => response.json())
                    .then(data => {
                        // Check if the response contains an address and a city
                        if (data.address && data.address.city) {
                            // Get the location select element
                            const locationSelect = document.getElementById('searchLocation');
                            // Find the city option in the location select element
                            const cityOption = Array.from(locationSelect.options).find(option => option.text === data.address.city);
                            if (cityOption) {
                                cityOption.selected = true;
                            }
                        }
                    });
            });
        }

        /*// Handle search form submission
        const searchForm = document.getElementById('searchForm');
        
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const searchName = document.getElementById('searchName').value;
                const searchLocation = document.getElementById('searchLocation').value;

                // Redirect to results.php with search parameters
                window.location.href = `results.php?name=${encodeURIComponent(searchName)}&location=${encodeURIComponent(searchLocation)}`;
            });
        }*/
    });

    // Set the user's location in the search bar
    document.addEventListener('DOMContentLoaded', function() {
        getLocation();
    });

    // Show welcome modal when page loads (only for logged-in users)
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['user_id'])): ?>
        const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'), {
            backdrop: 'static',  // Optional: prevents closing when clicking outside
            keyboard: true       // Allows closing with Esc key
        });
        // Show the modal
        welcomeModal.show();
        // Add event listener for the close button
        document.querySelector('#welcomeModal .btn-close').addEventListener('click', function() {
            welcomeModal.hide();
        });
        
        // Add event listener for the "Got it!" button
        document.querySelector('#welcomeModal .btn-primary').addEventListener('click', function() {
            welcomeModal.hide();
        });
        <?php endif; ?>
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Function to refresh center data
        function refreshCenterData() {
            // Get user's location from session
            const userLat = <?php echo $_SESSION['user_lat'] ?? 0; ?>;
            const userLon = <?php echo $_SESSION['user_lon'] ?? 0; ?>;

            // Use your existing fetch_tuition.php endpoint
            fetch('fetch_tuition.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `lat=${userLat}&lon=${userLon}`
            })
            .then(response => response.json())
            .then(data => {
                const grid = document.getElementById('recommendation-grid');
                if (grid) {
                    data.forEach(center => {
                        const cards = grid.querySelectorAll('.tuition-center-card');
                        cards.forEach(card => {
                            const cardId = card.dataset.centerId;
                            if (cardId === center.id) {
                                const ratingEl = card.querySelector('.rating');
                                if (ratingEl) {
                                    ratingEl.innerHTML = `
                                        <i class="${center.avg_rating > 0 ? 'fas' : 'far'} fa-star"></i>
                                        ${parseFloat(center.avg_rating).toFixed(1)}/5
                                    `;
                                }
                            }
                        });
                    });
                }
            })
            .catch(error => console.error('Error refreshing center data:', error));
        }

        // Refresh data every 30 seconds
        setInterval(refreshCenterData, 30000);
    });
    </script>
</body>
</html>
