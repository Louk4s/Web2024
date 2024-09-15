<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Update session variable for isInsideCircleCitizen
    if (isset($input['isInsideCircleCitizen']) && isset($input['taskId'])) {
        $_SESSION['isInsideCircleCitizen'][$input['taskId']] = $input['isInsideCircleCitizen'];

        // Send a success response
        echo json_encode(['success' => true]);
    } else {
        // Send an error response
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    exit();
}
?>
