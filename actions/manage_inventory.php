<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Έλεγχος για μήνυμα επιτυχίας
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Καθαρισμός του μηνύματος μετά την εμφάνισή του
}

// Ανάκτηση όλων των κατηγοριών για το dropdown
$categories_result = $conn->query("SELECT id, category_name FROM categories");
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Έλεγχος αν έχει επιλεγεί κατηγορία
$selected_category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Ανάκτηση των προϊόντων με βάση την επιλεγμένη κατηγορία
if ($selected_category_id) {
    $items_result = $conn->query("SELECT items.id, items.name, items.quantity, categories.category_name 
                                  FROM items 
                                  JOIN categories ON items.category_id = categories.id 
                                  WHERE items.category_id = " . $selected_category_id);
} else {
    // Αν δεν έχει επιλεγεί κατηγορία, εμφάνιση όλων των προϊόντων
    $items_result = $conn->query("SELECT items.id, items.name, items.quantity, categories.category_name 
                                  FROM items 
                                  JOIN categories ON items.category_id = categories.id");
}

$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Manage Inventory</h2>

    <!-- Εμφάνιση μηνύματος επιτυχίας -->
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Κουμπί για προσθήκη νέου προϊόντος -->
    <a href="add_item_form.php"><button>Add New Item</button></a>

    <!-- Dropdown για επιλογή κατηγορίας -->
    <form method="GET" action="manage_inventory.php">
        <label for="category_id">Select Category:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <!-- Προεπιλογή για όλες τις κατηγορίες -->
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php if ($category['id'] == $selected_category_id) echo 'selected'; ?>>
                    <?php echo $category['category_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Πίνακας με τα προϊόντα -->
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
</body>
</html>









