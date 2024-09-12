<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch rescuer ID based on the username from the session
$rescuer_username = $_SESSION['username'];
$rescuer_query = "SELECT id FROM users WHERE username = '$rescuer_username' AND role = 'rescuer'";
$rescuer_result = $conn->query($rescuer_query);
if ($rescuer_result && $rescuer_result->num_rows > 0) {
    $rescuer_data = $rescuer_result->fetch_assoc();
    $rescuer_id = $rescuer_data['id'];
} else {
    die("Error: Rescuer not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unload_item_id'], $_POST['unload_quantities'])) {
    $item_ids = $_POST['unload_item_id'];  // Array of selected item IDs
    $quantities = explode(',', $_POST['unload_quantities']);  // Comma-separated quantities

    foreach ($item_ids as $index => $item_id) {
        $unload_quantity = (int)$quantities[$index]; // Get corresponding quantity for each item

        // Check if the item exists in inventory for the rescuer
        $check_inventory_query = "SELECT * FROM inventory WHERE item_id = $item_id AND rescuer_id = $rescuer_id";
        $check_inventory_result = $conn->query($check_inventory_query);

        if ($check_inventory_result && $check_inventory_result->num_rows > 0) {
            $inventory_item = $check_inventory_result->fetch_assoc();
            $current_quantity = $inventory_item['quantity'];

            // Unload item if rescuer has enough
            if ($current_quantity >= $unload_quantity) {
                // Decrease the existing item quantity in the rescuer's inventory
                $new_quantity = $current_quantity - $unload_quantity;
                if ($new_quantity > 0) {
                    $update_inventory_query = "UPDATE inventory SET quantity = $new_quantity WHERE item_id = $item_id AND rescuer_id = $rescuer_id";
                    $conn->query($update_inventory_query);
                } else {
                    // Remove item from rescuer's inventory if quantity reaches zero
                    $delete_inventory_query = "DELETE FROM inventory WHERE item_id = $item_id AND rescuer_id = $rescuer_id";
                    $conn->query($delete_inventory_query);
                }

                // Add the quantity back to the base inventory (items table)
                $update_base_query = "UPDATE items SET quantity = quantity + $unload_quantity WHERE id = $item_id";
                $conn->query($update_base_query);
            } else {
                echo "Error: Not enough quantity to unload.";
            }
        }
    }

    // Redirect back to the inventory page after unloading items
    header("Location: manage_inventory_rescuer.php");
    exit();
}

$conn->close();
?>
