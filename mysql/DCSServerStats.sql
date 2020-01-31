-- --------------------------------------------------------
-- Verkkotietokone:              192.168.2.20
-- Palvelinversio:               10.3.21-MariaDB-log - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Versio:              10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for DCSServerStats
DROP DATABASE IF EXISTS `DCSServerStats`;
CREATE DATABASE IF NOT EXISTS `DCSServerStats` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `DCSServerStats`;

-- Dumping structure for taulu DCSServerStats.aircrafts
DROP TABLE IF EXISTS `aircrafts`;
CREATE TABLE IF NOT EXISTS `aircrafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `flights` int(11) NOT NULL DEFAULT 0,
  `flighttime` int(11) NOT NULL DEFAULT 0,
  `ejects` int(11) NOT NULL DEFAULT 0,
  `crashes` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `shots` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) NOT NULL DEFAULT 0,
  `inc_hits` int(11) NOT NULL DEFAULT 0,
  `inc_kills` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.aircrafts: ~0 rows (suunnilleen)
DELETE FROM `aircrafts`;
/*!40000 ALTER TABLE `aircrafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `aircrafts` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.dcs_events
DROP TABLE IF EXISTS `dcs_events`;
CREATE TABLE IF NOT EXISTS `dcs_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` bigint(20) NOT NULL,
  `missiontime` bigint(20) NOT NULL,
  `event` varchar(255) NOT NULL,
  `InitiatorID` int(11) NOT NULL,
  `InitiatorCoa` varchar(255) NOT NULL,
  `InitiatorGroupCat` varchar(255) NOT NULL,
  `InitiatorType` varchar(255) NOT NULL,
  `InitiatorPlayer` varchar(255) NOT NULL,
  `WeaponCat` varchar(255) NOT NULL,
  `WeaponName` varchar(255) NOT NULL,
  `TargetID` int(11) NOT NULL,
  `TargetCoa` varchar(255) NOT NULL,
  `TargetGroupCat` varchar(255) NOT NULL,
  `TargetType` varchar(255) NOT NULL,
  `TargetPlayer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.dcs_events: ~0 rows (suunnilleen)
DELETE FROM `dcs_events`;
/*!40000 ALTER TABLE `dcs_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `dcs_events` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.dcs_parser_log
DROP TABLE IF EXISTS `dcs_parser_log`;
CREATE TABLE IF NOT EXISTS `dcs_parser_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `durationms` int(11) NOT NULL,
  `events` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.dcs_parser_log: ~0 rows (suunnilleen)
DELETE FROM `dcs_parser_log`;
/*!40000 ALTER TABLE `dcs_parser_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `dcs_parser_log` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.flights
DROP TABLE IF EXISTS `flights`;
CREATE TABLE IF NOT EXISTS `flights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `takeofftime` int(11) NOT NULL,
  `takeoffmissiontime` int(11) NOT NULL,
  `landingtime` int(11) NOT NULL,
  `landingmissiontime` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `coalition` varchar(255) NOT NULL,
  `endofflighttype` varchar(255) NOT NULL DEFAULT 'landing',
  `raw_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.flights: ~0 rows (suunnilleen)
DELETE FROM `flights`;
/*!40000 ALTER TABLE `flights` DISABLE KEYS */;
/*!40000 ALTER TABLE `flights` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.hitsshotskills
DROP TABLE IF EXISTS `hitsshotskills`;
CREATE TABLE IF NOT EXISTS `hitsshotskills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `initiatorPid` int(11) NOT NULL,
  `initiatorAcid` int(11) NOT NULL,
  `initiatorCoa` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `missiontime` int(11) NOT NULL,
  `targetPid` int(11) NOT NULL DEFAULT 0,
  `targetAcid` int(11) NOT NULL DEFAULT 0,
  `targetCoa` varchar(255) NOT NULL DEFAULT '',
  `weaponid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `target_raw_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.hitsshotskills: ~0 rows (suunnilleen)
DELETE FROM `hitsshotskills`;
/*!40000 ALTER TABLE `hitsshotskills` DISABLE KEYS */;
/*!40000 ALTER TABLE `hitsshotskills` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.pilots
DROP TABLE IF EXISTS `pilots`;
CREATE TABLE IF NOT EXISTS `pilots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `disp_name` varchar(255) NOT NULL,
  `flighttime` int(11) NOT NULL DEFAULT 0,
  `flights` int(11) NOT NULL DEFAULT 0,
  `crashes` int(11) NOT NULL DEFAULT 0,
  `ejects` int(11) NOT NULL DEFAULT 0,
  `shots` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) NOT NULL DEFAULT 0,
  `inc_hits` int(11) NOT NULL DEFAULT 0,
  `inc_kills` int(11) NOT NULL DEFAULT 0,
  `lastactive` int(11) NOT NULL DEFAULT 0,
  `online` tinyint(1) NOT NULL DEFAULT 0,
  `show_kills` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.pilots: ~0 rows (suunnilleen)
DELETE FROM `pilots`;
/*!40000 ALTER TABLE `pilots` DISABLE KEYS */;
/*!40000 ALTER TABLE `pilots` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.pilot_aircrafts
DROP TABLE IF EXISTS `pilot_aircrafts`;
CREATE TABLE IF NOT EXISTS `pilot_aircrafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT 0,
  `flights` int(11) NOT NULL DEFAULT 0,
  `crashes` int(11) NOT NULL DEFAULT 0,
  `ejects` int(11) NOT NULL DEFAULT 0,
  `shots` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) NOT NULL DEFAULT 0,
  `inc_hits` int(11) NOT NULL DEFAULT 0,
  `inc_kills` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.pilot_aircrafts: ~0 rows (suunnilleen)
DELETE FROM `pilot_aircrafts`;
/*!40000 ALTER TABLE `pilot_aircrafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `pilot_aircrafts` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.position_data
DROP TABLE IF EXISTS `position_data`;
CREATE TABLE IF NOT EXISTS `position_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pilotid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `alt` double NOT NULL,
  `time` int(11) NOT NULL,
  `missiontime` int(11) NOT NULL,
  `raw_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.position_data: ~0 rows (suunnilleen)
DELETE FROM `position_data`;
/*!40000 ALTER TABLE `position_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `position_data` ENABLE KEYS */;

-- Dumping structure for taulu DCSServerStats.weapons
DROP TABLE IF EXISTS `weapons`;
CREATE TABLE IF NOT EXISTS `weapons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `shots` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table DCSServerStats.weapons: ~0 rows (suunnilleen)
DELETE FROM `weapons`;
/*!40000 ALTER TABLE `weapons` DISABLE KEYS */;
/*!40000 ALTER TABLE `weapons` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
