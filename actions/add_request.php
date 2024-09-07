<?php
session_start();

// Συμπερίληψη της σύνδεσης στη βάση δεδομένων
include '../db_connect.php';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει τον σωστό ρόλο
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

// Παίρνουμε το user_id από το session που αντιστοιχεί στον χρήστη που έχει κάνει login
$user_id = $_SESSION['user_id']; 

// Ανάκτηση κατηγοριών από τη βάση δεδομένων
$categories_sql = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_sql);
if (!$categories_result) {
    die("Σφάλμα κατά την ανάκτηση των κατηγοριών: " . $conn->error);
}

// Ανάκτηση ειδών από τη βάση δεδομένων
$items_sql = "SELECT id, name, category_id FROM items";
$items_result = $conn->query($items_sql);
if (!$items_result) {
    die("Σφάλμα κατά την ανάκτηση των ειδών: " . $conn->error);
}

// Αν το αίτημα είναι POST, τότε επεξεργαζόμαστε τη φόρμα
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id']; // Είδος που επιλέγει ο πολίτης
    $people_count = $_POST['people_count']; // Πλήθος ατόμων
    $latitude = $_POST['latitude']; // Γεωγραφικό πλάτος
    $longitude = $_POST['longitude']; // Γεωγραφικό μήκος
    $status = 'pending'; // Default κατάσταση για τα αιτήματα

    // Ελέγχουμε ότι έχουμε λάβει σωστά δεδομένα από τη φόρμα
    if (empty($latitude) || empty($longitude)) {
        die("Σφάλμα: Η τοποθεσία δεν είναι διαθέσιμη.");
    }

    // Προσθήκη του αιτήματος στη βάση δεδομένων
    $sql = "INSERT INTO requests (user_id, item_id, quantity, status, latitude, longitude, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisss", $user_id, $item_id, $people_count, $status, $latitude, $longitude);

    if ($stmt->execute()) {
        echo "Το αίτημα σας υποβλήθηκε με επιτυχία!";
    } else {
        echo "Σφάλμα κατά την υποβολή του αιτήματος: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Αίτημα Βοήθειας</title>
    <script>
        // Λειτουργία για λήψη τοποθεσίας χρήστη
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation δεν υποστηρίζεται από το πρόγραμμα περιήγησης.");
            }
        }

        // Εισαγωγή των συντεταγμένων στα κρυφά πεδία της φόρμας
        function showPosition(position) {
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("longitude").value = position.coords.longitude;

            console.log("Latitude: " + position.coords.latitude);
            console.log("Longitude: " + position.coords.longitude);
        }

        // Σε περίπτωση σφάλματος στην λήψη της τοποθεσίας
        function showError(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alert("Η πρόσβαση στην τοποθεσία απορρίφθηκε.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Η τοποθεσία δεν είναι διαθέσιμη.");
                    break;
                case error.TIMEOUT:
                    alert("Η λήψη της τοποθεσίας καθυστέρησε.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("Άγνωστο σφάλμα.");
                    break;
            }
        }

        // Φόρτωση τοποθεσίας όταν φορτώνει η σελίδα
        window.onload = function() {
            getLocation();
        };
    </script>
</head>
<body>
    <h1>Υποβολή Αιτήματος Βοήθειας</h1>
    
    <form action="add_request.php" method="POST">
        <!-- Επιλογή Κατηγορίας -->
        <label for="category">Επιλέξτε Κατηγορία:</label>
        <select id="category" name="category">
            <option value="">Επιλέξτε</option>
            <?php while ($category = $categories_result->fetch_assoc()): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Επιλογή Είδους -->
        <label for="item_id">Επιλέξτε Είδος:</label>
        <select name="item_id" id="item_id">
            <option value="">Επιλέξτε Είδος</option>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Πλήθος Ατόμων -->
        <label for="people_count">Πλήθος Ατόμων:</label>
        <input type="number" name="people_count" id="people_count" required>

        <!-- Κρυφά πεδία για γεωγραφικό πλάτος και μήκος -->
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <button type="submit">Send Request</button>
    </form>
</body>
</html>
