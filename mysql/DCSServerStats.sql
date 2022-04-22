-- --------------------------------------------------------
-- Verkkotietokone:              192.168.2.29
-- Palvelinversio:               10.5.13-MariaDB - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Versio:              10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for dcstat
CREATE DATABASE IF NOT EXISTS `dcstat` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `dcstat`;

-- Dumping structure for taulu dcstat.aircrafts
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.dcs_events
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
  `TargetPlayer` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.dcs_parser_log
CREATE TABLE IF NOT EXISTS `dcs_parser_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `durationms` int(11) NOT NULL,
  `events` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.flights
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.hitsshotskills
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
  `target_raw_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.pilots
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.pilot_aircrafts
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.position_data
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
) ENGINE=InnoDB AUTO_INCREMENT=1121 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

-- Dumping structure for taulu dcstat.weapons
CREATE TABLE IF NOT EXISTS `weapons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `shots` int(11) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Tietojen vientiä ei oltu valittu.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
