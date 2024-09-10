$(document).ready(function() {
    // Initialize Select2 for Category dropdown
    $('#category_id').select2({
        placeholder: "Select Category",
        allowClear: true
    });

    // Initialize Select2 for Item dropdown
    $('#item_id').select2({
        placeholder: "Select Item",
        allowClear: true
    });
});
