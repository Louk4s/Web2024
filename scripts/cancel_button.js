$(document).ready(function() {
    $('.cancel-btn').click(function() {
        var requestId = $(this).data('id');
        if (confirm('Are you sure you want to cancel this request?')) {
            $.post('cancel_request.php', { request_id: requestId }, function(response) {
                var data = JSON.parse(response);

                if (data.success) {
                    // Display success message using the .message class
                    $('#message').html('<div class="message">' + data.message + '</div>');

                    // Remove the canceled request row from the table
                    $('#request-row-' + requestId).remove();
                } else {
                    // Display error message if cancellation failed
                    $('#message').html('<div class="message error-message">' + data.message + '</div>');
                }
            }).fail(function() {
                $('#message').html('<div class="message error-message">An error occurred while processing your request.</div>');
            });
        }
    });
});



