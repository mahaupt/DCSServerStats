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
	
	require "config.inc.php";
	require "cron_classes.php";
	
	
	//$driver = new mysqli_driver();
	//$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	
	//password check
	if (!isset($_GET['pw']) || ($_GET['pw'] !== $PASSWORD)) die();		
	
	
	//start parsing
	$dcs_parser_log = new stdClass();
	$dcs_parser_log->time = time();
	$dcs_parser_log->starttimems = microtime(true) * 1000;
	
	
	//establish database connection
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	
	//get all objects from database
	$dcs_events = getAllDbObjects($mysqli, "dcs_events");
	$dcs_parser_log->events = sizeof($dcs_events);
	
	//add new weapons, pilots, aircrafts to database
	addNewEntrys($mysqli);
	//update counters
	updateCounters($mysqli);
	//process hits shots and kills
	addHitsShotsKills($mysqli, $dcs_events);
	//process landing and takeoff times
	calculateLandingTime($mysqli, $dcs_events);
	//delete events
	deleteProcessedEvents($mysqli);
	
		
	
	//end parsing
	$dcs_parser_log->endtimems = microtime(true) * 1000;
	$dcs_parser_log->durationms = round($dcs_parser_log->endtimems - $dcs_parser_log->starttimems);
	
	//write log entry
	$query = "INSERT INTO dcs_parser_log SET time='" . $dcs_parser_log->time . "', durationms='" . $dcs_parser_log->durationms . "', events='" . $dcs_parser_log->events . "'";
	$mysqli->query($query);
?>
