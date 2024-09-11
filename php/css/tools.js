var map;
var markers = [];
var mapCenter = [38.18638677, -115.16967773];
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
	map = L.map('map').setView(mapCenter, mapZoom);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		minZoom: 0,
		maxZoom: 17,
		attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors',
		}).addTo(map);
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
	// TODO: optimize this function

  // 	var flightPath = new google.maps.Polyline({
	// 	path: pathline,
	// 	geodesic: true,
	// 	strokeColor: '#FF0000',
	// 	strokeOpacity: 1.0,
	// 	strokeWeight: 2
	// });
	L.polyline(pathline, {color: '#FF0000', weight: 2, opacity: 1.0}).addTo(map);
  	
	var marker = L.marker([lat, lng], {title: name});
	marker.addTo(map);
	marker.bindPopup(text);
	if (show_always) marker.openPopup();
	markers.push(marker);
}

// Deletes all markers in the array from the map
function deleteMarkers() {
	for (var i = 0; i < markers.length; i++) {
		map.removeLayer(markers[i])
	}
	markers = [];
}