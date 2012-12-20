-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: crap6
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5.5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blah_comments`
--

DROP TABLE IF EXISTS `blah_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `when_posted` datetime DEFAULT NULL,
  `author_name` varchar(50) NOT NULL DEFAULT '',
  `author_email` varchar(50) NOT NULL DEFAULT '',
  `author_url` varchar(50) NOT NULL DEFAULT '',
  `author_host` varchar(50) NOT NULL DEFAULT '',
  `author_ip` varchar(15) NOT NULL DEFAULT '',
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `body_r` text NOT NULL,
  `signature` text NOT NULL,
  `signature_r` text NOT NULL,
  `is_deleted` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `idx_thread_id` (`thread_id`),
  KEY `idx_when_posted` (`when_posted`),
  KEY `idx_author_id` (`author_id`),
  KEY `idx_topic_id` (`topic_id`),
  KEY `idx_num` (`num`),
  KEY `idx_author_name` (`author_name`),
  FULLTEXT KEY `idx_fulltext` (`title`,`body`)
) ENGINE=MyISAM AUTO_INCREMENT=709384 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_lastread`
--

DROP TABLE IF EXISTS `blah_lastread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_lastread` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lastread` datetime DEFAULT NULL,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_thread_id` (`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_online`
--

DROP TABLE IF EXISTS `blah_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_online` (
  `session_id` char(32) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `when_visited` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `url` char(80) NOT NULL DEFAULT '',
  `title` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_publishvotes`
--

DROP TABLE IF EXISTS `blah_publishvotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_publishvotes` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `publish` enum('Y','N','D') NOT NULL DEFAULT 'D',
  KEY `idx_user_id` (`user_id`),
  KEY `idx_topic_id_publish` (`topic_id`,`publish`),
  KEY `idx_publish` (`publish`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_threads`
--

DROP TABLE IF EXISTS `blah_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` char(100) NOT NULL DEFAULT '',
  `when_opened` datetime DEFAULT NULL,
  `author_name` char(50) NOT NULL DEFAULT '',
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `when_last_comment` datetime DEFAULT NULL,
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=971 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_topics`
--

DROP TABLE IF EXISTS `blah_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `author_email` varchar(50) NOT NULL DEFAULT '',
  `author_name` varchar(50) NOT NULL DEFAULT '',
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `intro` text NOT NULL,
  `body` text NOT NULL,
  `when_created` datetime DEFAULT NULL,
  `when_submitted` datetime DEFAULT NULL,
  `when_published` datetime DEFAULT NULL,
  `when_modified` datetime DEFAULT NULL,
  `location` enum('frontpage','submission','pipeline') NOT NULL DEFAULT 'pipeline',
  `locked` enum('Y','N') NOT NULL DEFAULT 'N',
  `comments_mode` enum('flat','semithreaded') NOT NULL DEFAULT 'flat',
  `allow_guest_comments` enum('Y','N') NOT NULL DEFAULT 'Y',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_count` int(10) unsigned NOT NULL DEFAULT '0',
  `when_last_comment` datetime DEFAULT NULL,
  `first_thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_html` enum('Y','N') NOT NULL DEFAULT 'N',
  `show_both` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `idx_location` (`location`),
  FULLTEXT KEY `idx_fulltext` (`title`,`intro`,`body`)
) ENGINE=MyISAM AUTO_INCREMENT=6391 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blah_users`
--

DROP TABLE IF EXISTS `blah_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blah_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(50) NOT NULL DEFAULT '',
  `is_superuser` enum('Y','N') NOT NULL DEFAULT 'N',
  `is_trusted` enum('Y','N') NOT NULL DEFAULT 'N',
  `when_created` datetime DEFAULT NULL,
  `activation_key` varchar(10) NOT NULL DEFAULT '',
  `is_verified` enum('Y','N') NOT NULL DEFAULT 'N',
  `unique_name` enum('Y','N') NOT NULL DEFAULT 'N',
  `title` varchar(30) NOT NULL DEFAULT '',
  `lock_title` enum('Y','N') NOT NULL DEFAULT 'N',
  `extradata` text NOT NULL,
  `show_email` enum('Y','N') NOT NULL DEFAULT 'Y',
  `show_online` enum('Y','N') NOT NULL DEFAULT 'Y',
  `extra_company` varchar(50) NOT NULL DEFAULT '',
  `extra_realname` varchar(50) NOT NULL DEFAULT '',
  `extra_company_url` varchar(50) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `when_last_login` datetime DEFAULT NULL,
  `view_signatures` enum('Y','N') NOT NULL DEFAULT 'Y',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  `global_lastread` datetime DEFAULT NULL,
  `pagelength` int(10) unsigned NOT NULL DEFAULT '50',
  `avatar_filename` varchar(80) NOT NULL DEFAULT '',
  `avatar_is_custom` enum('Y','N') NOT NULL DEFAULT 'N',
  `extra_pagewidth` varchar(10) NOT NULL DEFAULT '750',
  PRIMARY KEY (`id`),
  KEY `idx_when_created` (`when_created`),
  KEY `idx_name` (`name`),
  KEY `idx_email` (`email`),
  KEY `idx_is_verified` (`is_verified`)
) ENGINE=MyISAM AUTO_INCREMENT=6550 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-12-21  1:16:28
