<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch tasks from the database
$sql = "
    SELECT 
        t.task_id, 
        t.task_type, 
        t.status, 
        u.fullname AS citizen_name, 
        IF(t.task_type = 'offer', o.item_ids, r.quantity) AS item_details,
        t.latitude, 
        t.longitude, 
        t.rescuer_id
    FROM tasks t
    LEFT JOIN offers o ON t.offer_id = o.id
    LEFT JOIN requests r ON t.request_id = r.id
    LEFT JOIN users u ON (o.user_id = u.id OR r.user_id = u.id)
    WHERE t.status != 'completed'
    ORDER BY t.created_at DESC
";

$result = $conn->query($sql);

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
            <th>Items/Request</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo ucfirst($task['task_type']); ?></td>
                <td><?php echo ucfirst($task['status']); ?></td>
                <td><?php echo htmlspecialchars($task['citizen_name']); ?></td>
                <td><?php echo $task['task_type'] == 'offer' ? "Offer of items" : "Request for " . $task['item_details'] . " items"; ?></td>
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
