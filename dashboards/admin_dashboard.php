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
<div class="container centered">
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</h2>
    
    <!-- Admin menu -->
    <ul class="admin-menu">
        <li><a href="../actions/manage_inventory.php">Manage Inventory</a></li>
        <li><a href="../actions/manage_rescuers.php">Manage Rescuers</a></li>
        <li><a href="../actions/upload_json.php">Upload JSON Data</a></li>
        <li><a href="../actions/manage_citizens.php">View Citizens</a></li>
        <li><a href="../dashboards/admin_map.php">View Map</a></li> 
        <li><a href="../admin_map.php">Set Base Location</a></li>
        <li><a href="../actions/create_announcement.php">Create Announcements</a></li>
        <li><a href="../actions/view_announcements_admin.php">View Announcements</a></li>
        <li><a href="../actions/statistics.php">Statistics</a></li>
    </ul>
    
    <a href="../logout.php" class="back-button">Logout</a>
</div>
</body>
</html>
