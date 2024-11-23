<?php
// Include the database connection file
include 'connection.php';

// Initialize error and success messages
$error_message = $success_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $new_password = $_POST['new_password'];
    
    // Check if email exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email exists
    if ($result->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $hashed_password, $email);
        $update_stmt->execute();

        // Check if the password update was successful
        if ($update_stmt->affected_rows > 0) {
            $success_message = "Your password has been successfully reset. You can now log in with your new password.";
        } else {
            $error_message = "An error occurred while resetting your password. Please try again.";
        }
    } else {
        // Set the error message if the email is not found
        $error_message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 500px;
            margin: 80px auto 50px; /* Adjusted for navbar and footer spacing */
            padding: 20px;
            background-color: #f4db7d; /* Light yellow background */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align text */
        }

        .position-relative {
            position: relative;
        }

        .show-hide {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1a2238;
        }

        .input-box input {
            width: 100%;
            padding: 10px 30px 10px 10px; /* Added right padding for the icon */
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2 class="text-center mb-4" style="color: #1a2238;">Reset Password</h2>
        
        <!-- Display the error message if it exists -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Display the success message if it exists -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php else: ?>
            <!-- Reset password form -->
            <form id="resetForm" action="" method="POST">
                <div class="input-box">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-box">
                    <label for="new_password">New Password:</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <span class="show-hide" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                <div class="button">
                    <input type="button" value="Reset Password" onclick="confirmReset()">
                </div>
            </form>
        <?php endif; ?>
            
        <div class="login-options">
            <a href="login.php" style="color: #1a2238;">Back to Login</a>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Password Reset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reset your password?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" onclick="submitReset()">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Script for password reset confirmation and toggle -->
    <script>
        function confirmReset() {
            var email = document.getElementById('email').value;
            var newPassword = document.getElementById('new_password').value;

            // Check if the email and new password fields are empty
            if (email.trim() === '' || newPassword.trim() === '') {
                alert('Please fill in both email and new password fields.');
                return;
            }

            // Show the confirmation modal
            var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        }

        // Submit the reset form
        function submitReset() {
            document.getElementById('resetForm').submit();
        }

        // Show/Hide Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('new_password');
        const toggleIcon = document.getElementById('toggleIcon');

        // Add event listener to toggle password visibility
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
