function initialize(latitude, longitude, zoomfaktor) {
	var mapOptions = {
		zoom: parseInt(zoomfaktor),
		center: new google.maps.LatLng(latitude, longitude)
	}
	var map = new google.maps.Map(document.getElementById('map-canvas'),
		mapOptions);

	setMarkers(map, dkd.solrgeo);
}

function setMarkers(map, locations) {
	var shape = {
		coord: [1, 1, 1, 20, 18, 20, 18 , 1],
		type: 'poly'
	};
	for (var i = 0; i < locations.length; i++) {
		var location = locations[i];
		var myLatLng = new google.maps.LatLng(location[1], location[2]);
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			shape: shape,
			title: location[0]
		});
	}
}

if (typeof(dkd) != "undefined") {
	if (!jQuery.isEmptyObject(dkd.solrgeo.location)) {
		google.maps.event.addDomListener(window, 'load',
			function () { initialize(dkd.solrgeo.location[0],dkd.solrgeo.location[1],dkd.solrgeo.zoom['zoom']); }
		);
	}
}
