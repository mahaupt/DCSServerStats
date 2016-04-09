#!/usr/bin/php
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




if (!isset($argc) || is_null($argc) || isset($_SERVER['REMOTE_ADDR']))
	die('CLI Mode only!');
	

//open sql file
$sqlfile = fopen("./tacviewexport.sql", "w");
fwrite($sqlfile, "INSERT INTO `dcs_events`(`id`, `time`, `missiontime`, `event`, `InitiatorID`, `InitiatorCoa`, `InitiatorGroupCat`, `InitiatorType`, `InitiatorPlayer`, `WeaponCat`, `WeaponName`, `TargetID`, `TargetCoa`, `TargetGroupCat`, `TargetType`, `TargetPlayer`) VALUES \n");


//loop through each file
for ($i = 1; $i < $argc; $i++) 
{
	$xml = simplexml_load_file($argv[$i]);
	$recordsource = $xml->FlightRecording->Source;
	$missionstarttime = getUnixTimeFromTimestamp($xml->FlightRecording->RecordingTime);
	$missiontime = 0;
	
	
	fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_MISSION_START', 0, '', '', '', '', '', '', 0, '', '', '', ''), \n");
	
	//loop through events
	foreach($xml->Events->Event as $event) {
		//get time
		$missiontime = $event->Time;
		$id = $event->PrimaryObject['ID'];
		$type = getUnitType($event->PrimaryObject->Type);
		
		
		//BIRTH
		if ($event->Action == "HasEnteredTheArea") {
			if ($type == "") continue;
			
			fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_BIRTH', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
		}
		
		
		//TAKEOFF
		if ($event->Action == "HasTakeOff" && $type == "AIRPLANE") {
			fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_TAKEOFF', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
		}
		
		
		//landing
		if ($event->Action == "HasLanded" && $type == "AIRPLANE") {
			fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_LAND', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
		}
		
		
		//Crash
		if ($event->Action == "HasBeenDestroyed" && isset($event->SecondaryObject)) {
			fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_CRASH', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
		}
		
		//EJECTION
		if ($event->Action == "HasFired" && $type == "AIRPLANE") {
			if ($event->SecondaryObject->Type == "Parachutist") {
				fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_EJECTION', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
			}
		}
		
		
		//HIT
		if ($event->Action == "HasBeenHitBy") {
			$times = 1;
			if (isset($event->Occurrences)) {
				$times = $event->Occurrences;
			}
			for ($j = 0; $j < $times; $j++) {
				fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_HIT', " . $event->ParentObject['ID'] . ", '" . parseCoalitionNames($event->ParentObject->Coalition) . "', '" . getUnitType($event->ParentObject->Type) . "', '" . filterAircraftNames($event->ParentObject->Name) . "', '" . filterAiName($event->ParentObject->Pilot) . "', '" . getUnitType($event->SecondaryObject->Type) . "', '" . filterTypeNames($event->SecondaryObject->Name) . "', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "'), \n");
			}
		}
		
		
		//SHOT
		if ($event->Action == "HasFired") {
			$weaponType = getUnitType($event->SecondaryObject->Type);
			if ($weaponType == "MISSILE" || $weaponType == "ROCKET" || $weaponType == "BOMB") {
				fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_SHOT', " . $event->PrimaryObject['ID'] . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '" . $weaponType . "', '" . filterTypeNames($event->SecondaryObject->Name) . "', 0, '', '', '', ''), \n");
			}
		}
		
		
		//PLAYER_LEAVE_UNIT
		if ($event->Action == "HasLeftTheArea") {
			fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_PLAYER_LEFT_UNIT', " . $id . ", '" . parseCoalitionNames($event->PrimaryObject->Coalition) . "', '" . $type . "', '" . filterAircraftNames($event->PrimaryObject->Name) . "', '" . filterAiName($event->PrimaryObject->Pilot) . "', '', '', 0, '', '', '', ''), \n");
		}
	}
	
	
	//mission end
	fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_MISSION_END', 0, '', '', '', '', '', '', 0, '', '', '', '')");
	if ($i == $argc-1) 
	{
		fwrite($sqlfile, ";");
	} 
	else 
	{
		fwrite($sqlfile, ", \n");
	}
}


//close sql file
fclose($sqlfile);

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
	if (strpos($name, "Pilot #") !== false) $name = "AI";
	if (strpos($name, "Cougar #") !== false) $name = "AI";
	if (strpos($name, "Mastic #") !== false) $name = "AI";
	if (strpos($name, "Cyborg") !== false) $name = "AI";
	if (strpos($name, "Unit #") !== false) $name = "AI";
	if (strpos($name, "Einheit #") !== false) $name = "AI";
				
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

?>