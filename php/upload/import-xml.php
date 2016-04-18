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


class XML_Import {
	private $event_list = array();
	
	
	function writeToDatabase($mysqli, $EVENT_DATABASE) {
		$prep = $mysqli->prepare("INSERT INTO `" . $EVENT_DATABASE . "`(`id`, `time`, `missiontime`, `event`, `InitiatorID`, `InitiatorCoa`, `InitiatorGroupCat`, `InitiatorType`, `InitiatorPlayer`, `WeaponCat`, `WeaponName`, `TargetID`, `TargetCoa`, `TargetGroupCat`, `TargetType`, `TargetPlayer`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		
		foreach($this->event_list as $event) {
			$prep->bind_param('iisissssssissss', $event->time, $event->missiontime, $event->event, $event->initiatorId, $event->initiatorCoa, $event->initiatorGroupCat, $event->initiatorType, $event->initiatorPlayer, $event->weaponCat, $event->weaponName, $event->targetId, $event->targetCoa, $event->targetGroupCat, $event->targetType, $event->targetPlayer);
			$prep->execute();
		}
		
		$prep->close();
	}
	
	function putEvent($_time, $_missiontime, $_event, $_initiatorId=0, $_initiatorCoa='', $_initiatoeGroupCat='', $_initiatorType='', $_initiatorPlayer='', $_weaponCat='No Weapon', $_weaponName='No Weapon', $_targetId=0, $_targetCoa='', $_targetGroupCat='', $_targetType='', $_targetPlayer='') {
		$event = new stdClass();
		$event->time = $_time;
		$event->missiontime = $_missiontime;
		$event->event = $_event;
		$event->initiatorId = $_initiatorId;
		$event->initiatorCoa = $_initiatorCoa;
		$event->initiatorGroupCat = $_initiatoeGroupCat;
		$event->initiatorType = $_initiatorType;
		$event->initiatorPlayer = $_initiatorPlayer;
		$event->weaponCat = $_weaponCat;
		$event->weaponName = $_weaponName;
		$event->targetId = $_targetId;
		$event->targetCoa = $_targetCoa;
		$event->targetGroupCat = $_targetGroupCat;
		$event->targetType = $_targetType;
		$event->targetPlayer = $_targetPlayer;
		
		$this->event_list[] = $event;
	}

	function XML_Import($filename) {		

		$xml = simplexml_load_file($filename);
		$recordsource = $xml->FlightRecording->Source;
		$missionstarttime = $this->getUnixTimeFromTimestamp($xml->FlightRecording->RecordingTime);
		$missiontime = 0;
		
		
		$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_MISSION_START');
		
		//loop through events
		foreach($xml->Events->Event as $event) {
			//get time
			$missiontime = $event->Time;
			$id = $event->PrimaryObject['ID'];
			$type = $this->getUnitType($event->PrimaryObject->Type);
			
			
			//BIRTH
			if ($event->Action == "HasEnteredTheArea") {
				if ($type == "") continue;
				
				$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_BIRTH', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
			}
			
			
			//TAKEOFF
			if ($event->Action == "HasTakeOff" && $type == "AIRPLANE") {
				$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_TAKEOFF', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
			}
			
			
			//landing
			if ($event->Action == "HasLanded" && $type == "AIRPLANE") {
				$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_LAND', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
			}
			
			
			//Crash
			if ($event->Action == "HasBeenDestroyed" && isset($event->SecondaryObject)) {
				$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_CRASH', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
			}
			
			//EJECTION
			if ($event->Action == "HasFired" && $type == "AIRPLANE") {
				if ($event->SecondaryObject->Type == "Parachutist") {
					$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_EJECTION', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
				}
			}
			
			
			//HIT
			if ($event->Action == "HasBeenHitBy") {
				$times = 1;
				
				if ($type != 'AIRPLANE' && $type != 'GROUND' && $type != 'HELICOPTER') {
					continue;
				}
				
				for ($j = 0; $j < $times; $j++) {
					$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_HIT', $event->ParentObject['ID'], $this->parseCoalitionNames($event->ParentObject->Coalition), $type, $this->filterAircraftNames($event->ParentObject->Name), $this->filterAiName($event->ParentObject->Pilot), $this->getUnitType($event->SecondaryObject->Type), $this->filterTypeNames($event->SecondaryObject->Name), $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
				}
			}
			
			
			//SHOT
			if ($event->Action == "HasFired") {
				$weaponType = $this->getUnitType($event->SecondaryObject->Type);
				if ($weaponType == "MISSILE" || $weaponType == "ROCKET" || $weaponType == "BOMB") {
					$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_SHOT', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot), $weaponType, $this->filterTypeNames($event->SecondaryObject->Name));
				}
			}
			
			
			//PLAYER_LEAVE_UNIT
			if ($event->Action == "HasLeftTheArea") {
				$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_PLAYER_LEFT_UNIT', $id, $this->parseCoalitionNames($event->PrimaryObject->Coalition), $type, $this->filterAircraftNames($event->PrimaryObject->Name), $this->filterAiName($event->PrimaryObject->Pilot));
			}
		}
		
		
		//mission end
		$this->putEvent(floor($missionstarttime + $missiontime), floor($missiontime), 'S_EVENT_MISSION_END');
	}
	
	//functions
	
	function getUnixTimeFromTimestamp($timestamp) 
	{
		$split = explode("T", $timestamp);
		$datestr = $split[0];
		$timestr = substr($split[1], 0, strlen($split[1])-1);
		
		//remove milliseconds
		if ($pos = strpos($timestr, ".")) {
			$timestr = substr($timestr, 0, $pos);
		}
		
		$datetime = DateTime::createFromFormat("Y-m-d H:i:s", $datestr . " " . $timestr, new DateTimeZone("UTC"));
		return $datetime->getTimestamp();
	}
	
	function getUnitType($type) {
		$rtype = "";
		switch($type) {
			case "Helicopter":
				$rtype = "HELICOPTER";
				break;
			case "Aircraft":
				$rtype = "AIRPLANE";
				break;
			case "Tank":
			case "Ground":
			case "Vehicle":
				$rtype = "GROUND";
				break;
			case "Shell":
				$rtype = "SHELL";
				break;
			case "Missile":
				$rtype = "MISSILE";
				break;
			case "Rocket":
				$rtype = "ROCKET";
				break;
			case "Bomb":
				$rtype = "BOMB";
				break;
		}
		return $rtype;
	}
	
	
	function filterAircraftNames($name) {
		$name = str_replace("Mirage 2000", "M-2000", $name);
		
		//general
		if ($pos = strpos($name, " ")) {
			$name = substr($name, 0, $pos);
		}
		
		return $name;
	}
	
	
	function filterAiName($name) {
		if (strlen($name) <= 0) return "AI";
		
		//turn AI to AI
		switch($name) {
			case("AWACS - Magic"):
				$name = "AI";
		}
		
		//generic pilot names
		if (strpos($name, "Pilot") !== false) $name = "AI";
		if (strpos($name, "Cougar") !== false) $name = "AI";
		if (strpos($name, "Mastic") !== false) $name = "AI";
		if (strpos($name, "Cyborg") !== false) $name = "AI";
		if (strpos($name, "Cowboy") !== false) $name = "AI";
		if (strpos($name, "Falcon") !== false) $name = "AI";
		if (strpos($name, "Hornet") !== false) $name = "AI";
		if (strpos($name, "Texico") !== false) $name = "AI";
		if (strpos($name, "Ghost") !== false) $name = "AI";
		if (strpos($name, "Diamond") !== false) $name = "AI";
		if (strpos($name, "Panther") !== false) $name = "AI";
		if (strpos($name, "Banshee") !== false) $name = "AI";
		if (strpos($name, "Hornet") !== false) $name = "AI";
		if (strpos($name, "Camel") !== false) $name = "AI";
		if (strpos($name, "Chalis") !== false) $name = "AI";
		if (strpos($name, "Nightmare") !== false) $name = "AI";
		if (strpos($name, "Dragon") !== false) $name = "AI";
		if (strpos($name, "Devil") !== false) $name = "AI";
		if (strpos($name, "AWACS") !== false) $name = "AI";
		if (strpos($name, "Unit") !== false) $name = "AI";
		if (strpos($name, "Einheit") !== false) $name = "AI";
					
		return $name;
	}
	
	function filterTypeNames($name) {
		if (strlen($name) <= 0) return $name;
		
		if (strpos($name, "weapons.") !== false) {
			$name = str_replace("weapons.missiles.", "", $name);
			$name = str_replace("weapons.shells.", "", $name);
			$name = str_replace("weapons.rockets.", "", $name);
			$name = str_replace("weapons.bombs.", "", $name);
			
			//weapons
			$name = str_replace("M61_20_", "M61 20 mm ", $name);
			$name = str_replace("DEFA552_30", "DEFA 552 30mm", $name);
			
			$name = str_replace("_", "-", $name);
		} else {
			
			
			//general
			if ($pos = strpos($name, " ")) {
				$name = substr($name, 0, $pos);
			}
		}
		return $name;
	}
	
	function parseCoalitionNames($name) {
		switch($name) {
			case("Enemies"):
				$name = "blue";
				break;
			case("Allies");
				$name = "red";
				break;
		}
		
		return $name;
	}
}

?>