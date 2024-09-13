<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'rescuer')) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Fetch base location
$baseQuery = "SELECT latitude, longitude FROM base WHERE id = 1";
$baseResult = $conn->query($baseQuery);
$base = $baseResult->fetch_assoc();

$logged_in_rescuer_id = $_SESSION['user_id'];

// Fetch rescuers' locations from users table where role = 'rescuer'
$rescuerQuery = "SELECT fullname, latitude, longitude FROM users WHERE role = 'rescuer'";
$rescuerResult = $conn->query($rescuerQuery);
$rescuers = array();

while ($row = $rescuerResult->fetch_assoc()) {
    $rescuers[] = $row;
}

// Fetch tasks from the tasks table with all necessary fields
$tasksQuery = "
    SELECT 
        t.task_id, 
        t.task_type, 
        t.latitude, 
        t.longitude, 
        t.status, 
        t.rescuer_id,
        u.fullname AS citizen_name,
        u.phone AS citizen_phone,
        t.created_at AS registered_on,
        IF(t.task_type = 'offer', o.item_ids, CONCAT(r.quantity, ' x ', i.name)) AS items
    FROM tasks t
    LEFT JOIN offers o ON t.offer_id = o.id
    LEFT JOIN requests r ON t.request_id = r.id
    LEFT JOIN items i ON r.item_id = i.id
    LEFT JOIN users u ON (o.user_id = u.id OR r.user_id = u.id)
    WHERE t.status = 'pending'
    OR (t.status = 'in_progress' AND t.rescuer_id = ?)
";
$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param('i', $logged_in_rescuer_id);
$stmt->execute();
$tasksResult = $stmt->get_result();
$tasks = array();

while ($row = $tasksResult->fetch_assoc()) {
    if ($row['task_type'] == 'offer') {
        // For offers, convert item_ids (item_id:quantity) into item names and quantities
        $items_and_quantities = explode(',', $row['items']);
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
    }
    
    $tasks[] = $row;
}

echo json_encode([
    'base' => $base,
    'rescuers' => $rescuers,
    'tasks' => $tasks
]);

$conn->close();
?>
