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

// Fetch all categories from the categories table
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);
$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch all items from the base for loading based on the selected category
$items = [];
if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
    $selected_categories = $_POST['category_id'];
    $categories_placeholder = implode(",", array_fill(0, count($selected_categories), '?'));
    $items_query = $conn->prepare("SELECT * FROM items WHERE category_id IN ($categories_placeholder)");
    $items_query->bind_param(str_repeat('i', count($selected_categories)), ...$selected_categories);
    $items_query->execute();
    $items_result = $items_query->get_result();
} else {
    $items_query = "SELECT * FROM items";
    $items_result = $conn->query($items_query);
}

while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

// Fetch current inventory for rescuer for unloading
$inventory_query = "
    SELECT i.name AS item_name, inv.quantity, inv.item_id
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.rescuer_id = $rescuer_id AND inv.quantity > 0
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
    <script>
        // This function will scroll the page to the Load Items section after the form is submitted
        function stayInPlaceAfterSubmit() {
            const loadItemsSection = document.getElementById('load-items-section');
            if (loadItemsSection) {
                loadItemsSection.scrollIntoView();
            }
        }
    </script>
</head>
<body onload="stayInPlaceAfterSubmit()">
<div class="container">
    <h2>Manage Inventory for <?php echo $_SESSION['username']; ?> (Rescuer)</h2>

    <!-- Select Category -->
    <h3>Select a Category(Press Ctrl for multiple)</h3>
    <form action="manage_inventory_rescuer.php" method="POST">
        <select name="category_id[]" multiple="multiple" size="5">
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter by Category">
    </form>

    <!-- Load Items -->
    <h3 id="load-items-section">Load Items from Base Storage(Press Ctrl for multiple)</h3> <!-- Assign id to this section -->
    <form action="../actions/load_items.php" method="POST" onsubmit="stayInPlaceAfterSubmit()">
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
    <h3>Unload Items from the Truck(Press Ctrl for multiple)</h3>
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

    <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Rescuer Dashboard</a>
</div>
</body>
</html>
