

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`test` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `test`;

/*Table structure for table `contacts` */

DROP TABLE IF EXISTS `contacts`;

CREATE TABLE `contacts` (
  `emailAddress` varchar(150) NOT NULL,
  `street` varchar(150) DEFAULT NULL,
  `phone` char(14) NOT NULL,
  PRIMARY KEY (`emailAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `customers` */

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL,
  `first` varchar(255) NOT NULL,
  `last` varchar(255) NOT NULL,
  `phone` varchar(14) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_customer` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `peeps` */

DROP TABLE IF EXISTS `peeps`;

CREATE TABLE `peeps` (
  `code` char(8) NOT NULL,
  `fname` varchar(150) NOT NULL,
  `lname` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `fk_contact_peep` (`email`),
  CONSTRAINT `fk_contact_peep` FOREIGN KEY (`email`) REFERENCES `contacts` (`emailAddress`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `tests` */

DROP TABLE IF EXISTS `tests`;

CREATE TABLE `tests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testName` varchar(255) NOT NULL,
  `started` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed` timestamp NULL DEFAULT NULL,
  `flagged` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `types` */

DROP TABLE IF EXISTS `types`;

CREATE TABLE `types` (
  `primary` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `utiny` tinyint(3) unsigned NOT NULL,
  `tiny` tinyint(4) NOT NULL,
  `usmall` smallint(5) unsigned NOT NULL,
  `small` smallint(6) NOT NULL,
  `umedium` mediumint(8) unsigned NOT NULL,
  `medium` mediumint(9) NOT NULL,
  `uint` int(10) unsigned NOT NULL,
  `int` int(11) NOT NULL DEFAULT '100',
  `null` int(11) DEFAULT NULL,
  `ubig` bigint(20) unsigned NOT NULL,
  `big` bigint(20) NOT NULL,
  `integer` int(11) DEFAULT NULL,
  `bool` tinyint(1) DEFAULT '0',
  `smallint` smallint(6) DEFAULT NULL,
  `mediumint` mediumint(9) DEFAULT NULL,
  `bigint` bigint(20) DEFAULT NULL,
  `decimal` decimal(15,5) DEFAULT NULL,
  `dec` decimal(10,0) DEFAULT NULL,
  `numeric` decimal(10,0) DEFAULT NULL,
  `fixed` decimal(12,2) DEFAULT '10.50',
  `float` float DEFAULT NULL,
  `double` double DEFAULT NULL,
  `double_precision` double DEFAULT NULL,
  `real` double DEFAULT NULL,
  `date` date DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `time` time DEFAULT NULL,
  `year` year(4) DEFAULT '2004',
  `char` char(100) DEFAULT 'CHAR',
  `varchar` varchar(250) DEFAULT NULL,
  `binary` binary(75) DEFAULT NULL,
  `varbinary` varbinary(255) DEFAULT 'BINARY',
  `tinyblob` tinyblob,
  `blob` blob,
  `mediumblob` mediumblob,
  `longblob` longblob,
  `tinytext` tinytext,
  `text` text,
  `mediumtext` mediumtext,
  `longtext` longtext,
  `enum` enum('one','two','three') DEFAULT 'one',
  `set` set('one','two','three') DEFAULT NULL,
  PRIMARY KEY (`primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
