<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$offer_id = isset($_GET['offer_id']) ? intval($_GET['offer_id']) : 0;
$user_id = $_SESSION['user_id'];

// First, delete the offer from the offers table
$sql_delete_offer = "DELETE FROM offers WHERE id = ? AND user_id = ?";
$stmt_offer = $conn->prepare($sql_delete_offer);
$stmt_offer->bind_param('ii', $offer_id, $user_id);
$stmt_offer->execute();

if ($stmt_offer->affected_rows > 0) {
    // If the offer was successfully deleted, now delete the corresponding task
    $sql_delete_task = "DELETE FROM tasks WHERE offer_id = ?";
    $stmt_task = $conn->prepare($sql_delete_task);
    $stmt_task->bind_param('i', $offer_id);
    $stmt_task->execute();

    if ($stmt_task->affected_rows > 0) {
        $_SESSION['success_message'] = "Offer and corresponding task deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Offer deleted, but the corresponding task could not be found.";
    }
} else {
    $_SESSION['error_message'] = "Offer deletion failed or the offer does not belong to you.";
}

// Redirect back to the offers management page or another relevant page
header("Location: view_my_offers.php");
exit();
?>
