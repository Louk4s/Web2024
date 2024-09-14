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

// Fetch offers associated with the user
$offers_query = "SELECT o.id, o.item_ids, o.status, o.created_at FROM offers o WHERE o.user_id = $user_id ORDER BY o.created_at DESC";
$offers_result = $conn->query($offers_query);

// Parse item_ids and fetch the item names and quantities
$offers = [];
if ($offers_result && $offers_result->num_rows > 0) {
    while ($row = $offers_result->fetch_assoc()) {
        $item_details = [];
        $item_ids_quantities = explode(',', $row['item_ids']); // Split item_ids string into individual item:quantity pairs

        foreach ($item_ids_quantities as $item_quantity) {
            list($item_id, $quantity) = explode(':', $item_quantity); // Split each item:quantity pair
            $item_query = "SELECT name FROM items WHERE id = $item_id";
            $item_result = $conn->query($item_query);
            if ($item_result && $item_result->num_rows > 0) {
                $item_name = $item_result->fetch_assoc()['name'];
                $item_details[] = "$quantity x $item_name"; // Store the quantity and item name together
            }
        }

        $offers[] = [
            'id' => $row['id'],
            'items' => implode(', ', $item_details), // Join all item details into a single string
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
}

// Handle offer cancellation (for pending offers)
if (isset($_GET['cancel_offer_id'])) {
    $offer_id = intval($_GET['cancel_offer_id']);
    
    // First delete the offer only if the status is 'pending'
    $cancel_offer_sql = "DELETE FROM offers WHERE id = $offer_id AND user_id = $user_id AND status = 'pending'";
    if ($conn->query($cancel_offer_sql)) {
        
        // Also delete the corresponding tasks with the same offer_id from the tasks table
        $delete_tasks_sql = "DELETE FROM tasks WHERE offer_id = $offer_id";
        $conn->query($delete_tasks_sql); // Execute the deletion query for tasks linked to this offer

        // Additional step: Delete tasks where both request_id and offer_id are NULL
        $delete_null_tasks_sql = "DELETE FROM tasks WHERE offer_id IS NULL AND request_id IS NULL";
        $conn->query($delete_null_tasks_sql); // Delete tasks where both offer_id and request_id are NULL

        $_SESSION['success_message'] = 'Offer and associated tasks successfully canceled.';
    } else {
        $_SESSION['error_message'] = 'Unable to cancel the offer.';
    }

    header("Location: view_offers.php");
    exit();
}

// Handle deletion of completed offer
if (isset($_GET['delete_offer_id'])) {
    $offer_id = intval($_GET['delete_offer_id']);
    
    // Delete the offer only if the status is 'completed'
    $delete_offer_sql = "DELETE FROM offers WHERE id = $offer_id AND user_id = $user_id AND status = 'completed'";
    if ($conn->query($delete_offer_sql)) {
        // Also delete the corresponding tasks with the same offer_id from the tasks table for completed offers
        $delete_tasks_sql = "DELETE FROM tasks WHERE offer_id = $offer_id";
        $conn->query($delete_tasks_sql);

        // Clean up tasks where both offer_id and request_id are NULL
        $cleanup_tasks_sql = "DELETE FROM tasks WHERE offer_id IS NULL AND request_id IS NULL";
        $conn->query($cleanup_tasks_sql); // Execute cleanup query

        $_SESSION['success_message'] = 'Completed offer deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Unable to delete the offer.';
    }

    header("Location: view_offers.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Offers</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Your Offers</h2>

    <!-- Display success message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Display error message -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (count($offers) > 0): ?>
        <table>
            <tr>
                <th>Items</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($offers as $offer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($offer['items']); ?></td>
                    <td><?php echo htmlspecialchars($offer['status']); ?></td>
                    <td><?php echo htmlspecialchars($offer['created_at']); ?></td>
                    <td>
                        <?php if ($offer['status'] == 'pending'): ?>
                            <a href="view_offers.php?cancel_offer_id=<?php echo $offer['id']; ?>" class="button">Cancel Offer</a>
                        <?php elseif ($offer['status'] == 'completed'): ?>
                            <a href="view_offers.php?delete_offer_id=<?php echo $offer['id']; ?>" class="button">Delete Offer</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No offers found.</p>
    <?php endif; ?>

    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Citizen Dashboard</a>
</div>
</body>
</html>
