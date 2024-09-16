<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Map</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" /> 
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <style>
        #filterContainer {
            display: none;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .filter-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .filter-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Map</h2>
        
        <!-- Toggle Button for Filters -->
        <button class="filter-button" id="toggleFilters">Show Filters</button>

        <!-- Filter Container (Hidden by default) -->
        <div id="filterContainer">
            <label><input type="checkbox" id="showRescuersWithActiveTasks"> Show Rescuers with Active Tasks</label><br>
            <label><input type="checkbox" id="showRescuersWithoutActiveTasks"> Show Rescuers without Active Tasks</label><br>
            <label><input type="checkbox" id="showOffers"> Show Offers</label><br>
            <label><input type="checkbox" id="showRequestsPending"> Show Pending Requests</label><br>
            <label><input type="checkbox" id="showRequestsInProgress"> Show In-Progress Requests</label><br>
            <label><input type="checkbox" id="showTaskLines"> Show Lines to Active Tasks</label>
        </div>

        <div id="mapContainer" style="margin-top: 20px;">
            <div id="map" style="height: 500px;"></div>
        </div>

        <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script src="../scripts/admin_map.js"></script>
</body>
</html>
