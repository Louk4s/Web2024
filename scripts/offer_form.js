$(document).ready(function() {
    // Get categories and items data from HTML attributes
    var categories = JSON.parse($('#category').attr('data-categories'));
    var items = JSON.parse($('#items').attr('data-items'));

    // Autocomplete for category
    $("#category").autocomplete({
        source: categories.map(c => c.category_name)
    });

    // Autocomplete for item search
    $("#items_search").autocomplete({
        source: items.map(i => i.name),
        select: function(event, ui) {
            // Auto-select the item in the select dropdown when chosen via autocomplete
            var itemId = items.find(i => i.name === ui.item.value).id;
            var $itemsSelect = $("#items");

            // Select the item in the dropdown if it exists
            if ($itemsSelect.find("option[value='" + itemId + "']").length) {
                $itemsSelect.val(itemId).change();
            }
        }
    });
});

