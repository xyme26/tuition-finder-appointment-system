<!-- This file handles the notification settings for the user -->
<!-- The purpose of this file is to update the notification settings for the user in the database -->
<?php
session_start();
$current_page = 'profile';
include 'connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch user details and notification preferences
$query = "SELECT fav_update, upcoming_appointment, appointment_confirmation FROM users WHERE id = ?";

// Prepare and execute the SQL query
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Fetch the user details
    $row = $result->fetch_assoc();
    $fav_update = $row['fav_update'];
    $upcoming_appointment = $row['upcoming_appointment'];
    $appointment_confirmation = $row['appointment_confirmation'];
} else {
    // Display an error message if there are no user details
    echo "Error retrieving user details.";
    exit();
}

// Update notification preferences on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fav_update = isset($_POST['fav_update']) ? 1 : 0;
    $upcoming_appointment = isset($_POST['upcoming_appointment']) ? 1 : 0;
    $appointment_confirmation = isset($_POST['appointment_confirmation']) ? 1 : 0;

    $update_query = "UPDATE users SET fav_update = ?, upcoming_appointment = ?, appointment_confirmation = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("iiii", $fav_update, $upcoming_appointment, $appointment_confirmation, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Notification preferences updated successfully.";
    } else {
        $error_message = "Error updating notification preferences.";
    }
}
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

.form-check {
    display: flex; /* Use flexbox for better alignment */
    align-items: center; /* Center items vertically */
    margin-bottom: 15px; /* Space between form checks */
}

.form-check-input {
    display: none; /* Hide the default checkbox */
}

.form-check-label {
    position: relative;
    padding-left: 35px; /* Space for toggle */
    cursor: pointer;
    user-select: none;
    margin-right: 10px; /* Space between label and toggle */
}

.form-check-label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 20px;
    width: 35px;
    background-color: #ccc;
    border-radius: 25px;
    transition: background-color 0.3s;
}

.form-check-input:checked + .form-check-label::before {
    background-color: #007bff; /* Change this to your preferred color */
}

.form-check-label::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 5px;
    height: 10px;
    width: 10px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.form-check-input:checked + .form-check-label::after {
    transform: translateX(20px);
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
                <li><a href="notifications.php" class="active">Notifications</a></li>
                <li><a href="review_history.php">Review History</a></li>
                <li><a href="favorite_history.php">Favorite History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Profile details -->
        <div class="profile-details">
            <h2>Notification Settings</h2>

            <!-- Display success or error messages -->
            <?php
            if (isset($success_message)) {
                echo "<p class='alert alert-success'>$success_message</p>";
            }
            if (isset($error_message)) {
                echo "<p class='alert alert-danger'>$error_message</p>";
            }
            ?>

            <!-- Form for notification settings -->
            <form method="POST" action="notifications.php">
                <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fav_update" id="fav_update" value="1" 
            <?php echo $fav_update ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="fav_update">Favorite Updates</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="upcoming_appointment" id="upcoming_appointment" value="1" 
                    <?php echo $upcoming_appointment ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="upcoming_appointment">Upcoming Appointments</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="appointment_confirmation" id="appointment_confirmation" value="1" 
                    <?php echo $appointment_confirmation ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="appointment_confirmation">Appointment Confirmation</label>
                </div>
                <button type="submit" class="btn btn-primary">Update Notifications</button>
            </form>
        </div>

    </div>
    <?php include 'footer.php'; ?>

    <script>
        // Function to toggle individual notification settings  
        function toggleNotification(checkbox, name) {
            const allCheckbox = document.getElementById('subscribe_all');
            if (checkbox.checked) {
                allCheckbox.checked = false; // Uncheck "Allow All Notifications" if individual is checked
            }
        }

        // Function to toggle all notification settings
        function toggleAllNotifications(checkbox) {
            // Get the checkboxes for individual notifications
            const favUpdate = document.getElementById('fav_update');
            const upcomingAppointment = document.getElementById('upcoming_appointment');
            const appointmentConfirmation = document.getElementById('appointment_confirmation');

            // Check/uncheck all notifications based on "Allow All Notifications"
            favUpdate.checked = checkbox.checked;
            upcomingAppointment.checked = checkbox.checked;
            appointmentConfirmation.checked = checkbox.checked;

            // Disable/enable other checkboxes based on "Allow All Notifications"
            favUpdate.disabled = checkbox.checked;
            upcomingAppointment.disabled = checkbox.checked;
            appointmentConfirmation.disabled = checkbox.checked;
        }

        // Add event listener to the DOM content loaded (for survey answers)
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
</head>
</html>
