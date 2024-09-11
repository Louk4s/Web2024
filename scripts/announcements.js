document.addEventListener("DOMContentLoaded", function () { 
    // Check if category and items exist before adding event listeners
    const categorySelect = document.getElementById('category');
    const itemSelect = document.getElementById('items');

    if (categorySelect && itemSelect) {
        categorySelect.addEventListener('change', function () {
            const selectedCategories = Array.from(categorySelect.selectedOptions).map(option => option.value);

            if (selectedCategories.length > 0) {
                fetchItems(selectedCategories);
            } else {
                itemSelect.innerHTML = '';  // Clear the items
                itemSelect.disabled = true;
            }
        });

        function fetchItems(categories) {
            const query = categories.join(',');  // Join selected category IDs into a string

            fetch(`get_items_by_category.php?category_ids=${query}`)
                .then(response => response.json())
                .then(data => {
                    itemSelect.innerHTML = ''; // Clear the items list
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        itemSelect.appendChild(option);
                    });
                    itemSelect.disabled = false; // Enable the items list
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                });
        }
    }

    // Check if delete buttons exist before adding event listeners
    const deleteButtons = document.querySelectorAll('.delete-button');

    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const announcementId = this.getAttribute('data-id');

                if (confirm("Are you sure you want to delete this announcement?")) {
                    deleteAnnouncement(announcementId);
                }
            });
        });
    }

    function deleteAnnouncement(id) {
        fetch('delete_announcement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Announcement deleted successfully.");
                // Remove the row from the table
                document.getElementById(`announcement-row-${id}`).remove();
            } else {
                alert("Failed to delete the announcement. Please try again.");
            }
        })
        .catch(error => {
            console.error('Error deleting announcement:', error);
            alert("An error occurred while trying to delete the announcement.");
        });
    }
});



