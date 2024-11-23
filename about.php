<?php
session_start();
$current_page = 'about';
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Tuition Finder</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* About Page Styling */
        .about-container {
        width: 90% !important; /* Override Bootstrap's container width */
        max-width: 1200px;
        margin: 80px auto 50px;
        padding: 20px;
        background-color: transparent; 
        border-radius: 8px;
        box-shadow: none; 
        }

        /* Feature Card Styling */
        .feature-card {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            height: 100%;
        }
        
        /* Subject Grid Styling */
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .subject-item {
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            text-align: center;
        }
        
        /* Timeline Styling */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 40px auto;
        }
        
        .timeline-item {
            padding: 20px;
            margin-bottom: 30px;
            position: relative;
            border-left: 2px solid #007bff;
            margin-left: 50px;
        }
        
        .timeline-number {
            position: absolute;
            left: -60px;
            width: 40px;
            height: 40px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
        }

        /* Media Queries */
        @media screen and (max-width: 992px) {
            .about-container {
                padding: 15px;
            }

            .about-section {
                padding: 20px;
            }

            .team-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media screen and (max-width: 768px) {
            .about-section {
                padding: 15px;
                margin-bottom: 20px;
            }

            .team-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }

            .team-member img {
                width: 120px;
                height: 120px;
            }

            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- About Page Container -->
    <div class="container about-container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <!-- About Us Title -->
                <h1 class="text-center mb-4">
                    <i class="fas fa-info-circle"></i> About Us
                </h1>
                <br>
                <!-- Our Mission Section -->
                <section class="mb-5">
                    <h2>
                        <i class="fas fa-bullseye"></i> Our Mission
                    </h2>
                    <p style="text-align: justify;">Tuition Finder is dedicated to connecting students with the perfect tuition centers in their area. We understand that finding the right educational support can be challenging, which is why we've created a platform that makes the process simple and efficient.</p>
                </section>

                <!-- Key Features Section -->
                <section class="mb-5">
                    <h2>
                        <i class="fas fa-star"></i> Key Features
                    </h2>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="feature-card">
                                <i class="fas fa-location-dot fa-2x mb-3 text-primary"></i>
                                <h3>Location-Based Search</h3>
                                <p>Find tuition centers near you with our advanced location-based search system.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="feature-card">
                                <i class="fas fa-calendar-check fa-2x mb-3 text-primary"></i>
                                <h3>Easy Appointment Booking</h3>
                                <p>Book appointments with tuition centers directly through our platform.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="feature-card">
                                <i class="fas fa-star fa-2x mb-3 text-primary"></i>
                                <h3>Reviews & Ratings</h3>
                                <p>Make informed decisions with our community-driven review system.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="feature-card">
                                <i class="fas fa-lightbulb fa-2x mb-3 text-primary"></i>
                                <h3>Smart Recommendations</h3>
                                <p>Get personalized tuition center recommendations based on your preferences.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Available Subjects Section -->
                <section class="mb-5">
                    <h2>
                        <i class="fas fa-book-open"></i> Available Subjects
                    </h2>
                    <div class="subject-grid">
                        <div class="subject-item"><i class="fas fa-calculator"></i> Mathematics</div>
                        <div class="subject-item"><i class="fas fa-flask"></i> Science</div>
                        <div class="subject-item"><i class="fas fa-book"></i> English</div>
                        <div class="subject-item"><i class="fas fa-dna"></i> Biology</div>
                        <div class="subject-item"><i class="fas fa-atom"></i> Chemistry</div>
                        <div class="subject-item"><i class="fas fa-magnet"></i> Physics</div>
                        <div class="subject-item"><i class="fas fa-square-root-variable"></i> Add Math</div>
                        <div class="subject-item"><i class="fas fa-coins"></i> Account</div>
                        <div class="subject-item"><i class="fas fa-landmark"></i> History</div>
                        <div class="subject-item"><i class="fas fa-chart-line"></i> Economy</div>
                        <div class="subject-item"><i class="fas fa-language"></i> Bahasa Malaysia</div>
                    </div>
                </section>
        
                <!-- How It Works Section -->
                <section class="mb-5">
                    <h2>
                        <i class="fas fa-clipboard-list"></i> How It Works
                    </h2>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-number">1</div>
                            <h3>Search</h3>
                            <p>Enter your location and preferred subjects to find tuition centers near you.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-number">2</div>
                            <h3>Compare</h3>
                            <p>Review ratings, prices, and distance to find the perfect match.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-number">3</div>
                            <h3>Book</h3>
                            <p>Schedule appointments directly through our platform.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-number">4</div>
                            <h3>Learn</h3>
                            <p>Start your learning journey with your chosen tuition center.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
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

