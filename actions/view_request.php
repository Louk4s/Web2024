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

// Fetch requests associated with the user along with the completed_at from the tasks table
$requests_query = "
    SELECT r.id, r.item_id, r.quantity, r.status, r.created_at, t.completed_at 
    FROM requests r 
    LEFT JOIN tasks t ON r.id = t.request_id 
    WHERE r.user_id = $user_id 
    ORDER BY r.created_at DESC";
$requests_result = $conn->query($requests_query);

// Parse the requests and fetch the item names and quantities
$requests = [];
if ($requests_result && $requests_result->num_rows > 0) {
    while ($row = $requests_result->fetch_assoc()) {
        // Fetch the item name for each request
        $item_query = "SELECT name FROM items WHERE id = " . intval($row['item_id']);
        $item_result = $conn->query($item_query);
        $item_name = $item_result->num_rows > 0 ? $item_result->fetch_assoc()['name'] : 'Unknown Item';

        $requests[] = [
            'id' => $row['id'],
            'item' => $item_name,
            'quantity' => $row['quantity'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'completed_at' => $row['completed_at'] // Add the completed_at date from the tasks table
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests</title>
    <link rel="stylesheet" href="../style/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery CDN -->
</head>
<body>
<div class="container">
    <h2>Your Requests</h2>

    <!-- Display success or error message here under the h2 header -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (count($requests) > 0): ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Date Created</th>
                <th>Completed At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($requests as $request): ?>
                <tr id="request-row-<?php echo $request['id']; ?>">
                    <td><?php echo htmlspecialchars($request['item']); ?></td>
                    <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                    <td><?php echo $request['status'] === 'completed' ? htmlspecialchars($request['completed_at']) : 'N/A'; ?></td>
                    <td>
                        <?php if ($request['status'] == 'pending'): ?>
                            <button class="cancel-btn" data-id="<?php echo $request['id']; ?>">Cancel Request</button>
                        <?php elseif ($request['status'] == 'completed'): ?>
                            <a href="delete_request.php?request_id=<?php echo $request['id']; ?>" class="button">Delete Request</a>
                        <?php elseif ($request['status'] == 'in_progress'): ?>
                            <span class="disabled-action">Cannot cancel or delete when in progress</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No requests found.</p>
    <?php endif; ?>

    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Citizen Dashboard</a>
</div>

<!-- Link to the external JS file -->
<script src="../scripts/cancel_button.js"></script> <!-- Adjust the path based on your file structure -->

</body>
</html>

