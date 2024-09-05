<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Fetch base location
$baseQuery = "SELECT * FROM base WHERE id = 1";
$baseResult = $conn->query($baseQuery);
$base = $baseResult->fetch_assoc();

// Fetch rescuers' locations
$rescuerQuery = "SELECT fullname, latitude, longitude FROM rescuers";
$rescuerResult = $conn->query($rescuerQuery);
$rescuers = array();

while ($row = $rescuerResult->fetch_assoc()) {
    $rescuers[] = $row;
}

// Return the data in JSON format
echo json_encode([
    'base' => $base,
    'rescuers' => $rescuers
]);

$conn->close();
?>
