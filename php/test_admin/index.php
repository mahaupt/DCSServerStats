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

	session_start();

	require "../config.inc.php";
	require "../functions.inc.php";
	
	//disable to prevent bruteforce
	//die();
	
	if (isset($_POST["pwd"]) && $_POST["pwd"] == $UPLOAD_PASSWORD) {
		$_SESSION["UPWD"] = md5($_POST["pwd"]);
	}
	
	$logged_in = false;
	if (isset($_SESSION["UPWD"])) {
		if ($_SESSION["UPWD"] == md5($UPLOAD_PASSWORD)) {
			$logged_in = true;
		}
	}
	
	if ($logged_in) 
	{
		$simStatsAdmin = new SimStatsAdmin(new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB));
		
		
		$message="";
		if (isset($_POST["rename"])) {
			$message = "Pilot renamed";
			$simStatsAdmin->renamePilot($_POST["pilotid"], $_POST["newname"]);
		} else if (isset($_POST["transferpilot"])) {
			$message = "Pilot transfered";
			$simStatsAdmin->mergePilot($_POST["frompilotid"], $_POST["topilotid"]);
		} else if(isset($_GET["makeai"])) {
			$message = "Declared Pilot as AI";
			$simStatsAdmin->renamePilot($_GET["makeai"], "AI");
		} else if(isset($_GET["showhidekills"])) {
			$message = "Pilot Kills Display Toggle";
			$simStatsAdmin->setShowKillsFlag($_GET["showhidekills"], (bool)$_GET["showkills"]);
		} else if(isset($_GET["forceland"])) {
			$message = "Force Landed Pilot";
			$simStatsAdmin->landFlight($_GET["forceland"], time());
		} else if(isset($_GET["delete"])) {
			$message = "Pilot Removed";
			$simStatsAdmin->removePilot($_GET['delete']);
		}
	}
	
?>

<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCSServerStats Admin Page</title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="text/javascript" src="../css/tools.js"></script>
  </head>
  <body>
  	<?php
	  	if ($logged_in) {
			$simStatsAdmin->echoAdminPilotsTable();  	
			echo $message;
		} else {
	?>
	<form method="post">
		<input type="password" name="pwd"><input type="submit" value="Login">
	</form>
	
	<?php
		}
	?>
	  
  </body>
</html>