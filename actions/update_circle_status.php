<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Update session variable
    if (isset($input['isInsideCircle'])) {
        $_SESSION['isInsideCircle'] = $input['isInsideCircle'];

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
