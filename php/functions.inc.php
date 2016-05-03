<?php
	// Copyright 2016 Marcel Haupt
	// http://marcel-haupt.eu/
	//
	// Licensed under the Apache License, Version 2.0 (the "License");
	// you may not use this file except in compliance with the License.
	// You may obtain a copy of the License at
	//
	// http ://www.apache.org/licenses/LICENSE-2.0
	//
	// Unless required by applicable law or agreed to in writing, software
	// distributed under the License is distributed on an "AS IS" BASIS,
	// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	// See the License for the specific language governing permissions and
	// limitations under the License.
	//
	// Github Project: https://github.com/cbacon93/DCSServerStats

	
class SimStats {
	protected $mysqli;
	
	public function SimStats(mysqli $mysqli) {
		$this->mysqli = $mysqli;
	}
	
	public function echoSiteContent() 
	{
		if (isset($_GET['pid'])) 
		{
			$this->echoPilotStatistic($_GET['pid']);
		}
		else if (isset($_GET['flightid'])) 
		{
			echo "<h2>Flight Details:</h2><br><br>";
			$this->echoFlightDetails($_GET['flightid']);
		}
		else if (isset($_GET['flights'])) 
		{
			echo "<h2>Flights</h2><br><br>";
			$this->echoFlightsTable();
		} 
		else if (isset($_GET['aircrafts'])) 
		{
			echo "<h2>Aircrafts</h2><br><br>";
			$this->echoAircraftsTable();
		} 
		else if (isset($_GET['weapons'])) 
		{
			echo "<h2>Weapons</h2><br><br>";
			$this->echoWeaponsTable();
		} 
		else if (isset($_GET['map'])) {
			echo "<h2>Live Radar Map</h2><br><br>";
			$this->echoLiveRadarMapScript();
		} 
		else
		{
			echo "<h2>Pilots</h2><br><br>";
			$this->echoPilotsTable();
		}
	}
		
	public static function timeToString($time) {
		$flight_hours = floor($time / 60 / 60);
		$flight_mins = floor($time / 60) - $flight_hours * 60;
		$flight_secs = $time  - $flight_mins * 60 - $flight_hours * 3600;
		
		if ($flight_mins < 10)
			$flight_mins = '0' . $flight_mins;
		if ($flight_secs < 10)
			$flight_secs = '0' . $flight_secs;
			
		return "$flight_hours:$flight_mins:$flight_secs";
	}
	
	
	function echoUpdateInfo() {
		$result = $this->mysqli->query("SELECT * FROM dcs_parser_log ORDER BY id DESC LIMIT 1");
		if ($row = $result->fetch_object()) {
		
			echo "Last update at " . date('G:i', $row->time) . " processed " . $row->events . " events in " . $row->durationms .  " ms";
		}
	}
	
	
	
	public function getPilotsTable() {
		$pilots = array();
		
		$result = $this->mysqli->query("SELECT * FROM pilots WHERE name<>'AI' AND disp_name<>'AI' ORDER BY flighttime DESC");
		while($row = $result->fetch_object()) {
			$pilots[] = $row;
		}
		
		return $pilots;
	}
	
	
	public function getWeaponsTable() {
		$weapons = array();
		
		$result = $this->mysqli->query("SELECT * FROM weapons ORDER BY hits DESC");
		while($row = $result->fetch_object()) {
			$weapons[] = $row;
		}
		
		return $weapons;
	}
	
	
	
	public function getPilotsFlightsTable($pilotid = -1) {
		$flights = array();
		
		if ($pilotid > 0) {
			$prep = $this->mysqli->prepare("SELECT 0, '', aircrafts.name, flights.coalition, flights.id, flights.takeofftime, flights.landingtime, flights.duration, flights.endofflighttype FROM flights, aircrafts WHERE flights.pilotid=? AND aircrafts.id=flights.aircraftid ORDER BY flights.takeofftime DESC LIMIT 10");
			$prep->bind_param('i', $pilotid);
		} else {
			$prep = $this->mysqli->prepare("SELECT pilots.id AS pid, pilots.disp_name AS pname, aircrafts.name, flights.coalition, flights.id, flights.takeofftime, flights.landingtime, flights.duration, flights.endofflighttype FROM flights, aircrafts, pilots WHERE pilots.id=flights.pilotid AND aircrafts.id=flights.aircraftid AND pilots.name<>'AI' AND pilots.disp_name<>'AI' ORDER BY flights.takeofftime DESC LIMIT 30");
		}
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row_pilotid, $row_pilotname, $row_acname, $row_coalition, $row_id, $row_takeofftime, $row_landingtime, $row_duration, $row_endofflighttype);
		
		while($prep->fetch()) {
			$flight = new stdClass();
			$flight->pilotid = $row_pilotid;
			$flight->pilotname = $row_pilotname;
			$flight->acname = $row_acname;
			$flight->coalition = $row_coalition;
			$flight->id = $row_id;
			$flight->takeofftime = $row_takeofftime;
			$flight->landingtime = $row_landingtime;
			$flight->duration = $row_duration;
			$flight->endofflighttype = $row_endofflighttype;
			$flights[] = $flight;
		}
		$prep->close();
		
		return $flights;
	}
	
	
	public function getPilotsAircraftTable($pilotid = -1) {
		$aircrafts = array();
		
		if ($pilotid > 0) {
			$prep = $this->mysqli->prepare("SELECT pilot_aircrafts.flights, aircrafts.name, pilot_aircrafts.time, pilot_aircrafts.ejects, pilot_aircrafts.crashes, pilot_aircrafts.kills, pilots.show_kills FROM pilot_aircrafts, aircrafts, pilots WHERE pilot_aircrafts.pilotid=? AND pilots.id = pilot_aircrafts.pilotid AND pilot_aircrafts.aircraftid=aircrafts.id ORDER BY pilot_aircrafts.time DESC");
			$prep->bind_param('i', $pilotid);
		} else {
			$prep = $this->mysqli->prepare("SELECT aircrafts.flights, aircrafts.name, aircrafts.flighttime, aircrafts.ejects, aircrafts.crashes, aircrafts.kills, 1 FROM aircrafts ORDER BY aircrafts.flighttime DESC");
		}
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row_flights, $row_acname, $row_time, $row_ejects, $row_crashes, $row_kills, $row_show_kills);
		
		while($prep->fetch()) {
			$aircraft = new stdClass();
			$aircraft->flights = $row_flights;
			$aircraft->acname = $row_acname;
			$aircraft->time = $row_time;
			$aircraft->ejects = $row_ejects;
			$aircraft->crashes = $row_crashes;
			$aircraft->kills = $row_kills;
			$aircraft->show_kills = $row_show_kills;
			
			$aircrafts[] = $aircraft;
		}
		$prep->close();
		
		return $aircrafts;
	}
	
	
	public function getActiveFlight($pilotid) {
		$prep = $this->mysqli->prepare("SELECT dcs_events.InitiatorCoa, dcs_events.InitiatorType, dcs_events.time FROM pilots, dcs_events WHERE pilots.id=? AND dcs_events.event IN ('S_EVENT_TAKEOFF', 'S_EVENT_BIRTH_AIRBORNE') AND dcs_events.InitiatorPlayer=pilots.name ORDER BY dcs_events.id DESC LIMIT 1");
		$prep->bind_param('i', $pilotid);
		$prep->execute();
	
		$row = new stdClass();
		$prep->bind_result($row->coalition, $row->actype, $row->takeofftime);
		
		//if active flight exists - return, otherwise fail
		if ($prep->fetch()) {
			$prep->close();
			return $row;
		}
		$prep->close();
		return false;
	}
	
	
	public function getFlightPath($pilotid, $aircraftid, $search_time, $raw_id) {
		$path = array();
		
		//get flight path line
		$prep = $this->mysqli->prepare("SELECT pd.missiontime, pd.lat, pd.lon FROM position_data AS pd WHERE pd.raw_id=? AND pd.time<=? AND pd.pilotid=? AND pd.aircraftid=? ORDER BY pd.time DESC"); //AND 
		$prep->bind_param('iiii', $raw_id, $search_time, $pilotid, $aircraftid);
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row_missiontime, $row_lat, $row_lon);
		
		$last_misst = 99999999;
		while($prep->fetch()) {
			//flight ended definately
			if ($last_misst < $row_missiontime) break;
			
			$point = new stdClass();
			$point->missiontime = $row_missiontime;
			$point->lat = $row_lat;
			$point->lon = $row_lon;
			$path[] = $point;
			
			$last_misst = $row_missiontime;
		}
		
		$prep->close();
		return $path;
	}
	
	
	public function getCurrentFlightPositions() {
		$flights = array();
		
		$result = $this->mysqli->query("SELECT pd.id, pd.lat, pd.lon, pilots.name as pname, aircrafts.name as acname, pd.raw_id, pd.pilotid, pd.aircraftid, pd.missiontime, pd.time FROM position_data AS pd, pilots, aircrafts WHERE pd.time>" . (time()-120) . " AND pd.pilotid=pilots.id AND aircrafts.id=pd.aircraftid AND pd.id IN (SELECT MAX(pd2.id) FROM position_data AS pd2 GROUP BY pd2.raw_id)");
		
		while($row = $result->fetch_object()) {
			$flights[] = $row;
		}
		
		return $flights;
	}
	
	
	public function quicksort($seq, $key) {
	    if(!count($seq)) return $seq;
		$pivot= $seq[0];
	    $low = array();
	    $high = array();
	    $length = count($seq);
	    for($i=1; $i < $length; $i++) {
	        if($seq[$i]->$key <= $pivot->$key) {
	            $low [] = $seq[$i];
	        } else {
	            $high[] = $seq[$i];
	        }
	    }
		return array_merge($this->quicksort($low, $key), array($pivot), $this->quicksort($high, $key));
	}
	
	
	
	public function getFlightData($flightid) {
		$flightData = new stdClass();
		$flightData->events = array();
		
		$prep = $this->mysqli->prepare("SELECT flights.pilotid, flights.aircraftid, flights.takeofftime, flights.landingtime, flights.duration, flights.coalition, flights.endofflighttype, flights.raw_id, aircrafts.name AS acname, pilots.disp_name AS pname FROM flights, pilots, aircrafts WHERE pilots.id=flights.pilotid AND aircrafts.id=flights.aircraftid AND flights.id=? LIMIT 1");
		
		$prep->bind_param('i', $flightid);
		$prep->execute();
		
		$prep->bind_result($row_pilotid, $row_aircraftid, $row_takeofftime, $row_landingtime, $row_duration, $row_coalition, $row_endofflighttype, $row_raw_id, $row_acname, $row_pname);
		if ($prep->fetch()) 
		{
			$prep->close();
			
			//general flight data
			$flightData->takeoff = $row_takeofftime;
			$flightData->landing = $row_landingtime;
			$flightData->duration = $row_duration;
			$flightData->aircraft = $row_acname;
			$flightData->pilot = $row_pname;
			$flightData->coalition = $row_coalition;
			$flightData->id = $flightid;
			$flightData->aircraftid = $row_aircraftid;
			$flightData->pilotid = $row_pilotid;
			$flightData->raw_id = $row_raw_id;
			
			//add takeoff
			$event = new stdClass();
			$event->time = $row_takeofftime;
			$event->event = "TAKEOFF";
			$event->initiatorpname = $flightData->pilot;
			$event->initiatoracname = $flightData->aircraft;
			$event->weapontype = "";
			$event->weaponname = "";
			$event->targetpname = "";
			$event->targetacname = "";
			$flightData->events[] = $event;
			
			
			//get outgoing shots
			$result = $this->mysqli->query("SELECT hsk.time, weapons.type AS weapontype, weapons.name AS weaponname, hsk.type AS event FROM hitsshotskills AS hsk, weapons WHERE hsk.initiatorPid=" . $row_pilotid . " AND hsk.initiatorAcid=" . $row_aircraftid . " AND hsk.time<" . $row_landingtime  . " AND hsk.time>" . $row_takeofftime . " AND hsk.weaponid=weapons.id AND hsk.type='SHOT'");
			while($row = $result->fetch_object()) {
				$row->incoming = false;
				$row->initiatorpname = $flightData->pilot;
				$row->initiatoracname = $flightData->aircraft;
				$row->targetpname = "";
				$row->targetacname = "";
				$flightData->events[] = $row;
			}
			
			
			//get outgoing hits and kills
			$result = $this->mysqli->query("SELECT hsk.time, weapons.type AS weapontype, weapons.name AS weaponname, hsk.type AS event, pilots.name AS targetpname, aircrafts.name AS targetacname FROM hitsshotskills AS hsk, weapons, aircrafts, pilots WHERE hsk.initiatorPid=" . $row_pilotid . " AND hsk.initiatorAcid=" . $row_aircraftid . " AND hsk.time<" . $row_landingtime  . " AND hsk.time>" . $row_takeofftime . " AND hsk.weaponid=weapons.id AND aircrafts.id=hsk.targetAcid AND pilots.id=hsk.targetPid AND hsk.type<>'SHOT'");
			while($row = $result->fetch_object()) {
				$row->incoming = false;
				$row->initiatorpname = $flightData->pilot;
				$row->initiatoracname = $flightData->aircraft;
				$flightData->events[] = $row;
			}
			
			//get incomming hits and kills
			$result = $this->mysqli->query("SELECT hsk.time, weapons.type AS weapontype, weapons.name AS weaponname, hsk.type AS event, pilots.name AS initiatorpname, aircrafts.name AS initiatoracname FROM hitsshotskills AS hsk, weapons, aircrafts, pilots WHERE hsk.targetPid=" . $row_pilotid . " AND hsk.targetAcid=" . $row_aircraftid . " AND hsk.time<" . $row_landingtime  . " AND hsk.time>" . $row_takeofftime . " AND hsk.weaponid=weapons.id AND aircrafts.id=hsk.initiatorAcid AND pilots.id=hsk.initiatorPid");
			while($row = $result->fetch_object()) {
				$row->incoming = true;
				$row->targetpname = $flightData->pilot;
				$row->targetacname = $flightData->aircraft;
				$flightData->events[] = $row;
			}
			
			//add landing
			$event = new stdClass();
			$event->time = $row_landingtime;
			$event->event = $row_endofflighttype;
			$event->initiatorpname = $flightData->pilot;
			$event->initiatoracname = $flightData->aircraft;
			$event->weapontype = "";
			$event->weaponname = "";
			$event->targetpname = "";
			$event->targetacname = "";
			$flightData->events[] = $event;
			
			
			$flightData->events = $this->quicksort($flightData->events, "time");
			
			return $flightData;
		}
		
		
		$prep->close();
		return false;
	}
		
		
		
	public function echoPilotsTable() {
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Pilot</th><th>Flights</th><th>Flight time</th><th>Kills</th><th>Ejections</th><th>Crashes</th><th>last active</th><th>status</th></tr>";
		
		$pilots = $this->getPilotsTable();
		
		foreach($pilots as $aid=>$pilot) {
			$onlinestatus = "<p class='pilot_offline'>On the Ground</p>";
			if ($pilot->online == 1)
				$onlinestatus = "<p class='pilot_online'>Flying</p>";
			
			if (!$pilot->show_kills) {
				$pilot->kills = '-';
				$pilot->ejects = '-';
				$pilot->crashes = '-';
			}
			
			echo "<tr onclick=\"window.document.location='?pid=" . $pilot->id . "'\" class='table_row_" . $aid%2 . "'><td>" . $pilot->disp_name . "</td><td>" . $pilot->flights . "</td><td>" . $this->timeToString($pilot->flighttime) . "</td><td>" . $pilot->kills . "</td><td>" . $pilot->ejects . "</td><td>" . $pilot->crashes . "</td><td>" . date('d.m.Y', $pilot->lastactive) . "</td><td>" . $onlinestatus . "</td></tr>";
			
			
		}
		
		if (sizeof($pilots) == 0) {
			echo "<tr><td style='text-align: center' colspan='8'>No Pilots listed</td></tr>";
		}
		
		echo "</table>";
	}
	
	
	public function getPilotsStatistic($pilotid) {
		//get pilot information
		$prep = $this->mysqli->prepare("SELECT id, name, disp_name, flighttime, flights, lastactive, online FROM pilots WHERE id=?");
		$prep->bind_param('i', $pilotid);
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row->id, $row->name, $row->disp_name, $row->flighttime, $row->flights, $row->lastactive, $row->online);
		if ($prep->fetch()) {
			$prep->close();
			return $row;
		}
		$prep->close();
		return false;
	}
	
	
	
	public function echoPilotsFlightsTable($pilotid) {	
		$flights = $this->getPilotsFlightsTable($pilotid);
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Aircraft</th><th>Coalition</th><th>Takeoff</th><th>Landing</th><th>Duration</th><th>Type of Landing</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr onclick=\"window.document.location='?flightid=" . $flight->id . "'\" class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . $flight->coalition . "</td><td>" . date('d.m.Y - H:i', $flight->takeofftime) . "</td><td>" . date('d.m.Y - H:i', $flight->landingtime) . "</td><td>" . $this->timeToString($flight->duration) . "</td><td>" . $flight->endofflighttype . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Flights listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoFlightsTable() {	
		$flights = $this->getPilotsFlightsTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Pilot</th><th>Aircraft</th><th>Coalition</th><th>Takeoff</th><th>Landing</th><th>Duration</th><th>Type of Landing</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr onclick=\"window.document.location='?flightid=" . $flight->id . "'\" class='table_row_" . $aid%2 . "'><td>" . $flight->pilotname . "</td><td>" . $flight->acname . "</td><td>" . $flight->coalition . "</td><td>" . date('d.m.Y - H:i', $flight->takeofftime) . "</td><td>" . date('d.m.Y - H:i', $flight->landingtime) . "</td><td>" . $this->timeToString($flight->duration) . "</td><td>" . $flight->endofflighttype . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='7'>No Flights listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoPilotsAircraftsTable($pilotid) {
		$flights = $this->getPilotsAircraftTable($pilotid);
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Aircraft</th><th>Flights</th><th>Flight time</th><th>Kills</th><th>Ejections</th><th>Crashes</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			if (!$flight->show_kills) {
				$flight->crashes = '-';
				$flight->ejects = '-';
				$flight->kills = '-';
			}
			
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . $flight->flights . "</td><td>" . $this->timeToString($flight->time) . "</td><td>" . $flight->kills . "</td><td>" . $flight->ejects . "</td><td>" . $flight->crashes . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Aircrafts listed</td></tr>";
		}
		
		
		echo "</table><br><br>";
	}
	
	
	public function echoAircraftsTable() {
		$flights = $this->getPilotsAircraftTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Aircraft</th><th>Flights</th><th>Flight time</th><th>Kills</th><th>Ejections</th><th>Crashes</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . $flight->flights . "</td><td>" . $this->timeToString($flight->time) . "</td><td>" . $flight->kills . "</td><td>" . $flight->ejects . "</td><td>" . $flight->crashes . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Aircrafts listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoActiveFlight($pilotid) {
				
		if ($flight = $this->getActiveFlight($pilotid)) {
			$duration = time() - $flight->takeofftime;
			echo "<b>Active Flight:</b> <br>";
			echo "<table class='table_stats'><tr class='table_header'><th>Aircraft</th><th>Coalition</th><th>Takeoff</th><th>Duration</th></tr>";
			echo "<tr><td>" . $flight->actype . "</td><td>" . $flight->coalition . "</td>";
			echo "<td>" . date('H:i d.m.Y', $flight->takeofftime) . "</td>";
			echo "<td><p class='js_timer'>" . $this->timeToString($duration) . "</p></td></tr></table>";
				
			echo "<br><br>";
		}
		
	}
	
	
	public function echoPilotStatistic($pilotid) {
		
		if ($pilot = $this->getPilotsStatistic($pilotid)) {
			
			$pilotid = $pilot->id;
			$online = $pilot->online;
			$onlinestatus = "<p class='pilot_offline'>On the Ground</p>";
			if ($pilot->online == 1)
				$onlinestatus = "<p class='pilot_online'>Flying</p>";
			
			echo "<h2>Pilot " . $pilot->disp_name . "</h2><br><br>";
			echo "<table class='table_stats'><tr class='table_row_0'><td>Total Flight Time: </td><td>" . $this->timeToString($pilot->flighttime) . "</td></tr>";
			echo "<tr class='table_row_1'><td>Flights: </td><td>" . $pilot->flights . "</td></tr>";
			echo "<tr class='table_row_0'><td>Last Activity: </td><td>" . date('d.m.Y', $pilot->lastactive) . "</td></tr>";
			echo "<tr class='table_row_1'><td>Status: </td><td>" . $onlinestatus . "</td></tr></table>";
			echo "<br><br>";
			
			
			//try to print active flight
			if ($online == 1) {
				$this->echoActiveFlight($pilotid);
			}
			
			echo "<b>Last Flights:</b>";
			$this->echoPilotsFlightsTable($pilotid);
			
			echo "<b>Flown Airplanes</b>";
			$this->echoPilotsAircraftsTable($pilotid);
					
		} else {
			echo "Pilot not found!";
		}
	}
	
	
	public function echoWeaponsTable() {
		$weapons = $this->getWeaponsTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Weapon</th><th>Category</th><th>Shots</th><th>Hits</th><th>Kills</th></tr>";
		
		foreach($weapons as $aid=>$weapon) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $weapon->name . "</td><td>" . $weapon->type . "</td><td>" . $weapon->shots . "</td><td>" . $weapon->hits . "</td><td>" . $weapon->kills . "</td></tr>";
		}
		
		if (sizeof($weapons) == 0) {
			echo "<tr><td style='text-align: center' colspan='5'>No Weapons listed</td></tr>";
		}
		
		echo "</table><br><em>Gunshots are not counted.</em><br>";
	}
	
	
	public function echoLiveRadarMapScript() {
		echo "<br><a href='#' onclick=\"setMapCenter({lat: 42.858056, lng: 41.128056});setMapZoom(7);flushMapChanges();\">Caucasus</a> - <a href='#' onclick=\"setMapCenter({lat: 38.18638677, lng: -115.16967773});setMapZoom(7);flushMapChanges();\">Nevada</a><br>";
		
		echo "<div id=\"map\"></div>";
		echo "<script src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyBZoFosVL27IeHx57Wujg-v_aW3slJWItA&callback=initLiveRadarMap\"
	        async defer></script>";
	}
	
	public function echoMapScriptForFlight($flightid) {
		echo "<br><a href='#' onclick=\"setMapCenter({lat: 42.858056, lng: 41.128056});setMapZoom(6);flushMapChanges();\">Caucasus</a> - <a href='#' onclick=\"setMapCenter({lat: 38.18638677, lng: -115.16967773});setMapZoom(6);flushMapChanges();\">Nevada</a><br>";
		
		echo "<div id=\"map\" style=\"width: 600px; height: 400px;\"></div>";
		echo "<script>setFlightId(" . $flightid . ");setMapZoom(6);</script>";
		echo "<script src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyBZoFosVL27IeHx57Wujg-v_aW3slJWItA&callback=initFlightPathMap\"
	        async defer></script>";
	}
	
	public function getLiveRadarMapInfoJSON() {
		$flights = $this->getCurrentFlightPositions();
		
		$json = "{\"flights\": [\n";
		
		foreach($flights as $id=>$flight) {
			
			//comma separator
			if ($id != 0) {$json .=  ",";}
			
			//pilot information
			$json .= "{\"pilot\": \"" . $flight->pname . "\",\"ac\": \"" . $flight->acname . "\",\"lat\": " . $flight->lat . ",\"lng\": " . $flight->lon . ",\"path\": [";
			
			
			//$text = "<table><tr><td>Pilot:</td><td>" . $flight->pname . "</td></tr><tr><td>Aircraft:</td><td>" . $flight->acname . "</td></tr></table>";
			
			//get flight path
			$fpath = $this->getFlightPath($flight->pilotid, $flight->aircraftid, $flight->time, $flight->raw_id);
			foreach($fpath as $aid=>$pt) {
				//comma separator
				if ($aid != 0) {$json .=  ",";}
				
				$json .= "{\"lat\": " . $pt->lat . ",\"lng\": " . $pt->lon . "}";
			}
			$json .= "]}\n";
	
		}
		
		$json .= "]}";
		
		return $json;
	}
	
	
	public function getFlightPathMapInfoJSON($flightid) {
		if ($flightDetail = $this->getFlightData($flightid)) {
		
			$json  = "{\"pilot\": \"" . $flightDetail->pilot . "\",";
			$json .= "\"ac\": \"" . $flightDetail->aircraft . "\",";
			$json .= "\"path\": [";
			
	
	
			//get flight path
			$fpath = $this->getFlightPath($flightDetail->pilotid, $flightDetail->aircraftid, $flightDetail->landing, $flightDetail->raw_id);
			foreach($fpath as $aid=>$pt) {
				//comma separator
				if ($aid != 0) {$json .=  ",";}
				
				$json .= "{\"lat\": " . $pt->lat . ",\"lng\": " . $pt->lon . "}";
			}
			$json .= "]}\n";
		
	
			
			return $json;
		}
	}
	
	
	public function echoFlightDetails($flightid) {
		
		if ($flightDetail = $this->getFlightData($flightid)) {
		
		
			echo "<b>Data:</b>";
			echo "<table class='table_stats'><tr class='table_row_0'><td>Takeoff: </td><td>" . date('d.m.Y - H:i', $flightDetail->takeoff) . "</td></tr>";
			echo "<tr class='table_row_1'><td>Duration: </td><td>" . $this->timeToString($flightDetail->duration)  . "</td></tr>";
			echo "<tr class='table_row_0'><td>Landing: </td><td>" . date('d.m.Y - H:i', $flightDetail->landing) . "</td></tr>";
			echo "<tr class='table_row_1'><td>Pilot: </td><td>" . $flightDetail->pilot . "</td></tr>";
			echo "<tr class='table_row_0'><td>Aircraft: </td><td>" . $flightDetail->aircraft . "</td></tr>";
			echo "<tr class='table_row_1'><td>Coalition: </td><td>" . $flightDetail->coalition . "</td></tr></table><br><br>";
			
			echo "<b>Flight Path:</b>";
			$this->echoMapScriptForFlight($flightDetail->id);
			
			
			echo "<br><br><b>Events:</b>";
			
			echo "<table class='table_stats'><tr class='table_header'><th>Time</th><th>Event</th><th>Initiator</th><th>Initiator Aircraft</th><th>Weapontype</th><th>Weapon</th><th>Target</th><th>Target Aircraft</th></tr>";
			
			foreach($flightDetail->events as $aid=>$event) {
				echo "<tr class='table_row_" . $aid%2 . "'><td>" . date('d.m.Y - H:i', $event->time) . "</td><td><b>" . $event->event . "</b></td><td>" . $event->initiatorpname . "</td><td>" . $event->initiatoracname . "</td><td>" . $event->weapontype . "</td><td>" . $event->weaponname . "</td><td>" . $event->targetpname . "</td><td>" . $event->targetacname . "</td></tr>";
			}
			
			echo "</table>";
		} else {
			echo "No data available;";
		}
	}
}


class SimStatsAdmin extends SimStats {
	
	public function echoAdminPilotsTable() {
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>ID</th><th>Raw Pilot Name</th><th>Pilot Name</th><th>status</th><th>Actions</th><th>Transfer to Pilot ID</th></tr>";
		
		$pilots = $this->getPilotsTable();
		
		foreach($pilots as $aid=>$pilot) {
			$onlinestatus = "<p class='pilot_offline'>On the Ground</p>";
			if ($pilot->online == 1)
				$onlinestatus = "<p class='pilot_online'>Flying</p>";
			
			$shkstring = "hide";
			if (!$pilot->show_kills) {
				$pilot->kills = '-';
				$pilot->ejects = '-';
				$pilot->crashes = '-';
				
				$shkstring = "show";
			}
			
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $pilot->id . "</td><td>" . $pilot->name . "</td><td><form method='POST'><input type='text' value='" . $pilot->disp_name . "' name='newname'><input type='submit' value='rename' name='rename'><input type='hidden' name='pilotid' value='" . $pilot->id . "'></form></td><td>" . $onlinestatus . "</td><td><a href='?makeai=" . $pilot->id . "'>declare as AI</a> - <a href='?showhidekills=" . $pilot->id . "&showkills=" . (int)(!$pilot->show_kills) . "'>" . $shkstring . " kills</a> - <a href='?delete=" . $pilot->id . "'>remove</a> - <a href='?forceland=" . $pilot->id . "'>forceland</a></td><td><form method='POST'><input type='text' name='topilotid' value='0' style='width: 20px;'><input type='hidden' name='frompilotid' value='" . $pilot->id . "'><input type='submit' name='transferpilot' value='transfer'></form></td></tr>";
			
			
		}
		
		if (sizeof($pilots) == 0) {
			echo "<tr><td style='text-align: center' colspan='8'>No Pilots listed</td></tr>";
		}
		
		echo "</table>";
	}
	
	
	public function removeFlight($flightid) {
		$prep = $this->mysqli->prepare("SELECT id, pilotid, aircraftid, duration, takeofftime, landingtime FROM flights WHERE id=?");
		$prep->bind_param('i', $flightid);
		$prep->execute();
		
		$prep->bind_result($row_id, $row_pilotid, $row_acid, $row_duration, $row_takeofftime, $row_landingtime);
		if ($prep->fetch()) {
			$prep->close();
			
			$this->mysqli->query("UPDATE pilots SET flighttime=flighttime-" . $row_duration . ", flights=flights-1 WHERE id=" . $row_pilotid);
			$this->mysqli->query("UPDATE aircrafts SET flighttime=flighttime-" . $row_duration . ", flights=flights-1 WHERE id=" . $row_acid);
			$this->mysqli->query("UPDATE pilot_aircrafts SET time=time-" . $row_duration . ", flights=flights-1 WHERE pilotid=" . $row_pilotid . " AND aircraftid=" . $row_acid);
			
			$this->mysqli->query("DELETE FROM flights WHERE id=" . $row_id . " LIMIT 1");
			
			return true;
		}
		$prep->close();
		return false;
	}
	
	public function mergePilot($pilotid_from, $pilotid_to) {
		$prep = $this->mysqli->prepare("SELECT disp_name, SUM(flighttime), SUM(flights), SUM(crashes), SUM(ejects), SUM(hits), SUM(shots), SUM(kills), SUM(inc_hits), SUM(inc_kills), SUM(show_kills) FROM pilots WHERE id IN (?, ?)");
		$prep->bind_param('ii', $pilotid_from, $pilotid_to);
		$prep->execute();
		$prep->bind_result($row_disp_name, $row_flighttime, $row_flights, $row_crashes, $row_ejects, $row_hits, $row_shots, $row_kills, $row_inc_hits, $row_inc_kills, $row_show_kills);
		
		if ($prep->fetch()) {
			$prep->close();
			
			//
			$show_kills = 0;
			if ($row_show_kills == 2) {
				$show_kills = 1;
			}
			
			//take new pilots data
			$prep2 = $this->mysqli->prepare("UPDATE pilots SET disp_name=?, flighttime=?, flights=?, crashes=?, ejects=?, hits=?, shots=?, kills=?, inc_hits=?, inc_kills=?, show_kills=? WHERE id=?");
			$prep2->bind_param('siiiiiiiiiii', $row_disp_name, $row_flighttime, $row_flights, $row_crashes, $row_ejects, $row_hits, $row_shots, $row_kills, $row_inc_hits, $row_inc_kills, $show_kills, $pilotid_to);
			$prep2->execute();
			$prep2->close();
			
			//delete old pilot
			$prep3 = $this->mysqli->prepare("DELETE FROM pilots WHERE id=? LIMIT 1");
			$prep3->bind_param('i', $pilotid_from);
			$prep3->execute();
			$prep3->close();
			
			//transfer flights
			$prep4 = $this->mysqli->prepare("UPDATE flights SET pilotid=? WHERE pilotid=?");
			$prep4->bind_param('ii', $pilotid_to, $pilotid_from);
			$prep4->execute();
			$prep4->close();
			
			//transfer hits shots kills
			$prep5 = $this->mysqli->prepare("UPDATE hitsshotskills SET initiatorPid=? WHERE initiatorPid=?");
			$prep5->bind_param('ii', $pilotid_to, $pilotid_from);
			$prep5->execute();
			$prep5->close();
			
			//transfer incoming hits shots kills
			$prep6 = $this->mysqli->prepare("UPDATE hitsshotskills SET targetPid=? WHERE targetPid=?");
			$prep6->bind_param('ii', $pilotid_to, $pilotid_from);
			$prep6->execute();
			$prep6->close();
			
			//transfer incoming hits shots kills
			$prep7 = $this->mysqli->prepare("UPDATE position_data SET pilotid=? WHERE pilotid=?");
			$prep7->bind_param('ii', $pilotid_to, $pilotid_from);
			$prep7->execute();
			$prep7->close();
			
			//transfer pilot aircrafts
			//get aircrafts
			$pilot_aircrafts = array();
			$prep8 = $this->mysqli->prepare("SELECT id, aircraftid, flights, time, crashes, ejects, hits, shots, kills, inc_hits, inc_kills FROM pilot_aircrafts WHERE pilotid=?");
			$prep8->bind_param('i', $pilotid_from);
			$prep8->execute();
			$prep8->bind_result($row_id, $row_acid, $row_flights, $row_time, $row_crashes, $row_ejects, $row_hits, $row_shots, $row_kills, $row_inc_hits, $row_inc_kills);
			while($prep8->fetch()) {
				$pa = new stdClass();
				$pa->id = $row_id;
				$pa->acid = $row_acid;
				$pa->flights = $row_flights;
				$pa->time = $row_time;
				$pa->crashes = $row_crashes;
				$pa->ejects = $row_ejects;
				$pa->hits = $row_hits;
				$pa->shots = $row_shots;
				$pa->kills = $row_kills;
				$pa->inc_hits = $row_inc_hits;
				$pa->inc_kills = $row_inc_kills;
				$pilot_aircrafts[] = $pa;
			}
			$prep8->close();
			
			
			//take aircraft data to new pilot
			foreach($pilot_aircrafts as $aircraft) {
				$prep9 = $this->mysqli->prepare("UPDATE pilot_aircrafts SET time=time+?, flights=flights+?, crashes=crashes+?, ejects=ejects+?, hits=hits+?, shots=shots+?, kills=kills+?, inc_hits=inc_hits+?, inc_kills=inc_kills+? WHERE aircraftid=? AND pilotid=? LIMIT 1");
				$prep9->bind_param('iiiiiiiiiii', $aircraft->time, $pa->flights, $aircraft->crashes, $aircraft->ejects, $aircraft->hits, $aircraft->shots, $aircraft->kills, $aircraft->inc_hits, $aircraft->inc_kills, $aircraft->acid, $pilotid_to);
				$prep9->execute();
				
				//no pilot_aircraft entry, take old one
				if ($prep9->affected_rows < 1) {
					$prep9->close();
					$prep10 = $this->mysqli->prepare("UPDATE pilot_aircrafts SET pilotid=? WHERE id=?");
					$prep10->bind_param('ii', $pilotid_to, $aircraft->id);
				}
				$prep9->close();
			}
			
			
			
			//remove old pilot
			$this->removePilot($pilotid_from);
			
			return true;
		}
		$prep->close();
		return false;
	}
	
	public function removePilot($pilotid) {
		$prep1 = $this->mysqli->prepare("DELETE FROM pilots WHERE id=? LIMIT 1");
		$prep1->bind_param('i', $pilotid);
		$prep1->execute();
		$prep1->close();

		$prep2 = $this->mysqli->prepare("DELETE FROM flights WHERE pilotid=?");
		$prep2->bind_param('i', $pilotid);
		$prep2->execute();
		$prep2->close();
		
		$prep3 = $this->mysqli->prepare("DELETE FROM position_data WHERE pilotid=?");
		$prep3->bind_param('i', $pilotid);
		$prep3->execute();
		$prep3->close();
		
		$prep4 = $this->mysqli->prepare("DELETE FROM pilot_aircrafts WHERE pilotid=?");
		$prep4->bind_param('i', $pilotid);
		$prep4->execute();
		$prep4->close();
	}
	
	public function landFlight($pilotid, $landingtime) {
		$prep = $this->mysqli->prepare("SELECT events.id, events.missiontime, events.time, events.InitiatorID, events.InitiatorCoa, events.InitiatorGroupCat, events.InitiatorType, events.InitiatorPlayer FROM dcs_events AS events, pilots WHERE events.event IN ('S_EVENT_TAKEOFF', 'S_EVENT_BIRTH_AIRBORNE') AND events.InitiatorPlayer=pilots.name AND pilots.id=? LIMIT 1");
		$prep->bind_param('i', $pilotid);
		$prep->execute();
		
		$prep->bind_result($row_id, $row_missiontime, $row_takeofftime, $row_iniId, $row_iniCoa, $row_iniGC, $row_iniType, $row_iniPlayer);
		if ($prep->fetch()) {
			$landing_missiontime = $landingtime - $row_takeofftime + $row_missiontime;
			$prep->close();
			
			$prep2 = $this->mysqli->prepare("INSERT INTO dcs_events SET time=?, missiontime=?, event='S_EVENT_LAND', InitiatorID=?, InitiatorCoa=?, InitiatorGroupCat=?, InitiatorType=?, InitiatorPlayer=?");
			$prep2->bind_param('iisssss', $landingtime, $landing_missiontime, $row_iniId, $row_iniCoa, $row_iniGC, $row_iniType, $row_iniPlayer);
			$prep2->execute();
			$prep2->close();
			return true;
		}
		$prep->close();
		return false;
	}
	
	public function setShowKillsFlag($pilotid, $show_kills) {
		$prep = $this->mysqli->prepare("UPDATE pilots SET show_kills=? WHERE id=? LIMIT 1");
		$prep->bind_param('ii', $show_kills, $pilotid);
		$prep->execute();
		$prep->close();
	}
	
	
	public function renamePilot($pilotid, $name) {
		$prep = $this->mysqli->prepare("UPDATE pilots SET disp_name=? WHERE id=? LIMIT 1");
		$prep->bind_param('si', $name, $pilotid);
		$prep->execute();
		$prep->close();
	}
	
	
	public function autoMergePilots() {
		$result = $this->mysqli->query("SELECT id, name FROM pilots");
		$pilots = array();
		while($row = $result->fetch_object()) {
			//get raw name
			$name = strtolower($row->name);
			$name = str_replace("vjs", "", $name);
			$name = str_replace("vjg", "", $name);
			$name = str_replace("vj", "", $name);
			$name = str_replace("161", "", $name);
			$name = str_replace("162", "", $name);
			$name = str_replace("16", "", $name);
			$name = str_replace("-", "", $name);
			$name = str_replace("_", "", $name);
			$name = str_replace(" ", "", $name);
			$name = str_replace("|", "", $name);
			
			//save data
			$pilot = new stdClass();
			$pilot->id = $row->id;
			$pilot->name = $name;
			$pilots[] = $pilot;
		}
		
		//search for duplicates
		for ($i = 0; $i < sizeof($pilots)-1; $i++) {
			for ($j = $i+1; $j < sizeof($pilots); $j++) {
				//discovered duplicate pilot
				if ($pilots[$i]->name == $pilots[$j]->name && $pilots[$i]->id != $pilots[$j]->id) {
					$this->mergePilot($pilots[$i]->id, $pilots[$j]->id);
				}
			}
		}
	}
}
 	
?>