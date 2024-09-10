$(document).ready(function() {
    // Initialize Select2 για την επιλογή κατηγορίας
    $('#category_id').select2({
        placeholder: "Select Category",
        allowClear: true
    });

    // Initialize Select2 για την επιλογή item
    $('#item_id').select2({
        placeholder: "Select Item",
        allowClear: true
    });

    // Όταν αλλάζει η κατηγορία
    $('#category_id').on('change', function() {
        var categoryId = $(this).val(); // Παίρνουμε την επιλεγμένη κατηγορία

        // Καθαρισμός του dropdown των items
        $('#item_id').empty();
        $('#item_id').append('<option value="">-- Select Item --</option>');

        if (categoryId) {
            $.ajax({
                url: '../actions/get_items_by_category.php', // Βεβαιώσου ότι η διαδρομή είναι σωστή
                type: 'GET',
                data: {category_id: categoryId},
                dataType: 'json',
                success: function(items) {
                    if (items.length > 0) {
                        // Γέμισμα του dropdown με τα items
                        $.each(items, function(key, item) {
                            $('#item_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    } else {
                        console.log("No items found for this category.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + error);
                    alert('Error fetching items.');
                }
            });
        }
    });
});

