<?php
session_start();

// Check if the user is authenticated as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Unauthorized
    echo json_encode(['message' => 'Unauthorized access.']);
    exit();
}

if (isset($_POST['announcement_id'])) {
    include '../db_connect.php';

    $announcement_id = intval($_POST['announcement_id']); // Sanitize the input

    if (!$announcement_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid announcement ID.']);
        exit();
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param('i', $announcement_id);

    // Check if the query executed successfully
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete announcement.']);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Invalid request.']);
}
?>

