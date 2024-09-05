<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include('db_connect.php');

// Fetch the base location
$baseQuery = "SELECT * FROM base WHERE id = 1";
$baseResult = $conn->query($baseQuery);
$base = $baseResult->fetch_assoc();

// Get success message from session
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Remove the message after displaying it once
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="style/styles.css"> <!-- External CSS -->
</head>
<body>
<div class="container">
    <h2>Admin Map</h2>

    <!-- Display success message -->
    <?php if (!empty($message)): ?>
        <div id="messageBox" class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Map container -->
    <div id="map" style="height: 500px; margin-top: 20px;"></div>

    <!-- Confirmation section -->
    <div id="confirmation" style="display: none; margin-top: 20px;">
        <button id="saveLocationBtn" class="button">Save Location</button>
        <p>Confirm the new location before saving.</p>
    </div>

    <!-- Back to dashboard button -->
    <a href="dashboards/admin_dashboard.php" class="button">Back to Admin Dashboard</a>

    <!-- Hidden form to store latitude and longitude -->
    <form id="locationForm" method="POST" action="actions/update_base_location.php" style="display: none;">
        <input type="hidden" id="latitude" name="latitude" value="<?php echo $base['latitude']; ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?php echo $base['longitude']; ?>">
    </form>

</div>

<!-- External JavaScript -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="scripts/admin_map_change.js"></script> <!-- Map-related JS -->
<script src="scripts/admin_map_message.js"></script> <!-- JS for message display -->
</body>
</html>

