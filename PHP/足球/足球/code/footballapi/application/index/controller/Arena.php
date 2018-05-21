<?php

namespace app\index\controller;

use app\library\logic\Safe;
use library\service\Agent;
use library\service\Oauth;
use library\service\Play;
use library\service\Rule;
use library\service\User;
use think\Db;
use think\Log;
use think\Cache;

class Arena extends Safe {

    /**
     * 擂台列表
     */
    public function lists() {
        $item_id = input("item_id/d");
        $playId = input("play_id/d");
        $rules_id = input("rules_id/d");
        $user_id = input("user_id/d");
        $gameId = input("game_id/d");
        $status = input("status");
        $page = max(1, input("page/d", 0, 'intval'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $playSvr = new \library\service\Play();
        //获取比赛队伍信息
        $where = [];

        if ($item_id) {
            $where['game_type'] = $item_id;
        }
        if ($playId) {
            $where['play_id'] = $playId;
        }
        if ($gameId) {
            $where['game_id'] = $gameId;
        }
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        if ($status) {
            $where['status'] = $status;
        }
        if ($rules_id) {
            $where['rules_id'] = $rules_id;
        }
        $where['has_hide'] = 0;
        $where['classify'] = ARENA_CLASSIFY_GOLD;
        //获取擂台列表
        $lists = Db::name('arena')->where($where)->limit($offset, $limit)->order("create_time desc")->select();
        $playLists = [];
        $playSvr = new Play();
        if ($lists) {
            $arenaSvr = new \library\service\Arena();
            foreach ($lists as $key => $val) {
                //$val['rules_id'] = $val['rules_type'];
                $val['rules_name'] = getRuleData($val['game_type'], $val['rules_id'], 'name');
                $val['friend'] = $arenaSvr->checkArenaFriend($val['id'], $this->getUserId());
                $val['invite'] = $arenaSvr->checkArenaInvite($val['id'], $this->getUserId());
                $val['user'] = getUserField($val['user_id']);
                //unset($val['rules_type']);
                $teams = (new \library\service\Play())->getTeams($val['play_id'], ['id', 'name', 'logo', 'logo_big', 'live_type', 'live'], $this->getUserId('sys'));
                $odds = @json_decode($val['odds'], true);
                $val['odds'] = (new Rule())->factory($val['game_type'])->parseOddsWords($odds, $val['rules_id'], $val['game_type'] == GAME_TYPE_FOOTBALL ? [] : $teams);
                $val['bet_total'] = @json_decode($val['bet_total'], true);
                $val['item_id'] = $val['game_type'];
                $val['item_name'] = getSport($val['game_type']);
                $val['item_value'] = getItemValue($val['game_type'], [
                    ['type' => 'match', 'value' => $val['match_id']],
                    ['type' => 'game', 'value' => $val['game_id']],
                ]);
                $val['teams'] = $teams;
                $val['match'] = getMatch($val['match_id'], null, ['id', 'name', 'logo']);
                if (isset($playLists[$val['play_id']])) {
                    $val['play'] = $playLists[$val['play_id']];
                } else {
                    $temp = $playSvr->getPlay($val['play_id'], ['id', 'play_time', 'status', 'live', 'match_time', 'bo']);
                    if ($temp['status'] == PLAT_STATUS_START) {
                        $temp['match_time'] = getMatchRunTime($temp['match_time'], $temp['play_time']);
                    }

                    if ($val['item_id'] == GAME_TYPE_WCG && $temp['bo'] && $temp['bo'] > 0) {
                        $temp['bo'] = "BO{$temp['bo']}";
                    } else {
                        $temp['bo'] = '';
                    }
                    $val['play'] = $temp;
                    $playLists[$val['play_id']] = $temp;
                }
                $val = $this->reField($val);
                $val['url'] = $arenaSvr->getArenaUrl($val['id'], $this->token);
                //二维码地址
                $val['qr_code_url'] = get_image_thumb_url($arenaSvr->getQrCode($val['id'], $this->token));
                $lists[$key] = $val;
            }
        }
        $total = Db::name('arena')->where($where)->count();
        cache("index_arena_lists_total", $total, 600);
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('arena.lists', $lists, '', ['next_page' => $next_page, 'total_page' => $total_pages]);
    }

    /**
     * 擂台详情
     */
    public function info() {
        $arena_id = input("arena_id/d", 0, 'intval');
        $arena_mark = input("arena_mark");
        //$credit_mark = input("credit_mark");
        if (!$arena_id && !$arena_mark) {
            return $this->retErr('arena.info', 10004);
        }
        $arenaSvr = new \library\service\Arena();
        $arena = $arena_id ? $arenaSvr->getCacheArenaById($arena_id) : $arenaSvr->getCacheArenaByMark($arena_mark);
        if (!$arena) {
            return $this->retErr('arena.info', 40001);
        }
        /* $creditUser = [];
          if($arena['classify'] == ARENA_CLASSIFY_CREDIT){
          if(!$credit_mark){
          return $this->retErr('arena.info',40001);
          }
          $creditUser = Db::name('arena_credit')->where(['arena_id' => $arena['id'],'mark' => $credit_mark])->find();
          if(!$creditUser){
          return $this->retErr('arena.info',40001);
          }
          } */



        //$arena['rules_id'] = $arena['rules_type'];
        $arena['item_id'] = $arena['game_type'];
        $arena['rules_name'] = getRuleData($arena['game_type'], $arena['rules_id'], 'name');
        $arena['friend'] = $arenaSvr->checkArenaFriend($arena['id'], $this->getUserId());
        $arena['invite'] = $arenaSvr->checkArenaInvite($arena['id'], $this->getUserId());
        //unset($arena['rules_type']);
        //相似擂台数
        $arena['same_arena_total'] = 0;
        $sameArena = (new Play())->getPlayRules($arena_id['play_id'], $arena['rules_id']);
        if ($sameArena) {
            $arena['same_arena_total'] = isset($sameArena['arena_total']) ? $sameArena['arena_total'] : 0;
        }
        if ($arena['same_arena_total'] > 0) {
            $arena['same_arena_total'] = $arena['same_arena_total'] - 1; //减去自己
        }

        $playSvr = new Play();
        $arena['play'] = $playSvr->getPlay($arena['play_id']);
        //$arena['play']['teams'] = [];//(new \library\service\Play())->getTeams($arena['play_id'],['id','name','logo','logo_big','score','yellow','red']);
        $teams = [];
        foreach ($arena['teams'] as $val) {
            $teams[] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'logo' => $val['logo'],
                'score' => $val['score'],
                'half_score' => $val['half_score'],
                'red' => $val['red'],
                'yellow' => $val['yellow'],
                'score_json' => $val['score_json'],
                'has_home' => $val['has_home'],
                'has_follow' => intval((new User())->checkFollow($this->getUserId('sys'), $val['id'], FOLLOW_TYPE_TEAM))
            ];
        }
        unset($arena['teams']);
        $ruleSvr = (new Rule())->factory($arena['game_type']);
        $ruleType = $arena['rules_type'];
        $arena['user'] = getUserField($arena['user_id']);
        $arena['odds'] = $ruleSvr->parseOddsWords($arena['odds'], $arena['rules_id'], $arena['item_id'] == GAME_TYPE_FOOTBALL ? [] : $teams);
        $target_list = $arena['target_list'];
        if ($target_list) {
            foreach ($target_list as $key => $val) {
                if (isset($arena['odds'][$val['target'] . $val['item']])) {
                    $arena['odds'][$val['target'] . $val['item']]['money_total'] = floatval($val['money']);
                }
            }
        }
        $arenaRule = getRuleData($arena['item_id'], $arena['rules_id']);
        if ($arenaRule) {
            $arena['rule'] = ['name' => $arenaRule['name'], 'alias' => $arenaRule['alias'], 'type' => $arenaRule['type'], 'intro' => $arenaRule['intro'], 'help_intro' => $arenaRule['help_intro'],];
        } else {
            $arena['rule'] = ['name' => '', 'alias' => '', 'intro' => '', 'help_intro' => '', 'type' => ''];
        }
        //擂台结果
        // $arena['result'] = $arenaSvr->getResult($arena['game_type'],$arena['id']);
        //获取当前登录用户本擂台投注信息
        $userId = $this->getUserId();
        $maxBet = intval($arena['max_bet']);
        if ($userId) {
            $bets = Db::name('arena_bet_detail')->where(['arena_id' => $arena_id, 'user_id' => $userId])->select();
            if ($bets) {
                foreach ($bets as $val) {
                    $arena['odds'][$val['target'] . $val['item']]['money'] += $val['money'];
                    //计算个人投注收益
                    $win = forWin($val['money'], $val['odds'], $ruleType, $val['brok'], $arena['item_id']);
                    $arena['odds'][$val['target'] . $val['item']]['win_money'] += $win['win_total'];
                    if ($arena['classify'] == ARENA_CLASSIFY_CREDIT) { //征信局扣除本金
                        $arena['odds'][$val['target'] . $val['item']]['win_money'] -= $val['money'];
                    }
                    $maxBet = $maxBet ? ($maxBet - $val['money']) : 0;
                }
            }
        }

        $arena['odds'] = array_values($arena['odds']);
        $arena['last_max_bet'] = $maxBet; //剩余最大投注金额
        $play = $arena['play'];
        if ($play['status'] == PLAT_STATUS_START) {
            $play['match_time'] = getMatchRunTime($play['match_time'], $play['play_time']);
        }
        if ($arena['item_id'] == GAME_TYPE_WCG && $play['bo'] && $play['bo'] > 0) {
            $play['bo'] = "BO{$play['bo']}";
        } else {
            $play['bo'] = '';
        }
        $arena['play'] = [
            'id' => $play['id'],
            'play_time' => $play['play_time'],
            'bo' => $play['bo'],
            'status' => $play['status'],
            'live_type' => $play['live_type'],
            'live' => $playSvr->getPlayLive($play['id'], $play['live']),
            'match_time' => $play['match_time'],
            'total_prize' => $play['total_prize'],
            'min_deposit' => $play['min_deposit'],
            'result' => $arena['status'] == ARENA_STATEMENT_END ? (new Play())->getResult($arena['game_type'], $arena['play_id'], 'array') : [],
            'teams' => $teams
        ];

        $match = $arena['match'];
        $arena['match'] = [];
        $arena['match']['id'] = $match['id'];
        $arena['match']['name'] = $match['name'];
        $user = $arena['user'];
        $arena['user'] = [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
        ];

        $arena['result'] = ''; //$arenaSvr->getResult($arena['win_target'],$arena['odds']);
        $arena = $this->reField($arena);
        //如果当前用户是擂主
        if (!$userId || $userId != $arena['user_id']) {
            $arena['invit_code'] = '';
        }
        $arena['url'] = $arenaSvr->getArenaUrl($arena_id, $this->token);
        //二维码地址
        $arena['qr_code_url'] = get_image_thumb_url($arenaSvr->getQrCode($arena_id, $this->token));
        //项目标识值
        $arena['item_value'] = getItemValue($arena['item_id'], [
            ['type' => 'match', 'value' => $arena['match_id']],
            ['type' => 'game', 'value' => $arena['game_id']],
        ]);
        //比赛预测
        $arena['play_dope'] = '';
        $dope = Db::name('play_dope')->where(['play_id' => $arena['play_id']])->find();
        if ($dope) {
            $arena['play_dope'] = getPlayDopeUrl($this->token, $arena['play_id']);
        }

        return $this->retSucc('arena.info', $arena);
    }

    private function reField($arena) {

        unset($arena['update_time']);
        unset($arena['has_default']);
        //unset($arena['invit_code']);
        unset($arena['user_nickname']);
        unset($arena['has_robot']);
        unset($arena['game_type']);
        //unset($arena['has_sys']);
        unset($arena['target_list']);
        unset($arena['bet_total']);
        $userId = $this->getUserId();
        if ($userId != $arena['user_id']) { //非擂主，返回数据时不显示收益数据
            unset($arena['win_target']);
            unset($arena['win']);
        }

        unset($arena['play']['team_home_id']);
        unset($arena['play']['team_home_name']);
        unset($arena['play']['team_guest_id']);
        unset($arena['play']['team_guest_name']);
        unset($arena['play']['game_type']);
        unset($arena['play']['md5_play']);
        unset($arena['play']['create_time']);
        unset($arena['play']['update_time']);
        unset($arena['play']['has_odds']);
        unset($arena['play']['odds_key']);
        unset($arena['play']['team_home_score']);
        unset($arena['play']['team_guest_score']);
        unset($arena['play']['team_home_half_score']);
        unset($arena['play']['team_guest_half_score']);
        unset($arena['play']['score_json']);


        unset($arena['user']['reg_time']);
        unset($arena['user']['last_login_time']);
        unset($arena['user']['mail']);
        unset($arena['user']['has_robot']);
        unset($arena['user']['mobile']);
        unset($arena['user']['username']);
        unset($arena['user']['friends']);
        unset($arena['user']['follows']);
        unset($arena['user']['gold']);

        return $arena;
    }

    /**
     * 检查擂台邀请码,必须先登录登录
     */
    public function chk_invite() {
        if ($this->request->isPost()) {
            if (!$this->checkLogin()) {
                return $this->retErr('arena.chk_invite', '20040');
            }
            $arena_id = input("post.arena_id/d", 0, 'intval');
            $code = input("post.code");
            if (!$arena_id || !$code) {
                return $this->retErr('arena.chk_invite', '10004');
            }
            $arenaSvr = new \library\service\Arena();
            if ($arenaSvr->checkArenaInvite($arena_id, $this->getUserId(), $code)) {
                return $this->retSucc('arena.chk_invite');
            } else {
                return $this->retErr('arena.chk_invite', 40002);
            }
        }
        return $this->retErr('arena.chk_invite', 10000);
    }

    /**
     * 征信局密码检查
     */
    public function check_credit() {
        if ($this->request->isPost()) {
            if (!$this->checkLogin()) {
                return $this->retErr('arena.check_credit', '20040');
            }
            $arena_id = input("post.arena_id/d", 0, 'intval');
            $pwd = input("post.pwd");
            $mark = input("post.credit_mark");
            if (!$arena_id || !$pwd) {
                return $this->retErr('arena.check_credit', '10004');
            }
            $arenaSvr = new \library\service\Arena();
            if ($ret = $arenaSvr->checkArenaCreditPwd($arena_id, $this->getUserId(), $mark, $pwd)) {
                return $this->retSucc('arena.check_credit', [
                            'gold' => $ret['avail_gold']
                ]);
            } else {
                return $this->retErr('arena.check_credit', $arenaSvr->getError());
            }
        }
        return $this->retErr('arena.check_credit', 10000);
    }

    /**
     * 擂台投注
     */
    public function bet() {
        if ($this->request->isPost()) {
            $arenaId = input("post.arena_id/d", 0, 'intval'); //擂台ID
            $target = input("post.target"); //投注项
            $item = input("post.item"); //比分、比分组合二级投注项
            $money = input("post.money/d", 0, 'intval'); //投注金额

            $retData = [
                'target' => $target,
                'item' => $item,
                'money' => $money,
            ];

            $fileName = RUNTIME_PATH . "log/bet.txt";
            $data = '';
            if (is_file($fileName)) {
                $data = file_get_contents($fileName);
            }
            $data .= "\r\n\$arenaId={$arenaId},\$target={$target},\$item={$item},\$money={$money},date=" . date("Y-m-d H:i:s");
            file_put_contents($fileName, $data);

            if (!$this->checkLogin()) {
                return $this->retErr('arena.bet', '20040', [], $retData);
            }
            $userId = $this->getUserId();
            if (!$arenaId || !$target || !$money || !$userId) {
                return $this->retErr('arena.bet', '10004', [], $retData);
            }

            $ret = $this->_bet($arenaId, $money, $target, $item);
            if (true === $ret) {
                return $this->retSucc('arena.bet', $retData, '40003');
            } else {
                return $this->retErr('arena.bet', $ret['code'], $ret['vars'], $retData);
            }
        }
        return $this->retErr('arena.bet', 10000);
    }

    //批量投注
    public function bets() {
        if ($this->request->isPost()) {
            if (Cache::has('max_user_raise_error_num') && Cache::get('max_user_raise_error_num') >= RAISE_ERROR_NUM) {
                //(new \library\service\Pad([]))->logout($this->getUserId());
                //Cache::rm('max_user_raise_error_num');
            }
            $arenaId = input("post.arena_id/d", 0, 'intval'); //擂台ID
            $agentUserNo = input("post.agent_user_id/d", 0, 'intval'); //代理用户编号
            //以下参数请忽略
            $credit_mark = input("post.credit_mark"); //征信局，用户唯一码
            //以上参数请忽略
            $betData = input("bet_data");

            $betData = json_decode(base64_decode($betData), true);

            if (!$this->checkLogin()) {
                return $this->retErr('arena.bet', 20040);
            }
            if (!$betData) {
                return $this->retErr("arena.bet", 40012);
            }
            $money = 0;
            foreach ($betData as $key => $value) {
                $money += $value['money'];
            }
            /* if(!$this->checkCsrf(input("csrf"))){ //防止重复提交
              $msg = lang(10005);
              return $this->retErr('csrf',"{$msg}(CSRF)");
              } */

            $agentUserId = 0;
            if ($agentUserNo) {
                $agent = (new Agent())->getAgentUserByMark($agentUserNo);
                if ($agent) {
                    $agentUserId = $agent['id'];
                }
            }
            $arenaSvr = new \library\service\Arena();
            $arena = $arenaSvr->getCacheArenaById($arenaId);
            $retData = [];
            $orderId = [];
            $errTotal = 0;
            $succTotal = 0;
            Log::write('bets $betData :'.json_encode($retData));
            foreach ($betData as $key => $val) {
                $target = $val['target'];
                $item = $val['item'];
                $money = $val['money'];
                $orderId[$key] = 'PAD' . date('YmdHis') . mt_rand(100000, 999999);
                $userid = $this->getUserId();
                if (!$arenaId || $target === '' || !$money) {
                    $retData[$key] = [
                        'code' => 10004,
                        'msg' => [],
                        'target' => $target,
                        'item' => $item,
                        'money' => $money,
                        'win_money' => 0,
                    ];
                    $errTotal++;
                } else {
                    $ret = $this->_bet($arenaId, $money, $target, $item, $agentUserId, $credit_mark, $orderId[$key]);
                    Log::write('bets betsbetsbets $ret :'.json_encode($ret));
                    if (!isset($ret['bet_status'])) {
                        $win = $arenaSvr->forWin($money, $ret['odds'], $arena['rules_type'], $ret['brok'], $arena['game_type']);
                        if ($arena['classify'] == ARENA_CLASSIFY_CREDIT) { //征信局要扣除本金
                            $win['win_total'] = $win['win_total'] - $money;
                        }
                        $succTotal++;
                        $retData[$key] = [
                            'code' => 0,
                            'msg' => '',
                            'target' => $target,
                            'item' => $item,
                            'money' => $money,
                            'win_money' => $win['win_total'],
                        ];
                    } else {
                        Log::write('bets lang $ret :'.lang($ret['code'], $ret['vars']));
                        $msg = lang($ret['code'], $ret['vars']);
                        $retData[$key] = [
                            'code' => $ret['code'],
                            'msg' => $ret['vars'],
                            'target' => $target,
                            'item' => $item,
                            'money' => $money,
                            'win_money' => 0,
                        ];
                        $errTotal++;
                    }
                }
                Log::write('bets $retData :'.json_encode($retData));
                if ($retData[$key]['code'] == 0) {
                    $gold_my = (0 - $money);
                    $myser = new MyServer();
                    $fh_data = $myser->settlement($gold_my, 1, $userid, $orderId[$key]);
                    Log::write('bet $fh_data :'.json_encode($fh_data));
                    if ($fh_data['payResult'] != '000000') {
                        if ($fh_data['ext']['isexit'] == 1) {
                            //删除错误下注
                            $this->DeleteThisBet($userid, $orderId);
                            //执行退出登录
                            (new \library\service\Pad([]))->logout($userid);
                        } else if ($fh_data['ext']['isexit'] == 0) {
                            $this->SetBetPadCache(array('user_id' => $userid, 'arenaId' => $arenaId, 'cpOrderId' => $orderId[$key], 'money' => $gold_my, 'err_total' => 1));
                        }
                        return $this->retErr("arena.bet", 8888, ['padmsg'=>$fh_data['payResult']]);
                    }else{
                        // 更新金币
                        Db::name('user')->where(['id' => $userid])->setDec('gold', $money);
                    }
                } else {
                    $this->DeleteThisBet($userid, $orderId);
                    return $this->retErr("arena.bet", $retData[$key]['code'], $retData[$key]['msg']);
                }
            }

            $arena = $arenaSvr->getCacheArenaById($arenaId);
            $deposit_total = $arena['deposit'] + $arena['bet_money'];
            $arena_target = Db::name('arena_target')->where(['arena_id' => $arenaId])->select();
            foreach ($arena_target as $val) {
                foreach ($retData as $rkey => $rval) {
                    if ($val['target'] == $rval['target'] && $val['item'] == $rval['item']) {
                        $rval['money_total'] = $val['money'];
                        $retData[$rkey] = $rval;
                    }
                }
            }
            return $this->retSucc("arena.bet", $retData, 40003, ['err_total' => $errTotal, 'succ_total' => $succTotal, 'deposit_total' => $deposit_total]);
        }
    }

    /**
     * 下注出错删除前面的下注
     * */
    private function DeleteThisBet($userid, $orderId) {
        $money = 0;
        foreach ($orderId as $vid) {
            
            Db::name('arena_bet_detail')->where(['user_id' => $userid, 'order_id' => $vid])->delete();
            
            $ss = Db::name('user_funds_log')->where('data', 'like', '%' . $vid . '%')->find();
            $money += $ss['number'];
            Db::name('user_funds_log')->where('id', $ss['id'])->delete();
        }
        Db::name('user')->where('id', $userid)->setInc('gold', $money);
    }

    /**
     * 用户下注加入队列 推送给平板
     * */
    private function SetBetPADCache($data) {
        Cache::rpush('user_bet_pad_data', json_encode($data));
    }

    private function _bet($arenaId, $money, $target, $item, $agentUserId, $credit_mark, $orderId) {
        $userId = $this->getUserId();
        $arenaSvr = new \library\service\Arena();
        $guid = (new Oauth())->getUuidByToken($this->token);
        if (false !== $ret = $arenaSvr->betting($arenaId, $money, $userId, $target, $item, $agentUserId, $credit_mark, $guid, $orderId)) {
            Log::write('bet $ret _bet_bet_bet :'.json_encode($ret));
            return $ret;
        } else {
            $code = $arenaSvr->getError();
            $vars = $arenaSvr->getErrorData();
            Log::write('bet $ret error :'.json_encode(['code' => $code, 'vars' => $vars, 'bet_status' => 0]));
            return ['code' => $code, 'vars' => $vars, 'bet_status' => 0];
        }
    }

    /**
     * 推荐擂台
     */
    public function recommend() {
        $list = Db::name('arena_recommend')->order("sort asc,create_time desc")->limit(100)->select();
        $temp = [];
        $arenaSvr = new \library\service\Arena();
        $playSvr = new \library\service\Play();

        foreach ($list as $val) {
            $arena = $arenaSvr->getCacheArenaById($val['arena_id']);
            if (!$arena) {
                continue;
            }
            //$arena['rules_id'] = $arena['rules_type'];
            $arena['rules_name'] = getRuleData($arena['game_type'], $arena['rules_id'], 'name');
            $arena['friend'] = $arenaSvr->checkArenaFriend($arena['id'], $this->getUserId());
            $arena['invite'] = $arenaSvr->checkArenaInvite($arena['id'], $this->getUserId());
            $arena['user'] = getUserField($arena['user_id']);
            //unset($arena['rules_type']);
            $teams = $playSvr->getTeams($arena['play_id'], [], $this->getUserId('sys'));
            $arena['odds'] = (new Rule())->factory($arena['game_type'])->parseOddsWords($arena['odds'], $arena['rules_id'], $teams);
            $arena['bet_total'] = @json_decode($arena['bet_total'], true);
            $arena['teams'] = $teams;
            $arena['item_id'] = $arena['game_type'];
            $arena['item_name'] = getSport($arena['game_type']);
            $arena['item_value'] = getItemValue($arena['item_id'], [
                ['type' => 'match', 'value' => $arena['match_id']],
                ['type' => 'game', 'value' => $arena['game_id']],
            ]);
            $arena = $this->reField($arena);
            unset($arena['game_type']);
            unset($arena['invit_code']);
            unset($arena['win']);
            unset($arena['win_target']);
            unset($arena['win_number']);
            unset($arena['create_time']);
            unset($arena['update_time']);
            $temp[] = $arena;
        }
        return $this->retSucc('arena.recommend', $temp);
    }

    /**
     * 获取擂台最新赔率
     */
    public function odds() {
        //if($this->request->isPost()){
        $arenaIds = input("arena_ids");
        if (!$arenaIds) {
            return $this->retErr('arena.odds', 10004);
        }
        $arenaIds = explode(",", $arenaIds);
        $arenaSvr = new \library\service\Arena();
        $ret = [];

        //获取当前登录用户本擂台投注信息
        $userId = $this->getUserId();
        foreach ($arenaIds as $val) {
            $val = intval($val);
            $arena = $arenaSvr->getCacheArenaById($val);
            if (!$arena) {
                $ret[$val] = [];
            } else {
                $odds = $arena['odds'];
                $maxBet = intval($arena['max_bet']); //剩余最大投注金额
                if ($maxBet && $userId && $this->checkLogin()) {
                    $betMoney = Db::name('arena_bet_detail')->where(['arena_id' => $val, 'user_id' => $userId])->sum('money');
                    $maxBet = $maxBet - $betMoney;
                }
                $teams = (new Play())->getTeams($arena['play_id'], [], $this->getUserId('sys'));
                $odds = (new Rule())->factory($arena['game_type'])->parseOddsWords($arena['odds'], $arena['rules_id'], $teams);
                $temp = [];
                foreach ($odds as $key => $ov) {
                    $_ = $arena['bet_total'][$ov['target']];
                    if (isset($ov['item']) && $ov['item']) {
                        $_ = $_[$ov['item']];
                    }
                    $temp[] = [
                        'odds' => floatval($ov['odds']),
                        'money_total' => $_['money'],
                        'handicap' => isset($ov['handicap']) ? $ov['handicap'] : '',
                        'handicap_value' => isset($ov['handicap_value']) ? $ov['handicap_value'] : '',
                        'under' => isset($ov['under']) ? $ov['under'] : '',
                        'over' => isset($ov['over']) ? $ov['over'] : '',
                    ];
                }
                $ret[$val] = [
                    'bet_money' => $arena['bet_money'],
                    'deposit' => $arena['deposit'],
                    'min_bet' => intval($arena['min_bet']),
                    'max_bet' => intval($arena['max_bet']),
                    'status' => intval($arena['status']),
                    'result' => $arena['status'] == ARENA_STATEMENT_END ? $arenaSvr->getResult($arena['win_target'], $arena['odds']) : '',
                    'play_result' => $arena['status'] == ARENA_STATEMENT_END ? (new Play())->factory($arena['game_type'])->getResult($arena['play_id'], 'array') : [],
                    'odds' => $temp,
                    'last_max_bet' => $maxBet,
                        //$arena['last_max_bet'] = $maxBet,
                ];
            }
        }
        return $this->retSucc('arena.odds', $ret);
        //}
        //return $this->retErr('arena.odds',10000);
    }

    /**
     * 获取擂台状态
     */
    public function status() {
        $arenaIds = input("arena_ids");
        if (!$arenaIds) {
            return $this->retErr('arena.status', 10004);
        }
        $arenaIds = array_map('intval', array_unique(explode(",", $arenaIds)));
        $arenaSvr = new \library\service\Arena();
        $ret = [];
        foreach ($arenaIds as $val) {
            $val = intval($val);
            $arena = $arenaSvr->getCacheArenaById($val);
            if ($arena) {
                $ret[$val] = [
                    'bet_money' => $arena['bet_money'],
                    'deposit' => $arena['deposit'],
                    'min_bet' => intval($arena['min_bet']),
                    'max_bet' => intval($arena['max_bet']),
                    'status' => intval($arena['status']),
                    'result' => $arena['status'] == ARENA_STATEMENT_END ? $arenaSvr->getResult($arena['win_target'], $arena['odds']) : '',
                    'play_result' => $arena['status'] == ARENA_STATEMENT_END ? (new Play())->factory($arena['game_type'])->getResult($arena['play_id'], 'array') : [],
                ];
            }
        }
        return $this->retSucc('arena.status', $ret);
    }

}
