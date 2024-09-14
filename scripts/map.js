document.addEventListener('DOMContentLoaded', (event) => {
    var baseLat = document.getElementById('base-lat').value;
    var baseLng = document.getElementById('base-lng').value;
    var map = L.map('map').setView([baseLat, baseLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Base marker
    L.marker([baseLat, baseLng], {icon: L.icon({iconUrl: '../icons/base-icon.png'})}).addTo(map)
        .bindPopup('Base Location')
        .openPopup();

    var rescuers = JSON.parse(document.getElementById('rescuers-data').textContent);
    var citizens = JSON.parse(document.getElementById('citizens-data').textContent);
    var requests = JSON.parse(document.getElementById('requests-data').textContent);

    // Marker layers for toggling
    var rescuerLayer = L.layerGroup();
    var citizenLayer = L.layerGroup();
    var requestLayer = L.layerGroup();
    var offerLayer = L.layerGroup();

    // Add rescuers to the map
    rescuers.forEach(function(rescuer) {
        var marker = L.marker([rescuer.latitude, rescuer.longitude], {icon: L.icon({iconUrl: '../icons/rescuer_icon.png'})});
        marker.bindPopup('Rescuer: ' + rescuer.fullname);
        rescuerLayer.addLayer(marker);
    });

    // Add citizens to the map
    citizens.forEach(function(citizen) {
        var marker = L.marker([citizen.latitude, citizen.longitude], {icon: L.icon({iconUrl: '../icons/citizen-icon.png'})});
        marker.bindPopup('Citizen: ' + citizen.fullname);
        citizenLayer.addLayer(marker);
    });

    // Add requests to the map
    requests.forEach(function(request) {
        var marker = L.marker([request.latitude, request.longitude], {icon: L.icon({iconUrl: '../icons/pending_request_icon.png'})});
        marker.bindPopup('Request: ' + request.description);
        requestLayer.addLayer(marker);
    });

    // Add offer markers (assuming offers are part of the requests data)
    requests.forEach(function(request) {
        if (request.type === 'offer') {
            var marker = L.marker([request.latitude, request.longitude], {icon: L.icon({iconUrl: '../icons/pending_offer_icon.png'})});
            marker.bindPopup('Offer: ' + request.description);
            offerLayer.addLayer(marker);
        }
    });

    // Add layers to the map
    rescuerLayer.addTo(map);
    citizenLayer.addTo(map);
    requestLayer.addTo(map);
    offerLayer.addTo(map);

    // Add layer controls for toggling
    var overlayMaps = {
        "Rescuers": rescuerLayer,
        "Citizens": citizenLayer,
        "Requests": requestLayer,
        "Offers": offerLayer
    };
    L.control.layers(null, overlayMaps).addTo(map);
});
