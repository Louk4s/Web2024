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

// Fetch all items from the base for loading
$items_query = "SELECT * FROM items";
$items_result = $conn->query($items_query);
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

// Fetch current inventory for rescuer for unloading
$inventory_query = "
    SELECT i.name AS item_name, inv.quantity, inv.item_id
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.rescuer_id = $rescuer_id
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
    <title>Manage Inventory (Rescuer)</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Manage Inventory for <?php echo $_SESSION['username']; ?> (Rescuer)</h2>

    <!-- Load Items -->
    <h3>Load Items from Base Storage</h3>
    <form action="../actions/load_items.php" method="POST">
        <select name="item_id[]" multiple="multiple" size="10">
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>"><?= $item['name'] ?> - Available: <?= $item['quantity'] ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Associate quantities for each selected item -->
        <label for="quantities">Enter quantities for each selected item (comma separated):</label>
        <input type="text" name="quantities" placeholder="e.g. 2,1" required>

        <input type="submit" value="Load Selected Items">
    </form>

    <!-- Unload Items -->
    <h3>Unload Items from the Truck</h3>
    <form action="../actions/unload_items.php" method="POST">
        <select name="unload_item_id[]" multiple="multiple" size="10">
            <?php if (!empty($inventory_items)): ?>
                <?php foreach ($inventory_items as $inv_item): ?>
                    <option value="<?= $inv_item['item_id'] ?>"><?= $inv_item['item_name'] ?> - Quantity: <?= $inv_item['quantity'] ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option>No items in inventory</option>
            <?php endif; ?>
        </select>

        <!-- Associate quantities for unloading -->
        <label for="unload_quantities">Enter quantities for each selected item (comma separated):</label>
        <input type="text" name="unload_quantities" placeholder="e.g. 2,1" required>

        <input type="submit" value="Unload Selected Items">
    </form>

    <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
</body>
</html>
