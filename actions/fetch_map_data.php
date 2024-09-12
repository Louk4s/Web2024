<?php
session_start();

// Check if user is logged in and authorized (either admin or rescuer can access this)
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'rescuer')) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Fetch base location
$baseQuery = "SELECT latitude, longitude FROM base WHERE id = 1";
$baseResult = $conn->query($baseQuery);
$base = $baseResult->fetch_assoc();

// Fetch rescuers' locations from users table where role = 'rescuer'
$rescuerQuery = "SELECT fullname, latitude, longitude FROM users WHERE role = 'rescuer'";
$rescuerResult = $conn->query($rescuerQuery);
$rescuers = array();

while ($row = $rescuerResult->fetch_assoc()) {
    $rescuers[] = $row;
}

// Fetch tasks from the tasks table
$tasksQuery = "
    SELECT task_id, task_type, latitude, longitude, status, rescuer_id 
    FROM tasks 
    WHERE status != 'completed'
";
$tasksResult = $conn->query($tasksQuery);
$tasks = array();

while ($row = $tasksResult->fetch_assoc()) {
    $tasks[] = $row;
}

// Return the data in JSON format
echo json_encode([
    'base' => $base,
    'rescuers' => $rescuers,
    'tasks' => $tasks
]);

$conn->close();
?>
