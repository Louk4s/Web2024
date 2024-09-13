document.addEventListener("DOMContentLoaded", function () {
    const categorySelect = document.getElementById('category');
    const itemSelect = document.getElementById('items');

    // Existing functionality: Fetch items based on selected categories
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
            const query = categories.join(',');  // Join selected category IDs into a comma-separated string

            fetch(`../actions/get_items_by_category.php?category_ids=${query}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to fetch items: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = ''; // Clear the items list
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            itemSelect.appendChild(option);
                        });
                        itemSelect.disabled = false; // Enable the items list
                    } else {
                        itemSelect.disabled = true; // Disable if no items
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                });
        }
    }

    // New functionality: Handle the deletion of announcements
    const deleteButtons = document.querySelectorAll('.delete-button');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const announcementId = this.getAttribute('data-id');
            console.log('Delete button clicked for announcement ID:', announcementId);

            if (confirm('Are you sure you want to delete this announcement?')) {
                deleteAnnouncement(announcementId);
            }
        });
    });

    function deleteAnnouncement(announcementId) {
        console.log('Sending request to delete announcement with ID:', announcementId);

        fetch('../actions/delete_announcement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `announcement_id=${encodeURIComponent(announcementId)}`
        })
        .then(response => {
            console.log('Received response from server:', response);
            return response.json();
        })
        .then(data => {
            console.log('Parsed response:', data);

            if (data.success) {
                // Remove the corresponding row from the table
                const row = document.getElementById(`announcement-row-${announcementId}`);
                if (row) {
                    row.remove();
                }
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting announcement:', error);
            alert('An error occurred while trying to delete the announcement.');
        });
    }
});


