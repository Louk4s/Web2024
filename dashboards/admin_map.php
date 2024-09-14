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
</head>
<body>
    <div class="container">
        <h2>Admin Map</h2>
        <label><input type="checkbox" id="showRescuers" checked> Show Rescuers</label>
        <label><input type="checkbox" id="showOffers" checked> Show Offers</label>
        <label><input type="checkbox" id="showRequests" checked> Show Requests</label>

        <div id="mapContainer" style="margin-top: 20px;">
            <div id="map" style="height: 500px;"></div>
        </div>

        <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- Include Leaflet MarkerCluster JS -->
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script src="../scripts/admin_map.js"></script> <!-- Link JS file -->
</body>
</html>



