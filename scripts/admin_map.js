document.addEventListener('DOMContentLoaded', (event) => {
    let rescuerMarkers = [];
    let offerMarkers = [];
    let requestMarkers = [];
    let map;

    // Initialize the map after DOM is loaded
    function initMap(baseLat, baseLng) {
        map = L.map('map').setView([baseLat, baseLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        return map;
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
            map = initMap(baseLat, baseLng);

            // Add base marker
            let baseMarker = L.marker([baseLat, baseLng], {
                icon: L.icon({
                    iconUrl: '../icons/base-icon.png',
                    iconSize: [20, 20],
                    iconAnchor: [10, 20],
                    popupAnchor: [0, -20]
                })
            }).addTo(map).bindPopup('Base Location');

            // Add rescuers
            rescuers.forEach(function (rescuer) {
                if (rescuer.latitude && rescuer.longitude) {
                    let marker = L.marker([rescuer.latitude, rescuer.longitude], {
                        icon: L.icon({
                            iconUrl: '../icons/rescuer_icon.png',
                            iconSize: [30, 30],
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -20]
                        })
                    }).bindPopup(`Rescuer: ${rescuer.fullname}`);
                    rescuerMarkers.push(marker);
                }
            });

            // Add tasks (offers and requests)
            tasks.forEach(function (task) {
                let iconUrl = task.task_type === 'offer' ? '../icons/offer-icon.png' : '../icons/request-icon.png';
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
                    Registered On: ${task.created_at}<br>
                    Items: ${task.items}<br>
                    Status: ${task.status}
                `);

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
