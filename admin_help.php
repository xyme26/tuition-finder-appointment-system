<?php
session_start();
$current_page = 'admin_help';
include 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Help - Tuition Finder</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br>
    <h1 class="mb-4">Admin Help Centre</h1>
        
    <!-- FAQ Accordion -->
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#q1">
                    How do I add my tuition center to this website?
                </button>
            </h2>
            <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    To add your tuition center, follow these steps:
                    <ol>
                        <li>Log in to your admin account.</li>
                        <li>Navigate to the "Manage Tuition Centers" section.</li>
                        <li>Click on the "Add New Tuition Center" button.</li>
                        <li>Fill in the required information, including name, address, description, and upload images.</li>
                        <li>Click "Save" to add your tuition center to the website.</li>
                    </ol>
                    Your tuition center will be visible to users automatically.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">
                    How do I edit tuition center's details?
                </button>
            </h2>
            <div id="q2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    To edit the tuition center's details:
                    <ol>
                        <li>Log in to your admin account.</li>
                        <li>Go to the "Manage Tuition Centers" section.</li>
                        <li>Find your tuition center in the list and click the "Edit" button.</li>
                        <li>Update the information as needed (name, address, description, images, etc.).</li>
                        <li>Click "Save Changes" to update your profile.</li>
                    </ol>
                    Note: The rating cannot be edited as it's based on user reviews.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q3">
                    Can I cancel appointments with users?
                </button>
            </h2>
            <div id="q3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, you can cancel appointments. Here's how:
                    <ol>
                        <li>Go to the "Manage Appointments" section in your admin dashboard.</li>
                        <li>Find the appointment you want to cancel.</li>
                        <li>Click on the "Cancel" button next to the appointment.</li>
                        <li>Provide a reason for the cancellation.</li>
                        <li>Tell the user the suggestion date and time.</li>
                        <li>Confirm the action.</li>
                    </ol>
                    Important: Always inform the user about cancellations or rescheduling in advance. Communicate the reason to avoid misunderstandings.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q4">
                    Which users can leave reviews?
                </button>
            </h2>
            <div id="q4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Only users who have completed an appointment with your tuition center can leave reviews. The system automatically enables the review option for users after their appointment date has passed.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q5">
                    How do I manage appointment availability?
                </button>
            </h2>
            <div id="q5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    To manage your appointment availability:
                    <ol>
                        <li>Go to the "Manage Availability" section in your admin dashboard.</li>
                        <li>Set your regular working hours for each day of the week.</li>
                        <li>Mark any specific dates as unavailable (e.g., holidays).</li>
                        <li>Set the duration for each appointment slot.</li>
                        <li>Specify the maximum number of appointments per slot, if applicable.</li>
                        <li>Save your changes.</li>
                    </ol>
                    Users will only be able to book appointments during your available times.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q6">
                    How do I respond to user reviews?
                </button>
            </h2>
            <div id="q6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    To respond to user reviews:
                    <ol>
                        <li>Go to the "Manage Reviews" section in your admin dashboard.</li>
                        <li>Find the review you want to respond to.</li>
                        <li>Click on the "Reply" button next to the review.</li>
                        <li>Type your response in the text box provided.</li>
                        <li>Click "Submit" to post your reply.</li>
                    </ol>
                    Responding to reviews shows that you value user feedback and can help improve your tuition center's reputation.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q7">
                    How can I view and manage user appointments?
                </button>
            </h2>
            <div id="q7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    To view and manage user appointments:
                    <ol>
                        <li>Go to the "Manage Appointments" section in your admin dashboard.</li>
                        <li>You'll see a list of all upcoming and past appointments.</li>
                        <li>Use the filters to sort appointments by date, status, or user.</li>
                        <li>Click on an appointment to view its details.</li>
                        <li>From here, you can confirm, reschedule, or cancel appointments as needed.</li>
                    </ol>
                    Regularly checking and managing appointments helps ensure smooth operations for your tuition center.
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

