<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}
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
        <li><a href="../actions/view_tasks.php">View Assigned Tasks</a></li>
        <li><a href="../actions/view_map.php">View Map</a></li>
        <li><a href="../actions/update_task_status.php">Update Task Status</a></li>
        <li><a href="../actions/view_announcements.php">View Announcements</a></li>
        <li><a href="../actions/view_offers.php">View Offers</a></li>
    </ul>

    <a href="../logout.php">Logout</a>
</div>
</body>
</html>

