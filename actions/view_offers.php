<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch offers made by the citizen with 'created_at'
$user_id = $_SESSION['user_id'];
$offers_result = $conn->query("SELECT id, item_ids, status, created_at FROM offers WHERE user_id = $user_id ORDER BY id DESC");

$offers = [];
if ($offers_result && $offers_result->num_rows > 0) {
    while ($row = $offers_result->fetch_assoc()) {
        // Explode item_ids and fetch item names
        $item_ids = explode(',', $row['item_ids']);
        $item_names = [];
        if (!empty($item_ids)) {
            $item_ids_sql = implode(',', array_map('intval', $item_ids));
            $items_result = $conn->query("SELECT name FROM items WHERE id IN ($item_ids_sql)");

            if ($items_result && $items_result->num_rows > 0) {
                while ($item_row = $items_result->fetch_assoc()) {
                    $item_names[] = $item_row['name'];
                }
            }
        }
        
        $offers[] = [
            'id' => $row['id'],
            'items' => implode(', ', $item_names),
            'status' => $row['status'],
            'created_at' => $row['created_at'] // Add created_at
        ];
    }
}

// Handle offer cancellation
if (isset($_GET['cancel_offer_id'])) {
    $offer_id = intval($_GET['cancel_offer_id']);
    $conn->query("DELETE FROM offers WHERE id = $offer_id AND status = 'pending'");
    $_SESSION['success_message'] = 'Offer successfully canceled.';
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
    <h2>My Offers</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (count($offers) > 0): ?>
        <table>
            <tr>
                <th>Items</th>
                <th>Status</th>
                <th>Created At</th> <!-- Add new column -->
                <th>Actions</th>
            </tr>
            <?php foreach ($offers as $offer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($offer['items']); ?></td>
                    <td><?php echo htmlspecialchars($offer['status']); ?></td>
                    <td><?php echo htmlspecialchars($offer['created_at']); ?></td> <!-- Display the created_at value -->
                    <td>
                        <?php if ($offer['status'] == 'pending'): ?>
                            <a href="view_offers.php?cancel_offer_id=<?php echo $offer['id']; ?>" class="button">Cancel</a>
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
