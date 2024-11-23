<?php
session_start();

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_username'])) {
    header("Location: admin_dashboard.php");
    exit();
}

include 'connection.php';  // Includes database connection

// Initialize remembered username variable
$rememberedUsername = ''; // Default value

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the admin exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verify the password (make sure the password in your database is hashed)
        if (password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['LAST_ACTIVITY'] = time(); // Store the time for session timeout handling

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password. Please double check.";
        }
    } else {
        $error = "Admin account not found.";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tuition Finder</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS and Icons-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Container styles */
        .container {
            max-width: 500px;
            margin: 80px auto 50px; /* Adjusted for navbar and footer spacing */
            padding: 20px;
            background-color: #f4db7d; /* Light yellow background */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align text */
        }
    </style>
</head>
<body>
    <header>
        <!-- Minimal Navigation bar for Admin Login -->
        <nav class="navbar navbar-expand-lg navbar-dark ">
            <div class="container-fluid">
                <a class="navbar-brand" href="login_admin.php">Tuition Finder - Admin</a>
            </div>
        </nav>          
    </header>       
    <div class="container">
            <h2 class="text-center mb-4">Admin Login</h2>

            <!-- Display error if login failed -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Display timeout message -->
            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning">
                    Your session has expired due to inactivity. Please log in again.
                </div>
            <?php endif; ?>

            <!-- Admin Login Form -->
            <form action="login_admin.php" method="POST">
                <div class="mb-3 position-relative">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password:</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <span class="show-hide" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Log in</button>
                </div>
                <div class="mt-3 text-center">
                    <a href="login.php" style="color: #1a2238;">Login as user</a>
                </div>
            </form>
    </div>

    <?php include 'admin_footer.php'; ?>

    <script>
        // Show/Hide Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
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
