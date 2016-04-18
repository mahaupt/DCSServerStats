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
	

class DCSStatsCron {
	private $mysqli;
	private $event_table;
	
	public function DCSStatsCron(mysqli $mysqli, $event_table) {
		$this->mysqli = $mysqli;
		$this->event_table = $event_table;
	}
	
	
	public function startProcessing() {
		//sparsing log data
		$dcs_parser_log = new stdClass();
		$dcs_parser_log->time = time();
		$dcs_parser_log->starttimems = microtime(true) * 1000;
		
		
		//lock tables
		//$this->mysqli->query("LOCK TABLES dcs_events WRITE, pilots READ, aircrafts");
	
		
		//get all objects from database
		$dcs_events = $this->getAllDbObjects();
		$dcs_parser_log->events = sizeof($dcs_events);
		
		//add new weapons, pilots, aircrafts to database
		$this->addNewEntrys();
		//update counters
		$this->updateCounters();
		//process hits shots and kills
		$this->addHitsShotsKills($dcs_events);
		//process landing and takeoff times
		$this->calculateLandingTime($dcs_events);
		//delete events
		$this->deleteProcessedEvents();
		
		
		//unlock tables
		//$this->mysqli->query("UNLOCK TABLES");
		
		
		//end parsing
		$dcs_parser_log->endtimems = microtime(true) * 1000;
		$dcs_parser_log->durationms = round($dcs_parser_log->endtimems - $dcs_parser_log->starttimems);
		
		//write log entry
		$query = "INSERT INTO dcs_parser_log SET time='" . $dcs_parser_log->time . "', durationms='" . $dcs_parser_log->durationms . "', events='" . $dcs_parser_log->events . "'";
		$this->mysqli->query($query);
	}
	
	
	private function getAllDbObjects() 
	{
		
		//get all events from database
		
		$query = "SELECT * FROM " . $this->event_table;
		$result = $this->mysqli->query($query);
		
		$objects = array();
		
		while($row = $result->fetch_object()) {		
			$objects[$row->id] = $row;
		}
		
		return $objects;
	}
	
	
	//need to first add all emty entries for new pilots, aircrafts, weapons and pilot_aircrafts so i can write later
	private function addNewEntrys() {
		//insert unknown pilots to pilot table
		$query1 = "INSERT INTO `pilots` (`name`, `disp_name`, `lastactive`) SELECT DISTINCT `events`.`InitiatorPlayer`, `events`.`InitiatorPlayer`, " . time() . " FROM `" . $this->event_table . "` AS events WHERE `events`.`InitiatorGroupCat` = 'AIRPLANE' AND `events`.`InitiatorPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 ) AND `events`.`InitiatorPlayer`<>''";
		$query2 = "INSERT INTO `pilots` (`name`, `disp_name`, `lastactive`) SELECT DISTINCT `events`.`TargetPlayer`, `events`.`TargetPlayer`, " . time() . " FROM `" . $this->event_table . "` AS events WHERE `events`.`TargetGroupCat` = 'AIRPLANE' AND `events`.`TargetPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 ) AND `events`.`TargetPlayer`<>''";
		$this->mysqli->query($query1);
		$this->mysqli->query($query2);
		
		//insert unknown aircraft to aircraft table
		$query3 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `events`.`InitiatorType` FROM `" . $this->event_table . "` AS events WHERE `events`.`InitiatorGroupCat` = 'AIRPLANE' AND `events`.`InitiatorType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$query4 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `events`.`TargetType` FROM `" . $this->event_table . "` AS events WHERE `events`.`TargetGroupCat` = 'AIRPLANE' AND `events`.`TargetType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$this->mysqli->query($query3);
		$this->mysqli->query($query4);
		
		//insert unknown weapons to weapon table
		$query5 = "INSERT INTO `weapons` (`type`, `name`) SELECT `events`.`WeaponCat`, `events`.`WeaponName` FROM `" . $this->event_table . "` AS events WHERE `events`.`WeaponCat` NOT IN ('', 'No Weapon') AND `events`.`WeaponName` NOT IN ('', 'No Weapon') AND `events`.`id` IN ( SELECT MIN(`events`.`id`) FROM `" . $this->event_table . "` as events GROUP BY `events`.`WeaponName` ) AND `events`.`WeaponName` NOT IN ( SELECT `weapons`.`name` FROM `weapons` WHERE 1 )";
		$this->mysqli->query($query5);
		
		//insert new aircrafts to pilots
		$query6 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE events.InitiatorPlayer = pilots.name AND events.InitiatorType = aircrafts.name AND events.InitiatorGroupCat = 'AIRPLANE' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$query7 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE events.TargetPlayer = pilots.name AND events.TargetType = aircrafts.name AND events.TargetGroupCat = 'AIRPLANE' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$this->mysqli->query($query6);
		$this->mysqli->query($query7);

		//position data
		$this->mysqli->query("INSERT INTO position_data (position_data.lat, position_data.lon, position_data.alt, position_data.time, position_data.missiontime, position_data.raw_id, position_data.pilotid, position_data.aircraftid) SELECT CAST(events.TargetType AS DECIMAL(18,12)), CAST(events.TargetPlayer AS DECIMAL(18,12)), CAST(events.TargetCoa AS DECIMAL(18,12)), events.time, events.missiontime, events.InitiatorID, pilots.id, aircrafts.id FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE pilots.name=events.InitiatorPlayer AND aircrafts.name=events.InitiatorType AND events.event='S_EVENT_POSITION' ORDER BY events.time ASC");
	}
	
	
	//update the counters in the database for crashes, ejections, shots
	private function updateCounters() {
		//update counters on pilot table
		$this->mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.shots = pilots.shots + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE events.event='S_EVENT_SHOT' AND events.WeaponCat='MISSILE' AND events.InitiatorPlayer=pilots.name)");
		$this->mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.crashes = pilots.crashes + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE events.event='S_EVENT_CRASH' AND events.InitiatorPlayer=pilots.name)");
		$this->mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.ejects = pilots.ejects + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE events.event='S_EVENT_EJECTION' AND events.InitiatorPlayer=pilots.name)");
		
		//update counters on aircraft table
		$this->mysqli->query("UPDATE aircrafts SET aircrafts.shots = aircrafts.shots + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE aircrafts.name=events.InitiatorType AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_SHOT' AND events.WeaponCat='MISSILE')");
		$this->mysqli->query("UPDATE aircrafts SET aircrafts.crashes = aircrafts.crashes + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE aircrafts.name=events.InitiatorType AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_CRASH')");
		$this->mysqli->query("UPDATE aircrafts SET aircrafts.ejects = aircrafts.ejects + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE aircrafts.name=events.InitiatorType AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_EJECTIONS')");

		//update counters on missile table
		$this->mysqli->query("UPDATE weapons SET weapons.shots = weapons.shots + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events WHERE events.event='S_EVENT_SHOT' AND events.WeaponName=weapons.name)");

		//pilot aircrafts
		$this->mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.shots = pilot_aircrafts.shots + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND events.InitiatorPlayer=pilots.name AND events.InitiatorType=aircrafts.name AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_SHOT' AND events.WeaponCat='MISSILE')");
		$this->mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.ejects = pilot_aircrafts.ejects + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND events.InitiatorPlayer=pilots.name AND events.InitiatorType=aircrafts.name AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_EJECTION')");
		$this->mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.crashes = pilot_aircrafts.crashes + (SELECT COUNT(events.id) FROM " . $this->event_table . " AS events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND events.InitiatorPlayer=pilots.name AND events.InitiatorType=aircrafts.name AND events.InitiatorGroupCat='AIRPLANE' AND events.event='S_EVENT_CRASH')");
	}
	
	
	//add hit from event to tables
	//need to check
	private function countHit($hitevent) {
		//add hit to table
		$this->mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.targetCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.targetAcid, hitsshotskills.targetPid, hitsshotskills.weaponid, hitsshotskills.type) SELECT " . $hitevent->time . ", " . $hitevent->missiontime . ", '" . $hitevent->InitiatorCoa . "', '" . $hitevent->TargetCoa . "', ac1.id, p1.id, ac2.id, p2.id, weapons.id, 'HIT' FROM aircrafts AS ac1, aircrafts AS ac2, pilots AS p1, pilots as p2, weapons WHERE ac1.name='" . $hitevent->InitiatorType . "' AND ac2.name='" . $hitevent->TargetType . "' AND p1.name='" . $hitevent->InitiatorPlayer . "' AND p2.name='" . $hitevent->TargetPlayer . "' AND weapons.name='" . $hitevent->WeaponName . "'");
		
		//update weapons statistic
		$this->mysqli->query("UPDATE weapons SET hits = hits + 1 WHERE name='" . $hitevent->WeaponName . "'");
		
		//COUNT ONLY IF MISSILE
		if ($hitevent->WeaponCat == 'MISSILE') {
			//update pilot statistic
			$this->mysqli->query("UPDATE pilots SET hits = hits + 1, lastactive=" . time() . " WHERE name='" . $hitevent->InitiatorPlayer . "'");
			$this->mysqli->query("UPDATE pilots SET inc_hits = inc_hits + 1, lastactive=" . time() . " WHERE name='" . $hitevent->TargetPlayer . "'");
			
			//update AC statistic
			$this->mysqli->query("UPDATE aircrafts SET hits = hits + 1 WHERE name='" . $hitevent->InitiatorType . "'");
			$this->mysqli->query("UPDATE aircrafts SET inc_hits = inc_hits + 1 WHERE name='" . $hitevent->TargetType . "'");
			
			
			//update pilots AC statistic
			$this->mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.hits = pilot_aircrafts.hits + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->InitiatorPlayer . "' AND aircrafts.name='" . $hitevent->InitiatorType . "'");
			$this->mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.inc_hits = pilot_aircrafts.inc_hits + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->TargetPlayer . "' AND aircrafts.name='" . $hitevent->TargetType . "'");
		}
		
		//delete hit event
		$this->mysqli->query("DELETE FROM " . $this->event_table . " WHERE id=" . $hitevent->id . "");
	}
	
	
	
	//parse hit events and try to figure out who killed who
	// double kill event: HIT, EJECTION, HIT, CRASH - prevented
	private function addHitsShotsKills($events) {
		//add entries to shot table
		$this->mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.weaponid, hitsshotskills.type) SELECT events.time, events.missiontime, events.InitiatorCoa, aircrafts.id, pilots.id, weapons.id, 'SHOT' FROM " . $this->event_table . " AS events, aircrafts, pilots, weapons WHERE aircrafts.name=events.InitiatorType AND pilots.name=events.InitiatorPlayer AND weapons.name=events.WeaponName AND events.event='S_EVENT_SHOT'");
		
		
		$lasthittable = array();
		//add hit/kill
		foreach($events as $id=>$event) {
			//first shot at target
			if (!array_key_exists($event->TargetID, $lasthittable) && $event->event == 'S_EVENT_HIT')	{
				$lasthittable[$event->TargetID] = $id;
				continue;
			}
			
			//pilot ejected, crashed or died - count kill
			if (array_key_exists($event->InitiatorID, $lasthittable) && ($event->event == 'S_EVENT_CRASH' || $event->event == 'S_EVENT_EJECTION' || $event->event == 'S_EVENT_PILOT_DEAD')) 
			{
				//get hit event and count as kill
				$hitevent = $events[$lasthittable[$event->InitiatorID]];
				
				//no hit when pilot crashed more than 120 seconds after hit
				if ($hitevent->time < $event->time - 120) continue;
				
				//no hit when target pilot was already killed 60 seconds before (prevent double kill entries)
				$killtwicetimeout = $hitevent->time + 60;
				$result = $this->mysqli->query("SELECT hitsshotskills.id FROM hitsshotskills, pilots WHERE pilots.name='" . $event->InitiatorPlayer . "' AND hitsshotskills.target_raw_id=" . $event->InitiatorID . " AND pilots.id=hitsshotskills.targetPid AND hitsshotskills.time>" . $killtwicetimeout . " AND type='KILL'");
				if ($result->num_rows > 0) {
					//add hit to statistics
					$this->countHit($hitevent);
	
					//set to zero - prevent double kill entrys if pilot ejects and AC crashes directly afterwards
					unset($lasthittable[$event->InitiatorID]);
					continue;
				}
				
				//add kill to table
				$this->mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.targetCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.targetAcid, hitsshotskills.targetPid, hitsshotskills.weaponid, hitsshotskills.type, hitsshotskills.target_raw_id) SELECT " . $event->time . ", " . $event->missiontime . ", '" . $hitevent->InitiatorCoa . "', '" . $hitevent->TargetCoa . "', ac1.id, p1.id, ac2.id, p2.id, weapons.id, 'KILL', " . $event->InitiatorID . " FROM aircrafts AS ac1, aircrafts AS ac2, pilots AS p1, pilots as p2, weapons WHERE ac1.name='" . $hitevent->InitiatorType . "' AND ac2.name='" . $hitevent->TargetType . "' AND p1.name='" . $hitevent->InitiatorPlayer . "' AND p2.name='" . $hitevent->TargetPlayer . "' AND weapons.name='" . $hitevent->WeaponName . "'");
				
				//update pilot statistic
				$this->mysqli->query("UPDATE pilots SET kills = kills + 1, lastactive=" . time() . " WHERE name='" . $hitevent->InitiatorPlayer . "'");
				$this->mysqli->query("UPDATE pilots SET inc_kills = inc_kills + 1, lastactive=" . time() . " WHERE name='" . $hitevent->TargetPlayer . "'");
				
				//update AC statistic
				$this->mysqli->query("UPDATE aircrafts SET kills = kills + 1 WHERE name='" . $hitevent->InitiatorType . "'");
				$this->mysqli->query("UPDATE aircrafts SET inc_kills = inc_kills + 1 WHERE name='" . $hitevent->TargetType . "'");
				
				//update weapons statistic
				$this->mysqli->query("UPDATE weapons SET kills = kills + 1 WHERE name='" . $hitevent->WeaponName . "'");
				
				//update pilots AC statistic
				$this->mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.kills = pilot_aircrafts.kills + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->InitiatorPlayer . "' AND aircrafts.name='" . $hitevent->InitiatorType . "'");
				$this->mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.inc_kills = pilot_aircrafts.inc_kills + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->TargetPlayer . "' AND aircrafts.name='" . $hitevent->TargetType . "'");
				
				
				//add hit to statistics
				$this->countHit($hitevent);
				
				//set to zero - prevent double kill entrys if pilot ejects and AC crashes directly afterwards
				unset($lasthittable[$event->InitiatorID]);
				continue;
			}
			
			
			//another hit at target count old hit as hit, but not as kill
			if (array_key_exists($event->TargetID, $lasthittable) && $event->event == 'S_EVENT_HIT') 
			{
				$hitevent = $events[$lasthittable[$event->TargetID]];
				$this->countHit($hitevent);
				$lasthittable[$event->TargetID] = $id;
				continue;
			}
			
			//mission started / ended - all hits in list are not kills
			if ($event->event == 'S_EVENT_MISSION_START' || $event->event == 'S_EVENT_MISSION_END') {
				foreach($lasthittable as $evids) {
					$hitevent = $events[$evids];
					$this->countHit($hitevent);
				}
				unset($lasthittable);
				$lasthittable = array();
				continue;
			}
		}
		
		//end of events
		//all items in list older than two minutes are no kills
		foreach($lasthittable as $evids) {
			$hitevent = $events[$evids];
			if ($hitevent->time < time() - 120) {
				$this->countHit($hitevent);
			}
		}
	}
	
	
	
	private function insertFlight($takeoffevent, $event, $endflightevent = false) {
		//calculate flight time in seconds
		$duration = $event->time - $takeoffevent->time;
		
		
		//get end of flight type
		$eoftype = "UNKNOWN";
		switch($event->event) {
			case 'S_EVENT_LAND':
				$eoftype = "LANDING";
				break;
			case 'S_EVENT_CRASH':
				$eoftype = "CRASH";
				break;
			case 'S_EVENT_EJECTION':
				$eoftype = "EJECTION";
				break;
			case 'S_EVENT_DEAD':
			case 'S_EVENT_PILOT_DEAD':
				$eoftype = "DEAD";
				break;
			case 'S_EVENT_MISSION_END':
				$eoftype = "MISSION_END";
				break;
			case 'S_EVENT_PLAYER_LEAVE_UNIT':
				$eoftype = "LEAVE";
				break;
		}
		
		
		//check for plausible data
		if ($duration < 12*2600 && $duration > 0 && 
			(($takeoffevent->InitiatorPlayer == $event->InitiatorPlayer && 
			$takeoffevent->InitiatorType == $event->InitiatorType && 
			$event->missiontime > $takeoffevent->missiontime) || $endflightevent)) 
		{ 
			//data plausible - add flight to logbook
			$this->mysqli->query("UPDATE pilots SET flights = flights + 1, flighttime = flighttime + " . $duration . ", lastactive=" . time() . " WHERE name='" . $takeoffevent->InitiatorPlayer. "' Limit 1");
			$this->mysqli->query("UPDATE aircrafts SET flights = flights + 1, flighttime = flighttime + " . $duration . " WHERE name='" . $takeoffevent->InitiatorType . "' Limit 1");
			$this->mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET " . 
				"pilot_aircrafts.flights = pilot_aircrafts.flights + 1, pilot_aircrafts.time = pilot_aircrafts.time + " . $duration . " WHERE " . 
				"pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . 
				$takeoffevent->InitiatorPlayer . "' AND aircrafts.name='" . $takeoffevent->InitiatorType . "'");
			
			//insert into flight log
			$this->mysqli->query("INSERT INTO flights (flights.pilotid, flights.aircraftid, flights.takeofftime, flights.takeoffmissiontime, flights.landingtime, flights.landingmissiontime, flights.duration, flights.coalition, flights.endofflighttype, flights.raw_id) SELECT pilots.id, aircrafts.id, " . $takeoffevent->time . ", " . $takeoffevent->missiontime . ", " . $event->time . ", " . $event->missiontime . ", " . $duration . ", '" . $takeoffevent->InitiatorCoa . "', '" . $eoftype . "', " . $takeoffevent->InitiatorID . " FROM pilots, aircrafts WHERE pilots.name='" . $takeoffevent->InitiatorPlayer . "' AND aircrafts.name='" . $takeoffevent->InitiatorType . "'");
		}
		
		//remove takeoff entry
		$this->mysqli->query("DELETE FROM " . $this->event_table . " WHERE id=" . $takeoffevent->id . " LIMIT 1");
	}
	
	
	
	private function calculateLandingTime($events) {
		
		$takeoffevents = array();
		
		foreach($events as $id=>$event) {
			
			//save takeoff events
			if (!array_key_exists($event->InitiatorID, $takeoffevents) && ($event->event == 'S_EVENT_TAKEOFF' || $event->event == 'S_EVENT_BIRTH_AIRBORNE')) {
				$takeoffevents[$event->InitiatorID] = $id;
				continue;
			}
			
			
			//flight interruption events - or mission ends (server restarted)
			if (array_key_exists($event->InitiatorID, $takeoffevents) && ($event->event == 'S_EVENT_CRASH' || $event->event == 'S_EVENT_PILOT_DEAD' || $event->event == 'S_EVENT_EJECTION' || $event->event == 'S_EVENT_LAND' || $event->event == 'S_EVENT_DEAD' || $event->event == 'S_EVENT_PLAYER_LEAVE_UNIT')) {
				
				$takeoffevent = $events[$takeoffevents[$event->InitiatorID]];
				$this->insertFlight($takeoffevent, $event);
				
				unset($takeoffevents[$event->InitiatorID]);
			}
			
			//flight time illegal time - delete takeoff entry
			if (array_key_exists($event->InitiatorID, $takeoffevents) && ($event->event == 'S_EVENT_TAKEOFF' || $event->event == 'S_EVENT_BIRTH' || $event->event == 'S_EVENT_BIRTH_AIRBORNE')) {
				$this->mysqli->query("DELETE FROM " . $this->event_table . " WHERE id=" . $takeoffevents[$event->InitiatorID] . " LIMIT 1");
				unset($takeoffevents[$event->InitiatorID]);
				
				//new takeoff
				if ($event->event == 'S_EVENT_TAKEOFF' || $event->event == 'S_EVENT_BIRTH_AIRBORNE') {
					$takeoffevents[$event->InitiatorID] = $id;
					continue;
				}
			}
			
			//flight time illegal time - delete all takeoff entry
			//if a new mission starts, all previous takeoffs are invalid
			if ($event->event == 'S_EVENT_MISSION_START') {
				$this->mysqli->query("DELETE FROM " . $this->event_table . " WHERE event IN ('S_EVENT_TAKEOFF', 'S_EVENT_BIRTH_AIRBORNE') AND id<" . $id);
				unset($takeoffevents);
				$takeoffevents = array();
			}
			
			//flight time end, entry all pilot times
			if ($event->event == 'S_EVENT_MISSION_END') {
				foreach($takeoffevents as $id) {
					$takeoffevent = $events[$id];
					$this->insertFlight($takeoffevent, $event, true);
				}
				
				unset($takeoffevents);
				$takeoffevents = array();
			}
		}
		
		
		//set online status
		$this->mysqli->query("UPDATE pilots SET online=0");
		foreach($takeoffevents as $id) {
			$pilotname = $events[$id]->InitiatorPlayer;
			$this->mysqli->query("UPDATE pilots SET online=1 WHERE name='" . $pilotname . "'");
		}
	}
	
	
	
	private function deleteProcessedEvents() {
		$this->mysqli->query("DELETE FROM " . $this->event_table . " WHERE event NOT IN ('S_EVENT_TAKEOFF', 'S_EVENT_HIT', 'S_EVENT_BIRTH_AIRBORNE')");
	}
	
}
?>