<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$rescuer_id = $_SESSION['user_id'];

// Check if the task belongs to the rescuer and is currently in progress
$check_sql = "SELECT task_type, offer_id, request_id FROM tasks WHERE task_id = ? AND rescuer_id = ? AND status = 'in_progress'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('ii', $task_id, $rescuer_id);
$check_stmt->execute();
$task_result = $check_stmt->get_result();

if ($task_result->num_rows > 0) {
    $task_details = $task_result->fetch_assoc();

    // Task belongs to the rescuer and is in progress, proceed with cancellation
    $update_task_sql = "UPDATE tasks SET rescuer_id = NULL, status = 'pending' WHERE task_id = ?";
    $update_task_stmt = $conn->prepare($update_task_sql);
    $update_task_stmt->bind_param('i', $task_id);
    $update_task_stmt->execute();

    if ($update_task_stmt->affected_rows > 0) {
        // If the task is of type 'offer', update the corresponding offer's status to 'pending'
        if ($task_details['task_type'] == 'offer') {
            $offer_id = $task_details['offer_id'];
            $update_offer_sql = "UPDATE offers SET status = 'pending' WHERE id = ?";
            $update_offer_stmt = $conn->prepare($update_offer_sql);
            $update_offer_stmt->bind_param('i', $offer_id);
            $update_offer_stmt->execute();
        }

        // If the task is of type 'request', update the corresponding request's status to 'pending'
        if ($task_details['task_type'] == 'request') {
            $request_id = $task_details['request_id'];
            $update_request_sql = "UPDATE requests SET status = 'pending' WHERE id = ?";
            $update_request_stmt = $conn->prepare($update_request_sql);
            $update_request_stmt->bind_param('i', $request_id);
            $update_request_stmt->execute();
        }

        $_SESSION['success_message'] = "Task and related data successfully canceled and returned to pending.";
    } else {
        $_SESSION['error_message'] = "Unable to cancel the task. Please try again.";
    }
} else {
    // Task does not belong to the rescuer or is not in progress
    $_SESSION['error_message'] = "Task cannot be canceled. It may not belong to you or is not in progress.";
}

header("Location: view_assigned_tasks.php");
exit();
?>
