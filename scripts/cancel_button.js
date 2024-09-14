$(document).ready(function() {
    $('.cancel-btn').click(function() {
        var requestId = $(this).data('id');
        if (confirm('Are you sure you want to cancel this request?')) {
            $.post('cancel_request.php', { request_id: requestId }, function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    // Display success message
                    $('#message').html('<div class="success-message">' + data.message + '</div>');
                    // Remove the canceled request row from the table
                    $('#request-row-' + requestId).remove();
                } else {
                    // Display error message if cancellation failed
                    $('#message').html('<div class="error-message">' + data.message + '</div>');
                }
            });
        }
    });
});


