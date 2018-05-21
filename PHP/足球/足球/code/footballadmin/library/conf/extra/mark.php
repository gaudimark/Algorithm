<?php

/**
 * api返回标头，用于定位位置
 */

return [
    'test' => '12345',
    'system' => '10000',
    'token' => '10001',
    'token_expire' => '10002', //token过期
    'not_login' => '10003', //未登录
    'srv_time' => '10004',//返回当前服务器时间
    'csrf' => '10005',//防XXS攻击，验证csrf失败
    'alert' => '10006',//系统弹框
    'unverified' => '10007',//未验证
    'captcha_rule_call' => '10008',//校验成功获取数据

    'word_captcha' => '11001',
    //用户中心
    'user' => [
        'login' => 20000,
        'register' => 20001,
        'visitor' => 20002,
        'rand_nickname' => 20003,
        'send_bind_mail' => 20004,
        'bind_mail' => 20005,
        'modify_pwd' => 20006,
        'forgot_pwd' => 20007,
        'forgot_pwd_code' => 20008,
        'reset_pwd' => 20009,


        'logout' => 29998, //退出
        'qlogin' => 29999, //快速登录

        'info_show' => 20010, //获取用户信息
        'my_add_friend' => 20011, //添加好友
        'my_un_friend' => 20012, //解除好友
        'my_add_follow' => 20013, //添加关注
        'my_un_follow' => 20014, //解除关注
        'info_record' => 20015, //用户战绩
        'my_friend' => 20016, //好友列表
        'my_apply_friend_total' => 20017, //好友申请数
        'info_search' => 20022, //用户搜索
        'my_follow' => 20018, //用户关注
        'my_bet' => 20019, //我的投注
        'my_bet_detail' => 20020, //我的投注
        'my_modify_bet_price' => 20021, //我的投注
        'my_apply_friend_list' => 20023, //好友申请列表
        'my_apply_friend' => 20024, //处理好友申请
        'my_message' => 20025, //我的消息
        'my_check_in' => 20026, //签到
        'my_sys_task' => '20027',//触发系统任务
        'my_avatar' => '20028',//更新用户头像
        'my_buy_bet' => '20029',//购买投注单查看
        'my_homeowner' => '20030',//申请成为房主
        'my_un_homeowner' => '20031',//取消成为房主
        'my_gift_alms' => '20032',//用户领取救济金
        'my_info' => '20033',//用户信息
        'my_new_user_reward' => '20034',//领取新手奖励
        'my_withdraw' => '20035',//兑换订单
        'my_message_del' => '20036',//删除或清空消息
        'my_promoter' => '20037',//推广有礼
        'my_receive_brokerage' => '20038',//领取推广佣金

        'send_sms' => '20050', //发送短信
        'reg_mobile' => '20051', //手机注册第一步
        'reg_mobile_save' => '20052', //手机注册第二步
        'forget_pwd_mobile_send' => '20053', //手机找回密码-发送验证码
        'forget_pwd_mobile_reset' => '20054', //手机找回密码-重置密码

        'my_re_mobile_check' => '20056',//更换手机号码,校验验证码
        'my_re_mobile_save' => '20057',//更换手机号码,校验验证码
        'my_mobile_send_not_exist' => '20058',//发送验证码,手机号码不能存在
        'my_mobile_send_code_exist' => '20059',//发送验证码,手机号码必须存在
        'info_game_vote' => 20060,//调查

        'my_log_funds' => 20100, //帐户记录

        'arena_lists' => 20200, //我的擂台
        'arena_detail' => 20201, //擂台详情
        'arena_publish_get' => 20202, //参考赔率
        'arena_odds_list' => 20203,//获取擂台赔率
        'arena_publish_save' => 20204,//发布擂台
        'arena_bet_list' => 20205,//获取擂台投注用户
        'arena_modify_odds' => 20206,//修改赔率赔率
        'arena_append_deposit' => 20207,//追加保证金
        'arena_seal' => 20208,//停止投注
        'arena_unseal' => 20209,//开启投注
        'arena_conf' => 20210,//修改擂台
        'arena_auth_user_list' => 20211,//授信列表
        'arena_auth_user_add' => 20212,//添加授信用户
        'arena_auth_user_info' => 20213,//添加授信用户
        //'arena_auth_user_info' => 20214,//授信用户详情
        'arena_recommend' => 20215,//推荐擂台
        'arena_auth_user_cancel' => 20216,//撤销授信用户

        'agent_user' => 20300,//代理用户列表
        'agent_add_user' => 20301,//添加代理用户
        'agent_user_detail' => 20302,//代理用户详情
        'agent_arena_lists' => 20303,//主代擂台列表
        'agent_modify_rate' => 20304,//修改提成
        'agent_add_arena' => 20305,//添加擂台
        'agent_rm_arena' => 20306,//移除擂台
        'agent_unsettlement' => 20307,//代理结算
        'agent_log' => 20308,//代理日志
        'agent_agent_arena_lists' => 20309,//代理的擂台列表
        'win_log' => 20310,//收益日志
        //金库
        'bank_password' => '20500',//设置密码
        'bank_login' => '20501', //登录
        'bank_inc' => '20502', //存款
        'bank_dec' => '20503', //取款
        'bank_modify_pwd' => '20504', //修改密码
        'bank_log' => '20505', //日志
        'bank_send_sms' => '20506', //找回密码，发送验证码
        'bank_forget_modify_pwd' => '20507', //找回密码
        //提现
        'withdrawal_info' => '21000',
        'withdrawal_bind' => '21001',
        'withdrawal_send' => '21002',
        //充值
        'recharge_conf' => '22000',
        'recharge_pay' => '22001',
        'recharge_apple' => '22002',
        //活动
        'active_lists' => '22100',
        'active_one' => '22101',
    ],


    //项目
    'play' => [
        'lists' => '30001',//获取比赛列表（未开始）
        'rule' => '30002',//玩法大厅
        'info' => '30003', //获取比赛信息
        'arena' => '30004', //获取比赛下的擂台列表
        'all' => '30005', //获取比赛列表
        'lists2' => '30006', //获取比赛列表
        'team_play_list' => '30007', //获取指定队伍的比赛列表
        'odds' => '30008', //赔率列表
    ],
    //擂台
    'arena' => [
        'lists' => '40001',//擂台列表
        'info' => '40002',//擂台详情
        'chk_invite' => '40003',//擂台邀请码检查
        'bet' => '40004',//擂台投注
        'buy_bet' => '40005',//购买投注单查看
        'recommend' => '40006',//推荐擂台
        'odds' => '40007',//获取擂台最新赔率
        'status' => '40008',//获取擂台状态及结果
        'check_credit' => '40009',//擂台授信检查
    ],
    //代理
    'agent' => [
        'login' => '50000',  //登录
        'logout' => '50001',  //退出登录
        'modify_pwd' => '50002',  //修改密码
        'my_arena' => '50003',  //我的-擂台列表
        'my_bet_user' => '50004',  //我的-投注用户列表
        'arena_lists' => '50005',  //我的-投注用户列表
        'check_mark' => '50100',  //代理推广页检查代理唯一码
    ],


    //公共
    'common' => [
        'item' => '90000',//获取可玩项目
        'rules' => '90001',//获取项目玩法
        'game' => '90002',//获取游戏
        'match' => '90003',//获取项目赛事
        'chips' => '90004',//默认筹码列表
        'match_play' => '90005',//有比赛的赛事列表
        'sys_task' => '90006',//系统任务
        'default_avatar' => '90007',//系统默认头像
        'notice' => '90008',//系统公告
        'handicap' => '90009',//玩法盘口
        'win_top' => '90010',//财富榜
        'god_top' => '90011',//大神
        'award' => '90012',//开奖
        'play_hot' => '90013',//热门比赛
        'forum_list' => '90014',//圈子列表
        'first_charge' => '90015',//首充
        'bank' => '90016',//银行列表
        'lost' => '90017',//银行列表
        'get_word_captcha' => '90018',//图形验证码
        'check_word_captcha' => '90019',//图形验证码
        'sport_layout' => '90020',//竞技模块
        'send_user_sms' => '90021',//发送短信
        'check_user_sms' => '90022',//验证发送短信
        'ask_captcha' => '90023',//问题验证码
        'check_ask_captcha' => '90024',//问题检验验证码
        'rand_captcha' => '90025',//随机验证码


        'customer_ask' => 90100,//客服-提问
        'customer_lists' => 90101,//客服-提问回复对话列表
        'customer_faq' => 90102,//客服-推荐问题

        'customer_agent_ask' => 90110,//代理-提问
        'customer_agent_lists' => 90111,//代理-对话列表


        'server_list' => '90200',//游戏服务器列表

    ],

    //PAD
    'pad' => [
        'recharge' => '92000',
        'query' => '92001',
        'force' => '92002',
        'callbackpoints' => '92003',
    ],
    //搜索
    'search' => [
        'q' => '91000', //搜索
    ],

    'push' => [
        'bind_uid' => '93000', // 绑定用户
        'bind_group' => '93001', // 绑定群组
        'send_group' => '93002', // 向群组发送消息
    ],

    'socket' => [
        'to_pong' => '92000', // socket心跳
        'to_connect' => '92001', // socket连接成功
        'to_send_uid' => '92002', // socket像用户发消息
        'to_send_group' => '92003', // socket像用户组发消息
        'to_send_all' => '92004', // socket像用户组发消息

        'to_send_customer' => '92010', // 客服回复
        'to_send_message' => '92011', // 邮箱、系统消息
        'to_send_friend' => '92012', // 好友申请
        'to_send_gold_change' => '92013', // 用户金币变化
        'to_send_gold_update' => '92014', // 用户金币更新
        'to_send_agent_customer' => '92015', // 代理-客服回复

        'ditch_tg_recharge_total' => '98000', // 推广员渠道包增加充值活动

        'to_off' => '92999', // 服务器主动断开socket连接
        'to_all_notice' => '99997', // 全服公告
        'to_stop' => '99998', // 停服
        'alert' => '99999',// socket系统弹框
    ],
];