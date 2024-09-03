<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'rescuer') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php';

$rescuer_id = $_SESSION['user_id']; // Assuming the rescuer's ID is stored in the session

// Fetch tasks assigned to the rescuer
$sql = "SELECT tasks.id, tasks.status, requests.details FROM tasks 
        JOIN requests ON tasks.request_id = requests.id 
        WHERE tasks.rescuer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rescuer_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tasks</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Your Tasks</h2>
    <table>
        <tr>
            <th>Task ID</th>
            <th>Request Details</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo $task['id']; ?></td>
                <td><?php echo $task['details']; ?></td>
                <td><?php echo $task['status']; ?></td>
                <td>
                    <?php if ($task['status'] == 'pending'): ?>
                        <a href="start_task.php?id=<?php echo $task['id']; ?>">Start Task</a>
                    <?php elseif ($task['status'] == 'in_progress'): ?>
                        <a href="complete_task.php?id=<?php echo $task['id']; ?>">Complete Task</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="rescuer_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
</body>
</html>
