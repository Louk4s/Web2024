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

