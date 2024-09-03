document.addEventListener('DOMContentLoaded', (event) => {
    var baseLat = document.getElementById('latitude').value || 0;
    var baseLng = document.getElementById('longitude').value || 0;
    var map = L.map('map').setView([baseLat, baseLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker([baseLat, baseLng], { draggable: true }).addTo(map)
        .bindPopup('Base Location')
        .openPopup();

    marker.on('dragend', function (e) {
        var latLng = marker.getLatLng();
        document.getElementById('latitude').value = latLng.lat;
        document.getElementById('longitude').value = latLng.lng;
    });
});
