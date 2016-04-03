<?php
	$PASSWORD = "waldblick56";
	$MYSQL_HOST = "127.0.0.1";
	$MYSQL_USER = "root";
	$MYSQL_PASS = "";
	$MYSQL_DB = "DCSServerStats";
	
	
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