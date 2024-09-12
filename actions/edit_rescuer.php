<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Check if 'id' is provided in the URL
if (!isset($_GET['id'])) {
    echo "No rescuer ID provided.";
    exit();
}

$id = $_GET['id'];
$message = "";

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $password = isset($_POST['password']) && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        // If password is provided, update with password
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullname, $phone, $password, $id);
    } else {
        // If password is not provided, update without password
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $fullname, $phone, $id);
    }

    if ($stmt->execute()) {
        $message = "Rescuer updated successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Retrieve the rescuer's data from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$rescuer = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Rescuer</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Edit Rescuer</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_rescuer.php?id=<?php echo $id; ?>" onsubmit="return validateEditRescuerForm();">
        <label for="fullname">Name:</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($rescuer['fullname']); ?>" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($rescuer['phone']); ?>" required>

        <label for="password">Password (leave blank if you don't want to change it):</label>
        <input type="password" id="password" name="password" placeholder="Enter new password (optional)">

        <button type="submit">Edit Rescuer</button>
    </form>

    <a href="manage_rescuers.php" class="back-button">Back to Manage Rescuers</a>
    
    <!-- Back to Admin Dashboard Button -->
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
<script src="../scripts/validation.js"></script>
</body>
</html>
