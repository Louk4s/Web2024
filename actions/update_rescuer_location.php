<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include '../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['latitude']) && isset($data['longitude'])) {
    $latitude = floatval($data['latitude']);
    $longitude = floatval($data['longitude']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ddi", $latitude, $longitude, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}

$conn->close();
?>
