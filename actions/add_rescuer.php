<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$message = ""; // To store the success or error message
$message_class = ""; // To assign the CSS class (for error or success messages)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Check if the username already exists
    $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();

    if ($check_username->num_rows > 0) {
        // If username exists, show an error
        $message = " The username '$username' is already taken. Please choose a different username.";
        $message_class = "error-message"; // Set error class for styling
    } else {
        // Insert the new rescuer
        $sql = "INSERT INTO users (fullname, phone, username, password, role, latitude, longitude) 
                VALUES (?, ?, ?, ?, 'rescuer', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdd", $fullname, $phone, $username, $password, $latitude, $longitude);

        if ($stmt->execute()) {
            // Success message
            $message = "Rescuer added successfully!";
            $message_class = "message"; // Set success class for styling
        } else {
            // Error in insertion
            $message = "Error: " . $stmt->error;
            $message_class = "error-message"; // Set error class for styling
        }

        $stmt->close();
    }

    $check_username->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Rescuer</title>
    <link rel="stylesheet" href="../style/styles.css">
</head>
<body>
<div class="container">
    <h2>Add Rescuer</h2>
    <?php if ($message): ?>
        <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    <form id="addRescuerForm" action="add_rescuer.php" method="post" onsubmit="return validateRescuerForm();">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required><br>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required><br>
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="off"><br>
        
        <label for="password">Password:</label>
        <!-- Use "new-password" to ensure browsers do not autofill saved passwords -->
        <input type="password" id="password" name="password" required autocomplete="new-password"><br>
        
        <label for="latitude">Latitude:</label>
        <input type="number" step="any" id="latitude" name="latitude" required><br>
        
        <label for="longitude">Longitude:</label>
        <input type="number" step="any" id="longitude" name="longitude" required><br>

        <button type="submit">Add Rescuer</button>
    </form>
    <a href="../actions/manage_rescuers.php" class="back-button">Back to Manage Rescuers</a>
    <a href="../dashboards/admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>
</div>
<script src="../scripts/validation.js"></script>
<script src="../scripts/validation.js"></script>
</body>
</html>
