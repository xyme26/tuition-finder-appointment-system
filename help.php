<?php
session_start();
$current_page = 'help';
require_once 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Page</title>
    <link rel="stylesheet" href="style.css">

    <style>
        /* Admin section styles remain the same */
        .admin-section {
            background-color: #92a4d5;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: 40px;
            width: 100%;
            border-radius: 8px;
        }
        .admin-section h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .admin-section button {
            background-color: #2b168a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .admin-section button:hover {
            background-color: #9daaf2;
            color: #1a2238;
        }
        @media (max-width: 768px) {
            .faq-container {
                width: 95%;
            }
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <br><br>
   
    <!-- Frequently Asked Questions (FAQs) -->
    <h1 class="mb-4">
    <i class="fas fa-circle-question"></i> 
        Frequently Asked Questions (FAQs)
    </h1>
        <div class="accordion" id="faqAccordion">
            <!-- Account-related questions -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#q1">
                        How do I create an account?
                    </button>
                </h2>
                <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        To create an account, click on the "Sign Up" button on the homepage and fill out the registration form with your details.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">
                        How do I reset my password?
                    </button>
                </h2>
                <div id="q2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        On the login page, click the "Forgot Password" link. Enter your email address and follow the instructions to reset your password.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q3">
                        How do I change my profile information?
                    </button>
                </h2>
                <div id="q3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Click on your username at the top right of the website to access your profile. On the personal details page, you can edit your information by clicking the "Edit" buttons next to each field.
                    </div>
                </div>
            </div>

            <!-- Appointment-related questions -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q4">
                        How do I make an appointment?
                    </button>
                </h2>
                <div id="q4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        When viewing a tuition center's details, you will see a "Book Appointment" button. Click on it to make an appointment.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q5">
                        Can I cancel my appointment?
                    </button>
                </h2>
                <div id="q5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you can cancel your appointment. We encourage users to cancel early to ensure that the tuition centers are informed in advance (except for emergencies).
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q6">
                        Can I reschedule my appointment?
                    </button>
                </h2>
                <div id="q6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you can reschedule your appointment if you cannot attend at the original appointment time.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q7">
                        What happens if I miss my appointment?
                    </button>
                </h2>
                <div id="q7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        If you miss your appointment, it's best to contact the tuition center directly to explain and possibly reschedule. Repeated missed appointments may affect your ability to book future appointments.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q8">
                        Can I make multiple appointments?
                    </button>
                </h2>
                <div id="q8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you can make multiple appointments with different tuition centers or at different times.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q9">
                        How long will it take to receive confirmation of my appointment from the tuition center?
                    </button>
                </h2>
                <div id="q9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Appointment confirmation times may vary. You can check the status of your appointment in your profile. If you haven't received a confirmation within a day, we recommend contacting the tuition center directly.
                    </div>
                </div>
            </div>

            <!-- Other general questions -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q10">
                        What is the purpose of the survey form?
                    </button>
                </h2>
                <div id="q10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        This survey form is used to help users find a suitable tuition center.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q11">
                        Can I complete my payment on this website?
                    </button>
                </h2>
                <div id="q11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        No, you cannot pay through this website. You can only pay at the tuition center of your choice.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q12">
                        Can I change my location or address?
                    </button>
                </h2>
                <div id="12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you can change your address on the personal details page in your profile.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q13">
                        Are there additional fees for making an appointment?
                    </button>
                </h2>
                <div id="q13" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        No, there is no additional fee for making an appointment through our website.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q14">
                        How do I contact a tuition center?
                    </button>
                </h2>
                <div id="q14" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can find contact information in the description of each tuition center on their details page.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q15">
                        What is the purpose of this website?
                    </button>
                </h2>
                <div id="q15" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        This website is designed for academic use to help users find and book appointments with tuition centers.
                    </div>
                </div>
            </div>
        </div>

    <!-- Admin Help Section -->
    <div class="admin-section">
        <h2>I'm an Admin</h2>
        <p>If you are an admin and need help, click the button below:</p>
        <a href="admin_help.php">
            <button>Admin Help</button>
        </a>
    </div>
    <br><br>
    <?php include 'footer.php'; ?>

    <script>
        // Get all accordion headers
        var acc = document.getElementsByClassName("accordion");
    
        // Add event listeners to each accordion header
        for (var i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                // Collapse all panels before expanding the clicked one
                for (var j = 0; j < acc.length; j++) {
                    // Get the next sibling element (the panel)
                    var panel = acc[j].nextElementSibling;
                    // Check if the current header is not the clicked one
                    if (acc[j] !== this) {
                        // Remove the active class from the current header
                        acc[j].classList.remove("active");
                        // Collapse the current panel
                        panel.style.maxHeight = null; // Collapse other panels
                    }
                }

                // Toggle the clicked panel
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    // Collapse the panel if it is expanded
                    panel.style.maxHeight = null; 
                } else {
                    // Expand the panel if it is collapsed
                    panel.style.maxHeight = panel.scrollHeight + "px"; 
                }
            });
        }

        // Add event listener to check for stored survey answers
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there are stored survey answers
            const storedSurveyAnswers = sessionStorage.getItem('surveyAnswers');
            if (storedSurveyAnswers && new URLSearchParams(window.location.search).get('survey') === 'completed') {
                // Parse the stored answers
                const surveyAnswers = JSON.parse(storedSurveyAnswers);

                // Update recommendations with the stored answers
                updateRecommendations(surveyAnswers);
                
                // Clear the stored answers
                sessionStorage.removeItem('surveyAnswers');
                
                // Scroll to recommendations
                document.getElementById('recommendation-grid').scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html>
