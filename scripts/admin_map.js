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

                // Log the rescuers data for debugging
                console.log('Rescuers:', rescuers);

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
                    // Log each rescuer's data
                    console.log('Adding rescuer:', rescuer);
                    
                    if (rescuer.latitude && rescuer.longitude) {  // Ensure valid coordinates
                        var marker = L.marker([rescuer.latitude, rescuer.longitude], {
                            icon: L.icon({
                                iconUrl: '../icons/rescuer-icon.png',
                                iconSize: [20, 20],
                                iconAnchor: [10, 20],
                                popupAnchor: [0, -20]
                            })
                        }).addTo(map);
                        marker.bindPopup('Rescuer: ' + rescuer.fullname);
                    }
                });
            })
            .catch(error => console.error('Error fetching map data:', error));
    });
});


