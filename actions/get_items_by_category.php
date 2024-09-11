<?php
include '../db_connect.php'; // Ensure the connection file path is correct

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
} else {
    // No category_id parameter in the request
    http_response_code(400);
    echo json_encode(["error" => "Missing category ID"]);
}

$conn->close();
?>
