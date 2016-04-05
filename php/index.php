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
	require "functions.inc.php";
	
	$driver = new mysqli_driver();
	$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	
?>

<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCSServerStats</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="text/javascript" src="css/tools.js"></script>
  </head>
  <body onload="timer()">
	  <?php echoSiteContent($mysqli); ?>
	  <br><br>
	  <?php echoFooter($mysqli); ?>
  </body>
</html>

