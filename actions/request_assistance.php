<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch categories and items for the dropdowns
$categories_result = $conn->query("SELECT id, category_name FROM categories");
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$items_result = $conn->query("SELECT id, name FROM items");
$items = [];
if ($items_result && $items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Assistance</title>

    <!-- Reference to the external CSS file -->
    <link rel="stylesheet" href="../style/request_assistance.css">

    <!-- Reference to Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
    <h2>Request Assistance</h2>

    <!-- Form to create a new request -->
    <form action="../actions/add_request_action.php" method="POST">
        <div class="form-group">
            <label for="category_id">Select Category:</label>
            <select id="category_id" class="form-control" style="width: 100%;">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="item_id">Select Item:</label>
            <select name="item_id" id="item_id" class="form-control" style="width: 100%;">
                <option value="">-- Select Item --</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="people_count">Number of People:</label>
            <input type="number" name="people_count" id="people_count" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>

    <!-- Back to Dashboard Button -->
    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Dashboard</a>
</div>

<!-- Reference to jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Reference to Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Reference to external JS file for form behavior -->
<script src="../scripts/request_assistance.js"></script>
</body>
</html>

