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
	
	$PASSWORD = "secretpassword"; //password for data transmission
	$UPLOAD_PASSWORD = "secretpassword"; //password for /upload/index.php
	
	$MYSQL_HOST = "127.0.0.1";
	$MYSQL_USER = "root";
	$MYSQL_PASS = "";
	$MYSQL_DB = "DCSServerStats";
	
	$AUTO_CRON = true; //disable autoparsing of event data, you must set up a cronjob to call cron.php
?>