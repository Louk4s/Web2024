<?php
include '../db_connect.php'; // Ensure the connection file path is correct

// Check if category_id or category_ids is set
if (isset($_GET['category_id']) || isset($_GET['category_ids'])) {
    // Handle single category (for request_assistance.php)
    if (isset($_GET['category_id'])) {
        $category_id = intval($_GET['category_id']); // Get the single category ID

        if ($category_id > 0) {
            // Query to fetch items that belong to the given category
            $stmt = $conn->prepare("SELECT id, name FROM items WHERE category_id = ?");
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $items_result = $stmt->get_result();

            if ($items_result && $items_result->num_rows > 0) {
                $items = [];
                while ($row = $items_result->fetch_assoc()) {
                    $items[] = $row;
                }
                // Return the items in JSON format
                echo json_encode($items);
            } else {
                // No items found
                echo json_encode([]);
            }
        } else {
            // Invalid category ID
            http_response_code(400);
            echo json_encode(["error" => "Invalid category ID"]);
        }
    }
    // Handle multiple categories (for create_announcement.php)
    else if (isset($_GET['category_ids'])) {
        $category_ids = explode(',', $_GET['category_ids']); // Convert the comma-separated string to an array

        if (!empty($category_ids)) {
            // Prepare placeholders for the SQL query
            $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
            
            // Query to fetch items that belong to the selected categories
            $stmt = $conn->prepare("SELECT id, name FROM items WHERE category_id IN ($placeholders)");

            // Bind the category IDs dynamically
            $types = str_repeat('i', count($category_ids)); // Create a string of 'i's (one for each integer parameter)
            $stmt->bind_param($types, ...$category_ids);
            $stmt->execute();
            $items_result = $stmt->get_result();

            if ($items_result && $items_result->num_rows > 0) {
                $items = [];
                while ($row = $items_result->fetch_assoc()) {
                    $items[] = $row;
                }
                // Return the items in JSON format
                echo json_encode($items);
            } else {
                // No items found
                echo json_encode([]);
            }
        } else {
            // Invalid category IDs
            http_response_code(400);
            echo json_encode(["error" => "Invalid category IDs"]);
        }
    }
} else {
    // No category_id or category_ids parameter in the request
    http_response_code(400);
    echo json_encode(["error" => "Missing category ID or category IDs"]);
}

$conn->close();
?>

