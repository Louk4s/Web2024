document.addEventListener("DOMContentLoaded", function () {
    const categorySelect = document.getElementById('category_id');
    const itemSelect = $('#item_id'); // Select2 dropdown is initialized as jQuery object
    const peopleCountInput = document.getElementById('people_count');
    const submitButton = document.querySelector('button[type="submit"]');

    // Function to check if all conditions are met
    function checkFormValidity() {
        const selectedCategory = categorySelect.value;
        const selectedItem = itemSelect.val(); // Use Select2's jQuery method to get the value
        const peopleCount = peopleCountInput.value;

        // Enable the submit button only if all conditions are met
        if (selectedCategory && selectedItem && peopleCount > 0) {
            submitButton.disabled = false;
        } 
    }

    // Add event listeners to inputs to trigger form validation
    if (categorySelect && itemSelect && peopleCountInput) {
        categorySelect.addEventListener('change', checkFormValidity);
        itemSelect.on('change', checkFormValidity); // Select2 change event
        peopleCountInput.addEventListener('input', checkFormValidity);
    }

    if (categorySelect && itemSelect) {
        categorySelect.addEventListener('change', function () {
            const selectedCategory = categorySelect.value;

            if (selectedCategory) {
                fetchItems(selectedCategory);
            } else {
                // Fetch all items when no category is selected
                fetchItems(null);
            }
            checkFormValidity(); // Recheck form validity after category selection
        });

        // Fetch items dynamically
        function fetchItems(category) {
            let url = '../actions/get_items_by_category.php';
            if (category !== null) {
                url += `?category_id=${category}`; // Append category_id if a category is selected
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to fetch items: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.html('<option value="">-- Select Item --</option>'); // Clear the items list
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = new Option(item.name, item.id, false, false);
                            itemSelect.append(option);
                        });
                        itemSelect.prop('disabled', false); // Enable the items list
                    } else {
                        itemSelect.prop('disabled', true); // Disable if no items
                    }
                    checkFormValidity(); // Recheck form validity after items are loaded
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                });
        }

        // Fetch all items by default (when page loads and no category is selected)
        fetchItems(null);
    }
});



