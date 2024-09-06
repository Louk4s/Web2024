<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $category_id = $_POST['category_id'];
    $quantity = $_POST['quantity'];

    // Inserting a new product into the database
    $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $item_name, $category_id, $quantity);

    if ($stmt->execute()) {
        // Save success message
        $success_message = "Item '$item_name' added successfully!";
    } else {
        $error = "Error adding item: " . $stmt->error;
    }

    $stmt->close();
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

    <!-- Display of a success message -->
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Add product form -->
    <form method="POST" action="">
        <label for="item_name">Item Name:</label>
        <input type="text" id="item_name" name="item_name" required><br>

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required><br>

        <button type="submit">Add Item</button>
    </form>

    <a href="manage_inventory.php" class="back-button">Back to Manage Inventory</a>
</div>
</body>
</html>


