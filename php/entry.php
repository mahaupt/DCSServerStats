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
	
	//password check
	if ($_POST['pw'] != $PASSWORD) die();
	//parameter check
	if ($_POST['size'] <= 0 && $_POST['size'] > 10) die('wrong param');
	
	
	//debugging
	$driver = new mysqli_driver();
	$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	
	//establish database connection
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	$query = "INSERT INTO `dcs_events`(`id`, `time`, `missiontime`, `event`, `InitiatorID`, `InitiatorCoa`, `InitiatorGroupCat`, `InitiatorType`, `InitiatorPlayer`, `WeaponCat`, `WeaponName`, `TargetID`, `TargetCoa`, `TargetGroupCat`, `TargetType`, `TargetPlayer`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	
	$stmt = $mysqli->prepare($query);
	
	$evts = 0;
	while($evts < $_POST['size']) {
		$stmt->bind_param("iisissssssissss", $_POST['time_' . $evts], 
												$_POST['missiontime_' . $evts], 
												$_POST['event_' . $evts], 
												$_POST['initid_' . $evts], 
												$_POST['initcoa_' . $evts], 
												$_POST['initgroupcat_' . $evts],
												$_POST['inittype_' . $evts], 
												$_POST['initplayer_' . $evts], 
												$_POST['eweaponcat_' . $evts],
												$_POST['eweaponname_' . $evts],  
												$_POST['targid_' . $evts], 
												$_POST['targcoa_' . $evts], 
												$_POST['targgroupcat_' . $evts], 
												$_POST['targtype_' . $evts], 
												$_POST['targplayer_' . $evts]);
		
		$stmt->execute();
		$evts++;
	}
	
	
	//quick and dirty: call cron.php to do an update if necessary
	if ($AUTO_CRON) {
		$CRON_NO_CLI_SET = true;
		include "cron.php";
	}
?>