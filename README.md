# DCSServerStats



**This Program exports Events from DCS Servers to a SQL Database**

Creates statistics for:
- flight times (takeoffs and landings)
- weapon hits, shots and kills
- Aircraft and Weapon usage
- Landing, Ejection, Crash statistics

You can use this data to:
- Set up a Killboard or Rankinglist on your Website
- To show the training process of your Wing
- You can even see live data and who is **currently flying**


## How does it work?

The lua script takes all Events from the Server and sends it via UDP to a java program.
The Java Program works as a queue and reduces workload. It sends the eventdata via https to the webserver where the database is located
The webserver takes the eventdata and saves it to the database.
To make the data readable, a cronjob parses and evaluates the eventdata and fills it in the statistic tables.


## Installation
1. Move the DCSUDPMissionDataExport.lua and DCSUDPPositionDataExport.lua to your %USERPROFILE%/Saved Games/DCS World/Scripts folder
2. Edit your MissionScripting.lua in %PROGRAMFILES%/DCS World/Scripts/ - add this line:
```lua
--Initialization script for the Mission lua Environment (SSE)

dofile('Scripts/ScriptingSystem.lua')
dofile(lfs.writedir()..'Scripts/DCSUDPMissionDataExport.lua') --<-- add this line

[...]
```

3. (For Position Updates) Edit your Export.lua in %USERPROFILE%/Saved Games/DCS World/Scripts and add this line:
```lua
dofile(lfs.writedir()..'Scripts/DCSUDPPositionDataExport.lua') --<-- add this line
```

4. Move all PHP Files on your Webserver where your Database is running. Make sure you have the PHP and MySQLi extension installed.
5. Setup the Database with the DCSServerStats.sql file
6. Edit config.inc.php, set the user and the passwort for the database access.
7. Create a .bat or .sh file to run the java program. The password must be the same as $PASSWORD in your config.inc.php file
```sh
# java -jar DCSServerStats.jar [BindIP for DCS UDP packets] [Port] [URL to entry.php] [password] 

java -jar DCSServerStats.jar 127.0.0.1 9182 https://example.com/dcsexport/entry.php secretpassword
```

## Start the Server
Run the Java Program before starting your DCS server. You can easily do this by creating a .bat/.sh file.

## Using a Cronjob
While not recommendet, you can use a cronjob to parse your event data. Simply turn $AUTO_CRON to false and set up a cronjob for cron.php for every 10 or 15 minutes.

## Using the xml Uploader
You can use the xml uploader to upload your as xml exported flights from Tacview directly into the database. Personally, I use it for all the BMS Pilots. 

## Using multiple servers
You can use this tool with multiple servers
To use this program properly, you need a separate event table for each of your server.
Simply copy the entry.php and set at the beginning of the file $OVERRIDE_EVENT_TABLE to the name of your separate event table. You also need the java program running on each server.
