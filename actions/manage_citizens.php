<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$citizens_result = $conn->query("SELECT * FROM users WHERE role = 'citizen'");
$citizens = [];
while ($row = $citizens_result->fetch_assoc()) {
    $citizens[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Citizens</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Citizens</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Username</th>
        </tr>
        <?php foreach ($citizens as $citizen): ?>
            <tr>
                <td><?php echo $citizen['id']; ?></td>
                <td><?php echo $citizen['fullname']; ?></td>
                <td><?php echo $citizen['phone']; ?></td>
                <td><?php echo $citizen['username']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
</body>
</html>
