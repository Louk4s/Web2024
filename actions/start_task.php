<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    
    // Update task status to 'in_progress'
    $sql = "UPDATE tasks SET status = 'in_progress' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: view_tasks.php");
