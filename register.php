<?php
session_start();
include 'db_connect.php';  // Βεβαιώσου ότι το αρχείο περιέχει την σωστή σύνδεση με τη βάση δεδομένων

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'citizen'; // Assuming all registrations here are for citizens
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // validation/checking of the phone
    if (!preg_match("/^69[0-9]{8}$/", $phone)) {
        $error = "Το τηλέφωνο πρέπει να ξεκινάει με 69 και να έχει 10 ψηφία.";
    } else {
        // Έλεγχος αν το username υπάρχει ήδη
        $check_username_query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_username_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Το username υπάρχει ήδη
            $error = "Το username χρησιμοποιείται ήδη. Παρακαλώ διαλέξτε άλλο.";
        } else {
            // Το username είναι διαθέσιμο, προχωράμε με την εισαγωγή
            $stmt = $conn->prepare("INSERT INTO users (fullname, phone, username, password, role, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssd", $fullname, $phone, $username, $password, $role, $latitude, $longitude);

            if ($stmt->execute()) {
                // Αν η εγγραφή ήταν επιτυχής, κάνουμε redirect στο dashboard
                header("Location: dashboards/citizen_dashboard.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }

        // Κλείσιμο των δηλώσεων
        $stmt->close();
    }

    // Κλείνουμε τη σύνδεση με τη βάση δεδομένων
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
    <a href="login.php" class="back-button">Back to login</a></p>
</div>
</body>
</html>


