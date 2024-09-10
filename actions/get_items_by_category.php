<?php
include '../db_connect.php'; // Σιγουρέψου ότι η διαδρομή του αρχείου σύνδεσης είναι σωστή

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']); // Ασφαλής μετατροπή του category_id σε ακέραιο

    if ($category_id > 0) {
        // Ερώτημα για την ανάκτηση των items που ανήκουν στην κατηγορία
        $items_result = $conn->query("SELECT id, name FROM items WHERE category_id = $category_id");

        if ($items_result && $items_result->num_rows > 0) {
            $items = [];
            while ($row = $items_result->fetch_assoc()) {
                $items[] = $row;
            }
            // Επιστροφή των items σε μορφή JSON
            echo json_encode($items);
        } else {
            // Δεν βρέθηκαν items
            echo json_encode([]);
        }
    } else {
        // Μη έγκυρη κατηγορία
        http_response_code(400);
        echo json_encode(["error" => "Invalid category ID"]);
    }
} else {
    // Δεν παρέχεται category_id στο αίτημα
    http_response_code(400);
    echo json_encode(["error" => "Missing category ID"]);
}

$conn->close();
?>
