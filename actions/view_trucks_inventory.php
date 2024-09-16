<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch rescuer's ID
$rescuer_username = $_SESSION['username'];
$user_query = "SELECT id FROM users WHERE username = '$rescuer_username' AND role = 'rescuer'";
$user_result = $conn->query($user_query);
$user_data = $user_result->fetch_assoc();
$rescuer_id = $user_data['id'];

// Fetch current inventory for rescuer
$inventory_query = "
    SELECT i.name AS item_name, inv.quantity 
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.rescuer_id = '$rescuer_id'
";
$inventory_result = $conn->query($inventory_query);
$inventory_items = [];
while ($row = $inventory_result->fetch_assoc()) {
    $inventory_items[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Truck's Inventory</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2><?php echo $_SESSION['username']; ?>'s Truck Inventory</h2>

    <!-- View Current Inventory -->
    <h3>Current Truck Inventory</h3>
    <?php if (count($inventory_items) > 0): ?>
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
            </tr>
            <?php foreach ($inventory_items as $inv_item): ?>
                <tr>
                    <td><?= htmlspecialchars($inv_item['item_name']) ?></td>
                    <td><?= htmlspecialchars($inv_item['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No items loaded in the inventory.</p>
    <?php endif; ?>

    <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Rescuer Dashboard</a>
</div>
</body>
</html>
