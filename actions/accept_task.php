<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$rescuer_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

// Check the current number of in-progress tasks
$sql_task_count = "
    SELECT COUNT(*) AS in_progress_count
    FROM tasks
    WHERE status = 'in_progress' AND rescuer_id = ?
";
$stmt_task_count = $conn->prepare($sql_task_count);
$stmt_task_count->bind_param('i', $rescuer_id);
$stmt_task_count->execute();
$result_task_count = $stmt_task_count->get_result();
$in_progress_count = $result_task_count->fetch_assoc()['in_progress_count'];

$stmt_task_count->close();

// If rescuer has 4 or more tasks in-progress, do not allow accepting more tasks
if ($in_progress_count >= 4) {
    echo "<script>alert('You have reached the maximum in-progress tasks (4)!'); window.history.back();</script>";
    exit();
}

// Check if the task is a request or offer
$task_details_query = "SELECT task_type, request_id, offer_id FROM tasks WHERE task_id = ?";
$task_stmt = $conn->prepare($task_details_query);
$task_stmt->bind_param('i', $task_id);
$task_stmt->execute();
$task_details = $task_stmt->get_result()->fetch_assoc();

if (!$task_details) {
    die("Task not found.");
}

// For requests: Check inventory before accepting
if ($task_details['task_type'] == 'request') {
    $request_sql = "
        SELECT r.item_id, r.quantity AS requested_quantity, i.name AS item_name, IFNULL(inv.quantity, 0) AS available_quantity
        FROM requests r
        JOIN items i ON r.item_id = i.id
        LEFT JOIN inventory inv ON inv.item_id = r.item_id AND inv.rescuer_id = ?
        WHERE r.id = ?
    ";
    $stmt = $conn->prepare($request_sql);
    $stmt->bind_param('ii', $rescuer_id, $task_details['request_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $insufficient_items = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['available_quantity'] < $row['requested_quantity']) {
            $insufficient_items[] = [
                'name' => $row['item_name'],
                'requested' => $row['requested_quantity'],
                'available' => $row['available_quantity']
            ];
        }
    }

    if (!empty($insufficient_items)) {
        $error_message = "Insufficient items in inventory:\\n";
        foreach ($insufficient_items as $item) {
            $error_message .= "Item: {$item['name']}, Requested: {$item['requested']}, Available: {$item['available']}\\n";
        }
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit();
    }

    // Update the status of the request to 'in_progress'
    $update_request_sql = "UPDATE requests SET status = 'in_progress' WHERE id = ?";
    $update_request_stmt = $conn->prepare($update_request_sql);
    $update_request_stmt->bind_param('i', $task_details['request_id']);
    $update_request_stmt->execute();
}

// Update the task to in_progress and assign it to the rescuer
$update_task_sql = "UPDATE tasks SET rescuer_id = ?, status = 'in_progress', collected_at = NOW() WHERE task_id = ?";
$update_task_stmt = $conn->prepare($update_task_sql);
$update_task_stmt->bind_param('ii', $rescuer_id, $task_id);
$update_task_stmt->execute();

if ($update_task_stmt->affected_rows > 0) {
    // If the task is an offer, update the offer status to 'in_progress'
    if ($task_details['task_type'] == 'offer') {
        $offer_id = $task_details['offer_id'];
        
        // Check if the offer exists and its current status
        $offer_check_sql = "SELECT status FROM offers WHERE id = ?";
        $offer_check_stmt = $conn->prepare($offer_check_sql);
        $offer_check_stmt->bind_param('i', $offer_id);
        $offer_check_stmt->execute();
        $offer_result = $offer_check_stmt->get_result();
        $offer = $offer_result->fetch_assoc();

        if ($offer) {
            // Update the offer status if not already in 'completed' state
            if ($offer['status'] !== 'completed') {
                $update_offer_sql = "UPDATE offers SET status = 'in_progress' WHERE id = ?";
                $update_offer_stmt = $conn->prepare($update_offer_sql);
                $update_offer_stmt->bind_param('i', $offer_id);
                $update_offer_stmt->execute();
            }
        }
    }

    $_SESSION['success_message'] = "Task accepted successfully!";
} else {
    $_SESSION['error_message'] = "Failed to accept task.";
}

header("Location: view_assigned_tasks.php");
exit();

