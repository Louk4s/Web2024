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

// Fetch rescuers' locations from users table where role = rescuer
$rescuerQuery = "SELECT fullname, latitude, longitude FROM users WHERE role = 'rescuer'";
$rescuerResult = $conn->query($rescuerQuery);
$rescuers = array();

while ($row = $rescuerResult->fetch_assoc()) {
    $rescuers[] = $row;
}

// Fetch tasks from the database
$tasksQuery = "SELECT * FROM tasks";
$tasksResult = $conn->query($tasksQuery);
$tasks = array();

while ($row = $tasksResult->fetch_assoc()) {
    $tasks[] = $row;
}

// Return base, rescuers, and tasks
echo json_encode([
    'base' => $base,
    'rescuers' => $rescuers,
    'tasks' => $tasks
]);

$conn->close();
?>



