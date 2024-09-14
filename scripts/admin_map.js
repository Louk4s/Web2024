document.addEventListener('DOMContentLoaded', (event) => {
    // Arrays to store markers for toggling
    let rescuerMarkers = [];
    let offerMarkers = [];
    let requestMarkers = [];

    // Fetch map data via AJAX
    fetch('../actions/fetch_map_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching map data:', data.error);
                return;
            }

            var baseLat = data.base.latitude;
            var baseLng = data.base.longitude;
            var rescuers = data.rescuers;
            var tasks = data.tasks;

            // Initialize the map
            var map = L.map('map').setView([baseLat, baseLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add a marker for the base location
            var baseMarker = L.marker([baseLat, baseLng], {
                icon: L.icon({
                    iconUrl: '../icons/base-icon.png',
                    iconSize: [20, 20],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                }),
                draggable: false
            }).addTo(map).bindPopup('<div class="custom-popup">Base Location</div>').openPopup();

            // Add rescuers to the map
            rescuers.forEach(function (rescuer) {
                if (rescuer.latitude && rescuer.longitude) {
                    let activeTask = tasks.find(task => task.rescuer_id === rescuer.rescuer_id && task.status === 'in_progress');
                    let taskDetails = activeTask ? `Active Task ID: ${activeTask.task_id}` : 'No active task';
                    let inventoryContent = rescuer.inventory || 'No inventory';
                    
                    let popupContent = `Rescuer: ${rescuer.fullname}<br>Inventory: ${inventoryContent}<br>${taskDetails}`;
                    
                    let rescuerMarker = L.marker([rescuer.latitude, rescuer.longitude], {
                        icon: L.icon({
                            iconUrl: '../icons/rescuer_icon.png',
                            iconSize: [30, 30],
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -20]
                        })
                    }).addTo(map);

                    rescuerMarker.bindPopup(popupContent);
                    rescuerMarkers.push(rescuerMarker);

                    if (activeTask) {
                        // Draw line to active task
                        let line = L.polyline([[rescuer.latitude, rescuer.longitude], [activeTask.latitude, activeTask.longitude]], {
                            color: 'blue'
                        }).addTo(map);
                    }
                }
            });

            // Add task markers (offers and requests)
            tasks.forEach(function (task) {
                let iconUrl = task.task_type === 'offer' ? '../icons/offer-icon.png' : '../icons/request-icon.png';
                let marker = L.marker([task.latitude, task.longitude], {
                    icon: L.icon({
                        iconUrl: iconUrl,
                        iconSize: [30, 30],
                        iconAnchor: [10, 20],
                        popupAnchor: [0, -20]
                    })
                }).addTo(map);

                let popupContent = `Task Type: ${task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1)}<br>
                                    Citizen: ${task.citizen_name}<br>
                                    Phone: ${task.citizen_phone}<br>
                                    Registered On: ${task.registered_on}<br>
                                    Items: ${task.items}<br>
                                    Status: ${task.status}`;
                
                marker.bindPopup(popupContent);

                if (task.task_type === 'offer') {
                    offerMarkers.push(marker);
                } else {
                    requestMarkers.push(marker);
                }
            });

            // Toggle functionality
            document.getElementById('showRescuers').addEventListener('change', function () {
                toggleMarkers(rescuerMarkers, this.checked);
            });
            document.getElementById('showOffers').addEventListener('change', function () {
                toggleMarkers(offerMarkers, this.checked);
            });
            document.getElementById('showRequests').addEventListener('change', function () {
                toggleMarkers(requestMarkers, this.checked);
            });
        })
        .catch(error => console.error('Error fetching map data:', error));

    // Helper function to toggle marker visibility
    function toggleMarkers(layerGroup, isChecked) {
        layerGroup.forEach(function (marker) {
            if (isChecked) {
                map.addLayer(marker);
            } else {
                map.removeLayer(marker);
            }
        });
    }
});
