<?php
session_start();

// Ensure only logged-in citizens can view this page
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch announcements from the database
$announcements = [];
$announcements_result = $conn->query("SELECT id, item_ids, description, created_at FROM announcements ORDER BY created_at DESC");

if ($announcements_result && $announcements_result->num_rows > 0) {
    while ($row = $announcements_result->fetch_assoc()) {
        // Explode the item_ids string into an array
        $item_ids = explode(',', $row['item_ids']);
        
        // Fetch the item names corresponding to the item_ids
        $item_names = [];
        if (!empty($item_ids)) {
            $item_ids_sql = implode(',', array_map('intval', $item_ids)); // Sanitize the item_ids for SQL
            $items_result = $conn->query("SELECT name FROM items WHERE id IN ($item_ids_sql)");
            
            if ($items_result && $items_result->num_rows > 0) {
                while ($item_row = $items_result->fetch_assoc()) {
                    $item_names[] = $item_row['name'];
                }
            }
        }
        
        // Add announcement data along with resolved item names
        $announcements[] = [
            'id' => $row['id'],
            'description' => $row['description'],
            'created_at' => $row['created_at'],
            'items' => implode(', ', $item_names) // Join item names into a comma-separated string
        ];
    }
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Announcements</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>View Announcements</h2>

    <!-- Display announcements in a table -->
    <?php if (count($announcements) > 0): ?>
        <table>
            <tr>
                <th>Items</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th> <!-- Added for the Offer button -->
            </tr>
            <?php foreach ($announcements as $announcement): ?>
                <tr>
                    <td><?php echo htmlspecialchars($announcement['items']); ?></td>
                    <td><?php echo htmlspecialchars($announcement['description']); ?></td>
                    <td><?php echo htmlspecialchars($announcement['created_at']); ?></td>
                    <td>
                        <!-- Offer Button -->
                        <a href="offer_form.php?announcement_id=<?php echo $announcement['id']; ?>" class="button">Offer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No announcements to display.</p>
    <?php endif; ?>

    <!-- Back to Citizen Dashboard Button -->
    <a href="../dashboards/citizen_dashboard.php" class="back-button">Back to Citizen Dashboard</a>

</div>
</body>
</html>
