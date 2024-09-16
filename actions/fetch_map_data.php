<?php
session_start();

// Check if user is logged in and authorized
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'rescuer')) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include('../db_connect.php');

// Error handling for better debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Fetch base location
    $baseQuery = "SELECT latitude, longitude FROM base WHERE id = 1";
    $baseResult = $conn->query($baseQuery);
    $base = $baseResult->fetch_assoc();

    // Admin-specific query (fetch all rescuers and tasks)
    if ($_SESSION['role'] === 'admin') {
        // Fetch rescuers' locations and inventory
        $rescuerQuery = "
            SELECT u.id AS rescuer_id, u.fullname, u.latitude, u.longitude, 
            GROUP_CONCAT(CONCAT(i.name, ' x ', inv.quantity) SEPARATOR ', ') AS inventory 
            FROM users u
            LEFT JOIN inventory inv ON u.id = inv.rescuer_id
            LEFT JOIN items i ON inv.item_id = i.id
            WHERE u.role = 'rescuer'
            GROUP BY u.id
        ";
        $rescuerResult = $conn->query($rescuerQuery);
        $rescuers = array();
        
        while ($row = $rescuerResult->fetch_assoc()) {
            $rescuers[] = $row;
        }

        // Fetch all tasks (including offers and requests)
        $tasksQuery = "
            SELECT 
                t.task_id, t.task_type, t.latitude, t.longitude, t.status, t.rescuer_id, 
                u.fullname AS citizen_name, u.phone AS citizen_phone, 
                res.fullname AS rescuer_name,  -- Fetch the assigned rescuer's name
                t.created_at AS registered_on, -- Use created_at for 'Accepted On'
                t.collected_at,  -- Use collected_at for when rescuer accepted the task
                r.quantity AS request_quantity, 
                o.item_ids AS offer_items, i.name AS item_name 
            FROM tasks t
            LEFT JOIN requests r ON t.request_id = r.id
            LEFT JOIN offers o ON t.offer_id = o.id
            LEFT JOIN items i ON r.item_id = i.id
            LEFT JOIN users u ON (r.user_id = u.id OR o.user_id = u.id)
            LEFT JOIN users res ON t.rescuer_id = res.id  -- Join with rescuer's info
            WHERE t.status != 'completed'
        ";
        $tasksResult = $conn->query($tasksQuery);
    } 
    // Rescuer-specific query (only see assigned tasks)
    else {
        $rescuer_id = $_SESSION['user_id']; // Rescuer ID from session

        // Fetch tasks, ensuring in-progress tasks are visible only to the assigned rescuer
        $tasksQuery = "
            SELECT 
                t.task_id, t.task_type, t.latitude, t.longitude, t.status, t.rescuer_id, 
                u.fullname AS citizen_name, u.phone AS citizen_phone, 
                res.fullname AS rescuer_name,  -- Fetch the assigned rescuer's name
                t.created_at AS registered_on,  -- Use created_at for 'Accepted On'
                t.collected_at,  -- Use collected_at for when rescuer accepted the task
                r.quantity AS request_quantity, 
                o.item_ids AS offer_items, i.name AS item_name 
            FROM tasks t
            LEFT JOIN requests r ON t.request_id = r.id
            LEFT JOIN offers o ON t.offer_id = o.id
            LEFT JOIN items i ON r.item_id = i.id
            LEFT JOIN users u ON (r.user_id = u.id OR o.user_id = u.id)
            LEFT JOIN users res ON t.rescuer_id = res.id  -- Join with rescuer's info
            WHERE t.status != 'completed'
            AND (t.status != 'in_progress' OR t.rescuer_id = ?)  -- Show in-progress tasks only for the assigned rescuer
        ";
        $stmt = $conn->prepare($tasksQuery);
        $stmt->bind_param('i', $rescuer_id);  // Use the rescuer's ID from session
        $stmt->execute();
        $tasksResult = $stmt->get_result();
    }

    $tasks = array();
    while ($row = $tasksResult->fetch_assoc()) {
        // Process offers for item names
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

    // Return the data in JSON format
    if ($_SESSION['role'] === 'admin') {
        echo json_encode([
            'base' => $base,
            'rescuers' => $rescuers,
            'tasks' => $tasks
        ]);
    } else {
        echo json_encode([
            'base' => $base,
            'tasks' => $tasks
        ]);
    }

} catch (Exception $e) {
    // Return error message in JSON format
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

$conn->close();
