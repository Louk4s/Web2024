<?php
session_start();
include 'db_connect.php';  // Ensure correct database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'citizen';  // Assuming all registrations are for citizens
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Validate phone number format
    if (!preg_match("/^69[0-9]{8}$/", $phone)) {
        $error = "The phone number must start with 69 and have 10 digits.";
    } else {
        // Check if username already exists
        $check_username_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_username_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Username already exists
            $error = "The username is already taken. Please choose another.";
        } else {
            // Username is available, proceed with insertion
            $stmt = $conn->prepare("INSERT INTO users (fullname, phone, username, password, role, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssd", $fullname, $phone, $username, $password, $role, $latitude, $longitude);

            if ($stmt->execute()) {
                // Successful registration, show success message
                $success = "Registration successful! You will be redirected to the login page in 3 seconds.";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 3000); // 3 seconds delay
                      </script>";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }

        // Close statement
        $stmt->close();
    }

    // Close database connection
    $conn->close();
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
    <?php elseif (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" pattern="69[0-9]{8}" title="The phone number must start with 69 and have 10 digits" required>

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
    <a href="login.php" class="back-button">Back to login</a>
</div>
</body>
</html>
