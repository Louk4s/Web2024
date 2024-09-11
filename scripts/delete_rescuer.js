document.addEventListener("DOMContentLoaded", function() {
    const deleteButtons = document.querySelectorAll(".delete-rescuer");

    deleteButtons.forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            const rescuerId = this.getAttribute("data-id");

            if (confirm("Are you sure you want to delete this rescuer?")) {
                fetch('delete_rescuer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: rescuerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Rescuer deleted successfully.");
                        location.reload(); // Reload the page to show the updated list
                    } else {
                        alert("Failed to delete rescuer.");
                    }
                });
            }
        });
    });
});
