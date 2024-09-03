<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];

    $sql = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);

    try {
        if ($stmt->execute()) {
            $message = "Category added successfully!";
        }
    } catch (mysqli_sql_exception $e) {
        $message = "Error: " . $e->getMessage();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Add Category</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="add_category.php" method="post">
        <label for="category">Category Name:</label>
        <input type="text" id="category" name="category" required><br>
        <button type="submit">Add Category</button>
    </form>
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
</div>
</body>
</html>
