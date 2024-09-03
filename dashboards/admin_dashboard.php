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
</head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="../actions/manage_inventory.php">Manage Inventory</a></li>
        <li><a href="../actions/manage_rescuers.php">Manage Rescuers</a></li>
        <li><a href="../actions/upload_json.php">Upload JSON Data</a></li>
        <li><a href="../actions/manage_citizens.php">View Citizens</a></li>
        <li><a href="../admin_map.php">Set Base Location</a></li>
    </ul>
    <a href="../logout.php">Logout</a>
</div>
</body>
</html>
