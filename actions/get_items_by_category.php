<?php
include '../db_connect.php'; // Σιγουρέψου ότι η διαδρομή του αρχείου σύνδεσης είναι σωστή

if (isset($_GET['category_ids'])) {
    $category_ids = $_GET['category_ids']; // Αποκτάμε τα IDs των κατηγοριών ως string

    // Ασφαλής μετατροπή σε ακέραιους και διαχωρισμός των IDs
    $category_ids_array = array_map('intval', explode(',', $category_ids));

    if (!empty($category_ids_array)) {
        // Ερώτημα για την ανάκτηση των items που ανήκουν στις κατηγορίες
        $placeholders = implode(',', array_fill(0, count($category_ids_array), '?'));
        $stmt = $conn->prepare("SELECT id, name FROM items WHERE category_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($category_ids_array)), ...$category_ids_array);
        $stmt->execute();
        $items_result = $stmt->get_result();

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
        // Μη έγκυρες κατηγορίες
        http_response_code(400);
        echo json_encode(["error" => "Invalid category IDs"]);
    }
} else {
    // Δεν παρέχονται category_ids στο αίτημα
    http_response_code(400);
    echo json_encode(["error" => "Missing category IDs"]);
}

$conn->close();
?>

