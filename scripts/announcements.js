document.addEventListener("DOMContentLoaded", function () {
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
});



