<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// Fetch data from the database
$base_query = "SELECT * FROM base";
$rescuers_query = "SELECT * FROM users WHERE role = 'rescuer'";
$citizens_query = "SELECT * FROM users WHERE role = 'citizen'";
$requests_query = "SELECT * FROM requests"; // Assuming a table for citizen requests/offers

$base_result = $conn->query($base_query);
if ($base_result->num_rows == 0) {
    die("No base coordinates found.");
}
$base = $base_result->fetch_assoc();

$rescuers_result = $conn->query($rescuers_query);
$citizens_result = $conn->query($citizens_query);
$requests_result = $conn->query($requests_query);

$rescuers = [];
$citizens = [];
$requests = [];

while ($row = $rescuers_result->fetch_assoc()) {
    $rescuers[] = $row;
}

while ($row = $citizens_result->fetch_assoc()) {
    $citizens[] = $row;
}

while ($row = $requests_result->fetch_assoc()) {
    $requests[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha384-xodZBNTC5n5lQifXZxMKCK2FT/ezZZiGIWv5lZ7EAK4iqDQEdFmgLbTL71eQ1J+g" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha384-a1n9x6goZxa0xyD9Rfa5iGV/8z8kv2meuV+p8L1S9I5LfOgynLx8l+6Y/IdQU0Cd" crossorigin=""></script>
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Map</h2>
    <div id="map"></div>
    <a href="dashboards/<?php echo $_SESSION['role']; ?>_dashboard.php">Back to Dashboard</a>
</div>

<!-- Hidden inputs to pass data to JavaScript -->
<input type="hidden" id="base-lat" value="<?php echo $base['latitude']; ?>">
<input type="hidden" id="base-lng" value="<?php echo $base['longitude']; ?>">
<div id="rescuers-data" style="display:none;"><?php echo json_encode($rescuers); ?></div>
<div id="citizens-data" style="display:none;"><?php echo json_encode($citizens); ?></div>
<div id="requests-data" style="display:none;"><?php echo json_encode($requests); ?></div>

<script src="../scripts/map.js"></script>
</body>
</html>
