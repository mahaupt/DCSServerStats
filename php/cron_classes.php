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
	
	
	function getAllDbObjects($mysqli, $table) 
	{
		
		//get all events from database
		
		$query = "SELECT * FROM " . $table;
		$result = $mysqli->query($query);
		
		$objects = array();
		
		while($row = $result->fetch_object()) {		
			$objects[$row->id] = $row;
		}
		
		return $objects;
	}
	
	
	function addNewEntrys($mysqli) {
		//insert unknown pilots to pilot table
		$query1 = "INSERT INTO `pilots` (`name`, `lastactive`) SELECT DISTINCT `dcs_events`.`InitiatorPlayer`, " . time() . " FROM `dcs_events` WHERE `dcs_events`.`InitiatorGroupCat` = 'AIRPLANE' AND `dcs_events`.`InitiatorPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 )";
		$query2 = "INSERT INTO `pilots` (`name`, `lastactive`) SELECT DISTINCT `dcs_events`.`TargetPlayer`, " . time() . " FROM `dcs_events` WHERE `dcs_events`.`TargetGroupCat` = 'AIRPLANE' AND `dcs_events`.`TargetPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 )";
		$query3 = "INSERT INTO `pilots` (`name`, `lastactive`) SELECT DISTINCT `dcs_events`.`InitiatorPlayer`, " . time() . " FROM `dcs_events` WHERE `dcs_events`.`InitiatorGroupCat` = 'HELICOPTER' AND `dcs_events`.`InitiatorPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 )";
		$query4 = "INSERT INTO `pilots` (`name`, `lastactive`) SELECT DISTINCT `dcs_events`.`TargetPlayer`, " . time() . " FROM `dcs_events` WHERE `dcs_events`.`TargetGroupCat` = 'HELICOPTER' AND `dcs_events`.`TargetPlayer` NOT IN ( SELECT `pilots`.`name` FROM `pilots` WHERE 1 )";
		$mysqli->query($query1);
		$mysqli->query($query2);
		$mysqli->query($query3);
		$mysqli->query($query4);
		
		//insert unknown aircraft to aircraft table
		$query5 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `dcs_events`.`InitiatorType` FROM `dcs_events` WHERE `dcs_events`.`InitiatorGroupCat` = 'AIRPLANE' AND `dcs_events`.`InitiatorType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$query6 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `dcs_events`.`TargetType` FROM `dcs_events` WHERE `dcs_events`.`TargetGroupCat` = 'AIRPLANE' AND `dcs_events`.`TargetType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$query7 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `dcs_events`.`InitiatorType` FROM `dcs_events` WHERE `dcs_events`.`InitiatorGroupCat` = 'HELICOPTER' AND `dcs_events`.`InitiatorType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$query8 = "INSERT INTO `aircrafts` (`name`) SELECT DISTINCT `dcs_events`.`TargetType` FROM `dcs_events` WHERE `dcs_events`.`TargetGroupCat` = 'HELICOPTER' AND `dcs_events`.`TargetType` NOT IN ( SELECT `aircrafts`.`name` FROM `aircrafts` WHERE 1 )";
		$mysqli->query($query5);
		$mysqli->query($query6);
		$mysqli->query($query7);
		$mysqli->query($query8);
		
		//insert unknown weapons to weapon table
		$query5 = "INSERT INTO `weapons` (`type`, `name`) SELECT `dcs_events`.`WeaponCat`, `dcs_events`.`WeaponName` FROM `dcs_events` WHERE `dcs_events`.`WeaponCat`<>'No Weapon' AND `dcs_events`.`WeaponName`<>'No Weapon' AND `dcs_events`.`id` IN ( SELECT MIN(`dcs_events`.`id`) FROM `dcs_events` GROUP BY `dcs_events`.`WeaponName` ) AND `dcs_events`.`WeaponName` NOT IN ( SELECT `weapons`.`name` FROM `weapons` WHERE 1 )";
		$mysqli->query($query5);
		
		//insert new aircrafts to pilots
		$query6 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM dcs_events, pilots, aircrafts WHERE dcs_events.InitiatorPlayer = pilots.name AND dcs_events.InitiatorType = aircrafts.name AND dcs_events.InitiatorGroupCat = 'AIRPLANE' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$query7 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM dcs_events, pilots, aircrafts WHERE dcs_events.TargetPlayer = pilots.name AND dcs_events.TargetType = aircrafts.name AND dcs_events.TargetGroupCat = 'AIRPLANE' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$query6 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM dcs_events, pilots, aircrafts WHERE dcs_events.InitiatorPlayer = pilots.name AND dcs_events.InitiatorType = aircrafts.name AND dcs_events.InitiatorGroupCat = 'HELICOPTER' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$query7 = "INSERT INTO pilot_aircrafts (pilot_aircrafts.pilotid, pilot_aircrafts.aircraftid) SELECT DISTINCT pilots.id, aircrafts.id FROM dcs_events, pilots, aircrafts WHERE dcs_events.TargetPlayer = pilots.name AND dcs_events.TargetType = aircrafts.name AND dcs_events.TargetGroupCat = 'HELICOPTER' AND aircrafts.id NOT IN (SELECT pilot_aircrafts.aircraftid FROM pilot_aircrafts WHERE pilot_aircrafts.pilotid = pilots.id )";
		$mysqli->query($query9);
		$mysqli->query($query10);
		$mysqli->query($query11);
		$mysqli->query($query12);

	}
	
	
	
	function updateCounters($mysqli) {
		//update counters on pilot table
		$mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.shots = pilots.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponCat='MISSILE' AND dcs_events.InitiatorPlayer=pilots.name)");
		$mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.hits = pilots.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE' AND dcs_events.InitiatorPlayer=pilots.name)");
		$mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.crashes = pilots.crashes + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_CRASH' AND dcs_events.InitiatorPlayer=pilots.name)");
		$mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.ejects = pilots.ejects + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_EJECTION' AND dcs_events.InitiatorPlayer=pilots.name)");
		$mysqli->query("UPDATE pilots SET pilots.lastactive=" . time() . ", pilots.inc_hits = pilots.inc_hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE' AND dcs_events.TargetPlayer=pilots.name)");
		
		//update counters on aircraft table
		$mysqli->query("UPDATE aircrafts SET aircrafts.hits = aircrafts.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.shots = aircrafts.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.crashes = aircrafts.crashes + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_CRASH')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.ejects = aircrafts.ejects + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_EJECTIONS')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.inc_hits = aircrafts.inc_hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.TargetType AND dcs_events.TargetGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
				$mysqli->query("UPDATE aircrafts SET aircrafts.hits = aircrafts.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.shots = aircrafts.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.crashes = aircrafts.crashes + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_CRASH')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.ejects = aircrafts.ejects + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.InitiatorType AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_EJECTIONS')");
		$mysqli->query("UPDATE aircrafts SET aircrafts.inc_hits = aircrafts.inc_hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE aircrafts.name=dcs_events.TargetType AND dcs_events.TargetGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
	
		//update counters on missile table
		$mysqli->query("UPDATE weapons SET weapons.shots = weapons.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponName=weapons.name)");
		$mysqli->query("UPDATE weapons SET weapons.hits = weapons.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events WHERE dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponName=weapons.name)");
		
		//update counters on pilot_aircrafts table
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.hits = pilot_aircrafts.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.shots = pilot_aircrafts.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.ejects = pilot_aircrafts.ejects + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_EJECTION')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.crashes = pilot_aircrafts.crashes + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_CRASH')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.inc_hits = pilot_aircrafts.inc_hits + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.TargetPlayer=pilots.name AND dcs_events.TargetType=aircrafts.name AND dcs_events.TargetGroupCat='AIRPLANE' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
		
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.hits = pilot_aircrafts.hits + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.shots = pilot_aircrafts.shots + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_SHOT' AND dcs_events.WeaponCat='MISSILE')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.ejects = pilot_aircrafts.ejects + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_EJECTION')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.crashes = pilot_aircrafts.crashes + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.InitiatorPlayer=pilots.name AND dcs_events.InitiatorType=aircrafts.name AND dcs_events.InitiatorGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_CRASH')");
		$mysqli->query("UPDATE pilot_aircrafts SET pilot_aircrafts.inc_hits = pilot_aircrafts.inc_hits + (SELECT COUNT(dcs_events.id) FROM dcs_events, pilots, aircrafts WHERE pilots.id=pilot_aircrafts.pilotid AND aircrafts.id=pilot_aircrafts.aircraftid AND dcs_events.TargetPlayer=pilots.name AND dcs_events.TargetType=aircrafts.name AND dcs_events.TargetGroupCat='HELICOPTER' AND dcs_events.event='S_EVENT_HIT' AND dcs_events.WeaponCat='MISSILE')");
	}
	
	
	function addHitsShotsKills($mysqli, $events) {
		//add entries to shot table
		$mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.weaponid, hitsshotskills.type) SELECT dcs_events.time, dcs_events.missiontime, dcs_events.InitiatorCoa, aircrafts.id, pilots.id, weapons.id, 'SHOT' FROM dcs_events, aircrafts, pilots, weapons WHERE aircrafts.name=dcs_events.InitiatorType AND pilots.name=dcs_events.InitiatorPlayer AND weapons.name=dcs_events.WeaponName AND dcs_events.event='S_EVENT_SHOT'");
		
		//add entries to hit table
		$mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.targetCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.targetAcid, hitsshotskills.targetPid, hitsshotskills.weaponid, hitsshotskills.type) SELECT dcs_events.time, dcs_events.missiontime, dcs_events.InitiatorCoa, dcs_events.TargetCoa, ac1.id, p1.id, ac2.id, p2.id, weapons.id, 'HIT' FROM dcs_events, aircrafts AS ac1, aircrafts AS ac2, pilots AS p1, pilots as p2, weapons WHERE ac1.name=dcs_events.InitiatorType AND ac2.name=dcs_events.TargetType AND p1.name=dcs_events.InitiatorPlayer AND p2.name=dcs_events.TargetPlayer AND weapons.name=dcs_events.WeaponName AND dcs_events.event='S_EVENT_HIT'");
		
		
		$lasthittable = array();
		//add hit/kill
		foreach($events as $id=>$event) {
			//save hits
			if ($event->event == 'S_EVENT_HIT')	{
				$lasthittable[$event->TargetID] = $id;
			}
			
			//pilot ejected, crashed or died
			if (array_key_exists($event->InitiatorID, $lasthittable) && ($event->event == 'S_EVENT_CRASH' || $event->event == 'S_EVENT_EJECTION' || $event->event == 'S_EVENT_PILOT_DEAD')) 
			{
				//get hit event and count as kill
				$hitevent = $events[$lasthittable[$event->InitiatorID]];
				
				if ($event->time - $hitevent->time > 120) continue; //no hit when pilot crashed more than 120 seconds after hit
				
				//add kill to table
				$mysqli->query("INSERT INTO hitsshotskills (hitsshotskills.time, hitsshotskills.missiontime, hitsshotskills.initiatorCoa, hitsshotskills.targetCoa, hitsshotskills.initiatorAcid, hitsshotskills.initiatorPid, hitsshotskills.targetAcid, hitsshotskills.targetPid, hitsshotskills.weaponid, hitsshotskills.type) SELECT " . $event->time . ", " . $event->missiontime . ", '" . $hitevent->InitiatorCoa . "', '" . $hitevent->TargetCoa . "', ac1.id, p1.id, ac2.id, p2.id, weapons.id, 'KILL' FROM aircrafts AS ac1, aircrafts AS ac2, pilots AS p1, pilots as p2, weapons WHERE ac1.name='" . $hitevent->InitiatorType . "' AND ac2.name='" . $hitevent->TargetType . "' AND p1.name='" . $hitevent->InitiatorPlayer . "' AND p2.name='" . $hitevent->TargetPlayer . "' AND weapons.name='" . $hitevent->WeaponName . "'");
				
				//update pilot statistic
				$mysqli->query("UPDATE pilots SET kills = kills + 1, lastactive=" . time() . " WHERE name='" . $hitevent->InitiatorPlayer . "'");
				$mysqli->query("UPDATE pilots SET inc_kills = inc_kills + 1, lastactive=" . time() . " WHERE name='" . $hitevent->TargetPlayer . "'");
				
				//update AC statistic
				$mysqli->query("UPDATE aircrafts SET kills = kills + 1 WHERE name='" . $hitevent->InitiatorType . "'");
				$mysqli->query("UPDATE aircrafts SET inc_kills = inc_kills + 1 WHERE name='" . $hitevent->TargetType . "'");
				
				//update weapons statistic
				$mysqli->query("UPDATE weapons SET kills = kills + 1 WHERE name='" . $hitevent->WeaponName . "'");
				
				//update pilots AC statistic
				$mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.kills = pilot_aircrafts.kills + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->InitiatorPlayer . "' AND aircrafts.name='" . $hitevent->InitiatorType . "'");
				$mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET pilot_aircrafts.inc_kills = pilot_aircrafts.inc_kills + 1 WHERE pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . $hitevent->TargetPlayer . "' AND aircrafts.name='" . $hitevent->TargetType . "'");
				
				
				//set to zero - prevent double kill entrys if pilot ejects and AC crashes directly afterwards
				unset($lasthittable[$event->InitiatorID]);
			}
		}
	}
	
	
	
	function calculateLandingTime($mysqli, $events) {
		
		$takeoffevents = array();
		
		foreach($events as $id=>$event) {
			
			//save takeoff events
			if ($event->event == 'S_EVENT_TAKEOFF') {
				$takeoffevents[$event->InitiatorID] = $id;
				continue;
			}
			
			
			//flight interruption events
			if (array_key_exists($event->InitiatorID, $takeoffevents) && ($event->event == 'S_EVENT_CRASH' || $event->event == 'S_EVENT_PILOT_DEAD' || $event->event == 'S_EVENT_EJECTION' || $event->event == 'S_EVENT_LAND' || $event->event == 'S_EVENT_CRASH' || $event->event == 'S_EVENT_MISSION_END' || $event->event == 'S_EVENT_DEAD')) {
				
				$takeoffevent = $events[$takeoffevents[$event->InitiatorID]];
				
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
				}
				
				
				//check for plausible data
				if ($duration < 12*2600 && $duration > 0 && 
					$takeoffevent->InitiatorPlayer == $event->InitiatorPlayer && 
					$takeoffevent->InitiatorType == $event->InitiatorType && 
					$event->missiontime > $takeoffevent->missiontime) 
				{ 
					//data plausible - add flight to logbook
					$mysqli->query("UPDATE pilots SET flights = flights + 1, flighttime = flighttime + " . $duration . ", lastactive=" . time() . " WHERE name='" . $event->InitiatorPlayer. "' Limit 1");
					$mysqli->query("UPDATE aircrafts SET flights = flights + 1, flighttime = flighttime + " . $duration . " WHERE name='" . $event->InitiatorType . "' Limit 1");
					$mysqli->query("UPDATE pilot_aircrafts, pilots, aircrafts SET " . 
						"pilot_aircrafts.flights = pilot_aircrafts.flights + 1, pilot_aircrafts.time = pilot_aircrafts.time + " . $duration . " WHERE " . 
						"pilot_aircrafts.pilotid=pilots.id AND pilot_aircrafts.aircraftid=aircrafts.id AND pilots.name='" . 
						$event->InitiatorPlayer . "' AND aircrafts.name='" . $event->InitiatorType . "'");
					
					//insert into flight log
					$mysqli->query("INSERT INTO flights (flights.pilotid, flights.aircraftid, flights.takeofftime, flights.takeoffmissiontime, flights.landingtime, flights.landingmissiontime, flights.duration, flights.coalition, flights.endofflighttype) SELECT pilots.id, aircrafts.id, " . $takeoffevent->time . ", " . $takeoffevent->missiontime . ", " . $event->time . ", " . $event->missiontime . ", " . $duration . ", '" . $event->InitiatorCoa . "', '" . $eoftype . "' FROM pilots, aircrafts WHERE pilots.name='" . $event->InitiatorPlayer . "' AND aircrafts.name='" . $event->InitiatorType . "'");
				}
				
				//remove takeoff entry
				$mysqli->query("DELETE FROM dcs_events WHERE id=" . $takeoffevents[$event->InitiatorID] . " LIMIT 1");
				unset($takeoffevents[$event->InitiatorID]);
			}
			
			//flight time illegal time - delete takeoff entry
			if (array_key_exists($event->InitiatorID, $takeoffevents) && ($event->event == 'S_EVENT_TAKEOFF' || $event->event == 'S_EVENT_BIRTH')) {
				$mysqli->query("DELETE FROM dcs_events WHERE id=" . $takeoffevents[$event->InitiatorID] . " LIMIT 1");
				unset($takeoffevents[$event->InitiatorID]);
			}
			
			//flight time illegal time - delete takeoff entry
			//if a new mission starts, all previous takeoffs are invalid
			if ($event->event == 'S_EVENT_MISSION_START') {
				$mysqli->query("DELETE FROM dcs_events WHERE id < " . $id);
				unset($takeoffevents);
				$takeoffevents = array();
			}
		}
		
		
		//set online status
		$mysqli->query("UPDATE pilots SET online=0");
		foreach($takeoffevents as $id) {
			$pilotname = $events[$id]->InitiatorPlayer;
			$mysqli->query("UPDATE pilots SET online=1 WHERE name='" . $pilotname . "'");
		}
	}
	
	
	
	function deleteProcessedEvents($mysqli) {
		$mysqli->query("DELETE FROM dcs_events WHERE event<>'S_EVENT_TAKEOFF'");
	}
?>