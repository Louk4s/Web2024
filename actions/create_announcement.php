<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch all categories from the database
$categories_result = $conn->query("SELECT id, category_name FROM categories");
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_items = isset($_POST['items']) ? $_POST['items'] : [];
    $description = $_POST['description'];

    if (!empty($selected_items) && !empty($description)) {
        $item_ids = implode(',', $selected_items);
        $stmt = $conn->prepare("INSERT INTO announcements (item_ids, description) VALUES (?, ?)");
        $stmt->bind_param('ss', $item_ids, $description);
        $stmt->execute();
        $_SESSION['success_message'] = 'Announcement successfully created.';
        header("Location: create_announcement.php");
        exit();
    } else {
        $error_message = "Please select items and provide a description.";
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Create Announcement</h2>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form method="POST" action="create_announcement.php">
        <!-- Τροποποίηση για πολλαπλή επιλογή κατηγοριών -->
        <label for="category">Select Categories (Press Ctrl to select multiple):</label>
        <select id="category" name="categories[]" multiple="multiple" style="width: 100%;">
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Τα items θα φορτώνονται δυναμικά με βάση τις επιλεγμένες κατηγορίες -->
        <label for="items">Select Items (Press Ctrl to select multiple):</label>
        <select name="items[]" id="items" multiple="multiple" style="width: 100%;" disabled>
            <option value="">Select an Item</option>
        </select>

        <label for="description">Announcement Description:</label>
        <textarea name="description" id="description" rows="4" style="width: 100%;"></textarea>

        <button type="submit">Create Announcement</button>
    </form>

    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
<link rel="stylesheet" href="../style/styles.css">
<!-- Custom JS for form interaction -->
<script src="../scripts/announcements.js"></script>
</body>
</html>
