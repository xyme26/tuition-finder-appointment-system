<header>
    <nav class="navbar navbar-expand-lg navbar-custom bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Tuition Finder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <!-- Collapsible navbar content -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'appointment') ? 'active' : ''; ?>" href="appointment.php">Appointment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>" href="about.php">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'help') ? 'active' : ''; ?>" href="help.php">Help</a>
                    </li>
                </ul>
                
                <!-- Move auth buttons to separate div for mobile -->
                <div class="auth-section">
                    <?php if (isset($_SESSION['username'])): ?>
                        <div class="profile-section">
                            <a id="profile" href="profile.php" 
                               class="<?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>&nbsp;<a id="notificationsDropdown" href="#" 
                               role="button" data-bs-toggle="dropdown" aria-expanded="false" class="position-relative">
                                <i class="fas fa-bell"></i>
                                <span class="badge" id="notificationCount"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="notificationList"></ul>
                        </div>
                    <?php else: ?>
                        <div class="auth-button-group">
                            <a id="signupLink" class="btn btn-outline-primary nav-link <?php echo ($current_page == 'sign_up') ? 'active' : ''; ?>" 
                            href="sign_up.php">Sign up</a>
                            <a id="loginLink" class="btn btn-outline-primary nav-link <?php echo ($current_page == 'login') ? 'active' : ''; ?>" 
                            href="login.php">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
    // Function to fetch notifications
    function fetchNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                const notificationCount = document.getElementById('notificationCount');
                notificationList.innerHTML = '';
                let unreadCount = 0;

                // Check if there are no notifications
                if (data.length === 0) {
                    notificationList.innerHTML = '<li class="dropdown-item">No notifications</li>';
                    notificationCount.style.display = 'none'; // Hide the badge
                } else {
                    // Loop through each notification
                    data.forEach(notification => {
                        // Create a new list item for each notification
                        const li = document.createElement('li');
                        li.className = notification.is_read ? 'dropdown-item' : 'dropdown-item unread';
                        li.dataset.notificationId = notification.id;
                        li.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="notification-icon bg-primary">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">${notification.message}</h6>
                                <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                            </div>
                        </div>
                    `;
                        // Add event listener to mark the notification as read
                        li.addEventListener('click', markAsRead);
                        // Append the list item to the notification list
                        notificationList.appendChild(li);

                        // Increment unread count if the notification is unread
                        if (!notification.is_read) {
                            unreadCount++;
                        }
                    });

                    // Update the notification count
                    notificationCount.textContent = unreadCount > 0 ? unreadCount : '';
                    notificationCount.style.display = unreadCount > 0 ? 'block' : 'none'; // Show or hide the badge
                }
            })
            // Handle any errors
            .catch(error => {
                console.error('Error fetching notifications:', error);
                const notificationList = document.getElementById('notificationList');
                notificationList.innerHTML = '<li class="dropdown-item">Error loading notifications</li>';
                notificationCount.style.display = 'none'; // Hide the badge on error
            });
    }

    // Function to mark a notification as read (when clicked)
    function markAsRead(event) {
        const notificationId = event.currentTarget.dataset.notificationId;

        // Send a POST request to mark the notification as read
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            // Send the notification ID as a form data parameter
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            // Check if the notification was marked as read successfully
            if (data.success) {
                event.currentTarget.classList.remove('unread'); // Remove unread class
                // Decrement the unread count
                const notificationCount = document.getElementById('notificationCount');
                let currentCount = parseInt(notificationCount.textContent) || 0; // Get current count
                if (currentCount > 0) {
                    currentCount--; // Decrement count
                    notificationCount.textContent = currentCount > 0 ? currentCount : ''; // Update badge
                    notificationCount.style.display = currentCount > 0 ? 'block' : 'none'; // Show or hide the badge
                }
            } else {
                // Log an error if marking the notification as read fails
                console.error('Failed to mark notification as read');
            }
        })
        // Handle any errors
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Fetch notifications every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Initial fetch
    document.addEventListener('DOMContentLoaded', fetchNotifications);

    // Add animation toggle for hamburger menu
    document.querySelector('.navbar-toggler').addEventListener('click', function() {
        document.querySelector('.hamburger').classList.toggle('active');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const navbar = document.querySelector('.navbar');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        const hamburger = document.querySelector('.hamburger');
        
        // Close the mobile menu if clicking outside
        if (!navbar.contains(event.target) && navbarCollapse.classList.contains('show')) {
            navbarCollapse.classList.remove('show');
            hamburger.classList.remove('active');
        }
    });
</script>
