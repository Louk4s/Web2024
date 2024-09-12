document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('mapContainer').setView([rescuerLocation.latitude, rescuerLocation.longitude], 13);

    var baseMarker;
    var rescuerMarker;
    var newLatLng = null;
    var activeTaskLine = null; // To hold the current in-progress task line

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
        })
        .catch(error => console.error('Error fetching base location:', error));

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

    // Load task markers
    tasksData.forEach(function(task) {
        var taskIconUrl;
        var popupText = task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1) + ' Task';

        if (task.task_type === 'offer') {
            taskIconUrl = task.status === 'pending' ? '../icons/pending_offer_icon.png' : '../icons/inprogress_offer_icon.png';
        } else {
            taskIconUrl = task.status === 'pending' ? '../icons/pending_request_icon.png' : '../icons/inprogress_request_icon.png';
        }

        // Add the task marker
        var taskMarker = L.marker([task.latitude, task.longitude], {
            icon: L.icon({
                iconUrl: taskIconUrl,
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            })
        }).addTo(map).bindPopup(popupText);

        // Log coordinates for debugging
        console.log('Rescuer Location:', rescuerLocation);
        console.log('Task Location:', task);

        // If the task is in progress, draw a straight red line between the rescuer and the task
        if (task.status === 'in_progress' && task.rescuer_id === rescuerLocation.rescuer_id) {
            if (activeTaskLine) {
                map.removeLayer(activeTaskLine); // Remove previous line if there is one
            }

            console.log('Drawing red line between rescuer and task:', task);

            // Double-check if coordinates are different
            if (rescuerLocation.latitude !== task.latitude || rescuerLocation.longitude !== task.longitude) {
                activeTaskLine = L.polyline([
                    [rescuerLocation.latitude, rescuerLocation.longitude],
                    [task.latitude, task.longitude]
                ], {color: 'red', weight: 3}).addTo(map);
            } else {
                console.warn('Rescuer and task locations are the same, no line drawn.');
            }
        }
    });

    // Remove the line when a task is completed
    document.querySelectorAll('[id^="completeTaskBtn_"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (activeTaskLine) {
                console.log('Removing line for completed task');
                map.removeLayer(activeTaskLine); // Remove the red line
                activeTaskLine = null;
            }
        });
    });
});
