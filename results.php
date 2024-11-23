<?php
session_start();
include 'connection.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get search parameters
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_lon = $_SESSION['user_lon'] ?? 0;

// Base query
$sql = "SELECT tc.*, 
       (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
        cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
        sin(radians(latitude)))) AS distance,
       AVG(r.rating) as avg_rating,
       COUNT(r.id) as review_count
FROM tuition_centers tc
LEFT JOIN reviews r ON tc.id = r.tuition_center_id";

// Initialize arrays for parameters and types
$params = [$user_lat, $user_lon, $user_lat];
$types = "ddd"; // For the three location parameters

// Build WHERE clause
$whereConditions = [];

// Add condition if name is not empty
if (!empty($name)) {
    $whereConditions[] = "tc.name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

// Add condition if location is not empty
if (!empty($location)) {
    $whereConditions[] = "(tc.address LIKE ? OR tc.city LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= "ss";
}

// Add WHERE clause if conditions exist
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

// Group by and order by
$sql .= " GROUP BY tc.id ORDER BY tc.name";

// Prepare and execute statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters dynamically
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Check if the result is false
if ($result === false) {
    die("Error executing query: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    
</head>
<body>
    <?php include 'header.php'; ?>
    <br>
    <!-- Search form -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <form action="results.php" method="GET" class="d-flex gap-2 mb-4">
                    <div class="flex-grow-1">
                        <input type="text" name="name" class="form-control" placeholder="Search by name" 
                               value="<?php echo htmlspecialchars($name); ?>">
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="location" class="form-control" placeholder="Search by city" 
                               value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        <!-- Filters -->
        <div class="row">
            <div class="col-md-3">
                <div class="filter-section">
                    <h4>Filters</h4>
                    <form id="filterForm">
                        <div class="mb-3">
                            <label for="minRating" class="form-label"><b>Minimum Rating:</b></label>
                            <input type="number" id="minRating" name="minRating" class="form-control" min="1" max="5" step="0.1">
                        </div>
                        <div class="mb-3">
                            <label for="sortBy" class="form-label"><b>Sort By:</b></label>
                            <div class="d-flex align-items-center gap-2">
                                <select id="sortBy" name="sortBy" class="form-select">
                                    <option value="rating_desc">Rating (High to Low)</option>
                                    <option value="rating_asc">Rating (Low to High)</option>
                                    <option value="name_asc">Name (A to Z)</option>
                                    <option value="name_desc">Name (Z to A)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><b>Subjects:</b></label>
                            <div class="row">
                                <!-- Column 1 -->
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Math" id="mathCheck">
                                        <label class="form-check-label" for="mathCheck">
                                            <i class="fas fa-calculator"></i> Math
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Science" id="scienceCheck">
                                        <label class="form-check-label" for="scienceCheck">
                                            <i class="fas fa-flask"></i> Science
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="English" id="englishCheck">
                                        <label class="form-check-label" for="englishCheck">
                                            <i class="fas fa-book"></i> English
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Biology" id="biologyCheck">
                                        <label class="form-check-label" for="biologyCheck">
                                            <i class="fas fa-dna"></i> Biology
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Chemistry" id="chemistryCheck">
                                        <label class="form-check-label" for="chemistryCheck">
                                            <i class="fas fa-atom"></i> Chemistry
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Physics" id="physicsCheck">
                                        <label class="form-check-label" for="physicsCheck">
                                            <i class="fas fa-magnet"></i> Physics
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Column 2 -->
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Add Math" id="addMathCheck">
                                        <label class="form-check-label" for="addMathCheck">
                                            <i class="fas fa-square-root-variable"></i> Add Math
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Account" id="accountCheck">
                                        <label class="form-check-label" for="accountCheck">
                                            <i class="fas fa-coins"></i> Account
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="History" id="historyCheck">
                                        <label class="form-check-label" for="historyCheck">
                                            <i class="fas fa-landmark"></i> History
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Economy" id="economyCheck">
                                        <label class="form-check-label" for="economyCheck">
                                            <i class="fas fa-chart-line"></i> Economy
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="subjects[]" value="Malay" id="malayCheck">
                                        <label class="form-check-label" for="malayCheck">
                                            <i class="fas fa-language"></i> Bahasa Malaysia
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><b>Teaching Languages:</b></label>
                            <div class="language-checkboxes">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="languages[]" value="English" id="englishLangCheck">
                                    <label class="form-check-label" for="englishLangCheck">English</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="languages[]" value="Bahasa Malaysia" id="malayLangCheck">
                                    <label class="form-check-label" for="malayLangCheck">Bahasa Malaysia</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="languages[]" value="Chinese" id="chineseLangCheck">
                                    <label class="form-check-label" for="chineseLangCheck">Chinese</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-9">
                <!--
            <div class="d-flex justify-content-end mb-3">
                    <div class="btn-group" role="group" aria-label="View toggle">
                        <button id="gridViewBtn" class="btn btn-outline-primary active">
                            <i class="fas fa-th-large"></i> Grid
                        </button>
                        <button id="listViewBtn" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> List
                        </button>
                    </div>
                </div>
                -->
                <div id="resultsContainer" class="results-container">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <script>
        // Add event listener to the DOM content loaded
        document.addEventListener('DOMContentLoaded', function() {
            const resultsContainer = document.getElementById('resultsContainer');
            const filterForm = document.getElementById('filterForm');
            /*
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');

            // Initialize with grid view
            resultsContainer.classList.add('grid-view');

            // Handle view toggle
            gridViewBtn.addEventListener('click', function() {
                resultsContainer.classList.remove('list-view');
                resultsContainer.classList.add('grid-view');
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
            });

            listViewBtn.addEventListener('click', function() {
                resultsContainer.classList.remove('grid-view');
                resultsContainer.classList.add('list-view');
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
            });
            */
            // Handle filter form submission
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchResults(1); // Reset to first page when filters change
            });

            // Initial load
            fetchResults();
        });

        // Function to fetch results
        function fetchResults(page = 1) {
            const resultsContainer = document.getElementById('resultsContainer');
            if (!resultsContainer) return;
            
            resultsContainer.innerHTML = '<div class="loading">Loading results...</div>';

            // Get filter parameters
            const params = new URLSearchParams();
            
            // Add page parameter
            params.append('page', page);
            
            // Add search parameters from URL
            const urlParams = new URLSearchParams(window.location.search);
            params.append('name', urlParams.get('name') || '');
            params.append('location', urlParams.get('location') || '');

            // Add filter parameters
            const minRating = document.getElementById('minRating')?.value;
            const sortBy = document.getElementById('sortBy')?.value;
            const selectedSubjects = Array.from(document.querySelectorAll('input[name="subjects[]"]:checked')).map(cb => cb.value); // Get selected subjects
            const selectedLanguages = Array.from(document.querySelectorAll('input[name="languages[]"]:checked')).map(cb => cb.value); // Get selected languages
        
            // Add minRating, sortBy, subjects, and languages to the parameters
            if (minRating) params.append('minRating', minRating); // Add minimum rating if provided
            if (sortBy) params.append('sortBy', sortBy); // Add sort by option if provided
            if (selectedSubjects.length > 0) params.append('subjects', JSON.stringify(selectedSubjects)); // Add selected subjects
            if (selectedLanguages.length > 0) params.append('languages', JSON.stringify(selectedLanguages)); // Add selected languages

            // Fetch the filtered results
            fetch(`fetch_filtered_results.php?${params.toString()}`) // Fetch results from the server
                .then(response => response.json()) // Parse JSON response
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Initialize resultsHTML with the grid div
                    let resultsHTML = '<div class="results-grid">';

                    // Check if there are centers and add them to the resultsHTML
                    if (data.centers && data.centers.length > 0) {
                        data.centers.forEach(center => {
                            resultsHTML += `
                                <div class="result-card">
                                    <img src="${center.image}" 
                                         alt="${center.name}"
                                         onerror="this.src='images/default-tuition.jpg'">
                                    <div class="card-content">
                                        <div>
                                            <h3>${center.name}</h3>
                                            <div class="rating-container">
                                                <span class="stars">${generateStarRating(center.avg_rating || 0)}</span>
                                                <span class="rating-text">(${center.review_count} reviews)</span>
                                            </div>
                                            <p class="price">
                                                <i class="fas fa-tag"></i> 
                                                ${center.price_range || 'Price not available'}
                                            </p>
                                        </div>
                                        <a href="tuition_details.php?id=${center.id}" 
                                           class="btn btn-primary details-btn">Details</a>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        resultsHTML += '<p class="no-results">No results found</p>';
                    }

                    resultsHTML += '</div>'; // Close grid container

                    // Update pagination controls
                    const totalPages = Math.ceil(data.totalCount / data.itemsPerPage); // Calculate total pages
                    resultsHTML += `
                        <div class="pagination-controls mt-5 mb-4 d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-primary" 
                                    onclick="fetchResults(${page - 1})"
                                    ${page <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <span class="btn btn-outline-secondary">
                                Page ${page} of ${totalPages}
                            </span>
                            <button class="btn btn-outline-primary" 
                                    onclick="fetchResults(${page + 1})"
                                    ${page >= totalPages ? 'disabled' : ''}>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    `;

                    // Close the wrapper div
                    resultsHTML += '</div>';

                    // Update the results container with the new results
                    resultsContainer.innerHTML = resultsHTML;
                })
                // Handle errors
                .catch(error => {
                    console.error('Error:', error);
                    resultsContainer.innerHTML = `<p class="error">Error loading results: ${error.message}</p>`;
                });
        }

        // Function to generate star rating
        function generateStarRating(rating) {
            // Calculate the number of full stars, half stars, and empty stars
            const fullStars = Math.floor(rating); // Full star
            const hasHalfStar = rating % 1 >= 0.5; // Check for half star
            const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0); // Empty stars
            
            // Build the star rating string
            let stars = '';
            // Add full stars
            for (let i = 0; i < fullStars; i++) {
                stars += '<i class="fas fa-star"></i>';
            }
            // Add a half star if there is one
            if (hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            }
            // Add empty stars
            for (let i = 0; i < emptyStars; i++) {
                stars += '<i class="far fa-star"></i>';
            }
            // Return the star rating string
            return stars;
        }
    </script>
</body>
</html>