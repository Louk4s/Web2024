<?php 
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $category_id = $_POST['category_id'];
    $quantity = $_POST['quantity'];
    $detail_name = $_POST['detail_name'];
    $detail_value = $_POST['detail_value'];

    // Check if the item with the same name and category already exists
    $check_query = "
        SELECT id 
        FROM items 
        WHERE name = ? 
        AND category_id = ?
    ";

    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("si", $item_name, $category_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If the item already exists, show an error message
        $error_message = "The item you are trying to add already exists (in this category). Update the quantity instead.";
    } else {
        // Proceed with item insertion if it doesn't exist
        $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $item_name, $category_id, $quantity);

        if ($stmt->execute()) {
            // Get the last inserted ID to use in the item_details table
            $item_id = $conn->insert_id;

            // Insert the details into the item_details table
            $detail_stmt = $conn->prepare("INSERT INTO item_details (item_id, detail_name, detail_value) VALUES (?, ?, ?)");
            $detail_stmt->bind_param("iss", $item_id, $detail_name, $detail_value);
            $detail_stmt->execute();
            $detail_stmt->close();

            // Save success message
            $success_message = "Item '$item_name' added successfully!";
        } else {
            $error_message = "Error adding item: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
}

// Retrieval of categories for the dropdown
$category_result = $conn->query("SELECT * FROM categories");
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Add New Item</h2>

    <!-- Display of success or error message -->
    <?php if ($success_message): ?>
        <div class="message"><?php echo $success_message; ?></div> <!-- Use the "message" class for success messages -->
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div> <!-- Use the "error-message" class for error messages -->
    <?php endif; ?>

    <!-- Add product form -->
    <form method="POST" action="" onsubmit="return validateAddItemForm();">
        <label for="item_name">Item Name:</label>
        <input type="text" id="item_name" name="item_name" required><br>

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <option value="" disabled selected>Select Category</option> <!-- Default option -->
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required><br>

        <!-- Add item details fields -->
        <label for="detail_name">Detail Name:</label>
        <input type="text" id="detail_name" name="detail_name" required><br>

        <label for="detail_value">Detail Value:</label>
        <input type="text" id="detail_value" name="detail_value" required><br>

        <button type="submit">Add Item</button>
    </form>

    <a href="manage_inventory.php" class="back-button">Back to Manage Inventory</a>
</div>

<!-- Include your JS validation script here -->
<script src="../scripts/validation.js"></script>

</body>
</html>
