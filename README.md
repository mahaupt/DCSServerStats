# DCSServerStats

If you like this Program and want to see more cool stuff, please consider a donation :)

[![Paypal Donate](https://www.paypalobjects.com/en_US/DE/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AM7K6U4ELAFBA)


**This Program exports Events from DCS Servers to a SQL Database**

Creates statistics for:
- flight times (takeoffs and landings)
- weapon hits, shots and kills
- Aircraft and Weapon usage
- Landing, Ejection, Crash statistics

You can use this data to:
- Set up a Killboard or Rankinglist on your Website
- To show the training process of your Wing


##How does it work?

The lua script takes all Events from the Server and sends it via UDP to a java program.
The Java Program works as a queue and reduces workload. It sends the eventdata via https to the webserver where the database is located
The webserver takes the eventdata and saves it to the database.
To make the data readable, a cronjob parses and evaluates the eventdata and fills it in the statistic tables.


##Installation
1. Move the DCSUDPMissionDataExport.lua to your %USERPROFILE%/Saved Games/DCS World/Scripts folder
2. Edit your MissionScripting.lua in %PROGRAMFILES%/DCS World/Scripts/ - add this line:
```lua
--Initialization script for the Mission lua Environment (SSE)

dofile('Scripts/ScriptingSystem.lua')
dofile(lfs.writedir()..'Scripts/DCSUDPMissionDataExport.lua') --<-- add this line

[...]
```

3. Move all PHP Files on your Webserver where your Database is running. Make sure you have the PHP and MySQLi extension installed.
4. Setup the Database with the DCSServerStats.sql file
5. Edit config.inc.php, set the user and the passwort for the database access.
6. Create a .bat or .sh file to run the java program. The password must be the same as $PASSWORD in your php file
```sh
# java DCSServerStats [BindIP for DCS UDP packets] [Port] [URL to entry.php] [password] 

java DCSServerStats 127.0.0.1 9182 https://example.com/dcsexport/entry.php secretpassword
```
7. Set a cronjob that runs the cron.php?pw=[password] every 15 to 30 minutes to parse the event data

##Start the Server
Run the Java Program before starting your DCS server. You can easily do this by creating a .bat/.sh file.
