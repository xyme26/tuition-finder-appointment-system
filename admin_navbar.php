<header>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg" id="adminNavbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Tuition Finder - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent"
                aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <!-- Hamburger icon -->
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <!-- Collapsible content -->
            <div class="collapse navbar-collapse" id="adminNavbarContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manage_tuition') ? 'active' : ''; ?>" href="manage_tuition.php">Manage Tuition Centers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manage_appointments') ? 'active' : ''; ?>" href="manage_appointments.php">Manage Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manage_reviews') ? 'active' : ''; ?>" href="manage_reviews.php">Manage Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manage_feedback') ? 'active' : ''; ?>" href="manage_feedback.php">Users Feedback</a>
                    </li>
                </ul>
                <!-- Right-aligned elements -->
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </span>
                    <a class="btn btn-outline-light" href="admin_logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>
