<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Validate form inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;

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
        die("Error: User not found.");
    }

    // Insert the new request into the database
    $status = 'pending'; // Default status for new requests

    if ($item_id && $people_count > 0) {
        $insert_query = "INSERT INTO requests (user_id, item_id, quantity, status, latitude, longitude) 
                         VALUES ('$user_id', '$item_id', '$people_count', '$status', '$latitude', '$longitude')";
        if ($conn->query($insert_query) === TRUE) {
            $request_id = $conn->insert_id;

            // Now insert a task for this request
            $task_type = 'request';
            $task_stmt = $conn->prepare("INSERT INTO tasks (task_type, request_id, status, created_at, latitude, longitude) VALUES (?, ?, 'pending', NOW(), ?, ?)");
            $task_stmt->bind_param('siss', $task_type, $request_id, $latitude, $longitude);
            $task_stmt->execute();

            $_SESSION['success_message'] = "Request submitted successfully and task added!";
            header("Location: ../dashboards/citizen_dashboard.php");
        } else {
            echo "Error: " . $insert_query . "<br>" . $conn->error;
        }
    } else {
        echo "Invalid item or people count.";
    }
}

$conn->close();
?>
