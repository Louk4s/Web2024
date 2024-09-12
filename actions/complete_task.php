<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

if (isset($_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);
    $rescuer_id = $_SESSION['user_id']; // Rescuer's ID

    // Update the task status to 'completed'
    $sql_update_task = "UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE task_id = ? AND rescuer_id = ?";
    $stmt_task = $conn->prepare($sql_update_task);
    $stmt_task->bind_param('ii', $task_id, $rescuer_id);

    if ($stmt_task->execute()) {
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
            $sql_update_offer = "UPDATE offers SET status = 'completed' WHERE id = ?";
            $stmt_offer = $conn->prepare($sql_update_offer);
            $stmt_offer->bind_param('i', $offer_id);
            $stmt_offer->execute();
        } elseif ($task['task_type'] == 'request') {
            // Update the status in the requests table
            $request_id = $task['request_id'];
            $sql_update_request = "UPDATE requests SET status = 'completed' WHERE id = ?";
            $stmt_request = $conn->prepare($sql_update_request);
            $stmt_request->bind_param('i', $request_id);
            $stmt_request->execute();
        }

        // Redirect back to the completed tasks page
        header("Location: view_completed_tasks.php");
        exit();
    } else {
        echo "Error updating task: " . $stmt_task->error;
    }

    $stmt_task->close();
}

$conn->close();
?>
