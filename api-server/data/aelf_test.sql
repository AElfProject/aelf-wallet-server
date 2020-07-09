/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50641
 Source Host           : localhost:3306
 Source Schema         : aelf_open

 Target Server Type    : MySQL
 Target Server Version : 50641
 File Encoding         : 65001

 Date: 09/06/2020 16:45:23
*/

CREATE DATABASE `aelf_bk2020` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
use `aelf_bk2020`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cc_address_book
-- ----------------------------
DROP TABLE IF EXISTS `cc_address_book`;
CREATE TABLE `cc_address_book` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `fc` char(1) NOT NULL DEFAULT '' COMMENT '用户名首字母',
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `note` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '=1启用=2删除',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `fc` (`fc`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='钱包用户的地址簿';

-- ----------------------------
-- Table structure for cc_bind
-- ----------------------------
DROP TABLE IF EXISTS `cc_bind`;
CREATE TABLE `cc_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '用户地址',
  `contract_address` varchar(255) NOT NULL DEFAULT '' COMMENT '合约地址',
  `symbol` varchar(50) NOT NULL DEFAULT '' COMMENT '合约币名',
  `chain_id` varchar(20) NOT NULL DEFAULT '' COMMENT '当前链',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '=1绑定=2解绑',
  `time` int(10) unsigned NOT NULL COMMENT '绑定时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_symbol` (`address`,`contract_address`,`symbol`,`chain_id`),
  KEY `address` (`address`),
  KEY `chain_id` (`chain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户绑定表';

-- ----------------------------
-- Table structure for cc_chain
-- ----------------------------
DROP TABLE IF EXISTS `cc_chain`;
CREATE TABLE `cc_chain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '链名称',
  `chainid` varchar(256) NOT NULL DEFAULT '' COMMENT '链id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='链信息';

-- ----------------------------
-- Records of cc_chain
-- ----------------------------
BEGIN;
INSERT INTO `cc_chain` VALUES (1, 'A', '1');
INSERT INTO `cc_chain` VALUES (2, 'B', '2');
INSERT INTO `cc_chain` VALUES (3, 'C', '3');
COMMIT;

-- ----------------------------
-- Table structure for cc_coin
-- ----------------------------
DROP TABLE IF EXISTS `cc_coin`;
CREATE TABLE `cc_coin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sortnum` int(11) DEFAULT NULL COMMENT '排序号越大越靠前',
  `name` varchar(20) NOT NULL DEFAULT '',
  `fullName` varchar(100) DEFAULT NULL,
  `currentBlockNumber` int(11) NOT NULL DEFAULT '0',
  `logo` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL COMMENT '0隐藏,1显示',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='主链币种';

-- ----------------------------
-- Records of cc_coin
-- ----------------------------
BEGIN;
INSERT INTO `cc_coin` VALUES (11, 1, 'ELF', 'AELF', 0, 'elf_wallet/elf/elf.png', 1);
INSERT INTO `cc_coin` VALUES (12, 1, 'EPC', 'tDVV', 0, 'elf_wallet/elf/tDVV.png', 1);
INSERT INTO `cc_coin` VALUES (13, 1, 'EDA', 'tDVW', 0, 'elf_wallet/elf/tDVW.png', 1);
INSERT INTO `cc_coin` VALUES (15, 1, 'EDB', 'tDVX', 0, 'elf_wallet/elf/tDVX.png', 1);
INSERT INTO `cc_coin` VALUES (16, 1, 'EDC', 'tDVY', 0, 'elf_wallet/elf/tDVY.png', 1);
INSERT INTO `cc_coin` VALUES (17, 1, 'EDD', 'tDVZ', 0, 'elf_wallet/elf/tDVZ.png', 1);
COMMIT;

-- ----------------------------
-- Table structure for cc_coin_fee
-- ----------------------------
DROP TABLE IF EXISTS `cc_coin_fee`;
CREATE TABLE `cc_coin_fee` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fee` decimal(20,8) NOT NULL DEFAULT '0.00000000' COMMENT '费用',
  `coin` varchar(20) NOT NULL DEFAULT '' COMMENT '币种',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COMMENT='通用矿工费用';

-- ----------------------------
-- Records of cc_coin_fee
-- ----------------------------
BEGIN;
INSERT INTO `cc_coin_fee` VALUES (15, 0.00050000, 'elf');
COMMIT;

-- ----------------------------
-- Table structure for cc_com_addr
-- ----------------------------
DROP TABLE IF EXISTS `cc_com_addr`;
CREATE TABLE `cc_com_addr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coin` varchar(20) NOT NULL,
  `address` varchar(100) NOT NULL DEFAULT '',
  `udid` varchar(100) DEFAULT NULL,
  `firsttime` int(10) NOT NULL,
  `lasttime` int(10) NOT NULL,
  `lang` varchar(20) DEFAULT NULL,
  `android_notice_token` varchar(100) DEFAULT NULL,
  `ios_notice_token` varchar(100) DEFAULT NULL,
  `device_info` varchar(100) DEFAULT NULL,
  `flag` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `firsttime` (`firsttime`) USING BTREE,
  KEY `lasttime` (`lasttime`) USING BTREE,
  KEY `coin` (`coin`) USING BTREE,
  KEY `address` (`address`) USING BTREE,
  KEY `udid` (`udid`) USING BTREE,
  KEY `flag` (`flag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='app传过来的币种地址（除eth，btc';

-- ----------------------------
-- Table structure for cc_comment
-- ----------------------------
DROP TABLE IF EXISTS `cc_comment`;
CREATE TABLE `cc_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` text,
  `create_time` int(10) NOT NULL DEFAULT '0',
  `star_num` tinyint(4) NOT NULL DEFAULT '4',
  `sort_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for cc_config_data
-- ----------------------------
DROP TABLE IF EXISTS `cc_config_data`;
CREATE TABLE `cc_config_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `val` text NOT NULL,
  `tip` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='所有设置数据';

-- ----------------------------
-- Records of cc_config_data
-- ----------------------------
BEGIN;
INSERT INTO `cc_config_data` VALUES (23, 'market_list', '', '行情币种列表url');
INSERT INTO `cc_config_data` VALUES (24, 'market_trade_kline', '', '行情K线url');
INSERT INTO `cc_config_data` VALUES (25, 'market_coin_detail', '', '币种详细行情url');
INSERT INTO `cc_config_data` VALUES (26, 'currencies', '{\"en\":[{\"id\":\"USD\",\"name\":\"USD\"},{\"id\":\"CNY\",\"name\":\"CNY\"}],\"zh-cn\":[{\"id\":\"USD\",\"name\":\"美元\"},{\"id\":\"CNY\",\"name\":\"人民币\"}],\"ko\":[{\"id\":\"USD\",\"name\":\"美元\"},{\"id\":\"CNY\",\"name\":\"人民币\"}]}', '');
INSERT INTO `cc_config_data` VALUES (27, 'oss_url', '', 'oss_url路径前缀');
INSERT INTO `cc_config_data` VALUES (28, 'increase_list', '', '行情路径');
INSERT INTO `cc_config_data` VALUES (30, 'market_price_list', '', '行情币价排行榜');
INSERT INTO `cc_config_data` VALUES (35, 'api_config', '{\n	\"web_api\": {\n		\"AELF\": \"http://54.199.254.157:8000\",\n		\"tDVV\": \"http://3.112.250.87:8000\"\n	},\n	\"balance_url\": \"http://127.0.0.1:8000/elf\",\n	\"base58_url\": \"http://127.0.0.1:8000/elf_trans\",\n	\"address_url\": \"http://127.0.0.1:8000/elf_address\",\n	\"tokens_url\": \"http://127.0.0.1:8000/elf_tokens\",\n	\"history_api\": {\n		\"AELF\": \"http://3.112.73.152:7101\",\n		\"tDVV\": \"http://18.179.200.57:7101\"\n	},\n	\"scaner_node\": {\n		\"AELF\": \"http://54.199.254.157:8000\",\n		\"tDVV\": \"http://3.112.250.87:8000\"\n	},\n	\"chain_color\": {\n		\"AELF\": \"#5C28A9\",\n		\"tDVV\": \"#4B60DD\"\n	},\n	\"base58_nodes\": {\n		\"AELF\": \"9992731\",\n		\"tDVV\": \"1866392\"\n	}\n}', 'aelf接口文件配置');
INSERT INTO `cc_config_data` VALUES (37, 'market_custom_list', '', '指定的自选币种');
INSERT INTO `cc_config_data` VALUES (38, 'chains', '[{\n		\"type\": \"main\",\n		\"name\": \"AELF\",\n		\"contract_address\": \"25CecrU94dmMdbhC3LWMKxtoaL4Wv8PChGvVJM6PxkHAyvXEhB\",\n		\"node\": \"http://54.199.254.157:8000/\",\n		\"symbol\": \"ELF\",\n		\"logo\": \"elf_wallet/elf/elf.png\",\n		\"explorer\": \"https://explorer-test.aelf.io\",\n		\"crossChainContractAddress\": \"x7G7VYqqeVAH8aeAsb7gYuTQ12YS1zKuxur9YES3cUj72QMxJ\",\n		\"transferCoins\": \"ELF\"\n	},\n	{\n		\"type\": \"side\",\n		\"name\": \"tDVV\",\n		\"contract_address\": \"EReNnYPBeZ3AfAjPXXdpNK7AV5YCjRPvM7d5M3SLettMZpxre\",\n		\"node\": \"http://3.112.250.87:8000/\",\n		\"symbol\": \"ELF\",\n		\"logo\": \"elf_wallet/elf/tDVV.png\",\n		\"explorer\": \"https://explorer-test-side01.aelf.io\",\n		\"crossChainContractAddress\": \"RSr6bPc7Hv6dMJiWdPgBBFMacUJcrgQoeHkVBMjqJ5HURtKK3\",\n		\"transferCoins\": \"ELF\"\n	}\n]', '跨链信息');
INSERT INTO `cc_config_data` VALUES (42, 'url', 'http://127.0.0.1:8081', '当前api服务域名url');
INSERT INTO `cc_config_data` VALUES (34, 'customer_service_list', 'help@aelf.io', '多个客服用逗号隔开');
INSERT INTO `cc_config_data` VALUES (40, 'unique_code', '', '允许访问识别code码');
INSERT INTO `cc_config_data` VALUES (41, 'access_ip', '127.0.0.1', '允许test、demo参数访问的ip');
COMMIT;

-- ----------------------------
-- Table structure for cc_cross_chain_transaction
-- ----------------------------
DROP TABLE IF EXISTS `cc_cross_chain_transaction`;
CREATE TABLE `cc_cross_chain_transaction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `txid` varchar(256) NOT NULL DEFAULT '' COMMENT '??id',
  `from_chain` varchar(20) NOT NULL DEFAULT '' COMMENT '转出链',
  `to_chain` varchar(20) NOT NULL DEFAULT '' COMMENT '接收链',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未完成1已完成3超时',
  `from_address` varchar(255) NOT NULL DEFAULT '' COMMENT '转出地址',
  `to_address` varchar(255) NOT NULL DEFAULT '' COMMENT '接收地址',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间戳',
  `symbol` varchar(20) NOT NULL DEFAULT '' COMMENT '转出token',
  `amount` decimal(30,8) NOT NULL DEFAULT '0.00000000' COMMENT '金额',
  `action_time` int(10) NOT NULL COMMENT '操作时间',
  `rcv_txid` varchar(256) DEFAULT NULL COMMENT '跨链接收txid',
  `memo` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `txid` (`txid`(255)),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cc_dapps_banner
-- ----------------------------
DROP TABLE IF EXISTS `cc_dapps_banner`;
CREATE TABLE `cc_dapps_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) unsigned NOT NULL COMMENT '对应游戏id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `img` text NOT NULL COMMENT '图片地址',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '=1启用=2弃用',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `flag` tinyint(1) unsigned DEFAULT '1' COMMENT '=1普通链接=2dapp链接',
  `addtime` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='dapps首页幻灯片';

-- ----------------------------
-- Table structure for cc_dapps_games
-- ----------------------------
DROP TABLE IF EXISTS `cc_dapps_games`;
CREATE TABLE `cc_dapps_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ico` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `coin` varchar(20) NOT NULL DEFAULT '' COMMENT '币种',
  `tag` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `name` text NOT NULL COMMENT '游戏名称',
  `desc` text COMMENT '游戏描述',
  `cat` tinyint(1) NOT NULL DEFAULT '1' COMMENT '分类',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '=1开启=2关闭',
  `isindex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '推荐首页=1是=2否',
  `url` varchar(255) DEFAULT NULL COMMENT '链接',
  `addtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `isindex` (`isindex`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='dapp 游戏列表';

-- ----------------------------
-- Table structure for cc_dapps_search
-- ----------------------------
DROP TABLE IF EXISTS `cc_dapps_search`;
CREATE TABLE `cc_dapps_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` varchar(50) NOT NULL DEFAULT '' COMMENT '游戏id',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '游戏名称',
  `rank` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rank` (`rank`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='dapps搜索热词管理';

-- ----------------------------
-- Table structure for cc_feedback
-- ----------------------------
DROP TABLE IF EXISTS `cc_feedback`;
CREATE TABLE `cc_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `desc` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户标识',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `udid` varchar(100) NOT NULL DEFAULT '',
  `device` varchar(20) NOT NULL DEFAULT '',
  `version` varchar(20) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cc_info
-- ----------------------------
DROP TABLE IF EXISTS `cc_info`;
CREATE TABLE `cc_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL DEFAULT '0',
  `classId` varchar(200) NOT NULL,
  `ordinal` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL,
  `subTitle` varchar(200) NOT NULL,
  `titleStyle` varchar(200) DEFAULT NULL,
  `alias` varchar(1000) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `pageTitle` varchar(200) DEFAULT NULL,
  `keywords` varchar(150) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `publishedDate` varchar(10) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `author` varchar(50) DEFAULT NULL,
  `intro` text,
  `content` longtext,
  `imageUrl` varchar(100) DEFAULT NULL,
  `bigImageUrl` varchar(100) DEFAULT NULL,
  `images` text,
  `files` text,
  `filename` varchar(200) DEFAULT NULL,
  `isApproved` smallint(6) NOT NULL DEFAULT '0',
  `isTop` smallint(6) NOT NULL DEFAULT '0',
  `isHot` smallint(6) NOT NULL DEFAULT '0',
  `isRecommended` smallint(6) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `downloadCount` int(11) NOT NULL DEFAULT '0',
  `createdUserId` int(11) NOT NULL,
  `createdDate` int(10) NOT NULL,
  `lastModifiedUserId` int(11) DEFAULT NULL,
  `lastModifiedDate` int(10) DEFAULT NULL,
  `extend` text,
  `sourceHtml` varchar(100) DEFAULT NULL,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='信息相关';

-- ----------------------------
-- Records of cc_info
-- ----------------------------
BEGIN;
INSERT INTO `cc_info` VALUES (1, 0, '101', 0, '111', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '<p>22222334</p>', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'zh-cn');
INSERT INTO `cc_info` VALUES (2, 0, '101', 0, '22', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '得得得', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'zh-cn');
INSERT INTO `cc_info` VALUES (3, 0, '103', 0, '11111111', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '11', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'en');
INSERT INTO `cc_info` VALUES (4, 0, '104', 0, '111111', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '22222', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'zh-cn');
INSERT INTO `cc_info` VALUES (5, 0, '104', 0, 'eeeeeeee', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'eeeeeeee', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'ko');
INSERT INTO `cc_info` VALUES (6, 0, '104', 0, 'eeeeeeerrrrrrrrr', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'rrrrrrrrrrrrrrrr', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'en');
INSERT INTO `cc_info` VALUES (7, 0, '103', 0, '用户协议', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '用户协议', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'zh-cn');
INSERT INTO `cc_info` VALUES (8, 0, '103', 0, '111111111', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '1111111112', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'ko');
INSERT INTO `cc_info` VALUES (9, 0, '102', 0, '1111', '', NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, '<p>1111111111111</p>', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, 'zh-cn');
COMMIT;

-- ----------------------------
-- Table structure for cc_infoclass
-- ----------------------------
DROP TABLE IF EXISTS `cc_infoclass`;
CREATE TABLE `cc_infoclass` (
  `id` varchar(200) NOT NULL,
  `companyId` int(11) NOT NULL DEFAULT '0',
  `ordinal` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `nameEn` varchar(200) NOT NULL,
  `alias` varchar(1000) DEFAULT NULL,
  `domain` int(1) DEFAULT '0',
  `classStyle` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `pageTitle` varchar(100) DEFAULT NULL,
  `keywords` varchar(150) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `intro` text,
  `content` longtext,
  `imageUrl` varchar(100) DEFAULT NULL,
  `bigImageUrl` varchar(100) DEFAULT NULL,
  `files` text,
  `maxLayer` tinyint(4) NOT NULL DEFAULT '2',
  `perPageCount` int(4) NOT NULL DEFAULT '20',
  `defaultDisplayMode` tinyint(4) NOT NULL DEFAULT '2',
  `displayModes` varchar(100) NOT NULL,
  `extend` text,
  `info` text,
  `other` text,
  `template` varchar(50) DEFAULT NULL,
  `lang` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='信息分类';

-- ----------------------------
-- Records of cc_infoclass
-- ----------------------------
BEGIN;
INSERT INTO `cc_infoclass` VALUES ('101', 0, 10, 'FLEET', '', 'fleet', 0, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 2, 20, 2, 'N;', 'a:5:{s:8:\"hasAlias\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:11:\"hasTemplate\";s:1:\"1\";}', 'a:9:{s:6:\"hasTop\";s:1:\"1\";s:14:\"hasRecommended\";s:1:\"1\";s:6:\"hasHot\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:13:\"hasTitleStyle\";s:1:\"1\";s:11:\"hasImageUrl\";s:1:\"1\";s:10:\"hasContent\";s:1:\"1\";}', 'a:13:{s:10:\"cpic1width\";s:0:\"\";s:11:\"cpic1height\";s:0:\"\";s:10:\"cpic2width\";s:0:\"\";s:11:\"cpic2height\";s:0:\"\";s:9:\"pic1width\";s:0:\"\";s:10:\"pic1height\";s:0:\"\";s:9:\"pic2width\";s:0:\"\";s:10:\"pic2height\";s:0:\"\";s:13:\"infopic1width\";s:0:\"\";s:14:\"infopic1height\";s:0:\"\";s:13:\"infopic2width\";s:0:\"\";s:14:\"infopic2height\";s:0:\"\";s:4:\"exts\";s:0:\"\";}', 'fleet', 'en');
INSERT INTO `cc_infoclass` VALUES ('102', 0, 20, 'FAQ', '', 'faq', 0, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 2, 20, 2, 'N;', 'a:5:{s:8:\"hasAlias\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:11:\"hasTemplate\";s:1:\"1\";}', 'a:9:{s:6:\"hasTop\";s:1:\"1\";s:14:\"hasRecommended\";s:1:\"1\";s:6:\"hasHot\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:13:\"hasTitleStyle\";s:1:\"1\";s:11:\"hasImageUrl\";s:1:\"1\";s:10:\"hasContent\";s:1:\"1\";}', 'a:13:{s:10:\"cpic1width\";s:0:\"\";s:11:\"cpic1height\";s:0:\"\";s:10:\"cpic2width\";s:0:\"\";s:11:\"cpic2height\";s:0:\"\";s:9:\"pic1width\";s:0:\"\";s:10:\"pic1height\";s:0:\"\";s:9:\"pic2width\";s:0:\"\";s:10:\"pic2height\";s:0:\"\";s:13:\"infopic1width\";s:0:\"\";s:14:\"infopic1height\";s:0:\"\";s:13:\"infopic2width\";s:0:\"\";s:14:\"infopic2height\";s:0:\"\";s:4:\"exts\";s:0:\"\";}', 'faq', 'en');
INSERT INTO `cc_infoclass` VALUES ('103', 0, 30, 'TESTIMONIAL', '', 'testimonial', 0, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 2, 20, 2, 'N;', 'a:5:{s:8:\"hasAlias\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:11:\"hasTemplate\";s:1:\"1\";}', 'a:9:{s:6:\"hasTop\";s:1:\"1\";s:14:\"hasRecommended\";s:1:\"1\";s:6:\"hasHot\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:13:\"hasTitleStyle\";s:1:\"1\";s:11:\"hasImageUrl\";s:1:\"1\";s:10:\"hasContent\";s:1:\"1\";}', 'a:13:{s:10:\"cpic1width\";s:0:\"\";s:11:\"cpic1height\";s:0:\"\";s:10:\"cpic2width\";s:0:\"\";s:11:\"cpic2height\";s:0:\"\";s:9:\"pic1width\";s:0:\"\";s:10:\"pic1height\";s:0:\"\";s:9:\"pic2width\";s:0:\"\";s:10:\"pic2height\";s:0:\"\";s:13:\"infopic1width\";s:0:\"\";s:14:\"infopic1height\";s:0:\"\";s:13:\"infopic2width\";s:0:\"\";s:14:\"infopic2height\";s:0:\"\";s:4:\"exts\";s:0:\"\";}', 'testimonial', 'en');
INSERT INTO `cc_infoclass` VALUES ('104', 0, 40, 'CONTACT', '', 'contact', 0, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 2, 20, 2, 'N;', 'a:5:{s:8:\"hasAlias\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:11:\"hasTemplate\";s:1:\"1\";}', 'a:9:{s:6:\"hasTop\";s:1:\"1\";s:14:\"hasRecommended\";s:1:\"1\";s:6:\"hasHot\";s:1:\"1\";s:12:\"hasPageTitle\";s:1:\"1\";s:11:\"hasKeywords\";s:1:\"1\";s:14:\"hasDescription\";s:1:\"1\";s:13:\"hasTitleStyle\";s:1:\"1\";s:11:\"hasImageUrl\";s:1:\"1\";s:10:\"hasContent\";s:1:\"1\";}', 'a:13:{s:10:\"cpic1width\";s:0:\"\";s:11:\"cpic1height\";s:0:\"\";s:10:\"cpic2width\";s:0:\"\";s:11:\"cpic2height\";s:0:\"\";s:9:\"pic1width\";s:0:\"\";s:10:\"pic1height\";s:0:\"\";s:9:\"pic2width\";s:0:\"\";s:10:\"pic2height\";s:0:\"\";s:13:\"infopic1width\";s:0:\"\";s:14:\"infopic1height\";s:0:\"\";s:13:\"infopic2width\";s:0:\"\";s:14:\"infopic2height\";s:0:\"\";s:4:\"exts\";s:0:\"\";}', 'contact', 'en');
COMMIT;

-- ----------------------------
-- Table structure for cc_message
-- ----------------------------
DROP TABLE IF EXISTS `cc_message`;
CREATE TABLE `cc_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL DEFAULT '0' COMMENT '标题',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '消息类型=1系统消息',
  `message` text NOT NULL COMMENT '内容',
  `desc` text NOT NULL COMMENT '描述',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `message_en` text NOT NULL COMMENT '英文内容',
  `message_ko` text NOT NULL COMMENT '韩文内容',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `define_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '自定义时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='添加消息';

-- ----------------------------
-- Records of cc_message
-- ----------------------------
BEGIN;
INSERT INTO `cc_message` VALUES (11, '{\"zh-cn\":\"AELF钱包测试网络版本\",\"en\":\"AELF Wallet Testnet Version\",\"ko\":\"창세 신작, AELF 지갑 반짝 등장\"}', 2, '<p>AELF钱包测试网络版本</p>', '{\"zh-cn\":\"AELF钱包测试网络版本\",\"en\":\"AELF Wallet Testnet Version\",\"ko\":\"창세 신작, AELF 지갑 반짝 등장\"}', 1562225213, '<p>AELF Wallet Testnet Version</p>', '<p>창세 신작, AELF 지갑 반짝 등장</p>', 2, 1578918022, 1, 0);
INSERT INTO `cc_message` VALUES (14, '{\"zh-cn\":\"aelf 简介\",\"en\":\"Introduction to aelf\",\"ko\":\"\"}', 1, '<p>&aelig;lf是一个去中心化云计算区块链网络，具有高性能、资源隔离特性以及更完善的治理和发展结构。在&aelig;lf的网络中，节点根据类型进行划分，专业化记账节点（全节点）能够运行在服务器集群之上，提高整个区块链网络性能；&ldquo;主链+多侧链&rdquo;结构，有效实现资源隔离、&ldquo;一链一场景&rdquo;；设立代币持有人的委托票选制度，保障网络高效治理及良性发展。ELF主要用于&aelig;lf的付费资源支付及治理决策，其中付费资源包括智能合约部署、升级及执行等操作(如交易手续费、跨链数据传输手续费等)，治理决策包括记账节点的选举、系统新特性的审批及产品重大更新的决策。</p>', '{\"zh-cn\":\"aelf 简介\",\"en\":\"Introduction to aelf\",\"ko\":\"\"}', 1562225213, '<p>Introduction to aelf</p>\r\n\r\n<p>Lolf is a decentralized cloud computing block chain network with high performance, resource isolation and better governance and development structure. In the network of Lolf, the nodes are divided according to their types, and the specialized accounting nodes (all nodes) can run on the server cluster to improve the performance of the whole block chain network; the structure of &quot;main chain + multi-side chain&quot; effectively realizes resource isolation and &quot;one chain and one scene&quot;; and the system of entrusted voting for token holders is established to guarantee the performance of the whole block chain network. Efficient network governance and sound development. ELF is mainly used for the payment of fee-paying resources and the decision-making of governance. Among them, fee-paying resources include the deployment, upgrading and execution of intelligent contracts (such as transaction fees, cross-link data transmission fees, etc.). Governance decisions include the election of accounting nodes, the approval of new features of the system and the decision-making of major product updates.</p>', '<p>测试测试</p>', 7, 1578919014, 0, 1579091791);
INSERT INTO `cc_message` VALUES (15, '{\"zh-cn\":\"aelf受邀参与微软Ignite大会\",\"en\":\"Microsoft Invites aelf to Attend Ignite Conference\",\"ko\":\"Microsoft Invites aelf to Attend Ignite Conference\"}', 1, '<h2>aelf技术副总裁戎朋受邀参加微软Ignite大会，就&ldquo;区块链+电商&rdquo;发展潜力及研发实践进行主题分享</h2>\r\n\r\n<p>2019.12.10</p>\r\n\r\n<p>2019 年 12 月 10 日，由微软云服务的专家和社区共同主持的Microsoft Ignite The Tour北京站活动在北京国家会议中心举行。Microsoft Ignite The Tour旨在为全球技术达人提供最前沿技术信息沟通。</p>\r\n\r\n<p>aelf技术副总裁戎朋与DNT技术专家Mike作为DNT开源社区代表，受邀参加会议并发表了以&ldquo;KT电商秒杀平台架构演进过程及区块链电商系统研发实践&rdquo;为主题的分享演讲，与到场的IT 从业者、技术精英进行了面对面的交流学习和深度探讨。</p>\r\n\r\n<p>互联网技术改变了商品交易方式，促成了淘宝、京东、拼多多等大型电商平台的出现。但平台即中介，随着平台的发展壮大终将形成垄断效应，就如淘宝发展至今，其付费导流、数据中心化和运营方的强势做派，最终都将使得参与其中的商家和消费者深受其垄断效应影响。</p>\r\n\r\n<p>区块链技术可以有效去除互联网行业中的中介平台，为行业赋能，打破原有公司边界、消除中介垄断效应，从而建立更好的激励体系。</p>\r\n\r\n<p>在DNT开源社区主题演讲上，aelf技术副总裁戎朋通过基于aelf开发的电商系统Demo，向参会者讲解了电商+区块链的无限可能性，以及去中心化区块链电商的溯源和订单系统研发实践。</p>\r\n\r\n<p>他在现场演示了基于aelf构建订单模块（创建订单/获取订单）的详细过程，并深入浅出地分析了基于区块链开发应用程序与开发普通应用程序的差异，使开发者对如何在aelf上快速、便捷地创建DAPP有了初步的认识。</p>\r\n\r\n<p>精彩的演讲过后，来自各地的开发爱好者和从业者与aelf技术副总裁戎朋就区块链技术在各行各业的落地应用等问题进行了深入的探讨和切磋，其中不乏计算机专业的在校学生、区块链领域的同行，更有IBM的资深技术人员。</p>\r\n\r\n<p>近些年来，全球范围内的知名企业都对区块链行业进行持续的关注和讨论，微软作为世界顶级技术服务公司也一直关注最前沿的技术动态，尤其是区块链技术的发展和落地应用。</p>\r\n\r\n<p>作为微软的技术合作伙伴，aelf Enterprise已经于2019年7月31日在微软云（Microsoft Azure）正式登陆并运行，这是微软云支持的首个区块链跨链项目，开发者和企业用户可以通过Azure Marketplace上架的aelf Enterprise一键部署去中心化云服务。</p>\r\n\r\n<p>aelf一直致力于布局全球区块链生态，打造商业场景落地应用，帮助更多开发者和企业快速、安全、合规地部署区块链应用。aelf作为区块链领域中的云计算平台，测试网络性能卓越，已具备支撑去中心化商业应用的能力。</p>\r\n\r\n<p>未来，aelf将继续凭借卓越的技术实力，积极探索与Microsoft Azure就区块链推广落地的更多可能性，为区块链实现真正的商用助力，为全球用户带来更为卓越的区块链开发和使用体验。</p>', '{\"zh-cn\":\"aelf技术副总裁戎朋受邀参加微软Ignite大会，就“区块链+电商”发展潜力及研发实践进行主题分享\",\"en\":\"aelf vice president of technology Rong Peng was invited to participate in the Microsoft Ignite conference\",\"ko\":\"\"}', 1562225213, '<h2>aelf vice president of technology Rong Peng was invited to participate in the Microsoft Ignite conference</h2>\r\n\r\n<p>2019.12.10</p>\r\n\r\n<p>On December 10, 2019, the Microsoft Ignite The Tour &mdash; Beijing conference was held at the Beijing National Convention Center. The Tour is designed to provide cutting-edge cloud technologies and developer tools from the world&rsquo;s leading experts.</p>\r\n\r\n<p>The event in Beijing ran over two days, with more than 100 different deep-dive sessions and workshops. DNT open source community, Shengpai developer community, Wechaty community, and Woman Who Code community attended alongside nearly 400 technical experts. Topics included cutting-edge fields of blockchain, artificial intelligence, big data analysis, IoT, mixed reality, security, and compliance.</p>\r\n\r\n<p>Aelf&rsquo;s vice president of technology Rong Peng and DNT technical expert Mike were invited to deliver a shared presentation on the theme of &ldquo;KT e-commerce kills platform architecture, evolution process, and the blockchain e-commerce system research and development practices&rdquo;. The talk garnered strong interest from IT practitioners and technical elites alike, resulting in multiple in-depth discussions.</p>\r\n\r\n<p>Internet technology has changed the way commodities are being traded, leading to the emergence of large-scale e-commerce platforms such as Taobao, JD.com, and Pinduoduo. These platforms, however, are intermediaries. The development and growth of such platforms will eventually result in a monopoly effect.</p>\r\n\r\n<p>Blockchain technology can effectively remove the intermediary within the e-commerce industry, empower and breaking the boundaries of existing companies, and eliminate the monopoly effect of intermediaries, thereby establishing a better incentive system.</p>\r\n\r\n<p>In the keynote speech, Peng explained the infinite possibilities of the integration of e-commerce with blockchain through an e-commerce system Demo based on aelf.</p>\r\n\r\n<p>He demonstrated the detailed process of constructing an order module (create order/get the order) based on aelf, and analyzed the differences between developing applications based on blockchain and ordinary applications in an easy-to-understand manner, enabling developers to learn how to quickly use aelf.</p>\r\n\r\n<p>After the speech, development enthusiasts and practitioners approached Peng for in-depth discussions on the application of blockchain technology within various industries, including senior technical staff from IBM.</p>\r\n\r\n<p>In recent years, well-known companies around the world have started to pay attention to and discussed the blockchain industry. Microsoft, one of the world&rsquo;s top technology service companies, is one such enterprise that has been paying attention to many cutting-edge technology developments, especially the development and application of blockchain technology.</p>\r\n\r\n<p>As a Microsoft technology partner, aelf Enterprise was officially listed on Microsoft Azure on July 31, 2019. This is the first blockchain cross-chain project supported by Microsoft Cloud. Developers and enterprise users can initiate the One-click deployment of decentralized cloud services through aelf Enterprise listed on the Azure Marketplace.</p>\r\n\r\n<p>aelf has been committed to deploying the global blockchain ecosystem, creating commercial applications that can be deployed on the ground, helping more developers and enterprises deploy blockchain applications quickly, securely, and in compliance. As a cloud computing platform in the field of blockchain, aelf has excellent test network performance and has the ability to support decentralized commercial applications.</p>\r\n\r\n<p>In the future, aelf will continue to rely on its superior technical strength to actively explore more possibilities for promoting the implementation of blockchain with Microsoft Azure. We aim to achieve real commercial assistance for blockchain and drive blockchain adoption to a global community of developers.</p>\r\n\r\n<p>&lt;span style=&quot;\\\\\\\\\\\\\\\\&amp;quot;color:rgb(149,&quot; 111,=&quot;&quot; 231);=&quot;&quot; font-size:10pt\\\\\\\\\\\\\\\\&quot;=&quot;&quot;&gt;</p>', '<h2>aelf vice president of technology Rong Peng was invited to participate in the Microsoft Ignite conference</h2>\r\n\r\n<p>2019.12.10</p>\r\n\r\n<p>On December 10, 2019, the Microsoft Ignite The Tour &mdash; Beijing conference was held at the Beijing National Convention Center. The Tour is designed to provide cutting-edge cloud technologies and developer tools from the world&rsquo;s leading experts.</p>\r\n\r\n<p>The event in Beijing ran over two days, with more than 100 different deep-dive sessions and workshops. DNT open source community, Shengpai developer community, Wechaty community, and Woman Who Code community attended alongside nearly 400 technical experts. Topics included cutting-edge fields of blockchain, artificial intelligence, big data analysis, IoT, mixed reality, security, and compliance.</p>\r\n\r\n<p>Aelf&rsquo;s vice president of technology Rong Peng and DNT technical expert Mike were invited to deliver a shared presentation on the theme of &ldquo;KT e-commerce kills platform architecture, evolution process, and the blockchain e-commerce system research and development practices&rdquo;. The talk garnered strong interest from IT practitioners and technical elites alike, resulting in multiple in-depth discussions.</p>\r\n\r\n<p>Internet technology has changed the way commodities are being traded, leading to the emergence of large-scale e-commerce platforms such as Taobao, JD.com, and Pinduoduo. These platforms, however, are intermediaries. The development and growth of such platforms will eventually result in a monopoly effect.</p>\r\n\r\n<p>Blockchain technology can effectively remove the intermediary within the e-commerce industry, empower and breaking the boundaries of existing companies, and eliminate the monopoly effect of intermediaries, thereby establishing a better incentive system.</p>\r\n\r\n<p>In the keynote speech, Peng explained the infinite possibilities of the integration of e-commerce with blockchain through an e-commerce system Demo based on aelf.</p>\r\n\r\n<p>He demonstrated the detailed process of constructing an order module (create order/get the order) based on aelf, and analyzed the differences between developing applications based on blockchain and ordinary applications in an easy-to-understand manner, enabling developers to learn how to quickly use aelf.</p>\r\n\r\n<p>After the speech, development enthusiasts and practitioners approached Peng for in-depth discussions on the application of blockchain technology within various industries, including senior technical staff from IBM.</p>\r\n\r\n<p>In recent years, well-known companies around the world have started to pay attention to and discussed the blockchain industry. Microsoft, one of the world&rsquo;s top technology service companies, is one such enterprise that has been paying attention to many cutting-edge technology developments, especially the development and application of blockchain technology.</p>\r\n\r\n<p>As a Microsoft technology partner, aelf Enterprise was officially listed on Microsoft Azure on July 31, 2019. This is the first blockchain cross-chain project supported by Microsoft Cloud. Developers and enterprise users can initiate the One-click deployment of decentralized cloud services through aelf Enterprise listed on the Azure Marketplace.</p>\r\n\r\n<p>aelf has been committed to deploying the global blockchain ecosystem, creating commercial applications that can be deployed on the ground, helping more developers and enterprises deploy blockchain applications quickly, securely, and in compliance. As a cloud computing platform in the field of blockchain, aelf has excellent test network performance and has the ability to support decentralized commercial applications.</p>\r\n\r\n<p>In the future, aelf will continue to rely on its superior technical strength to actively explore more possibilities for promoting the implementation of blockchain with Microsoft Azure. We aim to achieve real commercial assistance for blockchain and drive blockchain adoption to a global community of developers.</p>', 5, 1578919244, 1, 1575981604);
INSERT INTO `cc_message` VALUES (16, '{\"zh-cn\":\"Google Cloud支持首个区块链跨链项目aelf\",\"en\":\"Google Cloud Supports aelf Blockchain\",\"ko\":\"Google Cloud Supports aelf Blockchain\"}', 1, '<h2>Google Cloud支持首个区块链跨链项目aelf，一键部署去中心化云服务</h2>\r\n\r\n<p>2019.10.30</p>\r\n\r\n<p>10月28日，aelf Enterprise在全球领先的公有云计算平台谷歌云Google Cloud正式登陆并运行。开发者可以通过Google Cloud Platform上架的aelf Enterprise，大规模构建、管理和扩展区块链应用。此外，aelf还提供了配套的使用教程、智能合约及Dapp开发教学案例，以便用户可以专注于业务逻辑以及应用开发。</p>\r\n\r\n<p>aelf Enterprise是一个已经实现商用的底层区块链网络，目前已为全球多家大型企业、机构提供整体区块链商业化解决方案。此次aelf Enterprise上线Google Cloud将为企业级用户提供一个更为高效、便捷、优质的开发环境，企业用户不必在本地配置和设置环境，就能完成开发智能合约。</p>\r\n\r\n<p>aelf Enterprise在为企业提供区块链基础服务的同时，还能根据客户需求，提供相应的示例代码以及应用开发指南，帮助用户最快2分钟启动区块链网络、部署事物节点、创建DAPP等，推动区块链行业新业务模式的拓展，加速多机构间商业网络的革新，为后期不同主体间的业务往来传递价值。</p>\r\n\r\n<p>布局全球区块链生态，打造商业场景落地应用，帮助更多开发者和企业快速、安全、合规地部署区块链应用，是aelf的立身之本。未来，aelf将继续加强并扩展平台上的功能与服务,为Google Cloud上的用户带来更为独特的商业优势。</p>', '{\"zh-cn\":\"Google Cloud支持首个区块链跨链项目aelf，一键部署去中心化云服务\",\"en\":\"aelf Enterprise listed on Google Cloud Marketplace, available to millions of customers including Paypal, eBay, HSBC, HTC & Deloitte\",\"ko\":\"aelf Enterprise listed on Google Cloud Marketplace, available to millions of customers including Paypal, eBay, HSBC, HTC & Deloitte\"}', 1578913492, '<h2>aelf Enterprise listed on Google Cloud Marketplace, available to millions of customers including Paypal, eBay, HSBC, HTC &amp; Deloitte</h2>\r\n\r\n<p>2019.10.30</p>\r\n\r\n<p>On Oct 29th, aelf Enterprise was officially listed on Google Cloud Marketplace, one of the world&rsquo;s largest cloud computing platforms. Developers can deploy their own applications on aelf enterprise utilizing Google&rsquo;s Compute Engine with only a few commands. This allows enterprises to have a working blockchain entirely for their own needs within minutes. The listing also provides developers with supporting tutorials, smart contracts, and dApp samples.</p>\r\n\r\n<p>Google Cloud, is a subsidiary of Google and runs cloud computing services on the same infrastructure as Google Search &amp; YouTube. The platform is available in 20 regions around the world and as of 2018, has over 4 million customers. Some of the top customers include Bloomberg, Target, 20th Century Fox, Deloitte, eBay, PayPal, HTC, Woolworths, Twitter, HSBC, ASICS, and Airbus. By listing aelf, the aelf enterprise blockchain is now presenting to all of Google Cloud&rsquo;s customers.</p>\r\n\r\n<p>One of aelf&rsquo;s strongest technological features is the ability to process tens of thousands of transactions per second. aelf Enterprise will provide high-performance blockchain BaaS through massive cloud data storage, one-click-deployment blockchain toolkits and flexible dApp development modules &mdash; all functions attuned to test and implement blockchain with ease and precision by enterprises. aelf seeks to accelerate dApp development and expand blockchain ecosystem by making aelf more accessible for the industries seeking blockchain solutions.</p>\r\n\r\n<p>aelf will continue to push current technological boundaries and lead commercial blockchain adoption. aelf is continuously updating its system and code execution to make it easier for enterprises to experience blockchain BaaS solutions. The Enterprise version serves as a strong foundation and a segway for the aelf public network, which will connect the different Enterprise users under a greater umbrella of one aelf ecosystem.</p>', '<h2>aelf Enterprise listed on Google Cloud Marketplace, available to millions of customers including Paypal, eBay, HSBC, HTC &amp; Deloitte</h2>\r\n\r\n<p>2019.10.30</p>\r\n\r\n<p>On Oct 29th, aelf Enterprise was officially listed on Google Cloud Marketplace, one of the world&rsquo;s largest cloud computing platforms. Developers can deploy their own applications on aelf enterprise utilizing Google&rsquo;s Compute Engine with only a few commands. This allows enterprises to have a working blockchain entirely for their own needs within minutes. The listing also provides developers with supporting tutorials, smart contracts, and dApp samples.</p>\r\n\r\n<p>Google Cloud, is a subsidiary of Google and runs cloud computing services on the same infrastructure as Google Search &amp; YouTube. The platform is available in 20 regions around the world and as of 2018, has over 4 million customers. Some of the top customers include Bloomberg, Target, 20th Century Fox, Deloitte, eBay, PayPal, HTC, Woolworths, Twitter, HSBC, ASICS, and Airbus. By listing aelf, the aelf enterprise blockchain is now presenting to all of Google Cloud&rsquo;s customers.</p>\r\n\r\n<p>One of aelf&rsquo;s strongest technological features is the ability to process tens of thousands of transactions per second. aelf Enterprise will provide high-performance blockchain BaaS through massive cloud data storage, one-click-deployment blockchain toolkits and flexible dApp development modules &mdash; all functions attuned to test and implement blockchain with ease and precision by enterprises. aelf seeks to accelerate dApp development and expand blockchain ecosystem by making aelf more accessible for the industries seeking blockchain solutions.</p>\r\n\r\n<p>aelf will continue to push current technological boundaries and lead commercial blockchain adoption. aelf is continuously updating its system and code execution to make it easier for enterprises to experience blockchain BaaS solutions. The Enterprise version serves as a strong foundation and a segway for the aelf public network, which will connect the different Enterprise users under a greater umbrella of one aelf ecosystem.</p>', 0, 1578919286, 1, 1572439257);
COMMIT;

-- ----------------------------
-- Table structure for cc_relation
-- ----------------------------
DROP TABLE IF EXISTS `cc_relation`;
CREATE TABLE `cc_relation` (
  `id` varchar(20) NOT NULL,
  `ordinal` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `url` varchar(100) NOT NULL,
  `target` varchar(20) NOT NULL,
  `lang` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台菜单';

-- ----------------------------
-- Records of cc_relation
-- ----------------------------
BEGIN;
INSERT INTO `cc_relation` VALUES ('101', 10, 'Information management', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('102', 20, 'Senior Management', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('103', 30, 'System Management', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('101101', 10, 'Information / Search', '?con=admin&ctl=info/&act=search', '', 'en');
INSERT INTO `cc_relation` VALUES ('101102', 20, 'Information Classification Management', '?con=admin&ctl=info/class', '', 'en');
INSERT INTO `cc_relation` VALUES ('102101', 10, 'Site Settings', '?con=admin&ctl=system/site', '', 'en');
INSERT INTO `cc_relation` VALUES ('103101', 10, 'Role', '?con=admin&ctl=system/role', '', 'en');
INSERT INTO `cc_relation` VALUES ('103102', 20, 'User', '?con=admin&ctl=system/user', '', 'en');
INSERT INTO `cc_relation` VALUES ('102102', 20, 'Admin email', '?con=admin&ctl=adv/adminemail', '', 'en');
INSERT INTO `cc_relation` VALUES ('102103', 30, 'Email Receiver', '?con=admin&ctl=adv/subscribe', '', 'en');
INSERT INTO `cc_relation` VALUES ('102104', 40, 'Email Receiver group', '?con=admin&ctl=adv/subscribeclass', '', 'en');
INSERT INTO `cc_relation` VALUES ('102105', 50, 'Google Analytics setting', '?con=admin&ctl=adv/ga', '', 'en');
INSERT INTO `cc_relation` VALUES ('102106', 60, 'Google Analytics', '?con=admin&ctl=adv/ga&act=login', '', 'en');
INSERT INTO `cc_relation` VALUES ('104', 40, 'Page modify', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('105', 50, 'Function', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('106', 60, 'Email Marketing', '', '', 'en');
INSERT INTO `cc_relation` VALUES ('105101', 10, 'Site settings', '?con=admin&ctl=system/site', '', 'en');
INSERT INTO `cc_relation` VALUES ('105102', 20, 'Admin email', '?con=admin&ctl=adv/adminemail', '', 'en');
INSERT INTO `cc_relation` VALUES ('106101', 10, 'Email Receiver', '?con=admin&ctl=adv/subscribe', '', 'en');
INSERT INTO `cc_relation` VALUES ('106102', 20, 'Email Receiver group', '?con=admin&ctl=adv/subscribeclass', '', 'en');
INSERT INTO `cc_relation` VALUES ('105103', 30, 'Google Analytics', '?con=admin&ctl=adv/ga&act=login', '_blank', 'en');
INSERT INTO `cc_relation` VALUES ('105104', 40, 'Google Analytics setting', '?con=admin&ctl=adv/ga', '', 'en');
INSERT INTO `cc_relation` VALUES ('105105', 50, 'Vehicle Management ', '?con=admin&ctl=adv/vehicle', '', 'en');
INSERT INTO `cc_relation` VALUES ('105106', 60, 'Quote Management', '?con=admin&ctl=adv/quote', '', 'en');
INSERT INTO `cc_relation` VALUES ('105107', 70, 'Order Management', '?con=admin&ctl=adv/order', '', 'en');
INSERT INTO `cc_relation` VALUES ('105108', 80, 'Payment Management', '?con=admin&ctl=adv/transaction', '', 'en');
INSERT INTO `cc_relation` VALUES ('105109', 90, 'FAQ', '?con=admin&ctl=adv/faq', '', 'en');
INSERT INTO `cc_relation` VALUES ('105110', 100, 'System Configuration', '?con=admin&ctl=adv/config_data', '', 'en');
INSERT INTO `cc_relation` VALUES ('105111', 110, 'Message Management', '?con=admin&ctl=adv/contact', '', 'en');
INSERT INTO `cc_relation` VALUES ('105112', 120, 'Comments Management', '?con=admin&ctl=adv/comment', '', 'en');
INSERT INTO `cc_relation` VALUES ('105113', 130, 'Service Type', '?con=admin&ctl=adv/service', '', 'en');
INSERT INTO `cc_relation` VALUES ('104101', 10, 'FLEET', '?con=admin&ctl=info/class&act=edit&id=101&noback=1', '', 'en');
INSERT INTO `cc_relation` VALUES ('104102', 20, 'FAQ', '?con=admin&ctl=info/class&act=edit&id=102&noback=1', '', 'en');
INSERT INTO `cc_relation` VALUES ('104103', 30, 'TESTIMONIAL', '?con=admin&ctl=info/class&act=edit&id=103&noback=1', '', 'en');
INSERT INTO `cc_relation` VALUES ('104104', 40, 'CONTACT', '?con=admin&ctl=info/class&act=edit&id=104&noback=1', '', 'en');
INSERT INTO `cc_relation` VALUES ('107', 10, '内容管理', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('107101', 10, '如何使用aelf wallet', '?con=admin&ctl=adv/info&cid=101', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('107102', 20, '隐私条款', '?con=admin&ctl=adv/info&cid=102', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('108', 20, '设置', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('108101', 10, '站点基本设置', '?con=admin&ctl=system/site', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('108102', 20, 'APP参数设置', '?con=admin&ctl=adv/setting', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('109', 30, 'dapp管理', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('109102', 20, 'banner图管理', '?con=admin&ctl=onchain/dapps_banner', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('109103', 30, ' 游戏列表', '?con=admin&ctl=onchain/dapps_games', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('109104', 40, '搜索热词管理', '?con=admin&ctl=onchain/dapps_search', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('110', 40, 'APP数据管理', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('110101', 10, '用户address管理', '?con=admin&ctl=app/addr', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('107103', 30, '用户协议', '?con=admin&ctl=adv/info&cid=103', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('107104', 40, '帮助反馈', '?con=admin&ctl=adv/info&cid=104', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('111', 50, '会员', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('111101', 10, '地址管理', '?con=admin&ctl=member/address', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('111102', 20, '地址簿', '?con=admin&ctl=member/address_book', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('108104', 40, '版本更新管理', '?con=admin&ctl=system/upgrade', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('112', 60, '消息管理', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('112101', 10, '消息中心', '?con=admin&ctl=message/system_info', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('113', 1, 'System Management', '', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('113101', 10, 'Role', '?con=admin&ctl=system/role', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('113102', 20, 'User', '?con=admin&ctl=system/user', '', 'zh-cn');
INSERT INTO `cc_relation` VALUES ('111103', 30, '用户反馈', '?con=admin&ctl=member/feedback', '', 'zh-cn');
COMMIT;

-- ----------------------------
-- Table structure for cc_role
-- ----------------------------
DROP TABLE IF EXISTS `cc_role`;
CREATE TABLE `cc_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `isSystem` smallint(6) NOT NULL DEFAULT '0',
  `isSuper` tinyint(4) NOT NULL DEFAULT '0',
  `action` text,
  `info` text,
  `infoClass` text,
  `relation` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='角色相关';

-- ----------------------------
-- Records of cc_role
-- ----------------------------
BEGIN;
INSERT INTO `cc_role` VALUES (1, 'System administrator', 'Permission to have all the features of the system', 1, 0, 'a:40:{i:0;s:10:\"editor/pic\";i:1;s:10:\"upload/pic\";i:2;s:11:\"upload/file\";i:3;s:16:\"info/class/index\";i:4;s:14:\"info/class/add\";i:5;s:15:\"info/class/edit\";i:6;s:18:\"info/class/setting\";i:7;s:17:\"info/class/delete\";i:8;s:24:\"info/class/columnSetting\";i:9;s:16:\"info/index/index\";i:10;s:14:\"info/index/add\";i:11;s:15:\"info/index/edit\";i:12;s:17:\"info/index/delete\";i:13;s:18:\"info/index/preview\";i:14;s:17:\"info/index/search\";i:15;s:15:\"info/index/move\";i:16;s:15:\"info/index/copy\";i:17;s:16:\"info/index/state\";i:18;s:17:\"system/link/index\";i:19;s:15:\"system/link/add\";i:20;s:16:\"system/link/edit\";i:21;s:18:\"system/link/delete\";i:22;s:17:\"system/role/index\";i:23;s:15:\"system/role/add\";i:24;s:16:\"system/role/edit\";i:25;s:18:\"system/role/delete\";i:26;s:21:\"system/role/authorize\";i:27;s:17:\"system/site/index\";i:28;s:17:\"system/site/other\";i:29;s:17:\"system/user/index\";i:30;s:15:\"system/user/add\";i:31;s:16:\"system/user/edit\";i:32;s:18:\"system/user/delete\";i:33;s:21:\"system/user/authorize\";i:34;s:22:\"system/user/changepass\";i:35;s:13:\"0/class/index\";i:36;s:11:\"0/class/add\";i:37;s:13:\"0/index/index\";i:38;s:11:\"0/index/add\";i:39;s:14:\"0/index/search\";}', 'N;', 'N;', 'a:6:{i:0;s:6:\"101101\";i:1;s:6:\"101102\";i:2;s:6:\"102101\";i:3;s:6:\"102102\";i:4;s:6:\"103101\";i:5;s:6:\"103102\";}');
INSERT INTO `cc_role` VALUES (2, 'Administrator', '', 1, 0, 'N;', 'N;', 'N;', 'a:2:{i:0;s:6:\"102101\";i:1;s:6:\"102102\";}');
INSERT INTO `cc_role` VALUES (3, '普通角色', '普通角色', 0, 0, 'a:11:{i:0;s:20:\"member/address/index\";i:1;s:25:\"member/address_book/index\";i:2;s:21:\"member/feedback/index\";i:3;s:22:\"member/feedback/detail\";i:4;s:25:\"message/system_info/index\";i:5;s:24:\"message/system_info/edit\";i:6;s:26:\"message/system_info/delete\";i:7;s:24:\"message/system_info/push\";i:8;s:14:\"adv/info/index\";i:9;s:13:\"adv/info/edit\";i:10;s:15:\"adv/info/delete\";}', 'N;', 'N;', 'a:8:{i:0;s:6:\"107101\";i:1;s:6:\"107102\";i:2;s:6:\"107103\";i:3;s:6:\"107104\";i:4;s:6:\"111101\";i:5;s:6:\"111102\";i:6;s:6:\"111103\";i:7;s:6:\"112101\";}');
COMMIT;

-- ----------------------------
-- Table structure for cc_send_message
-- ----------------------------
DROP TABLE IF EXISTS `cc_send_message`;
CREATE TABLE `cc_send_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `mid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息列表id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0未读 1已读 2未读',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '读时间',
  `type` int(11) unsigned NOT NULL COMMENT '类型：1系统消息',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `userid_2` (`userid`,`mid`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `mid` (`mid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发送消息列表';

-- ----------------------------
-- Table structure for cc_service
-- ----------------------------
DROP TABLE IF EXISTS `cc_service`;
CREATE TABLE `cc_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for cc_sites
-- ----------------------------
DROP TABLE IF EXISTS `cc_sites`;
CREATE TABLE `cc_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `pageTitle` varchar(250) NOT NULL,
  `keywords` varchar(120) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `icp` varchar(20) DEFAULT NULL,
  `copyright` text,
  `isCopyrightEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `contact` text,
  `isContactEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `headJavascript` text,
  `isHeadJavascriptEnabled` tinyint(1) NOT NULL DEFAULT '0',
  `footJavascript` text,
  `isFootJavascriptEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='站点设置相关';

-- ----------------------------
-- Records of cc_sites
-- ----------------------------
BEGIN;
INSERT INTO `cc_sites` VALUES (1, 'Smart Bus', 'Smart Bus', '', '', '', '', 0, '', 0, NULL, 0, NULL, 0, 'en');
INSERT INTO `cc_sites` VALUES (2, '', '', '', '', '', '', 1, '', 1, NULL, 0, NULL, 1, 'zh-cn');
COMMIT;

-- ----------------------------
-- Table structure for cc_smtp
-- ----------------------------
DROP TABLE IF EXISTS `cc_smtp`;
CREATE TABLE `cc_smtp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salt` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `senderName` varchar(255) NOT NULL COMMENT '发送人姓名',
  `senderEmail` varchar(255) NOT NULL COMMENT '发送人邮箱',
  `sign` text COMMENT '签名',
  `host` varchar(255) NOT NULL,
  `port` varchar(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0禁用,1启用',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='SMTP';

-- ----------------------------
-- Records of cc_smtp
-- ----------------------------
BEGIN;
INSERT INTO `cc_smtp` VALUES (1, '23q8d%$lwxd', 'elf\'s aliyun smtp', 'ELF', '', 'ELF', 'smtp.mxhichina.com', '465', '', '', 1);
COMMIT;

-- ----------------------------
-- Table structure for cc_transaction
-- ----------------------------
DROP TABLE IF EXISTS `cc_transaction`;
CREATE TABLE `cc_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transNo` varchar(50) NOT NULL,
  `order_num` varchar(50) NOT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(16,2) NOT NULL,
  `notify_info` text,
  `payment` varchar(50) DEFAULT NULL,
  `pay_id` varchar(150) DEFAULT NULL,
  `is_pay` tinyint(4) NOT NULL DEFAULT '0',
  `pay_time` int(10) NOT NULL DEFAULT '0',
  `braintree_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for cc_user
-- ----------------------------
DROP TABLE IF EXISTS `cc_user`;
CREATE TABLE `cc_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `displayName` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `isAdmin` tinyint(4) NOT NULL DEFAULT '0',
  `isApproved` smallint(6) NOT NULL DEFAULT '0',
  `lastPasswordChangedDate` int(10) DEFAULT NULL,
  `lastLoginIP` varchar(15) DEFAULT NULL,
  `lastLoginDate` int(10) DEFAULT NULL,
  `loginCount` int(11) NOT NULL DEFAULT '0',
  `createdDate` int(10) NOT NULL,
  `lastModifiedDate` int(10) DEFAULT NULL,
  `role` int(11) NOT NULL,
  `roleExtendType` tinyint(4) NOT NULL DEFAULT '0',
  `groupid` int(11) NOT NULL DEFAULT '1',
  `action` text,
  `info` text,
  `infoClass` text,
  `relation` text,
  `oauth_connect` varchar(20) NOT NULL DEFAULT '',
  `oauth_connect_id` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='用户相关';

-- ----------------------------
-- Records of cc_user
-- ----------------------------
BEGIN;
INSERT INTO `cc_user` VALUES (1, 'super_admin', 'da6526d3bca08a010f6f1941662096c4', 'System administrator', 'admin@localhost.com', 1, 1, NULL, '127.0.0.1', 1586422124, 10, 1332743248, NULL, 1, 1, 0, NULL, NULL, NULL, NULL, '', '');
INSERT INTO `cc_user` VALUES (2, 'admin', 'e91ee822c8d94b4fdf6513a586eff53d', 'Administrator', 'admin@localhost.com', 1, 1, NULL, '127.0.0.1', 1333548903, 2, 1333548679, NULL, 2, 1, 0, NULL, NULL, NULL, NULL, '', '');
INSERT INTO `cc_user` VALUES (3, 'test', 'b7eb454f22192640649ddf455be97025', 'test', 'test@qq.com', 1, 1, NULL, '114.97.231.231', 1559785973, 14, 1559731852, NULL, 3, 1, 0, NULL, NULL, NULL, NULL, '', '');
INSERT INTO `cc_user` VALUES (4, 'elf-admin', '8d21bed1ae60b1b18c5385b316f96278', 'elf', 'elf-admin@admin.com', 1, 1, NULL, '185.222.221.197', 1578984299, 12, 1562834111, NULL, 3, 1, 0, NULL, NULL, NULL, NULL, '', '');
COMMIT;

-- ----------------------------
-- Table structure for cc_user_address
-- ----------------------------
DROP TABLE IF EXISTS `cc_user_address`;
CREATE TABLE `cc_user_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '备注昵称',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `currency` varchar(50) NOT NULL DEFAULT '' COMMENT '货币',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `address` (`address`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cc_user_transaction
-- ----------------------------
DROP TABLE IF EXISTS `cc_user_transaction`;
CREATE TABLE `cc_user_transaction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tx_id` varchar(200) NOT NULL DEFAULT '' COMMENT '交易id',
  `chain_id` varchar(50) NOT NULL DEFAULT '' COMMENT '链id',
  `address_from` varchar(200) NOT NULL DEFAULT '',
  `address_to` varchar(200) NOT NULL DEFAULT '',
  `tx_status` varchar(50) NOT NULL DEFAULT '' COMMENT '状态\r\n',
  `time` varchar(30) NOT NULL DEFAULT '0' COMMENT '接口返回时间，带时区',
  `time_stamp` int(11) NOT NULL COMMENT '时间戳',
  `method` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `quantity` bigint(64) unsigned DEFAULT '0' COMMENT '金额',
  `status_from` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0、未读 1、已读 2 删除',
  `status_to` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0、未读 1、已读 2 删除',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `symbol` varchar(10) NOT NULL DEFAULT '' COMMENT '币名',
  `block_height` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '块高度',
  `memo` varchar(256) NOT NULL DEFAULT '' COMMENT '交易memo',
  `to_chainid` varchar(20) NOT NULL DEFAULT '' COMMENT '接收链本链为空',
  `from_chainid` varchar(20) NOT NULL DEFAULT '' COMMENT '转出链本连为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tx_id` (`tx_id`),
  KEY `to_chainid` (`to_chainid`),
  KEY `from_chainid` (`from_chainid`),
  KEY `symbol` (`symbol`),
  KEY `address_from` (`address_from`),
  KEY `address_to` (`address_to`),
  KEY `status_from` (`status_from`),
  KEY `status_to` (`status_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cc_version
-- ----------------------------
DROP TABLE IF EXISTS `cc_version`;
CREATE TABLE `cc_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(20) NOT NULL,
  `appUrl` varchar(255) NOT NULL,
  `intro` varchar(2000) NOT NULL,
  `verNo` varchar(20) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `is_force` tinyint(1) NOT NULL DEFAULT '0',
  `intro_en` varchar(2000) NOT NULL DEFAULT '',
  `intro_ko` varchar(2000) NOT NULL DEFAULT '',
  `min_version` varchar(20) NOT NULL DEFAULT '',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upgrade_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='APP版本';

-- ----------------------------
-- Records of cc_version
-- ----------------------------
BEGIN;
INSERT INTO `cc_version` VALUES (1, 'iOS', 'https://fir.im/iOSAELF', '支持数字身份创建、导入#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.0', 1, 1, 'Support for Digital Identity Creation and Import#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', 'USDT, LTC, ONT 온체인 지갑 추가, 개인 프라이빗으로 관리#@#@#@지갑 안전 보안 업데이트 #@#@#@(아이폰 5 이하 기기는 지원하지 않습니다.)', '', 1561356470, 1577253757, 1561356470);
INSERT INTO `cc_version` VALUES (2, 'Android', 'https://fir.im/AelfAndroid', '支持数字身份创建、导入#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.0', 1, 1, 'Support for Digital Identity Creation and Import#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', 'USDT, LTC, ONT 온체인 지갑 추가, 개인 프라이빗으로 관리#@#@#@지갑 안전 보안 업데이트 #@#@#@(아이폰 5 이하 기기는 지원하지 않습니다.)', '', 1561356470, 1577253771, 1561356470);
INSERT INTO `cc_version` VALUES (3, 'iOS', 'https://fir.im/iOSAELF', '支持数字身份创建、导入#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.1', 1, 1, 'Support for Digital Identity Creation and Import#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', '1USDT, LTC, ONT 온체인 지갑 추가, 개인 프라이빗으로 관리#@#@#@지갑 안전 보안 업데이트 #@#@#@(아이폰 5 이하 기기는 지원하지 않습니다.)', '', 1561356470, 1577253749, 1561790495);
INSERT INTO `cc_version` VALUES (4, 'Android', 'https://fir.im/AelfAndroid', '支持数字身份创建、导入#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.1', 1, 1, 'Support for Digital Identity Creation and Import#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', '', '', 1562826849, 1577253792, 1562774400);
INSERT INTO `cc_version` VALUES (5, 'Android', 'https://fir.im/AelfAndroid', '支持数字身份创建、导入3#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.2', 1, 1, 'Support for Digital Identity Creation and Import3#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', '', '', 1562826849, 1577253778, 1577203200);
INSERT INTO `cc_version` VALUES (6, 'iOS', 'https://fir.im/iOSAELF', '支持数字身份创建、导入3#@#@#@支持转账、收款功能#@#@#@支持实时行情显示#@#@#@全新UI设计，极致用户体验', '1.0.2', 1, 1, 'Support for Digital Identity Creation and Import3#@#@#@Supporting Transfer and Receiving Functions#@#@#@Support Real-time Market Display#@#@#@New UI Design, Ultimate User Experience', '', '', 1577253733, 0, 1577203200);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
