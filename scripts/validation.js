function validateForm() {
    const fullname = document.getElementById('fullname').value;
    const phone = document.getElementById('phone').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;

    if (!fullname || !phone || !username || !password || !role) {
        alert('All fields are required.');
        return false;
    }

    if (!/^\d{10}$/.test(phone)) {
        alert('Phone number must be 10 digits.');
        return false;
    }

    return true;
}
