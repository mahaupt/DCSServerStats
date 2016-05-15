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
	// Github Project: https://github.com/cbacon93/BMSStats

	
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
		$result = $this->mysqli->query("SELECT * FROM bms_parser_log ORDER BY id DESC LIMIT 1");
		if ($row = $result->fetch_object()) {
		
			echo "Last update at " . date('G:i', $row->time) . " processed " . $row->events . " events in " . $row->durationms .  " ms";
		}
	}
	
	
	
	public function getPilotsTable() {
		$pilots = array();
		
		$result = $this->mysqli->query("SELECT * FROM bms_pilots WHERE name<>'AI' AND disp_name<>'AI' ORDER BY flighttime DESC");
		while($row = $result->fetch_object()) {
			$pilots[] = $row;
		}
		
		return $pilots;
	}
	
	
	
	public function getPilotsFlightsTable($pilotid = -1) {
		$flights = array();
		
		if ($pilotid > 0) {
			$prep = $this->mysqli->prepare("SELECT 0, '', aircrafts.name, flights.id, flights.takeofftime, flights.landingtime, flights.recordtime FROM bms_flights AS flights, bms_aircrafts AS aircrafts WHERE flights.pilotid=? AND aircrafts.id=flights.aircraftid ORDER BY flights.takeofftime DESC LIMIT 10");
			$prep->bind_param('i', $pilotid);
		} else {
			$prep = $this->mysqli->prepare("SELECT pilots.id AS pid, pilots.disp_name AS pname, aircrafts.name, flights.id, flights.takeofftime, flights.landingtime, flights.recordtime FROM bms_flights AS flights, bms_aircrafts AS aircrafts, bms_pilots AS pilots WHERE pilots.id=flights.pilotid AND aircrafts.id=flights.aircraftid AND pilots.name<>'AI' AND pilots.disp_name<>'AI' ORDER BY flights.takeofftime DESC LIMIT 30");
		}
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row_pilotid, $row_pilotname, $row_acname, $row_id, $row_takeofftime, $row_landingtime, $row_recordtime);
		
		while($prep->fetch()) {
			$flight = new stdClass();
			$flight->pilotid = $row_pilotid;
			$flight->pilotname = $row_pilotname;
			$flight->acname = $row_acname;
			$flight->id = $row_id;
			$flight->takeofftime = $row_takeofftime;
			$flight->landingtime = $row_landingtime;
			$flight->duration = $flight->landingtime - $flight->takeofftime;
			$flight->recordtime = $row_recordtime;
			$flights[] = $flight;
		}
		$prep->close();
		
		return $flights;
	}
	
	
	public function getPilotsAircraftTable($pilotid = -1) {
		$aircrafts = array();
		
		if ($pilotid > 0) {
			$prep = $this->mysqli->prepare("SELECT pilot_aircrafts.flights, aircrafts.name, pilot_aircrafts.flighttime FROM bms_pilot_aircrafts AS pilot_aircrafts, bms_aircrafts AS aircrafts, bms_pilots AS pilots WHERE pilot_aircrafts.pilotid=? AND pilots.id = pilot_aircrafts.pilotid AND pilot_aircrafts.aircraftid=aircrafts.id ORDER BY pilot_aircrafts.flighttime DESC");
			$prep->bind_param('i', $pilotid);
		} else {
			$prep = $this->mysqli->prepare("SELECT aircrafts.flights, aircrafts.name, aircrafts.flighttime FROM bms_aircrafts AS aircrafts ORDER BY aircrafts.flighttime DESC");
		}
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row_flights, $row_acname, $row_time);
		
		while($prep->fetch()) {
			$aircraft = new stdClass();
			$aircraft->flights = $row_flights;
			$aircraft->acname = $row_acname;
			$aircraft->time = $row_time;
			
			$aircrafts[] = $aircraft;
		}
		$prep->close();
		
		return $aircrafts;
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
		
		
		
	public function echoPilotsTable() {
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Pilot</th><th>Flights</th><th>Flight time</th><th>last active</th></tr>";
		
		$pilots = $this->getPilotsTable();
		
		foreach($pilots as $aid=>$pilot) {
						
			echo "<tr onclick=\"window.document.location='?pid=" . $pilot->id . "'\" class='table_row_" . $aid%2 . "'><td>" . $pilot->disp_name . "</td><td>" . $pilot->flights . "</td><td>" . $this->timeToString($pilot->flighttime) . "</td><td>" . date('d.m.Y', $pilot->lastactive) . "</td></tr>";
			
			
		}
		
		if (sizeof($pilots) == 0) {
			echo "<tr><td style='text-align: center' colspan='8'>No Pilots listed</td></tr>";
		}
		
		echo "</table>";
	}
	
	
	public function getPilotsStatistic($pilotid) {
		//get pilot information
		$prep = $this->mysqli->prepare("SELECT id, name, disp_name, flighttime, flights, lastactive FROM bms_pilots WHERE id=?");
		$prep->bind_param('i', $pilotid);
		$prep->execute();
		
		$row = new stdClass();
		$prep->bind_result($row->id, $row->name, $row->disp_name, $row->flighttime, $row->flights, $row->lastactive);
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
		echo "<tr class='table_header'><th>Aircraft</th><th>Takeoff</th><th>Landing</th><th>Duration</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . date('d.m.Y - H:i', $flight->takeofftime) . "</td><td>" . date('d.m.Y - H:i', $flight->landingtime) . "</td><td>" . $this->timeToString($flight->duration) . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Flights listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoFlightsTable() {	
		$flights = $this->getPilotsFlightsTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Record date</th><th>Pilot</th><th>Aircraft</th><th>Takeoff</th><th>Landing</th><th>Duration</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . date('d.m.Y', $flight->recordtime) . "</td><td>" . $flight->pilotname . "</td><td>" . $flight->acname . "</td><td>" . date('d.m.Y - H:i', $flight->takeofftime) . "</td><td>" . date('d.m.Y - H:i', $flight->landingtime) . "</td><td>" . $this->timeToString($flight->duration) . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='7'>No Flights listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoPilotsAircraftsTable($pilotid) {
		$flights = $this->getPilotsAircraftTable($pilotid);
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Aircraft</th><th>Flights</th><th>Flight time</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . $flight->flights . "</td><td>" . $this->timeToString($flight->time) . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Aircrafts listed</td></tr>";
		}
		
		
		echo "</table><br><br>";
	}
	
	
	public function echoAircraftsTable() {
		$flights = $this->getPilotsAircraftTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Aircraft</th><th>Flights</th><th>Flight time</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->acname . "</td><td>" . $flight->flights . "</td><td>" . $this->timeToString($flight->time) . "</td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='6'>No Aircrafts listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	
	public function echoPilotStatistic($pilotid) {
		
		if ($pilot = $this->getPilotsStatistic($pilotid)) {
			
			$pilotid = $pilot->id;
					
			echo "<h2>Pilot " . $pilot->disp_name . "</h2><br><br>";
			echo "<table class='table_stats'><tr class='table_row_0'><td>Total Flight Time: </td><td>" . $this->timeToString($pilot->flighttime) . "</td></tr>";
			echo "<tr class='table_row_1'><td>Flights: </td><td>" . $pilot->flights . "</td></tr>";
			echo "<tr class='table_row_0'><td>Last Activity: </td><td>" . date('d.m.Y', $pilot->lastactive) . "</td></tr>";
			echo "</table><br><br>";
			
			
			echo "<b>Last Flights:</b>";
			$this->echoPilotsFlightsTable($pilotid);
			
			echo "<b>Flown Airplanes</b>";
			$this->echoPilotsAircraftsTable($pilotid);
					
		} else {
			echo "Pilot not found!";
		}
	}
} 

class SimStatsAdmin extends SimStats {
	
	public function echoAdminPilotsTable() {
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>ID</th><th>Raw Pilot Name</th><th>Pilot Name</th><th>Actions</th><th>Transfer to Pilot ID</th></tr>";
		
		$pilots = $this->getPilotsTable();
		
		foreach($pilots as $aid=>$pilot) {
			
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $pilot->id . "</td><td>" . $pilot->name . "</td><td><form method='POST'><input type='text' value='" . $pilot->disp_name . "' name='newname'><input type='submit' value='rename' name='rename'><input type='hidden' name='pilotid' value='" . $pilot->id . "'></form></td><td><a href='?makeai=" . $pilot->id . "'>declare as AI</a> - <a href='?delete=" . $pilot->id . "'>remove</a></td><td><form method='POST'><input type='text' name='topilotid' value='0' style='width: 20px;'><input type='hidden' name='frompilotid' value='" . $pilot->id . "'><input type='submit' name='transferpilot' value='transfer'></form></td></tr>";
			
			
		}
		
		if (sizeof($pilots) == 0) {
			echo "<tr><td style='text-align: center' colspan='8'>No Pilots listed</td></tr>";
		}
		
		echo "</table>";
	}
	
	
	public function echoAdminFlightsTable() {
		$flights = $this->getPilotsFlightsTable();
		
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>ID</th><th>Pilot</th><th>Aircraft</th><th>Takeoff</th><th>Landing</th><th>Duration</th><th>Action</th></tr>";
		
		foreach($flights as $aid=>$flight) {
			echo "<tr class='table_row_" . $aid%2 . "'><td>" . $flight->id . "</td><td>" . $flight->pilotname . "</td><td>" . $flight->acname . "</td><td>" . date('d.m.Y - H:i', $flight->takeofftime) . "</td><td>" . date('d.m.Y - H:i', $flight->landingtime) . "</td><td>" . $this->timeToString($flight->duration) . "</td><td><a href=\"?flights&delete=" . $flight->id . "\">Delete</a></td></tr>";
		}
		
		if (sizeof($flights) == 0) {
			echo "<tr><td style='text-align: center' colspan='9'>No Flights listed</td></tr>";
		}
		
		echo "</table><br><br>";
	}
	
	
	public function echoAddFlight() {
		echo "<form action=\"?flights\" method=\"post\">";
		echo "<table class='table_stats'>";
		echo "<tr class='table_header'><th>Pilot</th><th>Aircraft</th><th>Takeoff</th><th>Landing</th><th>Action</th></tr>";
		
		//echo pilots
		echo "<tr><td>";
		$pilots = $this->getPilotsTable();
		echo "<select name='pilot'>";
		foreach($pilots as $pilot) {
			echo "<option value='" . $pilot->id . "'>" . $pilot->disp_name . "</option>";
		}
		echo "</select>";
		
		//echo aircrafts
		echo "</td><td>";
		$aircrafts = $this->getPilotsAircraftTable();
		echo "<select name='aircraft'>";
		foreach($aircrafts as $aircraft) {
			echo "<option value='" . $aircraft->id . "'>" . $aircraft->acname . "</option>";
		}
		echo "</select>";
		
		//takeoff time
		echo "</td><td>";
		echo "<input type='text' name='th' style='width:20px;' placeholder='HH'>:<input type='text' name='tm' style='width:20px;' placeholder='MM'><br>";
		echo "<input type='text' name='td' style='width:20px;' placeholder='DD'>.<input type='text' name='tmo' style='width:20px;' placeholder='MM'>.<input type='text' name='ty' style='width:40px;' placeholder='YYYY'>";
		
		//landing time
		echo "</td><td>";
		echo "<input type='text' name='lh' style='width:20px;' placeholder='HH'>:<input type='text' name='lm' style='width:20px;' placeholder='MM'><br>";
		echo "<input type='text' name='ld' style='width:20px;' placeholder='DD'>.<input type='text' name='lmo' style='width:20px;' placeholder='MM'>.<input type='text' name='ly' style='width:40px;' placeholder='YYYY'>";
		
		echo "</td><td><input type='submit' name='addflight' value='Add'></td></tr>";
		echo "</table>";
		echo "</form><br><br>";
	}
	
	
	public function addFlight($pilotid, $aircraftid, $takeofftime, $landingtime) {
		$duration = $landingtime - $takeofftime;
		$recordtime = time();
		if ($duration <= 0) {
			return "Time error";
		}
		
		$prep = $this->mysqli->prepare("UPDATE bms_pilots SET flights=flights+1, flighttime=flighttime+? WHERE id=?");
		$prep->bind_param('ii', $duration, $pilotid);
		$prep->execute();
		$prep->close();
		
		$prep = $this->mysqli->prepare("UPDATE bms_pilot_aircrafts SET flights=flights+1, flighttime=flighttime+? WHERE pilotid=? AND aircraftid=?");
		$prep->bind_param('iii', $duration, $pilotid, $aircraftid);
		$prep->execute();
		$prep->close();
		
		$prep = $this->mysqli->prepare("UPDATE bms_aircrafts SET flights=flights+1, flighttime=flighttime+? WHERE id=?");
		$prep->bind_param('ii', $duration, $aircraftid);
		$prep->execute();
		$prep->close();
		
		$prep = $this->mysqli->prepare("INSERT INTO bms_flights SET pilotid=?, aircraftid=?, takeofftime=?, landingtime=?, recordtime=?");
		$prep->bind_param('iiiii', $pilotid, $aircraftid, $takeofftime, $landingtime, $recordtime);
		$prep->execute();
		$prep->close();
	}
	
	
	public function removeFlight($flightid) {
		$prep = $this->mysqli->prepare("SELECT id, pilotid, aircraftid, takeofftime, landingtime FROM bms_flights WHERE id=?");
		$prep->bind_param('i', $flightid);
		$prep->execute();
		
		$prep->bind_result($row_id, $row_pilotid, $row_acid, $row_takeofftime, $row_landingtime);
		if ($prep->fetch()) {
			$prep->close();
			$row_duration = $row_landingtime - $row_takeofftime;
			
			$this->mysqli->query("UPDATE bms_pilots SET flighttime=flighttime-" . $row_duration . ", flights=flights-1 WHERE id=" . $row_pilotid);
			$this->mysqli->query("UPDATE bms_aircrafts SET flighttime=flighttime-" . $row_duration . ", flights=flights-1 WHERE id=" . $row_acid);
			$this->mysqli->query("UPDATE bms_pilot_aircrafts SET flighttime=flighttime-" . $row_duration . ", flights=flights-1 WHERE pilotid=" . $row_pilotid . " AND aircraftid=" . $row_acid);
			
			$this->mysqli->query("DELETE FROM bms_flights WHERE id=" . $row_id . " LIMIT 1");
			
			return true;
		}
		$prep->close();
		return false;
	}
	
	public function mergePilot($pilotid_from, $pilotid_to) {
		$prep = $this->mysqli->prepare("SELECT disp_name, SUM(flighttime), SUM(flights) FROM bms_pilots WHERE id IN (?, ?)");
		$prep->bind_param('ii', $pilotid_from, $pilotid_to);
		$prep->execute();
		$prep->bind_result($row_disp_name, $row_flighttime, $row_flights);
		
		if ($prep->fetch()) {
			$prep->close();
			
			//take new pilots data
			$prep2 = $this->mysqli->prepare("UPDATE bms_pilots SET disp_name=?, flighttime=?, flights=? WHERE id=?");
			$prep2->bind_param('siii', $row_disp_name, $row_flighttime, $row_flights, $pilotid_to);
			$prep2->execute();
			$prep2->close();
			
			//delete old pilot
			$prep3 = $this->mysqli->prepare("DELETE FROM bms_pilots WHERE id=? LIMIT 1");
			$prep3->bind_param('i', $pilotid_from);
			$prep3->execute();
			$prep3->close();
			
			//transfer flights
			$prep4 = $this->mysqli->prepare("UPDATE bms_flights SET pilotid=? WHERE pilotid=?");
			$prep4->bind_param('ii', $pilotid_to, $pilotid_from);
			$prep4->execute();
			$prep4->close();
			
			
			//transfer pilot aircrafts
			//get aircrafts
			$pilot_aircrafts = array();
			$prep8 = $this->mysqli->prepare("SELECT id, aircraftid, flights, flighttime FROM bms_pilot_aircrafts WHERE pilotid=?");
			$prep8->bind_param('i', $pilotid_from);
			$prep8->execute();
			$prep8->bind_result($row_id, $row_acid, $row_flights, $row_time);
			while($prep8->fetch()) {
				$pa = new stdClass();
				$pa->id = $row_id;
				$pa->acid = $row_acid;
				$pa->flights = $row_flights;
				$pa->time = $row_time;
				$pilot_aircrafts[] = $pa;
			}
			$prep8->close();
			
			
			//take aircraft data to new pilot
			foreach($pilot_aircrafts as $aircraft) {
				$prep9 = $this->mysqli->prepare("UPDATE bms_pilot_aircrafts SET flighttime=flighttime+?, flights=flights+? WHERE aircraftid=? AND pilotid=? LIMIT 1");
				$prep9->bind_param('iiii', $aircraft->time, $pa->flights, $aircraft->acid, $pilotid_to);
				$prep9->execute();
				
				//no pilot_aircraft entry, take old one
				if ($prep9->affected_rows < 1) {
					$prep9->close();
					$prep10 = $this->mysqli->prepare("UPDATE bms_pilot_aircrafts SET pilotid=? WHERE id=?");
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
		$prep1 = $this->mysqli->prepare("DELETE FROM bms_pilots WHERE id=? LIMIT 1");
		$prep1->bind_param('i', $pilotid);
		$prep1->execute();
		$prep1->close();

		$prep2 = $this->mysqli->prepare("DELETE FROM bms_flights WHERE pilotid=?");
		$prep2->bind_param('i', $pilotid);
		$prep2->execute();
		$prep2->close();
				
		$prep4 = $this->mysqli->prepare("DELETE FROM bms_pilot_aircrafts WHERE pilotid=?");
		$prep4->bind_param('i', $pilotid);
		$prep4->execute();
		$prep4->close();
	}
	
	
	public function renamePilot($pilotid, $name) {
		$prep = $this->mysqli->prepare("UPDATE bms_pilots SET disp_name=? WHERE id=? LIMIT 1");
		$prep->bind_param('si', $name, $pilotid);
		$prep->execute();
		$prep->close();
	}
	
	
	public function autoMergePilots() {
		$result = $this->mysqli->query("SELECT id, name FROM bms_pilots");
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