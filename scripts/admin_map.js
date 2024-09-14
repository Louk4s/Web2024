document.addEventListener('DOMContentLoaded', (event) => {
    // Fetch map data via AJAX
    fetch('../actions/fetch_map_data.php')
        .then(response => response.json())
        .then(data => {
            console.log(data);  // Log to check data structure
            
            var baseLat = data.base.latitude;
            var baseLng = data.base.longitude;
            var rescuers = data.rescuers;
            var tasks = data.tasks; // Added tasks

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
                            iconUrl: '../icons/rescuer_icon.png',
                            iconSize: [30, 30],
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -20]
                        })
                    }).addTo(map);
                    // Load details about the rescuer in the popup
                    marker.bindPopup('Rescuer: ' + rescuer.fullname);
                }
            });

            // Add task markers (offers and requests)
            tasks.forEach(function(task) {
                var iconUrl = task.task_type === 'offer' ? '../icons/offer-icon.png' : '../icons/request-icon.png';

                var marker = L.marker([task.latitude, task.longitude], {
                    icon: L.icon({
                        iconUrl: iconUrl,
                        iconSize: [30, 30],
                        iconAnchor: [10, 20],
                        popupAnchor: [0, -20]
                    })
                }).addTo(map);

                marker.bindPopup(task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1) + ' Task - Status: ' + task.status);
            });
        })
        .catch(error => console.error('Error fetching map data:', error));
});
