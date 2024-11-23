<footer>
    <div class="web-links">
        <a href="about.php">About Us</a>
        <a href="help.php">FAQs</a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#welcomeModal">Features</a>
        <a href="login_admin.php">Admin Login</a>
    </div>
    <div class="feedback">
        <button id="feedbackBtn" data-bs-toggle="modal" data-bs-target="#feedbackModal">Give us feedback!</button>
    </div>
    <p>© <span id="currentYear"></span> Tuition Finder Website. All rights reserved.</p>
</footer>

<!-- Feedback Popup -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Website Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <div class="mb-3">
                        <label class="form-label">How was your experience on our website?</label>
                        <div class="btn-group" role="group" aria-label="Feedback rating">
                            <input type="radio" class="btn-check" name="rating" id="ratingGood" value="good" autocomplete="off">
                            <label class="btn btn-outline-success" for="ratingGood">😃 Good</label>

                            <input type="radio" class="btn-check" name="rating" id="ratingNeutral" value="neutral" autocomplete="off">
                            <label class="btn btn-outline-warning" for="ratingNeutral">😐 Neutral</label>

                            <input type="radio" class="btn-check" name="rating" id="ratingBad" value="bad" autocomplete="off">
                            <label class="btn btn-outline-danger" for="ratingBad">😞 Bad</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feedbackComment" class="form-label">Comments (Tell me why)</label>
                        <textarea class="form-control" id="feedbackComment" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitFeedback">Submit Feedback</button>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Modal -->
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="welcomeModalLabel">Welcome to Tuition Finder! 👋</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Here's what you can do on our platform:</h6>
                <div class="features-list">
                    <p><i class="fas fa-search"></i> <strong>Search Tuition Centers:</strong> Find centers near you using our search filters</p>
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Location-Based Results:</strong> View centers sorted by distance from your location</p>
                    <p><i class="fas fa-calendar-check"></i> <strong>Book Appointments:</strong> Schedule sessions directly through our platform</p>
                    <p><i class="fas fa-star"></i> <strong>Reviews & Ratings:</strong> Read and write reviews for tuition centers</p>
                    <p><i class="fas fa-heart"></i> <strong>Favorites:</strong> Save your preferred tuition centers</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<script>
// Set the current year in footer
document.getElementById('currentYear').textContent = new Date().getFullYear();

// Add event listener to submit feedback button
document.getElementById('submitFeedback').addEventListener('click', function() {
    const rating = document.querySelector('input[name="rating"]:checked')?.value;
    const comment = document.getElementById('feedbackComment').value;

    // Check if rating is selected
    if (!rating) {
        alert('Please select a rating');
        return;
    }

    // Send feedback data to submit_feedback.php
    fetch('submit_feedback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            rating: rating,
            comment: comment
        })
    })
    // Parse the response as JSON
    .then(response => response.json())
    // Handle the response
    .then(data => {
        if (data.success) {
            // First reset the form
            document.getElementById('feedbackForm').reset();
            
            // Close the modal using Bootstrap's method
            const feedbackModal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
            if (feedbackModal) {
                feedbackModal.hide();
            } else {
                $('#feedbackModal').modal('hide'); // Fallback for older Bootstrap versions
            }

            // Show success message after modal is closed
            setTimeout(() => {
                alert('Thank you for your feedback!');
            }, 500);
        } else {
            alert('Error submitting feedback: ' + (data.error || 'Unknown error'));
        }
    })
    // Handle any errors
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting feedback. Please try again.');
    });
});
</script>