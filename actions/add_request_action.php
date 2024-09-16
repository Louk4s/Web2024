<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php'; // Adjusted path since it's in the same folder

// Validate form inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the input values
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;

    // Ensure the item_id is valid and people_count is valid
    if ($item_id == 0 && $people_count <= 0) {
        $_SESSION['error_message'] = "Error: You did not select an item, and the number of people was below 1. Try again!";
        session_write_close(); // Ensure session data is saved
        header("Location: request_assistance.php"); // Adjusted path since it's in the same folder
        exit();
    }

    if ($item_id == 0) {
        $_SESSION['error_message'] = "Error: You did not select an item. Try again!";
        session_write_close(); // Ensure session data is saved
        header("Location: request_assistance.php"); // Adjusted path
        exit();
    }

    if ($people_count <= 0) {
        $_SESSION['error_message'] = "Error: The number of people must be at least 1. Try again!";
        session_write_close(); // Ensure session data is saved
        header("Location: request_assistance.php"); // Adjusted path
        exit();
    }

    // Fetch the user location based on the session username
    $username = $_SESSION['username'];
    $user_query = "SELECT id, latitude, longitude FROM users WHERE username = '$username' AND role = 'citizen'";
    $user_result = $conn->query($user_query);

    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $user_id = intval($user_row['id']);
        $latitude = $user_row['latitude'];
        $longitude = $user_row['longitude'];
    } else {
        $_SESSION['error_message'] = "Error: User not found.";
        session_write_close(); // Ensure session data is saved
        header("Location: request_assistance.php"); // Adjusted path
        exit();
    }

    // Insert the new request into the database
    $status = 'pending'; // Default status for new requests
    $insert_query = "INSERT INTO requests (user_id, item_id, quantity, status, latitude, longitude) 
                     VALUES ('$user_id', '$item_id', '$people_count', '$status', '$latitude', '$longitude')";

    if ($conn->query($insert_query) === TRUE) {
        $request_id = $conn->insert_id;

        // Now insert a task for this request
        $task_type = 'request';
        $task_stmt = $conn->prepare("INSERT INTO tasks (task_type, request_id, status, created_at, latitude, longitude) VALUES (?, ?, 'pending', NOW(), ?, ?)");
        $task_stmt->bind_param('siss', $task_type, $request_id, $latitude, $longitude);
        $task_stmt->execute();

        // Store success message in the session
        $_SESSION['success_message'] = "Request submitted successfully!";
        session_write_close(); // Ensure session data is saved
        
        // Redirect back to the request assistance page to show the success message
        header("Location: request_assistance.php"); // Adjusted path
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        session_write_close(); // Ensure session data is saved
        header("Location: request_assistance.php"); // Adjusted path
        exit();
    }
}

$conn->close();
?>

