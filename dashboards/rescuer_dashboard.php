<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'rescuer') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

// Initialize the 'isInsideCircle' session variable if not set
if (!isset($_SESSION['isInsideCircle'])) {
    $_SESSION['isInsideCircle'] = false; // Default if not yet set
}

$isInsideCircle = $_SESSION['isInsideCircle'];
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
<div class="container centered">
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Rescuer)</h2>
    
    <!-- Rescuer menu -->
    <ul class="admin-menu">
        <?php if ($isInsideCircle): ?>
            <li><a href="../actions/manage_inventory_rescuer.php">Manage Inventory</a></li>
        <?php else: ?>
            <li><a href="#" onclick="alert('You are too far from the base to manage inventory.'); return false;">Manage Inventory</a></li>
        <?php endif; ?>
        <li><a href="../actions/view_trucks_inventory.php">View Truck's Inventory</a></li>
        <li><a href="../actions/view_assigned_tasks.php">View Assigned Tasks</a></li>
        <li><a href="../actions/view_completed_tasks.php">View Completed Tasks</a></li>
    </ul>
    
    <a href="../logout.php" class="back-button">Logout</a>
</div>
</body>
</html>

