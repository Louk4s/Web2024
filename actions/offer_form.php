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

// Fetch details for the selected items
$items = [];
if (!empty($selected_items)) {
    $item_ids_sql = implode(',', array_map('intval', $selected_items));
    $items_result = $conn->query("SELECT id, name FROM items WHERE id IN ($item_ids_sql)");

    if ($items_result && $items_result->num_rows > 0) {
        while ($row = $items_result->fetch_assoc()) {
            $items[] = $row;
        }
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

        // Fetch user geolocation from user table
        $latitude = '';
        $longitude = '';
        $user_query = $conn->query("SELECT latitude, longitude FROM users WHERE id = $user_id");
        if ($user_query && $user_query->num_rows > 0) {
            $user_row = $user_query->fetch_assoc();
            $latitude = $user_row['latitude'];
            $longitude = $user_row['longitude'];
        }

        // Insert offer into the offers table
        $stmt = $conn->prepare("INSERT INTO offers (user_id, item_ids, status, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $user_id, $item_ids, $status, $latitude, $longitude);
        $stmt->execute();

        // Now insert the task into the tasks table
        $offer_id = $stmt->insert_id; // Get the offer ID
        $task_type = 'offer';
        $task_status = 'pending';
        $created_at = date('Y-m-d H:i:s');
        $task_stmt = $conn->prepare("INSERT INTO tasks (task_type, offer_id, status, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $task_stmt->bind_param('sissss', $task_type, $offer_id, $task_status, $latitude, $longitude, $created_at);
        $task_stmt->execute();

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
        <!-- Item Selection with Quantity Input -->
        <label for="items">Select Items and Quantities:</label>
        <table class="scrollable-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th class="item-quantity">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="items[]" value="<?php echo $item['id']; ?>" checked>
                                <?php echo $item['name']; ?>
                            </td>
                            <td class="item-quantity">
                                <input type="number" name="quantities[]" min="1" placeholder="Quantity" style="width: 60px;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2">No items available for this announcement.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button type="submit">Submit Offer</button>
    </form>

    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
</body>
</html>
