<?php
session_start();

// Check if the rescuer is logged in and has valid permissions
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Sanitize the rescuer ID received from the query parameters
$rescuer_id = isset($_GET['rescuer_id']) ? intval($_GET['rescuer_id']) : 0;

// Error handling
if ($rescuer_id === 0) {
    echo json_encode(['error' => 'Invalid Rescuer ID']);
    exit();
}

try {
    // Fetch tasks assigned to the rescuer
    $taskQuery = "
        SELECT 
            t.task_id, t.task_type, t.status, t.latitude, t.longitude,
            r.quantity AS request_quantity, i.name AS item_name,
            o.item_ids AS offer_items, u.fullname AS citizen_name, u.phone AS citizen_phone
        FROM tasks t
        LEFT JOIN requests r ON t.request_id = r.id
        LEFT JOIN offers o ON t.offer_id = o.id
        LEFT JOIN items i ON r.item_id = i.id
        LEFT JOIN users u ON r.user_id = u.id
        WHERE t.rescuer_id = ? AND t.status != 'completed'
    ";

    $stmt = $conn->prepare($taskQuery);
    $stmt->bind_param('i', $rescuer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        // Process offer items
        if ($row['task_type'] == 'offer') {
            $items_and_quantities = explode(',', $row['offer_items']);
            $item_details = [];
            foreach ($items_and_quantities as $item_quantity) {
                list($item_id, $quantity) = explode(':', $item_quantity);
                $item_name_query = "SELECT name FROM items WHERE id = ?";
                $stmt_item = $conn->prepare($item_name_query);
                $stmt_item->bind_param('i', $item_id);
                $stmt_item->execute();
                $result_item = $stmt_item->get_result();
                $item_name = $result_item->fetch_assoc()['name'];
                $item_details[] = $quantity . ' x ' . $item_name;
            }
            $row['items'] = implode(', ', $item_details);
        } else {
            $row['items'] = $row['request_quantity'] . ' x ' . $row['item_name'];
        }

        $tasks[] = $row;
    }

    // Return tasks in JSON format
    echo json_encode(['tasks' => $tasks]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();

?>
