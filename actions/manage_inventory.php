<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Success Message
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear message after display
}

// Fetch categories for the dropdown
$categories_result = $conn->query("SELECT id, category_name FROM categories");
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    echo "Error fetching categories: " . $conn->error;
}

// Check if categories are selected
$selected_category_ids = isset($_GET['category_id']) ? $_GET['category_id'] : [];

// Convert selected categories to SQL-friendly format
$items = [];
if (!empty($selected_category_ids)) {
    // Ensure that we are working with an array
    if (!is_array($selected_category_ids)) {
        $selected_category_ids = [$selected_category_ids];
    }

    // Prepare the SQL query for multiple selected categories
    $selected_category_ids = array_map('intval', $selected_category_ids); // Secure the input
    $selected_category_ids_sql = implode(',', $selected_category_ids); // Convert to SQL-friendly format

    $items_result = $conn->query("SELECT items.id, items.name, items.quantity, categories.category_name 
                                  FROM items 
                                  JOIN categories ON items.category_id = categories.id 
                                  WHERE items.category_id IN ($selected_category_ids_sql)");
} else {
    // Show all items if no category is selected
    $items_result = $conn->query("SELECT items.id, items.name, items.quantity, categories.category_name 
                                  FROM items 
                                  JOIN categories ON items.category_id = categories.id");
}

if ($items_result && $items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
} else {
    echo "No items found or error fetching items: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/styles.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
    <h2>Manage Inventory</h2>

    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <a href="add_item_form.php"><button>Add New Item</button></a>

    <!-- Dropdown για επιλογή πολλαπλών κατηγοριών -->
    <form method="GET" action="manage_inventory.php">
        <label for="category_id">Select Categories:</label>
        <select name="category_id[]" id="category_id" multiple="multiple" style="width: 100%;">
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" 
                    <?php if (isset($selected_category_ids) && in_array($category['id'], (array)$selected_category_ids)) echo 'selected'; ?>>
                    <?php echo $category['category_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Select</button>
    </form>

    <!-- Table with the items -->
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['category_name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>
                        <a href="edit_inventory.php?id=<?php echo $item['id']; ?>">Edit</a>
                        <a href="delete_inventory.php?id=<?php echo $item['id']; ?>">Delete</a>
                        <a href="update_quantity.php?id=<?php echo $item['id']; ?>">Update Quantity</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No items found in this category.</td>
            </tr>
        <?php endif; ?>
    </table>
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>

<!-- jQuery (απαραίτητο για το Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom JS για το manage_inventory -->
<script src="../scripts/manage_inventory.js"></script>

</body>
</html>










