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
		if (isset($_GET['flights'])) {
			if (isset($_GET['delete'])) {
				$simStatsAdmin->removeFlight($_GET['delete']);
			} else if (isset($_POST['addflight'])) {
				
				$totstr = $_POST["th"] . ":" . $_POST["tm"] . "-" . $_POST["td"] . "." . $_POST["tmo"] . "." . $_POST["ty"];
				$ldgtstr = $_POST["lh"] . ":" . $_POST["lm"] . "-" . $_POST["ld"] . "." . $_POST["lmo"] . "." . $_POST["ly"];
				
				$takeofftime = DateTime::createFromFormat("H:i-d.m.Y", $totstr, new DateTimeZone("UTC"));
				$landingtime = DateTime::createFromFormat("H:i-d.m.Y", $ldgtstr, new DateTimeZone("UTC"));
				
				$message = $simStatsAdmin->addFlight(intval($_POST["pilot"]), intval(["aircraft"]), $takeofftime->getTimestamp(), $landingtime->getTimestamp());
			}
		} else {
			if (isset($_POST["rename"])) {
				$message = "Pilot renamed";
				$simStatsAdmin->renamePilot($_POST["pilotid"], $_POST["newname"]);
			} else if (isset($_POST["transferpilot"])) {
				$message = "Pilot transfered";
				$simStatsAdmin->mergePilot($_POST["frompilotid"], $_POST["topilotid"]);
			} else if(isset($_GET["makeai"])) {
				$message = "Declared Pilot as AI";
				$simStatsAdmin->renamePilot($_GET["makeai"], "AI");
			} else if(isset($_GET["delete"])) {
				$message = "Pilot Removed";
				$simStatsAdmin->removePilot($_GET['delete']);
			}
		}
	}
	
?>

<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMSStats Admin Page</title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="text/javascript" src="../css/tools.js"></script>
  </head>
  <body>
	<a href="?">Pilots</a> - <a href="?flights">Flights</a><br><br>
  	<?php
	  	if ($logged_in) {
		  	if (isset($_GET['flights'])) {
			  	$simStatsAdmin->echoAddFlight();
				$simStatsAdmin->echoAdminFlightsTable();
			} else {
				$simStatsAdmin->echoAdminPilotsTable(); 
			}	
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