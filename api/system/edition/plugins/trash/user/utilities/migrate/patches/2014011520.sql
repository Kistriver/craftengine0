SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `code` (
  `type` varchar(128) NOT NULL,
  `value` varchar(256) NOT NULL,
  `data` text NOT NULL,
  `time` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login_fail` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `user` int(8) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `browser_v` varchar(100) NOT NULL,
  `platform` varchar(100) NOT NULL,
  `platform_v` varchar(100) NOT NULL,
  `time` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login_ok` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `user` int(8) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `browser_v` varchar(100) NOT NULL,
  `platform` varchar(100) NOT NULL,
  `platform_v` varchar(100) NOT NULL,
  `time` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login_sid` (
  `sid` varchar(127) NOT NULL,
  `id` int(11) NOT NULL,
  `time` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `typeid` int(3) NOT NULL,
  `adress` varchar(150) NOT NULL,
  `params` text NOT NULL,
  `date` int(8) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `signup` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `sex` int(1) NOT NULL,
  `day` int(2) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `time` int(8) NOT NULL,
  `invite` int(1) NOT NULL,
  `about` tinytext NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `salt` int(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `time_reg` int(8) NOT NULL,
  `birthday` int(8) NOT NULL,
  `totaltime` int(10) NOT NULL,
  `rank` varchar(100) NOT NULL,
  `sex` int(1) NOT NULL,
  `invite` int(8) NOT NULL,
  `about` tinytext,
  `avatar_format` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_history` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `editor` int(8) NOT NULL,
  `user` int(8) NOT NULL,
  `type` int(3) NOT NULL,
  `time` int(8) NOT NULL,
  `data` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
