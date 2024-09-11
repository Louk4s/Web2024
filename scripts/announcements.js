document.addEventListener("DOMContentLoaded", function () {
    const categorySelect = document.getElementById('category');
    const itemSelect = document.getElementById('items');

    categorySelect.addEventListener('change', function () {
        const selectedCategories = Array.from(categorySelect.selectedOptions).map(option => option.value);

        if (selectedCategories.length > 0) {
            fetchItems(selectedCategories);
        } else {
            itemSelect.innerHTML = '';  // Αδειάζει τα items
            itemSelect.disabled = true;
        }
    });

    function fetchItems(categories) {
        const query = categories.join(',');  // Join selected category IDs into a string

        fetch(`get_items_by_category.php?category_ids=${query}`)
            .then(response => response.json())
            .then(data => {
                itemSelect.innerHTML = ''; // Αδειάζει τη λίστα των items
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    itemSelect.appendChild(option);
                });
                itemSelect.disabled = false; // Ενεργοποιεί τη λίστα των items
            })
            .catch(error => {
                console.error('Error fetching items:', error);
            });
    }
});


