<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Citizen)</h2>
    <ul>
        <li><a href="../actions/add_request.php">Request Assistance</a></li>
        <li><a href="#">Offer Help</a></li>
        <!-- Add other citizen-specific functionalities here -->
    </ul>
    <a href="../logout.php">Logout</a>
</div>
</body>
</html>
