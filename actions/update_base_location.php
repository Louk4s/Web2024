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
        $_SESSION['message'] = "Base location updated successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to admin_map.php with message
    header("Location: ../admin_map.php");
    exit();
}
?>
