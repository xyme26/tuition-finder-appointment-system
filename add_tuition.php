<?php
session_start();
include 'connection.php'; // Database connection
$current_page = 'manage_tuition';

// Function to geocode an address
function geocodeAddress($address) {
    // Google Maps API key
    $apiKey = 'AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs';
    // Construct the URL for the Geocoding API request
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
    
    // Get the response from the Geocoding API
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Check if the response status is OK
    if ($data['status'] === 'OK') {
        // Return the latitude and longitude
        return [
            'latitude' => $data['results'][0]['geometry']['location']['lat'],
            'longitude' => $data['results'][0]['geometry']['location']['lng']
        ];
    }
    // Return false if the response status is not OK
    return false;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get tuition center details from POST data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle multiple course tags
    $course_tags = $_POST['course_tags']; // This will be an array of subjects
    $course_tags_str = implode(',', $course_tags); // Convert the array into a comma-separated string

    // Handle the image upload 
    $target_dir = "uploads/"; // Directory to store uploaded images
    $target_file = $target_dir . basename($_FILES["image"]["name"]); // Path for the uploaded file
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Get the file extension

    // Check if image file is a valid image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Prepare SQL statement to insert tuition center details
        $stmt = $conn->prepare("INSERT INTO tuition_centers (name, address, description, contact, course_tags, teaching_language, price_range, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Handle teaching languages array
        $teaching_language = isset($_POST['teaching_language']) ? implode(',', $_POST['teaching_language']) : 'English';

        // Bind the parameters to the SQL statement
        $stmt->bind_param("sssssssdds", 
            $name, 
            $address, 
            $description, 
            $_POST['contact'], 
            $course_tags_str, 
            $teaching_language,
            $_POST['price_range'],
            $latitude,
            $longitude, 
            $target_file // Path to the uploaded image
        );

        // Check if insertion was successful
        if ($stmt->execute()) {
            echo "Tuition center added successfully!";
            header("Location: manage_tuition.php"); // Redirect back to admin page
        } else {
            echo "Error: " . $stmt->error; // Display error if insertion fails
        }

        // Close the statement and connection   
        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tuition Center</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br>
    
    <!-- Add Tuition Center Form Container -->
    <div class="container edit-form-container">
        <h2 class="text-center mb-4">Add Tuition Center</h2>

        <!-- Add Tuition Center Form -->    
        <form action="add_tuition.php" method="POST" enctype="multipart/form-data">
            <!-- Tuition Center Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Tuition Center Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <!-- Address Input -->
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                <button type="button" onclick="lookupAddress()" class="btn btn-secondary mt-2">
                    Look up coordinates
                </button>
            </div>

            <!-- Contact Information -->
            <div class="mb-3">
                <label for="contact" class="form-label">Contact:</label>
                <input type="text" class="form-control" id="contact" name="contact" required>
            </div>

            <!-- Choose Course Tags (Subjects) -->
            <div class="mb-3">
                <label class="form-label">Choose Course Tags (Subjects):</label>
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
                    
                    // Display each subject as a checkbox
                    foreach($subjects as $subject) {
                        $displayName = ($subject === 'Malay') ? 'Bahasa Malaysia' : $subject;
                        echo '<div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="course_tags[]" value="'.$subject.'" 
                                           id="'.$subject.'">
                                    <label class="form-check-label" for="'.$subject.'">
                                        '.$displayName.'
                                    </label>
                                </div>
                            </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Teaching Languages -->
            <div class="mb-3">
                <label class="form-label">Teaching Languages:</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="teaching_language[]" value="English" 
                                   id="langEnglish" checked>
                            <label class="form-check-label" for="langEnglish">
                                English
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="teaching_language[]" value="Bahasa Malaysia" 
                                   id="langMalay">
                            <label class="form-check-label" for="langMalay">
                                Bahasa Malaysia
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="teaching_language[]" value="Chinese" 
                                   id="langChinese">
                            <label class="form-check-label" for="langChinese">
                                Chinese
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->    
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="4" required></textarea>
            </div>

            <!-- Upload Image -->
            <div class="mb-3">
                <label for="image" class="form-label">Upload Image:</label>
                <input type="file" class="form-control" id="image" name="image" 
                       accept="image/*" required>
            </div>

            <!-- Price Range per Subject -->
            <div class="mb-3">
                <label for="price_range" class="form-label">Price Range per Subject (in RM):</label>
                <input type="text" class="form-control" id="price_range" name="price_range" 
                       placeholder="e.g., RM20-RM30" required>
            </div>

            <!-- Latitude and Longitude -->
            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude:</label>
                <input type="number" step="any" class="form-control" id="latitude" name="latitude" readonly>
            </div>

            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude:</label>
                <input type="number" step="any" class="form-control" id="longitude" name="longitude" readonly>
            </div>

            <!-- Add Tuition Center Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Add Tuition Center</button>
            </div>
        </form>
    </div>

    <!-- JavaScript to look up coordinates -->
    <script>
        // Function to look up coordinates
        function lookupAddress() {
            const address = document.getElementById('address').value;
            const geocoder = new google.maps.Geocoder();
            
            // Geocode the address
            geocoder.geocode({ address: address }, (results, status) => {
                // Check if the geocode was successful
                if (status === 'OK') {
                    // Get the latitude and longitude from the results
                    const latitude = results[0].geometry.location.lat();
                    const longitude = results[0].geometry.location.lng();
                    
                    // Update the latitude and longitude fields
                    document.getElementById('latitude').value = latitude;
                    document.getElementById('longitude').value = longitude;
                } else {
                    // Alert if the geocode was not successful
                    alert('Could not find coordinates for this address. Please check the address or enter coordinates manually.');
                }
            });
        }
    </script>

    <?php include 'admin_footer.php'; ?>
</body>
</html>
