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
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, username = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $fullname, $phone, $username, $password, $id);

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
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="edit_rescuer.php?id=<?php echo $id; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $rescuer['id']; ?>">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo $rescuer['fullname']; ?>" required><br>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo $rescuer['phone']; ?>" required><br>
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $rescuer['username']; ?>" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <button type="submit">Update Rescuer</button>
    </form>
    <a href="manage_rescuers.php" class="back-button">Back to Manage Rescuers</a>
</div>
</body>
</html>
