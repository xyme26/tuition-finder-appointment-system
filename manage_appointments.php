<?php
// admin/manage_appointments.php
session_start();
$current_page = 'manage_appointments';
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

// Check if the session has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Unset all session variables
    session_unset();
    // Destroy the session
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

// Include the database connection
require_once 'connection.php';

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $appointment_id = intval($_GET['delete']);

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    // Check if the delete statement affected any rows
    if ($stmt->affected_rows > 0) {
        $success = "Appointment deleted successfully.";
    } else {
        $error = "Failed to delete appointment.";
    }

    $stmt->close();
}

// Handle approval if requested
if (isset($_GET['approve'])) {
    $appointment_id = intval($_GET['approve']);

    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    // Check if the update statement affected any rows
    if ($stmt->affected_rows > 0) {
        $success = "Appointment approved successfully.";
        
        // Create notification for the user
        $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) SELECT user_id, CONCAT('Your appointment for ', DATE_FORMAT(appointment_datetime, '%Y-%m-%d'), ' at ', TIME_FORMAT(appointment_datetime, '%H:%i'), ' has been approved.') FROM appointments WHERE id = ?");
        // Bind the appointment ID parameter
        $notification_stmt->bind_param("i", $appointment_id);
        // Execute the statement
        $notification_stmt->execute();
        // Close the statement
        $notification_stmt->close();
    } else {
        // Set error message
        $error = "Failed to approve appointment.";
    }

    // Close the statement
    $stmt->close();
}

// Fetch all appointments
$sql = "SELECT a.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, 
        u.phone_number,
        tc.name AS tuition_center, 
        a.appointment_datetime, 
        a.status, 
        a.created_at
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN tuition_centers tc ON a.tuition_center_id = tc.id 
        WHERE a.status IS NOT NULL
        ORDER BY 
            CASE 
                WHEN a.status IN ('pending', 'approved') THEN 1
                WHEN a.status = 'completed' THEN 2
                WHEN a.status = 'cancelled' THEN 3
            END,
            a.appointment_datetime DESC";

// Execute the query
$result = $conn->query($sql);

// Function to send notification
function sendNotification($appointment_id) {
    global $conn;
    
    // Fetch appointment details
    $stmt = $conn->prepare("SELECT user_id, appointment_datetime FROM appointments WHERE id = ?");
    // Bind the appointment ID parameter
    $stmt->bind_param("i", $appointment_id);
    // Execute the statement
    $stmt->execute();
    // Get the result
    $result = $stmt->get_result();
    // Fetch the appointment details
    $appointment = $result->fetch_assoc();
    
    // Check if the appointment details were fetched successfully
    if ($appointment) {
        // Extract user ID and appointment datetime
        $user_id = $appointment['user_id'];
        $appointment_datetime = $appointment['appointment_datetime'];
        
        // Insert notification into notifications table
        $notification_text = "Your appointment for " . $appointment_datetime . " has been approved.";
        $insert_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        // Bind the user ID and notification text parameters
        $insert_stmt->bind_param("is", $user_id, $notification_text);
        // Execute the statement
        $insert_stmt->execute();
        // Close the statement
        $insert_stmt->close();
    }
    
    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br>
    <div class="container-fluid admin-dashboard-container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Manage Appointments</h2>

                <!-- Display success or error messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Appointments Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>User Contact</th>
                                <th>Tuition Center</th>
                                <th>Appointment Date/Time</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--Check if there are any appointments-->
                            <?php if ($result->num_rows > 0): ?>
                                <!--Loop through each appointment-->
                                <?php while($appointment = $result->fetch_assoc()): ?>
                                    <tr data-appointment-id="<?php echo htmlspecialchars($appointment['id']); ?>">
                                        <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['phone_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['tuition_center']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_datetime']); ?></td>
                                        <td>
                                            <?php 
                                            // Initialize status class and text
                                            $statusClass = '';
                                            $statusText = ucfirst(htmlspecialchars($appointment['status']));
                                            // Switch statement to set the status class based on the status
                                            switch($appointment['status']) {
                                                case 'completed':
                                                    $statusClass = 'badge bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'badge bg-danger';
                                                    break;
                                                case 'approved':
                                                    $statusClass = 'badge bg-primary';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'badge bg-warning';
                                                    break;
                                                default:
                                                    $statusClass = 'badge bg-secondary';
                                            }
                                            ?>
                                            <!--Display the status badge with the appropriate class and text-->
                                            <span class="<?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                            <?php 
                                            // Display the view reason button if the appointment was cancelled
                                            if ($appointment['status'] === 'cancelled') {
                                                echo ' <button class="btn btn-link btn-sm p-0" onclick="showCancellationReason(' . $appointment['id'] . ')">View Reason</button>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['created_at']); ?></td>
                                        <td>
                                            <!-- Display the action buttons for the appointment -->
                                            <div class="btn-group" role="group">
                                                <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="completeAppointment(<?php echo $appointment['id']; ?>)">
                                                        <i class="fas fa-check"></i> Complete
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                                                    <button class="btn btn-warning btn-sm" onclick="showCancelModal(<?php echo $appointment['id']; ?>)">
                                                        <i class="fas fa-ban"></i> Cancel
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-danger btn-sm" onclick="deleteAppointment(<?php echo $appointment['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Display a message if there are no appointments -->
                                <tr>
                                    <td colspan="7" class="text-center">No appointments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>

    <!-- Cancellation Reason Modal -->
    <div class="modal fade" id="cancellationReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancellation Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cancellationReasonBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Reschedule Reason Modal -->
    <div class="modal fade" id="rescheduleReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="rescheduleReasonBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Appointment Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelForm">
                        <input type="hidden" id="cancelAppointmentId" name="appointmentId">
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Reason for Cancellation</label>
                            <textarea class="form-control" id="cancelReason" name="cancelReason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="newDate" class="form-label">Suggest New Date (optional)</label>
                            <input type="date" class="form-control" id="newDate" name="newDate">
                        </div>
                        <div class="mb-3">
                            <label for="newTime" class="form-label">Suggest New Time (optional)</label>
                            <input type="time" class="form-control" id="newTime" name="newTime">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="cancelAppointment()">Confirm Cancellation</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to show the cancellation reason
        function showCancellationReason(appointmentId) {
            fetch('get_cancellation_reason.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                // Check if the response contains a success property
                if (data.success) {
                    // Set the cancellation reason in the modal body
                    document.getElementById('cancellationReasonBody').innerHTML = data.reason;
                    // Show the cancellation reason modal
                    var modal = new bootstrap.Modal(document.getElementById('cancellationReasonModal'));
                    modal.show();
                } else {
                    alert('Failed to fetch cancellation reason: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the cancellation reason');
            });
        }

        // Function to show the reschedule reason
        function showRescheduleReason(appointmentId) {
            fetch('get_reschedule_reason.php?id=' + appointmentId)
                .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('rescheduleReasonBody').innerHTML = data.reason;
                    var modal = new bootstrap.Modal(document.getElementById('rescheduleReasonModal'));
                    modal.show();
                } else {
                    alert('Failed to fetch reschedule reason: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the reschedule reason');
            });
        }

        // Function to show the cancel appointment modal
        function showCancelModal(appointmentId) {
            // Set the appointment ID in the hidden input field
            document.getElementById('cancelAppointmentId').value = appointmentId;
            // Show the cancel appointment modal
            var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
            cancelModal.show();
        }

        // Function to cancel the appointment
        function cancelAppointment() {
            // Get the form element
            var form = document.getElementById('cancelForm');
            // Create a FormData object from the form
            var formData = new FormData(form);

            // Send the form data to the cancel_appointment_admin.php script
            fetch('cancel_appointment_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Check if the response contains a success property
                if (data.success) {
                    alert('Appointment cancelled successfully');
                    location.reload(); // Reload the page to reflect changes
            } else {
                    alert('Failed to cancel appointment: ' + data.message);
                }
            })  
            .catch(error => {
                // Log any errors that occurred during the fetch operation
                console.error('Error:', error);
                // Display an alert if an error occurred
                alert('An error occurred while cancelling the appointment');
            });
        }

        // Function to complete the appointment
        function completeAppointment(appointmentId) {
        // Find the row first
        const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
        if (!row) {
            console.error('Row not found');
            return;
        }

        // Find the status cell
        const statusCell = row.querySelector('td:nth-child(5)');
        // Get the current status
        const currentStatus = statusCell.textContent.trim().toLowerCase();
        
        // Check if already completed
        if (currentStatus === 'completed') {
            alert('This appointment is already completed.');
            return;
        }

        // Confirm the action
        if (confirm('Are you sure you want to mark this appointment as completed?')) {
            // Send the appointment ID to the complete_appointment.php script
            fetch('complete_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `appointment_id=${appointmentId}`
            })
            // Parse the response as JSON
            .then(response => response.json())
            .then(data => {
                // Check if the response contains a success property
                if (data.success) {
                    // Update status badge
                    statusCell.innerHTML = '<span class="badge bg-success">Completed</span>';
                    
                    // Remove complete and cancel buttons, keep delete button
                    const actionCell = row.querySelector('td:last-child');
                    if (actionCell) {
                        const deleteButton = actionCell.querySelector('.btn-danger');
                        actionCell.querySelector('.btn-group').innerHTML = '';
                        actionCell.querySelector('.btn-group').appendChild(deleteButton);
                    }
                    // Display a success message
                    alert('Appointment marked as complete.');
                } else {
                    alert('Failed to complete appointment: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                // Log any errors that occurred during the fetch operation
                console.error('Error:', error);
                // Display an alert if an error occurred
                alert('An error occurred while completing the appointment');
            });
        }
        }

        // Function to delete the appointment   
        function deleteAppointment(appointmentId) {
            // Confirm the action
            if (confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
                // Send the appointment ID to the delete_appointment.php script 
                fetch('delete_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `appointment_id=${appointmentId}`
                })
                // Parse the response as JSON
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
                    if (row) {
                        row.remove();
                    }
                        // Display a success message
                        alert('Appointment deleted successfully.');
                    } else {
                        alert('Failed to delete appointment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    // Log any errors that occurred during the fetch operation
                    console.error('Error:', error);
                    // Display an alert if an error occurred
                    alert('An error occurred while deleting the appointment');
                });
            }
        }
    </script>
</body>
</html>
