<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['json_file'])) {
    $file = $_FILES['json_file']['tmp_name'];
    $json_data = file_get_contents($file);
    $data = json_decode($json_data, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $conn->begin_transaction();
        try {
            // Insert categories
            foreach ($data['categories'] as $category) {
                $stmt = $conn->prepare("INSERT INTO categories (id, category_name) VALUES (?, ?)
                                        ON DUPLICATE KEY UPDATE category_name=VALUES(category_name)");
                $stmt->bind_param("is", $category['id'], $category['category_name']);
                $stmt->execute();
            }

            // Insert items and item details
            foreach ($data['items'] as $item) {
                $stmt = $conn->prepare("INSERT INTO items (id, name, category_id) VALUES (?, ?, ?)
                                        ON DUPLICATE KEY UPDATE name=VALUES(name), category_id=VALUES(category_id)");
                $stmt->bind_param("isi", $item['id'], $item['name'], $item['category']);
                $stmt->execute();

                $item_id = $item['id'];
                foreach ($item['details'] as $detail) {
                    $stmt = $conn->prepare("INSERT INTO item_details (item_id, detail_name, detail_value) VALUES (?, ?, ?)
                                            ON DUPLICATE KEY UPDATE detail_value=VALUES(detail_value)");
                    $stmt->bind_param("iss", $item_id, $detail['detail_name'], $detail['detail_value']);
                    $stmt->execute();
                }
            }
            $conn->commit();
            $message = "JSON data uploaded and processed successfully!";
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Error: Invalid JSON file.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload JSON</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Upload JSON</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="upload_json.php" method="post" enctype="multipart/form-data">
        <label for="json_file">JSON File:</label>
        <input type="file" id="json_file" name="json_file" required><br>
        <button type="submit">Upload JSON</button>
    </form>
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
</body>
</html>
