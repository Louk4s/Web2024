<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$rescuer_id = $_SESSION['user_id'];

// Check if the task is a request or offer
$task_details_query = "SELECT task_type, request_id, offer_id FROM tasks WHERE task_id = ?";
$task_stmt = $conn->prepare($task_details_query);
$task_stmt->bind_param('i', $task_id);
$task_stmt->execute();
$task_details = $task_stmt->get_result()->fetch_assoc();

// Update task to in progress and store the time of collection
$sql = "UPDATE tasks SET rescuer_id = ?, status = 'in_progress', collected_at = NOW() WHERE task_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $rescuer_id, $task_id);
$stmt->execute();

// Update the corresponding request/offer status
if ($task_details['task_type'] == 'request') {
    $update_request_sql = "UPDATE requests SET status = 'in_progress' WHERE id = ?";
    $update_request_stmt = $conn->prepare($update_request_sql);
    $update_request_stmt->bind_param('i', $task_details['request_id']);
    $update_request_stmt->execute();
} elseif ($task_details['task_type'] == 'offer') {
    $update_offer_sql = "UPDATE offers SET status = 'in_progress' WHERE id = ?";
    $update_offer_stmt = $conn->prepare($update_offer_sql);
    $update_offer_stmt->bind_param('i', $task_details['offer_id']);
    $update_offer_stmt->execute();
}

$_SESSION['success_message'] = "Task accepted successfully!";
header("Location: view_assigned_tasks.php");
exit();
?>
