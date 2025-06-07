const mapOptions = {
    center: {
        lat: parseFloat(this.latitude),
        lng: parseFloat(this.longitude),
    },
    zoom: this.zoom,
    mapTypeId: this.getGoogleMapType(),
    ...this.getMapControlOptions(),
};

this.map = new google.maps.Map(mapContainer, mapOptions);

// Add marker - use legacy Marker for better compatibility
const markerPosition = {
    lat: parseFloat(this.latitude),
    lng: parseFloat(this.longitude),
};

this.marker = new google.maps.Marker({
    position: markerPosition,
    map: this.map,
    title: "Location",
    animation: google.maps.Animation.DROP,
});
