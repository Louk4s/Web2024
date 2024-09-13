document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('mapContainer').setView([rescuerLocation.latitude, rescuerLocation.longitude], 13);

    var baseMarker;
    var rescuerMarker;
    var newLatLng = null;
    var activeTaskLine = null; // To hold the current in-progress task line
    var taskMarkers = {}; // To store all task markers by task ID

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Load the base marker so the rescuer can see it
    function loadBaseMarker(baseData) {
        baseMarker = L.marker([baseData.latitude, baseData.longitude], {
            icon: L.icon({
                iconUrl: '../icons/base-icon.png',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            }),
            draggable: false // Base should not be draggable on the rescuer map
        }).addTo(map).bindPopup('Base Location');
    }

    // Fetch the base location and add it to the map
    fetch('../actions/fetch_map_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.base) {
                loadBaseMarker(data.base);
            } else {
                console.error("Base location not found in the response");
            }

            // Add task markers with popup information
            data.tasks.forEach(function (task) {
                var taskIconUrl;
                var popupText = '';  // Initializing popup content

                if (task.task_type === 'offer') {
                    taskIconUrl = task.status === 'pending' ? '../icons/pending_offer_icon.png' : '../icons/inprogress_offer_icon.png';
                } else {
                    taskIconUrl = task.status === 'pending' ? '../icons/pending_request_icon.png' : '../icons/inprogress_request_icon.png';
                }

                // Add task details to popup
                popupText += `<b>Citizen:</b> ${task.citizen_name || 'Unknown'}<br>`;
                popupText += `<b>Phone:</b> ${task.citizen_phone || 'Unknown'}<br>`;
                popupText += `<b>Registered On:</b> ${task.registered_on || 'Unknown'}<br>`;
                popupText += `<b>Items:</b> ${task.items || 'Unknown'}<br>`;
                popupText += `<b>Status:</b> ${task.status || 'Unknown'}<br>`;

                // Add "Complete" button only if the task status is in progress
                if (task.status === 'in_progress') {
                    popupText += `<a href="complete_task.php?task_id=${task.task_id}" class="button">Complete</a>`;
                }

                popupText += `<a href="cancel_task.php?task_id=${task.task_id}" class="button">Cancel</a>`;

                // Add the task marker
                var taskMarker = L.marker([task.latitude, task.longitude], {
                    icon: L.icon({
                        iconUrl: taskIconUrl,
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                }).addTo(map).bindPopup(popupText);  // Show popup with the task details

                // Store the task marker with task ID for easy reference later
                taskMarkers[task.task_id] = taskMarker;
            });
        })
        .catch(error => console.error('Error fetching map data:', error));

    // Rescuer marker
    rescuerMarker = L.marker([rescuerLocation.latitude, rescuerLocation.longitude], {
        icon: L.icon({
            iconUrl: '../icons/rescuer_icon.png',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        }),
        draggable: false // Initially not draggable, will be enabled by the button
    }).addTo(map).bindPopup('Rescuer Location');

    // Button to move the rescuer marker
    document.getElementById('moveMarkerBtn').addEventListener('click', function () {
        rescuerMarker.dragging.enable();
        alert('You can now move your marker. Once done, press "Save Location" to confirm.');

        rescuerMarker.on('dragend', function (event) {
            newLatLng = event.target.getLatLng();
            alert('New location selected. Click "Save Location" to confirm.');
        });
    });

    // Button to save the new marker position
    document.getElementById('saveMarkerBtn').addEventListener('click', function () {
        if (newLatLng) {
            fetch('../actions/update_rescuer_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    latitude: newLatLng.lat,
                    longitude: newLatLng.lng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Rescuer location updated successfully!");
                    rescuerMarker.setLatLng(newLatLng);
                    rescuerMarker.dragging.disable();
                } else {
                    alert("Error updating rescuer location.");
                }
            });
        } else {
            alert('Move your marker first before saving.');
        }
    });

    // Locate task on map when button is clicked
    document.querySelectorAll('.locate-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var lat = parseFloat(this.getAttribute('data-lat'));
            var lng = parseFloat(this.getAttribute('data-lng'));
            map.setView([lat, lng], 15); // Zoom into the task location
            var taskId = this.getAttribute('data-task-id');
            if (taskMarkers[taskId]) {
                taskMarkers[taskId].openPopup(); // Open popup for the task marker
            }
        });
    });
});
