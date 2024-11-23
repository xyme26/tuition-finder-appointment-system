<?php
session_start(); // Start the session
include 'connection.php'; // Database connection
$current_page = 'login';
// Initialize remembered username variable
$rememberedUsername = ''; // Default value

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $email = filter_var(trim($_POST['useremail']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['userpassword']);
    $remember = isset($_POST['remember']) ? true : false;

    // Prepare SQL statement to select the user
    $sql = "SELECT * FROM users WHERE email = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the query returned exactly one row
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Start session and store user details
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_success'] = true; // Set login success flag

                // Handle Remember Me
                if ($remember) {
                    // Set cookie that expires in 30 days
                    setcookie('remembered_email', $email, time() + (30 * 24 * 60 * 60), '/');
                } else {
                    // Remove the cookie if "Remember Me" is unchecked
                    setcookie('remembered_email', '', time() - 3600, '/');
                }

                // Redirect to profile page
                header("Location: profile.php");
                exit;
            } else {
                // Invalid password
                header("Location: login.php?error=invalid_credentials");
                exit;
            }
        } else {
            // User not found
            header("Location: login.php?error=user_not_found");
            exit;
        }
    }
    // Close the statement
    $stmt->close();
}
// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Link to Bootstrap Icons for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Link to jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Link to Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your custom CSS -->
    <style>
        /* Container styles */
        .container {
            max-width: 500px;
            margin: 80px auto 50px;
            padding: 20px;
            background-color: #f4db7d;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        /* Button styles */
        .button input[type="submit"] {
            background-color: #1a2238;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .button input[type="submit"]:hover {
            background-color: #2a3248;
        }

    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <br>
        <h2 class="text-center mb-4">User Login</h2>
        
        <!-- Display error if login failed -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                // Display error message based on the error type
                if ($_GET['error'] == 'invalid_credentials') {
                    echo "Invalid email or password!";
                } elseif ($_GET['error'] == 'user_not_found') {
                    echo "User not found!";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <div class="input-box">
                <label for="useremail">Email:</label>
                <input type="email" id="useremail" name="useremail" 
                       value="<?php echo isset($_COOKIE['remembered_email']) ? htmlspecialchars($_COOKIE['remembered_email']) : ''; ?>"
                       placeholder="Enter your email" required>
            </div>
            <div class="input-box position-relative">
                <label for="userpassword">Password:</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="userpassword" name="userpassword" placeholder="Enter your password" required>
                    <span class="show-hide" id="togglePassword">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <div class="form-check mb-3 text-start">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                       <?php echo isset($_COOKIE['remembered_email']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <div class="button">
                <input type="submit" value="Log in">
            </div>
            <div class="login-options">
                <a href="index.php" style="color: #1a2238;">Continue as guest</a><br>
                or<br>
                <a href="login_admin.php" style="color: #1a2238;">Login as admin</a><br>
                <a href="forgot_password.php" style="color: #1a2238;">Forgot Password?</a>
            </div>
        </form>
</div>

    <?php include 'footer.php'; ?>

    <script>
        // Show/Hide Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('userpassword');
        const toggleIcon = document.getElementById('toggleIcon');

        // Add event listener to toggle the password visibility
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
