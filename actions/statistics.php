<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include('../db_connect.php');

// Retrieve data based on time period from POST request
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';


if (!empty($startDate) && !empty($endDate)) {
    // Adjust the start and end dates to cover the entire day
$startDate .= ' 00:00:00';  // Set start date to 00:00:00
$endDate .= ' 23:59:59';    // Set end date to 23:59:59
    // Prepare SQL queries
    $pendingRequestsQuery = "SELECT COUNT(*) AS total_pending_requests FROM requests WHERE status = 'pending' AND created_at BETWEEN '$startDate' AND '$endDate'";
    $pendingOffersQuery = "SELECT COUNT(*) AS total_pending_offers FROM offers WHERE status = 'pending' AND created_at BETWEEN '$startDate' AND '$endDate'";
    $completedRequestsQuery = "SELECT COUNT(*) AS total_completed_requests FROM requests WHERE status = 'completed' AND created_at BETWEEN '$startDate' AND '$endDate'";
    $completedOffersQuery = "SELECT COUNT(*) AS total_completed_offers FROM offers WHERE status = 'completed' AND created_at BETWEEN '$startDate' AND '$endDate'";

    // Execute queries
    $pendingRequestsResult = mysqli_query($conn, $pendingRequestsQuery);
    $pendingOffersResult = mysqli_query($conn, $pendingOffersQuery);
    $completedRequestsResult = mysqli_query($conn, $completedRequestsQuery);
    $completedOffersResult = mysqli_query($conn, $completedOffersQuery);

    // Fetch data
    $pendingRequests = mysqli_fetch_assoc($pendingRequestsResult)['total_pending_requests'];
    $pendingOffers = mysqli_fetch_assoc($pendingOffersResult)['total_pending_offers'];
    $completedRequests = mysqli_fetch_assoc($completedRequestsResult)['total_completed_requests'];
    $completedOffers = mysqli_fetch_assoc($completedOffersResult)['total_completed_offers'];

    // Return data as JSON
    echo json_encode([
        'pending_requests' => $pendingRequests,
        'pending_offers' => $pendingOffers,
        'completed_requests' => $completedRequests,
        'completed_offers' => $completedOffers
    ]);

    exit(); // Stop script to prevent extra output
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../scripts/statistics.js"></script> <!-- Link to the JS file -->
</head>
<body>
    <div class="container">
        <h2>Statistics</h2>

        <!-- Time period selection form -->
        <form id="timePeriodForm">
            <div class="form-group">
                <label for="startDate">Start Date:</label>
                <input type="date" id="startDate" name="startDate" required placeholder="dd/mm/yyyy">
            </div>

            <div class="form-group">
                <label for="endDate">End Date:</label>
                <input type="date" id="endDate" name="endDate" required required placeholder="dd/mm/yyyy">
            </div>

            <!-- Button container -->
            <div class="form-group button-container">
                <button type="submit">Get Statistics</button>
            </div>
        </form>

        <!-- Canvas for Chart.js graph -->
        <canvas id="myChart" width="400" height="200"></canvas>
        <!-- Back to Citizen Dashboard Button -->
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
    </div>
</body>
</html>

