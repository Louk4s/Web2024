<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

// Check if the rescuer has more than 4 tasks
$task_count_sql = "SELECT COUNT(*) AS task_count FROM tasks WHERE rescuer_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($task_count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$task_count_result = $stmt->get_result();
$task_count_row = $task_count_result->fetch_assoc();
$task_count = intval($task_count_row['task_count']);

if ($task_count >= 4) {
    $_SESSION['error_message'] = "You cannot accept more than 4 tasks at a time.";
    header("Location: view_assigned_tasks.php");
    exit();
}

// Assign the task to the rescuer and update the status
$sql = "UPDATE tasks SET rescuer_id = ?, status = 'in_progress' WHERE task_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $task_id);

if ($stmt->execute()) {
    // Fetch the task type (offer/request) and the corresponding offer/request id
    $sql_fetch_task = "SELECT task_type, offer_id, request_id FROM tasks WHERE task_id = ?";
    $stmt_fetch_task = $conn->prepare($sql_fetch_task);
    $stmt_fetch_task->bind_param('i', $task_id);
    $stmt_fetch_task->execute();
    $result_task = $stmt_fetch_task->get_result();
    $task = $result_task->fetch_assoc();

    if ($task['task_type'] == 'offer') {
        // Update the status in the offers table
        $offer_id = $task['offer_id'];
        $sql_update_offer = "UPDATE offers SET status = 'in_progress' WHERE id = ?";
        $stmt_offer = $conn->prepare($sql_update_offer);
        $stmt_offer->bind_param('i', $offer_id);
        $stmt_offer->execute();
    } elseif ($task['task_type'] == 'request') {
        // Update the status in the requests table
        $request_id = $task['request_id'];
        $sql_update_request = "UPDATE requests SET status = 'in_progress' WHERE id = ?";
        $stmt_request = $conn->prepare($sql_update_request);
        $stmt_request->bind_param('i', $request_id);
        $stmt_request->execute();
    }

    $_SESSION['success_message'] = "Task accepted successfully!";
} else {
    $_SESSION['error_message'] = "Failed to accept task.";
}

$stmt->close();
$conn->close();

header("Location: view_assigned_tasks.php");
exit();
?>
