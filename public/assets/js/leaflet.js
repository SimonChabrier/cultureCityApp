const mapModule = {

    init: function()
    {
        console.log("map.init()");
        
        mapModule.displayMap();

    },

    currentMap: L.map('map').setView([48.8767488, 2.29376], 13),

    displayMap: function()
    {
        console.log("leaflet file");

        const map = mapModule.currentMap;

        L.tileLayer(
            'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiY3VsdHVyZWNpdHlhcHAiLCJhIjoiY2wwaTc0bHhvMDEwZTNjczB4ZXFzYzNqYiJ9.gyW9DkdRuL7iWqBp3wVvjQ',
        {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            minZomm: 3,
            maxZoom: 20,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: 'your.mapbox.access.token'
        }).addTo(map);

        mapModule.refreshMarkers([[48.8883317, 2.298457]]);
    },

    refreshMarkers: function(eventsCoordinates)
    {
        const markers = new L.LayerGroup().addTo(mapModule.currentMap);

        markers.clearLayers();

        for (const coordinates of eventsCoordinates)
        {
            const points = L.marker([coordinates[0], coordinates[1]]).addTo(markers);
            points.bindPopup("<p>le nom du lieu</p>");
        }
    }
}