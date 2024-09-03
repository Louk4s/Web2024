<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$rescuers_result = $conn->query("SELECT * FROM users WHERE role = 'rescuer'");
$rescuers = [];
while ($row = $rescuers_result->fetch_assoc()) {
    $rescuers[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rescuers</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Manage Rescuers</h2>
    <a href="add_rescuer.php" class="add-button">Add Rescuer</a> 
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Username</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($rescuers as $rescuer): ?>
            <tr>
                <td><?php echo $rescuer['id']; ?></td>
                <td><?php echo $rescuer['fullname']; ?></td>
                <td><?php echo $rescuer['phone']; ?></td>
                <td><?php echo $rescuer['username']; ?></td>
                <td>
                    <a href="edit_rescuer.php?id=<?php echo $rescuer['id']; ?>">Edit</a>
                    <a href="delete_rescuer.php?id=<?php echo $rescuer['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
</body>
</html>
