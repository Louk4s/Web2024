<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$message = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // First delete any related records from the item_details table
    $stmt = $conn->prepare("DELETE FROM item_details WHERE item_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Then delete the item from the items table
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Inventory item deleted successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    $message = "No inventory item ID provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Inventory Item</title>
    <link rel="stylesheet" href="../style/styles.css">
    <script src="../scripts/delete_inventory.js"></script>
</head>
<body>
<div class="container">
    <h2>Inventory Item Deletion</h2>
    <input type="hidden" id="message" value="<?php echo $message; ?>">
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
    <p> </p>
    <a href="manage_inventory.php" class="back-button">Back to Manage Inventory Page</a>

</div>
</body>
</html>
