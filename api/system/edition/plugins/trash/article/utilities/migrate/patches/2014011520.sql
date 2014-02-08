SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `user` int(8) NOT NULL,
  `title` varchar(256) NOT NULL,
  `article` mediumtext NOT NULL,
  `time` int(8) NOT NULL,
  `times` int(8) NOT NULL,
  `status` int(1) NOT NULL,
  `tags` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `articles_history` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `editor` int(8) NOT NULL,
  `time` int(8) NOT NULL,
  `type` int(3) NOT NULL,
  `data` mediumtext,
  `article` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `articles_new` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL,
  `article` mediumtext NOT NULL,
  `time` int(8) NOT NULL,
  `tags` text,
  `user` int(8) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
