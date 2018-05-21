#定时任务执行列表
Games/storePeak                 库存封值统计开始                        每天凌晨1:00        可接收参数ymd
Games/goldWinLoseAbsolute       玩家输赢绝对值监控(房间金币)              每3分钟一次
Games/platformGold              平台对账计划任务统计                     每天晚上 00:01
Games/statsGameGold             游戏金币统计(游戏金币输赢)                每天2：00分
Games/statics                   游戏统计                               每40分执行
Games/userEarly                 游戏观察者监控                          每3分钟一次


Misc/statBase                   1天基础数据统计                            每一天执行一次（统计次日数据相关数据 执行时间凌晨2点）
Misc/new_user                   新增用户日统计                             每20分
Misc/user_active_day            用户活跃日统计                             每小时 20分
Misc/user_active_week           用户活跃周统计                             每天 00:01
Misc/user_active_month          用户活跃月统计                             每月1日 00:01
Misc/user_keep                  用户留存日统计                             每天 04:00
Misc/user_leave                 用户流失日统计                             必须为每天00：01分
*Misc/user_lost                  用户使用流失统计                             每天 01:01
*Misc/statBaseHours             1小时基础统计总                            每小时
*Misc/arena                     擂台统计                                  每天凌晨1:00

Misc/user_online_detail         用户在线状态详情                           每5分钟一次
Misc/user_online_account         在线人数统计日                           每5分钟一次
Misc/user_online_time         在线时长                           每天 05:10


Misc/withdrawal_timeout         提现订单超时处理                  每分钟运行一次
Misc/finance_total         财务汇总统计                  每小时

Recharge/queue

================
用户在线状态详情 lt_stat_online_detail
用户主题 - 在线时长 lt_stat_online_time
用户主题 - 在线用户数 lt_stat_online_user






==================数据采集=====================
odds/odds_today 		足球比赛及赔率采集               		每10分钟一次
odds/oneData    		188网站采集足球大小赔率   		每5分钟一次
live/index      		足球比分实时更新                   		每5分钟一次
oddszgzcw/index			足彩网足球赔率采集			每10分钟一次
dianjing/index			188网站采集电竞比赛及赔率		每5分钟一次
basketball/index		188网站采集篮球比赛及赔率		每10分钟一次
pvp/index				王者荣耀官网采集比赛及赔率		每10分钟一次
leying/index			乐盈电竞数据采集			每10分钟一次
leying/end				乐盈电竞比分采集			每10分钟一次


#history/getPlayHistory		欧洲足球联赛历史对战数据			每天01:00执行一次
#history/getPlayHistory1		美洲足球联赛历史对战数据采集		每天01:00执行一次
#history/getPlayHistory2		亚洲足球联赛历史对战数据采集		每天01:00执行一次
#history/getPlayHistory3		非洲足球联赛历史对战数据采集		每天01:00执行一次
#history/countryHistoryData	足球国家队历史对战数据			每天01:00执行一次


================数据汇总================
acount/fundsYestodayCount		昨日数据汇总		每天02:00执行一次
acount/fundsWeekCount			一周数据汇总		每天02:00执行一次
acount/fundsMonthCount			一月数据汇总		每天02:00执行一次
acount/fundsQuarterCount      	季度数据汇总		每天02:00执行一次
acount/fundsHalfYearCount		半年数据汇总		每天02:00执行一次
acount/getMostWinData			最多连红数据汇总	每天02:00执行一次
acount/recommendWin				推荐中奖数据汇总	每天02:00执行一次
acount/fundsAllCount			全部数据汇总		每天02:00执行一次
cachematch/index				缓存				每三分钟一次




#库存封值统计开始,每天凌晨1:00
00 01 * * * flock -xn /var/run/Games_storePeak.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/storePeak >> /var/log/51bet/Games_storePeak.log 2>&1"

#玩家输赢绝对值监控(房间金币),每3分钟一次
*/3 * * * * flock -xn /var/run/Games_goldWinLoseAbsolute.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/goldWinLoseAbsolute >> /var/log/51bet/Games_goldWinLoseAbsolute.log 2>&1"

#平台对账计划任务统计,每天晚上 00:01
01 00 * * * flock -xn /var/run/Games_platformGold.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/platformGold >> /var/log/51bet/Games_platformGold.log 2>&1"

#游戏金币统计(游戏金币输赢),每天2：00
00 02 * * * flock -xn /var/run/Games_statsGameGold.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/statsGameGold >> /var/log/51bet/Games_statsGameGold.log 2>&1"

#游戏统计,每40分执行
*/40 * * * * flock -xn /var/run/Games_statics.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/statics >> /var/log/51bet/Games_statics.log 2>&1"

#1天基础数据统计,每一天执行一次（统计次日数据相关数据 执行时间凌晨2点）
00 02 * * * flock -xn /var/run/Misc_statBase .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/statBase  >> /var/log/51bet/Misc_statBase.log 2>&1"

#新增用户日统计,每20分
*/20 * * * * flock -xn /var/run/Misc_new_user .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/new_user  >> /var/log/51bet/Misc_new_user.log 2>&1"

#用户活跃日统计,每小时 20分
20 */1 * * * flock -xn /var/run/Misc_user_active_day .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_active_day  >> /var/log/51bet/Misc_user_active_day.log 2>&1"

#用户活跃周统计,每天 00:01
01 00 * * * flock -xn /var/run/Misc_user_active_week .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_active_week  >> /var/log/51bet/Misc_user_active_week.log 2>&1"

#user_active_month,每月1日 00:01
01 00 1 */1 * flock -xn /var/run/Misc_user_active_month .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_active_month  >> /var/log/51bet/Misc_user_active_month.log 2>&1"

#用户留存日统计,每天 04:00
00 04 * * * flock -xn /var/run/Misc_user_keep .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_keep  >> /var/log/51bet/Misc_user_keep.log 2>&1"

#用户流失日统计,必须为每天00：01分
01 00 * * * flock -xn /var/run/Misc_user_leave .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_leave  >> /var/log/51bet/Misc_user_leave.log 2>&1"

#用户在线状态详情,每5分钟一次
*/5 * * * * flock -xn /var/run/Misc_user_online_detail .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_online_detail  >> /var/log/51bet/Misc_user_online_detail.log 2>&1"

#在线时长,每天 05:10
10 05 * * * flock -xn /var/run/Misc_user_online_time .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_online_time  >> /var/log/51bet/Misc_user_online_time.log 2>&1""

#在线人数统计,每5分钟一次
*/5 * * * * flock -xn /var/run/Misc_user_online_account .lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_online_account  >> /var/log/51bet/Misc_user_online_account.log 2>&1"


#用户使用流失统计,每天 01:01
01 01 * * * flock -xn /var/run/Misc_cuser_lost.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/user_lost  >> /var/log/51bet/Misc_cuser_lost.log 2>&1"

#1小时基础统计总成功,每3小时一次
* */1 * * * flock -xn /var/run/Misc_statBaseHours.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/statBaseHours   >> /var/log/51bet/Misc_statBaseHours.log 2>&1"


#用擂台统计,每天 01:01
01 01 * * * flock -xn /var/run/Misc_arena.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/arena >> /var/log/51bet/Misc_arena.log 2>&1"


#提现订单超时处理,每分钟运行一次
*/1 * * * * flock -xn /var/run/Misc_withdrawal_timeout.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/withdrawal_timeout >> /var/log/51bet/Misc_withdrawal_timeout.log 2>&1"


#财务汇总统计,每小时
* */1 * * * flock -xn /var/run/Misc_finance_total.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/finance_total >> /var/log/51bet/Misc_finance_total.log 2>&1"

#财务汇总统计,每天0:30
30 00 * * * flock -xn /var/run/Misc_finance_total.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Misc/finance_total_torr >> /var/log/51bet/Misc_finance_total.log 2>&1"


#房间机器人,每分钟
*/1 * * * * flock -xn /var/run/arena_bet_android.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Arena/bet_android >> /var/log/51bet/arena_bet_android.log 2>&1"

#小游戏输赢同步至用户表，每1小时执行一次
* */1 * * * flock -xn /var/run/Games_total_result.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/updateTotalResultToLeitai >> /var/log/51bet/totalResultUpdate.log 2>&1"

#在线用户信息汇总,每分钟
*/1 * * * * flock -xn /var/run/online_user_data.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/onlineUserReuslt >> /var/log/51bet/online_user_data.log 2>&1"


#小游戏黑名单监控,每分钟
*/1 * * * * flock -xn /var/run/game_kill_score.lock -c "cd /opt/web/leitai/public_51bet/;/opt/php56/bin/php croncut0888.php Games/gameKillScoreLocker >> /var/log/51bet/game_kill_score.log 2>&1"


##################20180409最新####################
*/10 * * * * flock -xn /var/run/oddszgzcw_index.lock -c "cd /data/web/www/Football/public_51bet/;/usr/local/php/bin/php croncut0888.php oddszgzcw/index >> /var/log/51bet/oddszgzcw_index.log 2>&1"
*/10 * * * * flock -xn /var/run/odds_odds_today.lock -c "cd /data/web/www/Football/public_51bet/;/usr/local/php/bin/php croncut0888.php odds/odds_today >> /var/log/51bet/odds_odds_today.log 2>&1"
*/10 * * * * flock -xn /var/run/statement_football.lock -c "cd /data/web/www/Football/public_51bet/;/usr/local/php/bin/php croncut0888.php statement/football >> /var/log/51bet/statement_football.log 2>&1"
05 01 * * * flock -xn /var/run/misc_arena.lock -c "cd /data/web/www/Football/public_51bet/;/usr/local/php/bin/php croncut0888.php misc/arena >> /var/log/51bet/misc_arena.log 2>&1"


