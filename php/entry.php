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
	
	//password check
	if ($_POST['pw'] != $PASSWORD) die();
	
	
	//establish database connection
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	$query = "INSERT INTO `dcs_events`(`id`, `time`, `missiontime`, `event`, `InitiatorID`, `InitiatorCoa`, `InitiatorGroupCat`, `InitiatorType`, `InitiatorPlayer`, `WeaponCat`, `WeaponName`, `TargetID`, `TargetCoa`, `TargetGroupCat`, `TargetType`, `TargetPlayer`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("iisissssssissss", $_POST['time'], 
											$_POST['missiontime'], 
											$_POST['event'], 
											$_POST['initid'], 
											$_POST['initcoa'], 
											$_POST['initgroupcat'],
											$_POST['inittype'], 
											$_POST['initplayer'], 
											$_POST['eweaponcat'],
											$_POST['eweaponname'],  
											$_POST['targid'], 
											$_POST['targcoa'], 
											$_POST['targgroupcat'], 
											$_POST['targtype'], 
											$_POST['targplayer']);
	
	$stmt->execute();
?>