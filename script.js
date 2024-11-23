// Google Maps API key
const GOOGLE_MAPS_API_KEY = 'AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs';

// Get user's current location
function getLocation() {
    // Check if geolocation is supported
    if (navigator.geolocation) {
        // Set options for geolocation
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        // Get the user's current position
        navigator.geolocation.getCurrentPosition(
            showPosition, 
            handleError, 
            options
        );
    } else {
        // Alert if geolocation is not supported
        alert("Geolocation is not supported by this browser.");
        // Fetch tuition centers without user location
        fetchTuitionCenters(0, 0);
    }
}

// Initialize userLocation object
let userLocation = { lat: null, lon: null };

// Display the user's location in the input field
function showPosition(position) {
    // Update userLocation object with latitude and longitude
    userLocation.lat = position.coords.latitude;
    userLocation.lon = position.coords.longitude;

    // Get detailed address information
    const geocoder = new google.maps.Geocoder();
    const latlng = { lat: userLocation.lat, lng: userLocation.lon };

    // Get the detailed address information
    geocoder.geocode({ location: latlng }, (results, status) => {
        if (status === 'OK') {
            if (results[0]) {
                // Get the most accurate address
                const address = results[0].formatted_address;
                document.getElementById("searchLocation").value = address;
                
                // Update location on server with full address
                updateUserLocation(userLocation.lat, userLocation.lon, address);
                
                // Fetch tuition centers with accurate location
                fetchTuitionCenters(userLocation.lat, userLocation.lon);
            }
        }
    });
}

// Update the user's location on the server
function updateUserLocation(lat, lon, address) {
    // Send a POST request to update the user's location
    fetch('update_location.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lat=${lat}&lon=${lon}&address=${address}`
    })
    // Handle the response
    .then(response => response.json())
    .then(data => {
        // Check if the location update was successful
        if (!data.success) {
            console.error('Failed to update location');
        }
    })
    .catch(err => console.error(err));
}

// Haversine distance formula
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the Earth in kilometers
    const dLat = degreesToRadians(lat2 - lat1);
    const dLon = degreesToRadians(lon2 - lon1);
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(degreesToRadians(lat1)) * Math.cos(degreesToRadians(lat2)) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; // Distance in kilometers
}

// Convert degrees to radians
function degreesToRadians(degrees) {
    return degrees * (Math.PI / 180);
}

// Handle errors from geolocation
function handleError(error) {
    // Handle different error codes
    switch(error.code) {
        // User denied the request for Geolocation
        case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
            break;
        // Location information is unavailable
        case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
            break;
        // The request to get user location timed out
        case error.TIMEOUT:
            alert("The request to get user location timed out.");
            break;
        // An unknown error occurred
        case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
            break;
    }
}

// Run the getLocation function on page load
window.onload = getLocation;

// Add search button click event
document.getElementById("search-btn").addEventListener("click", function () {
    // Get the search name and location
    const name = document.getElementById("search-name").value;
    const location = document.getElementById("search-location").value;

    // Handle search logic here
    console.log(`Searching for ${name} in ${location}`);
});

// Set the current year for the copyright
document.getElementById('currentYear').textContent = new Date().getFullYear();

// Add favorite functionality
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        
        // Store the favorite status in local storage
        const tuitionName = btn.getAttribute('data-tuition-name');
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        
        // Check if the tuition name is already in favorites
        if (favorites.includes(tuitionName)) {
            favorites = favorites.filter(name => name !== tuitionName); // Remove from favorites
        } else {
            favorites.push(tuitionName); // Add to favorites
        }
        
        // Store the updated favorites in local storage
        localStorage.setItem('favorites', JSON.stringify(favorites));
    });
});

// Add search button click event
document.getElementById('search-btn').addEventListener('click', () => {
    // Check if geolocation is supported
    if (navigator.geolocation) {
        // Get the user's current position
        navigator.geolocation.getCurrentPosition(function (position) {
            // Update userLocation object with latitude and longitude
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Fetch tuition centers with accurate location
            fetchTuitionCenters(lat, lng);
        }, function (error) {
            console.error('Error fetching location:', error);
            // Fetch tuition centers without user location
            fetchTuitionCenters();
        });
    } else {
        // Alert if geolocation is not supported
        console.error('Geolocation is not supported by this browser.');
        // Fetch tuition centers without user location
        fetchTuitionCenters();
    }
});

// Convert address to coordinates
async function geocodeAddress(address) {
    // Create a geocoder object
    const geocoder = new google.maps.Geocoder();
    
    // Return a promise that resolves with the coordinates
    return new Promise((resolve, reject) => {
        // Geocode the address
        geocoder.geocode({ address: address }, (results, status) => {
            // Check if the geocoding was successful
            if (status === 'OK') {
                // Get the coordinates
                const location = {
                    lat: results[0].geometry.location.lat(),
                    lng: results[0].geometry.location.lng()
                };
                // Resolve the promise with the coordinates
                resolve(location);
            } else {
                // Reject the promise with the error message
                reject(`Geocoding failed: ${status}`);
            }
        });
    });
}

// Fetch tuition centers
async function fetchTuitionCenters(lat, lon) {
    try {
        // Create a distance matrix service object
        const service = new google.maps.DistanceMatrixService();
        // Send a POST request to fetch tuition centers
        const response = await fetch('fetch_tuition.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `lat=${lat}&lon=${lon}`
        });
        
        // Parse the response as JSON
        const centers = await response.json();
        
        // Process each center
        for (const center of centers) {
            // Check if the center has latitude and longitude
            if (center.latitude && center.longitude && lat && lon) {
                try {
                    // Create a promise to get the distance matrix
                    const result = await new Promise((resolve, reject) => {
                        // Get the distance matrix
                        service.getDistanceMatrix({
                            // Set the origins and destinations
                            origins: [{ lat: parseFloat(lat), lng: parseFloat(lon) }],
                            destinations: [{ lat: parseFloat(center.latitude), lng: parseFloat(center.longitude) }],
                            // Set the travel mode and unit system
                            travelMode: google.maps.TravelMode.DRIVING,
                            unitSystem: google.maps.UnitSystem.METRIC
                        }, (response, status) => {
                            // Check if the distance matrix request was successful
                            if (status === 'OK') {
                                resolve(response);
                            } else {
                                reject(status);
                            }
                        });
                    });

                    // Check if the distance matrix request was successful
                    if (result.rows[0].elements[0].status === 'OK') {
                        // Get the actual driving distance
                        center.distance = (result.rows[0].elements[0].distance.value / 1000).toFixed(1);
                    } else {
                        // Set distance to 'N/A' if the request failed
                        center.distance = 'N/A';
                    }
                } catch (error) {
                    // Set distance to 'N/A' if an error occurred
                    console.error('Error calculating distance:', error);
                    center.distance = 'N/A';
                }
            } else {
                // Set distance to 'N/A' if the center doesn't have latitude and longitude
                center.distance = 'N/A';
            }
        }

        // Display the tuition centers
        displayTuitionCenters(centers);
    } catch (err) {
        // Log any errors that occurred during the fetch
        console.error('Error fetching tuition centers:', err);
    }
}

// Display the tuition centers
function displayTuitionCenters(centers) {
    const resultsContainer = document.getElementById('recommendation-grid');
    resultsContainer.innerHTML = '';

    if (centers.length === 0) {
        resultsContainer.innerHTML = '<p class="no-results">No tuition centers found nearby.</p>';
        return;
    }

    centers.forEach(center => {
        const resultCard = document.createElement('div');
        resultCard.classList.add('tuition-center-card');

        // Set the result card's inner HTML to match index.php format
        resultCard.innerHTML = `
            <img src="${center.image}" alt="${center.name}">
            <div class="card-content">
                <h3>${center.name}</h3>
                <p class="distance">
                    <i class="fas fa-location-dot"></i> 
                    ${typeof center.distance === 'number' ? center.distance.toFixed(1) : center.distance} km
                </p>
                ${center.avg_rating ? `
                    <p class="rating">
                        <i class="fas fa-star"></i>
                        ${parseFloat(center.avg_rating).toFixed(1)}/5
                        <span class="review-count">
                            <i class="fas fa-comment"></i>
                            ${center.review_count || 0}
                        </span>
                    </p>
                ` : `
                    <p class="rating">
                        <i class="far fa-star"></i>
                        0.0/5
                        <span class="review-count">
                            <i class="fas fa-comment"></i>
                            0
                        </span>
                    </p>
                `}
                <button class="favorite-btn ${center.favorite ? 'active' : ''}" 
                    data-center-id="${center.id}"
                    ${!center.user_logged_in ? 'data-guest="true"' : ''}>
                    <i class="fas fa-heart"></i>
                </button>
                <a href="tuition_details.php?id=${center.id}" 
                   class="btn btn-primary details-btn">Details</a>
            </div>
        `;

        resultsContainer.appendChild(resultCard);
    });

    // Initialize favorite buttons after adding cards
    initializeFavoriteButtons();
}

// Initialize favorite buttons
function initializeFavoriteButtons() {
    // Add event listeners to all favorite buttons
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        // Add event listener to each button
        btn.addEventListener('click', () => {
            // Toggle the active class
            btn.classList.toggle('active');
            const centerId = btn.getAttribute('data-center-id');
            toggleFavorite(centerId);
        });
    });
}

// Toggle favorite
function toggleFavorite(centerId) {
    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `center_id=${centerId}`
    })
    // Handle the response
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(data.message);
        } else {
            console.error('Failed to toggle favorite:', data.error);
        }
    })
    .catch(err => console.error('Error toggling favorite:', err));
}

// Periodically update the user's location
function startLocationTracking() {
    setInterval(() => {
        getLocation();
    }, 60000); // Update every minute
}

// Call this function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    getLocation();
    startLocationTracking();
});

// Update sort icons after results are loaded
function updateSortIcons() {
    // Get the sort by value
    const sortBy = document.getElementById('sortBy').value;
    // Remove the active-sort class from all sort icons
    document.querySelectorAll('.sort-icon').forEach(icon => {
        icon.classList.remove('active-sort');
    });
    // Add the active-sort class to the appropriate sort icon
    document.querySelector(`[data-sort="${sortBy}"]`)?.classList.add('active-sort');
}

function refreshCenterData() {
    fetch('fetch_tuition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lat=${userLocation.lat}&lon=${userLocation.lon}`
    })
    .then(response => response.json())
    .then(data => {
        const grid = document.getElementById('recommendation-grid');
        if (grid) {  // Check if grid exists before proceeding
            data.forEach(center => {
                const cards = grid.querySelectorAll('.tuition-center-card');
                cards.forEach(card => {
                    if (card.dataset.centerId === center.id) {
                        const ratingEl = card.querySelector('.rating');
                        if (ratingEl) {  // Check if rating element exists
                            ratingEl.innerHTML = `
                                <i class="${center.avg_rating > 0 ? 'fas' : 'far'} fa-star"></i>
                                ${parseFloat(center.avg_rating).toFixed(1)}/5
                            `;
                        }
                    }
                });
            });
        }
    })
    .catch(error => console.error('Error refreshing center data:', error));
}
