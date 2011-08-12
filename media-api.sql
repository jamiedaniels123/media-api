-- phpMyAdmin SQL Dump
-- version 3.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 12, 2011 at 02:58 PM
-- Server version: 5.1.52
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `media-api`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_log`
--

DROP TABLE IF EXISTS `api_log`;
CREATE TABLE IF NOT EXISTS `api_log` (
  `al_index` int(10) NOT NULL AUTO_INCREMENT,
  `al_message` text COLLATE utf8_unicode_ci,
  `al_debug` text COLLATE utf8_unicode_ci NOT NULL,
  `al_reply` text COLLATE utf8_unicode_ci,
  `al_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`al_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=3 ;

--
-- Dumping data for table `api_log`
--

INSERT INTO `api_log` (`al_index`, `al_message`, `al_debug`, `al_reply`, `al_timestamp`) VALUES
(1, 'mess={"command":"poll-media","number":1,"data":{"command":"poll_media"},"timestamp":1313157124}', '', '{"command":"poll-media","status":"Y","number":12,"timestamp":1313157124,"data":[{"source_path":"HelloWorld/3gp/","source_filename":"rss2.xml","destination_path":"HelloWorld/3gp/","destination_filename":"rss2.xml","cqIndex":"1","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/1_HelloWorld%2F3gp%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/3gp/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/audio/","source_filename":"rss2.xml","destination_path":"HelloWorld/audio/","destination_filename":"rss2.xml","cqIndex":"2","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/2_HelloWorld%2Faudio%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/audio/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/desktop/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop/","destination_filename":"rss2.xml","cqIndex":"3","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/3_HelloWorld%2Fdesktop%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/desktop/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/hd/","source_filename":"rss2.xml","destination_path":"HelloWorld/hd/","destination_filename":"rss2.xml","cqIndex":"4","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/4_HelloWorld%2Fhd%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/hd/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/iphone/","source_filename":"rss2.xml","destination_path":"HelloWorld/iphone/","destination_filename":"rss2.xml","cqIndex":"5","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/5_HelloWorld%2Fiphone%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/iphone/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/ipod/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod/","destination_filename":"rss2.xml","cqIndex":"6","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/6_HelloWorld%2Fipod%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/ipod/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/large/","source_filename":"rss2.xml","destination_path":"HelloWorld/large/","destination_filename":"rss2.xml","cqIndex":"7","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/7_HelloWorld%2Flarge%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/large/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/transcript/","source_filename":"rss2.xml","destination_path":"HelloWorld/transcript/","destination_filename":"rss2.xml","cqIndex":"8","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/8_HelloWorld%2Ftranscript%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/transcript/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/youtube/","source_filename":"rss2.xml","destination_path":"HelloWorld/youtube/","destination_filename":"rss2.xml","cqIndex":"9","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/9_HelloWorld%2Fyoutube%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/youtube/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/extra/","source_filename":"rss2.xml","destination_path":"HelloWorld/extra/","destination_filename":"rss2.xml","cqIndex":"10","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/10_HelloWorld%2Fextra%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/extra/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/","source_filename":"rss2.xml","destination_path":"HelloWorld/","destination_filename":"rss2.xml","cqIndex":"11","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/11_HelloWorld%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/rss2.xml","status":"Y","mqIndex":"1","step":"3"}]}', '2011-08-12 14:52:04'),
(2, 'mess={"command":"poll-media","number":1,"data":{"command":"poll_media"},"timestamp":1313157126}', '', '{"command":"poll-media","status":"Y","number":6,"timestamp":1313157126,"data":[{"source_path":"HelloWorld/high/","source_filename":"rss2.xml","destination_path":"HelloWorld/high/","destination_filename":"rss2.xml","cqIndex":"12","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/12_HelloWorld%2Fhigh%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/high/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/ipod-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod-all/","destination_filename":"rss2.xml","cqIndex":"13","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/13_HelloWorld%2Fipod-all%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/ipod-all/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/desktop-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop-all/","destination_filename":"rss2.xml","cqIndex":"14","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/14_HelloWorld%2Fdesktop-all%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/desktop-all/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/epub/","source_filename":"rss2.xml","destination_path":"HelloWorld/epub/","destination_filename":"rss2.xml","cqIndex":"15","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/15_HelloWorld%2Fepub%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/epub/rss2.xml","status":"Y","mqIndex":"1","step":"3"},{"source_path":"HelloWorld/","destination_path":"HelloWorld/","source_filename":"htaccess","destination_filename":".htaccess","cqIndex":"16","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/16_HelloWorld%2Fhtaccess to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/.htaccess","status":"Y","mqIndex":"2","step":"3"}]}', '2011-08-12 14:52:06');

-- --------------------------------------------------------

--
-- Table structure for table `api_process`
--

DROP TABLE IF EXISTS `api_process`;
CREATE TABLE IF NOT EXISTS `api_process` (
  `ap_index` int(10) NOT NULL AUTO_INCREMENT,
  `ap_process_id` int(10) DEFAULT '0',
  `ap_script` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
  `ap_timestamp` datetime NOT NULL,
  `ap_last_checked` datetime NOT NULL,
  `ap_status` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ap_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `command_routes`
--

DROP TABLE IF EXISTS `command_routes`;
CREATE TABLE IF NOT EXISTS `command_routes` (
  `cr_index` int(10) NOT NULL AUTO_INCREMENT,
  `cr_source` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cr_destination` enum('admin-app','admin-api','encoder-api','media-api') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cr_execute` enum('admin-app','admin-api','encoder-api','media-api') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cr_action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cr_function` varchar(50) CHARACTER SET ucs2 COLLATE ucs2_unicode_ci DEFAULT NULL,
  `cr_callback` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cr_route_type` enum('queue','direct') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cr_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=15 ;

--
-- Dumping data for table `command_routes`
--

INSERT INTO `command_routes` (`cr_index`, `cr_source`, `cr_destination`, `cr_execute`, `cr_action`, `cr_function`, `cr_callback`, `cr_route_type`) VALUES
(1, 'admin-api', 'media-api', 'media-api', 'media-move-file', 'doMediaMoveFile', 'media-move-file', 'queue'),
(2, 'admin-api', 'media-api', 'media-api', 'media-delete-file', 'doMediaDeleteFile', 'media-delete-file', 'queue'),
(3, 'admin-api', 'media-api', 'media-api', 'media-delete-folder', 'doMediaDeleteFolder', 'media-delete-folder', 'queue'),
(4, 'admin-api', 'media-api', 'media-api', 'media-update-metadata', 'doMediaUpdateMetadata', 'media-update-metadata', 'queue'),
(5, 'admin-api', 'media-api', 'media-api', 'media-set-permissions', 'doSetPermisssions', 'media-set-permissions', 'queue'),
(6, 'admin-api', 'media-api', 'media-api', 'media-check-file', 'doMediaCheckFile', 'media-check-file', 'direct'),
(7, 'admin-api', 'media-api', 'media-api', 'media-check-folder', 'doMediaCheckFolder', 'media-check-folder', 'direct'),
(8, 'admin-api', 'media-api', 'media-api', 'status-media', 'doStatusMedia', 'status-media', 'direct'),
(9, 'admin-api', 'media-api', 'media-api', 'poll-media', 'doPollMedia', 'poll-media', 'direct'),
(10, 'admin-api', 'media-api', 'media-api', 'media-rename-file', 'doMediaRenameFile', 'media-rename-file', 'queue'),
(11, 'admin-api', 'media-api', 'media-api', 'update-metadata', 'doUpdateMetadata', 'update-metadata', 'queue'),
(12, 'admin-api', 'media-api', 'media-api', 'media-copy-folder', 'doMediaCopyFolder', 'media-copy-folder', 'queue'),
(13, 'admin-api', 'media-api', 'media-api', 'youtube-file-upload', 'doYoutubeFileUpload', 'youtube-file-upload', 'queue'),
(14, 'admin-api', 'media-api', 'media-api', 'youtube-file-update', 'doYoutubeFileUpdate', 'youtube-file-update', 'queue');

-- --------------------------------------------------------

--
-- Table structure for table `queue_commands`
--

DROP TABLE IF EXISTS `queue_commands`;
CREATE TABLE IF NOT EXISTS `queue_commands` (
  `cq_index` int(10) NOT NULL AUTO_INCREMENT,
  `cq_cq_index` int(10) NOT NULL DEFAULT '0',
  `cq_mq_index` int(10) NOT NULL,
  `cq_step` int(10) NOT NULL,
  `cq_command` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `cq_data` text COLLATE utf8_unicode_ci,
  `cq_result` text COLLATE utf8_unicode_ci,
  `cq_time` datetime DEFAULT NULL,
  `cq_update` datetime DEFAULT NULL,
  `cq_status` enum('Y','N','F','R') COLLATE utf8_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`cq_index`),
  KEY `ma_command` (`cq_cq_index`,`cq_command`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=17 ;

--
-- Dumping data for table `queue_commands`
--

INSERT INTO `queue_commands` (`cq_index`, `cq_cq_index`, `cq_mq_index`, `cq_step`, `cq_command`, `cq_data`, `cq_result`, `cq_time`, `cq_update`, `cq_status`) VALUES
(1, 1, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/3gp/","source_filename":"rss2.xml","destination_path":"HelloWorld/3gp/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/3gp/","source_filename":"rss2.xml","destination_path":"HelloWorld/3gp/","destination_filename":"rss2.xml","cqIndex":"1","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/1_HelloWorld%2F3gp%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/3gp/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:03', 'R'),
(2, 2, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/audio/","source_filename":"rss2.xml","destination_path":"HelloWorld/audio/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/audio/","source_filename":"rss2.xml","destination_path":"HelloWorld/audio/","destination_filename":"rss2.xml","cqIndex":"2","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/2_HelloWorld%2Faudio%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/audio/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:03', 'R'),
(3, 3, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/desktop/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/desktop/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop/","destination_filename":"rss2.xml","cqIndex":"3","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/3_HelloWorld%2Fdesktop%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/desktop/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(4, 4, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/hd/","source_filename":"rss2.xml","destination_path":"HelloWorld/hd/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/hd/","source_filename":"rss2.xml","destination_path":"HelloWorld/hd/","destination_filename":"rss2.xml","cqIndex":"4","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/4_HelloWorld%2Fhd%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/hd/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(5, 5, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/iphone/","source_filename":"rss2.xml","destination_path":"HelloWorld/iphone/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/iphone/","source_filename":"rss2.xml","destination_path":"HelloWorld/iphone/","destination_filename":"rss2.xml","cqIndex":"5","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/5_HelloWorld%2Fiphone%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/iphone/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(6, 6, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/ipod/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/ipod/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod/","destination_filename":"rss2.xml","cqIndex":"6","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/6_HelloWorld%2Fipod%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/ipod/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(7, 7, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/large/","source_filename":"rss2.xml","destination_path":"HelloWorld/large/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/large/","source_filename":"rss2.xml","destination_path":"HelloWorld/large/","destination_filename":"rss2.xml","cqIndex":"7","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/7_HelloWorld%2Flarge%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/large/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(8, 8, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/transcript/","source_filename":"rss2.xml","destination_path":"HelloWorld/transcript/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/transcript/","source_filename":"rss2.xml","destination_path":"HelloWorld/transcript/","destination_filename":"rss2.xml","cqIndex":"8","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/8_HelloWorld%2Ftranscript%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/transcript/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(9, 9, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/youtube/","source_filename":"rss2.xml","destination_path":"HelloWorld/youtube/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/youtube/","source_filename":"rss2.xml","destination_path":"HelloWorld/youtube/","destination_filename":"rss2.xml","cqIndex":"9","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/9_HelloWorld%2Fyoutube%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/youtube/rss2.xml"}', '2011-08-12 14:52:00', '2011-08-12 14:52:04', 'R'),
(10, 10, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/extra/","source_filename":"rss2.xml","destination_path":"HelloWorld/extra/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/extra/","source_filename":"rss2.xml","destination_path":"HelloWorld/extra/","destination_filename":"rss2.xml","cqIndex":"10","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/10_HelloWorld%2Fextra%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/extra/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:04', 'R'),
(11, 11, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/","source_filename":"rss2.xml","destination_path":"HelloWorld/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/","source_filename":"rss2.xml","destination_path":"HelloWorld/","destination_filename":"rss2.xml","cqIndex":"11","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/11_HelloWorld%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:04', 'R'),
(12, 12, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/high/","source_filename":"rss2.xml","destination_path":"HelloWorld/high/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/high/","source_filename":"rss2.xml","destination_path":"HelloWorld/high/","destination_filename":"rss2.xml","cqIndex":"12","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/12_HelloWorld%2Fhigh%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/high/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:04', 'R'),
(13, 13, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/ipod-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod-all/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/ipod-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/ipod-all/","destination_filename":"rss2.xml","cqIndex":"13","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/13_HelloWorld%2Fipod-all%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/ipod-all/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:04', 'R'),
(14, 14, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/desktop-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop-all/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/desktop-all/","source_filename":"rss2.xml","destination_path":"HelloWorld/desktop-all/","destination_filename":"rss2.xml","cqIndex":"14","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/14_HelloWorld%2Fdesktop-all%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/desktop-all/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:05', 'R'),
(15, 15, 1, 3, 'media-move-file', '{"source_path":"HelloWorld/epub/","source_filename":"rss2.xml","destination_path":"HelloWorld/epub/","destination_filename":"rss2.xml"}', '{"source_path":"HelloWorld/epub/","source_filename":"rss2.xml","destination_path":"HelloWorld/epub/","destination_filename":"rss2.xml","cqIndex":"15","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/15_HelloWorld%2Fepub%2Frss2.xml to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/epub/rss2.xml"}', '2011-08-12 14:52:01', '2011-08-12 14:52:05', 'R'),
(16, 16, 2, 3, 'media-move-file', '{"source_path":"HelloWorld/","destination_path":"HelloWorld/","source_filename":"htaccess","destination_filename":".htaccess"}', '{"source_path":"HelloWorld/","destination_path":"HelloWorld/","source_filename":"htaccess","destination_filename":".htaccess","cqIndex":"16","number":1,"result":"Y","debug":"/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/16_HelloWorld%2Fhtaccess to /data/web/media-podcast-dev.open.ac.uk/www/feeds/HelloWorld/.htaccess"}', '2011-08-12 14:52:01', '2011-08-12 14:52:05', 'R');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
