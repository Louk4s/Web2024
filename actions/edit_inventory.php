<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$message = "";

// Fetch all categories (id and name) for the dropdown
$category_result = $conn->query("SELECT id, category_name FROM categories");
$categories = [];
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'], $_POST['item_name'], $_POST['category_name'])) {
        $id = $_POST['id'];
        $name = $_POST['item_name'];
        $category_name = $_POST['category_name'];

        // Get the category ID based on the selected category name
        $stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->bind_result($category_id);
        $stmt->fetch();
        $stmt->close();

        if ($category_id) {
            // Update the item in the items table
            $stmt = $conn->prepare("UPDATE items SET name = ?, category_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $category_id, $id);

            if ($stmt->execute()) {
                $message = "Inventory item updated successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Invalid category name!";
        }
    } else {
        $message = "ID, Item Name, or Category not set or invalid.";
    }
} else {
    // If not a POST request, load the item details for the given ID
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
        } else {
            $message = "Item not found.";
        }
        $stmt->close();
    } else {
        $message = "ID not set or invalid.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Edit Inventory</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!isset($message) || !$message || isset($item)): ?>
        <form method="post" action="edit_inventory.php">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
            <label for="item_name">Item Name:</label>
            <input type="text" id="item_name" name="item_name" value="<?php echo $item['name']; ?>" required>

            <!-- Dropdown for selecting category by name -->
            <label for="category_name">Category Name:</label>
            <select id="category_name" name="category_name" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_name']; ?>" <?php echo $category['id'] == $item['category_id'] ? 'selected' : ''; ?>>
                        <?php echo $category['category_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Update Item</button>
        </form>
    <?php endif; ?>
    <a href="manage_inventory.php" class="back-button">Back to Manage Inventory</a>
</div>
</body>
</html>
