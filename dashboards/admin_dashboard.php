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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    
    <!-- Admin menu -->
    <ul>
        <li><a href="../actions/manage_inventory.php">Manage Inventory</a></li>
        <li><a href="../actions/manage_rescuers.php">Manage Rescuers</a></li>
        <li><a href="../actions/upload_json.php">Upload JSON Data</a></li>
        <li><a href="../actions/manage_citizens.php">View Citizens</a></li>
        <li><a href="javascript:void(0)" id="viewMapBtn">View Map</a></li>
        <li><a href="../admin_map.php">Set Base Location</a></li>
        <li><a href="../actions/create_announcement.php">Create Announcements</a></li>
    </ul>
    
    <a href="../logout.php">Logout</a>

    <!-- Map container (Initially hidden) -->
    <div id="mapContainer" style="display: none; margin-top: 20px;">
        <div id="map" style="height: 500px;"></div>
    </div>
</div>

<!-- External JavaScript -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="../scripts/admin_map.js"></script> <!-- Link  JS file -->

</body>
</html>

