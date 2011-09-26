-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 26, 2009 at 12:12 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ovckb`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda`
--

CREATE TABLE IF NOT EXISTS `agenda` (
  `agenda_id` int(11) NOT NULL auto_increment,
  `schooljaar` varchar(4) collate utf8_bin NOT NULL,
  `week` tinyint(4) NOT NULL,
  `dag` tinyint(4) NOT NULL,
  `lesuur` varchar(4) collate utf8_bin NOT NULL,
  `notitie_id` int(11) NOT NULL,
  PRIMARY KEY  (`agenda_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=984 ;

-- --------------------------------------------------------

--
-- Table structure for table `caps`
--

CREATE TABLE IF NOT EXISTS `caps` (
  `cap_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) collate utf8_bin NOT NULL,
  `comment` text collate utf8_bin NOT NULL,
  PRIMARY KEY  (`cap_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `doc2grp2vak`
--

CREATE TABLE IF NOT EXISTS `doc2grp2vak` (
  `doc2grp2vak_id` int(11) NOT NULL auto_increment,
  `ppl_id` int(11) NOT NULL,
  `grp2vak_id` int(11) NOT NULL,
  PRIMARY KEY  (`doc2grp2vak_id`),
  UNIQUE KEY `ppl_id` (`ppl_id`,`grp2vak_id`),
  KEY `ppl_id_2` (`ppl_id`),
  KEY `grp2vak_id` (`grp2vak_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2236 ;

-- --------------------------------------------------------

--
-- Table structure for table `grp`
--

CREATE TABLE IF NOT EXISTS `grp` (
  `grp_id` int(11) NOT NULL auto_increment,
  `naam` varchar(32) collate utf8_bin NOT NULL,
  `schooljaar` enum('0809','0910','1011','1112') collate utf8_bin NOT NULL,
  `stamklas` tinyint(1) NOT NULL default '0',
  `grp_type_id` varchar(32) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`grp_id`),
  UNIQUE KEY `naam` (`naam`,`schooljaar`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=758 ;

-- --------------------------------------------------------

--
-- Table structure for table `grp2vak`
--

CREATE TABLE IF NOT EXISTS `grp2vak` (
  `grp2vak_id` int(11) NOT NULL auto_increment,
  `grp_id` int(11) NOT NULL,
  `vak_id` int(11) default NULL,
  PRIMARY KEY  (`grp2vak_id`),
  UNIQUE KEY `grp_id` (`grp_id`,`vak_id`),
  KEY `vak_id` (`vak_id`),
  KEY `grp_id_2` (`grp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2660 ;

-- --------------------------------------------------------

--
-- Table structure for table `grp2vak2agenda`
--

CREATE TABLE IF NOT EXISTS `grp2vak2agenda` (
  `grp2vak_id` int(11) NOT NULL,
  `agenda_id` int(11) NOT NULL,
  PRIMARY KEY  (`grp2vak_id`,`agenda_id`),
  KEY `grp_id` (`grp2vak_id`),
  KEY `agenda_id` (`agenda_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `grp_types`
--

CREATE TABLE IF NOT EXISTS `grp_types` (
  `grp_type_id` int(11) NOT NULL auto_increment,
  `grp_type_naam` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_baas` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_lid` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_baas_mv` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_baas_ev` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_lid_mv` varchar(32) collate utf8_bin NOT NULL,
  `grp_type_lid_ev` varchar(32) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`grp_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `event` varchar(32) collate utf8_bin NOT NULL,
  `orig_ppl_id` int(11) default NULL,
  `ppl_id` int(11) default NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ip` varchar(15) collate utf8_bin NOT NULL,
  `comment` text collate utf8_bin,
  PRIMARY KEY  (`id`),
  KEY `event` (`event`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=9578 ;

-- --------------------------------------------------------

--
-- Table structure for table `notities`
--

CREATE TABLE IF NOT EXISTS `notities` (
  `notitie_id` int(11) NOT NULL auto_increment,
  `creat` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `text` text collate utf8_bin,
  PRIMARY KEY  (`notitie_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1211 ;

-- --------------------------------------------------------

--
-- Table structure for table `ppl`
--

CREATE TABLE IF NOT EXISTS `ppl` (
  `ppl_id` int(11) NOT NULL auto_increment,
  `naam1` varchar(64) collate utf8_bin NOT NULL,
  `naam2` varchar(16) collate utf8_bin NOT NULL,
  `naam0` varchar(128) collate utf8_bin NOT NULL,
  `login` varchar(16) character set utf8 collate utf8_unicode_ci NOT NULL,
  `active` tinyint(1) default '0' COMMENT '0 is active and NULL is inactive',
  `password` varchar(41) collate utf8_bin default NULL,
  `pw_reset_count` int(11) NOT NULL default '0',
  `email` varchar(128) collate utf8_bin default NULL,
  `oldid` int(11) default NULL COMMENT 'tmp',
  `type` enum('leerling','leraar','stagiare','oop','ouder') character set ascii collate ascii_bin NOT NULL,
  `timeout` int(11) NOT NULL default '60',
  `nieuwsbrief` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ppl_id`),
  UNIQUE KEY `login` (`login`,`active`),
  KEY `ppl_id` (`ppl_id`,`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3603 ;

-- --------------------------------------------------------

--
-- Table structure for table `ppl2agenda`
--

CREATE TABLE IF NOT EXISTS `ppl2agenda` (
  `ppl_id` int(11) NOT NULL,
  `agenda_id` int(11) NOT NULL,
  `allow_edit` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ppl_id`,`agenda_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ppl2caps`
--

CREATE TABLE IF NOT EXISTS `ppl2caps` (
  `ppl_id` int(11) NOT NULL,
  `cap_id` int(11) NOT NULL,
  `granter_ppl_id` int(11) default NULL,
  PRIMARY KEY  (`ppl_id`,`cap_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ppl2grp`
--

CREATE TABLE IF NOT EXISTS `ppl2grp` (
  `ppl_id` int(11) NOT NULL,
  `grp_id` int(11) NOT NULL,
  PRIMARY KEY  (`ppl_id`,`grp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(32) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags2notities`
--

CREATE TABLE IF NOT EXISTS `tags2notities` (
  `tag_id` int(11) NOT NULL,
  `notitie_id` int(11) NOT NULL,
  UNIQUE KEY `tag_id_2` (`tag_id`,`notitie_id`),
  KEY `tag_id` (`tag_id`),
  KEY `notitie_id` (`notitie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int(11) NOT NULL auto_increment,
  `status` enum('open','closed') collate utf8_bin NOT NULL,
  PRIMARY KEY  (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vak`
--

CREATE TABLE IF NOT EXISTS `vak` (
  `vak_id` int(11) NOT NULL auto_increment,
  `afkorting` varchar(8) collate utf8_bin NOT NULL,
  `naam` varchar(32) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`vak_id`),
  UNIQUE KEY `afkorting` (`afkorting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=100 ;

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `KB_LGRP`(
grp VARCHAR( 32 ) ,
vak VARCHAR( 8 )
) RETURNS varchar(41) CHARSET utf8
    DETERMINISTIC
BEGIN RETURN IF( grp REGEXP vak, grp, CONCAT( grp, '/', vak ) ) ; END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `KB_NAAM`(
naam0 VARCHAR( 128 ) ,
naam1 VARCHAR( 64 ) ,
naam2 VARCHAR( 16 )
) RETURNS varchar(190) CHARSET utf8
    DETERMINISTIC
BEGIN RETURN CONCAT( naam1, ' ', TRIM( LEADING ' ' FROM CONCAT( naam2, ' ', naam0 ) ) ); END$$

DELIMITER ;
