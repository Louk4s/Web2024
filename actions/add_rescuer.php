<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $sql = "INSERT INTO users (fullname, phone, username, password, role, latitude, longitude) VALUES (?, ?, ?, ?, 'rescuer', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdd", $fullname, $phone, $username, $password, $latitude, $longitude);

    if ($stmt->execute()) {
        $message = "Rescuer added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Rescuer</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body onload="getLocation()">
<div class="container">
    <h2>Add Rescuer</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="add_rescuer.php" method="post">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required><br>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required><br>
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <label for="latitude">Latitude:</label>
        <input type="number" step="any" id="latitude" name="latitude" required><br>
        
        <label for="longitude">Longitude:</label>
        <input type="number" step="any" id="longitude" name="longitude" required><br>

        <button type="submit">Add Rescuer</button>
    </form>
    <a href="manage_rescuers.php">Back to Manage Rescuers</a>
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
</div>

<!-- Link to the external JavaScript file -->
<script src="../scripts/location.js"></script>
</body>
</html>
