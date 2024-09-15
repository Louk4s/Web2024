document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('mapContainer').setView([rescuerLocation.latitude, rescuerLocation.longitude], 13);

    var baseMarker, rescuerMarker, circle, isInsideCircle = false;
    var newLatLng = null;
    var taskMarkers = {};  // Object to store markers for each task

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Load the base marker and draw a 100m circle around it
    function loadBaseMarker(baseData) {
        baseMarker = L.marker([baseData.latitude, baseData.longitude], {
            icon: L.icon({
                iconUrl: '../icons/base-icon.png',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            }),
            draggable: false
        }).addTo(map).bindPopup('Base Location');

        // Draw the 100m radius circle around the base
        circle = L.circle([baseData.latitude, baseData.longitude], {
            color: 'blue',
            fillColor: '#add8e6',
            fillOpacity: 0.3,
            radius: 100  // Radius in meters
        }).addTo(map);
    }

    // Check if the rescuer is within the 100m circle
    function checkIfInsideCircle() {
        if (circle && rescuerMarker) {
            var rescuerLatLng = rescuerMarker.getLatLng();
            var distance = map.distance(circle.getLatLng(), rescuerLatLng);  // Distance in meters
            isInsideCircle = (distance <= circle.getRadius());
            return isInsideCircle;
        }
        return false;
    }

    // Function to update session variable isInsideCircle
    function updateSessionCircle(isInside) {
        fetch('../actions/update_circle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ isInsideCircle: isInside })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to update session.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Cluster group for markers
    var markers = L.markerClusterGroup();

    // Fetch the base location and tasks and add them to the map
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
                var popupText = '';

                // Set the appropriate icon and display task type
                if (task.task_type === 'offer') {
                    taskIconUrl = task.status === 'pending' ? '../icons/pending_offer_icon.png' : '../icons/inprogress_offer_icon.png';
                    popupText += `<b>Task Type:</b> Offer<br>`;
                } else {
                    taskIconUrl = task.status === 'pending' ? '../icons/pending_request_icon.png' : '../icons/inprogress_request_icon.png';
                    popupText += `<b>Task Type:</b> Request<br>`;
                }

                // Add task details to the popup
                popupText += `<b>Citizen:</b> ${task.citizen_name || 'Unknown'}<br>`;
                popupText += `<b>Phone:</b> ${task.citizen_phone || 'Unknown'}<br>`;
                popupText += `<b>Registered On:</b> ${task.registered_on || 'Unknown'}<br>`;
                popupText += `<b>Items:</b> ${task.items || 'Unknown'}<br>`;
                popupText += `<b>Status:</b> ${task.status || 'Unknown'}<br>`;

                // Add action buttons based on task status
                if (task.status === 'pending') {
                    popupText += `<a href="accept_task.php?task_id=${task.task_id}" class="button">Accept Task</a><br>`;
                } else if (task.status === 'in_progress' && task.rescuer_id == rescuerLocation.rescuer_id) {
                    popupText += `<a href="complete_task.php?task_id=${task.task_id}" class="button">Complete Task</a><br>`;
                    popupText += `<a href="cancel_task.php?task_id=${task.task_id}" class="button">Cancel Task</a><br>`;
                }

                // Add the task marker
                var taskMarker = L.marker([task.latitude, task.longitude], {
                    icon: L.icon({
                        iconUrl: taskIconUrl,
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                }).bindPopup(popupText);

                // Store the marker for future reference
                taskMarkers[task.task_id] = taskMarker;
                markers.addLayer(taskMarker);
            });

            map.addLayer(markers); // Add the clustered markers to the map
        })
        .catch(error => console.error('Error fetching map data:', error));

    // Rescuer marker
    rescuerMarker = L.marker([rescuerLocation.latitude, rescuerLocation.longitude], {
        icon: L.icon({
            iconUrl: '../icons/rescuer_icon.png',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        }),
        draggable: false // Enable dragging for rescuer marker
    }).addTo(map);

    // When the marker is dragged, update the session and check if inside the circle
    rescuerMarker.on('dragend', function (event) {
        newLatLng = event.target.getLatLng();
        if (checkIfInsideCircle()) {
            updateSessionCircle(true);  // Inside the circle
        } else {
            updateSessionCircle(false);  // Outside the circle
        }
    });

    // Fetch rescuer tasks and display in rescuer popup with circle check
    fetch('../actions/fetch_rescuer_tasks.php?rescuer_id=' + rescuerLocation.rescuer_id)
        .then(response => response.json())
        .then(data => {
            var taskCount = data.tasks.length;
            var taskDetails = data.tasks.map(task => `${task.task_type}: ${task.items}`).join('<br>');

            var rescuerPopupText = `
                <b>Rescuer:</b> ${rescuerLocation.rescuer_name}<br>
                <b>Tasks:</b> ${taskCount}<br>
                <b>Details:</b><br>${taskDetails}<br>
                <a href="view_trucks_inventory.php" class="button">See Inventory</a>
            `;

            // Check if rescuer is inside the circle and display relevant message
            if (checkIfInsideCircle()) {
                rescuerPopupText += `<br><b>Note:</b> You are inside the base area and can access inventory.`;
            } else {
                rescuerPopupText += `<br><b>Note:</b> You are outside the base area and cannot access inventory.`;
            }

            rescuerMarker.bindPopup(rescuerPopupText).openPopup();  // Automatically open the rescuer popup
        })
        .catch(error => console.error('Error fetching rescuer tasks:', error));

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

                    // Re-check circle status after moving marker
                    if (checkIfInsideCircle()) {
                        updateSessionCircle(true);
                    } else {
                        updateSessionCircle(false);
                    }
                } else {
                    alert("Error updating rescuer location.");
                }
            });
        } else {
            alert('Move your marker first before saving.');
        }
    });
});
