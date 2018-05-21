<?php
define('PAD_GAME_ID','PAD-ENG-20180510-2197');
define("LANG","glod");//语言包，金币版
//====
define("DEVICE_MOBILE",1);//手机版
define("DEVICE_PC",2);//电脑PC版
define("DEVICE_TABLET",3);//平板电脑
define("BET_MONEY_MAX",10000);//单笔投注上限
//====
define("SYS_COMPANY",1);//系统发布赔率对应的博彩公司ID
define("SYS_USER_ID",1);//系统用户ID
define("MIN_DEPOSIT",1);//最小投注金额
//====
define("STATUS_YES",1); //是
define("STATUS_NO",0); //否
//====
define("STATUS_UNDONE",0); //未完成
define("STATUS_COMPLETE",1); //完成
define("STATUS_USE",2); //使用

define("STATUS_ENABLED",1); //可用\通过
define("STATUS_DISABLED",2); //不可用\禁用
//圈子帖子状态
define("THREAD_STATUS_VERIFY",1);//待审核
define("THREAD_STATUS_ENABLED",2);//审核通过
define("THREAD_STATUS_DELETE",3);//删除
define("THREAD_STATUS_DISABLED",4);//审核不通过
//平台
define("PLATFORM_IOS",1);
define("PLATFORM_ANDROID",2);
define("PLATFORM_H5",3);
define("PLATFORM_PC",4);
//====
define("HOME",1); //主队
define("GUEST",2); //客队
define("SAME",3); //平
//====
define("MEMBER",1); //用户
define("SYSTEM",2); //系统
//====赔率方式
define("ODDS_TYPE_ASIAN",1); //亚盘
define("ODDS_TYPE_EUROPE",2); //欧赔

//====玩法类型-暂定
define("RULES_TYPE_ASIAN",1);//让球、亚盘
define("RULES_TYPE_EUROPE",2);//欧赔、胜平负、独赢
define("RULES_TYPE_HOME_GOALS",3);//主进球
define("RULES_TYPE_GUEST_GOALS",4);//客进球
define("RULES_TYPE_ALL_SCORE",5);//全场比分
define("RULES_TYPE_HALF_SCORE",6);//半场比分
define("RULES_TYPE_ALL_YELLOW",7);//全场黄牌
define("RULES_TYPE_FIRST_GOALS",8);//最先进球
define("RULES_TYPE_ALL_GOALS",9);//全场进球
define("RULES_TYPE_BODAN",10);//比分，波胆
define("RULES_TYPE_BODAN_COMB",11);//比分组合，波胆组合
define("RULES_TYPE_SINGLE_DOUBLE",12);//单双
define("RULES_TYPE_MAX_GOALS",13);//上/下半场进球数
define("RULES_TYPE_OU",14); //大小
define("RULES_TYPE_COMO",15); //组合
define("RULES_TYPE_FIRST_BLOOD",16); //一血
define("RULES_TYPE_KILL_NUM",17); //人头大小
define("RULES_TYPE_OTHER",99); //其它

//====项目,赛事类型，值不能大于100，防止小游戏冲突
define("GAME_TYPE_FOOTBALL",1); //足球
define("GAME_TYPE_WCG",99); //电竞

define("GAME_TYPE_ESPECIALLY",100); //特别
define("GAME_TYPE_ENT",998); //娱乐
define("GAME_TYPE_HOT_ARENA",999); //热门擂台

//====比赛状态
define("PLAT_STATUS_NOT_START",1);//未开始
define("PLAT_STATUS_START",2);//进行中
define("PLAT_STATUS_INTERMISSION",3);//中场休息
define("PLAT_STATUS_END",4);//结束
define("PLAT_STATUS_EXC",5);//延期
define("PLAT_STATUS_SUSP",6);//停赛
define("PLAT_STATUS_WAIT",7);//待定
define("PLAT_STATUS_CUT",8);//腰斩
define("PLAT_STATUS_STATEMENT_BEGIN",99);//结算中
define("PLAT_STATUS_STATEMENT",100);//擂台已结算
//====比赛直播类型
define("PLAY_LIVE_VIDEO",1); //视频直播
define("PLAY_LIVE_TEXT",2); //文字直播

//====资金动向类型
define("FUNDS_CLASSIFY_DEP",1);//投注
define("FUNDS_CLASSIFY_ARE",2);//摆擂
define("FUNDS_CLASSIFY_REC",3);//充值
define("FUNDS_CLASSIFY_WIN_DEP",4);//投注
define("FUNDS_CLASSIFY_WIN_ARE",5);//擂主收益
define("FUNDS_CLASSIFY_WD",6);//提现
define("FUNDS_CLASSIFY_ADD_ARE",7);//追加保证金
define("FUNDS_CLASSIFY_DIS_ARE",8);//封禁擂台
define("FUNDS_CLASSIFY_AGENT_DEP",9);//代理投注提成
define("FUNDS_CLASSIFY_AGENT_RECHARGE",10);//庄家给代理充值 -支出
define("FUNDS_CLASSIFY_AGENT_RECHARGE_IN",11);//代理充值 -收入
define("FUNDS_CLASSIFY_AGENT_RECHARGE_OUT",14);//代理给其它用户充值 -支出
define("FUNDS_CLASSIFY_AGENT_SETTLE",12);//代理线下结算
define("FUNDS_CLASSIFY_AGENT_SETTLE_ONLINE",13);//代理线上结算
define("FUNDS_CLASSIFY_AGENT_BUY_GOLD",15);//从代理购买金币
define("FUNDS_CLASSIFY_TASK",16);//任务
define("FUNDS_CLASSIFY_CREDIT",17);//授信
define("FUNDS_CLASSIFY_BANK_DEC",18);//金库取款
define("FUNDS_CLASSIFY_BANK_INC",19);//金库存款
define("FUNDS_CLASSIFY_PLAY_GAME",20);//玩小游戏
define("FUNDS_CLASSIFY_SYS_REC",99);//系统充值
define("FUNDS_CLASSIFY_SYS_DED",100);//系统扣款
define("FUNDS_CLASSIFY_FREEZE",101);//冻结金币
define("FUNDS_CLASSIFY_VIEW_REC", 102);//查看收益
define("FUNDS_CLASSIFY_VIEW_DED", 103);//查看支出
define("FUNDS_CLASSIFY_UNFREEZE", 104);//解冻金币
define("FUNDS_CLASSIFY_GIFT_GOLD", 105);//赠送金币
define("FUNDS_CLASSIFY_BROKERAGE", 106);//推广佣金
//====资金类型
define("FUNDS_TYPE_GOLD",1);//金币
define("FUNDS_TYPE_MONEY",2);//金钱
//====投注状态
define("DEPOSIT_WIN",1);//中奖
define("DEPOSIT_LOSE",2);//未中奖
define("DEPOSIT_NOT_START",3);//未开奖
define("DEPOSIT_CANCEL",4);//取消
define("DEPOSIT_SAME",5);//平手,退全部本金
define("DEPOSIT_LOST_HALF",6);//输一半本金
define("DEPOSIT_WIN_HALF",7);//赢一半
//====擂台状态
define("ARENA_START",1);//投注中
define("ARENA_SEAL",2);//封擂，禁投
define("ARENA_PLAY",3);//比赛开始
define("ARENA_END",4);//结束
define("ARENA_DIS",5);//封禁
define("ARENA_DEL",6);//删除
define("ARENA_STATEMENT_BEGIN",10);//结算中
define("ARENA_STATEMENT_END",11);//结算完成
define("ARENA_STATEMENT_ERROR",12);//结算失败
//====擂台类型
define("ARENA_CLASSIFY_GOLD",1); //金币局
define("ARENA_CLASSIFY_CREDIT",2); //征信局
//====擂台可见状态
define("ARENA_DISPLAY_ALL",1); //所有人可见
define("ARENA_DISPLAY_FRIENDS",2); //好友
define("ARENA_DISPLAY_CODE",3); //邀请码
//====定义秘钥
define("DEFAULT_KEY",'gm9?lh=ngV.w86!Q');//默认加密秘钥
define("ENCODE_KEY",'e4.D`Y]o');//默认加密秘钥
define("URL_ENCODE_KEY",'2XXn=?oxCZ.S5!iP');//默认地址加密秘钥
define("DATA_ENCRYPT_KEY",'f?Xn4.Dx]o.SgV!X');//数据加密秘钥，此值请勿修改

//====返回CODE说明
define("NOT_LOGIN",-9999); //未登录
define("NOT_GOLD",-9998);//金币不足
define("NOT_FRIENDS",-9997);//好友查看
define("NOT_INVITE",-9996);//需要邀请码
//====系统日志类型
define("SYSTEM_LOG_METHOD",1);//访问日志
define("SYSTEM_LOG_OPERATION",2);//操作日志
define("SYSTEM_LOG_FUNDS",3);//资金日志
define("SYSTEM_LOG_STATEMENT",4);//系统自动结算
//====用户日志
define("USER_LOG_LOGIN",1);//登录日志
define("USER_LOG_OPT",2);//操作日志
define("USER_LOG_PASSWORD",3);//修改密码
define("USER_LOG_KICK",4);//游戏自动封号
//====系统收支类别
define("SYSTEM_INCOME_COM", 1);//佣金
define("SYSTEM_INCOME_DEPOSIT", 2);//保证金
define("SYSTEM_INCOME_ARENA_END", 3);//擂台结算
define("SYSTEM_INCOME_ARENA_START", 4);//开设擂台
define("SYSTEM_INCOME_ARENA_BETTING", 5);//投注
//结算 状态
define("STATEMENT_STATUS_SUCCESS",1);//结算成功
define("STATEMENT_STATUS_ERROR",2);//结算失败
//用户状态类型
define("FOLLOW_TYPE_USER",1); //用户
define("FOLLOW_TYPE_TEAM",2); //球队


//====消息接收对象
define("MESSAGE_RECEIVE_ALL",1);//全部用户
define("MESSAGE_RECEIVE_ASSIGN",2);//指定用户
define("MESSAGE_RECEIVE_DITCH",3);//渠道

define("MESSAGE_QUEUE_TYPE_SEAL",1);//后台封擂
define("MESSAGE_QUEUE_TYPE_DISABLED",2);//擂台取消
define("MESSAGE_QUEUE_TYPE_STATEMENT",3);//擂台结算
define("MESSAGE_QUEUE_TYPE_DELETE",4);//擂台删除
define("MESSAGE_QUEUE_TYPE_SMALL_GAME_OFFLINE",5);//小游戏时掉线

//====TOP奖金榜
define('TOP_BONUS_TODAY',1);//昨日
define('TOP_BONUS_WEEK',2);//一周
define('TOP_BONUS_MONTH',3);//一月
define('TOP_BONUS_THREE_MONTH',4);//三月
define('TOP_BONUS_SIX_MONTH',5);//六月

//比赛热度
define('PLAY_HOT_LM',1);//冷门
define('PLAY_HOT_PT',2);//普通
define('PLAY_HOT_RM',3);//热门

/******代理******/
//===擂台方式
define("AGENT_USER_USERNAME_LENGTH",20); //用户名长度
define("AGENT_USER_PASSWORD_LENGTH",20); //密码长度
define("AGENT_USER_ARENA_TYPE_ALL",1); //全部
define("AGENT_USER_ARENA_TYPE_SINGLE",2); //单个
/**odds表modify设置*/
define("ODDS_ZGZCW_MODIFY", 1); //足彩网采集，需更新
define("ODDS_ZGZCW_UNMODIFY", 2); //足彩网采集，不更新
define("ODDS_USER_UNMODIFY", 3); //人工添加，不更新

/** 用户中奖跑马灯推送阀值 **/
define("MIN_USER_EARN_MONEY", 10);
/**投注失败次数限制**/
define('RAISE_ERROR_NUM',3);