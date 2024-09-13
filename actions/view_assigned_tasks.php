<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$rescuer_id = $_SESSION['user_id'];

// Fetch tasks that are either pending or in progress for the current rescuer
$sql = "
    SELECT 
        t.task_id, 
        t.task_type, 
        t.status, 
        u.fullname AS citizen_name, 
        u.phone AS citizen_phone, 
        IF(t.task_type = 'offer', o.item_ids, r.quantity) AS item_details,
        IF(t.task_type = 'offer', NULL, i.name) AS item_name,
        t.latitude, 
        t.longitude, 
        t.rescuer_id
    FROM tasks t
    LEFT JOIN offers o ON t.offer_id = o.id
    LEFT JOIN requests r ON t.request_id = r.id
    LEFT JOIN items i ON r.item_id = i.id
    LEFT JOIN users u ON (o.user_id = u.id OR r.user_id = u.id)
    WHERE t.status = 'pending'
    OR (t.status = 'in_progress' AND t.rescuer_id = ?)
    ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $rescuer_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

// Fetch the rescuer's current location
$rescuer_location_sql = "SELECT latitude, longitude FROM users WHERE id = ?";
$stmt = $conn->prepare($rescuer_location_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$rescuer_location_result = $stmt->get_result();
$rescuer_location = $rescuer_location_result->fetch_assoc();
$stmt->close();

// Collect all item details in advance (for offer items)
$all_items_query = "SELECT id, name FROM items";
$all_items_result = $conn->query($all_items_query);
$items = [];
if ($all_items_result && $all_items_result->num_rows > 0) {
    while ($item_row = $all_items_result->fetch_assoc()) {
        $items[$item_row['id']] = $item_row['name'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assigned Tasks</title>
    <link rel="stylesheet" href="../style/styles.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="../scripts/rescuer_map.js"></script>
</head>
<body>
<div class="container">
    <h2>Assigned Tasks</h2>

    <!-- Task Table -->
    <table>
        <tr>
            <th>Task Type</th>
            <th>Status</th>
            <th>Citizen</th>
            <th>Phone</th>
            <th>Offers/Request</th>
            <th>Action</th>
            <th>Locate on Map</th> <!-- New Column for the locate button -->
        </tr>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo ucfirst($task['task_type']); ?></td>
                <td><?php echo ucfirst($task['status']); ?></td>
                <td><?php echo htmlspecialchars($task['citizen_name']); ?></td>
                <td><?php echo htmlspecialchars($task['citizen_phone']); ?></td>
                <td>
                    <?php 
                    if ($task['task_type'] == 'offer') {
                        // For offers, display the item names and quantities
                        $offer_items = explode(',', $task['item_details']);
                        foreach ($offer_items as $item) {
                            list($item_id, $quantity) = explode(':', $item);
                            echo "Offer of $quantity " . htmlspecialchars($items[$item_id]) . "<br>";
                        }
                    } else {
                        // For requests, display item name and requested quantity
                        echo "Request for " . htmlspecialchars($task['item_details']) . " " . htmlspecialchars($task['item_name']);
                    }
                    ?>
                </td>
                <td>
                    <?php if ($task['status'] == 'pending' && is_null($task['rescuer_id'])): ?>
                        <a href="accept_task.php?task_id=<?php echo $task['task_id']; ?>" class="button">Accept Task</a>
                    <?php elseif ($task['status'] == 'in_progress' && $task['rescuer_id'] == $_SESSION['user_id']): ?>
                        <a href="complete_task.php?task_id=<?php echo $task['task_id']; ?>" class="button" id="completeTaskBtn_<?php echo $task['task_id']; ?>">Complete Task</a>
                    <?php elseif ($task['status'] == 'in_progress' && $task['rescuer_id'] != $_SESSION['user_id']): ?>
                        Task assigned to another rescuer
                    <?php else: ?>
                        Task Completed
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Locate on Map Button -->
                    <button class="locate-btn" data-lat="<?php echo $task['latitude']; ?>" data-lng="<?php echo $task['longitude']; ?>">Locate on Map</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Map Container -->
    <div id="mapContainer" style="height: 500px;"></div>

    <a href="#" id="moveMarkerBtn" class="button">Move Marker</a>
    <a href="#" id="saveMarkerBtn" class="button">Save Location</a>

    <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Dashboard</a>

    <script>
        // Pass the tasks and rescuer location data to the JS map script
        var tasksData = <?php echo json_encode($tasks); ?>;
        var rescuerLocation = <?php echo json_encode($rescuer_location); ?>;
    </script>
</div>
</body>
</html>
