<?php
session_start();
$current_page = 'appointment';
require_once 'connection.php';

// Initialize variables
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Only fetch appointments if user is logged in
if ($isLoggedIn) {
    // Query to fetch upcoming appointments
    $upcoming_query = "
        SELECT a.id, a.appointment_datetime, a.reason, a.status, tc.name AS tuition_center_name
        FROM appointments a
        JOIN tuition_centers tc ON a.tuition_center_id = tc.id
        WHERE a.user_id = ? 
        AND a.status NOT IN ('cancelled', 'completed')
        ORDER BY a.appointment_datetime ASC
    ";
    // Prepare the statement
    $upcoming_stmt = $conn->prepare($upcoming_query);
    // Bind the user ID parameter
    $upcoming_stmt->bind_param("i", $user_id);
    // Execute the statement
    $upcoming_stmt->execute();
    // Get the result
    $upcoming_result = $upcoming_stmt->get_result();

    // Query to fetch past appointments (including completed and cancelled)
    $past_query = "
        SELECT a.*, tc.name AS tuition_center_name
        FROM appointments a
        JOIN tuition_centers tc ON a.tuition_center_id = tc.id
        WHERE a.user_id = ? 
        AND (
            a.status IN ('completed', 'cancelled')
            OR (a.appointment_datetime < NOW() AND a.status NOT IN ('completed', 'cancelled'))
        )
        AND a.appointment_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY a.appointment_datetime DESC
    ";
    // Prepare the statement
    $past_stmt = $conn->prepare($past_query);
    // Bind the user ID parameter
    $past_stmt->bind_param("i", $user_id);
    // Execute the statement
    $past_stmt->execute();
    // Get the result
    $past_result = $past_stmt->get_result();
}
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
    <title>Appointment Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Container */
        .container {
            max-width: 1200px;
        }

        /* Appointment section */
        .appointment-section {
            margin: 20px 0;
        }

        /* Appointment card */
        .appointment-card {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 15px;
            text-align: left;
            background-color: #f8f9fa;
        }

        /* Disable completed button */
        .complete-btn.disabled {
            pointer-events: none;
            opacity: 0.65;
        }

        /* Media Queries */
        @media screen and (max-width: 992px) {
            .container {
                padding: 0 15px;
            }

            .row {
                margin: 0 -10px;
            }

            .col-md-4 {
                padding: 0 10px;
            }

            .card {
                margin-bottom: 20px;
            }
        }

        @media screen and (max-width: 768px) {
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .card {
                margin-bottom: 15px;
            }

            .modal-dialog {
                margin: 10px;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                width: 100%;
                margin: 5px 0;
            }

            .status-badge {
                display: inline-block;
                margin-top: 5px;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-footer .btn {
                width: 100%;
                margin: 5px 0;
            }
            .web-links {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .web-links a {
                margin: 5px 0; /* Vertical spacing for links */
            }
        }

        /* Completed badge */
        .completed {
            background-color: #198754 !important;
            color: white !important;
        }

        /* Badge */
        .card-footer .badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <br><br>
    <main class="container mt-4">
        <h1>My Appointments</h1>

        <!-- If user is not logged in -->
        <?php if (!$isLoggedIn): ?>
            <!-- Alert message -->
            <div class="alert alert-warning text-center" role="alert">
                <h4 class="alert-heading">Please Log In</h4>
                <p>You need to be logged in to view your appointments.</p>
                <hr>
                <a href="login.php" class="btn btn-primary">Login Now</a>
                <a href="sign_up.php" class="btn btn-secondary ms-2">Sign Up</a>
            </div>
        <!-- If user is logged in -->
        <?php else: ?>
            <!-- Existing appointment sections -->
            <section class="upcoming-appointments mb-5">
                <h2>Upcoming Appointments</h2>
                <!-- If there are upcoming appointments -->
                <?php if ($upcoming_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($appointment = $upcoming_result->fetch_assoc()): ?>
                            <?php if ($appointment['status'] !== 'completed'): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($appointment['tuition_center_name']); ?></h5>
                                            <p class="card-text">Date: <?php echo date('Y-m-d', strtotime($appointment['appointment_datetime'])); ?></p>
                                            <p class="card-text">Time: <?php echo date('H:i', strtotime($appointment['appointment_datetime'])); ?></p>
                                            <p class="card-text">
                                                Status: 
                                                <span class="status-badge badge <?php echo ($appointment['status'] === 'completed') ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                                </span>
                                            </p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal<?php echo $appointment['id']; ?>">
                                                Details
                                            </button>
                                        </div>
                                        <div class="card-footer">
                                            <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        onclick="completeAppointment(<?php echo $appointment['id']; ?>, this)">
                                                    Completed
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No upcoming appointments.</p>
                <?php endif; ?>
            </section>

            <!-- Past appointments section -->
            <section class="past-appointments mb-5">
                <h3>Appointment History (Last 30 Days)</h3>
                <?php if ($past_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($appointment = $past_result->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($appointment['tuition_center_name']); ?></h5>
                                        <p class="card-text">Date: <?php echo date('Y-m-d', strtotime($appointment['appointment_datetime'])); ?></p>
                                        <p class="card-text">Time: <?php echo date('H:i', strtotime($appointment['appointment_datetime'])); ?></p>
                                        <p class="card-text">
                                            Status: 
                                            <span class="status-badge <?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                            </span>
                                        </p>
                                        <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#appointmentModal<?php echo $appointment['id']; ?>">
                                            Details
                                        </button>
                                    </div>
                                    <div class="card-footer">
                                        <span class="badge <?php echo ($appointment['status'] === 'completed') ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- If no past appointments are found -->
                    <div class="alert alert-info" role="alert">
                        No past appointments found in the last 30 days.
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
    
    <!-- If user is logged in -->
    <?php if ($isLoggedIn): ?>
        <?php 
        // Reset the result pointers
        $upcoming_result->data_seek(0);
        $past_result->data_seek(0);
        // Loop through upcoming appointments
        while ($appointment = $upcoming_result->fetch_assoc()): 
        ?>
            <!-- Upcoming appointment modal -->
            <div class="modal fade" id="appointmentModal<?php echo $appointment['id']; ?>" tabindex="-1" 
                 aria-labelledby="appointmentModalLabel<?php echo $appointment['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="appointmentModalLabel<?php echo $appointment['id']; ?>">
                                Appointment Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <p><strong>Tuition Center:</strong> <?php echo htmlspecialchars($appointment['tuition_center_name']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($appointment['appointment_datetime'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('H:i', strtotime($appointment['appointment_datetime'])); ?></p>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge <?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                </span>
                            </p>
                            <!-- If appointment is not completed or cancelled -->
                            <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                <button type="button" class="btn btn-primary mt-3 me-2" 
                                        onclick="showRescheduleForm(<?php echo $appointment['id']; ?>)">
                                    Reschedule
                                </button>
                                <button type="button" class="btn btn-danger mt-3" 
                                        onclick="showCancellationForm(<?php echo $appointment['id']; ?>)">
                                    Cancel Booking
                                </button>
                            <?php endif; ?>
                        </div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Loop through past appointments -->
        <?php while ($appointment = $past_result->fetch_assoc()): ?>
            <div class="modal fade" id="appointmentModal<?php echo $appointment['id']; ?>" tabindex="-1" aria-labelledby="appointmentModalLabel<?php echo $appointment['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="appointmentModalLabel<?php echo $appointment['id']; ?>">Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                            <p><strong>Tuition Center:</strong> <?php echo htmlspecialchars($appointment['tuition_center_name']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($appointment['appointment_datetime'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('H:i', strtotime($appointment['appointment_datetime'])); ?></p>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></p>
                        </div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Cancellation confirmation -->
        <div class="modal fade" id="cancellationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this appointment? Please tell me your reason(s):</p>
                        <form id="cancellationForm">
                            <input type="hidden" id="appointmentId" name="appointmentId">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="Schedule Conflict" id="reason1">
                                <label class="form-check-label" for="reason1">Schedule Conflict</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="Changed Mind" id="reason2">
                                <label class="form-check-label" for="reason2">Changed Mind</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="Found Alternative" id="reason3">
                                <label class="form-check-label" for="reason3">Found Alternative</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="Other" id="reason4">
                                <label class="form-check-label" for="reason4">Other</label>
                            </div>
                        </form>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" onclick="cancelAppointment()">Confirm Cancellation</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rescheduling -->
        <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <form id="rescheduleForm">
                            <input type="hidden" id="rescheduleAppointmentId" name="appointmentId">
                            <div class="mb-3">
                                <label for="newDate" class="form-label">New Date</label>
                                <input type="date" class="form-control" id="newDate" name="newDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="newTime" class="form-label">New Time</label>
                                <input type="time" class="form-control" id="newTime" name="newTime" required>
                            </div>
                            <div class="mb-3">
                                <label for="rescheduleReason" class="form-label">Reason for Rescheduling</label>
                                <textarea class="form-control" id="rescheduleReason" name="rescheduleReason" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="rescheduleAppointment()">Confirm Reschedule</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation reason modal -->
        <div class="modal fade" id="cancellationReasonModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancellation Reason</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Reason will be displayed here -->
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Toggle between list and grid views
        document.getElementById('listViewBtn').addEventListener('click', function() {
            document.getElementById('appointmentHistory').style.display = 'block';
            document.getElementById('appointmentHistoryGrid').style.display = 'none';
        });
        // Grid view button
        document.getElementById('gridViewBtn').addEventListener('click', function() {
            document.getElementById('appointmentHistory').style.display = 'none';
            document.getElementById('appointmentHistoryGrid').style.display = 'flex';
        });

        // Set the current year for the copyright
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Show cancellation form
        function showCancellationForm(appointmentId) {
        document.getElementById('appointmentId').value = appointmentId;
        var cancellationModal = new bootstrap.Modal(document.getElementById('cancellationModal'));
        cancellationModal.show();
        }

        // Cancel appointment
        function cancelAppointment() {
            var form = document.getElementById('cancellationForm');
            var formData = new FormData(form);
            
            // Fetch the cancel_appointment.php script
            fetch('cancel_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
            if (data.success) {
                alert('Appointment cancelled successfully');
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to cancel appointment: ' + data.message);
                }
            })
            // Catch any errors
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the appointment');
            });
        }

        // Show reschedule form
        function showRescheduleForm(appointmentId) {
            document.getElementById('rescheduleAppointmentId').value = appointmentId;
            var rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
            rescheduleModal.show();
        }

        // Reschedule appointment
        function rescheduleAppointment() {
            var form = document.getElementById('rescheduleForm');
            var formData = new FormData(form);

            // Fetch the reschedule_appointment.php script
            fetch('reschedule_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment rescheduled successfully');
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Failed to reschedule appointment: ' + data.message);
                }
            })
            // Catch any errors
            .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rescheduling the appointment');
            });
        }

        // Show cancellation reason
        function showCancellationReason(appointmentId) {
            // Fetch the get_cancellation_reason.php script
            fetch('get_cancellation_reason.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Use a modal instead of an alert for better user experience
                    $('#cancellationReasonModal .modal-body').text(data.reason);
                    $('#cancellationReasonModal').modal('show');
                } else {
                    alert('Failed to fetch cancellation reason: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the cancellation reason');
            });
        }

        // Complete appointment
        function completeAppointment(appointmentId, buttonElement) {
            // Confirmation
            if (confirm('Are you sure you want to mark this appointment as completed?')) {
                // Fetch the complete_appointment_user.php script
                fetch('complete_appointment_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                    body: `appointment_id=${appointmentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                    // Find the card containing the button
                    const card = buttonElement.closest('.card');
                    const cardContainer = card.closest('.col-md-4');
                    
                    // Create a clone of the card for the past appointments section
                    const cardClone = cardContainer.cloneNode(true);
                    
                    // Update the status badge in both original and cloned card
                    const statusElements = cardClone.querySelectorAll('.status-badge, .badge');
                    statusElements.forEach(element => {
                        element.className = 'badge bg-success';
                        element.textContent = 'Completed';
                    });
                    
                    // Remove the complete button from the cloned card
                    const completeButton = cardClone.querySelector('button[onclick^="completeAppointment"]');
                    if (completeButton) {
                        completeButton.remove();
                    }
                    
                    // Find the past appointments section
                    const pastAppointmentsRow = document.querySelector('.past-appointments .row');
                    if (pastAppointmentsRow) {
                        // Insert the cloned card at the beginning of past appointments
                        pastAppointmentsRow.insertBefore(cardClone, pastAppointmentsRow.firstChild);
                        
                        // Remove the original card from upcoming appointments
                        cardContainer.remove();
                        
                        // Check if the upcoming appointments section is empty
                        const upcomingRow = document.querySelector('.upcoming-appointments .row');
                        if (upcomingRow && !upcomingRow.querySelector('.col-md-4')) {
                            upcomingRow.innerHTML = '<div class="col-12"><p>No upcoming appointments.</p></div>';
                        }
                    }
                    // Alert message
                    alert('Appointment marked as completed successfully!');
                } else {
                    alert('Failed to complete appointment: ' + data.message);
                }
            })
            // Catch any errors
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while completing the appointment');
            });
        }
    }

    // Check if there are stored survey answers
    document.addEventListener('DOMContentLoaded', function() {
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
