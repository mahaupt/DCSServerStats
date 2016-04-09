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
	
	//$driver = new mysqli_driver();
	//$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	//CLI Mode Only !!!
	if (!isset($argc) || isset($_SERVER['REMOTE_ADDR'])) {
		//die("CLI Only!");
	}
	
	//establish database connection
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	$mysqli->query("TRUNCATE TABLE pilots");
	$mysqli->query("TRUNCATE TABLE aircrafts");
	$mysqli->query("TRUNCATE TABLE flights");
	$mysqli->query("TRUNCATE TABLE weapons");
	$mysqli->query("TRUNCATE TABLE pilot_aircrafts");
	$mysqli->query("TRUNCATE TABLE dcs_events");
	$mysqli->query("TRUNCATE TABLE dcs_parser_log");
	$mysqli->query("TRUNCATE TABLE hitsshotskills");
	
?>