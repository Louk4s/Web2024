<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $sql = "UPDATE base SET latitude = ?, longitude = ? WHERE id = 1"; // Assuming there's only one base record
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dd", $latitude, $longitude);

    if ($stmt->execute()) {
        $message = "Base location updated successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../admin_map.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Base Location</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2><?php echo $message; ?></h2>
    <a class="back-button" href="../dashboards/admin_dashboard.php">Back to Admin Dashboard</a>
</div>
</body>
</html>
