<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$sql = "SELECT * FROM base WHERE id = 1"; // Assuming there's only one base record
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $base = $result->fetch_assoc();
    $latitude = $base['latitude'];
    $longitude = $base['longitude'];
} else {
    echo "No base coordinates found.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
<div class="container">
    <h2>Admin Map</h2>
    <div id="map"></div>
    <form id="locationForm" method="post" action="actions/update_base_location.php">
        <input type="hidden" id="latitude" name="latitude" value="<?php echo $latitude; ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?php echo $longitude; ?>">
        <button type="submit">Save Location</button>
    </form>
    <br>
    <a class="back-button" href="dashboards/admin_dashboard.php">Back to Admin Dashboard</a>
</div>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="scripts/admin_map.js"></script>
</body>
</html>
