<?php 
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch Base Location (ID = 1)
$base_query = "SELECT * FROM base WHERE id = 1";
$base_result = $conn->query($base_query);

if ($base_result && $base_result->num_rows > 0) {
    $base = $base_result->fetch_assoc();
    $latitude = $base['latitude'];
    $longitude = $base['longitude'];
} else {
    $latitude = 'N/A';
    $longitude = 'N/A';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescuer Dashboard</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Rescuer)</h2>

    <ul>
        <li><a href="../actions/manage_inventory_rescuer.php">Manage Inventory</a></li>
        <li><a href="../actions/view_trucks_inventory.php">View Truck's Inventory</a></li>
        <li><a href="../actions/view_assigned_tasks.php">View Assigned Tasks</a></li>
        <li><a href="../actions/view_completed_tasks.php">View Completed Tasks</a></li>
      
    </ul>

    <!-- Base Location Information -->
    <!-- <p>Base Location: Latitude: <?php echo $latitude; ?>, Longitude: <?php echo $longitude; ?></p>  -->

    <!-- Logout Button -->
    <a href="../logout.php" class="back-button">Logout</a>
</div>
</body>
</html>

