<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$rescuer_id = $_SESSION['user_id'];

// Ensure the task belongs to this rescuer and is in progress
$sql = "UPDATE tasks SET rescuer_id = NULL, status = 'pending' WHERE task_id = ? AND rescuer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $task_id, $rescuer_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success_message'] = "Task canceled successfully!";
} else {
    $_SESSION['error_message'] = "Unable to cancel task. It may not belong to you.";
}

header("Location: view_assigned_tasks.php");
exit();
?>
