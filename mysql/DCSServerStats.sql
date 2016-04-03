-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 03. Apr 2016 um 16:19
-- Server-Version: 10.1.10-MariaDB
-- PHP-Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `DCSServerStats`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `aircrafts`
--

CREATE TABLE `aircrafts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `flights` int(11) NOT NULL DEFAULT '0',
  `flighttime` int(11) NOT NULL DEFAULT '0',
  `ejects` int(11) NOT NULL DEFAULT '0',
  `crashes` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `shots` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  `inc_hits` int(11) NOT NULL DEFAULT '0',
  `inc_kills` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dcs_events`
--

CREATE TABLE `dcs_events` (
  `id` int(11) NOT NULL,
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
  `TargetPlayer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dcs_parser_log`
--

CREATE TABLE `dcs_parser_log` (
  `id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `durationms` int(11) NOT NULL,
  `events` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `takeofftime` int(11) NOT NULL,
  `takeoffmissiontime` int(11) NOT NULL,
  `landingtime` int(11) NOT NULL,
  `landingmissiontime` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `coalition` varchar(255) NOT NULL,
  `endofflighttype` varchar(255) NOT NULL DEFAULT 'landing'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hitsshotskills`
--

CREATE TABLE `hitsshotskills` (
  `id` int(11) NOT NULL,
  `initiatorPid` int(11) NOT NULL,
  `initiatorAcid` int(11) NOT NULL,
  `initiatorCoa` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `missiontime` int(11) NOT NULL,
  `targetPid` int(11) NOT NULL DEFAULT '0',
  `targetAcid` int(11) NOT NULL DEFAULT '0',
  `targetCoa` varchar(255) NOT NULL DEFAULT '',
  `weaponid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pilots`
--

CREATE TABLE `pilots` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `flighttime` int(11) NOT NULL DEFAULT '0',
  `flights` int(11) NOT NULL DEFAULT '0',
  `crashes` int(11) NOT NULL DEFAULT '0',
  `ejects` int(11) NOT NULL DEFAULT '0',
  `shots` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  `inc_hits` int(11) NOT NULL DEFAULT '0',
  `inc_kills` int(11) NOT NULL DEFAULT '0',
  `lastactive` int(11) NOT NULL DEFAULT '0',
  `online` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pilot_aircrafts`
--

CREATE TABLE `pilot_aircrafts` (
  `id` int(11) NOT NULL,
  `pilotid` int(11) NOT NULL,
  `aircraftid` int(11) NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `flights` int(11) NOT NULL DEFAULT '0',
  `crashes` int(11) NOT NULL DEFAULT '0',
  `ejects` int(11) NOT NULL DEFAULT '0',
  `shots` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0',
  `inc_hits` int(11) NOT NULL DEFAULT '0',
  `inc_kills` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `weapons`
--

CREATE TABLE `weapons` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `shots` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `kills` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `aircrafts`
--
ALTER TABLE `aircrafts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `dcs_events`
--
ALTER TABLE `dcs_events`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `dcs_parser_log`
--
ALTER TABLE `dcs_parser_log`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hitsshotskills`
--
ALTER TABLE `hitsshotskills`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `pilots`
--
ALTER TABLE `pilots`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `pilot_aircrafts`
--
ALTER TABLE `pilot_aircrafts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `aircrafts`
--
ALTER TABLE `aircrafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `dcs_events`
--
ALTER TABLE `dcs_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `dcs_parser_log`
--
ALTER TABLE `dcs_parser_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `hitsshotskills`
--
ALTER TABLE `hitsshotskills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `pilots`
--
ALTER TABLE `pilots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `pilot_aircrafts`
--
ALTER TABLE `pilot_aircrafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `weapons`
--
ALTER TABLE `weapons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
