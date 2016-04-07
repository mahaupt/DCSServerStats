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


$SMOOTHTIMES = 500;


if (!isset($argc) || is_null($argc) || isset($_SERVER['REMOTE_ADDR']))
	die('CLI Mode only!');
	

//open sql file
$sqlfile = fopen("./tacviewexport.sql", "w");
fwrite($sqlfile, "INSERT INTO `dcs_events`(`id`, `time`, `missiontime`, `event`, `InitiatorID`, `InitiatorCoa`, `InitiatorGroupCat`, `InitiatorType`, `InitiatorPlayer`, `WeaponCat`, `WeaponName`, `TargetID`, `TargetCoa`, `TargetGroupCat`, `TargetType`, `TargetPlayer`) VALUES \n");


//loop through each file
for ($i = 1; $i < $argc; $i++) 
{
	$file = fopen($argv[$i], "r") or die("Unable to open file " . $argv[$i]);
	
	//variables
	$missiontime = 0.0; //time of current frame
	$missionstarttime = 0;
	$objects = array();
	$coalitions = array();
	$isheader = true;
	$fileversion = 0;
	$offlat = 0;
	$offlon = 0;
	
	
	while(!feof($file)) 
	{
		//get line
		$line = fgets($file);
		//remove \n's
		$line = str_replace("\n", "", $line);
		
		
		//get header information
		if ($isheader) 
		{
			$info = explode("=", $line);
			
			switch($info[0]) 
			{
				case "RecordingTime":
					$missionstarttime = getUnixTimeFromTimestamp($info[1]);
					break;
					
				case "Coalition":
					$coalitions[] = getCoalition($info[1]);
					break;
					
				case "FileVersion":
					$fileversion = floatval($info[1]);
					break;
					
				case "LongitudeOffset":
					$offlon = floatval($info[1]);
					break;
				case "LatitudeOffset":
					$offlat = floatval($info[1]);
					break;
			}
		}
		
		
		//header is over, data begins
		//take frame time
		if ($line[0] == '#') 
		{
			//remove line
			$line = str_replace("#", "", $line);
			
			//get current frame time (missiontime)
			$missiontime = floatval($line);
			
			
			//first frame - mission starts
			if ($isheader) 
			{
				fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_MISSION_START', 0, '', '', '', '', '', '', 0, '', '', '', ''), \n");
				$isheader = false;
			}
			
			
			continue;
		}
		
		
		//frame information
		if (!$isheader) 
		{
			//add object
			if ($line[0] == '+') 
			{
				$line = str_replace("+", "", $line);
				$split = explode(",", $line);
				
				$id = hexdec($split[0]);
				$type = "";
				//object is aircraft or helicopter
				switch (hexdec($split[2])) 
				{
					case(hexdec("10")):
						$type="AIRPLANE";
						break;
					case(hexdec("18")):
						$type="HELICOPTER";
						break;
					case(hexdec("40")):
						$type="MISSILE";
						break;
					case(hexdec("44")):
						$type="ROCKET";
						break;
					case(hexdec("48")):
						$type="SHELL";
						break;
					case(hexdec("49")):
						$type="BULLET";
						break;
					case(hexdec("4a")):
						$type="BALLISTIC_SHELL";
						break;
					case(hexdec("4c")):
						$type="BOMB";
						break;
					case(hexdec("46")):
						$type="SHRAPNEL";
						break;
					case(hexdec("2e")):
						$type="PARACHUTIST";
						break;
				}
				
				//no type - no recording
				if ($type == "") 
				{
					continue;
				}
				
				
				$objects[$id] = new stdClass();
				$objects[$id]->id = $id;
				$objects[$id]->type = $type;
				$objects[$id]->parentId = hexdec($split[1]);
				$objects[$id]->coalitionId = hexdec($split[3]);
				$objects[$id]->typename = $split[5];
				$objects[$id]->pilotname = $split[6];
				$objects[$id]->lat = 0;
				$objects[$id]->lon = 0;
				$objects[$id]->alt = 0;
				$objects[$id]->vel = 0;
				$objects[$id]->vel_lat = 0;
				$objects[$id]->vel_lon = 0;
				$objects[$id]->vel_alt = 0;
				$objects[$id]->position = false;
				$objects[$id]->started = false;
				$objects[$id]->lastupdate = $missiontime;

				
				$coalitionname = $coalitions[$objects[$id]->coalitionId]->color;
				
				//add birth message
				if ($type == "AIRPLANE" || $type == "HELICOPTER") 
				{
					fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_BIRTH', " . $id . ", '" . $coalitionname . "', '" . $objects[$id]->type . "', '" . $objects[$id]->typename . "', '" . $objects[$id]->pilotname . "', '', '', 0, '', '', '', ''), \n");
				}
			}
			//special event 
			else if ($line[0] == '!') 
			{
				$line = str_replace("!", "", $line);
				$split = explode(",", $line);
				$event = hexdec($split[0]);
				$id = hexdec($split[1]);
				
				/*if ($event == hexdec("20") && isset($objects[$id])) {
					if ($objects[$id]->type == "AIRPLANE" && $objects[$id]->started) {
						fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_CRASH', " . $id . ", '" . $coalitions[$objects[$id]->coalitionId]->color . "', '" . $objects[$id]->type . "', '" . $objects[$id]->typename . "', '" . $objects[$id]->pilotname . "', '', '', 0, '', '', '', ''), \n");
					}
				}*/
			} 
			//advanced telemetry
			else if ($line[0] == '@') 
			{
				$line = str_replace("@", "", $line);
				$split = explode(",", $line);
				
			}
			//position update 
			else 
			{
				$split = explode(",", $line);
				$id = hexdec($split[0]);
				if (isset($objects[$id])) 
				{
					//last position
					$last_lat = $objects[$id]->lat;
					$last_lon = $objects[$id]->lon;
					$last_alt = $objects[$id]->alt;
					
					//aktuelle pos
					$objects[$id]->lat = floatval($split[1]) + $offlat;
					$objects[$id]->lon = floatval($split[2]) + $offlon;
					$objects[$id]->alt = floatval($split[3]);
					
					//last aktualisierungs time
					$deltatime = $missiontime - $objects[$id]->lastupdate;
					$objects[$id]->lastupdate = $missiontime;
					
					//calculate velocity and takeoffs
					if ($objects[$id]->position) 
					{
						if ($deltatime > 0) 
						{
							//calculate passed distances
							$distance = getCoordDistance($last_lat, $last_lon, $last_alt, $objects[$id]->lat, $objects[$id]->lon, $objects[$id]->alt);
							$distance_lat = $objects[$id]->lat - $last_lat;
							$distance_lon = $objects[$id]->lon - $last_lon;
							$distance_alt = $objects[$id]->alt - $last_alt;
							
							//calculate velocities
							$objects[$id]->vel = ($objects[$id]->vel * ($SMOOTHTIMES-1) + $distance / $deltatime) / $SMOOTHTIMES;
							$objects[$id]->vel_lat = ($objects[$id]->vel_lat * ($SMOOTHTIMES-1) + $distance_lat / $deltatime) / $SMOOTHTIMES;
							$objects[$id]->vel_lon = ($objects[$id]->vel_lon * ($SMOOTHTIMES-1) + $distance_lon / $deltatime) / $SMOOTHTIMES;
							$objects[$id]->vel_alt = ($objects[$id]->vel_alt * ($SMOOTHTIMES-1) + $distance_alt / $deltatime) / $SMOOTHTIMES;
							
							//start message
							if ($objects[$id]->type == "AIRPLANE" && !$objects[$id]->started && $objects[$id]->vel > 60) 
							{
								$objects[$id]->started = true;
								fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_TAKEOFF', " . $id . ", '" . $coalitions[$objects[$id]->coalitionId]->color . "', '" . $objects[$id]->type . "', '" . $objects[$id]->typename . "', '" . $objects[$id]->pilotname . "', '', '', 0, '', '', '', ''), \n");
							}
							//landing message
							if ($objects[$id]->type == "AIRPLANE" && $objects[$id]->started && $objects[$id]->vel < 40) 
							{
								$objects[$id]->started = false;
								fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_LAND', " . $id . ", '" . $coalitions[$objects[$id]->coalitionId]->color . "', '" . $objects[$id]->type . "', '" . $objects[$id]->typename . "', '" . $objects[$id]->pilotname . "', '', '', 0, '', '', '', ''), \n");
							}
						}
					}
					
					
					//first position update
					if (!$objects[$id]->position) 
					{
						//missile shot - get shooter - add shoot message
						if ($objects[$id]->type == "MISSILE" || $objects[$id]->type == "ROCKET" || $objects[$id]->type == "BOMB") 
						{
							if ($shooter = getClosestObject($missiontime, $objects, $objects[$id])) 
							{
								fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_SHOT', " . $shooter->id . ", '" . $coalitions[$shooter->coalitionId]->color . "', '" . $shooter->type . "', '" . $shooter->typename . "', '" . $shooter->pilotname . "', '" . $objects[$id]->type . "', '" . $objects[$id]->typename . "', 0, '', '', '', ''), \n");
							}
						}
						
						//ejection - add ejection message
						if ($objects[$id]->type == "SHRAPNEL" && $objects[$id]->typename == "PILOTACES") 
						{
							echo $objects[$id]->parendId . "\n";
							if ($shooter = getClosestObject($missiontime, $objects, $objects[$id])) 
							{
								fwrite($sqlfile, "(NULL, " . floor($missionstarttime + $missiontime) . ", " . floor($missiontime) . ", 'S_EVENT_EJECTION', " . $shooter->id . ", '" . $coalitions[$shooter->coalitionId]->color . "', '" . $shooter->type . "', '" . $shooter->typename . "', '" . $shooter->pilotname . "', '', '', 0, '', '', '', ''), \n");
							}
						}
						
						//write info for next time
						$objects[$id]->position = true;
					}
				}
			}	
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
	fclose($file);
}


//close sql file
fclose($sqlfile);




//functions

function getUnixTimeFromTimestamp($timestamp) 
{
	$split = explode("T", $timestamp);
	$datestr = $split[0];
	$timestr = substr($split[1], 0, strlen($split[1])-1);
	
	$datetime = DateTime::createFromFormat("Y-m-d H:i:s", $datestr . " " . $timestr, new DateTimeZone("UTC"));
	return $datetime->getTimestamp();
}

function getCoalition($str) 
{
	$info = explode(",", $str);
	$ret = new stdClass();
	$ret->name = $info[0];
	$ret->color = strtolower($info[1]);
	return $ret;
}

//get closest object from position
function getClosestObject($misstime, $objects, $object1, $minimum_dist=9999999999) {
	$mindist = $minimum_dist;
	$minid = -1;
	
	foreach($objects as $id=>$object) 
	{
		if ($object->position && ($object->type == "AIRPLANE" || $object->type == "HELICOPTER")) 
		{
			//get distance
			$object = extrapolateObjectPosition($misstime, $object);
			$distance = getCoordDistance($object1->lat, $object1->lon, $object1->alt, $object->lat, $object->lon, $object->alt);
			echo $object->pilotname . " - " . $distance . "\n";
			
			//get minimum distance
			if ($distance < $mindist) 
			{
				$mindist = $distance;
				$minid = $id;
			}
		}
	}
	
	if ($minid > 0) 
	{
		return $objects[$minid];
	}
}


//get distance between two coordinates
function getCoordDistance($lat1, $lon1, $alt1, $lat2, $lon2, $alt2) {
		
	$distlon = getDistanceLon($lon1, $lon2, $lat1, $lat2);
	$distlat = getDistanceLat($lat1, $lat2);
	$distalt = getDistanceAlt($alt1, $alt2);
	
	//get distance
	return sqrt($distlon*$distlon + $distlat*$distlat + $distalt*$distalt);
}

function getDistanceLat($lat1, $lat2) {
	return ($lat2 - $lat1)*111120;
}

function getDistanceAlt($alt1, $alt2) {
	return ($alt2 - $alt1);
}

function getDistanceLon($lon1, $lon2, $lat1, $lat2) {
	$medlat = ($lat2 + $lat1) / 2;
	$loncoeff = cos($medlat/57.2957795131); //medium latitude coefficient

	return ($lon2 - $lon1)*$loncoeff*111120;
}

//update position from known velocities
function extrapolateObjectPosition($newmissiontime, $object) {
	$deltat = $newmissiontime - $object->lastupdate;
	
	//if ($deltat > 0 && $object->vel > 0) {
		$object->lat += $object->vel_lat * ($deltat);
		$object->lon += $object->vel_lon * ($deltat);
		$object->alt += $object->vel_alt * ($deltat);
		$object->lastupdate = $newmissiontime;
		
	//}
	return $object;
}

//get cos between one objects heading and an other object
function getRelativePosition($object1, $object2) {
	if ($object1->position) {
		//vector from obj1 to obj2
		$toObject2 = new stdClass();
		$toObject2->lat = getDistanceLat($object1->lat, $object2->lat);
		$toObject2->lon = getDistanceLon($object1->lon, $object2->lon, $object1->lat, $object2->lat);
		$toObject2->alt = getDistanceLat($object1->alt, $object2->alt);
		
		$distance = sqrt($toObject2->lat*$toObject2->lat + $toObject2->lon*$toObject2->lon + $toObject2->alt*$toObject2->alt);
		
		return ($toObject2->lat * $object1->vel_lat + $toObject2->lon * $object1->vel_lon + $toObject2->alt * $object1->lon) / $distance / $object1->vel;
	}
	return 0;
}
	
?>