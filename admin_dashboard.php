<?php
// admin_dashboard.php
session_start();
require_once 'connection.php';
$current_page = 'dashboard';
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

// Check if the session has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1"); // Redirect to login page with timeout message
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

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
    <title>Admin Dashboard - Tuition Finder</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <!-- Load Google Charts API -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="style.css">

    <!-- JavaScript for the charts -->
    <!--text/javascript means that the code is written in JavaScript-->
    <script type="text/javascript">
        // Load Google Charts
        google.charts.load('current', { packages: ['corechart', 'bar'] });

        // Callback to draw the charts
        google.charts.setOnLoadCallback(drawCharts);

        // Function to draw the charts
        function drawCharts() {
            fetchAppointmentsData(); // Fetch appointments data
            fetchTuitionCentersData(); // Fetch tuition centers data
            fetchUserAccountsData(); // Fetch user accounts data
        }

        // Function to fetch appointments data
        function fetchAppointmentsData(selectedYear = null, selectedMonth = null) {
            const currentYear = new Date().getFullYear(); // Get the current year
            const year = selectedYear || currentYear; // Use selected year or current year
            let url = `fetch_chart.php?type=appointments&year=${year}`; // Construct URL for fetching data
            if (selectedMonth && selectedMonth !== 'all') {
                url += `&month=${selectedMonth}`; // Append month if specified
            }

            // Fetch the data from the server
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Log the appointments data
                    console.log('Appointments data:', data);
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    // Draw the appointments chart
                    drawAppointmentsChart(data, year, selectedMonth);
                })
                .catch(error => {
                    // Log any errors that occurred during the fetch
                    console.error('Error fetching appointments data:', error);
                    document.getElementById('appointments_chart').innerHTML = 'Error loading appointments chart: ' + error.message;
                });
        }

        // Function to draw the appointments chart
        function drawAppointmentsChart(data, year, selectedMonth) {
            const chartData = [['Period', 'Appointments']]; // Initialize chart data
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            if (selectedMonth && selectedMonth !== 'all') {
                // If a specific month is selected, show daily data for that month
                const daysInMonth = new Date(year, selectedMonth, 0).getDate(); // Get number of days in the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayData = data.find(item => item.day === day); // Find data for the day
                    // Ensure the value is never negative, default to 0 if no data
                    chartData.push([day.toString(), Math.max(0, dayData ? parseInt(dayData.appointments) : 0)]);
                }
            } else {
                // Show monthly data for the entire year
                for (let month = 1; month <= 12; month++) {
                    const monthData = data.find(item => item.month === month); // Find data for the month
                    // Ensure the value is never negative, default to 0 if no data
                    chartData.push([monthNames[month - 1], Math.max(0, monthData ? parseInt(monthData.appointments) : 0)]);
                }
            }

            // Convert chart data to a DataTable
            const dataTable = google.visualization.arrayToDataTable(chartData);
            const options = {
                // Set the chart title
                title: `Appointments for ${selectedMonth && selectedMonth !== 'all' ? monthNames[selectedMonth - 1] : year}`,
                // Set the curve type (function means a smooth curve)
                curveType: 'function',
                // Set the legend position
                legend: { position: 'bottom' },
                // Set the horizontal axis title
                hAxis: { 
                    title: selectedMonth && selectedMonth !== 'all' ? 'Day of Month' : 'Month',
                    textPosition: 'out'
                },
                vAxis: { 
                    // Set the vertical axis title
                    title: 'Number of Appointments', 
                    minValue: 0,
                    viewWindow: { min: 0 },
                    format: '0',
                    gridlines: {
                        count: -1
                    },
                    baselineColor: '#ccc',
                    baseline: 0
                },  
                // Set the chart area size
                chartArea: { width: '80%', height: '70%' },
                // Set the chart colors
                colors: ['#1a73e8'],
                // Set the line width
                lineWidth: 3,
                // Set the point size
                pointSize: 5
            };

            // Create the chart
            const chart = new google.visualization.LineChart(document.getElementById('appointments_chart'));
            // Draw the chart
            chart.draw(dataTable, options);
        }

        // Function to update the appointments chart
        function updateAppointmentsChart() {
            const yearSelect = document.getElementById('year-select');
            const monthSelect = document.getElementById('month-select');
            const selectedYear = yearSelect.value; // Get selected year
            const selectedMonth = monthSelect.value; // Get selected month

            fetchAppointmentsData(selectedYear, selectedMonth);// Fetch data for selected year and month
        }

        // Function to fetch tuition centers data
        function fetchTuitionCentersData() {
            fetch('fetch_chart.php?type=tuition_centers') // Fetch tuition data
                .then(response => response.json())
                .then(data => {
                    console.log('Tuition centers data:', data);
                    drawTuitionCentersChart(data);  // Draw the tuition chart
                })
                .catch(error => console.error('Error fetching tuition centers data:', error));
        }

        // Function to draw the tuition centers chart
        function drawTuitionCentersChart(data) {
            // Chart for top rated tuition centers
            var chartData = [['Tuition Center', 'Rating']];
            
            // Loop through the data and add it to the chart data
            data.forEach(item => {
                chartData.push([
                    item.center,
                    parseFloat(item.avg_rating) // Add name and average rating
                ]);
            });

            // Convert chart data to a DataTable
            var dataTable = google.visualization.arrayToDataTable(chartData);
            var options = {
                // Set the chart title
                title: 'Top Rated Tuition Centers',
                // Set the bars to be horizontal (horizontal bar chart)
                bars: 'horizontal',
                // Set the chart height
                height: 300,
                // Set the chart colors
                colors: ['#1a73e8'],
                // Set the horizontal axis title
                hAxis: {
                    title: 'Rating (0-5)',
                    minValue: 0,
                    maxValue: 5,
                    format: '#.0'
                },
                // Set the vertical axis title
                vAxis: {
                    title: 'Tuition Center'
                },
                // Set the legend position
                legend: { position: 'none' }
            };
            // Create the chart
            var chart = new google.visualization.BarChart(document.getElementById('tuition_centers_chart'));
            // Draw the chart
            chart.draw(dataTable, options);
        }

        // Function to fetch user accounts data
        function fetchUserAccountsData() {
            fetch('fetch_chart.php?type=user_accounts') // Fetch user accounts data
                .then(response => response.json())
                .then(data => {
                    console.log('User accounts data:', data);
                    drawUserAccountsChart(data); // Draw the user accounts chart
                })
                .catch(error => console.error('Error fetching user accounts data:', error));
        }

        // Function to draw the user accounts chart
        function drawUserAccountsChart(data) {
            // Chart for monthly user registrations
            var chartData = [['Month', 'New Users']]; // Initialize chart data
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                               "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            data.forEach(item => {
                chartData.push([
                    monthNames[item.month - 1], // Add month name
                    item.registrations // Add number of registration
                ]);
            });

            // Convert chart data to a DataTable
            var dataTable = google.visualization.arrayToDataTable(chartData);
            var options = {
                // Set the chart title
                title: 'Monthly User Registrations',
                // Set the chart height
                height: 300,
                // Set the legend position
                legend: { position: 'none' },
                // Set the bars to be vertical
                bars: 'vertical',
                // Set the bar width
                bar: { groupWidth: '70%' },
                // Set the chart colors
                colors: ['#1a73e8'],
                vAxis: {
                    title: 'Number of Registrations',
                    minValue: 0,
                    format: '0'
                }
            };

            var chart = new google.charts.Bar(document.getElementById('user_accounts_chart'));
            chart.draw(dataTable, google.charts.Bar.convertOptions(options)); // Draw the chart
        }

        // Function to get the month name
        function getMonthName(month) {
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            return months[month - 1]; // Return month name (Arrays-1)
        }

        // Function to initialize year and month selectors
        function initializeSelectors() {
            const yearSelect = document.getElementById('year-select');
            const monthSelect = document.getElementById('month-select');
            const currentYear = new Date().getFullYear();

            // Populate year selector
            for (let year = currentYear; year <= currentYear + 1; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year; // Set option text
                yearSelect.appendChild(option); // Add option to year select
            }

            // Populate month selector
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            monthNames.forEach((month, index) => {
                const option = document.createElement('option');
                option.value = index + 1; // Set month value
                option.textContent = month; // Set option text
                monthSelect.appendChild(option); // Add option to month select
            });

            yearSelect.addEventListener('change', updateAppointmentsChart); // Update chart on year change
            monthSelect.addEventListener('change', updateAppointmentsChart); // Update chart on month change
        }

        // Call this function when the page loads
        document.addEventListener('DOMContentLoaded', initializeSelectors); // Initialize selectors on page load
    </script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br>
    <div class="container-fluid mt-4 admin-dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
        <p>This is your admin dashboard. You can use the navigation bar to manage various aspects of the Tuition Finder platform.
            Have a nice day!
        </p>

        <!-- Google Charts Sections -->
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <!-- Select Year -->
                    <label for="year-select" class="form-label">Select Year:</label>
                    <select id="year-select" class="form-select">
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                <div class="mb-3">
                    <!-- Select Month -->
                    <label for="month-select" class="form-label">Select Month:</label>
                    <select id="month-select" class="form-select">
                        <option value="all">All Months</option>
                        <!-- Other options will be populated by JavaScript -->
                    </select>
                </div>
                <div id="appointments_chart" style="height: 400px;"></div> <!-- Appointments chart -->
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-md-6">
                <div id="tuition_centers_chart" style="height: 300px;"></div> <!-- Top Rated Tuition chart -->
            </div>
            <div class="col-md-6">
                <div id="user_accounts_chart" style="height: 300px;"></div> <!-- User accounts chart -->
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>
</body>
</html>
