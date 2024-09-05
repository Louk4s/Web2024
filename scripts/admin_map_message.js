// Show message box if there is a message
var messageBox = document.getElementById('messageBox');
if (messageBox) {
    messageBox.style.display = 'block';
    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        messageBox.style.display = 'none';
    }, 5000);
}
