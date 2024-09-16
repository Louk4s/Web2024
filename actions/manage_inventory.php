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
$items_result = null; // Make sure items_result is initialized
if (!empty($selected_category_ids)) {
    // Ensure that we are working with an array
    if (!is_array($selected_category_ids)) {
        $selected_category_ids = [$selected_category_ids];
    }

    // Prepare the SQL query for multiple selected categories
    $selected_category_ids = array_map('intval', $selected_category_ids); // Secure the input
    $selected_category_ids_sql = implode(',', $selected_category_ids); // Convert to SQL-friendly format

    // Fetch base inventory and truck inventory
    $items_result = $conn->query("
        SELECT i.id, i.name, i.quantity AS base_quantity, c.category_name, 
               COALESCE(SUM(r.quantity), 0) AS truck_quantity
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN (
            SELECT item_id, SUM(quantity) AS quantity
            FROM inventory 
            GROUP BY item_id
        ) r ON i.id = r.item_id
        WHERE i.category_id IN ($selected_category_ids_sql)
        GROUP BY i.id
        ORDER BY i.id DESC");
} else {
    // Show all items if no category is selected
    $items_result = $conn->query("
        SELECT i.id, i.name, i.quantity AS base_quantity, c.category_name, 
               COALESCE(SUM(r.quantity), 0) AS truck_quantity
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN (
            SELECT item_id, SUM(quantity) AS quantity
            FROM inventory 
            GROUP BY item_id
        ) r ON i.id = r.item_id
        GROUP BY i.id
        ORDER BY i.id DESC");
}

// Now check if the query executed successfully
if ($items_result && $items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
} else {
    echo "No items found or error fetching items: " . $conn->error;
}

// Fetch details for each item and concatenate them, ensuring that only distinct values are retrieved
$item_details = [];
$details_query = "
    SELECT item_id, 
           GROUP_CONCAT(DISTINCT CONCAT(detail_name, ': ', detail_value) SEPARATOR ', ') AS item_details
    FROM item_details
    GROUP BY item_id";

$details_result = $conn->query($details_query);

if ($details_result && $details_result->num_rows > 0) {
    while ($detail_row = $details_result->fetch_assoc()) {
        $item_details[$detail_row['item_id']] = $detail_row['item_details'];
    }
} else {
    echo "Error fetching item details: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Base Inventory</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/styles.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">

    <!-- Back to Admin Dashboard Button  -->
    <div class="button-container">
    <a href="../dashboards/admin_dashboard.php" class="back-button back-to-dashboard-top">Back to Admin Dashboard</a>
    </div>
    <h2>Manage Base Inventory</h2>

    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="message"><?php echo $success_message; ?></div> <!-- Styled message from CSS -->
    <?php endif; ?>
    
    <a href="add_item_form.php" class="add-button">Add New Item</a>

    <!-- Update Inventory Button -->
    <form action="../actions/update_inventory.php" method="POST">
        <button type="submit" class="update-button">Update Inventory</button>
    </form>

    <!-- Dropdown for selecting multiple categories -->
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
            <th>Total Quantity</th>
            <th>Base Quantity</th>
            <th>Truck Quantity</th>
            <th>Details</th> <!-- New column for item details -->
            <th>Actions</th>
        </tr>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['category_name']; ?></td>
                    <td><?php echo $item['base_quantity'] + $item['truck_quantity']; ?></td> <!-- Total Quantity -->
                    <td><?php echo $item['base_quantity']; ?></td> <!-- Quantity in base -->
                    <td><?php echo $item['truck_quantity']; ?></td> <!-- Quantity in trucks -->
                    <td>
                        <?php echo isset($item_details[$item['id']]) ? $item_details[$item['id']] : 'N/A'; ?>
                    </td> <!-- Display concatenated details -->
                    <td>
                        <a href="edit_inventory.php?id=<?php echo $item['id']; ?>">Edit</a>
                        <a href="delete_inventory.php?id=<?php echo $item['id']; ?>">Delete</a>
                        <a href="update_quantity.php?id=<?php echo $item['id']; ?>">Update Quantity</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No items found in this category.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" class="back-button">Back to Top</button>

    <!-- Back to Admin Dashboard Button -->
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>

<!-- jQuery for Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom JS for manage_inventory -->
<script src="../scripts/manage_inventory.js"></script>

</body>
</html>
