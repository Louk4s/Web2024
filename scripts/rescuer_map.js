document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('mapContainer').setView([rescuerLocation.latitude, rescuerLocation.longitude], 13);

    var baseMarker, rescuerMarker, circle, isInsideCircle = false;
    var newLatLng = null;
    var taskMarkers = {};  // Object to store markers for each task
    var offerMarkers = [];
    var pendingRequestMarkers = [];
    var inProgressRequestMarkers = [];
    var taskLines = [];  // Store lines to active tasks
    var taskCircles = {};  // Store task completion circles

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

    // Check if the rescuer is within the 100m base circle
    function checkIfInsideCircle() {
        if (circle && rescuerMarker) {
            var rescuerLatLng = rescuerMarker.getLatLng();
            var distance = map.distance(circle.getLatLng(), rescuerLatLng);  // Distance in meters
            isInsideCircle = (distance <= circle.getRadius());
            return isInsideCircle;
        }
        return false;
    }

    // Function to update session variable isInsideCircle for base circle
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
                console.error('Failed to update session for base circle.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Cluster group for markers
    var markers = L.markerClusterGroup();

    // Fetch the base location and tasks and add them to the map
    function fetchAndRenderTasks() {
        // Clear existing lines and circles before re-rendering
        taskLines.forEach(line => map.removeLayer(line));
        taskLines = [];  // Reset task lines

        Object.values(taskCircles).forEach(circle => map.removeLayer(circle));
        taskCircles = {};  // Reset task completion circles

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

                    // Determine the correct icon based on task type and status
                    if (task.task_type === 'offer') {
                        taskIconUrl = task.status === 'pending' 
                            ? '../icons/pending_offer_icon.png' 
                            : '../icons/inprogress_offer_icon.png';
                        popupText += `<b>Task Type:</b> Offer<br>`;
                    } else if (task.task_type === 'request') {
                        taskIconUrl = task.status === 'pending' 
                            ? '../icons/pending_request_icon.png' 
                            : '../icons/inprogress_request_icon.png';
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
                        // Only the rescuer can accept a pending task
                        popupText += `<a href="#" class="button accept-task" data-task-id="${task.task_id}">Accept Task</a><br>`;
                    } else if (task.status === 'in_progress') {
                        if (task.rescuer_id == rescuerLocation.rescuer_id) {
                            // If this rescuer is assigned to the task, show complete/cancel options
                            popupText += `<b>Assigned to:</b> You<br>`;
                            popupText += `<a href="complete_task.php?task_id=${task.task_id}" class="button complete-task">Complete Task</a><br>`;
                            popupText += `<a href="cancel_task.php?task_id=${task.task_id}" class="button cancel-task">Cancel Task</a><br>`;

                            // Draw a line from the rescuer to the task location
                            let line = L.polyline([[rescuerLocation.latitude, rescuerLocation.longitude], [task.latitude, task.longitude]], {
                                color: 'blue'
                            });
                            taskLines.push(line);
                            map.addLayer(line);  // Show the line on the map

                            // Draw a 50m circle around the task
                            let taskCircle = L.circle([task.latitude, task.longitude], {
                                color: 'green',
                                fillColor: '#32cd32',
                                fillOpacity: 0.3,
                                radius: 50  // Radius in meters (50 meters)
                            }).addTo(map);

                            taskCircles[task.task_id] = taskCircle;

                            // Check if rescuer is inside the 50m circle
                            checkIfInsideTaskCircle(taskCircle);
                        } else {
                            // Display which rescuer is handling the task
                            popupText += `<b>Assigned to:</b> ${task.rescuer_name || 'Unknown'}<br>`;
                        }
                    }

                    // Add the task marker to the map
                    var taskMarker = L.marker([task.latitude, task.longitude], {
                        icon: L.icon({
                            iconUrl: taskIconUrl,
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        })
                    }).bindPopup(popupText);

                    taskMarkers[task.task_id] = taskMarker;

                    if (task.task_type === 'offer') {
                        offerMarkers.push(taskMarker);
                    } else if (task.status === 'pending') {
                        pendingRequestMarkers.push(taskMarker);
                    } else if (task.status === 'in_progress') {
                        inProgressRequestMarkers.push(taskMarker);
                    }

                    markers.addLayer(taskMarker);
                });

                map.addLayer(markers); // Add the clustered markers to the map
            })
            .catch(error => console.error('Error fetching map data:', error));
    }

    // Function to check if the rescuer is inside the 50m task circle
    function checkIfInsideTaskCircle(taskCircle) {
        if (taskCircle && rescuerMarker) {
            var rescuerLatLng = rescuerMarker.getLatLng();
            var distance = map.distance(taskCircle.getLatLng(), rescuerLatLng);  // Distance in meters
            isInsideCircle = (distance <= taskCircle.getRadius());

            // Update the session variable
            updateSessionCircleForTask(isInsideCircle);
        }
    }

    // Function to update session variable isInsideCircleCitizen
    function updateSessionCircleForTask(isInside) {
        fetch('../actions/update_task_circle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ isInsideCircleCitizen: isInside })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to update session.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    fetchAndRenderTasks();

    // Rescuer marker
    rescuerMarker = L.marker([rescuerLocation.latitude, rescuerLocation.longitude], {
        icon: L.icon({
            iconUrl: '../icons/rescuer_icon.png',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        }),
        draggable: true // Enable dragging for rescuer marker
    }).addTo(map);

    // When the marker is dragged, update the session and check if inside the task circle or base circle
    rescuerMarker.on('dragend', function (event) {
        newLatLng = event.target.getLatLng();

        // Check if inside the base 100m circle
        if (checkIfInsideCircle()) {
            updateSessionCircle(true);  // Inside the base circle
        } else {
            updateSessionCircle(false);  // Outside the base circle
        }

        // Check if inside any task circle after dragging
        Object.values(taskCircles).forEach(circle => {
            checkIfInsideTaskCircle(circle);
        });
    });

    // Accept task functionality
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('accept-task')) {
            e.preventDefault();  // Prevent page scroll

            let taskId = e.target.getAttribute('data-task-id');

            // Send request to accept the task
            fetch(`accept_task.php?task_id=${taskId}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Task accepted successfully!');
                        // Re-fetch tasks and update the map
                        fetchAndRenderTasks();
                    } else {
                        alert('Error accepting task');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });

    // Complete and Cancel task functionality
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('complete-task') || e.target.classList.contains('cancel-task')) {
            e.preventDefault();  // Prevent page scroll
        }
    });

    // Button to move the rescuer marker
    document.getElementById('moveMarkerBtn').addEventListener('click', function (e) {
        e.preventDefault();  // Prevent page scroll
        rescuerMarker.dragging.enable();
        alert('You can now move your marker. Once done, press "Save Location" to confirm.');
    });

    // Button to save the new marker position
    document.getElementById('saveMarkerBtn').addEventListener('click', function (e) {
        e.preventDefault();  // Prevent page scroll
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
                    Object.values(taskCircles).forEach(circle => {
                        checkIfInsideTaskCircle(circle);
                    });

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

    // Apply initial filter state
    applyFilterState();

    // Filter functionality
    document.getElementById('toggleFilters').addEventListener('click', function () {
        let filterContainer = document.getElementById('filterContainer');
        if (filterContainer.style.display === 'none') {
            filterContainer.style.display = 'block';
            this.textContent = 'Hide Filters';
        } else {
            filterContainer.style.display = 'none';
            this.textContent = 'Show Filters';
        }
    });

    // Apply filter state based on checkboxes
    function applyFilterState() {
        let showOffers = document.getElementById('showOffers').checked;
        let showRequestsPending = document.getElementById('showRequestsPending').checked;
        let showRequestsInProgress = document.getElementById('showRequestsInProgress').checked;
        let showTaskLines = document.getElementById('showTaskLines').checked;

        toggleMarkers(offerMarkers, showOffers);
        toggleMarkers(pendingRequestMarkers, showRequestsPending);
        toggleMarkers(inProgressRequestMarkers, showRequestsInProgress);
        toggleLines(taskLines, showTaskLines);
    }

    // Toggle marker visibility
    function toggleMarkers(markerArray, isChecked) {
        markerArray.forEach(function (marker) {
            if (isChecked) {
                map.addLayer(marker);
            } else {
                map.removeLayer(marker);
            }
        });
    }

    // Toggle line visibility
    function toggleLines(lineArray, isChecked) {
        lineArray.forEach(function (line) {
            if (isChecked) {
                map.addLayer(line);
            } else {
                map.removeLayer(line);
            }
        });
    }

    // Add event listeners for the checkboxes
    document.getElementById('showOffers').addEventListener('change', applyFilterState);
    document.getElementById('showRequestsPending').addEventListener('change', applyFilterState);
    document.getElementById('showRequestsInProgress').addEventListener('change', applyFilterState);
    document.getElementById('showTaskLines').addEventListener('change', applyFilterState);
});
