var map;
var markers = [];
var mapCenter = {lat: 38.18638677, lng: -115.16967773};
var mapZoom = 7;
var flightId = -1;


function timer() {
	window.setTimeout(timer, 1000);
	
	var elements = document.getElementsByClassName('js_timer');
	
	for(i = 0; i < elements.length; i++) {
		var tstr = elements[i].innerHTML;
		var split = tstr.split(':');
		var hours = parseInt(split[0]);
		var mins = parseInt(split[1]);
		var secs = parseInt(split[2]);
		
		//count up
		secs += 1;
		if (secs >= 60) { mins += 1; secs -= 60; }
		if (mins >= 60) { hours += 1; mins -= 60; }
		
		//two zeros
		if (secs < 10) {
			secs = "0" + secs;
		}
		if (mins < 10) {
			mins = "0" + mins;
		}
		
		//set new time
		elements[i].innerHTML = hours + ":" + mins + ":" + secs;
	}
	
	elements = document.getElementsByClassName('js_timer_down');
	
	for(i = 0; i < elements.length; i++) {
		var tstr = elements[i].innerHTML;
		var split = tstr.split(':');
		var hours = parseInt(split[0]);
		var mins = parseInt(split[1]);
		var secs = parseInt(split[2]);
		
		if (secs <= 0 && mins <= 0 && hours <= 0) continue;
		
		//count up
		secs -= 1;
		if (secs < 0) { mins -= 1; secs += 60; }
		if (mins < 0) { hours -= 1; mins += 60; }
		
		//two zeros
		if (secs < 10) {
			secs = "0" + secs;
		}
		if (mins < 10) {
			mins = "0" + mins;
		}
		
		//set new time
		elements[i].innerHTML = hours + ":" + mins + ":" + secs;
	}
}


function flushMapChanges() {
	map.panTo(mapCenter);
	map.setZoom(mapZoom);
}

function setMapCenter(center) {
	mapCenter = center;
}

function setMapZoom(zoom) {
	mapZoom = zoom;
}

function setFlightId(fid) {
	flightId = fid;
}

function initLiveRadarMap() {
	initMap();
	loadLiveRadarMapInfo();
}

function initFlightPathMap() {
	initMap();
	loadFlightPathMap();
}

function initMap() {
	// Specify features and elements to define styles.
	var customMapType = new google.maps.StyledMapType([
	{
		featureType: "all",
		stylers: [
			{ saturation: -80 }
		]
	},{
		featureType: "road.arterial",
		elementType: "geometry",
		stylers: [
			{ hue: "#00ffee" },
			{ saturation: 50 }
			]
	},{
		featureType: "poi.business",
		elementType: "labels",
		stylers: [
			{ visibility: "off" }
		]
	}
	], {
		name: 'Radar'
	});
	var customMapTypeId = 'custom_style';
	
	//create map
	map = new google.maps.Map(document.getElementById('map'), {
		center: mapCenter,
		zoom: mapZoom,
		mapTypeControlOptions: {
			mapTypeIds: [google.maps.MapTypeId.ROADMAP, customMapTypeId]
		}
	});
	map.mapTypes.set(customMapTypeId, customMapType);
	map.setMapTypeId(customMapTypeId);
}


function loadLiveRadarMapInfo() {
	//load new map info every 30 secs
	window.setTimeout(loadLiveRadarMapInfo, 30000);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			deleteMarkers();
			var json = JSON.parse(xhttp.responseText);
			for (i=0; i < json.flights.length; i++) {
				var text = "<table><tr><td>Pilot:</td><td>" + json.flights[i].pilot + "</td></tr><tr><td>Aircraft:</td><td>" + json.flights[i].ac + "</td></tr></table>";
				
				addMapMarker(json.flights[i].lat, json.flights[i].lng, json.flights[i].pilot, text, json.flights[i].path, false);
			}
		}
	};
	xhttp.open("GET", "index.php?mapjson", true);
	xhttp.send();
}


function loadFlightPathMap() {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			deleteMarkers();
			var json = JSON.parse(xhttp.responseText);
			var text = "<b>Landing Point</b><table><tr><td>Pilot:</td><td>" + json.pilot + "</td></tr><tr><td>Aircraft:</td><td>" + json.ac + "</td></tr></table>";
			addMapMarker(json.path[0].lat, json.path[0].lng, json.pilot, text, json.path, true);
		}
	};
	xhttp.open("GET", "index.php?mapjsonid=" + flightId, true);
	xhttp.send();
}


function addMapMarker(lat, lng, name, text, pathline, show_always) {
	var infowindow = new google.maps.InfoWindow({
    	content: text
  	});
  	
  	var flightPath = new google.maps.Polyline({
		path: pathline,
		geodesic: true,
		strokeColor: '#FF0000',
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
  	
	var marker = new google.maps.Marker({
		map: map,
		position: {lat: lat, lng: lng},
		title: name
	});
	
	marker.addListener('click', function() {
    	infowindow.open(map, marker);
  	});
  	
  	if (!show_always) {
	  	marker.addListener('mouseover', function() {
		  	flightPath.setMap(map);
	  	});
	  	
	  	marker.addListener('mouseout', function() {
		  	flightPath.setMap(null);
	  	});
  	} else {
	  	flightPath.setMap(map);
  	}
  	
  	markers.push(marker);
}


// Sets the map on all markers in the array.
function setMapOnAll(map) {
	for (var i = 0; i < markers.length; i++) {
		markers[i].setMap(map);
	}
}

// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
	setMapOnAll(null);
	markers = [];
}