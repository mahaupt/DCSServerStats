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
	include "import-xml.php";
	include "../functions.inc.php";
	
	$driver = new mysqli_driver();
	$driver->report_mode = MYSQLI_REPORT_ERROR;
	
	$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
	
	$PARSE_FILE = false;
	$PARSE_FILE_NAME = "";
	$error = "";
	$anti_bruteforce = false;
	
	
	//simple anti bruteforce
	if (!isset($_SESSION['xml_upload_time'])) {
		$_SESSION['xml_upload_time'] = time();
		$_SESSION['xml_upload_mult'] = 1;
		$anti_bruteforce = true;
	} else {
		//too fast
		if (time()-$_SESSION['xml_upload_time'] <= 3*$_SESSION['xml_upload_mult']) {
			$anti_bruteforce = true;
			$_SESSION['xml_upload_mult'] += 1;
			$error = "<p style='color: red'>Slow down! </p><p class='js_timer_down'>" . timeToString(3*$_SESSION['xml_upload_mult']+1) . "</p>";
		} else {
			$_SESSION['xml_upload_mult'] = 1;
		}
		$_SESSION['xml_upload_time'] = time();
	}
	
	
	//get uploaded file
	if (isset($_FILES['file']) && isset($_POST['pwd']) && !$anti_bruteforce) {
		if ($_POST['pwd'] == $UPLOAD_PASSWORD) {
			if (is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['type'] == "text/xml") {
				$PARSE_FILE_NAME = $_FILES['file']['tmp_name'];
				$PARSE_FILE = true;
				$error = "<p style='color: green;'>Upload successful!</p>";
			} else {
				$error = "<p style='color: red;'>Upload error!</p>";
			}
		} else {
			$error = "<p style='color: red;'>Wrong Password!</p>";
		}
		
	}
	
	
	
?>

<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCSServerStats File Upload</title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="text/javascript" src="../css/tools.js"></script>
  </head>
  <body onload="timer()">
	  <h2>File Upload</h2><br><br>
	  Upload your Tacview Flight Log in .xml Format<br><br>
	  <form method="post" action="#" enctype="multipart/form-data">
		  <input type="hidden" name="MAX_FILE_SIZE" value= "100000000">
		  <input type="file" name="file"><br>
		  <input type="password" name="pwd"><br>
		  <input type="Submit" name="submit" value="Upload">
	  </form>
	  <?php echo $error; ?>
  </body>
</html>


<?php
	if ($PARSE_FILE && $AUTO_CRON && !$anti_bruteforce) {
		$CRON_NO_CLI_SET = true;
		$OVERRIDE_EVENT_TABLE = "bms_events";
		
		$import = new XML_Import($PARSE_FILE_NAME);
		$import->writeToDatabase($mysqli, $OVERRIDE_EVENT_TABLE);
		
		include "../cron.php";
	}
	
?>

