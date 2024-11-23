<?php
session_start();
include 'connection.php';
$current_page = 'manage_tuition';

// Set the timeout duration
$timeout_duration = 1800;

// Check if the session has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Check if the admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

// Initialize success and error messages
$success = '';
$error = '';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_tuition.php");
    exit();
}

// Get the center ID
$center_id = intval($_GET['id']);

// Fetch existing tuition center data
$stmt = $conn->prepare("SELECT * FROM tuition_centers WHERE id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the tuition center was found
if ($result->num_rows === 0) {
    header("Location: manage_tuition.php?error=Tuition centre not found.");
    exit();
}

// Fetch the tuition center data
$center = $result->fetch_assoc();

// Close the statement
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the name, address, contact, description, price range, latitude, and longitude from the form
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $description = trim($_POST['description']);
    $price_range = trim($_POST['price_range']);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle course tags array
    $course_tags = isset($_POST['course_tags']) ? implode(',', $_POST['course_tags']) : '';

    // Initialize image variable
    $image = null;

    // Check for image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)); // Get file extension
        $target_dir = "uploads/"; // Directory to save uploaded files
        $target_file = $target_dir . uniqid() . '.' . $imageFileType; // Create a unique file name

        // Validate image file type
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif']; // Allowed file types
        if (!in_array($imageFileType, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed."; // Set error message for invalid file type
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file; // Set image path for database
            } else {
                $error = "Failed to upload image.";
            }
        }
    } else {
        // If no new image is uploaded, keep the existing one
        $image = $center['image'];
    }

    // Validate required fields
    if (empty($name) || empty($contact)) {
        $error = "Name and contact are required.";
    } else {
        // Update the tuition center in the database
        $stmt = $conn->prepare("UPDATE tuition_centers SET name = ?, address = ?, description = ?, contact = ?, course_tags = ?, teaching_language = ?, price_range = ?, image = ?, latitude = ?, longitude = ? WHERE id = ?");
        $teaching_language = isset($_POST['teaching_language']) ? implode(',', $_POST['teaching_language']) : 'English';
        $stmt->bind_param("ssssssssddi", 
            $name, 
            $address, 
            $description, 
            $contact, 
            $course_tags,
            $teaching_language,
            $price_range, 
            $image,
            $latitude,
            $longitude, 
            $center_id
        );

        // Execute the statement
        if ($stmt->execute()) {
            $success = "Tuition center updated successfully.";
            // Refresh the data
            $center['name'] = $name;
            $center['address'] = $address;
            $center['contact'] = $contact;
            $center['description'] = $description;
            $center['course_tags'] = $course_tags;
            $center['price_range'] = $price_range;
            $center['image'] = $image;
            $center['latitude'] = $latitude;
            $center['longitude'] = $longitude;
        } else {
            // Set error message if the statement fails
            $error = "Error updating tuition center: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tuition Center - Admin</title>
     <!-- Link to Bootstrap CSS for styling -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br>

    <!-- Edit tuition center form -->
    <div class="container edit-form-container">
        <h2>Edit Tuition Center</h2>
        <form action="edit_tuition.php?id=<?php echo $center_id; ?>" method="POST" enctype="multipart/form-data">
            <!-- Display success or error messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Name input -->
            <div class="mb-3">
            <label for="name" class="form-label">Tuition Center Name:</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php echo htmlspecialchars($center['name']); ?>" required>
            </div>

            <!-- Address input -->
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <input type="text" class="form-control" id="address" name="address" 
                    value="<?php echo htmlspecialchars($center['address']); ?>" required>
                <button type="button" onclick="lookupAddress()" class="btn btn-secondary mt-2">
                    Look up coordinates
                </button>
            </div>

            <!-- Contact input -->
            <div class="mb-3">
                <label for="contact" class="form-label">Contact Information:</label>
                <input type="text" class="form-control" id="contact" name="contact" 
                    value="<?php echo htmlspecialchars($center['contact']); ?>" required>
            </div>

            <!-- Description input -->
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description" 
                    rows="4"><?php echo htmlspecialchars($center['description']); ?></textarea>
            </div>

            <!-- Subjects Offered input -->
            <div class="mb-3">
                <label class="form-label">Subjects Offered:</label>
            <div class="row g-3">
                <?php
                $subjects = [
                    'Math',
                    'Science',
                    'English',
                    'Biology',
                    'Chemistry',
                    'Physics',
                    'Add Math',
                    'Account',
                    'History',
                    'Economy',
                    'Malay'
                ];
                
                // Get current course tags as array
                $current_tags = explode(',', $center['course_tags']);
                
                // Loop through each subject
                foreach($subjects as $subject) {
                    $displayName = ($subject === 'Malay') ? 'Bahasa Malaysia' : $subject;
                    $checked = in_array($subject, $current_tags) ? 'checked' : '';
                    
                    // Display the subject as a checkbox
                    echo '<div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="course_tags[]" value="'.$subject.'" 
                                       id="'.$subject.'" '.$checked.'>
                                <label class="form-check-label" for="'.$subject.'">
                                    '.$displayName.'
                                </label>
                            </div>
                        </div>';
                }
                ?>
                </div>
            </div>

            <!-- Price Range input -->
            <div class="mb-3">
                <label for="price_range" class="form-label">Price Range per Subject (in RM):</label>
                <input type="text" class="form-control" id="price_range" name="price_range" 
                    value="<?php echo htmlspecialchars($center['price_range']); ?>" 
                    placeholder="e.g., RM20-RM30" required>
            </div>

            <!-- Latitude input -->
            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude:</label>
                <input type="number" step="any" class="form-control" id="latitude" name="latitude" 
                    value="<?php echo htmlspecialchars($center['latitude']); ?>" readonly>
            </div>

            <!-- Longitude input -->
            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude:</label>
                <input type="number" step="any" class="form-control" id="longitude" name="longitude" 
                    value="<?php echo htmlspecialchars($center['longitude']); ?>" readonly>
            </div>

            <!-- Current Image input -->
            <div class="mb-3">
                <label class="form-label">Current Image:</label>
                <?php if (!empty($center['image'])): ?>
                    <div class="current-image-preview mb-2">
                        <img src="<?php echo htmlspecialchars($center['image']); ?>" 
                         alt="Current tuition center image" 
                         style="max-width: 200px; height: auto;">
                    </div>
                <?php endif; ?>
            </div>  

            <!-- Upload New Image input -->
            <div class="mb-3">
                <label for="image" class="form-label">Upload New Image:</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF</small>
            </div>

            <!-- Teaching Languages input -->
            <div class="mb-3">
                <label class="form-label">Teaching Languages:</label>
                <div class="row g-3">
                    <?php
                    // Define the languages
                    $languages = ['English', 'Bahasa Malaysia', 'Chinese'];
                    // Get the current languages
                    $current_languages = explode(',', $center['teaching_language']);
                    
                    // Loop through each language
                    foreach($languages as $language) {
                        $checked = in_array($language, $current_languages) ? 'checked' : '';
                        // Generate the language ID
                        $langId = 'lang' . str_replace(' ', '', $language);
                        // Display the language as a checkbox
                        echo '<div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="teaching_language[]" value="'.$language.'" 
                                        id="'.$langId.'" '.$checked.'>
                                    <label class="form-check-label" for="'.$langId.'">
                                        '.$language.'
                                    </label>
                                </div>
                            </div>';
                        }
                    ?>
                </div>
            </div>

            <!-- Update Tuition Center button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update Tuition Center</button>
            </div>
    </form>
</div>

<?php include 'admin_footer.php'; ?>

<script>
    // Function to lookup address and get coordinates
    function lookupAddress() {
        const address = document.getElementById('address').value;
        const geocoder = new google.maps.Geocoder();
        
        // Geocode the address
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK') {
                const latitude = results[0].geometry.location.lat();
                const longitude = results[0].geometry.location.lng();
                
                // Set the latitude and longitude values
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
            } else {
                // Display an alert if the coordinates were not found
                alert('Could not find coordinates for this address. Please check the address or enter coordinates manually.');
            }
        });
    }
</script>
</body>
</html>
