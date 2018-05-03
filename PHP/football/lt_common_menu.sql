/*
Navicat MySQL Data Transfer

Source Server         : 本地测试服
Source Server Version : 50639
Source Host           : 192.168.188.189:3306
Source Database       : football

Target Server Type    : MYSQL
Target Server Version : 50639
File Encoding         : 65001

Date: 2018-05-03 20:15:16
*/

SET FOREIGN_KEY_CHECKS=0;

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
-- Records of lt_common_menu
-- ----------------------------

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
) ENGINE=MyISAM AUTO_INCREMENT=193 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统配置表';

-- ----------------------------
-- Records of lt_config
-- ----------------------------
INSERT INTO `lt_config` VALUES ('1', 'site_name', '51betty', '1');
INSERT INTO `lt_config` VALUES ('2', 'site_domain', 'http://www.51bet.com/', '1');
INSERT INTO `lt_config` VALUES ('3', 'site_source_domain', 'http://res.51bet.com/', '1');
INSERT INTO `lt_config` VALUES ('4', 'title', '51bet', '1');
INSERT INTO `lt_config` VALUES ('5', 'keywords', '51bet,足球', '1');
INSERT INTO `lt_config` VALUES ('6', 'description', '', '1');
INSERT INTO `lt_config` VALUES ('7', 'stat', '', '1');
INSERT INTO `lt_config` VALUES ('69', 'sys_gold', '1000', '1');
INSERT INTO `lt_config` VALUES ('70', 'sys_yuan', '1', '1');
INSERT INTO `lt_config` VALUES ('71', 'sys_brok', '5', '1');
INSERT INTO `lt_config` VALUES ('72', 'sys_rec_prefix', 'REC', '1');
INSERT INTO `lt_config` VALUES ('75', 'sys_min_deposit', '10000', '1');
INSERT INTO `lt_config` VALUES ('76', 'com', null, '1');
INSERT INTO `lt_config` VALUES ('77', 'deposit', '1000', '1');
INSERT INTO `lt_config` VALUES ('78', 'max_deposit', null, '1');
INSERT INTO `lt_config` VALUES ('79', 'target', '1', '1');
INSERT INTO `lt_config` VALUES ('85', 'auto_arena', '{\"com\":{\"asia\":[\"22\",\"2\",\"3\",\"4\",\"5\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"],\"europe\":[\"22\",\"2\",\"3\",\"4\",\"7\",\"8\",\"10\",\"12\",\"13\",\"14\",\"15\",\"16\",\"17\",\"18\",\"19\",\"20\",\"21\"]},\"has_auto\":1,\"deposit\":5000,\"max_deposit\":null,\"target\":1}', '1');
INSERT INTO `lt_config` VALUES ('86', 'sys_maker_brok', '10', '1');
INSERT INTO `lt_config` VALUES ('87', 'sys_player_brok', '1', '1');
INSERT INTO `lt_config` VALUES ('92', 'sys_arena_auto_statement', '1', '1');
INSERT INTO `lt_config` VALUES ('88', 'sys_max_arena_open_time', '5', '1');
INSERT INTO `lt_config` VALUES ('89', 'sys_max_arena_unsettled', '60', '1');
INSERT INTO `lt_config` VALUES ('91', 'sys_chip', '[10,50,100,500,1000]', '1');
INSERT INTO `lt_config` VALUES ('95', 'arena_auto_statement', '{\"1\":\"10\",\"99\":\"10\"}', '1');
INSERT INTO `lt_config` VALUES ('97', 'sys_freeze_home', '20000', '1');
INSERT INTO `lt_config` VALUES ('98', 'territory', '{\"domain_api\":\"http:\\/\\/api.football.test\",\"domain_socket\":\"192.168.188.172:8282\",\"domain_res\":\"res.pad.com\"}', '1');
INSERT INTO `lt_config` VALUES ('188', 'user_reg_word_validate', '0', '1');
INSERT INTO `lt_config` VALUES ('99', 'outside', '[]', '1');
INSERT INTO `lt_config` VALUES ('187', 'filter_word_faq_on', '0', '1');
INSERT INTO `lt_config` VALUES ('100', 'sys_homeowner_on', '0', '1');
INSERT INTO `lt_config` VALUES ('126', 'sys_user_min_bet_money', '10', '1');
INSERT INTO `lt_config` VALUES ('132', 'sys_arena_min_deposit', '{\"1\":\"30000\"}', '1');
INSERT INTO `lt_config` VALUES ('133', 'sys_arena_min_bet_money', '{\"1\":\"20\"}', '1');
INSERT INTO `lt_config` VALUES ('136', 'arena_android_limit', '{\"1\":{\"1\":{\"min\":\"20\",\"max\":\"8000\"},\"2\":{\"min\":\"20\",\"max\":\"12000\"},\"3\":{\"min\":\"20\",\"max\":\"20000\"}},\"99\":{\"1\":{\"1\":{\"min\":\"20\",\"max\":\"5000\"},\"2\":{\"min\":\"20\",\"max\":\"10000\"},\"3\":{\"min\":\"20\",\"max\":\"15000\"}},\"2\":{\"1\":{\"min\":\"20\",\"max\":\"5000\"},\"2\":{\"min\":\"20\",\"max\":\"10000\"},\"3\":{\"min\":\"20\",\"max\":\"15000\"}},\"3\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"4\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"5\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"6\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"7\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"8\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"9\":{\"1\":{\"min\":\"20\",\"max\":\"5000\"},\"2\":{\"min\":\"20\",\"max\":\"10000\"},\"3\":{\"min\":\"20\",\"max\":\"15000\"}}}}', '1');
INSERT INTO `lt_config` VALUES ('137', 'arena_android_gt_rand', '{\"1\":{\"1\":{\"min\":\"1\",\"max\":\"10\"},\"2\":{\"min\":\"1\",\"max\":\"10\"},\"3\":{\"min\":\"1\",\"max\":\"10\"}},\"99\":{\"1\":{\"1\":{\"min\":\"1\",\"max\":\"10\"},\"2\":{\"min\":\"1\",\"max\":\"10\"},\"3\":{\"min\":\"1\",\"max\":\"10\"}},\"2\":{\"1\":{\"min\":\"1\",\"max\":\"10\"},\"2\":{\"min\":\"1\",\"max\":\"10\"},\"3\":{\"min\":\"1\",\"max\":\"10\"}},\"3\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"4\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"5\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"6\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"7\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"8\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"9\":{\"1\":{\"min\":\"1\",\"max\":\"10\"},\"2\":{\"min\":\"1\",\"max\":\"10\"},\"3\":{\"min\":\"1\",\"max\":\"10\"}}}}', '1');
INSERT INTO `lt_config` VALUES ('138', 'arena_android_lt_rand', '{\"1\":{\"1\":{\"min\":\"1\",\"max\":\"5\"},\"2\":{\"min\":\"1\",\"max\":\"5\"},\"3\":{\"min\":\"1\",\"max\":\"5\"}},\"99\":{\"1\":{\"1\":{\"min\":\"1\",\"max\":\"5\"},\"2\":{\"min\":\"1\",\"max\":\"5\"},\"3\":{\"min\":\"1\",\"max\":\"5\"}},\"2\":{\"1\":{\"min\":\"1\",\"max\":\"5\"},\"2\":{\"min\":\"1\",\"max\":\"5\"},\"3\":{\"min\":\"1\",\"max\":\"5\"}},\"3\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"4\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"5\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"6\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"7\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"8\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"9\":{\"1\":{\"min\":\"1\",\"max\":\"5\"},\"2\":{\"min\":\"1\",\"max\":\"5\"},\"3\":{\"min\":\"1\",\"max\":\"5\"}}}}', '1');
INSERT INTO `lt_config` VALUES ('139', 'arena_android_bfb_rand', '{\"1\":{\"1\":{\"min\":\"90\",\"max\":\"110\"},\"2\":{\"min\":\"90\",\"max\":\"110\"},\"3\":{\"min\":\"90\",\"max\":\"110\"}},\"99\":{\"1\":{\"1\":{\"min\":\"90\",\"max\":\"110\"},\"2\":{\"min\":\"90\",\"max\":\"110\"},\"3\":{\"min\":\"90\",\"max\":\"110\"}},\"2\":{\"1\":{\"min\":\"90\",\"max\":\"110\"},\"2\":{\"min\":\"90\",\"max\":\"110\"},\"3\":{\"min\":\"90\",\"max\":\"110\"}},\"3\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"4\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"5\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"6\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"7\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"8\":{\"1\":{\"min\":\"0\",\"max\":\"0\"},\"2\":{\"min\":\"0\",\"max\":\"0\"},\"3\":{\"min\":\"0\",\"max\":\"0\"}},\"9\":{\"1\":{\"min\":\"90\",\"max\":\"110\"},\"2\":{\"min\":\"90\",\"max\":\"110\"},\"3\":{\"min\":\"90\",\"max\":\"110\"}}}}', '1');
INSERT INTO `lt_config` VALUES ('140', 'arena_android_on', '[\"1\",\"99\"]', '1');
INSERT INTO `lt_config` VALUES ('143', 'validate_code_auto_fill', '0', '1');
INSERT INTO `lt_config` VALUES ('145', 'user_guid_reg_warn_num', '3', '1');
INSERT INTO `lt_config` VALUES ('148', 'app_online_update_url_lb', 'http://h5.zdjhsm.com/table/index.html', '1');
INSERT INTO `lt_config` VALUES ('152', 'auto_seal', 'null', '1');
INSERT INTO `lt_config` VALUES ('157', 'agent_settled_on', '0', '1');
INSERT INTO `lt_config` VALUES ('176', 'system_report_on', '0', '1');
INSERT INTO `lt_config` VALUES ('177', 'system_report_on_weixin_account', '没鸡儿是我儿', '1');
INSERT INTO `lt_config` VALUES ('178', 'system_report_on_weixin_name', '没鸡儿是我儿', '1');
INSERT INTO `lt_config` VALUES ('189', 'user_login_word_validate', '0', '1');
INSERT INTO `lt_config` VALUES ('190', 'user_reg_mobile_change', '0', '1');
INSERT INTO `lt_config` VALUES ('191', 'user_new_guide', '0', '1');
INSERT INTO `lt_config` VALUES ('24', 'upload_upyun_username', '', '1');
INSERT INTO `lt_config` VALUES ('28', 'upload_upyun_timeout', '300', '1');
INSERT INTO `lt_config` VALUES ('23', 'upload_upyun_server', '', '1');
INSERT INTO `lt_config` VALUES ('25', 'upload_upyun_password', '', '1');
INSERT INTO `lt_config` VALUES ('26', 'upload_upyun_domain', '', '1');
INSERT INTO `lt_config` VALUES ('27', 'upload_upyun_bucket', '', '1');
INSERT INTO `lt_config` VALUES ('14', 'upload_type', 'local', '1');
INSERT INTO `lt_config` VALUES ('39', 'upload_size', '2048', '1');
INSERT INTO `lt_config` VALUES ('22', 'upload_qiniu_timeout', '300', '1');
INSERT INTO `lt_config` VALUES ('18', 'upload_qiniu_secretkey', '', '1');
INSERT INTO `lt_config` VALUES ('20', 'upload_qiniu_domain', '', '1');
INSERT INTO `lt_config` VALUES ('21', 'upload_qiniu_bucket', '', '1');
INSERT INTO `lt_config` VALUES ('19', 'upload_qiniu_accesskey', '', '1');
INSERT INTO `lt_config` VALUES ('17', 'upload_local_level', '5', '1');
INSERT INTO `lt_config` VALUES ('15', 'upload_local_domain', '/assets/attach/', '1');
INSERT INTO `lt_config` VALUES ('16', 'upload_local_bucket', 'assets/attach/', '1');
INSERT INTO `lt_config` VALUES ('31', 'upload_ftp_username', '', '1');
INSERT INTO `lt_config` VALUES ('36', 'upload_ftp_timeout', '300', '1');
INSERT INTO `lt_config` VALUES ('29', 'upload_ftp_server', '', '1');
INSERT INTO `lt_config` VALUES ('30', 'upload_ftp_port', '21', '1');
INSERT INTO `lt_config` VALUES ('32', 'upload_ftp_password', '', '1');
INSERT INTO `lt_config` VALUES ('35', 'upload_ftp_level', '1', '1');
INSERT INTO `lt_config` VALUES ('33', 'upload_ftp_domain', '', '1');
INSERT INTO `lt_config` VALUES ('34', 'upload_ftp_bucket', '/', '1');
INSERT INTO `lt_config` VALUES ('40', 'upload_exts', 'jpg|jpeg|gif|png', '1');
INSERT INTO `lt_config` VALUES ('37', 'upload_backstage_size', '10240', '1');
INSERT INTO `lt_config` VALUES ('38', 'upload_backstage_exts', 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|swf', '1');
INSERT INTO `lt_config` VALUES ('192', 'odds', '1', '1');

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
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='国家';

-- ----------------------------
-- Records of lt_country
-- ----------------------------
INSERT INTO `lt_country` VALUES ('1', '0', 'g', '国际', '', '', null, null);
INSERT INTO `lt_country` VALUES ('2', '0', 'o', '欧洲', '', '', null, null);
INSERT INTO `lt_country` VALUES ('3', '0', 'm', '美洲', '', '', null, null);
INSERT INTO `lt_country` VALUES ('4', '0', 'y', '亚洲', '', '', null, null);
INSERT INTO `lt_country` VALUES ('5', '0', 'f', '非洲', '', '', null, null);
INSERT INTO `lt_country` VALUES ('6', '0', 'd', '大洋洲', '', '', null, null);
INSERT INTO `lt_country` VALUES ('7', '2', '', '英格兰', '', 'match_logo/7971470104495410.jpg', null, null);
INSERT INTO `lt_country` VALUES ('8', '2', '', '意大利', '', 'match_logo/4751470104495598.jpg', null, null);
INSERT INTO `lt_country` VALUES ('9', '2', '', '西班牙', '', 'match_logo/8851470104495762.jpg', null, null);
INSERT INTO `lt_country` VALUES ('10', '2', '', '德国', '', 'match_logo/5891470104495770.gif', null, null);
INSERT INTO `lt_country` VALUES ('11', '2', '', '法国', '', 'match_logo/7101470104496464.jpg', null, null);
INSERT INTO `lt_country` VALUES ('12', '2', '', '葡萄牙', '', 'match_logo/2031470104496334.jpg', null, null);
INSERT INTO `lt_country` VALUES ('13', '2', '', '苏格兰', '', 'match_logo/1331470104496390.jpg', null, null);
INSERT INTO `lt_country` VALUES ('14', '2', '', '荷兰', '', 'match_logo/7501470104496840.jpg', null, null);
INSERT INTO `lt_country` VALUES ('15', '2', '', '比利时', '', 'match_logo/3631470104496962.jpg', null, null);
INSERT INTO `lt_country` VALUES ('16', '2', '', '瑞典', '', 'match_logo/5151470104496682.jpg', null, null);
INSERT INTO `lt_country` VALUES ('17', '2', '', '芬兰', '', 'match_logo/3591470104496350.jpg', null, null);
INSERT INTO `lt_country` VALUES ('18', '2', '', '挪威', '', 'match_logo/9321470104497509.jpg', null, null);
INSERT INTO `lt_country` VALUES ('19', '2', '', '丹麦', '', 'match_logo/4301470104497777.jpg', null, null);
INSERT INTO `lt_country` VALUES ('20', '2', '', '奥地利', '', 'match_logo/2851470104497517.jpg', null, null);
INSERT INTO `lt_country` VALUES ('21', '2', '', '瑞士', '', 'match_logo/4371470104497415.jpg', null, null);
INSERT INTO `lt_country` VALUES ('22', '2', '', '爱尔兰', '', 'match_logo/9101470104497453.gif', null, null);
INSERT INTO `lt_country` VALUES ('23', '2', '', '北爱尔兰', '', 'match_logo/6881470104497585.gif', null, null);
INSERT INTO `lt_country` VALUES ('24', '2', '', '俄罗斯', '', 'match_logo/1891470104497513.jpg', null, null);
INSERT INTO `lt_country` VALUES ('25', '2', '', '波兰', '', 'match_logo/1421470104497360.jpg', null, null);
INSERT INTO `lt_country` VALUES ('26', '2', '', '乌克兰', '', 'match_logo/4581470104498445.jpg', null, null);
INSERT INTO `lt_country` VALUES ('27', '2', '', '捷克', '', 'match_logo/9101470104498161.jpg', null, null);
INSERT INTO `lt_country` VALUES ('28', '2', '', '希腊', '', 'match_logo/9021470104498446.jpg', null, null);
INSERT INTO `lt_country` VALUES ('29', '2', '', '罗马尼亚', '', 'match_logo/1501470104498861.jpg', null, null);
INSERT INTO `lt_country` VALUES ('30', '2', '', '斯洛伐克', '', 'match_logo/2531470104498262.bmp', null, null);
INSERT INTO `lt_country` VALUES ('31', '2', '', '冰岛', '', 'match_logo/8721470104498180.jpg', null, null);
INSERT INTO `lt_country` VALUES ('32', '2', '', '白俄罗斯', '', 'match_logo/3981470104498991.jpg', null, null);
INSERT INTO `lt_country` VALUES ('33', '2', '', '威尔士', '', 'match_logo/2351470104499792.bmp', null, null);
INSERT INTO `lt_country` VALUES ('34', '2', '', '匈牙利', '', 'match_logo/9701470104499578.jpg', null, null);
INSERT INTO `lt_country` VALUES ('35', '2', '', '土耳其', '', 'match_logo/3491470104499417.jpg', null, null);
INSERT INTO `lt_country` VALUES ('36', '2', '', '克罗地亚', '', 'match_logo/2531470104499122.jpg', null, null);
INSERT INTO `lt_country` VALUES ('37', '2', '', '保加利亚', '', 'match_logo/1721470104499829.gif', null, null);
INSERT INTO `lt_country` VALUES ('38', '2', '', '斯洛文尼亚', '', 'match_logo/5821470104499269.bmp', null, null);
INSERT INTO `lt_country` VALUES ('39', '2', '', '塞浦路斯', '', 'match_logo/9151470104499647.bmp', null, null);
INSERT INTO `lt_country` VALUES ('40', '2', '', '塞尔维亚', '', 'match_logo/2411470104500315.jpg', null, null);
INSERT INTO `lt_country` VALUES ('41', '2', '', '以色列', '', 'match_logo/6371470104500746.jpg', null, null);
INSERT INTO `lt_country` VALUES ('42', '2', '', '波黑', '', 'match_logo/6651470104500909.jpg', null, null);
INSERT INTO `lt_country` VALUES ('43', '2', '', '爱沙尼亚', '', 'match_logo/5471470104500645.gif', null, null);
INSERT INTO `lt_country` VALUES ('44', '2', '', '法罗群岛', '', 'match_logo/1381470104500445.jpg', null, null);
INSERT INTO `lt_country` VALUES ('46', '3', '', '阿根廷', '', 'match_logo/9941470104501472.jpg', null, null);
INSERT INTO `lt_country` VALUES ('47', '3', '', '巴西', '', 'match_logo/8181470104501186.jpg', null, null);
INSERT INTO `lt_country` VALUES ('48', '3', '', '乌拉圭', '', 'match_logo/6191470104501287.jpg', null, null);
INSERT INTO `lt_country` VALUES ('49', '3', '', '美国', '', 'match_logo/4481470104501221.jpg', null, null);
INSERT INTO `lt_country` VALUES ('50', '3', '', '智利', '', 'match_logo/4451470104501535.jpg', null, null);
INSERT INTO `lt_country` VALUES ('51', '3', '', '墨西哥', '', 'match_logo/6041470104502845.jpg', null, null);
INSERT INTO `lt_country` VALUES ('52', '3', '', '哥伦比亚', '', 'match_logo/5581470104502515.jpg', null, null);
INSERT INTO `lt_country` VALUES ('53', '3', '', '巴拉圭', '', 'match_logo/2451470104502230.jpg', null, null);
INSERT INTO `lt_country` VALUES ('54', '3', '', '加拿大', '', 'match_logo/5911470104503873.jpg', null, null);
INSERT INTO `lt_country` VALUES ('55', '3', '', '玻利维亚', '', 'match_logo/5801470104503597.gif', null, null);
INSERT INTO `lt_country` VALUES ('56', '3', '', '厄瓜多尔', '', 'match_logo/6301470104503905.jpg', null, null);
INSERT INTO `lt_country` VALUES ('58', '3', '', '危地马拉', '', 'match_logo/3091470104504359.jpg', null, null);
INSERT INTO `lt_country` VALUES ('59', '3', '', '哥斯达黎加', '', 'match_logo/5211470104504117.jpg', null, null);
INSERT INTO `lt_country` VALUES ('60', '3', '', '萨尔瓦多', '', 'match_logo/6121470104504332.jpg', null, null);
INSERT INTO `lt_country` VALUES ('61', '4', '', '中国', '', 'match_logo/2961470104505564.jpg', null, null);
INSERT INTO `lt_country` VALUES ('62', '4', '', '日本', '', 'match_logo/2731470104505570.jpg', null, null);
INSERT INTO `lt_country` VALUES ('63', '4', '', '韩国', '', 'match_logo/2021470104505980.jpg', null, null);
INSERT INTO `lt_country` VALUES ('64', '4', '', '澳大利亚', '', 'match_logo/2771470104505370.jpg', null, null);
INSERT INTO `lt_country` VALUES ('65', '4', '', '伊朗', '', 'match_logo/1001470104505439.jpg', null, null);
INSERT INTO `lt_country` VALUES ('66', '4', '', '沙特阿拉伯', '', 'match_logo/2581470104505380.gif', null, null);
INSERT INTO `lt_country` VALUES ('67', '4', '', '阿联酋', '', 'match_logo/2981470104505261.gif', null, null);
INSERT INTO `lt_country` VALUES ('68', '4', '', '黎巴嫩', '', 'match_logo/3001470104509795.jpg', null, null);
INSERT INTO `lt_country` VALUES ('69', '4', '', '科威特', '', 'match_logo/6561470104509419.jpg', null, null);
INSERT INTO `lt_country` VALUES ('70', '4', '', '卡塔尔', '', 'match_logo/9391470104509464.jpg', null, null);
INSERT INTO `lt_country` VALUES ('71', '4', '', '阿曼', '', 'match_logo/5831470104509435.jpg', null, null);
INSERT INTO `lt_country` VALUES ('72', '4', '', '约旦', '', 'match_logo/4571470104509482.gif', null, null);
INSERT INTO `lt_country` VALUES ('73', '4', '', '越南', '', 'match_logo/8391470104509280.jpg', null, null);
INSERT INTO `lt_country` VALUES ('74', '4', '', '乌兹别克', '', 'match_logo/2911470104509597.jpg', null, null);
INSERT INTO `lt_country` VALUES ('75', '4', '', '印度尼西亚', '', 'match_logo/8331470104510376.jpg', null, null);
INSERT INTO `lt_country` VALUES ('76', '5', '', '阿尔及利亚', '', 'match_logo/5231470104510106.jpg', null, null);
INSERT INTO `lt_country` VALUES ('77', '5', '', '埃及', '', 'match_logo/4251470104510696.gif', null, null);
INSERT INTO `lt_country` VALUES ('78', '5', '', '摩洛哥', '', 'match_logo/7891470104510555.jpg', null, null);
INSERT INTO `lt_country` VALUES ('79', '5', '', '突尼斯', '', 'match_logo/8231470104510761.jpg', null, null);
INSERT INTO `lt_country` VALUES ('80', '5', '', '南非', '', 'match_logo/2721470104510341.jpg', null, null);
INSERT INTO `lt_country` VALUES ('81', '5', 'l', '利比亚', '', 'match_logo/9881470104511543.jpg', null, null);
INSERT INTO `lt_country` VALUES ('82', '5', 'n', '尼日利亚', '', '', null, null);

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
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理员表';

-- ----------------------------
-- Records of lt_manager
-- ----------------------------
INSERT INTO `lt_manager` VALUES ('1', '-1', 'admin', 'ba8717d5e1aceb0a50d9c80bcdfe7ad9', '123456', '管理员大哥', '05/b5/d0/e0/4e/04fb1b120a0f40cacd3f34.jpg', '1', '1525272911', '1471053873', '1520515199');

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
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台权限点';

-- ----------------------------
-- Records of lt_permit
-- ----------------------------
INSERT INTO `lt_permit` VALUES ('16', '0', '系统', '', '0', '1470985619', '1470988615');
INSERT INTO `lt_permit` VALUES ('17', '16', '系统管理', '[{\"name\":\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"controller\":\"config\",\"action\":\"basic\"},{\"name\":\"\\u90ae\\u7bb1\\u8bbe\\u7f6e\",\"controller\":\"config\",\"action\":\"mail\"},{\"name\":\"\\u9ad8\\u7ea7\\u8bbe\\u7f6e\",\"controller\":\"config\",\"action\":\"system\"},{\"name\":\"\\u7cfb\\u7edf\\u81ea\\u52a8\\u6446\\u64c2\",\"controller\":\"config\",\"action\":\"auto\"},{\"name\":\"\\u8bf7\\u6c42\\u57df\\u540d\",\"controller\":\"config\",\"action\":\"domain\"},{\"name\":\"\\u5145\\u503c\",\"controller\":\"config\",\"action\":\"recharge\"},{\"name\":\"\\u63d0\\u73b0\",\"controller\":\"config\",\"action\":\"withdrawal\"},{\"name\":\"\\u8d60\\u9001\",\"controller\":\"config\",\"action\":\"gift\"},{\"name\":\"\\u9650\\u5236\\u8bcd\\u8bed\",\"controller\":\"config\",\"action\":\"words\"},{\"name\":\"\\u7740\\u9646\\u9875\",\"controller\":\"config\",\"action\":\"app_client,app_client_add,app_client_del\"},{\"name\":\"\\u623f\\u95f4\\u81ea\\u52a8\\u7ed3\\u7b97\",\"controller\":\"config\",\"action\":\"arena_auto\"},{\"name\":\"\\u5206\\u4eab\\u8bbe\\u7f6e\",\"controller\":\"config\",\"action\":\"shareindex,shareadd,sharedel\"},{\"name\":\"\\u623f\\u95f4\\u673a\\u5668\\u4eba\",\"controller\":\"config\",\"action\":\"arena_android\"}]', '0', '1470985633', '1524713461');
INSERT INTO `lt_permit` VALUES ('83', '16', '系统任务', '[{\"name\":\"\\u4efb\\u52a1\\u5217\\u8868\",\"controller\":\"task\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u4fee\\u6539\\u3001\\u5220\\u9664\",\"controller\":\"task\",\"action\":\"add,del\"}]', '0', '1493260661', '1493260706');
INSERT INTO `lt_permit` VALUES ('19', '16', '后台权限点', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"permit\",\"action\":\"index\"},{\"name\":\" \\u6dfb\\u52a0\\/\\u4fee\\u6539\\/\\u5220\\u9664\",\"controller\":\"permit\",\"action\":\"add,del\"},{\"name\":\"\\u6743\\u9650\\u70b9\\u66f4\\u65b0\",\"controller\":\"permit\",\"action\":\"point\"}]', '0', '1470993263', '1493800388');
INSERT INTO `lt_permit` VALUES ('22', '0', '会员', '', '0', '1470993875', '1470993875');
INSERT INTO `lt_permit` VALUES ('23', '22', '会员管理', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"user\",\"action\":\"index\"},{\"name\":\"\\u5c01\\u7981\\/\\u89e3\\u5c01\",\"controller\":\"user\",\"action\":\"sealuser\"},{\"name\":\"\\u91cd\\u7f6e\\u5bc6\\u7801\",\"controller\":\"user\",\"action\":\"changepass\"},{\"name\":\"\\u7528\\u6237\\u65e5\\u5fd7\",\"controller\":\"user\",\"action\":\"userlog\"},{\"name\":\"\\u5145\\u503c\",\"controller\":\"user\",\"action\":\"userpay\"},{\"name\":\"\\u6263\\u6b3e\",\"controller\":\"user\",\"action\":\"offuserpay\"},{\"name\":\"\\u5e10\\u6237\\u660e\\u7ec6\",\"controller\":\"user\",\"action\":\"memberlog,memberlog_sys\"},{\"name\":\"\\u67e5\\u770b\\u6295\\u6ce8\",\"controller\":\"user\",\"action\":\"userarenabet\"},{\"name\":\"\\u91cd\\u7f6e\\u91d1\\u5e93\\u5bc6\\u7801\",\"controller\":\"user\",\"action\":\"changebankpass\"},{\"name\":\"\\u8d26\\u6237\\u7ed1\\u5b9a\\u7ba1\\u7406\",\"controller\":\"user\",\"action\":\"bind,unbind,bind_account\"},{\"name\":\"\\u7ed1\\u5b9a\\u624b\\u673a\",\"controller\":\"user\",\"action\":\"bind_mobile\"},{\"name\":\"\\u8bbe\\u7f6e\\u5907\\u6ce8\",\"controller\":\"user\",\"action\":\"modify_remarks\"},{\"name\":\"\\u7528\\u6237\\u8be6\\u60c5\",\"controller\":\"user\",\"action\":\"info\"},{\"name\":\"\\u767b\\u5f55\\u8bb0\\u5f55\",\"controller\":\"user\",\"action\":\"login_log\"},{\"name\":\"\\u64cd\\u4f5c\\u8bb0\\u5f55\",\"controller\":\"user\",\"action\":\"opt_log\"},{\"name\":\"\\u5bc6\\u7801\\u4fee\\u6539\\u8bb0\\u5f55\",\"controller\":\"user\",\"action\":\"modify_password_log\"},{\"name\":\"\\u7279\\u6b8a\\u7528\\u6237\\u8bbe\\u7f6e\",\"controller\":\"user\",\"action\":\"set_common\"},{\"name\":\"\\u4fee\\u6539\\u6635\\u79f0\",\"controller\":\"user\",\"action\":\"modifynickname\"},{\"name\":\"\\u5728\\u7ebf\\u4f1a\\u5458\",\"controller\":\"user\",\"action\":\"onlineuser\"},{\"name\":\"\\u5c01\\u7981\\u7528\\u6237\\uff08IP\\/GUID\\uff08\\u5168\\u90e8\\uff09)\",\"controller\":\"user\",\"action\":\"sealuserall,same_account\"}]', '0', '1470993886', '1502717970');
INSERT INTO `lt_permit` VALUES ('37', '16', '角色权限', '[{\"name\":\"\\u89d2\\u8272\\u5217\\u8868\",\"controller\":\"role\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\/\\u7f16\\u8f91\\u89d2\\u8272\",\"controller\":\"role\",\"action\":\"add\"},{\"name\":\"\\u5220\\u9664\\u89d2\\u8272\",\"controller\":\"role\",\"action\":\"del\"},{\"name\":\"\\u7ba1\\u7406\\u5458\\u5217\\u8868\",\"controller\":\"manager\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\/\\u7f16\\u8f91\\u7ba1\\u7406\\u5458\",\"controller\":\"manager\",\"action\":\"add\"},{\"name\":\"\\u5220\\u9664\\u7ba1\\u7406\\u5458\",\"controller\":\"manager\",\"action\":\"del\"}]', '0', '1471056691', '1471056829');
INSERT INTO `lt_permit` VALUES ('24', '16', '消息、公告', '[{\"name\":\"\\u6d88\\u606f\\u5217\\u8868\",\"controller\":\"message\",\"action\":\"index\"},{\"name\":\"\\u6d88\\u606f(\\u6dfb\\u52a0\\/\\u4fee\\u6539\\/\\u5220\\u9664)\",\"controller\":\"message\",\"action\":\"addmessage,deletemessage\"},{\"name\":\"\\u516c\\u544a\\u5217\\u8868\",\"controller\":\"notice\",\"action\":\"index\"},{\"name\":\"\\u516c\\u544a(\\u6dfb\\u52a0\\/\\u4fee\\u6539\\/\\u5220\\u9664)\",\"controller\":\"notice\",\"action\":\"add,del\"}]', '0', '1470994508', '1493280105');
INSERT INTO `lt_permit` VALUES ('25', '0', '内容', '', '0', '1470994619', '1470994619');
INSERT INTO `lt_permit` VALUES ('26', '25', '自定义代码', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"code\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\/\\u7f16\\u8f91\\/\\u5220\\u9664\",\"controller\":\"code\",\"action\":\"addcode,deletecode\"}]', '0', '1470994644', '1471056925');
INSERT INTO `lt_permit` VALUES ('27', '25', '编辑推荐', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"recommend\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\/\\u4fee\\u6539\\/\\u5220\\u9664\",\"controller\":\"recommend\",\"action\":\"addrecommend,deleterecommend\"}]', '0', '1470994831', '1471056930');
INSERT INTO `lt_permit` VALUES ('28', '25', '国家管理', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"country\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\/\\u4fee\\u6539\",\"controller\":\"country\",\"action\":\"addcountry\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"country\",\"action\":\"delcountry\"}]', '0', '1470994909', '1493287111');
INSERT INTO `lt_permit` VALUES ('29', '25', '圈子管理', '[{\"name\":\"\\u521b\\u5efa\\u3001\\u4fee\\u6539\\u3001\\u5220\\u9664\\u5708\\u5b50\",\"controller\":\"forum\",\"action\":\"addforum,deleteforum\"},{\"name\":\"\\u5217\\u8868\",\"controller\":\"forum\",\"action\":\"index\"},{\"name\":\"\\u516c\\u544a\\u5217\\u8868\",\"controller\":\"forum\",\"action\":\"message\"},{\"name\":\"\\u53d1\\u5e03\\u3001\\u7f16\\u8f91\\u516c\\u544a\",\"controller\":\"forum\",\"action\":\"addmessage\"},{\"name\":\"\\u5220\\u9664\\u516c\\u544a\",\"controller\":\"forum\",\"action\":\"deletemessage\"}]', '0', '1470994987', '1493289955');
INSERT INTO `lt_permit` VALUES ('95', '88', '游戏管理', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"game\"},{\"name\":\"\\u7f16\\u8f91\",\"controller\":\"items\",\"action\":\"game_add\"}]', '0', '1493262704', '1493263743');
INSERT INTO `lt_permit` VALUES ('93', '88', '赛事管理', '[{\"name\":\"\\u8d5b\\u4e8b\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"match\"},{\"name\":\"\\u8d5b\\u4e8b\\u7f16\\u8f91\",\"controller\":\"items\",\"action\":\"match_add\"}]', '0', '1493262614', '1493263723');
INSERT INTO `lt_permit` VALUES ('94', '88', '球队管理', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"teams\"},{\"name\":\"\\u7f16\\u8f91\",\"controller\":\"items\",\"action\":\"teams_add\"}]', '0', '1493262631', '1493263736');
INSERT INTO `lt_permit` VALUES ('91', '88', '投注', '[{\"name\":\"\\u6295\\u6ce8\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"betting_list\"}]', '0', '1493262334', '1493263705');
INSERT INTO `lt_permit` VALUES ('92', '88', '玩法', '[{\"name\":\"\\u73a9\\u6cd5\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"rules\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u4fee\\u6539\",\"controller\":\"items\",\"action\":\"rules_add\"}]', '0', '1493262362', '1493263715');
INSERT INTO `lt_permit` VALUES ('90', '88', '擂台', '[{\"name\":\"\\u64c2\\u53f0\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"arena_list\"},{\"name\":\"\\u8ffd\\u52a0\\u4fdd\\u8bc1\\u91d1\",\"controller\":\"items\",\"action\":\"arena_deposit\"},{\"name\":\"\\u64c2\\u53f0\\u8bbe\\u7f6e\",\"controller\":\"items\",\"action\":\"arena_conf\"},{\"name\":\"\\u64c2\\u53f0\\u6295\\u6ce8\\u9879\\u7edf\\u8ba1\",\"controller\":\"items\",\"action\":\"arena_bet_stat\"},{\"name\":\"\\u7cfb\\u7edf\\u8865\\u6ce8\",\"controller\":\"items\",\"action\":\"arena_sys_bet\"},{\"name\":\"\\u64c2\\u53f0\\u6295\\u6ce8\\u7528\\u6237\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"arena_bet_user\"},{\"name\":\"\\u64c2\\u53f0\\u5c01\\u64c2\",\"controller\":\"items\",\"action\":\"sealarena\"},{\"name\":\"\\u64c2\\u53f0\\u89e3\\u5c01\",\"controller\":\"items\",\"action\":\"unsealarena\"},{\"name\":\"\\u64c2\\u53f0\\u53d6\\u6d88\",\"controller\":\"items\",\"action\":\"arena_disabled,batch_opt\"},{\"name\":\"\\u64c2\\u53f0\\u5220\\u9664\",\"controller\":\"items\",\"action\":\"arena_del,batch_opt\"},{\"name\":\"\\u8bbe\\u4e3a\\u9ed8\\u8ba4\\u64c2\\u53f0\",\"controller\":\"items\",\"action\":\"rdef\"},{\"name\":\"\\u63a8\\u8350\\u64c2\\u53f0\",\"controller\":\"items\",\"action\":\"arena_recommend\"},{\"name\":\"\\u53d6\\u6d88\\u63a8\\u8350\\u64c2\\u53f0\",\"controller\":\"items\",\"action\":\"un_arena_recommend\"},{\"name\":\"\\u8d54\\u7387\\u76d1\\u63a7\",\"controller\":\"items\",\"action\":\"odds_monitor\"},{\"name\":\"\\u623f\\u95f4\\u8be6\\u60c5\",\"controller\":\"items\",\"action\":\"arena_info\"}]', '0', '1493262085', '1501678844');
INSERT INTO `lt_permit` VALUES ('88', '0', '项目（统一）', '', '1', '1493261470', '1493263633');
INSERT INTO `lt_permit` VALUES ('89', '88', '比赛', '[{\"name\":\"\\u6bd4\\u8d5b\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"play\"},{\"name\":\"\\u8d54\\u7387\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"odds\"},{\"name\":\"\\u8d54\\u7387\\u6dfb\\u52a0\\u3001\\u66f4\\u65b0\",\"controller\":\"items\",\"action\":\"odds_add\"},{\"name\":\"\\u6446\\u64c2\",\"controller\":\"items\",\"action\":\"arena_publish\"},{\"name\":\"\\u6bd4\\u8d5b\\u63a8\\u8350\\u3001\\u53d6\\u6d88\\u63a8\\u8350\",\"controller\":\"items\",\"action\":\"recommend\"},{\"name\":\"\\u73a9\\u6cd5\",\"controller\":\"items\",\"action\":\"play_rule\"},{\"name\":\"\\u76f4\\u64ad\",\"controller\":\"items\",\"action\":\"play_live,batch_play_live\"},{\"name\":\"\\u586b\\u5199\\u6bd4\\u8d5b\\u7ed3\\u679c\",\"controller\":\"items\",\"action\":\"play_result\"},{\"name\":\"\\u6bd4\\u8d5b\\u8bbe\\u7f6e\",\"controller\":\"items\",\"action\":\"play_conf\"},{\"name\":\"\\u53ef\\u7ed3\\u7b97\\u5217\\u8868\",\"controller\":\"items\",\"action\":\"statement\"},{\"name\":\"\\u7ed3\\u7b97\",\"controller\":\"items\",\"action\":\"statement_manual\"},{\"name\":\"\\u6dfb\\u52a0\\u6bd4\\u8d5b\",\"controller\":\"items\",\"action\":\"play_add\"},{\"name\":\"\\u6bd4\\u8d5b\\u9884\\u6d4b\",\"controller\":\"items\",\"action\":\"play_dope\"},{\"name\":\"\\u6279\\u91cf\\u5f00\\u623f\",\"controller\":\"items\",\"action\":\"batch_arena_publish\"},{\"name\":\"\\u6bd4\\u8d5b\\u70ed\\u5ea6\",\"controller\":\"items\",\"action\":\"play_hot\"}]', '0', '1493261514', '1502355137');
INSERT INTO `lt_permit` VALUES ('96', '25', '热门擂台', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"arenarecommend\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u4fee\\u6539\",\"controller\":\"arenarecommend\",\"action\":\"addarena\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"arenarecommend\",\"action\":\"deletearena\"}]', '0', '1493287138', '1493287199');
INSERT INTO `lt_permit` VALUES ('86', '22', '会员设置', '[{\"name\":\"\\u4f1a\\u5458\\u7b49\\u7ea7\",\"controller\":\"user\",\"action\":\"level\"}]', '0', '1493260997', '1493261027');
INSERT INTO `lt_permit` VALUES ('87', '25', '首页模块', '[{\"name\":\"\\u6a21\\u5757\\u5217\\u8868\",\"controller\":\"layout\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u4fee\\u6539\\u3001\\u5220\\u9664\",\"controller\":\"layout\",\"action\":\"add,delete\"},{\"name\":\"\\u6a21\\u5757\\u5185\\u5bb9\",\"controller\":\"layout\",\"action\":\"detail\"}]', '0', '1493261066', '1493261133');
INSERT INTO `lt_permit` VALUES ('85', '16', '日志', '[{\"name\":\"\\u7cfb\\u7edf\\u65e5\\u5fd7\",\"controller\":\"log\",\"action\":\"index\"}]', '0', '1493260945', '1493260959');
INSERT INTO `lt_permit` VALUES ('84', '16', '系统统计', '[{\"name\":\"\\u57fa\\u7840\\u6570\\u636e\",\"controller\":\"stat.basic\",\"action\":\"total_base\"},{\"name\":\"\\u6d3b\\u8dc3\\/\\u65b0\\u589e\\u7528\\u6237\\u91d1\\u5e01\\u7edf\\u8ba1\",\"controller\":\"stat.basic\",\"action\":\"total_base_avg\"},{\"name\":\"\\u57fa\\u7840\\u6570\\u636e\\u8d8b\\u52bf\\u56fe\",\"controller\":\"stat.basic\",\"action\":\"total_base_map\"},{\"name\":\"\\u57fa\\u7840\\u6570\\u636e\\u6307\\u6807\\u8bbe\\u7f6e\",\"controller\":\"stat.basic\",\"action\":\"total_base_field\"},{\"name\":\"\\u7cfb\\u7edf\\u6536\\u652f\",\"controller\":\"stat.system\",\"action\":\"income\"},{\"name\":\"\\u623f\\u95f4\\u7edf\\u8ba1\",\"controller\":\"stat.basic\",\"action\":\"arena\"},{\"name\":\"\\u57fa\\u7840\\u6570\\u636e-\\u6295\\u653e\\uff08\\u5e7f\\u544a\\u6210\\u672c\\uff09\",\"controller\":\"stat.basic\",\"action\":\"total_base_tf\"}]', '0', '1493260722', '1509603350');
INSERT INTO `lt_permit` VALUES ('116', '109', '超级客服端操作日志', '[{\"name\":\"\\u8d85\\u7ea7\\u5ba2\\u670d\\u7aef\\u64cd\\u4f5c\\u65e5\\u5fd7\",\"controller\":\"games.gm\",\"action\":\"log\"}]', '0', '1499498837', '1499498878');
INSERT INTO `lt_permit` VALUES ('78', '0', '代理', '', '0', '1481162339', '1481162339');
INSERT INTO `lt_permit` VALUES ('79', '78', '房间代理', '[{\"name\":\"\\u63a8\\u5e7f\\u64c2\\u53f0\\u7edf\\u8ba1\",\"controller\":\"agent.arena\",\"action\":\"arena\"},{\"name\":\"\\u6295\\u6ce8\\u7528\\u6237\\u5217\\u8868\",\"controller\":\"agent.arena\",\"action\":\"bet\"},{\"name\":\"\\u8d26\\u6237\\u7ba1\\u7406\",\"controller\":\"agent.arena\",\"action\":\"info\"},{\"name\":\"\\u5217\\u8868\",\"controller\":\"agent.arena\",\"action\":\"index\"},{\"name\":\"\\u4fee\\u6539\\u5bc6\\u7801\",\"controller\":\"agent.arena\",\"action\":\"modifypassword\"}]', '0', '1481162402', '1499950171');
INSERT INTO `lt_permit` VALUES ('97', '25', '客服答疑', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"customer\",\"action\":\"index\"},{\"name\":\"\\u56de\\u590d\",\"controller\":\"customer\",\"action\":\"reply\"},{\"name\":\"\\u6d88\\u606f\\u63d0\\u9192\",\"controller\":\"customer\",\"action\":\"total\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"customer\",\"action\":\"del\"},{\"name\":\"\\u5220\\u9664\\u56de\\u590d\",\"controller\":\"customer\",\"action\":\"reply_del\"}]', '0', '1493287209', '1501070336');
INSERT INTO `lt_permit` VALUES ('98', '25', '常见问题', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"faq\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u7f16\\u8f91\",\"controller\":\"faq\",\"action\":\"add\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"faq\",\"action\":\"delete\"}]', '0', '1493287290', '1493287325');
INSERT INTO `lt_permit` VALUES ('102', '0', '其他', '', '0', '1494838617', '1494838617');
INSERT INTO `lt_permit` VALUES ('99', '0', '财务', '', '0', '1495626470', '1495626470');
INSERT INTO `lt_permit` VALUES ('100', '99', '订单管理', '[{\"name\":\"\\u5145\\u503c\\u8ba2\\u5355\\u5217\\u8868\",\"controller\":\"finance.order\",\"action\":\"index\"},{\"name\":\"\\u8ba2\\u5355\\u4f5c\\u5e9f\",\"controller\":\"finance.order\",\"action\":\"invalid\"}]', '0', '1495626486', '1495626555');
INSERT INTO `lt_permit` VALUES ('101', '99', '提现管理', '[{\"name\":\"\\u63d0\\u73b0\\u8ba2\\u5355\",\"controller\":\"finance.withdrawal\",\"action\":\"index\"},{\"name\":\"\\u5ba1\\u6838\",\"controller\":\"finance.withdrawal\",\"action\":\"confirm\"},{\"name\":\"\\u63d0\\u73b0\\u989d\\u5ea6\\u9884\\u8b66\\u5217\\u8868\",\"controller\":\"finance.withdrawal\",\"action\":\"quota\"},{\"name\":\"\\u5c0f\\u6e38\\u620f\\u5c40\\u6570\\u9884\\u8b66\\u5217\\u8868\",\"controller\":\"finance.withdrawal\",\"action\":\"play_game\"},{\"name\":\"\\u9000\\u6b3e\",\"controller\":\"finance.withdrawal\",\"action\":\"recede\"},{\"name\":\"\\u62d2\\u7edd\",\"controller\":\"finance.withdrawal\",\"action\":\"dis\"},{\"name\":\"\\u63d0\\u73b0\\u6d88\\u606f\",\"controller\":\"finance.withdrawal\",\"action\":\"total\"}]', '0', '1495626494', '1501070369');
INSERT INTO `lt_permit` VALUES ('103', '102', '房间预警提醒', '[{\"name\":\"\\u623f\\u95f4\\u9884\\u8b66\\u63d0\\u9192\",\"controller\":\"items.all\",\"action\":\"risk,risk_total\"}]', '0', '1494838638', '1499951960');
INSERT INTO `lt_permit` VALUES ('104', '102', '更新缓存', '[{\"name\":\"\\u66f4\\u65b0\\u7f13\\u5b58\",\"controller\":\"common\",\"action\":\"refresh\"}]', '0', '1494839227', '1494839241');
INSERT INTO `lt_permit` VALUES ('105', '16', '限定词语', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"config\",\"action\":\"words\"},{\"name\":\"\\u65b0\\u589e\\/\\u4fee\\u6539\",\"controller\":\"config\",\"action\":\"words_add\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"config\",\"action\":\"words_del\"}]', '0', '1497601604', '1497601678');
INSERT INTO `lt_permit` VALUES ('106', '22', '限制IP', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"user\",\"action\":\"limit_ip\"},{\"name\":\"\\u65b0\\u589e\\/\\u7f16\\u8f91\",\"controller\":\"user\",\"action\":\"limit_ip_add\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"user\",\"action\":\"limit_ip_del\"}]', '0', '1497601844', '1497601899');
INSERT INTO `lt_permit` VALUES ('107', '22', '限制GUID', '[{\"name\":\"\\u5217\\u8868\",\"controller\":\"user\",\"action\":\"limit_guid\"},{\"name\":\"\\u65b0\\u589e\\/\\u7f16\\u8f91\",\"controller\":\"user\",\"action\":\"limit_guid_add\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"user\",\"action\":\"limit_guid_del\"}]', '0', '1497601917', '1497601969');
INSERT INTO `lt_permit` VALUES ('108', '22', '会员统计', '[{\"name\":\"\\u65b0\\u589e\\u7528\\u6237\",\"controller\":\"stat.user\",\"action\":\"new_user\"},{\"name\":\"\\u65e5\\u6d3b\\u8dc3\\u7528\\u6237\",\"controller\":\"stat.user\",\"action\":\"user_active_day\"},{\"name\":\"\\u5468\\u6d3b\\u8dc3\\u7528\\u6237\",\"controller\":\"stat.user\",\"action\":\"user_active_week\"},{\"name\":\"\\u6708\\u6d3b\\u8dc3\\u7528\\u6237\",\"controller\":\"stat.user\",\"action\":\"user_active_month\"},{\"name\":\"\\u7528\\u6237\\u7559\\u5b58\",\"controller\":\"stat.user\",\"action\":\"re_user\"},{\"name\":\"5\\u5206\\u949f\\u5728\\u7ebf\\u660e\\u7ec6\\uff08\\u603b\\uff09\",\"controller\":\"user\",\"action\":\"online_5min\"},{\"name\":\"5\\u5206\\u949f\\u5728\\u7ebf\\u660e\\u7ec6\\uff08\\u6e38\\u620f\\uff09\",\"controller\":\"user\",\"action\":\"online_5min_game\"},{\"name\":\"\\u6e38\\u620f\\u65f6\\u957f\",\"controller\":\"user\",\"action\":\"online_time\"},{\"name\":\"\\u5728\\u7ebf\\u8d26\\u53f7\\u6570\",\"controller\":\"user\",\"action\":\"online_account\"},{\"name\":\"\\u6bcf\\u65e5\\u5cf0\\u4f1a\",\"controller\":\"user\",\"action\":\"online_top\"},{\"name\":\"\\u7528\\u6237\\u6d41\\u5931\",\"controller\":\"stat.user\",\"action\":\"user_leave\"},{\"name\":\"\\u7528\\u6237\\u4f7f\\u7528\\u6d41\\u5931\\u7edf\\u8ba1\",\"controller\":\"stat.user\",\"action\":\"user_lost\"}]', '0', '1497601996', '1497616351');
INSERT INTO `lt_permit` VALUES ('109', '0', '小游戏', '', '0', '1497613858', '1497613858');
INSERT INTO `lt_permit` VALUES ('110', '109', '游戏库存管理', '[{\"name\":\"\\u6e38\\u620f\\u5e93\\u5b58\\u7ba1\\u7406\",\"controller\":\"games.stock\",\"action\":\"index\"},{\"name\":\"\\u6e38\\u620f\\u5e93\\u5b58\\u660e\\u7ec6\",\"controller\":\"games.stock\",\"action\":\"detail\"},{\"name\":\"\\u5e93\\u5b58\\u4fee\\u6539\\u5217\\u8868\",\"controller\":\"games.stock\",\"action\":\"change_log\"},{\"name\":\"\\u7f16\\u8f91\\u623f\\u95f4\\u5e93\\u5b58\\u8bbe\\u7f6e\",\"controller\":\"games.stock\",\"action\":\"modify\"},{\"name\":\"\\u6e38\\u620f\\u5e93\\u5b58\\u6bcf\\u65e5\\u5cf0\\u503c\",\"controller\":\"games.stock\",\"action\":\"peak\"},{\"name\":\"\\u65b0\\u623f\\u95f4\\u5e93\\u5b58\\u9ed8\\u8ba4\\u503c\",\"controller\":\"games.stock\",\"action\":\"new_room,new_room_modify\"},{\"name\":\"\\u5e93\\u5b58\\u4fee\\u6539\",\"controller\":\"games.stock\",\"action\":\"change_modify\"}]', '0', '1497613877', '1497617031');
INSERT INTO `lt_permit` VALUES ('111', '109', '房间金币监控', '[{\"name\":\"\\u623f\\u95f4\\u91d1\\u5e01\\u9884\\u8b66\\u8bbe\\u7f6e\",\"controller\":\"games.monitor\",\"action\":\"gold_absolute_detail\"},{\"name\":\"\\u65b0\\u589e\\/\\u7f16\\u8f91\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"gold_absolute_add\"},{\"name\":\"\\u5220\\u9664\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"gold_absolute_del\"},{\"name\":\"\\u8d62\\u5f97\\u91d1\\u5e01\\u73a9\\u5bb6\\u540d\\u5355\",\"controller\":\"games.monitor\",\"action\":\"gold_absolute_detail_user_list\"}]', '0', '1497613889', '1497614332');
INSERT INTO `lt_permit` VALUES ('112', '109', '游戏数据统计', '[{\"name\":\"\\u6570\\u636e\\u7edf\\u8ba1\",\"controller\":\"stat.basic\",\"action\":\"plat_in_time,in_time_data\"},{\"name\":\"\\u6e38\\u620f\\u91d1\\u5e01\\u8f93\\u8d62\",\"controller\":\"stat.game\",\"action\":\"gold\"},{\"name\":\"\\u8f93\\u8d62\\u6392\\u884c\\u699c\",\"controller\":\"stat.game\",\"action\":\"winboard\"},{\"name\":\"\\u7528\\u6237\\u8fdb\\u5165\\u6e38\\u620f\\u8bb0\\u5f55\",\"controller\":\"stat.game\",\"action\":\"inout\"},{\"name\":\"\\u7528\\u6237\\u6e38\\u620f\\u8bb0\\u5f55\",\"controller\":\"stat.game\",\"action\":\"record\"},{\"name\":\"\\u6e38\\u620f\\u7edf\\u8ba1\",\"controller\":\"stat.game\",\"action\":\"statics\"}]', '0', '1497613901', '1497681799');
INSERT INTO `lt_permit` VALUES ('113', '109', '游戏库存监控', '[{\"name\":\"\\u6e38\\u620f\\u5e93\\u5b58\",\"controller\":\"games.monitor\",\"action\":\"stock_detail\"},{\"name\":\"\\u65b0\\u589e\\/\\u7f16\\u8f91\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"stock_add\"},{\"name\":\"\",\"controller\":\"games.monitor\",\"action\":\"\"}]', '0', '1497614365', '1497614423');
INSERT INTO `lt_permit` VALUES ('114', '109', '观察者名单监控', '[{\"name\":\"\\u89c2\\u5bdf\\u8005\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"user_early_detail\"},{\"name\":\"\\u65b0\\u589e\\/\\u7f16\\u8f91\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"user_early_add\"},{\"name\":\"\\u5220\\u9664\\u9884\\u8b66\",\"controller\":\"games.monitor\",\"action\":\"user_early_del\"},{\"name\":\"\\u89c2\\u5bdf\\u8005\\u73a9\\u5bb6\\u540d\\u5355\",\"controller\":\"games.monitor\",\"action\":\"user_early_list\"}]', '0', '1497614448', '1497614581');
INSERT INTO `lt_permit` VALUES ('115', '25', '资讯', '[{\"name\":\"\\u8d44\\u8baf\\u5217\\u8868\",\"controller\":\"article\",\"action\":\"index\"},{\"name\":\"\\u6dfb\\u52a0\\u3001\\u7f16\\u8f91\",\"controller\":\"article\",\"action\":\"add\"},{\"name\":\"\\u5220\\u9664\",\"controller\":\"article\",\"action\":\"delarticle\"}]', '0', '1499241150', '1499241227');
INSERT INTO `lt_permit` VALUES ('117', '16', '渠道', '[{\"name\":\"\\u6e20\\u9053\\u5217\\u8868\",\"controller\":\"ditch\",\"action\":\"index\"},{\"name\":\"\\u6e20\\u9053\\u6dfb\\u52a0,\\u7f16\\u8f91\",\"controller\":\"ditch\",\"action\":\"add\"},{\"name\":\"\\u6e20\\u9053\\u5220\\u9664\",\"controller\":\"ditch\",\"action\":\"del\"},{\"name\":\"\\u6e20\\u9053\\u516c\\u53f8\\u5217\\u8868\",\"controller\":\"ditch\",\"action\":\"company\"},{\"name\":\"\\u6e20\\u9053\\u516c\\u53f8\\u6dfb\\u52a0\\u3001\\u7f16\\u8f91\",\"controller\":\"ditch\",\"action\":\"company_add\"},{\"name\":\"\\u6e20\\u9053\\u516c\\u53f8\\u5220\\u9664\",\"controller\":\"ditch\",\"action\":\"company_del\"},{\"name\":\"\\u4fee\\u6539\\u5bc6\\u7801\",\"controller\":\"ditch\",\"action\":\"changepass\"}]', '0', '1499500563', '1502778442');
INSERT INTO `lt_permit` VALUES ('118', '78', '充值代理', '[{\"name\":\"\\u5145\\u503c\\u4ee3\\u7406\\u7ba1\\u7406\",\"controller\":\"agent.recharge\",\"action\":\"index\"},{\"name\":\"\\u5145\\u503c\\u4ee3\\u7406\\u6dfb\\u52a0\\u3001\\u7f16\\u8f91\",\"controller\":\"agent.recharge\",\"action\":\"add\"},{\"name\":\"\\u5145\\u503c\\u4ee3\\u7406\\u5220\\u9664\",\"controller\":\"agent.recharge\",\"action\":\"del\"},{\"name\":\"\\u5145\\u503c\",\"controller\":\"agent.recharge\",\"action\":\"pay\"},{\"name\":\"\\u5145\\u503c\\u65e5\\u5fd7\",\"controller\":\"agent.recharge\",\"action\":\"logs\"}]', '0', '1499950183', '1499950287');
INSERT INTO `lt_permit` VALUES ('119', '0', '控制台', '', '0', '1499950890', '1499950890');
INSERT INTO `lt_permit` VALUES ('120', '119', '后台首页', '[{\"name\":\"\\u7edf\\u8ba1\\u6570\\u636e\",\"controller\":\"common\",\"action\":\"index_stat\"}]', '0', '1499950940', '1499951015');
INSERT INTO `lt_permit` VALUES ('121', '99', '统计', '[{\"name\":\"\\u6c47\\u603b\\u7edf\\u8ba1\",\"controller\":\"finance.stat\",\"action\":\"total\"}]', '0', '1500517620', '1500517645');
INSERT INTO `lt_permit` VALUES ('123', '109', '黑名单', '[{\"name\":\"\\u6740\\u5206\",\"controller\":\"games.monitor\",\"action\":\"game_shafen,game_shafen_add,delkilluser\"},{\"name\":\"\\u6740\\u5206\\u6e38\\u620f\\u5f00\\u5173\",\"controller\":\"games.monitor\",\"action\":\"game_shafen_controll,changekill\"},{\"name\":\"\\u89c2\\u5bdf\\u8005\\u540d\\u5355\",\"controller\":\"games.monitor\",\"action\":\"game_user_early\"}]', '0', '1502355550', '1502355686');
INSERT INTO `lt_permit` VALUES ('125', '22', '伙牌分析', '[{\"name\":\"\\u6597\\u5730\\u4e3b\\u4f19\\u724c\",\"controller\":\"user\",\"action\":\"landlord\"},{\"name\":\"\\u6597\\u5730\\u4e3b\\u767d\\u540d\\u5355\",\"controller\":\"user\",\"action\":\"landwhitelist,whitelist_add,delwhitelist,checkwhite\"},{\"name\":\"\\u8bc8\\u91d1\\u82b1\\u4f19\\u724c\",\"controller\":\"user\",\"action\":\"gflower\"},{\"name\":\"\\u8bbe\\u7f6e\",\"controller\":\"user\",\"action\":\"modifyconfig,cheatnum,sealusercheat\"}]', '0', '1502715430', '1502715606');
INSERT INTO `lt_permit` VALUES ('127', '22', 'guid统计', '[{\"name\":\"guid\\u7edf\\u8ba1\",\"controller\":\"user\",\"action\":\"guidcount\"},{\"name\":\"\\u4fee\\u6539guid\\u6ce8\\u518c\\u4e0b\\u9650\",\"controller\":\"user\",\"action\":\"guidwarnnum\"},{\"name\":\"ip\\u7edf\\u8ba1\",\"controller\":\"user\",\"action\":\"ipcount\"},{\"name\":\"\\u4fee\\u6539ip\\u6ce8\\u518c\\u4e0b\\u9650\",\"controller\":\"user\",\"action\":\"ipwarnnum\"}]', '0', '1502715711', '1502715823');
