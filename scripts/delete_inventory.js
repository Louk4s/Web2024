// Ενεργοποίηση Select2 για πολλαπλές επιλογές
$(document).ready(function() {
    $('#category_id').select2({
        placeholder: "Select Categories",  // Προσθήκη placeholder
        allowClear: true                   // Επιτρέπει την εκκαθάριση των επιλογών
    });

    // Validate form submission for Load and Unload actions
    $('#loadForm').on('submit', function() {
        return validateSelection('loadSelect', 'load');
    });

    $('#unloadForm').on('submit', function() {
        return validateSelection('unloadSelect', 'unload');
    });

    // Filter items for Load action
    $('#loadSearch').on('keyup', function() {
        filterItems('loadSelect', 'loadSearch');
    });

    // Filter items for Unload action
    $('#unloadSearch').on('keyup', function() {
        filterItems('unloadSelect', 'unloadSearch');
    });
});

// JavaScript to scroll to the top
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Validate that at least one item is selected for load/unload
function validateSelection(selectId, action) {
    const selectedOptions = document.getElementById(selectId).selectedOptions;
    if (selectedOptions.length === 0) {
        alert('Please select at least one item to ' + action + '.');
        return false;
    }
    return true;
}

// Filter the items in the select dropdown based on search input
function filterItems(selectId, searchInputId) {
    const searchInput = document.getElementById(searchInputId).value.toLowerCase();
    const selectElement = document.getElementById(selectId);
    const options = selectElement.options;

    for (let i = 0; i < options.length; i++) {
        const optionText = options[i].text.toLowerCase();
        if (optionText.includes(searchInput)) {
            options[i].style.display = '';
        } else {
            options[i].style.display = 'none';
        }
    }
}
