<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $quantity = $_POST['quantity'];

    // Updating the quantity in the database
    $stmt = $conn->prepare("UPDATE items SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $id);

    if ($stmt->execute()) {
        $successMessage = "Successful update of the quantity.";
    } else {
        $error = "Error updating quantity: " . $stmt->error;
    }

    $stmt->close();
}

$id = $_GET['id'];

// Fetching the info of the item
$item_result = $conn->query("SELECT * FROM items WHERE id = $id");
$item = $item_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Quantity</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Update Quantity for: <?php echo $item['name']; ?></h2> <!-- Display item name -->

    <!-- Success Message -->
    <?php if ($successMessage): ?>
        <div class="message"><?php echo $successMessage; ?></div> <!-- Use the "message" class for success messages -->
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div> <!-- Use the "error-message" class for error messages -->
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo $item['quantity']; ?>" required>
        <button type="submit">Update</button>
    </form>

    <a href="manage_inventory.php" class="back-button">Back to Manage Inventory</a>
</div>
</body>
</html>
