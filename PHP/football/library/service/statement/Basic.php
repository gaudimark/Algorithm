<?php

namespace library\service\statement;

use library\service\Image;
use library\service\Log;
use library\service\Misc;
use library\service\Pad;
use library\service\Play;
use library\service\Socket;
use library\service\User;
use think\Cache;
use think\Db;
use think\Exception;

class Basic {

    public $arenaIsCredit = false; //是否是征信局
    public $userList = [];
    public $error = '';
    public $prizePool = 0; //奖惩
    public $winTotal = 0; //赢的人数
    public $brokerage = 0; //佣金总额
    public $arena = null;
    public $ruleType = null;
    public $ruleId = null;
    public $arenaId = 0;
    public $play = null;
    public $teams = null;
    public $match = null;
    private $userWinData = []; //用户中奖数据，用于结算完成后socket推送
    private $userLoseData = []; //用户未中奖数据，用于结算完成后socket推送
    private $userTempList = []; //用户中奖流水

    public function reset() {
        $this->arenaIsCredit = false; //是否是征信局
        $this->userList = [];
        $this->error = '';
        $this->prizePool = 0; //奖惩
        $this->winTotal = 0; //赢的人数
        $this->brokerage = 0; //佣金总额
        $this->arena = null;
        $this->ruleType = null;
        $this->ruleId = null;
        $this->arenaId = 0;
    }

    public function setArena($arena) {
        $arena['odds'] = @json_decode($arena['odds'], true);
        $this->ruleType = $arena['rules_type'];
        $this->ruleId = $arena['rules_id'];
        $this->arena = $arena;
        $this->arenaId = $arena['id'];
        $this->prizePool = $arena['deposit'] + $arena['bet_money']; //奖池 = 擂主押金+投注金额
        $this->arenaIsCredit = $arena['classify'] == ARENA_CLASSIFY_CREDIT ? true : false;
        //var_dump($this->prizePool);
    }

    public function setPlay($play) {
        $this->play = $play;
        $this->teams = (new \library\service\Play())->getTeams($play['id'], ['id', 'name']);
        $this->match = getMatch($this->play['match_id']);
    }

    public function toCalc($result) {
        if ($this->arenaIsCredit) {
            //return $this->toCalcCredit($result);
        } else {
            return $this->toCalcGold($result);
        }
    }

    /**
     * 金币局结算
     * @param $result
     * @return bool
     */
    public function toCalcGold($result) {
        if ($this->prizePool < 0) {
            $this->error = "结算失败，结算结果奖池将：{$this->prizePool}";
            return false;
        }
        // var_dump($this->prizePool);
        //throw new Exception('xxxx');
        //擂台佣金比例
        $arenaBrok = isset($this->arena['brok']) ? $this->arena['brok'] : floatval(config("system.sys_player_brok"));
        //擂台佣金金币
        $arenaBrokNumber = 0;

        if (isset($result['fee']) && $result['fee'] > 0) {
            $this->sysWin($result['fee']);
            Log::sysIncome($result['fee'], lang(40502, ['arenaId' => $this->arena['id']]), [
                'brok' => $arenaBrok,
                'arena_id' => $this->arenaId,
                'number' => $result['win'],
                    ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_COM);
        }

        $total = $this->arena['deposit'] + $this->arena['bet_money'];
        //获取擂台合伙人保证金数目
        $deposit = Db::name("arena_deposit_detail")->field("has_sys,user_id,sum(number) as number")->group("user_id")->where(['arena_id' => $this->arenaId])->select();
        $isWin = false;
        $winTotal = $this->prizePool - $this->arena['deposit'];
        $fee = 0;
        //$sys_maker_brok = intval(config("system.sys_maker_brok"));
        if ($winTotal > 0 && $arenaBrok) {
            //$sys_maker_brok = $sys_maker_brok / 100;
            //$arenaBrokNumber = sprintf("%.2f",$winTotal * $arenaBrok);
            $arenaBrokNumber = numberFormat($winTotal * $arenaBrok, 2);
            $this->prizePool = $this->prizePool - $arenaBrokNumber; //奖池扣除佣金
            $this->sysWin($arenaBrokNumber);
            Log::sysIncome($arenaBrokNumber, lang(40501, ['arenaId' => $this->arena['id']]), [
                'brok' => $arenaBrok,
                'arena_id' => $this->arenaId,
                'number' => $winTotal,
                    ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_COM);
            $winTotal = $winTotal - $arenaBrokNumber; //擂主收益要扣除佣金
        }

        if ($deposit) {
            foreach ($deposit as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user) {
                    $this->error = "无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})";
                    return false;
                    //throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                //$money = sprintf("%.2f", $val['number'] / $this->arena['deposit']) * $this->prizePool;
                $money = sprintf("%.2f", ($val['number'] / $this->arena['deposit']) * $this->prizePool);
                $msg = lang('40500', ['arenaId' => $this->arena['id']]);
                if ($val['user_id'] == SYS_USER_ID || $user['has_robot']) {//系统用户和机器人都不进行结算
                    Log::sysIncome($money, $msg, [
                        'brok' => $arenaBrok,
                        'arena_id' => $this->arenaId,
                        'number' => $val['number'],
                        'money' => $money,
                            ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_ARENA_END);
                } else {
                    $arena_win = $money - $val['number'];
                    Db::name("user")->where(['id' => $val['user_id']])->update(
                            [
                                //'gold' => ['exp', "gold+{$money}"],
                                'arena_money' => ['exp', "arena_money+{$arena_win}"],
                            ]
                    );
                    $this->winUserSocketTemp($val['user_id'], $money, $val['number']);
                    //(new Socket())->userGold($val['user_id'],$money);
                    Log::UserFunds(
                            $val['user_id'], FUNDS_CLASSIFY_WIN_ARE, FUNDS_TYPE_GOLD, $money, $user['gold'], $user['gold'] + $money, $msg, ['money' => $money, 'fee' => $arenaBrokNumber, 'data' => $val]);

                    (new User())->setUserDetailCache($val['user_id']);
                }
            }
        }
        //var_dump($winTotal);
        //var_dump($result);
        //throw new Exception("xxxx");
        Db::name("arena")->where(['id' => $this->arenaId])->update([
            'win' => $winTotal,
            'win_brok' => $arenaBrokNumber,
            'risk' => $winTotal,
            'win_number' => $this->winTotal,
            'status' => ARENA_STATEMENT_END,
            'win_target' => json_encode($result),
            'update_time' => time(),
        ]);
        return true;
    }

    public function win($data, $user, $isHalf = false) {

        $this->winTotal++;
        $result = $this->calculate($data['money'], $data['odds'], $this->ruleType, $isHalf, $data);
        $money = $result['win']; //// + $data['money'];

        Db::name("arena_bet_detail")->where(['id' => $data['id']])->update([
            'win_money' => $result['win'], // + $data['money'],
            'fee' => $result['fee'],
            'win_time' => time(),
            'status' => $isHalf ? DEPOSIT_WIN_HALF : DEPOSIT_WIN,
        ]);
        $info = Db::name("arena_bet_detail")->where('id', $data['id'])->find();

        $this->prizePool = $this->prizePool - $money - $result['fee'];
        $this->brokerage += $result['fee'];
        if (!$this->arenaIsCredit) {
            $teams = $this->teams;
            $content = '';
            if (count($teams) > 2) {
                $content = $this->match['name'];
            } else {
                $content = "{$teams[0]['name']} VS {$teams[1]['name']}";
            }

            Log::UserFunds($data['user_id']
                    , FUNDS_CLASSIFY_WIN_DEP
                    , FUNDS_TYPE_GOLD
                    , $money
                    , $user['gold']
                    , $user['gold'] + $money
                    , lang(90013, ['team' => $content])
                    , ['money' => $result['win'], 'fee' => $result['fee'], 'data' => $data, 'win' => $result]
            );


            $win_money_only = $money - $data["money"]; //去掉本金，更新用户表的累计输赢
            //向服务器推送一条结算数据
            (new \library\service\Pad())->my_settlement($money, 2, $info['user_id'], $info['order_id'], $win_money_only);

            //将中奖数据大于等于阀值的数据写入缓存
            if ($win_money_only >= MIN_USER_EARN_MONEY) {
                $this->sendSlideCache($data['user_id'], $win_money_only);
            }
            //将中奖数据写入缓存
            if ($user['has_robot']) {//机器中奖人投注算系统中奖
                Db::name("user")->where(['id' => $data['user_id']])->update([
                    //'win_money' => ['exp',"win_money+{$money}"],
                    'win_money' => ['exp', "win_money+{$win_money_only}"],
                    'win_total' => ['exp', "win_total+1"],
                ]);
                Log::sysIncome($result['win'], lang(40502, ['arenaId' => $this->arena['id']]), [
                    'brok' => $result['fee'],
                    'arena_id' => $this->arenaId,
                    'number' => $result['win'],
                    'user_id' => $data['user_id']
                        ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_ARENA_BETTING);
            } else {
                Db::name("user")->where(['id' => $data['user_id']])->update([
                    //'gold' => ['exp',"gold+{$money}"],
                    //'win_money' => ['exp',"win_money+{$money}"],
                    'win_money' => ['exp', "win_money+{$win_money_only}"],
                    'win_total' => ['exp', "win_total+1"],
                ]);
                $this->userNotice($data['id'], $data['user_id'], $money);
                (new User())->setUserDetailCache($data['user_id']);
                $this->winUserSocketTemp($data['user_id'], $money, $data['money']);
                // (new Socket())->userGold($data['user_id'],$money);
                //(new Socket())->sendToUid($data['user_id'],['type' => 'gold','gold' => $money],"socket.to_send_gold_change");
                $this->betOrderTotal($data['user_id']);
                $this->betOrderWin($data['user_id'], lang(40503, ['number' => $money]));
            }
        } else {
            $winTotal = $money - $data['money']; //扣除本金的佣金
            Db::name("arena_credit")->where(['user_id' => $data['user_id'], 'arena_id' => $this->arenaId])->update([
                'win' => ['exp', "win+{$winTotal}"],
            ]);
        }
        return $result;
    }

    public function userNotice($betId, $userId, $money) {
        $USER_NOTICE = cache("USER_NOTICE_WIN_{$userId}"); //用户中奖公告
        $teams = $this->teams;
        if (count($teams) > 2) {
            $content = $match = $this->match['name'];
            //$content = $match['name'];
        } else {
            $content = "{$teams[0]['name']} VS {$teams[1]['name']}";
        }

        if (!$USER_NOTICE) {
            $USER_NOTICE = [];
        }
        $USER_NOTICE[$betId] = [
            'money' => $money,
            'title' => '',
            'content' => $content
        ];
        cache("USER_NOTICE_WIN_{$userId}", $USER_NOTICE);
    }

    /**
     * 未中奖
     * @param $data
     * @param $user
     * @param $isHalf ,true：输一半
     * @throws \think\Exception
     */
    public function lose($data, $user, $isHalf = false) {
        Db::name("arena_bet_detail")->where(['id' => $data['id']])->update([
            'win_money' => 0,
            'fee' => 0,
            'win_time' => time(),
            'status' => $isHalf ? DEPOSIT_LOST_HALF : DEPOSIT_LOSE,
        ]);

        $info = Db::name("arena_bet_detail")->where('id', $data['id'])->find();
        $capital = $data['money'];
        if ($isHalf) { //输一半
            $capital = numberFormat($data['money'] / 2, 2);
            $this->prizePool = $this->prizePool - $capital;
        }
        if (!$this->arenaIsCredit) {
            $logContent = '';
            if (count($this->teams) > 2) {
                $logContent = $this->match['name'];
            } else {
                $logContent = "{$this->teams[0]['name']} VS {$this->teams[1]['name']}";
            }
            if ($isHalf) {//输一半,退一半本金
                Db::name("user")->where(['id' => $data['user_id']])->update([
                    //'gold' => ['exp', "gold+{$capital}"],
                    'win_money' => ["exp", "win_money-{$capital}"]
                ]);
                //Db::name("user")->where(['id' => $data['user_id']])->update(['gold' => ['exp', "gold+{$capital}"],]);
                $this->winUserSocketTemp($data['user_id'], $capital, $data['money']);
                //(new Socket())->userGold($data['user_id'],$capital);
                //(new Socket())->sendToUid($data['user_id'], ['type' => 'gold', 'gold' => $capital], "socket.to_send_gold_change");
                Log::UserFunds($data['user_id']
                        , FUNDS_CLASSIFY_WIN_DEP
                        , FUNDS_TYPE_GOLD
                        , $capital
                        , $user['gold']
                        , $user['gold'] + $capital
                        , lang(90014, ['team' => $logContent])
                        , ['money' => $capital, 'capital' => $data['money']]
                );

                $money_only = $capital - $data["money"]; //去掉本金，更新用户表的累计输赢
                //向服务器推送一条结算数据
                (new \library\service\Pad())->my_settlement($capital, 2, $info['user_id'], $info['order_id'], $money_only);
            } else {
                $this->loseUserSocketTemp($data['user_id'], $capital);
                Db::name("user")->where(['id' => $data['user_id']])->update(['win_money' => ["exp", "win_money-{$capital}"]]);
            }
            //$message = lang($isHalf ? 90014 : 90021,['team' => $logContent]);
            $this->betOrderTotal($data['user_id']);
            (new User())->setUserDetailCache($data['user_id']);
        } else {
            Db::name("arena_credit")->where(['user_id' => $data['user_id'], 'arena_id' => $this->arenaId])->update([
                'win' => ['exp', "win-{$capital}"],
            ]);
        }
        return ['capital' => $capital];
    }

    /**
     * 平局，退回本金
     * @param $data
     * @param $user
     */
    public function same($data, $user) {
        Db::name("arena_bet_detail")->where(['id' => $data['id']])->update([
            'win_money' => 0,
            'fee' => 0,
            'win_time' => time(),
            'status' => DEPOSIT_SAME,
        ]);
        $info = Db::name("arena_bet_detail")->where('id', $data['id'])->find();
        if (!$this->arenaIsCredit) {
            $logContent = '';
            if (count($this->teams) > 2) {
                $logContent = $this->match['name'];
            } else {
                $logContent = "{$this->teams[0]['name']} VS {$this->teams[1]['name']}";
            }
            $capital = $data['money'];

            $message = lang(90020, ['team' => $logContent]);
            if ($user['has_robot']) {//机器人投注平局，退回到系统
                Log::sysIncome($capital, $message, [
                    'brok' => 0,
                    'arena_id' => $this->arenaId,
                    'number' => $capital,
                    'user_id' => $data['user_id']
                        ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_ARENA_BETTING);
            } else {

                //向服务器推送一条结算数据
                (new \library\service\Pad())->my_settlement($capital, 2, $info['user_id'], $info['order_id'], 0);

                //$message = "平局(#{$data['arena_id']})";
                //Db::name("user")->where(['id' => $data['user_id']])->setInc("gold", $data['money']);
                if (!array_key_exists('money', $user)) {
                    $user['money'] = 0;
                }
                $user['money'] += $capital;
                $this->prizePool = $this->prizePool - $capital;
                Log::UserFunds($data['user_id'], FUNDS_CLASSIFY_WIN_DEP, FUNDS_TYPE_GOLD, $capital, $user['gold'], $user['gold'] + $capital, $message, ['money' => $capital, 'data' => $data, 'win' => '']);
                (new User())->setUserDetailCache($data['user_id']);
                //(new Socket())->userGold($data['user_id'],$capital);
                $this->winUserSocketTemp($data['user_id'], $capital, $data['money']);
                //(new Socket())->sendToUid($data['user_id'],['type' => 'gold','gold' => $capital],"socket.to_send_gold_change");
            }
        }
    }

    /**
     * 计算结算
     * @param $money 投注金额
     * @param $odds 赔率
     * @param $rule 玩法
     * @param bool $half 是否赢一半
     * @return array
     */
    public function calculate($money, $odds, $ruleType, $half = false, $data) {
        $sys_player_brok = floatval($data['brok']);
        $winResult = forWin($money, $odds, $ruleType, $sys_player_brok, $this->arena['game_type']);
        $win = $winResult['win']; //收益，未包含本金
        $brok = $winResult['brok'];
        $win_total = $winResult['win_total']; //收益，包含本金,未扣除了佣金
        if ($half) { //只赢一半
            $win = numberFormat($win / 2, 2);
            $brok = numberFormat($win * $sys_player_brok, 2);
            $win_total = $win + $money;
            $win = $win_total - $brok;
        } else {
            $win = $winResult['win_money']; //收益，包含本金,并扣除了佣金
        }
        return ['win' => $win, 'fee' => $brok, 'win_total' => $win_total];
    }

    public function getError() {
        return $this->error;
    }

    public function betOrderTotal($user_id) {
        $orderTotal = cache("arena_bet_order_total");
        if (isset($orderTotal[$user_id])) {
            $orderTotal[$user_id] ++;
        } else {
            $orderTotal[$user_id] = 1;
        }
        cache("arena_bet_order_total", $orderTotal);
    }

    public function arenaBetOrderNew($user_id) {
        $orderTotal = cache("arena_bet_order_new");
        if (isset($orderTotal[$user_id])) {
            $orderTotal[$user_id] ++;
        } else {
            $orderTotal[$user_id] = 1;
        }
        cache("arena_bet_order_new", $orderTotal);
    }

    public function betOrderWin($user_id, $msg) {
        $orderTotal = cache("arena_bet_order_win");
        $orderTotal[$user_id][] = $msg;

        cache("arena_bet_order_win", $orderTotal);
    }

    /**
     * 缓存系统收益
     * @param $number 收益金额
     */
    public function sysWin($number) {
        $number = numberFormat($number, 2);
        if ($number <= 0) {
            return false;
        }
        (new Misc())->setCacheTotal('sys_income', $number);
    }

    /**
     * 通用让球玩法计算结果
     * @param $team_home_score
     * @param $team_guest_score
     * @param $handicap
     * @return array
     */
    function letRuleResult($team_home_score, $team_guest_score, $handicap) {
        $result = [];
        $value = $team_home_score + $handicap;
        $isHalf = false;
        if (stripos($handicap, "/") !== false) {
            list($a, $b) = explode("/", $handicap);
            if ($a + $team_home_score > $team_guest_score && $b + $team_home_score > $team_guest_score) {
                $winTarget = 0;
                $result['target_name'] = $this->teams[0]['name'] . "胜";
            } elseif ($a + $team_home_score < $team_guest_score && $b + $team_home_score < $team_guest_score) {
                $winTarget = 1;
                $result['target_name'] = $this->teams[1]['name'] . "胜";
            } elseif (($a + $team_home_score == $team_guest_score && $b + $team_home_score > $team_guest_score) || ($a + $team_home_score > $team_guest_score && $b + $team_home_score == $team_guest_score)
            ) {
                $winTarget = 0;
                $result['target_name'] = $this->teams[0]['name'] . "胜";
                $isHalf = true;
            } elseif (($a + $team_home_score < $team_guest_score && $b + $team_home_score == $team_guest_score) || ($a + $team_home_score == $team_guest_score && $b + $team_home_score < $team_guest_score)
            ) {
                $winTarget = 1;
                $result['target_name'] = $this->teams[1]['name'] . "胜";
                $isHalf = true;
                //$type = $target == 'home' ? 4 : 3;
            } else {
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        } else {

            if ($value > $team_guest_score) {
                $winTarget = 0;
                $result['target_name'] = $this->teams[0]['name'] . "胜";
            } elseif ($value < $team_guest_score) {
                $winTarget = 1;
                $result['target_name'] = $this->teams[1]['name'] . "胜";
            } else {
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        }
        if ($isHalf) {
            $result['target_name'] .= "/平，退一半本金";
        }
        $result['winTarget'] = $winTarget;
        $result['isHalf'] = $isHalf;
        return $result;
    }

    /**
     * 通用大小玩法计算结果
     * @param $number
     * @param $scoreTotal
     * @return array
     */
    public function ouRuleResult($number, $scoreTotal) {
        $result = [];
        $isHalf = false;
        if (stripos($number, "/") !== false) {
            list($a, $b) = explode("/", $number);
            if ($scoreTotal > $a && $scoreTotal > $b) {
                $winTarget = '0';
                $result['target_name'] = "大";
            } elseif ($scoreTotal < $a && $scoreTotal < $b) {
                $winTarget = '1';
                $result['target_name'] = "小";
            } elseif ($scoreTotal == $a && $scoreTotal < $b) {
                $winTarget = '1';
                $result['target_name'] = "小";
                $isHalf = true;
            } elseif ($scoreTotal == $b && $scoreTotal > $a) {
                $winTarget = '0';
                $result['target_name'] = "大";
                $isHalf = true;
            } elseif ($scoreTotal == $b && $scoreTotal == $a) {
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        } else {
            if ($scoreTotal > $number) {
                $winTarget = '0';
                $result['target_name'] = "大";
            } elseif ($scoreTotal < $number) {
                $winTarget = '1';
                $result['target_name'] = "小";
            } else {
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        }
        if ($isHalf) {
            $result['target_name'] .= "/平，退一半本金";
        }
        $result['winTarget'] = $winTarget;
        $result['isHalf'] = $isHalf;
        return $result;
    }

    /**
     * 用户中奖数据缓存
     * @param $userId
     * @param $gold
     */
    public function winUserSocketTemp($userId, $gold, $capital = 0) {
        if (isset($this->userWinData[$userId])) {
            //$this->userWinData[$userId] += $gold;
            $temp = $this->userWinData[$userId];
            $temp['gold'] += $gold;
            $temp['capital'] += $capital;
            $this->userWinData[$userId] = $temp;
        } else {
            $this->userWinData[$userId] = [
                'gold' => $gold,
                'capital' => $capital
            ];
        }
    }

    /**
     * 用户未中奖数据缓存
     * @param $userId
     * @param $gold
     */
    public function loseUserSocketTemp($userId, $gold) {
        $gold = abs($gold);

        if (isset($this->userLoseData[$userId])) {
            $this->userLoseData[$userId] += $gold;
        } else {

            $this->userLoseData[$userId] = $gold;
        }
    }

    /**
     * 整场比赛结算完成后才一起socket推送
     */
    public function socketSend() {
        $pad = new Pad([]); //发送至平板
        if ($this->userWinData) {
            $socket = new Socket();
            foreach ($this->userWinData as $userId => $val) {
                $socket->userGold($userId, $val['gold']);
                $pad->statement($userId, $val['gold'], $val['capital']);
            }
        }
        if ($this->userLoseData) { //输也写入流水
            foreach ($this->userLoseData as $userId => $gold) {
                $pad->statement($userId, 0, -$gold);
            }
        }
    }

    /**
     * 用户中奖后缓存数据
     * */
    public function sendSlideCache($userId, $money) {
        $data['cpid'] = Db::name('user')->where(['id' => $userId])->value('cpid');
        if (!$data['cpid']) {
            return false;
        }
        $data['amount'] = $money;
        //用户中奖并超过阀值时，写入缓存
        Cache::rpush('user_earn_side_data', json_encode($data));
    }

}
