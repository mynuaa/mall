SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE DATABASE IF NOT EXISTS `app_zfjmall` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `app_zfjmall`;

CREATE TABLE IF NOT EXISTS `think_ad` (
  `ad_id` int(6) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `grade` int(1) NOT NULL COMMENT 'æ•°å­—å¤§çš„æŽ’ä½é å‰',
  `time` date NOT NULL,
  `href` varchar(60) NOT NULL,
  PRIMARY KEY (`ad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_admin` (
  `admin_id` int(6) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(14) NOT NULL,
  `admin_uid` int(11) NOT NULL,
  `admin_grade` int(1) NOT NULL COMMENT '2,较高权限，允许所有功能 1，只允许使用商品检索类的功能',
  `admin_time` date NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='存储管理员信息' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_banned` (
  `banned_id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `username` varchar(14) NOT NULL,
  `start_time` date NOT NULL,
  `last_time` int(1) NOT NULL COMMENT '如果为0，则永久封号。3,7表示三天，七天',
  `is_over` int(1) NOT NULL COMMENT '1尚未结束 0 已经结束',
  `admin_username` varchar(14) NOT NULL COMMENT '执行禁言操作的管理员ID',
  PRIMARY KEY (`banned_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_bug` (
  `bug_id` int(6) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `username` varchar(14) NOT NULL,
  `bug_content` text NOT NULL,
  `time` datetime NOT NULL,
  `email` varchar(60) NOT NULL,
  PRIMARY KEY (`bug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_collection` (
  `uid` int(12) NOT NULL,
  `username` varchar(14) NOT NULL,
  `goods_id` int(10) NOT NULL,
  `goods_name` varchar(16) NOT NULL,
  `collect_time` date NOT NULL,
  `collect_id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`collect_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='c' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_config` (
  `uid` int(12) NOT NULL,
  `config_id` int(10) NOT NULL AUTO_INCREMENT,
  `contact` varchar(60) NOT NULL,
  `location` int(1) NOT NULL,
  `attention` varchar(26) NOT NULL,
  `data` datetime NOT NULL COMMENT 'åˆ›å»ºæˆ–ä¿®æ”¹æ—¥æœŸ',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_message` (
  `message_id` int(10) NOT NULL AUTO_INCREMENT,
  `data` datetime NOT NULL,
  `from_uid` int(12) NOT NULL,
  `from_username` varchar(16) NOT NULL,
  `to_uid` int(12) NOT NULL,
  `to_username` varchar(16) NOT NULL,
  `content` text NOT NULL,
  `goods_id` int(10) NOT NULL COMMENT '相关联的商品id',
  `goods_name` varchar(31) NOT NULL,
  `status` int(1) NOT NULL COMMENT '0已读，1未读',
  `message_type` int(1) NOT NULL COMMENT '0主题有回复 ，1收藏的物品已售出，2管理员为商品完成交易，3管理员删除商品,4收藏的物品已被删除',
  PRIMARY KEY (`message_id`),
  KEY `to_uid` (`to_uid`),
  KEY `goods_id` (`goods_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_newgoods` (
  `uid` int(12) NOT NULL COMMENT '用户uid',
  `username` varchar(16) NOT NULL,
  `goods_id` int(10) NOT NULL AUTO_INCREMENT,
  `data` datetime NOT NULL COMMENT '发布信息的时间',
  `need_type` int(1) NOT NULL COMMENT '0求购1出售',
  `price` float NOT NULL COMMENT '价格',
  `description` text NOT NULL COMMENT '商品描述',
  `location` int(1) NOT NULL COMMENT '0本部1江宁 2位置不限',
  `classify` int(1) NOT NULL COMMENT '0-9分别代表手机，电子，书籍，车辆，服饰，化妆，日用，乐器，房屋，其他',
  `photo` text NOT NULL COMMENT '图片地址',
  `goods_name` varchar(31) NOT NULL,
  `contact` varchar(60) NOT NULL,
  `Concern_shopid` int(6) NOT NULL COMMENT '等于0的时候不属于店铺',
  PRIMARY KEY (`goods_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='待出售的物品' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_notice` (
  `notice_id` int(6) NOT NULL AUTO_INCREMENT,
  `notice_title` varchar(30) NOT NULL,
  `notice_content` text NOT NULL,
  `notice_time` date NOT NULL,
  `notice_grade` int(3) DEFAULT NULL,
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_shop` (
  `shop_id` int(6) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(20) NOT NULL,
  `shop_type` varchar(10) NOT NULL,
  `create_time` date NOT NULL,
  `admin_id` varchar(11) NOT NULL,
  `admin_name` varchar(14) NOT NULL,
  `contact` varchar(30) NOT NULL COMMENT 'è”ç³»æ–¹å¼',
  `description` varchar(60) NOT NULL,
  `cover` varchar(100) NOT NULL COMMENT 'å°é¢',
  `is_open` int(1) NOT NULL COMMENT '1å·²å¼€ï¼Œ0å°šæœªé€šè¿‡éªŒè¯',
  PRIMARY KEY (`shop_id`),
  UNIQUE KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `think_soldout` (
  `soldout_id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(12) NOT NULL,
  `username` varchar(16) NOT NULL,
  `goods_id` int(10) NOT NULL,
  `data` datetime NOT NULL,
  `close_time` date NOT NULL,
  `need_type` int(1) NOT NULL,
  `price` int(4) NOT NULL,
  `description` text NOT NULL,
  `location` int(1) NOT NULL,
  `classify` int(1) NOT NULL,
  `photo` text NOT NULL,
  `goods_name` varchar(31) NOT NULL,
  `contact` varchar(60) NOT NULL,
  PRIMARY KEY (`soldout_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
