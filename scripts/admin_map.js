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

                // Initialize the map
                var map = L.map('map').setView([baseLat, baseLng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Add a marker for the base location with smaller size
                var baseMarker = L.marker([baseLat, baseLng], {
                    icon: L.icon({
                        iconUrl: '../icons/base-icon.png',
                        iconSize: [20, 20], // Set the size smaller
                        iconAnchor: [10, 20], // Center-bottom anchor point
                        popupAnchor: [0, -20] // Popup position relative to the icon
                    }),
                    draggable: false
                }).addTo(map).bindPopup('<div class="custom-popup">Base Location</div>').openPopup(); // Added a custom class here

                // Update base location in the database when dragged
                baseMarker.on('dragend', function(e) {
                    var latLng = baseMarker.getLatLng();
                    
                    // AJAX to update base location
                    fetch('../actions/update_base_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `latitude=${latLng.lat}&longitude=${latLng.lng}`
                    });
                });

                // Add rescuers to the map with smaller size
                rescuers.forEach(function(rescuer) {
                    var marker = L.marker([rescuer.latitude, rescuer.longitude], {
                        icon: L.icon({
                            iconUrl: '../icons/rescuer-icon.png',
                            iconSize: [20, 20], // Set the size smaller for rescuers
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -20]
                        })
                    }).addTo(map);
                    marker.bindPopup('Rescuer: ' + rescuer.fullname);
                });
            })
            .catch(error => console.error('Error fetching map data:', error));
    });
});



