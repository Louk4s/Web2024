document.addEventListener('DOMContentLoaded', (event) => {
    let rescuerMarkers = [];
    let offerMarkers = [];
    let pendingRequestMarkers = [];
    let inProgressRequestMarkers = [];
    let taskLines = [];
    let map;
    let markersClusterGroup = L.markerClusterGroup(); // Cluster group for markers

    // Initialize map
    function initMap(baseLat, baseLng) {
        map = L.map('map').setView([baseLat, baseLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    }

    // Fetch map data
    fetch('../actions/fetch_map_data.php')
        .then(response => response.json())
        .then(data => {
            let baseLat = data.base.latitude;
            let baseLng = data.base.longitude;
            let rescuers = data.rescuers;
            let tasks = data.tasks;

            // Initialize the map
            initMap(baseLat, baseLng);

            // Add a marker for the base location
            let baseMarker = L.marker([baseLat, baseLng], {
                icon: L.icon({
                    iconUrl: '../icons/base-icon.png',
                    iconSize: [20, 20],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                }),
                draggable: false
            }).addTo(map).bindPopup('<div class="custom-popup">Base Location</div>').openPopup();

            // Add rescuers and draw lines to their active tasks
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
                    }).bindPopup(popupContent);

                    rescuerMarkers.push({
                        marker: rescuerMarker,
                        hasActiveTask: !!activeTask // Flag to determine if the rescuer has an active task
                    });

                    // Add rescuer marker to the cluster group
                    markersClusterGroup.addLayer(rescuerMarker);

                    // Draw line to active task if available
                    if (activeTask) {
                        let line = L.polyline([[rescuer.latitude, rescuer.longitude], [activeTask.latitude, activeTask.longitude]], {
                            color: 'blue'
                        });
                        taskLines.push(line);
                    }
                }
            });

            // Add task markers (offers/requests)
            tasks.forEach(function (task) {
                let iconUrl = task.task_type === 'offer' ? '../icons/offer-icon.png' : task.status === 'pending' ? '../icons/pending_request_icon.png' : '../icons/inprogress_request_icon.png';
                let marker = L.marker([task.latitude, task.longitude], {
                    icon: L.icon({
                        iconUrl: iconUrl,
                        iconSize: [30, 30],
                        iconAnchor: [10, 20],
                        popupAnchor: [0, -20]
                    })
                }).bindPopup(`
                    Task Type: ${task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1)}<br>
                    Citizen: ${task.citizen_name}<br>
                    Phone: ${task.citizen_phone}<br>
                    Accepted On: ${task.collected_at ? task.collected_at : 'Not accepted yet'}<br>
                    Rescuer: ${task.rescuer_name ? task.rescuer_name : 'Not assigned yet'}<br>
                    Items: ${task.items}<br>
                    Status: ${task.status}
                `);

                // Add task markers to corresponding arrays and cluster group
                if (task.task_type === 'offer') {
                    offerMarkers.push(marker);
                    markersClusterGroup.addLayer(marker);
                } else if (task.status === 'pending') {
                    pendingRequestMarkers.push(marker);
                    markersClusterGroup.addLayer(marker);
                } else if (task.status === 'in_progress') {
                    inProgressRequestMarkers.push(marker);
                    markersClusterGroup.addLayer(marker);
                }
            });

            // Add cluster group to the map
            map.addLayer(markersClusterGroup);

            // Apply initial visibility based on the default checkbox state
            applyFilterState();

            // Toggle functionality for markers and lines
            document.getElementById('showRescuersWithActiveTasks').addEventListener('change', applyFilterState);
            document.getElementById('showRescuersWithoutActiveTasks').addEventListener('change', applyFilterState);
            document.getElementById('showOffers').addEventListener('change', applyFilterState);
            document.getElementById('showRequestsPending').addEventListener('change', applyFilterState);
            document.getElementById('showRequestsInProgress').addEventListener('change', applyFilterState);
            document.getElementById('showTaskLines').addEventListener('change', applyFilterState);
        })
        .catch(error => console.error('Error fetching map data:', error));

    // Apply filter state based on checkboxes
    function applyFilterState() {
        let showRescuersWithActiveTasks = document.getElementById('showRescuersWithActiveTasks').checked;
        let showRescuersWithoutActiveTasks = document.getElementById('showRescuersWithoutActiveTasks').checked;
        let showOffers = document.getElementById('showOffers').checked;
        let showRequestsPending = document.getElementById('showRequestsPending').checked;
        let showRequestsInProgress = document.getElementById('showRequestsInProgress').checked;
        let showTaskLines = document.getElementById('showTaskLines').checked;

        toggleRescuerMarkers(showRescuersWithActiveTasks, showRescuersWithoutActiveTasks);
        toggleMarkers(offerMarkers, showOffers);
        toggleMarkers(pendingRequestMarkers, showRequestsPending);
        toggleMarkers(inProgressRequestMarkers, showRequestsInProgress);
        toggleLines(taskLines, showTaskLines);
    }

    // Toggle marker visibility
    function toggleMarkers(markerArray, isChecked) {
        markerArray.forEach(function (marker) {
            if (isChecked) {
                markersClusterGroup.addLayer(marker); // Add marker to cluster group
            } else {
                markersClusterGroup.removeLayer(marker); // Remove marker from cluster group
            }
        });
    }

    // Toggle visibility for rescuers based on their active task status
    function toggleRescuerMarkers(showWithActiveTasks, showWithoutActiveTasks) {
        rescuerMarkers.forEach(function (rescuer) {
            if (rescuer.hasActiveTask && showWithActiveTasks) {
                markersClusterGroup.addLayer(rescuer.marker); // Add rescuer marker to cluster
            } else if (!rescuer.hasActiveTask && showWithoutActiveTasks) {
                markersClusterGroup.addLayer(rescuer.marker); // Add rescuer marker to cluster
            } else {
                markersClusterGroup.removeLayer(rescuer.marker); // Remove rescuer marker from cluster
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

    // Toggle filter visibility
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

    // Ensure all checkboxes are checked on page load
    document.getElementById('showRescuersWithActiveTasks').checked = true;
    document.getElementById('showRescuersWithoutActiveTasks').checked = true;
    document.getElementById('showOffers').checked = true;
    document.getElementById('showRequestsPending').checked = true;
    document.getElementById('showRequestsInProgress').checked = true;
    document.getElementById('showTaskLines').checked = true;

    // Apply the filter state on page load to show all markers
    applyFilterState();
});
