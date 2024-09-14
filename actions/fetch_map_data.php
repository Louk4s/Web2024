<?php
session_start();

// Check if user is logged in and authorized (either admin or rescuer can access this)
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'rescuer')) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Get the rescuer_id of the currently logged-in rescuer
$rescuer_id = $_SESSION['user_id'];

// Fetch base location
$baseQuery = "SELECT latitude, longitude FROM base WHERE id = 1";
$baseResult = $conn->query($baseQuery);
$base = $baseResult->fetch_assoc();

// Fetch rescuers' locations from users table where role = 'rescuer'
$rescuerQuery = "SELECT fullname, latitude, longitude FROM users WHERE role = 'rescuer'";
$rescuerResult = $conn->query($rescuerQuery);
$rescuers = array();

while ($row = $rescuerResult->fetch_assoc()) {
    $rescuers[] = $row;
}

// Fetch tasks excluding tasks in progress assigned to other rescuers
$tasksQuery = "
    SELECT 
        t.task_id, 
        t.task_type, 
        t.latitude, 
        t.longitude, 
        t.status, 
        t.rescuer_id, 
        t.collected_at, 
        u.fullname AS citizen_name,
        u.phone AS citizen_phone,
        t.created_at AS registered_on,
        r.quantity AS request_quantity, 
        o.item_ids AS offer_items, 
        i.name AS item_name, 
        res.fullname AS collected_by
    FROM tasks t
    LEFT JOIN requests r ON t.request_id = r.id
    LEFT JOIN offers o ON t.offer_id = o.id
    LEFT JOIN items i ON r.item_id = i.id
    LEFT JOIN users u ON (r.user_id = u.id OR o.user_id = u.id)
    LEFT JOIN users res ON t.rescuer_id = res.id
    WHERE t.status != 'completed'
      AND (t.status != 'in_progress' OR t.rescuer_id = ?) -- Exclude in-progress tasks unless assigned to the current rescuer
";
$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param('i', $rescuer_id);
$stmt->execute();
$tasksResult = $stmt->get_result();
$tasks = array();

while ($row = $tasksResult->fetch_assoc()) {
    // Process offers for item names
    if ($row['task_type'] == 'offer') {
        $items_and_quantities = explode(',', $row['offer_items']);
        $item_details = [];

        foreach ($items_and_quantities as $item_quantity) {
            list($item_id, $quantity) = explode(':', $item_quantity);
            // Fetch item name from the items table
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

// Return the data in JSON format
echo json_encode([
    'base' => $base,
    'rescuers' => $rescuers,
    'tasks' => $tasks
]);

$conn->close();
?>
