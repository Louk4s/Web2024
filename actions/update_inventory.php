<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch the JSON data from the provided URL
$json_url = 'http://usidas.ceid.upatras.gr/web/2023/export.php';
$json_data = file_get_contents($json_url);
$data = json_decode($json_data, true);

if ($data === null) {
    die("Error decoding JSON");
}

// Process each item from the JSON
$items = $data['items'];
foreach ($items as $item) {
    $name = $item['name'];
    $category_name = $item['category']; // Assuming category in JSON is a name, not ID

    // Fetch the corresponding category ID from the database
    $category_query = "SELECT id FROM categories WHERE category_name = '$category_name'";
    $category_result = $conn->query($category_query);

    if ($category_result && $category_result->num_rows > 0) {
        $category_row = $category_result->fetch_assoc();
        $category_id = intval($category_row['id']);
    } else {
        // If category does not exist, create a new category
        $insert_category_query = "INSERT INTO categories (category_name) VALUES ('$category_name')";
        if ($conn->query($insert_category_query) === TRUE) {
            $category_id = $conn->insert_id; // Get the ID of the newly created category
        } else {
            echo "Error: " . $insert_category_query . "<br>" . $conn->error;
            continue; // Skip to the next item if there's an error
        }
    }

    $quantity = 0; // Assuming new items start with 0 quantity

    // Check if the item already exists in the database
    $check_item_query = "SELECT id FROM items WHERE name = '$name' AND category_id = $category_id";
    $check_result = $conn->query($check_item_query);

    if ($check_result && $check_result->num_rows > 0) {
        // Item exists, update it
        $update_query = "UPDATE items SET quantity = quantity + $quantity WHERE name = '$name' AND category_id = $category_id";
        $conn->query($update_query);
    } else {
        // Item does not exist, insert it
        $insert_query = "INSERT INTO items (name, category_id, quantity) VALUES ('$name', $category_id, $quantity)";
        $conn->query($insert_query);
    }

    // Insert item details if present
    if (isset($item['details']) && is_array($item['details'])) {
        foreach ($item['details'] as $detail) {
            $detail_name = $detail['detail_name'];
            $detail_value = $detail['detail_value'];

            // Insert each detail for the item
            $insert_detail_query = "INSERT INTO item_details (item_id, detail_name, detail_value) 
                                    VALUES ((SELECT id FROM items WHERE name = '$name' AND category_id = $category_id), 
                                    '$detail_name', '$detail_value')";
            $conn->query($insert_detail_query);
        }
    }
}

$_SESSION['success_message'] = "Inventory successfully updated.";
header("Location: manage_inventory.php");

$conn->close();
?>

