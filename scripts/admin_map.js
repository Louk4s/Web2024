document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('viewMapBtn').addEventListener('click', function() {
        var mapContainer = document.getElementById('mapContainer');
        mapContainer.style.display = 'block'; // Show the map container

        // Fetch map data via AJAX
        fetch('../actions/fetch_map_data.php')
            .then(response => response.json())
            .then(data => {
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
                rescuers.forEach(function(rescuer) {
                    if (rescuer.latitude && rescuer.longitude) {  // Ensure valid coordinates
                        var marker = L.marker([rescuer.latitude, rescuer.longitude], {
                            icon: L.icon({
                                iconUrl: '../icons/rescuer-icon.png',
                                iconSize: [30, 30],
                                iconAnchor: [10, 20],
                                popupAnchor: [0, -20]
                            })
                        }).addTo(map);
                        marker.bindPopup('Rescuer: ' + rescuer.fullname);
                    }
                });

                // Loop through the tasks and add them to the map
                tasks.forEach(function(task) {
                    let markerIcon = task.task_type === 'offer' ? 'offer-icon.png' : 'request-icon.png';
                    let marker = L.marker([task.latitude, task.longitude], {
                        icon: L.icon({
                            iconUrl: '../icons/' + markerIcon,
                            iconSize: [30, 30],
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -20]
                        })
                    }).addTo(map);

                    marker.bindPopup(task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1) + 
                                     ' Task: ID ' + task.related_id + '<br>Status: ' + task.status);
                });
            })
            .catch(error => console.error('Error fetching map data:', error));
    });
});



