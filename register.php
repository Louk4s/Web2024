<?php
session_start();
include 'db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'citizen'; // Assuming all registrations here are for citizens
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Έλεγχος αν το τηλέφωνο είναι έγκυρο
    if (!preg_match("/^69[0-9]{8}$/", $phone)) {
        $error = "Το τηλέφωνο πρέπει να ξεκινάει με 69 και να έχει 10 ψηφία.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, phone, username, password, role, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssd", $fullname, $phone, $username, $password, $role, $latitude, $longitude);

        if ($stmt->execute()) {
            header("Location: dashboards/citizen_dashboard.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style/styles.css">
    <script src="scripts/location.js"></script>
</head>
<body onload="getLocation()">
<div class="container">
    <h2>Register</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" pattern="69[0-9]{8}" title="Το τηλέφωνο πρέπει να ξεκινάει με 69 και να έχει 10 ψηφία" required>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude" required>

        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude" required>

        <button type="submit">Register</button>
    </form>
    <a href="login.php">Back to Login</a>
</div>
</body>
</html>

