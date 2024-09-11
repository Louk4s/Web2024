<?php
session_start();
include '../db_connect.php';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει τον σωστό ρόλο
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

// Έλεγχος αν υπάρχει το user_id στη συνεδρία
if (!isset($_SESSION['user_id'])) {
    die("Το user_id δεν βρέθηκε στη συνεδρία. Σιγουρευτείτε ότι έχετε συνδεθεί σωστά.");
}

$user_id = $_SESSION['user_id'];

// Επεξεργασία AJAX αιτήματος για την ανάκτηση των ειδών
if (isset($_POST['ajax']) && isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
    $items_sql = "SELECT id, name FROM items WHERE category_id = ?";
    $stmt = $conn->prepare($items_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    $items = [];
    while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
    }

    // Αποστολή της απάντησης JSON και σταμάτημα της εκτέλεσης
    echo json_encode($items);
    exit();  // Σταματά την εκτέλεση εδώ για το AJAX αίτημα
}

// Επεξεργασία φόρμας υποβολής αιτήματος
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['ajax'])) {
    $item_id = $_POST['item_id'];
    $people_count = $_POST['people_count'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $status = 'pending';

    if (empty($latitude) || empty($longitude)) {
        die("Σφάλμα: Η τοποθεσία δεν είναι διαθέσιμη.");
    }

    // Προσθήκη του αιτήματος στη βάση δεδομένων
    $sql = "INSERT INTO requests (user_id, item_id, quantity, status, latitude, longitude, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisss", $user_id, $item_id, $people_count, $status, $latitude, $longitude);

    if ($stmt->execute()) {
        // Αποθήκευση επιτυχής, εμφάνιση μηνύματος επιτυχίας
        $success_message = "Το αίτημα σας υποβλήθηκε με επιτυχία!";
    } else {
        // Σφάλμα κατά την υποβολή
        $error_message = "Σφάλμα κατά την υποβολή του αιτήματος: " . $stmt->error;
    }

    $stmt->close();
}

// Ανάκτηση κατηγοριών για το dropdown
$categories_sql = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_sql);
if (!$categories_result) {
    die("Σφάλμα κατά την ανάκτηση των κατηγοριών: " . $conn->error);
}

$conn->close(); // Κλείνουμε τη σύνδεση με τη βάση δεδομένων στο τέλος του κώδικα
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Υποβολή Αιτήματος Βοήθειας</title>
    <script>
        // Λειτουργία για λήψη τοποθεσίας χρήστη
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation δεν υποστηρίζεται από το πρόγραμμα περιήγησης.");
            }
        }

        function showPosition(position) {
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("longitude").value = position.coords.longitude;
        }

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

        // AJAX για τη δυναμική φόρτωση των ειδών ανά κατηγορία
        function loadItems(categoryId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_request.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    var items = JSON.parse(this.responseText);
                    var itemSelect = document.getElementById('item_id');
                    itemSelect.innerHTML = '<option value="">Επιλέξτε Είδος</option>';
                    items.forEach(function(item) {
                        var option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        itemSelect.appendChild(option);
                    });
                }
            };
            xhr.send('ajax=1&category_id=' + categoryId);
        }

        // Φόρτωση τοποθεσίας όταν φορτώνει η σελίδα
        window.onload = function() {
            getLocation();
        };
    </script>
</head>
<body>
    <h1>Request assistance!</h1>

    <!-- Εμφάνιση μηνύματος επιτυχίας ή αποτυχίας -->
    <?php if (isset($success_message)): ?>
        <p style="color:green;"><?php echo $success_message; ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <p style="color:red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <form action="add_request.php" method="POST">
        <!-- Επιλογή Κατηγορίας -->
        <label for="category_id">Choose Category:</label>
        <select id="category_id" name="category_id" onchange="loadItems(this.value)" required>
            <option value="">Categories</option>
            <?php while ($category = $categories_result->fetch_assoc()): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Επιλογή Είδους -->
        <label for="item_id">Choose item:</label>
        <select name="item_id" id="item_id" required>
            <option value="">Items</option>
        </select>

        <!-- Πλήθος Ατόμων -->
        <label for="people_count">Number of people:</label>
        <input type="number" name="people_count" id="people_count" required>

        <!-- Κρυφά πεδία για γεωγραφικό πλάτος και μήκος -->
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <button type="submit">Submit request</button>
    </form>
</body>
</html>


