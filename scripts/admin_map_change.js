document.addEventListener('DOMContentLoaded', () => {
    var baseLat = parseFloat(document.getElementById('latitude').value);
    var baseLng = parseFloat(document.getElementById('longitude').value);

    // Initialize the map
    var map = L.map('map').setView([baseLat, baseLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add a marker for the base location
    var baseMarker = L.marker([baseLat, baseLng], {
        icon: L.icon({
            iconUrl: '/Web2024/icons/base-icon.png',
            iconSize: [20, 20], 
            iconAnchor: [10, 20], 
            popupAnchor: [0, -20]
        }),
        draggable: true // Make the marker draggable
    }).addTo(map).bindPopup('Drag to set new base location').openPopup();

    // Update the latitude and longitude when the marker is dragged
    baseMarker.on('dragend', function (e) {
        var latLng = baseMarker.getLatLng();
        document.getElementById('latitude').value = latLng.lat;
        document.getElementById('longitude').value = latLng.lng;

        // Show the confirmation section
        document.getElementById('confirmation').style.display = 'block';
    });

    // Handle the Save Location button click
    document.getElementById('saveLocationBtn').addEventListener('click', function () {
        // Submit the form to update the location
        document.getElementById('locationForm').submit();
    });
});
