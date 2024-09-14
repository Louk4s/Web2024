<?php
session_start();
include '../db_connect.php';

// Ensure the user is logged in as a citizen
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$user_id = $_SESSION['user_id'];

// Cancel the request if it's in 'pending' status
$cancel_request_sql = "DELETE FROM requests WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt_request = $conn->prepare($cancel_request_sql);
$stmt_request->bind_param('ii', $request_id, $user_id);
$stmt_request->execute();

if ($stmt_request->affected_rows > 0) {
    // Also delete the corresponding task if it's associated with the canceled request
    $delete_task_sql = "DELETE FROM tasks WHERE request_id = ? AND offer_id IS NULL";
    $stmt_task = $conn->prepare($delete_task_sql);
    $stmt_task->bind_param('i', $request_id);
    $stmt_task->execute();

    // Additionally, delete any tasks where both offer_id and request_id are NULL
    $delete_null_task_sql = "DELETE FROM tasks WHERE offer_id IS NULL AND request_id IS NULL";
    $conn->query($delete_null_task_sql);

    echo json_encode(['success' => true, 'message' => 'Request and associated task successfully canceled.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel the request or the request does not belong to you.']);
}
?>
