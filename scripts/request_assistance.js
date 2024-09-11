$(document).ready(function() {
    // Initialize Select2 for category selection
    $('#category_id').select2({
        placeholder: "Select Category",
        allowClear: true
    });

    // Initialize Select2 for item selection
    $('#item_id').select2({
        placeholder: "Select Item",
        allowClear: true
    });

    // When the category is changed
    $('#category_id').on('change', function() {
        var categoryId = $(this).val(); // Get the selected category

        // Clear the items dropdown
        $('#item_id').empty();
        $('#item_id').append('<option value="">-- Select Item --</option>');

        if (categoryId) {
            $.ajax({
                url: '../actions/get_items_by_category.php', // Ensure the correct path
                type: 'GET',
                data: { category_id: categoryId }, // Send singular category_id
                dataType: 'json',
                success: function(items) {
                    if (items.length > 0) {
                        // Populate the items dropdown with the retrieved items
                        $.each(items, function(key, item) {
                            $('#item_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    } else {
                        alert("No items found for this category.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + error);
                    alert('Error fetching items. Please try again later.');
                }
            });
        }
    });
});
