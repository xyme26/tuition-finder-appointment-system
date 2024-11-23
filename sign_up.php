<?php
include 'connection.php'; // Database connection
$current_page = 'sign_up';

// Initialize flags for alerts and errors
$showAlert = false;  
$showError = false;  
$exists = false; 

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']); // Capture confirm password

    // Check if username already exists
    $sql = "SELECT * FROM users WHERE username=?";
    
    // Prepare the statement for username check
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if the username already exists in the database
        if ($result->num_rows > 0) {
            $exists = "Username not available";  
        } else {
            // Check if the passwords match
            if ($password === $cpassword) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
                
                // Prepare SQL statement for inserting new user
                $sql = "INSERT INTO users (username, email, password, user_role) VALUES (?, ?, ?, 'user')";
                
                // Prepare the statement for inserting new user
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sss", $username, $email, $hashedPassword);
                    
                    // Execute the statement for inserting new user
                    if ($stmt->execute()) {
                        $showAlert = true;  // Successfully created account
                    } else {
                        $showError = "Signup failed. Please try again.";
                    }
                } else {
                    $showError = "Error preparing statement.";
                }
            } else {
                $showError = "Passwords do not match";  
            }
        }
    } else {
        $showError = "Error preparing statement.";
    }
    
    // Close statement
    $stmt->close(); 
}

$conn->close(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>  

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css" />

    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" 
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"> 
    </script> 
        
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" 
        integrity= "sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"> 
    </script> 

    <style>
        /* Import Google font - Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

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

        /* Heading */
        .container h2 {
            color: #1a2238; /* Dark blue text */
            margin-bottom: 20px;
        }

        /* Input field styles */
        .input-box {
            position: relative;
            margin-bottom: 15px;
            text-align: left; /* Align text to left for labels */
        }

        /* Labels */
        .input-box label {
            display: block;
            margin-bottom: 5px;
            color: #1a2238; /* Dark blue */
            font-weight: 500;
        }

        /* Input fields */
        .input-box input {
            width: 100%;
            padding: 10px 40px 10px 10px; /* Added padding-right for icon */
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-box input:focus {
            border-color: #1a2238; /* Focus color */
        }

        /* Show/Hide Password Icon */
        .input-box i.show-hide {
            position: absolute;
            top: 38px;
            right: 10px;
            cursor: pointer;
            color: #1a2238; /* Icon color */
            transition: color 0.3s ease;
        }

        .input-box i.show-hide:hover {
            color: #ff6a3d; /* Change icon color on hover */
        }

        /* Error message styles */
        .error {
            display: none;
            margin-top: 5px;
            font-size: 12px;
            color: red;
            text-align: left; /* Align error text to left */
        }

        /* Button styles */
        .button {
            text-align: center;
            margin-top: 20px;
        }

        /* Media Queries for responsiveness */
        @media (max-width: 768px) {
            .navbar-custom .navbar-nav {
                text-align: center;
            }

            .navbar-custom .nav-link {
                margin: 5px 0;
            }

            .container {
                margin: 60px 20px 30px; /* Adjusted margins for smaller screens */
            }
        }

        /* Add these styles for the password toggle */
        .show-hide {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1a2238;
        }

        .show-hide:hover {
            color: #ff6a3d;
        }

        .position-relative {
            position: relative;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2 class="text-center mb-4">Sign Up</h2>
        <?php 
        // Display success or error alerts
        if ($showAlert) { 
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert"> 
                    <strong>Success!</strong> Your account is now created and you can login. 
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> 
                        <span aria-hidden="true">x</span>  
                    </button>  
                </div>';  
        } 
        
        // Display error if signup fails
        if ($showError) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">  
                <strong>Error!</strong> ' . $showError . ' 
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> 
                        <span aria-hidden="true">x</span>  
                    </button>  
                </div>';  
        } 
        
        // Display error if username already exists
        if ($exists) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                <strong>Error!</strong> ' . $exists . ' 
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">  
                    <span aria-hidden="true">x</span>  
                </button> 
              </div>';  
        } 
        ?>

        <!-- Signup Form -->
        <form action="" method="POST">
            <div class="input-box">
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <div class="error" id="emailError">Please enter a valid email address.</div>
            </div>
            <div class="input-box position-relative">
                <label for="password">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="show-hide" id="togglePassword">
                        <i class="bi bi-eye-slash" id="toggleIcon1"></i>
                    </span>
                </div>
            </div>
            <div class="input-box position-relative">
                <label for="cpassword">Confirm Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="cpassword" name="cpassword" required>
                    <span class="show-hide" id="toggleConfirmPassword">
                        <i class="bi bi-eye-slash" id="toggleIcon2"></i>
                    </span>
                </div>
                <small id="emailHelp" class="form-text text-muted">Make sure you have enter the same password</small>
            </div>
            <div class="button">
                <input type="submit" value="Sign Up">
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        // Add Bootstrap Icons CSS in the head section if not already added
        document.head.innerHTML += '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">';

        // Password Toggle Functionality
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('cpassword');
        const toggleIcon1 = document.getElementById('toggleIcon1');
        const toggleIcon2 = document.getElementById('toggleIcon2');

        // Toggle for Password
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon1.classList.toggle('bi-eye');
            toggleIcon1.classList.toggle('bi-eye-slash');
        });

        // Toggle for Confirm Password
        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            toggleIcon2.classList.toggle('bi-eye');
            toggleIcon2.classList.toggle('bi-eye-slash');
        });

        // Email Validation Functionality
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError'); // Error message for email

        // Check if the email is valid
        function checkEmail() {
            // Email pattern
            const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

            // If the email does not match the pattern, display the error message
            if (!emailInput.value.match(emailPattern)) {
                emailError.style.display = 'block';
                return false;
            }

            // If the email matches the pattern, hide the error message
            emailError.style.display = 'none';
            return true;
        }
    
        // Password Validation
        function createPass() {
            // Password pattern
            const passPattern =
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            // If the password does not match the pattern, add the invalid class to the password field
            if (!passInput.value.match(passPattern)) {
                return passField.classList.add("invalid");
            }

            // If the password matches the pattern, remove the invalid class from the password field
            passField.classList.remove("invalid");
        }

        // Add event listeners to input fields
        emailInput.addEventListener("keyup", checkEmail); // Check email on keyup
        passInput.addEventListener("keyup", createPass); // Check password on keyup
        cPassInput.addEventListener("keyup", confirmPass); // Check confirm password on keyup
    </script>
</body>
</html>
