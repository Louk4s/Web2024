<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch all completed tasks from the database
$sql = "
    SELECT 
        t.task_id, 
        t.task_type, 
        t.status, 
        u.fullname AS citizen_name, 
        IF(t.task_type = 'offer', o.item_ids, r.quantity) AS item_details,
        t.completed_at, 
        rescuer.fullname AS rescuer_name
    FROM tasks t
    LEFT JOIN offers o ON t.offer_id = o.id
    LEFT JOIN requests r ON t.request_id = r.id
    LEFT JOIN users u ON (o.user_id = u.id OR r.user_id = u.id)
    LEFT JOIN users rescuer ON t.rescuer_id = rescuer.id
    WHERE t.status = 'completed'
    ORDER BY t.completed_at DESC
";

$result = $conn->query($sql);

$tasks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Tasks</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Completed Tasks</h2>

    <table>
        <tr>
            <th>Task Type</th>
            <th>Completed By</th>
            <th>Citizen</th>
            <th>Items/Request</th>
            <th>Completed At</th>
        </tr>
        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo ucfirst($task['task_type']); ?></td>
                    <td><?php echo htmlspecialchars($task['rescuer_name']); ?></td>
                    <td><?php echo htmlspecialchars($task['citizen_name']); ?></td>
                    <td><?php echo $task['task_type'] == 'offer' ? "Offer of items" : "Request for " . $task['item_details'] . " items"; ?></td>
                    <td><?php echo $task['completed_at']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No completed tasks found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <a href="../dashboards/rescuer_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
</body>
</html>
