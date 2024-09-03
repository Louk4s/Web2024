<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

// Fetch base location, rescuers, citizens, and requests data from the database
include '../db_connect.php';

// Get base location (assuming you have it stored in the base table)
$base_query = $conn->query("SELECT latitude, longitude FROM base WHERE id = 1");
$base_location = $base_query->fetch_assoc();

// Fetch all rescuers
$rescuers_query = $conn->query("SELECT fullname, latitude, longitude FROM users WHERE role = 'rescuer'");
$rescuers = $rescuers_query->fetch_all(MYSQLI_ASSOC);

// Fetch all citizens
$citizens_query = $conn->query("SELECT fullname, latitude, longitude FROM users WHERE role = 'citizen'");
$citizens = $citizens_query->fetch_all(MYSQLI_ASSOC);

// Fetch all requests
$requests_query = $conn->query("SELECT description, latitude, longitude, type FROM requests");
$requests = $requests_query->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescuer Map</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <div class="container">
        <h2>Rescuer Map</h2>
        <div id="map" style="height: 500px;"></div>
        <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Rescuer Dashboard</a>
    </div>

    <!-- Hidden fields to store base location data -->
    <input type="hidden" id="base-lat" value="<?php echo $base_location['latitude']; ?>">
    <input type="hidden" id="base-lng" value="<?php echo $base_location['longitude']; ?>">

    <!-- Hidden fields to store JSON data -->
    <div id="rescuers-data" style="display: none;"><?php echo json_encode($rescuers); ?></div>
    <div id="citizens-data" style="display: none;"><?php echo json_encode($citizens); ?></div>
    <div id="requests-data" style="display: none;"><?php echo json_encode($requests); ?></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../scripts/map.js"></script>
</body>
</html>
