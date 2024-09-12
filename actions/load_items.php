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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['quantities'])) {
    $item_ids = $_POST['item_id'];  // Array of selected item IDs
    $quantities = explode(',', $_POST['quantities']);  // Comma-separated quantities

    foreach ($item_ids as $index => $item_id) {
        $load_quantity = (int)$quantities[$index]; // Get corresponding quantity for each item

        // Check if the item is available and fetch the current quantity
        $item_query = "SELECT * FROM items WHERE id = $item_id";
        $item_result = $conn->query($item_query);
        if ($item_result && $item_result->num_rows > 0) {
            $item = $item_result->fetch_assoc();
            $available_quantity = $item['quantity'];

            // Load item if available
            if ($available_quantity >= $load_quantity) {

                // Decrease quantity in items table
                $new_quantity = $available_quantity - $load_quantity;
                $update_item_query = "UPDATE items SET quantity = $new_quantity WHERE id = $item_id";
                $conn->query($update_item_query);

                // Check if item already exists in rescuer's inventory
                $check_inventory_query = "SELECT * FROM inventory WHERE item_id = $item_id AND rescuer_id = $rescuer_id";
                $inventory_result = $conn->query($check_inventory_query);

                if ($inventory_result && $inventory_result->num_rows > 0) {
                    // Item already exists in inventory, update the quantity
                    $update_inventory_query = "UPDATE inventory SET quantity = quantity + $load_quantity WHERE item_id = $item_id AND rescuer_id = $rescuer_id";
                    $conn->query($update_inventory_query);
                } else {
                    // Item doesn't exist in inventory, insert a new record
                    $insert_inventory_query = "INSERT INTO inventory (item_id, rescuer_id, item_name, quantity) VALUES ($item_id, $rescuer_id, '{$item['name']}', $load_quantity)";
                    $conn->query($insert_inventory_query);
                }
            } else {
                echo "Not enough items available.";
            }
        }
    }

    // Redirect back to the inventory page after loading items
    header("Location: manage_inventory_rescuer.php");
    exit();
}

$conn->close();
?>

