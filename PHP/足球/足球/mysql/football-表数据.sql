

SET FOREIGN_KEY_CHECKS=0;

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
-- Records of lt_help
-- ----------------------------
INSERT INTO `lt_help` VALUES ('11', '让球盘', '<size=30>预测哪一支球队在盘口指定的让球数在全场/半场/赛事某个时节赢得比赛。\r\n让球盘由交战球队、让球数（即盘口）及贴水这3个部分组成。例如曼联0.86球半1.00富勒姆。其中曼联和富勒姆就是交战球队、球半即为让球盘数、0.86/1.00即为投注双方的获胜赔率又称“贴水”。\r\n让球的算法：实际比赛的结果+让球数=竞猜结果\r\n让半球：打平算输，赢一个或以上算赢\r\n让一球：打平算输，赢一个算平，赢两个或以上算赢。\r\n以此类推。\r\n\r\n<color=#2c539e><b>让球 - 全场</b></color>\r\n根据盘口让球信息预测最终获得胜利的球队。\r\n投注的结算皆以球赛所规定的完场时间90分钟为准。\r\n如果赛事在90分钟结束前取消或中断，所有注单将会被视为无效。</size>\r\n', '1524726453', '1524830174', '1', '1', '1', '#rangqiu');
INSERT INTO `lt_help` VALUES ('12', '', '<size=30>除非另有注明，所有足球投注的结算皆以球赛所规定的完场时间90分钟为准。完场时间90分钟包括球员伤停补时。加时赛、淘汰赛、点球，以及赛事后如果裁判或相关管理机构更改任何赛果则不计算在内。\r\n如果赛事被中断或延迟，并且没有在36小时内重新开始，所有该场赛事的投注即被视为无效且取消，除非在个别投注类型规则里另有指定注明。\r\n如果赛事于预定时间前开始，则只有在赛事开始前投注的项目方为有效。\r\n如果赌盘没有于正确时间关闭或中止，则平台保留避免投注者于实际开始时间后投注的权利。\r\n\\t除非在个别玩法规则另有注明，乌龙球将予以计算在内。\r\n\\t平台不对日期、时间、地点、对手、投注赔率、结果、统计数据或投注信息的相关错误或遗漏承担任何责任，且保留更正任何明显错误的权利，并会采取所有合理的措施来确保以诚信透明的方式管理赌盘。</size>\r\n', '1524726836', '1524829292', '1', '1', '2', '#ybwf');
INSERT INTO `lt_help` VALUES ('13', '大/小盘', '<size=30>预测赛事总入球数将大于或小于在盘口指定的大/小盘球数。\r\n如果赛事的总入球数多于盘口的大/小盘球数，则为大盘。如果少于盘口的大/小盘球数，则为小盘。\r\n所有注单将按盘口开出的大/小盘球数在玩法的时节结束后结算。\r\n大/小盘的玩法分为以下几种：\r\nA.大/小于‘一球’（如：2，3，4，等）\r\n大/小于‘半球’（如：1.5，2.5，3.5，等）\r\n混合以上‘半球/一球’的方式（如：1.5/2，2.5/3，3.5/4，等）\r\n如果赛事中断前已有明确结果并且之后没有任何显著会影响赛事结果的情况，大/小盘注单才会被结算。若遇到任何其他情况，注单将一律被取消。\r\n支持玩法\r\n<color=#2c539e><b>大/小 - 全场</b></color>\r\n所有的投注将以全场90分钟的赛果结算。\r\n如果比赛停止，取消或中断，所有投注将被视为无效，除非在赛事取消或中断前，结果已经明确。</size>\r\n', '1524727216', '1524830102', '1', '1', '1', '#dxp');
INSERT INTO `lt_help` VALUES ('14', '独赢盘', '<size>预测单一赛事中预测哪一支球队将在比赛胜出。盘口提供两支球队胜负和平局为投注选项。\r\n投注将以0-0的比分作为计算基础（让球并不计算在内）。\r\n\r\n<color=#2c539e><b>独赢 - 全场</b></color>\r\n预测哪一支球队将在90分钟比赛胜出或赛事和局。</size>\r\n', '1524727978', '1524830422', '1', '1', '1', '#dyp');
INSERT INTO `lt_help` VALUES ('15', '波胆', '<size=30>预测一场特定赛事中相关时间段的准确比分。\r\n全场波胆投注的结算根据90分钟完场比分做出裁决。\r\n如果赛事取消，全场波胆投注在“其它比分”为仅有可能获胜的选项，投注将被视为有效；所有其他的投注则被视为无效，此是由于赛事无条件决定后面的进球不会影响赛事的结果。</size>\r\n', '1524728134', '1524829837', '1', '1', '1', '#bd');
INSERT INTO `lt_help` VALUES ('17', '总进球数', '<size=30>预测在比赛中进球的数量，由赛事中的总进球数决定投注结果。\r\n	所有的投注以赛事官方90分钟为完场时间，包括加时、伤停补时。\r\n	如果赛事中断，将以官方单位公布的最后赛果为准，其中包括赛事重新开始或指定的分数。\r\n\r\n<color=#2c539e><b>全场</b></color>\r\n	预测全场两队的总入球数。\r\n	全场总入球数注单结算是根据全场“90分钟”为准。\r\n	如果赛事中断，总入球数投注将仅结算当赛事进球7个或更多，这是由于任何后面的进球不会影响赛事结果。任何其他的情况，投注将被视为无效。</size>\r\n', '1524728347', '1524829743', '1', '1', '1', '#zjqs');
INSERT INTO `lt_help` VALUES ('18', '双方球队进球', '<size=30>双方球队进球（主队进球数/客队进球数）\r\n	预测双方球队在90分钟时间内是否进球。\r\n	全场总入球数注单结算是根据全场“90分钟”为准。\r\n	如果赛事在双方球队都有进球后中断，所有注单保持有效。\r\n	如果赛事在双方球队没有进球前中断或延迟，所有注单将被取消。\r\n	乌龙球将予以计算为得分那方入球。</size>\r\n', '1524728596', '1524829475', '1', '1', '1', '#sfqdjc');
INSERT INTO `lt_help` VALUES ('19', '单 / 双', '<size=30>预测赛事中球员的总进球数是单数或双数。\r\n如果赛事中断或推迟，所有注单将会被取消，除非赛事已有明确结果并且之后入球对赛事没有任何影响。\r\n若比赛没有球员进球，赛果为0，投注‘双’注单为赢。</size>\r\n', '1524728831', '1524809176', '1', '1', '1', '#ds');


-- ----------------------------
-- Records of lt_help_type
-- ----------------------------
INSERT INTO `lt_help_type` VALUES ('1', '投注类型');
INSERT INTO `lt_help_type` VALUES ('2', '一般玩法');


-- ----------------------------
-- Records of lt_layout_sports
-- ----------------------------
INSERT INTO `lt_layout_sports` VALUES ('1', '1', '1', '11', 'match', '{\"29\":{\"id\":\"29\",\"name\":\"\\u4e2d\\u5317\\u7f8e\\u51a0\"}}', '999', '1', '1523953006', '1523953006');


-- ----------------------------
-- Records of lt_manager
-- ----------------------------
INSERT INTO `lt_manager` VALUES ('1', '-1', 'admin', 'ba8717d5e1aceb0a50d9c80bcdfe7ad9', '123456', '管理员大哥', '05/b5/d0/e0/4e/04fb1b120a0f40cacd3f34.jpg', '1', '1525084912', '1471053873', '1520515199');


-- ----------------------------
-- Records of lt_odds_company
-- ----------------------------
INSERT INTO `lt_odds_company` VALUES ('22', '澳门', '1', '1', '1');
INSERT INTO `lt_odds_company` VALUES ('2', 'ＳＢ/皇冠', '1', '1', '3');
INSERT INTO `lt_odds_company` VALUES ('3', 'Bet365', '1', '1', '8');
INSERT INTO `lt_odds_company` VALUES ('4', '易胜博', '1', '1', '12');
INSERT INTO `lt_odds_company` VALUES ('5', '韦德', '1', '0', '14');
INSERT INTO `lt_odds_company` VALUES ('7', '10bet', '1', '1', '22');
INSERT INTO `lt_odds_company` VALUES ('8', '金宝博', '1', '1', '23');
INSERT INTO `lt_odds_company` VALUES ('9', '12bet/沙巴', '1', '0', '24');
INSERT INTO `lt_odds_company` VALUES ('10', '利记', '1', '1', '31');
INSERT INTO `lt_odds_company` VALUES ('11', '盈禾', '1', '0', '35');
INSERT INTO `lt_odds_company` VALUES ('12', '18bet', '1', '1', '42');
INSERT INTO `lt_odds_company` VALUES ('13', '伟德(直布罗陀)', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('14', '立博', '0', '1', '4');
INSERT INTO `lt_odds_company` VALUES ('15', 'bwin', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('16', '明升', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('17', 'SNAI', '0', '1', '7');
INSERT INTO `lt_odds_company` VALUES ('18', 'Singbet', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('19', 'Pinnacle Sports', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('20', 'Unibet', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('21', 'Sportingbet', '0', '1', null);
INSERT INTO `lt_odds_company` VALUES ('1', '官方', '0', '0', null);
INSERT INTO `lt_odds_company` VALUES ('23', '腾讯赔率', '0', '0', null);
INSERT INTO `lt_odds_company` VALUES ('24', '中国足彩网', '1', '1', null);
INSERT INTO `lt_odds_company` VALUES ('25', '乐盈', '0', '0', null);


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


-- ----------------------------
-- Records of lt_rules
-- ----------------------------
INSERT INTO `lt_rules` VALUES ('1', '1', '0', '1', '0', null, '1', '1', '胜负(让球)', '让球', null, '加减让球值的基础上计算输赢，打平退回本金', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后输赢。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，打平退回本金。', '1', '10000', null, '1', '0', '1470622673', '1497774005');
INSERT INTO `lt_rules` VALUES ('2', '1', '0', '1', '0', null, '1', '2', '胜平负', '独赢', null, '猜全场90分钟内含补时的胜平负结果', '胜平负是指全场90分钟（含伤停补时，不含加时赛和点球大战）的胜、平、负情况。', '2', '10000', null, '1', '0', '1470627797', '1524367714');
INSERT INTO `lt_rules` VALUES ('3', '0', '0', '0', '0', null, '1', '3', '主进球', '#team_home_name#进球', null, '猜全场90分钟内(含伤停补时)主队进球总数', '猜全场90分钟内(含伤停补时)主队进球总数', '999', '10000', null, '1', '0', '1470627862', '1524369337');
INSERT INTO `lt_rules` VALUES ('4', '0', '0', '0', '0', null, '1', '4', '客进球', '#team_guest_name#进球', null, '猜全场90分钟内(含伤停补时)客队进球总数', '猜全场90分钟内(含伤停补时)客队进球总数', '999', '10000', null, '1', '0', '1470635639', '1524367754');
INSERT INTO `lt_rules` VALUES ('5', '0', '0', '0', '0', null, '1', '5', '全场比分', null, null, null, null, '999', '0', null, '0', '1', '1470635804', '1470635804');
INSERT INTO `lt_rules` VALUES ('6', '0', '0', '0', '0', null, '1', '6', '半场比分', null, null, null, null, '999', '0', null, '0', '1', '1470635909', '1470635909');
INSERT INTO `lt_rules` VALUES ('7', '0', '0', '0', '0', null, '1', '7', '全场黄牌总数', null, null, '猜全场黄牌数', '', '999', '50000', null, '1', '0', '1470636005', '1488268794');
INSERT INTO `lt_rules` VALUES ('8', '0', '0', '1', '0', null, '1', '8', '最先进球', null, null, '猜最先进球数', '', '999', '60000', null, '1', '0', '1470636057', '1488268804');
INSERT INTO `lt_rules` VALUES ('9', '0', '0', '0', '0', null, '1', '9', '全场进球总数', '总进球', null, '猜全场90分钟（含伤停补时）两队进球数的总和', '进球数是指全场90分钟（含伤停补时）两队进球数的总和。', '999', '10000', null, '1', '0', '1470636144', '1524367800');
INSERT INTO `lt_rules` VALUES ('10', '0', '0', '0', '0', null, '1', '10', '比分', '波胆', null, '比分是指全场90分钟（含伤停补时）两队的比分情况。', '比分是指全场90分钟（含伤停补时）两队的比分情况, 主1：0 指主队1，客队0。', '5', '10000', null, '1', '0', '1470636272', '1524367727');
INSERT INTO `lt_rules` VALUES ('11', '0', '0', '0', '0', null, '1', '11', '比分组合', '比分组合', null, '猜对组合中任一比分即为中', '比分是指全场90分钟（含伤停补时）两队的比分情况，猜对组合中任意一种情况即为中。', '999', '10000', null, '2', '0', '1470636272', '1524367635');
INSERT INTO `lt_rules` VALUES ('12', '0', '0', '1', '0', null, '1', '12', '单双', '单双', null, '猜全场两队进球数总和是单数还是双数', '猜全场90分钟（含伤停补时）两队进球数的总和是单数还是双数。', '4', '10000', null, '1', '0', '1470637932', '1524367545');
INSERT INTO `lt_rules` VALUES ('13', '0', '0', '1', '0', null, '1', '13', '上/下半场进球数比较', '半场比较', null, '猜上/下半场两队进球数总合对比', '猜上/下半场两队进球数总合是上半场多还是下半场多。', '999', '10000', null, '2', '0', '1470638045', '1524367627');
INSERT INTO `lt_rules` VALUES ('14', '0', '0', '1', '0', '1', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后得分。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，其中一个比分相同，则赢一半。', '999', '1000', null, '1', '0', '1479181121', '1500900207');
INSERT INTO `lt_rules` VALUES ('15', '0', '0', '1', '0', '1', '99', '14', '大小', '大小', '[\"home\",\"guest\",\"over\",\"under\"]', '猜全场总地图数/回合数大于/小于指定数值', '<p>猜全场总地图数/回合数大于/小于指定数值</p>', '1', '0', null, '1', '0', '1479181121', '1500900076');
INSERT INTO `lt_rules` VALUES ('16', '0', '0', '1', '0', '1', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1479181121', '1500900393');
INSERT INTO `lt_rules` VALUES ('17', '0', '0', '1', '0', '2', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '一方队伍加减让分值的基础上计算输赢，打平退回本金', '999', '0', null, '1', '0', '1479346137', '1500900226');
INSERT INTO `lt_rules` VALUES ('18', '0', '0', '1', '0', '2', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1479346137', '1500900383');
INSERT INTO `lt_rules` VALUES ('19', '0', '0', '1', '0', '5', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '<p>加减让分值的基础上计算输赢，打平退回本金</p>', '999', '0', null, '1', '0', '1479181121', '1500900300');
INSERT INTO `lt_rules` VALUES ('20', '0', '0', '1', '0', '3', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1479346139', '1500900402');
INSERT INTO `lt_rules` VALUES ('21', '0', '0', '1', '0', '2', '99', '14', '大小', '大小', '[\"home\",\"guest\",\"over\",\"under\"]', '猜全场总地图数/回合数大于/小于指定数值', '<p>猜全场总地图数/回合数大于/小于指定数值</p>', '999', '0', null, '1', '0', '1479435569', '1500900091');
INSERT INTO `lt_rules` VALUES ('22', '0', '0', '1', '0', '4', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后得分。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，其中一个比分相同，则赢一半。', '999', '0', null, '1', '0', '1479452804', '1500900290');
INSERT INTO `lt_rules` VALUES ('23', '0', '0', '1', '0', '4', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1479452804', '1500900411');
INSERT INTO `lt_rules` VALUES ('24', '0', '0', '1', '0', '4', '99', '14', '大小', '大/小', '[\"home\",\"guest\",\"over\",\"under\"]', '总地图数/回合数是否大于或小于盘口数', '预测在比赛中的总地图数/回合数是否大于或小于盘口数，任何形式的额外时间或加时赛均计算在内。', '999', '0', null, '1', '0', '1479452804', '1490952437');
INSERT INTO `lt_rules` VALUES ('25', '0', '0', '1', '0', '3', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢。', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后得分。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，其中一个比分相同，则赢一半。', '999', '0', null, '1', '0', '1479965049', '1500900236');
INSERT INTO `lt_rules` VALUES ('26', '0', '0', '0', '0', '3', '99', '14', '大小', '大/小', '[\"home\",\"guest\",\"over\",\"under\"]', '总地图数/回合数是否大于或小于盘口数', '预测在比赛中的总地图数/回合数是否大于或小于盘口数，任何形式的额外时间或加时赛均计算在内。', '999', '0', null, '1', '0', '1480065092', '1490952338');
INSERT INTO `lt_rules` VALUES ('27', '0', '0', '1', '0', '5', '99', '14', '大小', '大/小', '[\"home\",\"guest\",\"over\",\"under\"]', '猜全场总地图数/回合数大于/小于指定数值', '猜全场总地图数/回合数大于/小于指定数值', '999', '20000', null, '1', '0', '1479181121', '1491443116');
INSERT INTO `lt_rules` VALUES ('28', '0', '0', '1', '0', '6', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后得分。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，其中一个比分相同，则赢一半。', '999', '0', null, '1', '0', '1480131690', '1500900310');
INSERT INTO `lt_rules` VALUES ('29', '0', '0', '0', '0', '6', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1480318595', '1500900432');
INSERT INTO `lt_rules` VALUES ('30', '0', '1', '1', '0', '0', '4', '1', '让球', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1480916867', '1480916867');
INSERT INTO `lt_rules` VALUES ('31', '0', '1', '1', '0', '0', '4', '4', '进球:单/双', null, '[\"\\u5355\",\"\\u53cc\"]', null, null, '999', '0', null, '1', '0', '1480916867', '1480916867');
INSERT INTO `lt_rules` VALUES ('32', '0', '1', '1', '0', '0', '4', '9', '让球-加时&点球', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1480916867', '1480916867');
INSERT INTO `lt_rules` VALUES ('33', '0', '1', '1', '0', '0', '4', '9', '进球:大/小-加时&点球', null, '[\"\\u5927\",\"\\u5c0f\",\"over\",\"under\"]', null, null, '999', '0', null, '1', '0', '1480916867', '1480916867');
INSERT INTO `lt_rules` VALUES ('34', '0', '1', '1', '0', '0', '4', '9', '独赢盘-加时&点球', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1480916867', '1480916867');
INSERT INTO `lt_rules` VALUES ('35', '0', '1', '1', '0', '0', '4', '2', '进球:大/小', null, '[\"\\u5927\",\"\\u5c0f\",\"over\",\"under\"]', null, null, '999', '0', null, '1', '0', '1480916874', '1480916874');
INSERT INTO `lt_rules` VALUES ('36', '0', '0', '1', '0', '2', '99', '99', '第1局一血', '第1局一血', '[\"home\",\"guest\"]', '猜本场比赛第1局一血的获得方', '<p>本场比赛第1局一血的获得方</p>', '999', '0', null, '1', '0', '1500865372', '1500897687');
INSERT INTO `lt_rules` VALUES ('37', '0', '0', '1', '0', '2', '99', '99', '第2局一血', '第2局一血', '[\"home\",\"guest\"]', '猜本场比赛第2局一血的获得方', '<p>猜本场比赛第2局一血的获得方</p>', '999', '0', null, '1', '0', '1500865373', '1500897708');
INSERT INTO `lt_rules` VALUES ('38', '0', '0', '1', '0', '2', '99', '99', '第3局一血', '第3局一血', '[\"home\",\"guest\"]', '猜本场比赛第3局一血的获得方', '<p>猜本场比赛第3局一血的获得方</p>', '999', '0', null, '1', '0', '1500865373', '1500899058');
INSERT INTO `lt_rules` VALUES ('39', '0', '0', '1', '0', '2', '99', '99', '第1局一塔', '第1局一塔', '[\"home\",\"guest\"]', '猜本场比赛第1局一塔的获得方', '<p>猜本场比赛第1局一塔的获得方</p>', '999', '0', null, '1', '0', '1500874635', '1500899083');
INSERT INTO `lt_rules` VALUES ('40', '0', '1', '1', '0', '0', '2', '2', '独赢盘', '猜胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1481770405', '1490952893');
INSERT INTO `lt_rules` VALUES ('41', '0', '0', '1', '0', '2', '99', '99', '第2局一塔', '第2局一塔', '[\"home\",\"guest\"]', '猜本场比赛第2局一塔的获得方', '<p>猜本场比赛第2局一塔的获得方</p>', '999', '0', null, '1', '0', '1500874636', '1500899101');
INSERT INTO `lt_rules` VALUES ('42', '0', '0', '1', '0', '2', '99', '99', '第3局一塔', '第3局一塔', '[\"home\",\"guest\"]', '猜本场比赛第3局一塔的获得方', '<p>猜本场比赛第3局一塔的获得方</p>', '999', '0', null, '1', '0', '1500874636', '1500899111');
INSERT INTO `lt_rules` VALUES ('43', '0', '1', '1', '0', '0', '2', '15', '球队得分:#team_guest_name#-最后一位数', null, '[\"0\\u62165\",\"1\\u62166\",\"2\\u62167\",\"3\\u62168\",\"4\\u62169\"]', null, null, '999', '0', null, '1', '0', '1481770381', '1481770381');
INSERT INTO `lt_rules` VALUES ('44', '0', '1', '1', '0', '0', '2', '15', '球队得分:#team_home_name#-最后一位数', null, '[\"0\\u62165\",\"1\\u62166\",\"2\\u62167\",\"3\\u62168\",\"4\\u62169\"]', null, null, '999', '0', null, '1', '0', '1481770381', '1481770381');
INSERT INTO `lt_rules` VALUES ('45', '0', '1', '1', '0', '0', '6', '1', '让球', null, '[\"home\",\"guest\",\"handicap\"]', '', null, '999', '0', null, '1', '0', '1481012102', '1486344979');
INSERT INTO `lt_rules` VALUES ('46', '0', '1', '1', '0', '0', '6', '2', '大/小', null, '[\"\\u5927\",\"\\u5c0f\",\"over\",\"under\"]', null, null, '999', '0', null, '1', '0', '1481012103', '1481018936');
INSERT INTO `lt_rules` VALUES ('47', '0', '1', '1', '0', '0', '4', '3', '独赢盘', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1481076015', '1481076015');
INSERT INTO `lt_rules` VALUES ('48', '0', '0', '1', '0', '5', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '猜全场比赛含补时的最终胜负结果。平局则退回本金。', '999', '0', null, '1', '0', '1481262309', '1500900421');
INSERT INTO `lt_rules` VALUES ('49', '0', '1', '1', '0', '0', '3', '7', '总局数:大/小-第一盘', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487214062', '1487214062');
INSERT INTO `lt_rules` VALUES ('50', '0', '0', '1', '0', '2', '99', '99', '第1局第一只小龙', '第1局小龙', '[\"home\",\"guest\"]', '猜第1局获得第一只小龙的队伍', '<p>猜第1局获得第一只小龙的队伍</p>', '999', '0', null, '1', '0', '1500877204', '1500899229');
INSERT INTO `lt_rules` VALUES ('51', '0', '0', '1', '0', '2', '99', '99', '第2局第一只小龙', '第2局小龙', '[\"home\",\"guest\"]', '猜第2局获得第一只小龙的队伍', '<p>猜第2局获得第一只小龙的队伍</p>', '999', '0', null, '1', '0', '1500877204', '1500899260');
INSERT INTO `lt_rules` VALUES ('52', '0', '1', '1', '0', '0', '2', '12', '总分:单/双', '单/双', '[\"\\u5355\",\"\\u53cc\"]', '双方队伍得分总和是单数还是双数', '全场双方队伍得分总和是单数还是双数，任何形式的额外时间或加时赛均计算在内。', '999', '0', null, '1', '0', '1481770381', '1490952861');
INSERT INTO `lt_rules` VALUES ('53', '0', '1', '1', '0', '0', '2', '14', '总分:大/小', '大/小', '[\"\\u5927\",\"\\u5c0f\"]', '比赛双方总分是否大于或小于盘口数', '比赛双方总分是否大于或小于盘口数，任何形式的额外时间或加时赛均计算在内。\r\n', '999', '0', null, '1', '0', '1481770381', '1490952769');
INSERT INTO `lt_rules` VALUES ('54', '0', '1', '1', '0', '0', '2', '1', '让球', '让球', '[\"home\",\"guest\"]', '一方队伍加减让分值的基础上计算输赢', '即在比赛开始前一方队伍已经让分给另一方，在此基础上计算最后得分。 例如：塞曼巴东 VS 米特拉库卡，比赛实际比分为1:0。 让球为2.5，则最终以1:2.5计算结果 让球为1/2.5，则按1:1/1:2.5计算结果，两个比分都赢算全赢，其中一个比分相同，则赢一半。', '999', '0', null, '1', '0', '1481770381', '1490952721');
INSERT INTO `lt_rules` VALUES ('55', '0', '0', '1', '0', '2', '99', '99', '第3局第一只小龙', '第3局小龙', '[\"home\",\"guest\"]', '猜第3局获得第一只小龙的队伍', '<p>猜第3局获得第一只小龙的队伍</p>', '999', '0', null, '1', '0', '1500877205', '1500899275');
INSERT INTO `lt_rules` VALUES ('56', '0', '1', '0', '0', '6', '99', '14', '大小', '大/小', '[\"home\",\"guest\"]', '总地图数/回合数是否大于或小于盘口数', '预测在比赛中的总地图数/回合数是否大于或小于盘口数，任何形式的额外时间或加时赛均计算在内。', '999', '20000', null, '1', '0', '1481780910', '1490952669');
INSERT INTO `lt_rules` VALUES ('57', '0', '1', '0', '0', '2', '99', '99', '第4局一血', '第4局一血', '[\"home\",\"guest\"]', '猜本场比赛第4局一血的获得方', '<p>猜本场比赛第4局一血的获得方</p>', '999', '0', null, '1', '0', '1500882743', '1500899309');
INSERT INTO `lt_rules` VALUES ('58', '0', '1', '0', '0', '2', '99', '99', '第5局一血', '第5局一血', '[\"home\",\"guest\"]', '猜本场比赛第5局一血的获得方', '<p>猜本场比赛第5局一血的获得方</p>', '999', '0', null, '1', '0', '1500882792', '1500899320');
INSERT INTO `lt_rules` VALUES ('59', '0', '1', '0', '0', '2', '99', '99', '第4局一塔', '第4局一塔', '[\"home\",\"guest\"]', '猜本场比赛第4局一塔的获得方', '<p>猜本场比赛第4局一塔的获得方</p>', '999', '0', null, '1', '0', '1500882826', '1500899338');
INSERT INTO `lt_rules` VALUES ('60', '0', '1', '0', '0', '2', '99', '99', '第5局一塔', '第5局一塔', '[\"home\",\"guest\"]', '猜本场比赛第5局一塔的获得方', '<p>猜本场比赛第5局一塔的获得方</p>', '999', '0', null, '1', '0', '1500882846', '1500899351');
INSERT INTO `lt_rules` VALUES ('61', '0', '1', '0', '0', '2', '99', '99', '第4局第一只小龙', '第4局小龙', '[\"home\",\"guest\"]', '第4局第一只小龙的获得者', '<p>第4局第一只小龙的获得者</p>', '999', '0', null, '1', '0', '1500882883', '1500899384');
INSERT INTO `lt_rules` VALUES ('62', '0', '1', '0', '0', '2', '99', '99', '第5局第一只小龙', '第5局小龙', '[\"home\",\"guest\"]', '第5局第一只小龙的获得者', '<p>第5局第一只小龙的获得者</p>', '999', '0', null, '1', '0', '1500882907', '1500899395');
INSERT INTO `lt_rules` VALUES ('63', '0', '1', '0', '0', '1', '99', '99', '第1局一血', '第1局一血', '[\"home\",\"guest\"]', '猜本场比赛第1局一血的获得方', '<p>猜本场比赛第1局一血的获得方</p>', '999', '0', null, '1', '0', '1500883018', '1500899557');
INSERT INTO `lt_rules` VALUES ('64', '0', '1', '0', '0', '1', '99', '99', '第2局一血', '第2局一血', '[\"home\",\"guest\"]', '猜本场比赛第2局一血的获得方', '<p>猜本场比赛第2局一血的获得方</p>', '999', '0', null, '1', '0', '1500883037', '1500899567');
INSERT INTO `lt_rules` VALUES ('65', '0', '1', '0', '0', '1', '99', '99', '第3局一血', '第3局一血', '[\"home\",\"guest\"]', '猜本场比赛第3局一血的获得方', '<p>猜本场比赛第3局一血的获得方</p>', '999', '0', null, '1', '0', '1500883055', '1500899576');
INSERT INTO `lt_rules` VALUES ('66', '0', '1', '0', '0', '1', '99', '99', '第4局一血', '第4局一血', '[\"home\",\"guest\"]', '猜本场比赛第4局一血的获得方', '<p>猜本场比赛第4局一血的获得方</p>', '999', '0', null, '1', '0', '1500883069', '1500899587');
INSERT INTO `lt_rules` VALUES ('67', '0', '1', '0', '0', '1', '99', '99', '第5局一血', '第5局一血', '[\"home\",\"guest\"]', '猜本场比赛第5局一血的获得方', '<p>猜本场比赛第5局一血的获得方</p>', '999', '0', null, '1', '0', '1500883088', '1500899598');
INSERT INTO `lt_rules` VALUES ('68', '0', '1', '0', '0', '1', '99', '99', '第1局一塔', '第1局一塔', '[\"home\",\"guest\"]', '猜本场比赛第1局一塔的获得方', '<p>猜本场比赛第1局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883113', '1500899613');
INSERT INTO `lt_rules` VALUES ('69', '0', '1', '0', '0', '1', '99', '99', '第2局一塔', '第2局一塔', '[\"home\",\"guest\"]', '猜本场比赛第2局一塔的获得方', '<p>猜本场比赛第2局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883128', '1500899624');
INSERT INTO `lt_rules` VALUES ('70', '0', '1', '0', '0', '1', '99', '99', '第3局一塔', '第3局一塔', '[\"home\",\"guest\"]', '猜本场比赛第3局一塔的获得方', '<p>猜本场比赛第3局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883143', '1500899642');
INSERT INTO `lt_rules` VALUES ('71', '0', '1', '0', '0', '1', '99', '99', '第4局一塔', '第4局一塔', '[\"home\",\"guest\"]', '猜本场比赛第4局一塔的获得方', '<p>猜本场比赛第4局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883159', '1500899654');
INSERT INTO `lt_rules` VALUES ('72', '0', '1', '0', '0', '1', '99', '99', '第5局一塔', '第5局一塔', '[\"home\",\"guest\"]', '猜本场比赛第5局一塔的获得方', '<p>猜本场比赛第5局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883173', '1500899666');
INSERT INTO `lt_rules` VALUES ('73', '0', '1', '0', '0', '1', '99', '99', '第1局第一座肉山', '第1局肉山', '[\"home\",\"guest\"]', '猜本场比赛第1局一座肉山的获得方', '<p>猜本场比赛第1局一座肉山的获得方</p>', '999', '0', null, '1', '0', '1500883205', '1500899681');
INSERT INTO `lt_rules` VALUES ('74', '0', '1', '0', '0', '1', '99', '99', '第2局第一座肉山', '第2局肉山', '[\"home\",\"guest\"]', '猜本场比赛第2局一座肉山的获得方', '<p>猜本场比赛第2局一座肉山的获得方</p>', '999', '0', null, '1', '0', '1500883228', '1500899694');
INSERT INTO `lt_rules` VALUES ('75', '0', '1', '0', '0', '1', '99', '99', '第3局第一座肉山', '第3局肉山', '[\"home\",\"guest\"]', '猜本场比赛第3局一座肉山的获得方', '<p>猜本场比赛第3局一座肉山的获得方</p>', '999', '0', null, '1', '0', '1500883248', '1500899705');
INSERT INTO `lt_rules` VALUES ('76', '0', '1', '0', '0', '1', '99', '99', '第4局第一座肉山', '第4局肉山', '[\"home\",\"guest\"]', '猜本场比赛第4局一座肉山的获得方', '<p>猜本场比赛第4局一座肉山的获得方</p>', '999', '0', null, '1', '0', '1500883265', '1500899716');
INSERT INTO `lt_rules` VALUES ('77', '0', '1', '0', '0', '1', '99', '99', '第5局第一座肉山', '第5局肉山', '[\"home\",\"guest\"]', '猜本场比赛第5局一座肉山的获得方', '<p>猜本场比赛第5局一座肉山的获得方</p>', '999', '0', null, '1', '0', '1500883285', '1500899727');
INSERT INTO `lt_rules` VALUES ('78', '0', '1', '0', '0', '9', '99', '99', '第1局一血', '第1局一血', '[\"home\",\"guest\"]', '猜本场比赛第1局一血的获得方', '<p>猜本场比赛第1局一血的获得方</p>', '999', '0', null, '1', '0', '1500883403', '1500899815');
INSERT INTO `lt_rules` VALUES ('79', '0', '1', '0', '0', '9', '99', '99', '第2局一血', '第2局一血', '[\"home\",\"guest\"]', '猜本场比赛第2局一血的获得方', '<p>猜本场比赛第2局一血的获得方</p>', '999', '0', null, '1', '0', '1500883421', '1500899825');
INSERT INTO `lt_rules` VALUES ('80', '0', '1', '0', '0', '9', '99', '99', '第3局一血', '第3局一血', '[\"home\",\"guest\"]', '猜本场比赛第3局一血的获得方', '<p>猜本场比赛第3局一血的获得方</p>', '999', '0', null, '1', '0', '1500883439', '1500899836');
INSERT INTO `lt_rules` VALUES ('81', '0', '1', '0', '0', '9', '99', '99', '第4局一血', '第4局一血', '[\"home\",\"guest\"]', '猜本场比赛第4局一血的获得方', '<p>猜本场比赛第4局一血的获得方</p>', '999', '0', null, '1', '0', '1500883458', '1500899929');
INSERT INTO `lt_rules` VALUES ('82', '0', '1', '0', '0', '9', '99', '99', '第5局一血', '第5局一血', '[\"home\",\"guest\"]', '猜本场比赛第5局一血的获得方', '<p>猜本场比赛第5局一血的获得方</p>', '999', '0', null, '1', '0', '1500883474', '1500899944');
INSERT INTO `lt_rules` VALUES ('83', '0', '1', '0', '0', '9', '99', '99', '第1局一塔', '第1局一塔', '[\"home\",\"guest\"]', '猜本场比赛第1局一塔的获得方', '<p>猜本场比赛第1局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883494', '1500899962');
INSERT INTO `lt_rules` VALUES ('84', '0', '1', '0', '0', '9', '99', '99', '第2局一塔', '第2局一塔', '[\"home\",\"guest\"]', '猜本场比赛第2局一塔的获得方', '<p>猜本场比赛第2局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883509', '1500899971');
INSERT INTO `lt_rules` VALUES ('85', '0', '1', '0', '0', '9', '99', '99', '第3局一塔', '第3局一塔', '[\"home\",\"guest\"]', '猜本场比赛第3局一塔的获得方', '<p>猜本场比赛第3局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883528', '1500899982');
INSERT INTO `lt_rules` VALUES ('86', '0', '1', '0', '0', '9', '99', '99', '第4局一塔', '第4局一塔', '[\"home\",\"guest\"]', '猜本场比赛第4局一塔的获得方', '<p>猜本场比赛第4局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883546', '1500899992');
INSERT INTO `lt_rules` VALUES ('87', '0', '1', '0', '0', '9', '99', '99', '第5局一塔', '第5局一塔', '[\"home\",\"guest\"]', '猜本场比赛第5局一塔的获得方', '<p>猜本场比赛第5局一塔的获得方</p>', '999', '0', null, '1', '0', '1500883563', '1500900004');
INSERT INTO `lt_rules` VALUES ('88', '0', '1', '0', '0', '3', '99', '99', '第1局胜', '第1局胜', '[\"home\",\"guest\"]', '第1局胜', '', '999', '0', null, '1', '0', '1500883654', '1500883654');
INSERT INTO `lt_rules` VALUES ('89', '0', '1', '0', '0', '3', '99', '99', '第2局胜', '第2局胜', '[\"home\",\"guest\"]', '第2局胜', '', '999', '0', null, '1', '0', '1500883673', '1500883673');
INSERT INTO `lt_rules` VALUES ('90', '0', '1', '0', '0', '3', '99', '99', '第3局胜', '第3局胜', '[\"home\",\"guest\"]', '第3局胜', '', '999', '0', null, '1', '0', '1500883696', '1500883696');
INSERT INTO `lt_rules` VALUES ('91', '0', '1', '0', '0', '3', '99', '99', '第4局胜', '第4局胜', '[\"home\",\"guest\"]', '第4局胜', '', '999', '0', null, '1', '0', '1500883716', '1500883716');
INSERT INTO `lt_rules` VALUES ('92', '0', '1', '0', '0', '3', '99', '99', '第5局胜', '第5局胜', '[\"home\",\"guest\"]', '第5局胜', '', '999', '0', null, '1', '0', '1500883733', '1500883733');
INSERT INTO `lt_rules` VALUES ('93', '0', '1', '1', '0', '0', '3', '9', '独赢盘-第一盘', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487214048', '1487214048');
INSERT INTO `lt_rules` VALUES ('94', '0', '1', '1', '0', '0', '4', '9', '独赢', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487158306', '1487158306');
INSERT INTO `lt_rules` VALUES ('95', '0', '1', '1', '0', '0', '3', '4', '总局数:单/双', null, '[\"\\u5355\",\"\\u53cc\"]', null, null, '999', '0', null, '1', '0', '1487214048', '1487214048');
INSERT INTO `lt_rules` VALUES ('96', '0', '1', '1', '0', '0', '3', '2', '总局数:大/小', null, '[\"\\u5927\",\"\\u5c0f\",\"over\",\"under\"]', null, null, '999', '0', null, '1', '0', '1487214048', '1487214048');
INSERT INTO `lt_rules` VALUES ('97', '0', '1', '1', '0', '0', '3', '6', '让局', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487214048', '1487214048');
INSERT INTO `lt_rules` VALUES ('98', '0', '1', '1', '0', '0', '3', '3', '独赢盘', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487214047', '1487214047');
INSERT INTO `lt_rules` VALUES ('99', '0', '1', '1', '0', '0', '3', '1', '让盘', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487214047', '1487214047');
INSERT INTO `lt_rules` VALUES ('100', '0', '1', '1', '0', '0', '3', '10', '独赢', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487238713', '1487238713');
INSERT INTO `lt_rules` VALUES ('101', '0', '1', '1', '0', '0', '3', '11', '让局-第一盘', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487294987', '1487294987');
INSERT INTO `lt_rules` VALUES ('102', '0', '1', '1', '0', '0', '3', '11', '让局-第二盘', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487294987', '1487294987');
INSERT INTO `lt_rules` VALUES ('103', '0', '1', '1', '0', '0', '3', '7', '总局数:大/小-第二盘', null, '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1487294987', '1487294987');
INSERT INTO `lt_rules` VALUES ('104', '0', '1', '1', '0', '0', '3', '10', '独赢-第一盘', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487294987', '1487294987');
INSERT INTO `lt_rules` VALUES ('105', '0', '1', '1', '0', '0', '3', '10', '独赢-第二盘', null, '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1487294987', '1487294987');
INSERT INTO `lt_rules` VALUES ('106', '0', '1', '1', '0', '0', '4', '9', '独赢盘-第一节', '独赢盘-第一节', '[\"home\",\"guest\"]', null, null, '999', '0', null, '1', '0', '1491966902', '1491966902');
INSERT INTO `lt_rules` VALUES ('107', '0', '1', '1', '0', '0', '4', '6', '让球-第一节', '让球-第一节', '[\"home\",\"guest\",\"handicap\"]', null, null, '999', '0', null, '1', '0', '1491966902', '1491966902');
INSERT INTO `lt_rules` VALUES ('108', '0', '1', '1', '0', '0', '4', '7', '进球:大/小-第一节', '进球:大/小-第一节', '[\"\\u5927\",\"\\u5c0f\",\"over\",\"under\"]', null, null, '999', '0', null, '1', '0', '1491966902', '1491966902');
INSERT INTO `lt_rules` VALUES ('109', '0', '0', '1', '0', '9', '99', '1', '让分', '让分', '[\"home\",\"guest\",\"handicap\"]', '加减让分值的基础上计算输赢，打平退回本金', '<p>加减让分值的基础上计算输赢，打平退回本金</p>', '999', '0', null, '1', '0', '0', '1500900323');
INSERT INTO `lt_rules` VALUES ('110', '0', '0', '0', '0', '9', '99', '2', '独赢', '胜负', '[\"home\",\"guest\"]', '猜全场比赛含补时的最终胜负结果', '<p>猜全场比赛含补时的最终胜负结果，打平则退回本金。</p>', '999', '0', null, '1', '0', '1480318595', '1500900517');
INSERT INTO `lt_rules` VALUES ('111', '0', '1', '0', '0', '9', '99', '14', '大小', '大/小', '[\"home\",\"guest\"]', '猜全场总地图数/回合数大于/小于指定数值', '猜全场总地图数/回合数大于/小于指定数值', '999', '20000', null, '1', '0', '1481780910', '1490604098');
INSERT INTO `lt_rules` VALUES ('112', '0', '0', '0', '0', '0', '1', '14', '大小', '大/小', '[\"home\",\"guest\"]', '猜全场总进球数是大于还是小于盘口数', '<p>猜全场总进球数是大于还是小于盘口数</p>', '3', '10000', null, '1', '0', '0', '1495867252');
INSERT INTO `lt_rules` VALUES ('113', '0', '1', '0', '0', '9', '99', '16', '一血', '一血', '[\"home\",\"guest\"]', '猜这场比赛中谁先获得一血', '<p>猜这场比赛中谁先获得一血</p>', '999', '0', null, '2', '0', '1498120615', '1499679831');
INSERT INTO `lt_rules` VALUES ('114', '0', '1', '0', '0', '1', '99', '16', '一血', '一血', '[\"home\",\"guest\"]', '猜这场比赛中谁先获得一血', '<p>猜这场比赛中谁先获得一血</p>', '999', '0', null, '2', '0', '1498120642', '1500532147');
INSERT INTO `lt_rules` VALUES ('115', '0', '1', '0', '0', '2', '99', '16', '一血', '一血', '[\"home\",\"guest\"]', '猜这场比赛中谁先获得一血', '<p>猜这场比赛中谁先获得一血</p>', '999', '0', null, '2', '0', '1498120657', '1500532612');
INSERT INTO `lt_rules` VALUES ('116', '0', '1', '0', '0', '3', '99', '16', '一血', '一血', '[\"home\",\"guest\"]', '猜这场比赛中谁先获得一血', '<p>猜这场比赛中谁先获得一血</p>', '999', '0', null, '2', '0', '1498120676', '1500532390');
INSERT INTO `lt_rules` VALUES ('117', '0', '1', '0', '0', '1', '99', '17', '人头数', '人头数大小', '[\"home\",\"guest\"]', '猜这场比赛中双方总获得的人头数大于/小于固定值', '<p>猜这场比赛中双方总获得的人头数大于/小于固定值</p>', '999', '0', null, '1', '0', '1498120961', '1500900122');
INSERT INTO `lt_rules` VALUES ('118', '0', '1', '0', '0', '2', '99', '17', '人头数', '人头数大小', '[\"home\",\"guest\"]', '猜这场比赛中双方总获得的人头数大于/小于固定值', '<p>猜这场比赛中双方总获得的人头数大于/小于固定值</p>', '999', '0', null, '1', '0', '1498120981', '1500900109');
INSERT INTO `lt_rules` VALUES ('119', '0', '1', '0', '0', '3', '99', '17', '人头数', '人头数', '[\"home\",\"guest\"]', '猜这场比赛中双方总获得的人头总数', '<p>猜这场比赛中双方总获得的人头总数</p>', '999', '0', null, '2', '0', '1498121012', '1500532402');
INSERT INTO `lt_rules` VALUES ('120', '0', '1', '0', '0', '9', '99', '17', '人头数', '人头数大小', '[\"home\",\"guest\"]', '猜这场比赛中双方总获得的人头总数大于/小于固定值', '<p>猜这场比赛中双方总获得的人头数大于/小于固定值</p>', '999', '0', null, '1', '0', '1498121025', '1500900053');
INSERT INTO `lt_rules` VALUES ('121', '0', '1', '0', '0', '2', '99', '99', '第1局胜', '第1局胜', '[\"home\",\"guest\"]', '猜这场比赛中第一局获胜者', '<p>猜这场比赛中第一局获胜者</p>', '999', '0', null, '1', '0', '1498188910', '1500899428');
INSERT INTO `lt_rules` VALUES ('122', '0', '1', '0', '0', '2', '99', '99', '第2局胜', '第2局胜', '[\"home\",\"guest\"]', '猜这场比赛中第二局获胜者', '<p>猜这场比赛中第二局获胜者</p>', '999', '0', null, '1', '0', '1498189016', '1500532306');
INSERT INTO `lt_rules` VALUES ('123', '0', '1', '0', '0', '1', '99', '99', '第1局胜', '第1局胜', '[\"home\",\"guest\"]', '猜这场比赛中第一局获胜者', '<p>猜这场比赛中第一局获胜者</p>', '999', '0', null, '1', '0', '1498189029', '1500532185');
INSERT INTO `lt_rules` VALUES ('124', '0', '1', '0', '0', '9', '99', '99', '第1局胜', '第1局胜', '[\"home\",\"guest\"]', '猜这场比赛中第一局获胜者', '<p>猜这场比赛中第一局获胜者</p>', '999', '0', null, '1', '0', '1498189045', '1500532441');
INSERT INTO `lt_rules` VALUES ('125', '0', '1', '0', '0', '1', '99', '99', '第2局胜', '第2局胜', '[\"home\",\"guest\"]', '猜这场比赛中第二局获胜者', '<p>猜这场比赛中第二局获胜者</p>', '999', '0', null, '1', '0', '1498189065', '1500532198');
INSERT INTO `lt_rules` VALUES ('126', '0', '1', '0', '0', '9', '99', '99', '第2局胜', '第2局胜', '[\"home\",\"guest\"]', '猜这场比赛中第二局获胜者', '<p>猜这场比赛中第二局获胜者</p>', '999', '0', null, '1', '0', '1498189078', '1500532448');
INSERT INTO `lt_rules` VALUES ('127', '0', '1', '0', '0', '1', '99', '99', '第3局胜', '第3局胜', '[\"home\",\"guest\"]', '猜这场比赛中第三局获胜者', '<p>猜这场比赛中第三局获胜者</p>', '999', '0', null, '1', '0', '1498189089', '1500532213');
INSERT INTO `lt_rules` VALUES ('128', '0', '1', '0', '0', '2', '99', '99', '第3局胜', '第3局胜', '[\"home\",\"guest\"]', '猜这场比赛中第三局获胜者', '<p>猜这场比赛中第三局获胜者</p>', '999', '0', null, '1', '0', '1498189097', '1500532324');
INSERT INTO `lt_rules` VALUES ('129', '0', '1', '0', '0', '9', '99', '99', '第3局胜', '第3局胜', '[\"home\",\"guest\"]', '猜这场比赛中第三局获胜者', '<p>猜这场比赛中第三局获胜者</p>', '999', '0', null, '1', '0', '1498189106', '1500532455');
INSERT INTO `lt_rules` VALUES ('130', '0', '1', '0', '0', '1', '99', '99', '第4局胜', '第4局胜', '[\"home\",\"guest\"]', '猜这场比赛中第4局获胜者', '<p>猜这场比赛中第4局获胜者</p>', '999', '0', null, '1', '0', '1498189127', '1500532236');
INSERT INTO `lt_rules` VALUES ('131', '0', '1', '0', '0', '2', '99', '99', '第4局胜', '第4局胜', '[\"home\",\"guest\"]', '猜这场比赛中第4局获胜者', '<p>猜这场比赛中第4局获胜者</p>', '999', '0', null, '1', '0', '1498189136', '1500532338');
INSERT INTO `lt_rules` VALUES ('132', '0', '1', '0', '0', '9', '99', '99', '第4局胜', '第4局胜', '[\"home\",\"guest\"]', '猜这场比赛中第四局获胜者', '<p>猜这场比赛中第四局获胜者</p>', '999', '0', null, '1', '0', '1498189144', '1500532462');
INSERT INTO `lt_rules` VALUES ('133', '0', '1', '0', '0', '1', '99', '99', '第5局胜', '第5局胜', '[\"home\",\"guest\"]', '猜这场比赛中第5局获胜者', '<p>猜这场比赛中第5局获胜者</p>', '999', '0', null, '1', '0', '1498189155', '1500532253');
INSERT INTO `lt_rules` VALUES ('134', '0', '1', '0', '0', '2', '99', '99', '第5局胜', '第5局胜', '[\"home\",\"guest\"]', '猜这场比赛中第5局获胜者', '<p>猜这场比赛中第5局获胜者</p>', '999', '0', null, '1', '0', '1498189164', '1500532350');
INSERT INTO `lt_rules` VALUES ('135', '0', '1', '0', '0', '9', '99', '99', '第5局胜', '第5局胜', '[\"home\",\"guest\"]', '猜这场比赛中第五局获胜者', '<p>猜这场比赛中第五局获胜者</p>', '999', '0', null, '1', '0', '1498189172', '1500532469');
