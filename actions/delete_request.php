<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$user_id = $_SESSION['user_id'];

// Delete the request only if the status is 'completed'
$sql_delete_request = "DELETE FROM requests WHERE id = ? AND user_id = ? AND status = 'completed'";
$stmt_request = $conn->prepare($sql_delete_request);
$stmt_request->bind_param('ii', $request_id, $user_id);
$stmt_request->execute();

 // Additional step: Delete tasks where both request_id and offer_id are NULL
 $delete_null_tasks_sql = "DELETE FROM tasks WHERE offer_id IS NULL AND request_id IS NULL";
 $conn->query($delete_null_tasks_sql); // Delete tasks where both offer_id and request_id are NULL
 $_SESSION['success_message'] = 'Request and associated task successfully deleted.';
 
if ($stmt_request->affected_rows > 0) {
    // If the request was successfully deleted, now delete the corresponding task
    $sql_delete_task = "DELETE FROM tasks WHERE request_id = ?";
    $stmt_task = $conn->prepare($sql_delete_task);
    $stmt_task->bind_param('i', $request_id);
    $stmt_task->execute();

    if ($stmt_task->affected_rows > 0) {
        $_SESSION['success_message'] = "Request and corresponding task deleted successfully.";
    } 
} else {
    $_SESSION['error_message'] = "Request deletion failed or the request does not belong to you.";
}

// Redirect back to the requests management page
header("Location: view_request.php");
exit();
?>
