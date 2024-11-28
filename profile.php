<?php
session_start();
$current_page = 'profile';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'connection.php';

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$user = null;

// Prepare and execute the SQL query
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is exactly one row returned
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error preparing statement.";
    exit;
}

// Close the connection
$conn->close();
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
    <br>

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Left navigation with custom design -->
        <div class="left-nav">
            <ul>
                <li><a href="profile.php" class="active">Personal Details</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="review_history.php">Review History</a></li>
                <li><a href="favorite_history.php">Favorite History</a></li>
                <li><a href="#" onclick="showLogoutConfirmation()">Logout</a></li>
            </ul>
        </div>
    
        <div class="profile-details">
            <h2>Update your profile information.</h2>

            <!-- Name Edit Section -->
            <div class="profile-item">
                <span>Full Name</span>
                <span data-field="name">
                    <?php 
                    // Display the user's full name
                    $firstName = $user['first_name'] ?? '';
                    $lastName = $user['last_name'] ?? '';
                    echo htmlspecialchars(trim($firstName . ' ' . $lastName)); 
                    ?>
                </span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editNameModal">Edit</a>
            </div>

            <!-- Username Edit Section -->
            <div class="profile-item">
                <span>Username</span>
                <span data-field="username"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editUsernameModal">Edit</a>
            </div>
    
            <!-- Email Edit Section -->
            <div class="profile-item">
                <span>Email Address</span>
                <span data-field="email"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editEmailModal">Edit</a>
            </div>

            <!-- Phone Number Edit Section -->
            <div class="profile-item">
                <span>Phone Number</span>
                <span data-field="phone"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editphoneNumModal">Edit</a>
            </div>

            <!-- Address Edit Section -->
            <div class="profile-item">
                <span>Address</span>
                <span data-field="address"><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editAddressModal">Edit</a>
            </div>
        </div>
    </div>

    <!-- Modals for Editing -->
    <!-- Edit Name Modal -->
    <div class="modal fade" id="editNameModal" tabindex="-1" aria-labelledby="editNameLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="editNameLabel">Edit Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editNameForm">
                    <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Email Modal -->
    <div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmailLabel">Edit Email Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editEmailForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="email" class="form-label">New Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Phone Number Modal -->
    <div class="modal fade" id="editphoneNumModal" tabindex="-1" aria-labelledby="editPhoneNumLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPhoneNumLabel">Edit Phone Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPhoneForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">New Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressLabel">Edit Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAddressForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">New Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Username Modal -->
    <div class="modal fade" id="editUsernameModal" tabindex="-1" aria-labelledby="editUsernameLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUsernameLabel">Edit Username</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUsernameForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">New Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="showGoodbyeMessage()">Yes, Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Goodbye Message Modal -->
    <div class="modal fade" id="goodbyeModal" tabindex="-1" aria-labelledby="goodbyeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="goodbyeModalLabel">Goodbye!</h5>
                </div>
                <div class="modal-body">
                    Okay, see you next time! 👋
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
       // Adjust the field mapping to support all forms
function handleFormSubmit(formId) {
    document.getElementById(formId).addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('update_profile.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the displayed fields for all supported forms
                const formFields = {
                    editNameForm: ['first_name', 'last_name'],
                    editEmailForm: ['email'],
                    editPhoneForm: ['phone_number'],
                    editAddressForm: ['address'],
                    editUsernameForm: ['username'],
                };

                const fieldsToUpdate = formFields[formId] || [];
                fieldsToUpdate.forEach(field => {
                    const value = formData.get(field);
                    if (value) {
                        const displayField = field === 'phone_number' ? 'phone' : field;
                        document.querySelector(`.profile-item span[data-field="${displayField}"]`).textContent = value;
                    }
                });

                // Close the modal
                const modalId = `${formId.replace('Form', '')}Modal`;
                bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
            } else {
                alert('Failed to update profile. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
}


        // Initialize the forms
        handleFormSubmit('editNameForm');
        handleFormSubmit('editEmailForm');
        handleFormSubmit('editPhoneForm');
        handleFormSubmit('editAddressForm');
        handleFormSubmit('editUsernameForm');

        // Add event listener to the DOM content loaded
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

        // Function to show the logout confirmation modal
        function showLogoutConfirmation() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }

        // Function to show the goodbye message modal
        function showGoodbyeMessage() {
            // Hide first modal
            const logoutModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            logoutModal.hide();
            
            // Show goodbye modal
            const goodbyeModal = new bootstrap.Modal(document.getElementById('goodbyeModal'));
            goodbyeModal.show();
            
            // Wait 2 seconds then logout
            setTimeout(() => {
                window.location.href = 'logout.php';
            }, 1000);
        }
    </script>
</body>
</html>
