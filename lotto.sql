-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2012 at 07:04 AM
-- Server version: 5.1.58
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lotto`
--

-- --------------------------------------------------------

--
-- Table structure for table `lotto_id`
--

CREATE TABLE IF NOT EXISTS `lotto_id` (
  `lotto_id` bigint(20) NOT NULL,
  `ticketprice` decimal(17,2) unsigned DEFAULT NULL,
  `max_tickets` int(5) unsigned DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `sendto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sendto_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `forumlink` text COLLATE utf8_unicode_ci,
  `dice` text COLLATE utf8_unicode_ci,
  `winner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `winner_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`lotto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Lotto information';

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `uniqticket` bigint(20) NOT NULL AUTO_INCREMENT,
  `owner` varchar(255) DEFAULT NULL,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `payment` bigint(20) unsigned DEFAULT NULL,
  `recieved` datetime DEFAULT NULL,
  `ticketnumber` text,
  `lotto_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`uniqticket`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `walletjournal`
--

CREATE TABLE IF NOT EXISTS `walletjournal` (
  `date` datetime NOT NULL,
  `refID` bigint(20) unsigned NOT NULL,
  `refTypeID` smallint(5) unsigned NOT NULL,
  `ownerName1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerID1` bigint(20) unsigned DEFAULT NULL,
  `ownerName2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerID2` bigint(20) unsigned DEFAULT NULL,
  `argName1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `argID1` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(17,2) NOT NULL,
  `balance` decimal(17,2) NOT NULL,
  `reason` text COLLATE utf8_unicode_ci,
  `processed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`refID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
