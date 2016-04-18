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
	
	require_once "config.inc.php";
	require_once "cron_functions.inc.php";
	require_once "functions.inc.php";
	
	
	
	//security
	if ((!isset($argc) || isset($_SERVER['REMOTE_ADDR'])) && !isset($CRON_NO_CLI_SET)) {
		die("CLI Only!");
	}
	
	
	//get mysql event table
	$EVENT_TABLE = "dcs_events";
	if (isset($OVERRIDE_EVENT_TABLE)) {
		$EVENT_TABLE = $OVERRIDE_EVENT_TABLE;
	}
	
	
	$driver = new mysqli_driver();
	$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	//cron
	$dcsstats = new DCSStatsCron($mysqli, $EVENT_TABLE);
	$dcsstats->startProcessing();
	
	//auto merge pilots
	$simStatsAdmin = new SimStatsAdmin($mysqli);
	$simStatsAdmin->autoMergePilots();
?>