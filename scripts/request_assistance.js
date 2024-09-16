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
        itemSelect.addEventListener('change', checkFormValidity);
        peopleCountInput.addEventListener('input', checkFormValidity);
    }

    if (categorySelect && itemSelect) {
        categorySelect.addEventListener('change', function () {
            const selectedCategory = categorySelect.value;

            if (selectedCategory) {
                fetchItems(selectedCategory);
            } else {
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';  // Clear the items
                itemSelect.disabled = true;
            }
            checkFormValidity(); // Recheck form validity after category selection
        });

        function fetchItems(category) {
            fetch(`../actions/get_items_by_category.php?category_id=${category}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to fetch items: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>'; // Clear the items list
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
                    checkFormValidity(); // Recheck form validity after items are loaded
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                });
        }
    }
});

