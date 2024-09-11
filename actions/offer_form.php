<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch announcement details based on the selected announcement_id
$announcement_id = isset($_GET['announcement_id']) ? intval($_GET['announcement_id']) : 0;
$selected_items = [];

if ($announcement_id) {
    $announcement_result = $conn->query("SELECT item_ids FROM announcements WHERE id = $announcement_id");
    if ($announcement_result && $announcement_result->num_rows > 0) {
        $row = $announcement_result->fetch_assoc();
        $selected_items = explode(',', $row['item_ids']); // The default items from the announcement
    }
}

// Fetch all items and categories from the inventory
$categories_result = $conn->query("SELECT id, category_name FROM categories");
$items_result = $conn->query("SELECT items.id, items.name, items.category_id FROM items");

$categories = [];
$items = [];

if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($items_result && $items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming you store user ID in session
    $offer_items = isset($_POST['items']) ? $_POST['items'] : [];
    $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];
    $status = 'pending'; // Default status for new offers

    if (!empty($offer_items) && !empty($quantities)) {
        $items_and_quantities = [];
        foreach ($offer_items as $index => $item_id) {
            $items_and_quantities[] = $item_id . ':' . intval($quantities[$index]); // Store item_id:quantity
        }
        $item_ids = implode(',', $items_and_quantities);
        $stmt = $conn->prepare("INSERT INTO offers (user_id, item_ids, status) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $user_id, $item_ids, $status);
        $stmt->execute();
        $_SESSION['success_message'] = 'Offer successfully created.';
        header("Location: offer_form.php?announcement_id=$announcement_id");
        exit();
    } else {
        $error_message = "Please select at least one item and provide quantity.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Offer</title>
    <link rel="stylesheet" href="../style/styles.css">

    <!-- jQuery and jQuery UI for autocomplete -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<div class="container">
    <h2>Create Offer</h2>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form method="POST" action="offer_form.php?announcement_id=<?php echo $announcement_id; ?>">
        <!-- Category Selection -->
        <label for="category">Select Category:</label>
        <input type="text" id="category" name="category" placeholder="Search Category" 
               data-categories='<?php echo json_encode($categories); ?>'>

        <!-- Item Selection -->
        <label for="items">Select Item:</label>
        <input type="text" id="items_search" placeholder="Search Item">
        <select name="items[]" id="items" multiple="multiple" style="width: 100%;" 
                data-items='<?php echo json_encode($items); ?>'>
            <?php foreach ($items as $item): ?>
                <option value="<?php echo $item['id']; ?>" <?php echo in_array($item['id'], $selected_items) ? 'selected' : ''; ?>>
                    <?php echo $item['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Quantity Input -->
        <label for="quantities[]">Select Quantity:</label>
        <input type="number" name="quantities[]" min="1" placeholder="Enter Quantity">

        <button type="submit">Submit Offer</button>
    </form>

    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Dashboard</a>
</div>

<!-- jQuery and jQuery UI -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- Custom JS for the offer form -->
<script src="../scripts/offer_form.js"></script>
</body>
</html>

