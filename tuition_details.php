<?php
session_start(); 
$current_page = 'tuition_details';
include 'connection.php'; // Database connection

// Check if tuition center ID is passed in the URL
if (isset($_GET['id'])) { // Ensure the parameter name matches
    $tuition_center_id = $_GET['id'];

    // Near the top of the file, after getting the user's location
    $user_lat = $_SESSION['user_lat'] ?? 0;
    $user_lon = $_SESSION['user_lon'] ?? 0;

    // Modify the SQL query
    $sql = "SELECT t.*, 
                   (6371 * acos(cos(radians(?)) * cos(radians(t.latitude)) * cos(radians(t.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(t.latitude)))) AS distance
            FROM tuition_centers t 
            WHERE t.id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    // Bind the parameters to the statement
    $stmt->bind_param("dddi", $user_lat, $user_lon, $user_lat, $tuition_center_id);
    // Execute the statement
    $stmt->execute();
    // Get the result
    $result = $stmt->get_result();

    // Check if the result has any rows
    if ($result->num_rows > 0) {
        // Fetch the tuition center details
        $tuition = $result->fetch_assoc();
        $tuition['distance'] = $tuition['distance'] ? number_format($tuition['distance'], 2) . ' km' : 'N/A';
    } else {
        // Display an error message if no tuition center is found
        echo "No tuition center found!";
        exit;
    }
    // Close the statement
    $stmt->close();
} else {
    // Display an error message if the request is invalid
    echo "Invalid request!";
    exit;
}

// Fetch reviews for this tuition center along with the username of the reviewer
$sql = "
    SELECT r.rating, r.comment, r.created_at, u.username, r.liked_by_admin, r.reply
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.tuition_center_id = ?
";

// Prepare the statement
$stmt = $conn->prepare($sql);
// Bind the parameters to the statement
$stmt->bind_param("i", $tuition_center_id);
// Execute the statement
$stmt->execute();
// Get the result
$reviews = $stmt->get_result();

// Fetch the average rating
$sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE tuition_center_id = ?";
// Prepare the statement
$stmt = $conn->prepare($sql);
// Bind the parameters to the statement
$stmt->bind_param("i", $tuition_center_id);
// Execute the statement
$stmt->execute();
// Get the result
$result = $stmt->get_result();
// Fetch the average rating
$row = $result->fetch_assoc();
// Format the average rating
$averageRating = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tuition['name']); ?> - Tuition Center Details</title>
     <!-- Link to Bootstrap CSS for styling -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />   

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    <style>
        #container {
            width: 90%;
            max-width: 1200px; /* Add a max-width to prevent stretching on large screens */
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box; /* Include padding in width calculation */
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap; /* Allow items to wrap on smaller screens */
            width: 100%;
        }

        .header-section h2 {
            margin: 0;
            flex: 1;
            min-width: 200px; /* Minimum width before wrapping */
            word-wrap: break-word; /* Allow long words to break */
        }

        .rating-box {
            display: inline-flex;
            align-items: center;
            background-color: #1a2238;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            margin-left: 20px;
            white-space: nowrap; /* Keep rating box content on one line */
        }

        /* Adjust responsive styling */
        @media screen and (max-width: 768px) {
            #container {
                width: 95%; /* Slightly wider on mobile */
                padding: 10px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .rating-box {
                margin-left: 0;
                margin-top: 10px;
            }
        }

        .tuition-image {
            width: 40%;
            float: left;
            padding-right: 15px; /* Reduced padding */
        }

        .tuition-details {
            width: 60%;
            float: right;
            padding-left: 15px; /* Reduced padding */
        }

        .tuition-details h2 {
            font-size: 2.5rem;
            color: #1a2238;
            margin-bottom: 10px;
        }

        .tuition-details p {
            margin-bottom: 10px;
        }

        .tuition-description {
            clear: both;
            padding-top: 20px;
        }

        .booking-btn {
            margin-top: 20px;
            display: inline-block;
            margin-right: 10px;
        }
        .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f4f4f4;
        }

        .reviews-header h4 {
            margin: 0;
            color: #1a2238;
            font-size: 1.5rem;
        }

        .reviews-header .review-btn {
            background-color: #1a2238;
                color: white;
            padding: 8px 16px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .reviews-section .card {
            height: 100%;
        }

        .reviews-section .card-body {
            display: flex;
            flex-direction: column;
        }

        .reviews-section .card-text {
            flex-grow: 1;
        }

        .tuition-details p strong {
            width: 100%; /* Adjust this value to align the labels */
            vertical-align: top;
        }

        .tuition-details p span {
            display: inline-block;
            width: calc(100% - 145px); /* Adjust based on the width of the strong tag */
        }

        /* Media Queries */
        @media screen and (max-width: 768px) {
        .tuition-content {
            flex-direction: column;
        }

        .tuition-image,
        .tuition-details {
            width: 100%;
        }

        .reviews-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .booking-btn {
            width: 100%;
            max-width: none;
        }

        .header-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .rating-box {
            margin-left: 0;
            margin-top: 10px;
        }

        #container {
            width: 95%; /* Slightly wider on mobile */
            padding: 10px;
        }

        .header-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .rating-box {
            margin-left: 0;
            margin-top: 10px;
        }
    }

    @media screen and (max-width: 480px) {
        .container {
            padding: 10px;
        }

        .tuition-details h2 {
            font-size: 1.8rem;
        }

        .booking-btn {
            padding: 10px 20px;
            font-size: 0.9rem;
        }
    }

    /* Favorite button styling */
    .favorite-btn-large {
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .favorite-btn-large.active {
        background-color: #1a2238;
        border-color: #1a2238;
        color: white;
    }

    .favorite-btn-large:not(.active):hover {
        background-color: #9daaf2;
        border-color: #1a2238;
        color: #1a2238;
    }

    .favorite-btn-large.active:hover {
        background-color: #9daaf2;
        border-color: #1a2238;
    }

    .rating-score {
        font-size: 24px;
        font-weight: bold;
        margin-right: 12px;
        padding-right: 12px;
        border-right: 2px solid rgba(255, 255, 255, 0.3);
    }

    .rating-details {
        display: flex;
        flex-direction: column;
    }

    .rating-details .star-rating {
        color: #ffffff; 
        font-size: 14px;
    }

    .review-count {
        font-size: 12px;
        opacity: 0.9;
        margin-top: 2px;
    }

    .travel-time {
        font-weight: bold;
        color: #0d53af; 
        font-size: 1.2rem; /* Increase the font size */
        background-color: #f0f8ff; /* Light background color for contrast */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 5px 10px; /* Add some padding around the text */
        border-radius: 5px; /* Rounded corners */
        border-style: solid;
        border-color: #1a2238;
        display: inline-block; /* Make it inline-block to respect padding */
        max-width: max-content;
    }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <br><br>
    <div id="container">
        <!-- Tuition Center Details -->
        <div class="clearfix">
            <div class="tuition-image">
                <img src="<?php echo htmlspecialchars($tuition['image']); ?>" alt="Tuition Center Image" class="img-fluid mb-3">
            </div>

            <!-- Tuition Center Details -->
            <div class="tuition-details">
                <div class="header-section">
                    <h2><?php echo htmlspecialchars($tuition['name']); ?></h2>
                    <div class="rating-box">
                        <div class="rating-score"><?php echo number_format($averageRating, 1); ?></div>
                        <div class="rating-details">
                            <div class="star-rating" data-rating="<?php echo number_format($averageRating, 1); ?>"></div>
                            <div class="review-count"><?php echo $reviews->num_rows; ?> reviews</div>
                        </div>
                    </div>
                </div>
                <p><strong><i class="fas fa-map-marker-alt"></i> Address:</strong> <span><?php echo htmlspecialchars($tuition['address']); ?></span></p>
                <p><strong><i class="fa-solid fa-location-dot"></i> Distance:</strong> <span><?php echo htmlspecialchars($tuition['distance']); ?></span></p>
                <p><strong><i class="fas fa-clock"></i> Estimated Travel Time: (By car)</strong> <span id="travel-time" class="travel-time">Calculating...</span></p>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($tuition['address']); ?>" 
                    class="btn btn-sm btn-secondary mb-2 google-maps-btn" target="_blank" rel="noopener noreferrer">
                     <i class="fas fa-map-marker-alt"></i> View on Google Maps
                </a>
                <p><strong><i class="fas fa-phone"></i> Contact: </strong> 
                    <span>
                        <a href="tel:+60<?php echo htmlspecialchars(ltrim($tuition['contact'], '0')); ?>" class="contact-link">
                            <?php echo htmlspecialchars($tuition['contact']); ?>
                        </a>
                        |
                        <a href="https://wa.me/60<?php echo htmlspecialchars(ltrim($tuition['contact'], '0')); ?>" target="_blank" rel="noopener noreferrer" class="contact-link">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </span>
                </p>
                <p><strong><i class="fas fa-book"></i> Subjects Offered: </strong> <span><?php echo htmlspecialchars($tuition['course_tags']); ?></span></p>
                <p><strong><i class="fas fa-language"></i> Teaching Languages: </strong> <span><?php 
                    // Split the teaching languages into an array
                    $languages = explode(',', $tuition['teaching_language']);
                    // Define the language icons
                    $languageIcons = [
                        'English' => '<i class="fas fa-flag-usa"></i>',
                        'Bahasa Malaysia' => '<i class="fas fa-flag"></i>',
                        'Chinese' => '<i class="fas fa-yen-sign"></i>'
                    ];
                    
                    // Map the languages to their icons
                    $formattedLanguages = array_map(function($lang) use ($languageIcons) {
                        $icon = isset($languageIcons[trim($lang)]) ? $languageIcons[trim($lang)] . ' ' : '';
                        return $icon . trim($lang);
                    }, $languages);
                    // Display the formatted languages
                    echo htmlspecialchars(implode(' | ', $languages));
                ?></span></p>
                <p><strong><i class="fas fa-money-bill-wave"></i> Price Range: RM</strong> <span><?php echo htmlspecialchars($tuition['price_range']); ?></span></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Favorite Button -->
                    <button class="btn btn-outline-primary favorite-btn-large 
                    <?php 
                        // Prepare the statement
                        $checkFav = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
                        // Bind the parameters to the statement
                        $checkFav->bind_param("ii", $_SESSION['user_id'], $tuition_center_id);
                        // Execute the statement
                        $checkFav->execute();
                        // Display the active class if the favorite exists
                        echo $checkFav->get_result()->num_rows > 0 ? 'active' : '';
                    ?>" data-center-id="<?php echo $tuition_center_id; ?>">
                        <i class="fas fa-heart"></i> 
                        <span class="favorite-text">
                            <?php 
                            // Execute the statement
                            $checkFav->execute();
                            // Display the text based on whether the favorite exists
                            echo $checkFav->get_result()->num_rows > 0 ? 'Remove from Favorites' : 'Add to Favorites';
                            ?>
                        </span>
                    </button>
                <?php else: ?>
                    <!-- Login to Favorite Button -->
                    <button class="btn btn-outline-primary" disabled>
                        <i class="fas fa-heart"></i> Login to Favorite
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <div class="tuition-description mt-3">
            <h4>Description</h4>
            <p><?php echo htmlspecialchars($tuition['description']); ?></p>
        </div>
                
        <!-- Favorite Button -->
        <button class="favorite-btn <?php   
            // Prepare the statement
            $checkFav = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND tuition_center_id = ?");
            // Bind the parameters to the statement
            $checkFav->bind_param("ii", $_SESSION['user_id'], $tuition_center_id);
            // Execute the statement
            $checkFav->execute();
            // Display the active class if the favorite exists
            echo $checkFav->get_result()->num_rows > 0 ? 'active' : '';
            ?>" data-center-id="<?php echo $tuition_center_id; ?>">
            <i class="fas fa-heart"></i> Favorite
        </button>
        <!-- Book an Appointment -->
        <button type="button" class="btn btn-primary booking-btn" data-bs-toggle="modal" data-bs-target="#appointmentModal">
        <i class="fas fa-calendar-plus"></i> Book Appointment
        </button>

        <!-- Appointment Modal -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">Book Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="appointment-form">
                            <input type="hidden" name="tuition_center_id" value="<?php echo $tuition_center_id; ?>">
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="appointmentTime" name="time" required>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentReason" class="form-label">Reason</label>
                                <textarea class="form-control" id="appointmentReason" name="reason" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Book Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="review-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reviewModalLabel">Submit Your Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="review-rating" class="form-label">Rating:*</label>
                            <select class="form-control" id="review-rating" name="rating" required>
                                <option value="">Select rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="review-comment" class="form-label">Comment:*</label>
                            <textarea class="form-control" id="review-comment" name="comment" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Display Reviews -->
    <div class="reviews-section mt-4">
        <div class="reviews-header">
            <h4>Reviews</h4>
            <!-- Review Button -->
            <button type="button" class="btn btn-success review-btn" data-bs-toggle="modal" data-bs-target="#reviewModal">
                <i class="far fa-edit"></i> Write a Review
            </button>
        </div>
        <!-- Display the reviews if there are any -->
        <?php if ($reviews->num_rows > 0): ?>
            <div class="row">
                <?php 
                // Initialize the count
                $count = 0;
                // Fetch the reviews
                while ($review = $reviews->fetch_assoc() and $count < 6): 
                ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($review['username']); ?></h5>
                                <div class="star-rating" data-rating="<?php echo htmlspecialchars($review['rating']); ?>"></div>
                                <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                                <p class="card-text"><small class="text-muted">Reviewed on: <?php echo htmlspecialchars($review['created_at']); ?></small></p>
                                <?php if (!empty($review['reply'])): ?>
                                    <p class="admin-reply"><strong>Admin Reply:</strong> <?php echo htmlspecialchars($review['reply']); ?></p>
                                <?php endif; ?>
                                <?php if ($review['liked_by_admin']): ?>
                                    <p class="admin-like"><i class="fas fa-thumbs-up"></i> Liked by admin</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                // Increment the count
                $count++;
                // End the loop
                endwhile; 
                ?>
            </div>
            <!-- Display the view all reviews button if there are more than 6 reviews -->
            <?php if ($reviews->num_rows > 6): ?>
                <!-- Display the view all reviews button -->
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-primary">View All Reviews</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Display a message if there are no reviews -->
            <p>No reviews yet. Be the first to write one!</p>
        <?php endif; ?>
    </div>
</div>

    <?php include 'footer.php'; ?>

    <script>
        // Handle appointment form submission
        document.getElementById('appointment-form').addEventListener('submit', function (event) {
            // Prevent the default form submission
            event.preventDefault();
                
            // Get the values from the form for the appointment date, time, and reason
            const date = document.getElementById('appointment-date').value;
            const time = document.getElementById('appointment-time').value;
            const reason = document.getElementById('appointment-reason').value;
            const tuition_center_id = <?php echo json_encode($tuition_center_id); ?>;

            // Simple validation for the appointment date, time, and reason
            if (!date || !time || !reason) {
                alert("Please select a date, time, and provide a reason."); // Alert if validation fails
                return;
            }

            // Prepare the data to send
            const appointmentData = {
                date: date,
                time: time,
                reason: reason,
                tuition_center_id: tuition_center_id
            };

            // Log the appointment data being sent
            console.log('Appointment data being sent:', appointmentData);
            // Send the data to the backend via AJAX (using Fetch API)
            fetch('book_appointment.php', {
                // Specify the method as POST
                method: 'POST',
                // Set the content type to JSON
                headers: {
                'Content-Type': 'application/json',
            },
                // Send the data as a JSON string
                body: JSON.stringify(appointmentData)
            })
            // Handle the response from the backend
            .then(response => response.json())
            .then(data => {
                // Display a success message if the appointment was booked successfully
                if (data.success) {
                    alert(`Appointment booked for ${date} at ${time}. Reason: ${reason}`);
                    // Reset the form
                    document.getElementById('appointment-form').reset();
                    // Close the modal
                    var appointmentModal = bootstrap.Modal.getInstance(document.getElementById('appointmentModal'));
                    appointmentModal.hide();
                } else {
                    // Display an error message if the appointment was not booked successfully
                    alert(`Error: ${data.message}`);
                }
        })
        .catch((error) => {
            console.error('Fetch error:', error);
                // Display an error message if an error occurred while booking the appointment
                alert('An error occurred while booking the appointment.');
            });
        });

    // Handle review form submission
    document.getElementById('review-form').addEventListener('submit', function (event) {
        event.preventDefault();

        // Get form values
        const rating = document.getElementById('review-rating').value;
        const comment = document.getElementById('review-comment').value;

        // Simple validation for the rating and comment
        if (!rating || !comment) {
            alert("Please provide a rating and comment.");
            return;
        }

        // Prepare the data to send
        const reviewData = {
            // Include the tuition center ID
            tuition_center_id: "<?php echo $tuition_center_id; ?>",  
            rating: rating,
            comment: comment
        };

        // Send data to the backend via AJAX (using Fetch API)
        fetch('submit_reviews.php', {
            // Specify the method as POST
            method: 'POST',
            // Set the content type to JSON
            headers: {
                'Content-Type': 'application/json',
            },
            // Send the data as a JSON string
            body: JSON.stringify(reviewData),
        })
        // Handle the response from the backend
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Review submitted successfully!');
                // Reset form
                document.getElementById('review-form').reset();
                // Close the modal
                var reviewModal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
                reviewModal.hide();
            } else {
                // Display an error message if the review was not submitted successfully
                alert('Error: ' + (data.message || 'Failed to submit review'));
            }
        })
        .catch(error => {
            // Log the error
            console.error('Error:', error);
            // Display an error message if an error occurred while submitting the review
            alert('An error occurred while submitting the review.');
        });
    });

    // Initialize star ratings
    document.addEventListener('DOMContentLoaded', function() {
        // Loop through each star rating element
        document.querySelectorAll('.star-rating').forEach(function(ratingElement) {
            // Get the rating from the data attribute
            const rating = parseFloat(ratingElement.dataset.rating);
            // Log the raw rating and the parsed rating
            console.log('Raw rating:', ratingElement.dataset.rating);
            // Log the parsed rating
            console.log('Parsed rating:', rating);

            // Check if the rating is NaN
            if (isNaN(rating)) {
                // Log an error message if the rating is NaN
                console.error('Invalid rating:', ratingElement.dataset.rating);
                // Return if the rating is NaN
                return;
            }

            // Initialize the HTML for the star rating
            let starsHtml = '';
            // Loop through each star from 1 to 5
            for (let i = 1; i <= 5; i++) {
                // Add a full star if the current star is less than or equal to the rating
                if (i <= rating) {
                    starsHtml += '<i class="fas fa-star"></i>';
                } else if (i - 0.5 <= rating) {
                    // Add a half star if the current star minus 0.5 is less than or equal to the rating
                    starsHtml += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    // Add a blank star if none of the above conditions are met
                    starsHtml += '<i class="far fa-star"></i>';
                }
            }
            // Add the rating to the HTML
            starsHtml += ` (${rating.toFixed(1)})`;
            // Set the inner HTML of the rating element to the stars HTML
            ratingElement.innerHTML = starsHtml;
        });
    });

    // Handle appointment form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Get the date and time input elements
        const dateInput = document.getElementById('appointmentDate');
        const timeInput = document.getElementById('appointmentTime');

        // Add event listeners to the date and time input elements
        dateInput.addEventListener('change', updateTimeSlots);
        timeInput.addEventListener('change', validateTimeSlot);

        // Function to update the time slots based on the selected date
        function updateTimeSlots() {
            // Get the selected date
            const selectedDate = new Date(dateInput.value);
            // Get the day of the week (0 for Sunday, 1 for Monday, etc.)
            const dayOfWeek = selectedDate.getDay();

            // Clear existing time
            timeInput.value = '';

            // Disable time input on weekends
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                // Disable the time input if it's a weekend
                timeInput.disabled = true;
                // Display an alert if it's a weekend
                alert('Appointments are only available on weekdays');
            } else {
                // Enable the time input if it's a weekday
                timeInput.disabled = false;
            }
        }

        // Function to validate the selected time slot
        function validateTimeSlot() {
            // Get the selected time
            const selectedTime = timeInput.value;
            // Split the time into hours and minutes
            const [hours, minutes] = selectedTime.split(':').map(Number);

            // Check if the selected time is between 11 AM and 5 PM
            if (hours < 11 || hours >= 17 || (hours === 16 && minutes > 30)) {
                alert('Appointments are only available between 11 AM and 5 PM');
                // Clear the time input if the selected time is not valid
                timeInput.value = '';
                // Return if the selected time is not valid
                return;
            }

            // Check if the selected time is not on the hour or half-hour
            if (minutes !== 0 && minutes !== 30) {
                alert('Appointments must be booked on the hour or half-hour');
                // Clear the time input if the selected time is not valid
                timeInput.value = '';
                // Return if the selected time is not valid
                return;
            }
        }

        // Add event listener to the appointment form
        document.getElementById('appointment-form').addEventListener('submit', function(e) {
            // Prevent the default form submission
            e.preventDefault(); 
            // Get the form data
            var formData = new FormData(this);
            // Prepare the data to send
            var appointmentData = {
                tuition_center_id: formData.get('tuition_center_id'),
                date: formData.get('date'),
                time: formData.get('time'),
                reason: formData.get('reason')
            };

            // Send the data to the backend via AJAX (using Fetch API)
            fetch('book_appointment.php', {
                // Specify the method as POST
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(appointmentData)
            })
            // Handle the response from the backend
            .then(response => response.json())
            .then(data => {
                // Display a success message if the appointment was booked successfully
                if (data.success) {
                    alert('Appointment booked successfully!');
                    // Close the modal
                    $('#appointmentModal').modal('hide');
                    // Reset the form
                    this.reset();
                } else {
                    // Display an error message if the appointment was not booked successfully
                    alert('Failed to book appointment: ' + data.message);
                }
            })
            // Handle any errors that occurred during the fetch operation   
            .catch(error => {
                // Log the error
                console.error('Error:', error);
                // Display an error message if an error occurred while booking the appointment
                alert('An error occurred while booking the appointment');
            });
        });
    });

    // Handle favorite button clicks
    document.querySelectorAll('.favorite-btn-large').forEach(button => {
        // Add event listener to the favorite button
        button.addEventListener('click', function() {
            // Get the center ID and check if it's favorited
            const centerId = this.dataset.centerId;
            const isFavorited = this.classList.contains('active');

            // Send the data to the backend via AJAX (using Fetch API)
            fetch('toggle_favorite.php', {
                // Specify the method as POST
                method: 'POST',
                // Set the content type to URL-encoded form data
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                // Send the data as URL-encoded form data
                body: `center_id=${centerId}&action=${isFavorited ? 'remove' : 'add'}`
            })
            // Handle the response from the backend
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle the active class on the favorite button
                    this.classList.toggle('active');
                    // Update the text of the favorite button
                    const textSpan = this.querySelector('.favorite-text');
                    textSpan.textContent = isFavorited ? 'Add to Favorites' : 'Remove from Favorites';
                } else {
                    // Display an error message if the favorite status was not updated successfully
                    alert('Error updating favorite status');
                }
            })
            .catch(error => {
                // Log the error
                console.error('Error:', error);
                // Display an error message if an error occurred while updating the favorite status
                alert('An error occurred while updating favorite status');
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Get user's current location from session
        const userLat = <?php echo json_encode($_SESSION['user_lat'] ?? 0); ?>; // User's latitude
        const userLng = <?php echo json_encode($_SESSION['user_lon'] ?? 0); ?>; // User's longitude
        const tuitionAddress = "<?php echo htmlspecialchars($tuition['address']); ?>"; // Tuition center's address

        // Function to fetch travel time from user's location to the tuition center
        function fetchTravelTime() {
            const service = new google.maps.DistanceMatrixService(); // Create a new DistanceMatrixService instance
            service.getDistanceMatrix({
                origins: [{ lat: userLat, lng: userLng }], // Set the origin to the user's location
                destinations: [tuitionAddress], // Set the destination to the tuition center's address
                travelMode: 'DRIVING', // Driving mode
                unitSystem: google.maps.UnitSystem.METRIC, // Use metric units for distance
            }, (response, status) => {
                // Callback function to handle the response from the Distance Matrix API
                if (status === 'OK') {
                    const travelTime = response.rows[0].elements[0].duration.text; // Get the travel time from the response
                    document.getElementById('travel-time').innerText = travelTime; // Display the travel time in the designated HTML element
                } else {
                    console.error('Error fetching travel time:', status); // Log an error if the request fails
                    document.getElementById('travel-time').innerText = 'Unable to calculate'; // Fallback message if travel time cannot be calculated
                }
            });
        }

        // Call the function to fetch travel time when the page loads
        fetchTravelTime();
    });
</script>
</body>
</html>
