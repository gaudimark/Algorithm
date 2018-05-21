/*
Navicat MySQL Data Transfer

Source Server         : 本地测试服
Source Server Version : 50639
Source Host           : 192.168.188.189:3306
Source Database       : football

Target Server Type    : MYSQL
Target Server Version : 50639
File Encoding         : 65001

Date: 2018-05-19 22:58:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lt_agent_arena
-- ----------------------------
DROP TABLE IF EXISTS `lt_agent_arena`;
CREATE TABLE `lt_agent_arena` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '擂台发布者',
  `agent_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理员',
  `arena_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bet_money` double(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投注金额',
  `bet_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投注人数',
  `win_total` double(10,2) DEFAULT '0.00' COMMENT '擂台盈亏',
  `arena_status` int(11) DEFAULT NULL COMMENT '擂台状态',
  `status` int(11) DEFAULT NULL COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `agent_user_id_arena_id` (`agent_user_id`,`arena_id`) USING BTREE,
  KEY `agent_user_id` (`agent_user_id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='代理所属擂台（针对单个擂台）';

-- ----------------------------
-- Table structure for lt_agent_user
-- ----------------------------
DROP TABLE IF EXISTS `lt_agent_user`;
CREATE TABLE `lt_agent_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mark` varchar(32) DEFAULT NULL COMMENT '用户标识',
  `user_id` int(11) DEFAULT NULL COMMENT '所属上级用户',
  `username` varchar(20) DEFAULT NULL COMMENT '帐户',
  `init_pwd` varchar(1000) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL COMMENT '密码',
  `salt` varchar(10) DEFAULT NULL COMMENT '密码辅助',
  `rate` double(5,2) NOT NULL DEFAULT '0.00' COMMENT '提层比例',
  `arena_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '擂台方式，1-全部，2-单个',
  `gold` double(20,2) unsigned DEFAULT '0.00' COMMENT '帐户金币',
  `freeze` double(20,2) unsigned DEFAULT '0.00' COMMENT '冻结金币',
  `money` double(10,2) unsigned DEFAULT '0.00' COMMENT '帐户余额',
  `bet_money` double(20,2) unsigned DEFAULT '0.00' COMMENT '累计投注金额',
  `win_total` double(20,2) unsigned DEFAULT '0.00' COMMENT '总收益',
  `win_unsettlement` double(20,2) unsigned DEFAULT '0.00',
  `last_login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '帐户状态',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `mark` (`mark`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `arena_type` (`arena_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='代理帐号';

-- ----------------------------
-- Table structure for lt_agent_user_funds_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_agent_user_funds_log`;
CREATE TABLE `lt_agent_user_funds_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `classify` int(11) DEFAULT NULL COMMENT '资金去向类型（投注，充值等）',
  `type` tinyint(3) unsigned NOT NULL COMMENT '金币或金钱(1金币，2金钱)',
  `number` double(20,2) DEFAULT NULL COMMENT '金额',
  `before_num` double(20,2) DEFAULT NULL COMMENT '操作前',
  `after_num` double(20,2) DEFAULT NULL COMMENT '操作后',
  `explain` varchar(200) DEFAULT NULL COMMENT '总金额去向说明',
  `data` text COMMENT '关联数据',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_user_funds_log_user1_idx` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户资金详情表';

-- ----------------------------
-- Table structure for lt_agent_user_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_agent_user_log`;
CREATE TABLE `lt_agent_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `explain` varchar(500) DEFAULT NULL COMMENT '原因',
  `data` text,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='代理用户日志';

-- ----------------------------
-- Table structure for lt_arena
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena`;
CREATE TABLE `lt_arena` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mark` varchar(32) DEFAULT NULL COMMENT '擂台地址标识',
  `has_sys` tinyint(1) unsigned DEFAULT '0' COMMENT '是否是系统擂台',
  `has_robot` tinyint(1) unsigned DEFAULT '0' COMMENT '是否是机器人',
  `has_recommend` tinyint(1) unsigned DEFAULT NULL COMMENT '是否推荐',
  `has_hide` tinyint(1) unsigned DEFAULT '0' COMMENT '是否隐藏',
  `classify` tinyint(1) unsigned DEFAULT '1' COMMENT '擂台类型，1-金币局，2-征信局',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `game_id` int(10) unsigned DEFAULT NULL COMMENT '所属游戏',
  `user_nickname` varchar(20) DEFAULT NULL,
  `game_type` mediumint(8) unsigned DEFAULT '1' COMMENT '比赛类型',
  `play_id` int(10) unsigned NOT NULL COMMENT '比赛ID',
  `match_id` int(10) unsigned NOT NULL COMMENT '赛事ID',
  `rules_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩法类型',
  `rules_type` int(10) unsigned DEFAULT NULL COMMENT '玩法类型-rules.id',
  `odds_id` int(10) unsigned DEFAULT '0' COMMENT '同步赔率ID',
  `company_id` int(10) unsigned DEFAULT '0' COMMENT '赔率公司',
  `odds` text COMMENT '擂台赔率',
  `deposit` bigint(20) unsigned DEFAULT '0' COMMENT '擂主押金',
  `brok` double(10,2) unsigned DEFAULT '0.00' COMMENT '佣金',
  `has_unlimited` tinyint(1) unsigned DEFAULT '0' COMMENT '是否无限制',
  `bet_money` double(20,2) unsigned DEFAULT '0.00' COMMENT '投注总金额',
  `bet_number` int(11) unsigned DEFAULT '0' COMMENT '投注人数',
  `bet_total` text COMMENT '分项统计(json)',
  `min_bet` int(11) unsigned DEFAULT '0' COMMENT '投注下限',
  `max_bet` int(11) unsigned DEFAULT '0' COMMENT '投注上限',
  `risk` double(10,2) DEFAULT '0.00' COMMENT '风险值',
  `private` tinyint(4) unsigned DEFAULT NULL COMMENT '隐私设置',
  `invit_code` char(12) DEFAULT NULL COMMENT '邀请码',
  `win` double(20,2) DEFAULT '0.00' COMMENT '擂台盈亏',
  `win_brok` double(10,2) DEFAULT '0.00' COMMENT '擂台佣金',
  `win_target` varchar(500) DEFAULT NULL COMMENT '结果',
  `win_number` int(11) DEFAULT '0' COMMENT '赢的人数',
  `ret_credit_gold` double(20,2) DEFAULT '0.00' COMMENT '征信局收回本金',
  `status` tinyint(4) unsigned DEFAULT NULL COMMENT '擂台状态',
  `intro` varchar(500) DEFAULT NULL COMMENT '擂台宣传语',
  `has_default` tinyint(4) unsigned DEFAULT '0' COMMENT '默认擂台',
  `credit_bill_pic` text COMMENT '征信局账单图片',
  `auto_update_odds` tinyint(4) DEFAULT '0' COMMENT '是否自动更新赔率',
  `android_bet` int(11) DEFAULT '0' COMMENT '机器人最大总投注金额',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `mark` (`mark`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=466 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='擂台列表';

-- ----------------------------
-- Table structure for lt_arena_android
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_android`;
CREATE TABLE `lt_arena_android` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `arena_id` int(10) unsigned NOT NULL,
  `next_time` int(10) unsigned NOT NULL COMMENT '下一次运行时间',
  `condition` text NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `arena_id` (`arena_id`) USING BTREE,
  KEY `next_time` (`next_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=465 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='房间机器人';

-- ----------------------------
-- Table structure for lt_arena_bet_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_bet_detail`;
CREATE TABLE `lt_arena_bet_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `has_sys` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否是系统注量',
  `arena_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '投注用户',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '所属代理用户',
  `agent_sign` char(100) DEFAULT NULL COMMENT '代理标记',
  `agent_remark` char(100) DEFAULT NULL COMMENT '代理备注',
  `target` varchar(50) DEFAULT NULL COMMENT '投注对象',
  `item` varchar(50) DEFAULT NULL COMMENT '选项',
  `odds` varchar(10) DEFAULT NULL COMMENT '投注时的赔率',
  `handicap` varchar(10) DEFAULT NULL,
  `under` varchar(20) DEFAULT '0' COMMENT '大小，大',
  `over` varchar(20) DEFAULT '0' COMMENT '大小，小',
  `money` int(11) DEFAULT NULL COMMENT '投注金额',
  `win_money` double(20,2) DEFAULT '0.00' COMMENT '盈利金额(包含本金)',
  `brok` double(10,2) DEFAULT '0.00' COMMENT '佣金',
  `fee` double(10,2) unsigned DEFAULT NULL COMMENT '手续费',
  `buy` double(10,2) unsigned DEFAULT '0.00' COMMENT '查看价格',
  `buy_count` int(11) NOT NULL DEFAULT '0' COMMENT '查看人数',
  `buy_total` double DEFAULT '0' COMMENT '查看购买总金额',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '投注状态',
  `follow_user_id` int(11) DEFAULT '0' COMMENT '跟投的用户ID',
  `follow_bet_id` int(11) DEFAULT '0' COMMENT '跟投的投注id',
  `win_time` int(10) DEFAULT '0' COMMENT '开奖时间',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `order_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='擂台投注详情表';

-- ----------------------------
-- Table structure for lt_arena_bet_view
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_bet_view`;
CREATE TABLE `lt_arena_bet_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bet_id` int(11) DEFAULT NULL COMMENT '查看的投注id',
  `buy_user_id` int(11) DEFAULT NULL COMMENT '查看的用户id',
  `buy` double DEFAULT NULL COMMENT '查看费用',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='投注单查看购买';

-- ----------------------------
-- Table structure for lt_arena_credit
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_credit`;
CREATE TABLE `lt_arena_credit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mark` varchar(15) NOT NULL DEFAULT '0' COMMENT '用户唯一码',
  `arena_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '绑定的用户ID',
  `name` varchar(50) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `gold` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '征信额度',
  `avail_gold` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '可用额度',
  `code` varchar(6) NOT NULL DEFAULT '0' COMMENT '授权码',
  `target` text COMMENT '投注内容数据沉余',
  `win` double(20,2) DEFAULT '0.00' COMMENT '收益',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE,
  KEY `mark` (`mark`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='擂台征信用户列表';

-- ----------------------------
-- Table structure for lt_arena_deposit_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_deposit_detail`;
CREATE TABLE `lt_arena_deposit_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `has_sys` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是系统追加',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `arena_id` int(10) unsigned NOT NULL,
  `number` double(10,2) unsigned DEFAULT NULL COMMENT '押金金额',
  `create_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`has_sys`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=465 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='擂台押金详情表';

-- ----------------------------
-- Table structure for lt_arena_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_log`;
CREATE TABLE `lt_arena_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1-用户，2-系统',
  `arena_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL DEFAULT '0',
  `explain` varchar(500) NOT NULL,
  `data` text,
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1558 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='擂台日志';

-- ----------------------------
-- Table structure for lt_arena_odds
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_odds`;
CREATE TABLE `lt_arena_odds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `arena_id` int(10) unsigned DEFAULT NULL,
  `mark` varchar(50) DEFAULT NULL COMMENT '赔率标识',
  `odds` text COMMENT '赔率列表',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE,
  KEY `mark` (`mark`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=945 DEFAULT CHARSET=utf8 RROW_FORMAT=FIXED COMMENT='擂台赔率变更列表';

-- ----------------------------
-- Table structure for lt_arena_recommend
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_recommend`;
CREATE TABLE `lt_arena_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arena_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='后台擂台推荐';

-- ----------------------------
-- Table structure for lt_arena_target
-- ----------------------------
DROP TABLE IF EXISTS `lt_arena_target`;
CREATE TABLE `lt_arena_target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `arena_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rules_type` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `target` varchar(20) NOT NULL,
  `item` varchar(20) DEFAULT NULL,
  `target_name` varchar(255) DEFAULT NULL,
  `money` double(20,2) unsigned DEFAULT '0.00',
  `sys_money` double(20,2) unsigned DEFAULT '0.00' COMMENT '系统补投',
  `deposit` double(20,2) unsigned DEFAULT '0.00',
  `bonus` double(20,2) unsigned DEFAULT '0.00',
  `number` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `arena_id` (`arena_id`) USING BTREE,
  KEY `rules_type` (`rules_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1954 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='擂台投注项列表';

-- ----------------------------
-- Table structure for lt_common_menu
-- ----------------------------
DROP TABLE IF EXISTS `lt_common_menu`;
CREATE TABLE `lt_common_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `menu` text COMMENT '常用模块',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_config
-- ----------------------------
DROP TABLE IF EXISTS `lt_config`;
CREATE TABLE `lt_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `var` varchar(50) DEFAULT NULL COMMENT '字段名',
  `value` text COMMENT '字段值',
  `group_id` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统配置表';

-- ----------------------------
-- Table structure for lt_country
-- ----------------------------
DROP TABLE IF EXISTS `lt_country`;
CREATE TABLE `lt_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT '所属',
  `first` varchar(2) DEFAULT NULL COMMENT '名称首字母',
  `name` varchar(45) DEFAULT NULL COMMENT '中文名称',
  `ename` varchar(100) DEFAULT NULL COMMENT '英文名称',
  `logo` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='国家';

-- ----------------------------
-- Table structure for lt_crontab_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_crontab_log`;
CREATE TABLE `lt_crontab_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(500) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(10) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_curse_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_curse_log`;
CREATE TABLE `lt_curse_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `admin_id` int(11) unsigned NOT NULL,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '1-加，2-减',
  `create_time` int(11) unsigned NOT NULL,
  `update_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_gold_change_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_gold_change_log`;
CREATE TABLE `lt_gold_change_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '变动用户id',
  `before_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前',
  `after_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后',
  `explain` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作说明',
  `operation_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_help
-- ----------------------------
DROP TABLE IF EXISTS `lt_help`;
CREATE TABLE `lt_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `add_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1. 常见问题   2.消息',
  `type_id` int(11) DEFAULT '0' COMMENT '帮助信息/分类id',
  `anchor` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_help_type
-- ----------------------------
DROP TABLE IF EXISTS `lt_help_type`;
CREATE TABLE `lt_help_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_layout
-- ----------------------------
DROP TABLE IF EXISTS `lt_layout`;
CREATE TABLE `lt_layout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '模块名称',
  `type` tinyint(4) unsigned NOT NULL COMMENT '1-单项，2-双项，3-三项',
  `btime` int(11) unsigned NOT NULL COMMENT '开始时间',
  `etime` int(11) unsigned NOT NULL COMMENT '结束时间',
  `position` smallint(5) unsigned NOT NULL COMMENT '位置',
  `inv_img` varchar(250) NOT NULL COMMENT '倒影图片',
  `detail` text NOT NULL COMMENT '项目配置',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL,
  `update_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `btime` (`btime`) USING BTREE,
  KEY `etime` (`etime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='首页模块';

-- ----------------------------
-- Table structure for lt_layout_sports
-- ----------------------------
DROP TABLE IF EXISTS `lt_layout_sports`;
CREATE TABLE `lt_layout_sports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` smallint(6) NOT NULL,
  `is_hot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '热度',
  `name` varchar(50) NOT NULL COMMENT '模块名称',
  `type` varchar(10) NOT NULL COMMENT '模块类型',
  `detail` text NOT NULL COMMENT '项目配置',
  `sort` smallint(6) NOT NULL DEFAULT '999',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL,
  `update_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='竞技模块';

-- ----------------------------
-- Table structure for lt_manager
-- ----------------------------
DROP TABLE IF EXISTS `lt_manager`;
CREATE TABLE `lt_manager` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) DEFAULT '0',
  `username` varchar(50) DEFAULT '0',
  `password` varchar(50) DEFAULT '0',
  `salt` int(11) DEFAULT '0',
  `nickname` varchar(20) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态',
  `last_login_time` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `role_id` (`role_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='管理员表';

-- ----------------------------
-- Table structure for lt_manager_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_manager_log`;
CREATE TABLE `lt_manager_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1948 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='管理员登录日志';

-- ----------------------------
-- Table structure for lt_match
-- ----------------------------
DROP TABLE IF EXISTS `lt_match`;
CREATE TABLE `lt_match` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5_match` varchar(32) DEFAULT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL,
  `game_type` tinyint(4) DEFAULT NULL COMMENT '赛事类型，如足球、篮球等',
  `name` varchar(45) DEFAULT NULL COMMENT '赛事名称',
  `bgcolor` varchar(8) DEFAULT NULL COMMENT '背景颜色',
  `logo` varchar(250) DEFAULT NULL COMMENT '赛事logo',
  `logo_hover` varchar(250) DEFAULT NULL COMMENT '赛事选中logo',
  `begin_time` int(11) DEFAULT NULL COMMENT '开赛时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
  `explain` text COMMENT '说明',
  `address` varchar(250) DEFAULT NULL COMMENT '比赛地点',
  `alias` varchar(300) DEFAULT NULL COMMENT '赛事别名',
  `is_hot` tinyint(1) DEFAULT '0' COMMENT '是否是热门赛事',
  `is_recommend` tinyint(1) DEFAULT '0' COMMENT '是否是推荐赛事',
  `is_show` tinyint(1) DEFAULT '0' COMMENT '是否显示',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index3` (`game_type`) USING BTREE,
  KEY `fk_matchs_country_idx` (`country_id`) USING BTREE,
  KEY `game_id` (`game_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7963 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='赛事列表';

-- ----------------------------
-- Table structure for lt_match_recommend
-- ----------------------------
DROP TABLE IF EXISTS `lt_match_recommend`;
CREATE TABLE `lt_match_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) DEFAULT NULL COMMENT '赛事id',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_monitor_gold_win_lose_absolute
-- ----------------------------
DROP TABLE IF EXISTS `lt_monitor_gold_win_lose_absolute`;
CREATE TABLE `lt_monitor_gold_win_lose_absolute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `KindID` int(11) NOT NULL COMMENT '游戏ID',
  `DitchNumber` int(11) NOT NULL COMMENT '渠道编号',
  `ServerID` int(11) NOT NULL COMMENT '房间ID',
  `Absolute` bigint(20) NOT NULL COMMENT '报警绝对值',
  `Email` text COMMENT '报警邮件地址',
  `EmailStatus` tinyint(4) NOT NULL COMMENT '邮件开关',
  `Mobile` text COMMENT '报警短信手机',
  `MobileStatus` tinyint(4) NOT NULL COMMENT '短信开关',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `KindID` (`KindID`) USING BTREE,
  KEY `ServerID` (`ServerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='玩家输赢绝对值报警设置(房间金币报警)';

-- ----------------------------
-- Table structure for lt_monitor_stock
-- ----------------------------
DROP TABLE IF EXISTS `lt_monitor_stock`;
CREATE TABLE `lt_monitor_stock` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `KindID` int(11) NOT NULL COMMENT '游戏ID',
  `ServerID` int(11) NOT NULL COMMENT '房间ID',
  `Number` bigint(20) NOT NULL COMMENT '报警值',
  `Email` text COMMENT '报警邮件地址',
  `EmailStatus` tinyint(4) NOT NULL COMMENT '邮件开关',
  `Mobile` text COMMENT '报警短信手机',
  `MobileStatus` tinyint(4) NOT NULL COMMENT '短信开关',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `KindID` (`KindID`) USING BTREE,
  KEY `ServerID` (`ServerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='游戏库存报警设置';

-- ----------------------------
-- Table structure for lt_monitor_user_early
-- ----------------------------
DROP TABLE IF EXISTS `lt_monitor_user_early`;
CREATE TABLE `lt_monitor_user_early` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `KindID` int(11) NOT NULL COMMENT '游戏ID',
  `DitchNumber` int(11) NOT NULL COMMENT '渠道编号',
  `ServerID` int(11) NOT NULL COMMENT '房间ID',
  `Number` bigint(20) NOT NULL COMMENT '报警绝对值',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `KindID` (`KindID`) USING BTREE,
  KEY `ServerID` (`ServerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='观察者预警设置';

-- ----------------------------
-- Table structure for lt_notice
-- ----------------------------
DROP TABLE IF EXISTS `lt_notice`;
CREATE TABLE `lt_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '消息内容',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='消息列表';

-- ----------------------------
-- Table structure for lt_odds
-- ----------------------------
DROP TABLE IF EXISTS `lt_odds`;
CREATE TABLE `lt_odds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(100) DEFAULT NULL COMMENT 'md5',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `game_type` int(10) unsigned NOT NULL COMMENT '所属比赛类型',
  `play_id` int(10) unsigned NOT NULL COMMENT '比赛ID',
  `odds_company_id` int(10) unsigned NOT NULL COMMENT '赔率公司',
  `loop` int(10) DEFAULT '1' COMMENT '同一个玩法的顺序',
  `rules_type` mediumint(8) unsigned NOT NULL COMMENT '玩法类型',
  `rules_id` mediumint(8) unsigned DEFAULT '0' COMMENT '玩法d',
  `modify` tinyint(3) unsigned DEFAULT '1' COMMENT '是否更新，1为足彩网采集需更新，2为足彩网采集不更新，3为人工添加不更新',
  `odds` text COMMENT '赔率数据(json)',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `odds_type` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `game_type` (`game_type`) USING BTREE,
  KEY `play_id` (`play_id`) USING BTREE,
  KEY `odds_company_id` (`odds_company_id`) USING BTREE,
  KEY `md5` (`md5`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=841983 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='赔率表';

-- ----------------------------
-- Table structure for lt_odds_company
-- ----------------------------
DROP TABLE IF EXISTS `lt_odds_company`;
CREATE TABLE `lt_odds_company` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(200) DEFAULT NULL COMMENT '公司名称',
  `has_asia` tinyint(1) DEFAULT '0' COMMENT '是否开亚盘',
  `has_europe` tinyint(1) DEFAULT '0' COMMENT '是否开欧赔',
  `zc_id` int(10) DEFAULT NULL COMMENT '足彩网对应博彩公司id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='博彩公司';

-- ----------------------------
-- Table structure for lt_odds_company_game
-- ----------------------------
DROP TABLE IF EXISTS `lt_odds_company_game`;
CREATE TABLE `lt_odds_company_game` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `odds_company_id` int(10) unsigned NOT NULL,
  `game_type` tinyint(3) unsigned DEFAULT NULL COMMENT '关联类型，比如足球，篮球，电子竞技',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `odds_company_id` (`odds_company_id`) USING BTREE,
  KEY `play_game_id` (`game_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='博彩公司关联赛事类型';

-- ----------------------------
-- Table structure for lt_odds_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_odds_detail`;
CREATE TABLE `lt_odds_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `odds_id` int(10) unsigned NOT NULL,
  `odds` text NOT NULL COMMENT '赔率数据(json)',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_odds_list_detail_odds_list1_idx` (`odds_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15958882 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='赔率走势表';

-- ----------------------------
-- Table structure for lt_permit
-- ----------------------------
DROP TABLE IF EXISTS `lt_permit`;
CREATE TABLE `lt_permit` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `content` text NOT NULL COMMENT '权限点列表',
  `has_item` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='后台权限点';

-- ----------------------------
-- Table structure for lt_play
-- ----------------------------
DROP TABLE IF EXISTS `lt_play`;
CREATE TABLE `lt_play` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5_play` varchar(32) DEFAULT NULL COMMENT 'MD5(match_id+play_time+team_home_name+team_guest_name)',
  `has_manual` tinyint(3) unsigned DEFAULT '0' COMMENT '是否人工添加的比赛',
  `game_id` varchar(32) DEFAULT NULL,
  `game_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '比赛类型，足球、蓝球等',
  `match_id` int(10) unsigned NOT NULL COMMENT '所属赛事',
  `play_time` int(10) unsigned DEFAULT NULL COMMENT '比赛时间',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '比赛结束时间',
  `team_home_id` int(10) unsigned NOT NULL COMMENT '主场队',
  `team_home_name` varchar(100) DEFAULT NULL,
  `team_guest_id` int(10) unsigned NOT NULL COMMENT '客场队',
  `team_guest_name` varchar(100) DEFAULT NULL,
  `team_home_score` smallint(5) unsigned DEFAULT '0' COMMENT '主场得分',
  `team_guest_score` smallint(5) unsigned DEFAULT '0' COMMENT '客场得分',
  `team_home_half_score` tinyint(4) unsigned DEFAULT '0' COMMENT '主半场得分',
  `team_guest_half_score` tinyint(4) DEFAULT '0' COMMENT '客半场得分',
  `score_json` text COMMENT '比分详情',
  `home_yellow` tinyint(2) DEFAULT '0' COMMENT '主队黄牌数',
  `home_red` tinyint(2) DEFAULT '0' COMMENT '主队红牌数',
  `guest_yellow` tinyint(2) DEFAULT '0' COMMENT '客队黄牌数',
  `guest_red` tinyint(2) DEFAULT '0' COMMENT '客队红牌数',
  `total_prize` bigint(20) unsigned DEFAULT '0' COMMENT '总奖池',
  `min_deposit` int(10) unsigned DEFAULT '0' COMMENT '开擂最低保证金',
  `bo` tinyint(3) DEFAULT '0' COMMENT '回合数，1为一局一胜，3为三局两胜等',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '比赛状态，(未开始,中场休息,结束,延期,停赛,加时,点球等)',
  `has_statement` tinyint(1) unsigned DEFAULT '0' COMMENT '是否结算',
  `statement_user` int(10) unsigned DEFAULT '0' COMMENT '结算的后台用户id',
  `statement_time` int(10) unsigned DEFAULT '0' COMMENT '结算时间',
  `statement_status` tinyint(1) unsigned DEFAULT '0' COMMENT '结算状态，自动结算判断是否成功',
  `statement_status_text` varchar(255) DEFAULT '',
  `first_goals` tinyint(4) DEFAULT '0' COMMENT '最先进球队伍',
  `arena_total` smallint(5) unsigned DEFAULT '0' COMMENT '擂台数',
  `sys_arena_total` tinyint(3) unsigned DEFAULT '0' COMMENT '系统擂台数',
  `is_recommend` tinyint(4) DEFAULT '0' COMMENT '是否热门比赛',
  `create_time` int(10) unsigned DEFAULT '0',
  `update_time` int(10) unsigned DEFAULT '0',
  `has_sys_arena` tinyint(3) unsigned DEFAULT '0' COMMENT '是否有系统擂台',
  `has_odds` tinyint(1) unsigned DEFAULT '0' COMMENT '是否有赔率。1-有',
  `has_arena` tinyint(1) unsigned DEFAULT '0' COMMENT '是否有擂台，1-有',
  `has_play_rules` tinyint(1) DEFAULT '0' COMMENT '是否有玩法设置',
  `odds_key` varchar(500) DEFAULT NULL COMMENT '玩法选项',
  `live_type` tinyint(4) DEFAULT '0' COMMENT '直播类型，1-视频，2-文字',
  `live` varchar(500) DEFAULT '' COMMENT '比赛直播地址',
  `match_time` varchar(255) DEFAULT '' COMMENT '比赛进行的时长，只有正在比赛的需要使用',
  `hot` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1-冷门，2-普通，3-热门',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=91999 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛列表';

-- ----------------------------
-- Table structure for lt_play_dope
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_dope`;
CREATE TABLE `lt_play_dope` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `play_id` int(11) NOT NULL COMMENT '比赛id',
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '1' COMMENT '后台管理员ID',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='比赛预测';

-- ----------------------------
-- Table structure for lt_play_fenxi
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_fenxi`;
CREATE TABLE `lt_play_fenxi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `play_id` int(11) NOT NULL COMMENT '比赛',
  `content` text NOT NULL COMMENT '分析内容',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛分析表';

-- ----------------------------
-- Table structure for lt_play_history
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_history`;
CREATE TABLE `lt_play_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5_play` varchar(32) NOT NULL COMMENT 'md5(match_id+match_time+team_home_name+match_guest_name)',
  `game_type` tinyint(3) NOT NULL DEFAULT '1',
  `match_id` int(10) unsigned NOT NULL COMMENT '所属赛事',
  `play_time` int(10) unsigned DEFAULT NULL COMMENT '比赛时间',
  `team_home_id` int(10) unsigned NOT NULL COMMENT '主场队',
  `team_home_name` varchar(100) DEFAULT NULL,
  `team_guest_id` int(10) unsigned NOT NULL COMMENT '客场队',
  `team_guest_name` varchar(100) DEFAULT NULL,
  `team_home_score` smallint(5) unsigned DEFAULT '0' COMMENT '主场得分',
  `team_guest_score` smallint(5) unsigned DEFAULT '0' COMMENT '客场得分',
  `team_home_half_score` tinyint(4) unsigned DEFAULT '0' COMMENT '主半场得分',
  `team_guest_half_score` tinyint(4) DEFAULT '0' COMMENT '客半场得分',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '比赛状态，(未开始,中场休息,结束,延期,停赛,加时,点球等)',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_match_game_matchs1_idx` (`match_id`) USING BTREE,
  KEY `fk_match_game_teams1_idx` (`team_home_id`) USING BTREE,
  KEY `fk_match_game_teams2_idx` (`team_guest_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛列表';

-- ----------------------------
-- Table structure for lt_play_result
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_result`;
CREATE TABLE `lt_play_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `play_id` int(10) unsigned NOT NULL,
  `result` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `play_id` (`play_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=404 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛结果';

-- ----------------------------
-- Table structure for lt_play_rules
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_rules`;
CREATE TABLE `lt_play_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `play_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '比赛ID',
  `rules_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '玩法ID',
  `total_prize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总奖池',
  `arena_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '擂台总数',
  `arena_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '默认擂台',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '999' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `play_id_rules_id` (`play_id`,`rules_id`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛与玩法对应';

-- ----------------------------
-- Table structure for lt_play_rules_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_rules_detail`;
CREATE TABLE `lt_play_rules_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) NOT NULL,
  `game_type` int(11) NOT NULL DEFAULT '1' COMMENT '类型',
  `game_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '游戏id',
  `play_id` int(11) NOT NULL DEFAULT '0' COMMENT '比赛id',
  `rules_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩法id',
  `odds_id` int(11) NOT NULL DEFAULT '0',
  `rules_explain` text NOT NULL COMMENT '玩法选项',
  `create_time` int(10) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `md5` (`md5`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40663 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_play_team
-- ----------------------------
DROP TABLE IF EXISTS `lt_play_team`;
CREATE TABLE `lt_play_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `play_id` int(10) unsigned NOT NULL DEFAULT '0',
  `team_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '队伍ID',
  `score` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '全场比分',
  `half_score` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '半场比分',
  `first_score` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '最先得分',
  `red` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '红牌数',
  `yellow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '黄牌数',
  `score_json` text COMMENT '比赛得分冗余数据',
  `has_home` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否是主队',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `play_id` (`play_id`) USING BTREE,
  KEY `has_home` (`has_home`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=184071 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛队伍列表';

-- ----------------------------
-- Table structure for lt_queue
-- ----------------------------
DROP TABLE IF EXISTS `lt_queue`;
CREATE TABLE `lt_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mark` varchar(50) NOT NULL DEFAULT '0',
  `type` varchar(20) DEFAULT NULL,
  `data` text,
  `status` tinyint(4) DEFAULT '0',
  `result` text,
  `count` tinyint(4) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `mark` (`mark`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='任务队列';

-- ----------------------------
-- Table structure for lt_role
-- ----------------------------
DROP TABLE IF EXISTS `lt_role`;
CREATE TABLE `lt_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `limit` text,
  `other` text,
  `status` tinyint(4) DEFAULT '1',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_rules
-- ----------------------------
DROP TABLE IF EXISTS `lt_rules`;
CREATE TABLE `lt_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '系统默认，无法删除',
  `is_edit` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否可编辑选项',
  `is_single` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `has_customize` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏',
  `game_type` int(11) DEFAULT NULL COMMENT '项目,比如足球、蓝球、电竞',
  `type` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '玩法类型',
  `name` varchar(300) DEFAULT NULL COMMENT '玩法名称',
  `alias` varchar(300) DEFAULT NULL COMMENT '别名',
  `explain` text COMMENT '玩法选项',
  `intro` varchar(245) DEFAULT NULL COMMENT '简介',
  `help_intro` text COMMENT '玩法帮助说明',
  `sort` smallint(6) DEFAULT '999' COMMENT '排序',
  `min_deposit` int(11) DEFAULT '0' COMMENT '最低保证金',
  `explan` text COMMENT '说明或数据字段扩展',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态',
  `is_delete` tinyint(4) DEFAULT '0' COMMENT '是否删除',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `game_type` (`game_type`) USING BTREE,
  KEY `is_default` (`is_default`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `game_id` (`game_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛项目玩法';

-- ----------------------------
-- Table structure for lt_rules_item
-- ----------------------------
DROP TABLE IF EXISTS `lt_rules_item`;
CREATE TABLE `lt_rules_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rules_id` int(10) unsigned NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `min` smallint(6) DEFAULT NULL,
  `max` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `lt_rules_id` (`rules_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='比赛项目玩法选项';

-- ----------------------------
-- Table structure for lt_stat_arena_gold
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_arena_gold`;
CREATE TABLE `lt_stat_arena_gold` (
  `s_date` int(10) unsigned NOT NULL COMMENT '日期, 0点时间戳',
  `use_num` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '金币消耗量',
  `get_num` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '金币产出量',
  `total_user_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消耗用户数',
  UNIQUE KEY `s_date` (`s_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='擂台输赢统计';

-- ----------------------------
-- Table structure for lt_stat_online_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_online_detail`;
CREATE TABLE `lt_stat_online_detail` (
  `detail_id` bigint(16) unsigned NOT NULL AUTO_INCREMENT,
  `s_date` int(11) unsigned NOT NULL COMMENT '日期',
  `game_id` int(11) unsigned NOT NULL,
  `room_id` int(11) unsigned NOT NULL,
  `total` int(10) unsigned NOT NULL COMMENT '总在线用户数',
  `game_total` int(10) unsigned NOT NULL COMMENT '游戏在线用户数',
  PRIMARY KEY (`detail_id`) USING BTREE,
  UNIQUE KEY `s_date_game_id_room_id` (`s_date`,`game_id`,`room_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=851964 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户在线状态详情';

-- ----------------------------
-- Table structure for lt_stat_online_time
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_online_time`;
CREATE TABLE `lt_stat_online_time` (
  `s_date` int(11) unsigned NOT NULL COMMENT '日期',
  `game_id` int(10) NOT NULL COMMENT '游戏',
  `total_online_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总在线时长(单位:秒)',
  `total_user_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆账户数',
  UNIQUE KEY `s_date` (`s_date`,`game_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户主题 - 在线时长';

-- ----------------------------
-- Table structure for lt_stat_online_user
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_online_user`;
CREATE TABLE `lt_stat_online_user` (
  `s_date` int(10) unsigned NOT NULL COMMENT '时间(每天0点)',
  `game_id` int(10) NOT NULL COMMENT '游戏',
  `login_user_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总登录用户数',
  `max_online_num` int(10) unsigned NOT NULL COMMENT '平台最高在线人数',
  `min_online_num` int(10) unsigned DEFAULT NULL COMMENT '平台最低在线人数',
  `game_max_online_num` int(10) unsigned DEFAULT NULL COMMENT '游戏最高在线人数',
  `game_min_online_num` int(10) unsigned DEFAULT NULL COMMENT '游戏最低在线人数',
  UNIQUE KEY `s_date` (`s_date`,`game_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户主题 - 在线用户数';

-- ----------------------------
-- Table structure for lt_stat_online_user_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_online_user_detail`;
CREATE TABLE `lt_stat_online_user_detail` (
  `user_id` int(10) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL DEFAULT '0',
  `server_id` int(10) unsigned NOT NULL DEFAULT '0',
  `online_time` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `game_id` (`game_id`) USING BTREE,
  KEY `server_id` (`server_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='在线用户详情';

-- ----------------------------
-- Table structure for lt_stat_user_arena
-- ----------------------------
DROP TABLE IF EXISTS `lt_stat_user_arena`;
CREATE TABLE `lt_stat_user_arena` (
  `s_date` int(11) NOT NULL,
  `item_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目',
  `arena_total` int(11) NOT NULL DEFAULT '0' COMMENT '擂台数',
  `arena_win` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '擂台输赢',
  `arena_deposit` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '擂台押金',
  `arena_brok` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '擂台佣金',
  `arena_deposit_add` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '擂台押金追加',
  `bet_total` int(11) NOT NULL DEFAULT '0' COMMENT '投注注数',
  `bet_money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '总投注金额',
  `bet_win` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '投注总赢金额',
  `bet_lost` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '投注总输金额',
  `bet_bork` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '投注佣金',
  UNIQUE KEY `s_date_item_id` (`s_date`,`item_id`) USING BTREE,
  KEY `item_id` (`item_id`) USING BTREE,
  KEY `s_date` (`s_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='擂台输赢统计';

-- ----------------------------
-- Table structure for lt_system_income
-- ----------------------------
DROP TABLE IF EXISTS `lt_system_income`;
CREATE TABLE `lt_system_income` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '收支类型',
  `category` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1为佣金，2为保证金,3为擂台结算，4为开设擂台',
  `number` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `explain` varchar(200) NOT NULL COMMENT '说明',
  `data` text COMMENT '数据',
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=862 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统收益';

-- ----------------------------
-- Table structure for lt_system_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_system_log`;
CREATE TABLE `lt_system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `method` varchar(50) DEFAULT NULL COMMENT '访问类型',
  `classify` int(11) DEFAULT NULL COMMENT '统日志类型',
  `number` double(10,2) DEFAULT NULL COMMENT '金额',
  `number_type` tinyint(3) unsigned NOT NULL COMMENT '金额类型',
  `explain` varchar(200) DEFAULT NULL COMMENT '说明',
  `controller` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `data` text COMMENT '数据',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `controller` (`controller`) USING BTREE,
  KEY `action` (`action`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=64194 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='系统资金详情表';

-- ----------------------------
-- Table structure for lt_system_notice
-- ----------------------------
DROP TABLE IF EXISTS `lt_system_notice`;
CREATE TABLE `lt_system_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classify` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '公告类型',
  `ditch_classify` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '渠道分组',
  `title` varchar(250) DEFAULT NULL COMMENT '公告标题',
  `content` text COMMENT '公告内容',
  `btime` int(11) DEFAULT NULL COMMENT '开始时间',
  `etime` int(11) DEFAULT NULL COMMENT '结束时间',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`classify`) USING BTREE,
  KEY `ditch_classify` (`ditch_classify`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统公告';

-- ----------------------------
-- Table structure for lt_sys_message
-- ----------------------------
DROP TABLE IF EXISTS `lt_sys_message`;
CREATE TABLE `lt_sys_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '接收对象（1全部用户2指定用户3渠道）',
  `title` varchar(500) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `is_out` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否发送',
  `data` text,
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `creater` int(11) NOT NULL COMMENT '创建人',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `is_out` (`is_out`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22267 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_sys_message_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_sys_message_detail`;
CREATE TABLE `lt_sys_message_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读（0未读1已读）',
  `read_time` int(11) DEFAULT NULL COMMENT '阅读时间',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `message_id_user_id` (`message_id`,`user_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56207 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_sys_message_queue
-- ----------------------------
DROP TABLE IF EXISTS `lt_sys_message_queue`;
CREATE TABLE `lt_sys_message_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `opt` varchar(10) NOT NULL DEFAULT '0',
  `type` smallint(6) DEFAULT NULL COMMENT '队列类型',
  `value` double(20,2) DEFAULT NULL,
  `data` varchar(500) DEFAULT NULL COMMENT '队列数据',
  `status` tinyint(4) DEFAULT '0' COMMENT '队列状态，1-已处理',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `opt` (`opt`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=67642 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统消息队列';

-- ----------------------------
-- Table structure for lt_team
-- ----------------------------
DROP TABLE IF EXISTS `lt_team`;
CREATE TABLE `lt_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) DEFAULT NULL COMMENT 'md5(name),name去除空格且均为小写',
  `game_type` int(10) unsigned NOT NULL DEFAULT '1',
  `country_id` int(10) unsigned NOT NULL COMMENT '所属国家',
  `first` varchar(2) DEFAULT NULL COMMENT '名称首字母',
  `name` varchar(100) DEFAULT NULL COMMENT '队伍中文名称',
  `ename` varchar(200) DEFAULT NULL COMMENT '队伍英文名称',
  `found` varchar(15) DEFAULT NULL COMMENT '成立时间',
  `coach` varchar(100) DEFAULT NULL COMMENT '主教练',
  `website` varchar(100) DEFAULT NULL COMMENT '官网',
  `logo` varchar(200) DEFAULT NULL,
  `alias` varchar(300) DEFAULT NULL COMMENT '球队别名',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_teams_country1_idx` (`country_id`) USING BTREE,
  KEY `index3` (`name`) USING BTREE,
  KEY `index4` (`first`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31298 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='队伍列表';

-- ----------------------------
-- Table structure for lt_team_detail
-- ----------------------------
DROP TABLE IF EXISTS `lt_team_detail`;
CREATE TABLE `lt_team_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `name` varchar(300) NOT NULL,
  `nickname` varchar(300) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for lt_team_rank
-- ----------------------------
DROP TABLE IF EXISTS `lt_team_rank`;
CREATE TABLE `lt_team_rank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `match_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_season` tinyint(4) DEFAULT '0' COMMENT '上一季排名',
  `last_match_id` int(11) DEFAULT NULL,
  `last_match_name` varchar(255) DEFAULT NULL,
  `season` tinyint(4) DEFAULT '0' COMMENT '本季排名',
  `win_total` smallint(6) DEFAULT '0' COMMENT '胜',
  `sane` smallint(6) DEFAULT '0' COMMENT '平',
  `lose` smallint(6) DEFAULT '0' COMMENT '负',
  `score` smallint(6) DEFAULT '0' COMMENT '得分',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `fifa_season` int(11) DEFAULT NULL COMMENT 'fifa排名',
  `fifa_integral` int(11) DEFAULT NULL COMMENT 'fifa积分',
  PRIMARY KEY (`id`,`team_id`) USING BTREE,
  UNIQUE KEY `team_id` (`team_id`) USING BTREE,
  KEY `match_id` (`match_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9915 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='队伍排名';

-- ----------------------------
-- Table structure for lt_top_bonus
-- ----------------------------
DROP TABLE IF EXISTS `lt_top_bonus`;
CREATE TABLE `lt_top_bonus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT NULL COMMENT '榜单类型(日，周，1月，3月)1-昨日，2-一周，3-一月，4-三月，5-半年',
  `user_id` int(11) DEFAULT NULL,
  `user_nickname` varchar(20) DEFAULT NULL,
  `total` double(10,2) DEFAULT NULL,
  `sort` tinyint(2) DEFAULT NULL COMMENT '排序',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5933 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='TOP奖金榜';

-- ----------------------------
-- Table structure for lt_top_hit
-- ----------------------------
DROP TABLE IF EXISTS `lt_top_hit`;
CREATE TABLE `lt_top_hit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT NULL COMMENT '榜单类型(日，周，1月，3月)',
  `user_id` int(11) DEFAULT NULL,
  `user_nickname` varchar(20) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `sort` tinyint(2) DEFAULT NULL COMMENT '排序',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5933 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOP命中榜';

-- ----------------------------
-- Table structure for lt_top_leitai_win
-- ----------------------------
DROP TABLE IF EXISTS `lt_top_leitai_win`;
CREATE TABLE `lt_top_leitai_win` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT NULL COMMENT '榜单类型(日，周，1月，3月)',
  `user_id` int(11) DEFAULT NULL,
  `user_nickname` varchar(20) DEFAULT NULL,
  `total` double(11,2) DEFAULT NULL,
  `is_auto` tinyint(1) DEFAULT '1' COMMENT '1为自动统计，2为手动添加 ',
  `sort` tinyint(2) DEFAULT NULL COMMENT '排序',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='TOP擂台大神榜';

-- ----------------------------
-- Table structure for lt_user
-- ----------------------------
DROP TABLE IF EXISTS `lt_user`;
CREATE TABLE `lt_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `has_robot` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `has_online` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否在线',
  `has_bind` tinyint(1) unsigned DEFAULT '0' COMMENT '是否绑定账户',
  `has_homeowner` tinyint(1) unsigned DEFAULT '0',
  `imei` varchar(128) DEFAULT NULL COMMENT '设备唯一识别码',
  `uuid` varchar(50) DEFAULT NULL COMMENT '用户唯一识别码',
  `cpid` varchar(50) DEFAULT NULL COMMENT '界面展示给用户的唯一识别码',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `username` varchar(20) DEFAULT NULL,
  `nickname` varchar(20) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(250) DEFAULT NULL,
  `gold` double(20,2) unsigned DEFAULT '0.00' COMMENT '帐户金币',
  `nouseaccount` double(20,2) unsigned DEFAULT '0.00' COMMENT '流水',
  `reg_time` int(11) DEFAULT '0' COMMENT '注册时间',
  `reg_ip` varchar(15) DEFAULT NULL COMMENT '注册IP',
  `reg_platform` tinyint(4) DEFAULT '0' COMMENT '注册平台',
  `last_login_time` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `login_total` int(11) DEFAULT '0' COMMENT '登录次数',
  `last_login_ip` varchar(15) DEFAULT NULL COMMENT '最后登录ip',
  `location` varchar(50) DEFAULT NULL COMMENT '用户当前所在城市名称',
  `sex` tinyint(4) DEFAULT NULL COMMENT '用户性别，1表示男，2表示女',
  `deposit_money` bigint(20) unsigned DEFAULT '0' COMMENT '投注金额',
  `deposit_total` int(11) unsigned DEFAULT '0' COMMENT '投注次数',
  `arena_money` double(20,2) DEFAULT '0.00' COMMENT '擂台收效',
  `win_money` double(20,2) DEFAULT '0.00' COMMENT '中奖金额',
  `win_total` int(11) unsigned DEFAULT '0' COMMENT '中奖次数',
  `fans_num` int(11) unsigned DEFAULT '0' COMMENT '粉丝数量',
  `most_win` int(11) unsigned DEFAULT '0' COMMENT '最多连红',
  `bet_view_total` int(11) unsigned DEFAULT '0' COMMENT '投注单被查看次数',
  `follow_win` double(20,2) unsigned DEFAULT '0.00' COMMENT '推荐中奖金额',
  `profit` double(10,2) unsigned DEFAULT '0.00' COMMENT '盈利率',
  `status` tinyint(4) DEFAULT '1' COMMENT '帐户状态',
  `sys_message_total` smallint(6) unsigned DEFAULT '0' COMMENT '未读系统消息',
  `remarks` text COMMENT '用户备注',
  `device` varchar(10) DEFAULT NULL COMMENT '设备',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uuid` (`uuid`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `reg_time` (`reg_time`) USING BTREE,
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `cpid` (`cpid`) USING BTREE,
  KEY `imei` (`imei`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户表';

-- ----------------------------
-- Table structure for lt_user_follow
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_follow`;
CREATE TABLE `lt_user_follow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned DEFAULT '1' COMMENT '关注类型，1-用户，2-球队',
  `user_id` int(10) unsigned NOT NULL,
  `user_follow_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态，0-未审核，1-正常',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_user_friend_user1_idx` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户关注表';

-- ----------------------------
-- Table structure for lt_user_friend
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_friend`;
CREATE TABLE `lt_user_friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_friend_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态，0-未审核，1-正常,2-拒绝',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_user_friend_user1_idx` (`user_id`) USING BTREE,
  KEY `user_friend_id` (`user_friend_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for lt_user_funds_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_funds_log`;
CREATE TABLE `lt_user_funds_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `classify` int(11) DEFAULT NULL COMMENT '资金去向类型（投注，充值等）',
  `type` tinyint(3) unsigned NOT NULL COMMENT '金币或金钱(1金币，2金钱)',
  `number` double(20,2) DEFAULT NULL COMMENT '金额',
  `before_num` double(20,2) DEFAULT NULL COMMENT '操作前',
  `after_num` double(20,2) DEFAULT NULL COMMENT '操作后',
  `explain` varchar(200) DEFAULT NULL COMMENT '总金额去向说明',
  `data` text COMMENT '关联数据',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_user_funds_log_user1_idx` (`user_id`) USING BTREE,
  KEY `classify` (`classify`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=136871 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户资金详情表';

-- ----------------------------
-- Table structure for lt_user_level
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_level`;
CREATE TABLE `lt_user_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '等级名称',
  `min` int(11) NOT NULL DEFAULT '0' COMMENT '下限',
  `max` int(11) NOT NULL DEFAULT '0' COMMENT '上限',
  `lookbet` int(11) DEFAULT '0' COMMENT '查看价格',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户等级';

-- ----------------------------
-- Table structure for lt_user_log
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_log`;
CREATE TABLE `lt_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ditch_number` int(11) NOT NULL DEFAULT '0',
  `classify` tinyint(4) NOT NULL DEFAULT '0' COMMENT '日志类型',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `explain` varchar(500) DEFAULT NULL COMMENT '原因',
  `data` text,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `classify` (`classify`) USING BTREE,
  KEY `ditch_number` (`ditch_number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9870 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户日志';

-- ----------------------------
-- Table structure for lt_user_lost
-- ----------------------------
DROP TABLE IF EXISTS `lt_user_lost`;
CREATE TABLE `lt_user_lost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ditch` int(11) DEFAULT '0' COMMENT '渠道',
  `guid` varchar(50) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `guid_type` (`guid`,`type`) USING BTREE,
  UNIQUE KEY `ditch_guid_type` (`ditch`,`guid`,`type`) USING BTREE,
  KEY `guid` (`guid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户使用流失列表(APP有效)';
