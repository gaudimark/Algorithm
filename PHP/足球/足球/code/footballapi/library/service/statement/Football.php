<?php

/**
 * 足球擂台结算
 */

namespace library\service\statement;
use library\service\Play;
use library\service\Rule;
use library\service\Socket;
use library\service\User;
use think\Db;
use think\Exception;
use library\service\Log;
use library\service\Misc;

class Football extends Basic{
    private $detail = null;

    public function checkPlayData($play){
        if(!$this->play){
            $this->error = "未找到比赛信息，无法结算";
            return false;
        }
        if($this->play['status'] != PLAT_STATUS_END){
            $this->error = "比赛未结束信息，无法结算";
            return false;
        }
        //$this->play = (new Play())->factory(GAME_TYPE_FOOTBALL)->getStatementResult($this->play);
        /*if(!$this->play['first_goals']){
            $this->error = "比赛数据设置错误：未选择最先进球队伍";
            return false;
        }*/

        if($this->play['team_home_half_score'] > $this->play['team_home_score'] || $this->play['team_guest_half_score'] > $this->play['team_guest_score']){
            $this->error = "比赛数据设置错误：半场比分不能大于全场比分";
            return false;
        }
        /*

        if(in_array($this->play['first_goals'],[HOME,GUEST]) && $this->play['team_home_half_score'] == 0 &&
            $this->play['team_home_score'] == $this->play['team_home_half_score'] &&
            $this->play['team_home_half_score'] == $this->play['team_guest_score'] &&
            $this->play['team_guest_half_score'] == $this->play['team_home_half_score']){
            $this->error = '比赛数据设置错误：比分错误';
            return false;
        }*/

        return true;
    }

    public function run(){

        $ruleSvr = (new Rule())->factory(GAME_TYPE_FOOTBALL);
        if($ruleSvr->checkRuleTypeDisabled($this->ruleType)){
            $this->error = '当前擂台玩法已被禁用';
            return false;
        }
        //Db::startTrans();
        //try{
            $result = null;
            
            switch ($this->ruleType){
                case RULES_TYPE_ASIAN : //亚盘
                    $result = $this->_asian();
                    break;
                case RULES_TYPE_EUROPE : //欧盘
                    $result = $this->_europe();
                    break;
                case RULES_TYPE_HOME_GOALS : //主进球
                case RULES_TYPE_GUEST_GOALS : //客进球
                    $result = $this->_goals();
                    break;
                case RULES_TYPE_ALL_YELLOW : //黄牌
                    $result = $this->_yellow();
                    break;
                case RULES_TYPE_FIRST_GOALS : //最先进球
                    $result = $this->_firstGoals();
                    break;
                case RULES_TYPE_ALL_GOALS : //全场进球
                    $result = $this->_allGoals();
                    break;
                case RULES_TYPE_SINGLE_DOUBLE : //单双
                    $result = $this->_singleDoubel();
                    break;
                case RULES_TYPE_MAX_GOALS ://上下场进球数比较
                    $result = $this->_maxGoals();
                    break;
                case RULES_TYPE_BODAN : //波胆
                    $result = $this->_bodan();
                    break;
                case RULES_TYPE_BODAN_COMB : //波胆组合
                    $result = $this->_bodanComb();
                    break;
                case RULES_TYPE_OU : //大小
                    $result = $this->_ou();
                   // $result = $this->_bodanComb();
                    break;
            }

            if(false === $this->toCalc($result)){
                throw new Exception($this->getError());
                return false;
            }
            //return false;
            //Db::commit();
        //}catch (\Exception $e){
            //$this->error = $e->getMessage();//.$e->getLine().$e->getFile();
            //var_dump($e->getMessage(),$e->getLine(),$e->getFile());
            //Db::rollback();
            //return false;
        //}
        return true;
    }

    /**
     * 亚盘
     */
    private function _asian(){
        $result = [];
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];

        $teams = $this->teams;
        $result['target'] = '';
        $result['target_name'] = [];
        $result['win'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $result['fee'] = 0;
        //echo "winTarget = $winTarget,isHalf = ".intval($isHalf)."\r\n";
        while(true){
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset,$limit)->where(['arena_id' => $this->arenaId])->select();
            if(!$detail){break;}
            foreach($detail as $val){
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if(!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $ret = $this->_asian_handicap($team_home_score,$team_guest_score,$val['handicap']);
                $winTarget = $ret['winTarget'];
                $isHalf  = $ret['isHalf'];
                $target = $val['target'];
                $result['target_name'] = '';
                /*if($winTarget == 'home'){
                    $result['target_name'][] = $teams[0]['name']."胜".($isHalf ? "/平，退一半本金":"");
                }elseif($winTarget == 'guest'){
                    $result['target_name'][] = $teams[1]['name']."胜".($isHalf ? "/平，退一半本金":"");
                }elseif($winTarget == 'same'){
                    $result['target_name'][] = "平手";
                }*/
                //echo "target = $target,money={$val['money']},odds={$val['odds']}\r\n";
                $message = "";
                $logContent = '';
                if(count($teams) > 2){
                    $logContent = $this->match['name'];
                }else{
                    $logContent = "{$teams[0]['name']} VS {$teams[1]['name']}";
                }
                $winMoney = 0;  //中奖金额
                $capital = 0; //本金
                $fee = 0 ; //佣金
                $winResult = [];
                //检测是否为平局，如果是则退回本金
                if($winTarget == 'same'){ //平手,退回本金
                    $this->same($val,$user);
                }elseif($winTarget == $target){
                    $ret = $this->win($val,$user,$isHalf);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }elseif($isHalf && $winTarget != $target){ //退一半本金
                    $ret = $this->lose($val,$user,$isHalf);
                    $result['capital'] += $ret['capital'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        //exit;
        
        return $result;
    }

    private function _asian_handicap($team_home_score,$team_guest_score,$handicap){
        $diff = $team_home_score - $team_guest_score; //进球差
        $handicapList = config("handicap");
        $cHandicap = $handicapList[abs($handicap).''][1];
        $winTarget = '';
        $isHalf = false;
        $handicap = floatval($handicap);
        if($handicap > 0.25 && $diff >= 0){
            $winTarget = 'home';
        }elseif($handicap < -0.25 && $diff <= 0){
            $winTarget = 'guest';
        }else{
            if(stripos($cHandicap,"/") !== false){
                list($a,$b) = explode("/",$cHandicap);
                if($handicap < 0){
                    $a = -$a;
                    $b = -$b;
                }
                //echo "a = $a,b = $b,team_home_score={$team_home_score},team_guest_score={$team_guest_score}";
                if($a + $team_home_score > $team_guest_score && $b + $team_home_score > $team_guest_score){
                    //$type = $target == 'home' ? 1 : 2;
                    $winTarget = 'home';
                }elseif(($a + $team_home_score  == $team_guest_score && $b + $team_home_score > $team_guest_score)
                    || ($a + $team_home_score  > $team_guest_score && $b + $team_home_score == $team_guest_score)
                ){
                    $winTarget = 'home';
                    $isHalf = true;
                    // $type = $target == 'home' ? 3 : 4;
                }elseif(($a + $team_home_score  < $team_guest_score && $b + $team_home_score == $team_guest_score)
                    || ($a + $team_home_score  == $team_guest_score && $b + $team_home_score < $team_guest_score)
                ){
                    $winTarget = 'guest';
                    $isHalf = true;
                    //$type = $target == 'home' ? 4 : 3;
                }elseif($a + $team_home_score  < $team_guest_score && $b + $team_home_score < $team_guest_score){
                    $winTarget = 'guest';
                }
            }else{
                if($team_home_score+$handicap == $team_guest_score){
                    // $type = 5;
                    $winTarget = 'same';
                }elseif($team_home_score + $handicap > $team_guest_score){
                    //$type = $target == 'home' ? 1 : 2;
                    $winTarget = 'home';
                }else{
                    //$type = $target == 'home' ? 2 : 1;
                    $winTarget = 'guest';
                }
            }
        }
        $result['winTarget'] = $winTarget;
        $result['isHalf'] = $isHalf;
        return $result;
    }

    //欧盘
    private function _europe(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];

        $winTarget = '';
        if($team_home_score > $team_guest_score){
            $winTarget = 'home';
        }elseif($team_home_score < $team_guest_score){
            $winTarget = 'guest';
        }elseif($team_home_score == $team_guest_score){
            $winTarget = 'same';
        }
        $result['target'] = $winTarget;
        $result['target_name'] = '其他';
        $teams = $this->teams;
        if($winTarget == 'home'){
            $result['target_name'] = $teams[0]['name']."胜";
        }elseif($winTarget == 'guest'){
            $result['target_name'] = $teams[1]['name']."胜";
        }elseif($winTarget == 'same'){
            $result['target_name'] = "平手";
        }
        while(true){
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset,$limit)->where(['arena_id' => $this->arenaId])->select();
            if(!$detail){break;}
            foreach($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if(!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];
                if ($target == $winTarget){//赢
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }
    //进球数
    private function _goals(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }

        $winTarget = '';
        foreach($rule as $key => $val){
            if(
            ($this->arena['rules_type'] == RULES_TYPE_HOME_GOALS && $team_home_score >= $val[1] && $team_home_score <= $val[2]) ||
            ($this->arena['rules_type'] == RULES_TYPE_GUEST_GOALS && $team_guest_score >= $val[1] && $team_guest_score <= $val[2])
            ){
                $winTarget = $key;
                $result['target_name'] = $val[0];
                break;
            }
        }
        $result['target'] = $winTarget;
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];/*
                $min = $rule[$val['target']][1];
                $max = $rule[$val['target']][2];*/
                if($winTarget == $target){
                    //$result['target'] = $target;
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }
    //黄牌
    private function _yellow(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];
        $yellowTotal = $this->play['home_yellow'] + $this->play['guest_yellow'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        $winTarget = '';
        foreach($rule as $key => $val){
            if($yellowTotal >= $val[1] && $yellowTotal <= $val[2]){
                $winTarget = $key;
                $result['target_name'] = $val[0];
                break;
            }
        }
        $result['target'] = $winTarget;
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];/*
                $min = $rule[$val['target']][1];
                $max = $rule[$val['target']][2];*/
                if($winTarget == $target){
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }
    //最先进球
    private function _firstGoals(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $first = strtolower($this->play['first_goals']);
        $winTarget = '';
        $teams = $this->teams;
        if($first == HOME){
            $winTarget = 'home';
            $result['target_name'] = $teams[0]['name'];
        }elseif($first == GUEST){
            $winTarget = 'guest';
            $result['target_name'] = $teams[1]['name'];
        }elseif($first == 99){
            $winTarget = 'zero';
            $result['target_name'] = '没有进球';
        }
        $result['target'] = $winTarget;


        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];
                if($winTarget == $target){
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }

    //全场进球
    public function _allGoals(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $scoreTotal = $this->play['team_home_score'] + $this->play['team_guest_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        $winTarget = '';
        foreach($rule as $key => $val){
            if($scoreTotal >= $val[1] && $scoreTotal <= $val[2]){
                $winTarget = $key;
                $result['target_name'] = $val[0];
                break;
            }
        }
        $result['target'] = $winTarget;
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];/*
                $min = $rule[$val['target']][1];
                $max = $rule[$val['target']][2];*/
                if($winTarget == $target){
                    //$result['target'] = $target;
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }

    //单双
    public function _singleDoubel(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $scoreTotal = $this->play['team_home_score'] + $this->play['team_guest_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        $winTarget = '';
        if(($scoreTotal % 2 == 0)){
            $winTarget = 'sd_2';
            $result['target_name'] = '双数';
        }else{
            $winTarget = 'sd_1';
            $result['target_name'] = '单数';
        }
        $result['target'] = $winTarget;
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];//$scoreTotal > 0 && (($scoreTotal % 2 == 0 && $target == 'sd_2') || $scoreTotal % 2 && $target == 'sd_1')
                if($winTarget == $target){
                    //$result['target'] = $target;
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }
    //大小
    public function _ou(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $scoreTotal = $this->play['team_home_score'] + $this->play['team_guest_score'];
        $number = isset($this->arena['odds']['under']) ? $this->arena['odds']['under'] : $this->arena['odds']['over'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        /*
        $winTarget = '';
        $isHalf = false;
        if(stripos($number,"/") !== false){
            list($a,$b) = explode("/",$number);
            if($scoreTotal > $a && $scoreTotal > $b){
                $winTarget = 'home';
                $result['target_name'] = "大";
            }elseif($scoreTotal < $a && $scoreTotal < $b){
                $winTarget = 'guest';
                $result['target_name'] = "小";
            }elseif($scoreTotal == $a && $scoreTotal < $b){
                $winTarget = 'guest';
                $result['target_name'] = "小";
                $isHalf = true;
            }elseif($scoreTotal == $b && $scoreTotal > $a){
                $winTarget = 'home';
                $result['target_name'] = "大";
                $isHalf = true;
            }elseif($scoreTotal == $b && $scoreTotal == $a){
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        }else {
            $scoreTotal = floatval($scoreTotal);
            if ($scoreTotal > $number){
                $winTarget = 'home';
                $result['target_name'] = "大";
            } elseif ($scoreTotal < $number) {
                $winTarget = 'guest';
                $result['target_name'] = "小";
            } else {
                $winTarget = 'same';
                $result['target_name'] = "平局";
            }
        }
        if($isHalf){
            $result['target_name'] .= "/平，退一半本金";
        }*/
        $result['target'] = [];
        $result['target_name'] = [];
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $ret = $this->ouRuleResult($val['under'],$scoreTotal);
                $winTarget = $ret['winTarget'];
                if($winTarget == '1'){
                    $winTarget = 'guest';
                }elseif($winTarget == '0'){
                    $winTarget = 'home';
                }
                $isHalf  = $ret['isHalf'];
                $result['target'][] = $winTarget;
                //$result['target_name'][] = $ret['target_name'];
                $result['target_name'] = '';
                $target = $val['target'];
                if($winTarget === 'same'){
                    $this->same($val,$user);
                }else {
                    if ($target == $winTarget){//赢
                        $ret = $this->win($val, $user,$isHalf);
                        $result['win'] += $ret['win'];
                        $result['fee'] += $ret['fee'];
                        $result['capital'] += $val['money'];
                        $result['win_capital'] += $val['money'];
                    } else {
                        $ret = $this->lose($val, $user,$isHalf);
                        $result['capital'] += $ret['capital'];
                    }
                }
            }
            $page++;
        }
        return $result;
    }

    //上半场进球较多； 上/下半场进球相同； 下半场进球较多；
    public function _maxGoals(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $allScoreTotal = $this->play['team_home_score'] + $this->play['team_guest_score'];
        $halfScoreTotal = $this->play['team_home_half_score'] + $this->play['team_guest_half_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        /**
         *
        'max_goals_1' => '上半场较多',
        'max_goals_2' => '上/下半场相同',
        'max_goals_3' => '下半场较多',
         */
        $winTarget = '';
        if(($allScoreTotal - $halfScoreTotal) < $halfScoreTotal){
            $winTarget = 'max_goals_1';
            $result['target_name'] = '上半场较多';
        }elseif(($allScoreTotal - $halfScoreTotal) > $halfScoreTotal){
            $winTarget = 'max_goals_3';
            $result['target_name'] = '下半场较多';
        }elseif(($allScoreTotal - $halfScoreTotal) == $halfScoreTotal){
            $winTarget = 'max_goals_2';
            $result['target_name'] = '上/下半场相同';
        }
        $result['target'] = $winTarget;
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];
                if($winTarget == $target){
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }

    //波胆
    public function _bodan(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        //计算结果
        foreach($rule as $k => $v){
            $result['item'] = $k;
            if($k != 'other'){
                $s1 = $v[1];
                $s2 = $v[2];
                if($team_guest_score == $team_home_score  &&  $s1 == $s2 && $team_home_score == $s1){ //平
                    $result['target'] = 'same';
                    $result['target_name'] = "相同 {$v[0]}";
                    //$result = ['target' => 'same','item' => $k];
                    break;
                }elseif($team_home_score == $s1 && $team_guest_score == $s2){ //主胜
                    $result['target'] = 'home';
                    $result['target_name'] = "主 {$v[0]}";
                    //$result = ['target' => 'home','item' => $k];
                    break;
                }elseif($team_home_score == $s2 && $team_guest_score == $s1){ //客胜
                    //$result = ['target' => 'guest','item' => $k];
                    $result['target'] = 'guest';
                    $result['target_name'] = "客 {$v[0]}";
                    break;
                }
            }
        }
        if(!isset($result['target'])){
            //$result = ['target' => 'other','item' => 'other']; //其它
            $result['target'] = 'other';
            $result['target_name'] = "其他";
        }
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }

            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];
                $item = $val['item'];
                if($target == $result['target'] && $item == $result['item']){
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }
    //波胆组合
    public function _bodanComb(){
        $result = [];
        $result['win'] = 0;
        $result['fee'] = 0;
        $result['capital'] = 0;
        $result['win_capital'] = 0;
        $limit = 100;
        $offset = 0;
        $page = 0;
        $team_home_score = $this->play['team_home_score'];
        $team_guest_score = $this->play['team_guest_score'];
        $rule = $this->getRules(GAME_TYPE_FOOTBALL,$this->arena['rules_type']);
        if(!$rule){
            throw new Exception("匹配玩法失败");
        }
        $winTarget = '';
        $winItem = '';
        foreach($rule as $key => $val){
            $scoreList = $val[1];
            $winItem = $key;
            foreach( $scoreList as $score){
                if($score[0] == $team_home_score && $score[1] == $team_guest_score){
                    $winTarget = 'home';
                    $result['target_name'] = "主 {$val[0]}";
                    break 2;
                }elseif($score[1] == $team_home_score && $score[0] == $team_guest_score){
                    $winTarget = 'guest';
                    $result['target_name'] = "客 {$val[0]}";
                    break 2;
                }
            }
            $winItem = "";
        }
        $result['target'] = $winTarget;
        $result['item'] = $winItem;
        if(!$winTarget){
            $result['target_name'] = "其它";
            $result['target'] = 'other';
            $result['item'] = 'other';
        }
        //var_dump($team_home_score,$team_guest_score);
        //var_dump($rule);
        //var_dump($result);
        while(true) {
            $offset = $page * $limit;
            $detail = Db::name('arena_bet_detail')->limit($offset, $limit)->where(['arena_id' => $this->arenaId])->select();
            if (!$detail){
                break;
            }
            foreach ($detail as $val) {
                $user = Db::name("user")->where(['id' => $val['user_id']])->find();
                if (!$user){
                    throw  new Exception("无效结算结果，用户未找到(擂台:{$this->arenaId},投注ID：{$val['id']},用户ID:{$val['user_id']})");
                }
                $target = $val['target'];
                $item = $val['item'];/*
                $scoreList = $rule[$item][1];
                $win = 0;
                foreach($scoreList as $score){
                    if(
                    ($target == 'home' && $score[0] == $team_home_score && $score[1] == $team_guest_score)
                    || ($target == 'guest' && $score[1] == $team_home_score && $score[0] == $team_guest_score)
                    ){
                        /*$result = [
                            'target' => $target,
                            'item'  => $item,
                        ];*
                        $result['target'] = $target;
                        $result['item'] = $item;
                        $win = 1;
                        break;
                    }
                }*/
                if($winTarget == $target && $item == $winItem){
                    $ret = $this->win($val,$user);
                    $result['win'] += $ret['win'];
                    $result['fee'] += $ret['fee'];
                    $result['capital'] += $val['money'];
                    $result['win_capital'] += $val['money'];
                }else{
                    $this->lose($val,$user);
                }
            }
            $page++;
        }
        return $result;
    }


    public function getRules($game_type,$key){
        $rules = config("rules.".$game_type);
        if(!isset($rules['list'])){return false;}
        $rules = $rules['list'];
        if(!isset($rules[$key])){return false;}
        return $rules[$key];
    }
    
}