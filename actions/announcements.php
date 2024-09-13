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
    <title>Announcements</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Announcements</h2>
    
    <div class="button-container">
        <a href="create_announcement.php" class="add-button" style="float: left;">Create Announcement</a>
        <a href="view_announcements_admin.php" class="add-button" style="float: right;">View Announcements</a>
    </div>
    
    <!-- Clear floats after buttons -->
    <div style="clear: both;"></div>
     <!-- Back to Admin Dashboard Button -->
     <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
</body>
</html>
