<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Validate form inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = ''; // This will be fetched based on the session username
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;

    // Fetch the user ID, latitude, and longitude based on the username in the session
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
    $status = 'Pending'; // Default status for new requests

    if ($item_id && $people_count > 0) {
        $insert_query = "INSERT INTO requests (user_id, item_id, quantity, status, latitude, longitude) 
                         VALUES ('$user_id', '$item_id', '$people_count', '$status', '$latitude', '$longitude')";

        if ($conn->query($insert_query) === TRUE) {
            $_SESSION['success_message'] = "Request submitted successfully!";
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
