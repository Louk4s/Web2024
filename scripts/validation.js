function validateForm() {
    const fullname = document.getElementById('fullname').value;
    const phone = document.getElementById('phone').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;

    // Βασική επιβεβαίωση για τα πεδία
    if (!fullname || !phone || !username || !password || !latitude || !longitude) {
        alert('All the filds are mandatory.');
        return false;
    }

    // Επιβεβαίωση αν ο αριθμός τηλεφώνου έχει το σωστό format
    if (!/^69[0-9]{8}$/.test(phone)) {
        alert('The phone number must start with 69 and must have 10 digits.');
        return false;
    }
    
    // Έλεγχος για το username μέσω AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'register.php', true);  // Χρησιμοποιούμε το ίδιο register.php για τον έλεγχο
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // Ακούμε για το αποτέλεσμα της AJAX αίτησης
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            
            if (response.status === 'error') {
                // Εμφανίζουμε μήνυμα αν το username υπάρχει ήδη
                alert(response.message);
                return false;  // Σταματάμε την υποβολή της φόρμας
            } else {
                // Συνεχίζουμε με την υποβολή της φόρμας αν το username είναι διαθέσιμο
                document.getElementById('registerForm').submit();
            }
        }
    };

    // Στέλνουμε τα δεδομένα στη register.php για να ελέγξουμε το username
    xhr.send('username=' + encodeURIComponent(username) + 
             '&fullname=' + encodeURIComponent(fullname) + 
             '&phone=' + encodeURIComponent(phone) + 
             '&password=' + encodeURIComponent(password) +
             '&latitude=' + encodeURIComponent(latitude) + 
             '&longitude=' + encodeURIComponent(longitude));

    // Επιστρέφουμε false για να περιμένουμε την απάντηση της AJAX αίτησης
    return false;
}

function validateAddItemForm() {
    const itemName = document.getElementById('item_name').value;
    const categoryId = document.getElementById('category_id').value;
    const quantity = document.getElementById('quantity').value;
    const detailName = document.getElementById('detail_name').value;
    const detailValue = document.getElementById('detail_value').value;

    // Check if all fields are filled
    if (!itemName || !categoryId || !quantity || !detailName || !detailValue) {
        alert('All fields are mandatory.');
        return false;
    }

    // Check if quantity is a valid number
    if (quantity <= 0) {
        alert('Quantity must be greater than zero.');
        return false;
    }

    return true;
}


function validateEditItemForm() {
    const itemName = document.getElementById('item_name').value;
    const categoryId = parseInt(document.getElementById('category_id').value);  // Ensure categoryId is an integer

    // Check if item name and category ID are filled
    if (!itemName || !categoryId) {
        alert('Item Name and Category ID are mandatory.');
        return false;
    }

    // Check if the category ID is valid
    if (!validCategoryIds.includes(categoryId)) {
        alert('This category ID does not exist!');
        return false;
    }

    return true;
}

function validateRescuerForm() {
    const fullname = document.getElementById('fullname').value;
    const phone = document.getElementById('phone').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;

    // Basic validation for required fields
    if (!fullname || !phone || !username || !password || !latitude || !longitude) {
        displayErrorMessage('All fields are mandatory.');
        return false;
    }

    // Validation for phone number format (must start with "69" and have 10 digits)
    if (!/^69[0-9]{8}$/.test(phone)) {
        displayErrorMessage('The phone number must start with 69 and must have 10 digits.');
        return false;
    }

    // If validation passes, allow the form to submit
    return true;
}

function validateEditRescuerForm() {
    const phone = document.getElementById('phone').value;
    const fullname = document.getElementById('fullname').value;
    
    // Basic validation for required fields
    if (!fullname || !phone) {
        displayErrorMessage('All fields are mandatory.');
        return false;
    }

    // Validation for phone number format (must start with "69" and have 10 digits)
    if (!/^69[0-9]{8}$/.test(phone)) {
        displayErrorMessage('The phone number must start with 69 and must have 10 digits.');
        return false;
    }

    // If validation passes, allow the form to submit
    return true;
}

function displayErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;

    // Check if an error message already exists, and replace it
    const container = document.querySelector('.container');
    const existingError = document.querySelector('.error-message');
    if (existingError) {
        container.removeChild(existingError);
    }

    container.insertBefore(errorDiv, container.firstChild);
}
