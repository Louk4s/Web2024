document.addEventListener('DOMContentLoaded', function () {
    // Initialize Flatpickr with date format
    flatpickr('#startDate', {
        dateFormat: 'd/m/Y',
        maxDate: 'today'
    });

    flatpickr('#endDate', {
        dateFormat: 'd/m/Y',
        maxDate: 'today'
    });

    document.getElementById('timePeriodForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get the start and end date values
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Reformat the dates from dd/mm/yyyy to yyyy-mm-dd for the SQL query
        const formattedStartDate = formatDateForSQL(startDate);
        const formattedEndDate = formatDateForSQL(endDate);

        // Check if end date is before start date
        if (new Date(formattedEndDate) < new Date(formattedStartDate)) {
            alert('End date cannot be before start date.');
            return; // Exit the function to prevent further processing
        }
        if (formattedStartDate && formattedEndDate) {
            // Fetch data from statistics.php
            fetch('statistics.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `start_date=${formattedStartDate}&end_date=${formattedEndDate}`
            })
            .then(response => response.json())  // Parse response as JSON
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    // Update the chart with all 4 categories in one chart
                    updateChart(data.pending_requests, data.pending_offers, data.completed_requests, data.completed_offers);
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
        } else {
            alert('Please select a valid time range.');
        }
    });

    // Function to reformat date from dd/mm/yyyy to yyyy-mm-dd
    function formatDateForSQL(dateString) {
        const [day, month, year] = dateString.split('/');
        return `${year}-${month}-${day}`;  // Return date in yyyy-mm-dd format
    }

    // Function to update the chart
    function updateChart(pendingRequests, pendingOffers, completedRequests, completedOffers) {
        const ctx = document.getElementById('myChart').getContext('2d');

        // Check if the chart already exists and destroy it before creating a new one
        if (typeof window.myChart !== 'undefined' && window.myChart instanceof Chart) {
            window.myChart.destroy(); // Destroy only if myChart exists and is a Chart instance
        }

        // Create new chart
        window.myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending Requests', 'Pending Offers', 'Completed Requests', 'Completed Offers'],
                datasets: [{
                    label: 'Quantity',
                    data: [pendingRequests, pendingOffers, completedRequests, completedOffers],
                    backgroundColor: ['#FFA500', '#FFA500', '#4CAF50', '#4CAF50'], // Orange for pending, Green for completed
                    borderColor: ['#FFA500', '#FFA500', '#4CAF50', '#4CAF50'], // Border colors to match the background
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1, // Ensure the Y-axis displays whole numbers
                            precision: 0 // Removes decimal places
                        },
                        title: {
                            display: true,
                            text: 'Quantity',  // Display "Quantity" label on the y-axis
                            font: {
                                size: 16
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Hide the legend at the top
                    }
                }
            }
        });
    }
});









