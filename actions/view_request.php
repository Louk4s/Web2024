<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch the user ID from the session
$username = $_SESSION['username'];
$user_query = "SELECT id FROM users WHERE username = '$username'";
$user_result = $conn->query($user_query);

if ($user_result && $user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $user_id = $user_row['id'];
} else {
    die("User not found");
}

// Fetch requests associated with the user
$requests_query = "SELECT r.*, i.name AS item_name 
                   FROM requests r 
                   JOIN items i ON r.item_id = i.id 
                   WHERE r.user_id = $user_id 
                   ORDER BY r.created_at DESC";
$requests_result = $conn->query($requests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Your Requests</h2>

    <table>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php if ($requests_result && $requests_result->num_rows > 0): ?>
            <?php while ($row = $requests_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No requests found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$conn->close();
?>
