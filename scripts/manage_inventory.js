// Ενεργοποίηση Select2 για πολλαπλές επιλογές
$(document).ready(function() {
    $('#category_id').select2({
        placeholder: "Select Categories",  // Προσθήκη placeholder
        allowClear: true                   // Επιτρέπει την εκκαθάριση των επιλογών
    });
});

// JavaScript to scroll to the top
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
