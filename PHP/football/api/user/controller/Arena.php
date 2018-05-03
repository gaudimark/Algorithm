<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 10:28
 */
namespace app\user\controller ;
use app\user\logic\User;
use library\service\Image;
use library\service\Misc;
use library\service\Oauth;
use library\service\Play;
use library\service\Rule;
use think\Cache;
use think\Db;

class Arena extends User{
    public function __construct(){
        parent::__construct();
    }


    /**
     * 我的擂台
     */
    public function lists(){
        $page = max(1,input("page/d",0));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $where['user_id'] = $this->myUserId;
        $lists = Db::name('arena')->order("create_time desc")->where($where)->field("id,mark,classify,user_id,game_type as item_id,play_id,match_id,deposit,bet_money,bet_number,rules_type,rules_id,status,create_time,win")->limit($offset,$limit)->select();
        $playSvr = new Play();
        foreach($lists as $key => $val){

            $play = $playSvr->getPlay($val['play_id'],['id','play_time','status','match_time','bo']);
            if($play['status'] == PLAT_STATUS_START){
                $play['match_time'] = getMatchRunTime($play['match_time'], $play['play_time']);
            }
            if($val['item_id'] == GAME_TYPE_WCG && $play['bo'] && $play['bo'] > 0){
                $play['bo'] = "BO{$play['bo']}";
            }else{
                $play['bo'] = '';
            }
            $val['play_time'] = $play['play_time'];
            $val['play'] = $play;
            $val['teams'] = $playSvr->getTeams($val['play_id'],['id','name','logo']);
            $val['match'] = getMatch($val['match_id'],'',['id','name','logo','bgcolor']);
            $arenaRule = getRuleData($val['item_id'],$val['rules_id']);
            if($arenaRule){
                $val['rule'] = ['name' => $arenaRule['name'], 'alias' => $arenaRule['alias'],'type' => $arenaRule['type'], 'intro' => $arenaRule['intro'], 'help_intro' => $arenaRule['help_intro'],];
            }else{
                $val['rule'] = ['name' => '', 'alias' => '', 'intro' => '', 'help_intro' => '','type' => ''];
            }
            $lists[$key] = $val;
        }


        $total = '';//Cache("USER_ARENA_LISTS_TOTAL_{$this->myUserId}");
        if(!$total){
            $total = Db::name('arena')->where($where)->count();
            Cache("USER_ARENA_LISTS_TOTAL_{$this->myUserId}",$total,3600);
        }
        $totalPage = ceil($total / $limit);
        $nextPage = $totalPage > $page ? 1: 0;

        return $this->retSucc('user.arena_lists',$lists,'',['nextPage' => $nextPage,'total_page' => $totalPage]);

    }


    /**
     * 擂台详情0
     */
    public function detail(){
        $arenaId = input("arena_id/d");
        if(!$arenaId){
            return $this->retErr('user.arena_detail',10004);
        }

        $arenaSvr = (new \library\service\Arena());
        $arena = $arenaSvr->getCacheArenaById($arenaId);

        if(!$arena || $arena['user_id'] != $this->myUserId || $arena['has_sys'] || $arena['has_robot']){
            //return $this->retErr('user.arena_detail',40001);
        }

        $playSvr = new Play();
        $play = $playSvr->getPlay($arena['play']['id'],['id','play_time','status','match_time','bo']);
        if($play['status'] == PLAT_STATUS_START){
            $play['match_time'] = getMatchRunTime($play['match_time'], $play['play_time']);
        }
        if($arena['game_type'] == GAME_TYPE_WCG && $play['bo'] && $play['bo'] > 0){
            $play['bo'] = "BO{$play['bo']}";
        }else{
            $play['bo'] = '';
        }
        $teams = $playSvr->getTeams($arena['play']['id'],['id','name','logo','has_home','score','half_score','red','yellow','score_json'],$this->myUserId);
        $arena['teams'] = $teams;
        //$teams = (new Play())->getTeams($arena['play_id'],['id','name','logo','has_home','score','half_score','first_score','red','yellow']);
        $match = [
            'id' => $arena['match']['id'],
            'name' => $arena['match']['name'],
            'bgcolor' => $arena['match']['bgcolor'],
            'logo' => $arena['match']['logo'],
        ];
        //$arena['rules_id'] = $arena['rules_type'];
        $odds = $arena['odds'];
        $arena['odds'] = (new Rule())->factory($arena['game_type'])->parseOddsWords($arena['odds'],$arena['rules_id'],$arena['game_type'] == GAME_TYPE_FOOTBALL ? [] : $teams);

        //最高支付奖金
        $arena['max_pay_money'] = 0;
        //最低支付奖金
        $arena['min_pay_money'] = null;
        //二维码地址
        $arena['qr_code_url'] = get_image_thumb_url($arenaSvr->getQrCode($arenaId,$this->token));

        $arena['item_id'] = $arena['game_type'];
        $odds = $arena['odds'];
        $bet_total = $arena['bet_total'];
        $target_list = $arena['target_list'];
        foreach($target_list as $val){
            if(!in_array($val['target'],['handicap','over','under'])){
                $arena['max_pay_money'] = max($arena['max_pay_money'], $val['bonus']);
                $arena['min_pay_money'] = !is_null($arena['min_pay_money']) ? min($arena['min_pay_money'], $val['bonus']) : $val['bonus'];
            }
            if(isset($odds[$val['target'].$val['item']])){
                //$total = $arena['deposit'];//+$arena['bet_money']-$val['money'];
                //可接收的总投注$val['deposit'];//
                $odds[$val['target'] . $val['item']]['bet_total'] = $arenaSvr->factory($arena['game_type'])->betMaxLimit($odds[$val['target'].$val['item']]['odds'],$val['deposit'],$arena['rules_type']);
                //已投注
                //$odds[$val['target'] . $val['item']]['bet'] = $arenaSvr->factory($arena['game_type'])->betMaxLimit($odds[$val['target'].$val['item']]['odds'],($arena['deposit']+$arena['bet_money']-$odds[$val['target'] . $val['item']]['money']),$arena['rules_id']);
                if($arena['classify'] == ARENA_CLASSIFY_CREDIT){ //征信局扣除本金
                    $odds[$val['target'] . $val['item']]['bonus'] = $val['bonus']-$val['money'];
                }else{
                    $odds[$val['target'] . $val['item']]['bonus'] = $val['bonus'];
                }
                $odds[$val['target'] . $val['item']]['number'] = $val['number'];
                $odds[$val['target'] . $val['item']]['money'] = $val['money'];
                $odds[$val['target'] . $val['item']]['real_money'] = 0;
                $odds[$val['target'] . $val['item']]['rate'] = ($arena['deposit'] + $arena['bet_money']) ? numberFormat($val['bonus'] / ($arena['deposit'] + $arena['bet_money']) * 100,2) : 0;
            }

        }
        //$arena['target_list'] = $target_list;


        $arena['win_target'] = $arena['win_target'] ? @json_decode($arena['win_target'],true) : '';
        if($arena['win_target'] && $arena['classify'] == ARENA_CLASSIFY_CREDIT){ //征信局支付资金要扣除本金
            //$arena['win_target']['win'] = $arena['win_target']['win'] - $arena['win_target']['capital'];
        }
        $arena['play'] = $play;
        //$arena['play']['teams'] = $teams;
        $arena['match'] = $match;
        $arenaRule = getRuleData($arena['item_id'],$arena['rules_id']);
        if($arenaRule){
            $arena['rule'] = ['name' => $arenaRule['name'], 'alias' => $arenaRule['alias'],'type' => $arenaRule['type'], 'intro' => $arenaRule['intro'], 'help_intro' => $arenaRule['help_intro'],];
        }else{
            $arena['rule'] = ['name' => '', 'alias' => '', 'intro' => '', 'help_intro' => '','type' => ''];
        }
        //如果擂台已结算
        $returnGold = 0;
        if($arena['status'] == ARENA_STATEMENT_END){
            $returnGold = Db::name('arena_bet_detail')->where(['arena_id' => $arena['id'],'status' => DEPOSIT_SAME])->sum('money');
            $lst = Db::name('arena_bet_detail')->field("sum(money) as money,sum(win_money) as win_money,sum(fee) as fee,target,item,status")->group("status,target,item")->where(['arena_id' => $arena['id'],'status' => ['in',[DEPOSIT_WIN,DEPOSIT_SAME,DEPOSIT_LOST_HALF,DEPOSIT_WIN_HALF]]])->select();
            if($arena['classify'] == ARENA_CLASSIFY_CREDIT){
                foreach ($lst as $val) {
                    if (isset($odds[$val['target'] . $val['item']])){
                        if (!isset($odds[$val['target'] . $val['item']]['real_money'])){
                            $odds[$val['target'] . $val['item']]['real_money'] = 0;
                        }
                        if (in_array($val['status'], [DEPOSIT_WIN, DEPOSIT_WIN_HALF])) {
                            $odds[$val['target'] . $val['item']]['real_money'] += ($val['win_money'] - $val['money']);
                            $arena['win_target']['win'] = $arena['win_target']['win'] - $val['money'];
                        }/*elseif ($val['status'] == DEPOSIT_LOST_HALF) { //征信局支付资金扣除本金中包括了输一半的本金，此处再加一半回来
                            $arena['win_target']['win'] += numberFormat($val['money'] / 2);
                        }*/
                    }
                }
            }else {
                foreach ($lst as $val) {
                    if (isset($odds[$val['target'] . $val['item']])){
                        if (!isset($odds[$val['target'] . $val['item']]['real_money'])){
                            $odds[$val['target'] . $val['item']]['real_money'] = 0;
                        }

                        if ($val['status'] == DEPOSIT_SAME){
                            $odds[$val['target'] . $val['item']]['real_money'] += $val['money'];
                        } elseif ($val['status'] == DEPOSIT_LOST_HALF) {
                            $returnGold += numberFormat($val['money'] / 2);
                            $odds[$val['target'] . $val['item']]['real_money'] += numberFormat($val['money'] / 2);
                        } elseif (in_array($val['status'], [DEPOSIT_WIN, DEPOSIT_WIN_HALF])) {
                            $odds[$val['target'] . $val['item']]['real_money'] += ($val['win_money'] + $val['fee']);
                        }
                    }
                }
            }
        }
        $arena['return_gold'] = floatval($returnGold);//中一半退回投注都本金合计
        $arena['ret_credit_gold'] = isset($arena['ret_credit_gold']) ? floatval($arena['ret_credit_gold']) : 0; //征信局收回本金
        $arena['odds'] = $odds;
        $billPic = isset($arena['credit_bill_pic']) ? $arena['credit_bill_pic'] : '';
        $arena['bill_pic'] = ''; //征信擂台账单图片
        if(!$billPic &&  $arena['status'] == ARENA_STATEMENT_END){ //如果未生成图表，重新生成一次
            $billPic = (new Image())->bettingByCreditArena($arenaId);
        }
        if($billPic){
            $_ = is_string($billPic) ? @json_decode($billPic,true) : $billPic;
            $billPic = [];
            foreach($_ as $val){
                $billPic[] = get_image_thumb_url($val);
            }
            $arena['bill_pic'] = array_values($billPic);
        }
        if($arena['company_id']){
            $oddsCompany = cache('odds_company');
            $company  = isset($oddsCompany[$arena['company_id']]) ? $oddsCompany[$arena['company_id']] : [];
            $arena['company_name'] = $company['name'];
        }


        unset($arena['credit_bill_pic']);
        unset($arena['update_time']);
        unset($arena['has_sys']);
        unset($arena['has_robot']);
        //unset($arena['rules_type']);
        unset($arena['bet_total']);
        unset($arena['target_list']);
        unset($arena['game_type']);
        unset($arena['play_id']);
        unset($arena['match_id']);
        unset($arena['user_id']);
        unset($arena['user_nickname']);

        return $this->retSucc('user.arena_detail',$arena);
    }


    /**
     * 擂台投注列表
     */
    public function bet_list(){
        $arenaId = input("arena_id/d");
        $page = max(1,input("page/d",0,'intval'));
        $limit = 10;
        if(!$arenaId){
            return $this->retErr('user.arena_bet_list',10004);
        }

        $arenaSvr = (new \library\service\Arena());
        $arena = $arenaSvr->getCacheArenaById($arenaId);

        if(!$arena || $arena['user_id'] != $this->myUserId || $arena['has_sys'] || $arena['has_robot']){
            return $this->retErr('user.arena_bet_list',40001);
        }

        $teams = $arena['teams'];
        $offset = ($page - 1) * $limit;
        $lists = Db::name('arena_bet_detail')->where(['arena_id' => $arenaId])->order("id desc")->limit($offset,$limit)->select();
        $total = $arena['bet_number'];
        $totalPage = ceil($total / $limit);
        $data = [];
        if($lists){
            $ruleSvr = (new Rule())->factory($arena['game_type']);
            foreach ($lists as $val){
                $user = getUser($val['user_id']);
                $data[] = [
                    'user_id' => $val['user_id'],
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                    'level' => $user['level'],
                    'money' => $val['money'],
                    'odds' => $val['odds'],
                    'target' => $val['target'],
                    'item' => $val['item'],
                    'handicap' => $val['handicap'],
                    'under' => $val['under'],
                    'over' => $val['over'],
                    'status' => $val['status'],
                    'win_money' => $val['win_money'] ? $val['win_money'] : 0,
                    'target_name' => $ruleSvr->getBetTargetToRulesName($arena['rules_type'],$val['target'],$val['item'],$teams)
                ];
            }
        }
        return $this->retSucc('user.arena_bet_list',$data,'',['next_page' => $totalPage > $page ? 1 : 0,'total_page' => $totalPage]);
    }

    /**
     * 发布擂台获取基本设置
     */
    public function publish_get(){
        $playId = input("play_id/d");
        $ruleId = input("rule_id/d");
        $itemValue = input("item_value");
        $system = config('system');
        if(!$playId || !$ruleId){
            return $this->retErr('user.arena_publish_get',10004);
        }
        $playSvr = new \library\service\Play();
        $play = $playSvr->getPlay($playId,['status','play_time','game_type','min_deposit','match_id','game_id']);
        $ruleSvr = (new Rule())->factory($play['game_type']);

        $system = config('system');

        $teams = $play['game_type'] == GAME_TYPE_FOOTBALL ? [] : $playSvr->getTeams($playId);
        $ret = Db::name('odds')->alias('o')
                    ->join('__ODDS_COMPANY__ oc','o.odds_company_id = oc.id','LEFT')
                    ->order('oc.id asc')
                    ->where(['o.play_id' => $playId,'o.rules_id' => $ruleId,'oc.id' => ['gt',0]])
                    //->field('o.odds')
                    ->field('o.id,o.odds,o.play_id,o.game_type,o.odds_company_id as company_id,o.rules_id as rule_id,o.rules_type as rules_type,o.odds,oc.`name` as company_name')
                    ->find();
        $odds = [];
        if($ret){
            $odds = @json_decode($ret['odds'],true);
            $odds = isset($odds['time']) ? $odds['time'] : $odds['init'];
            $odds = $ruleSvr->parseOddsWords($odds, $ret['rule_id'], $teams);
            if($odds){
                foreach($odds as $key => $val){
                    unset($val['money']);
                    $odds[$key] = $val;
                }
            }
        }else{
            $odds = (new Rule())->factory($play['game_type'])->getDefaultOdds($ruleId,$teams);
            $odds = isset($odds['time']) ? $odds['time'] : $odds['init'];
            $odds = (new Rule())->factory($play['game_type'])->parseOddsWords($odds, $ruleId, $teams);
            if($odds){
                foreach($odds as $key => $val){
                    unset($val['money']);
                    $odds[$key] = $val;
                }
            }
        }

        if(!$play['min_deposit']){
            $play['min_deposit'] = $playSvr->getMinDeposit($play['game_type'],$ruleId);
        }
        $play['rule_id'] = $ruleId;
        $play['rule_type'] = intval($ruleSvr->getRuleType($ruleId));
        //$play['item_id'] = $play['game_type'];
        $play['brokerage'] = (new Misc())->getMakerBrokerage();
        $play['company_id'] = (int)$ret['company_id'];
        $play['company_name'] = (string)$ret['company_name'];
        $play['odds_id'] = (int)$ret['id'];
        $play['item_id'] = $play['game_type'];
        $play['min_bet_money'] = (double)$system['sys_user_min_bet_money'] ?: 0;
        unset($play['game_type']);
        $play['odds'] = array_values($odds);
        //项目标识值
        $play['item_value'] = getItemValue($play['item_id'],[
            ['type' => 'match','value' => $play['match_id']],
            ['type' => 'game','value' => $play['game_id']],
        ]);//$itemValue;

        //$play['teams'] = $playSvr->getTeams($playId,['id','name','logo','logo_big','has_home']);
        return $this->retSucc('user.arena_publish_get',$play);
    }

    //赔率公司列表-参考赔率
    public function odds_list(){
        $playId = input("play_id/d");
        $ruleId = input("rule_id/d");

        if(!$playId || !$ruleId){
            return $this->retErr('user.arena_odds_list',10004);
        }

        $oddsCompany = cache('odds_company');
        $Ids = [];
        foreach($oddsCompany as $val){
            $Ids[] = $val['id'];
        }
        $oddsList = [];
        $play = (new Play())->getPlay($playId);
        $teams = (new Play())->getTeams($playId);
        $ruleSvr = (new Rule())->factory($play['game_type']);
        if($Ids){
            $temp = [];
            $oddsList = Db::name('odds')->alias('o')->where(['o.play_id' => $playId, 'o.rules_id' => $ruleId, 'odds_company_id' => ['in', array_values($Ids)]])//->field('o.odds')
                ->field('o.id,o.play_id,o.game_type as item_id,o.odds_company_id as company_id,o.rules_type as rule_type  , o.rules_id as rule_id,o.odds')->select();
            if ($oddsList){
                foreach ($oddsList as $key => $val) {
                    //$val['rule_type'] = $ruleSvr->getRuleType($val['rule_id']);
                    $temp[$val['company_id']][] = $val['id'];
                    $val['company_name'] = $oddsCompany[$val['company_id']]['name'];
                    $odds = @json_decode($val['odds'], true);
                    $odds = isset($odds['time']) ? $odds['time'] : $odds['init'];
                    $_ = $val['item_id'] == GAME_TYPE_FOOTBALL ? [] : $teams;
                    $odds = $ruleSvr->parseOddsWords($odds, $val['rule_id'], $_);
                    if ($odds){
                        foreach ($odds as $k => $o) {
                            unset($o['money']);
                            $odds[$k] = $o;
                        }
                    }
                    $val['odds'] = array_values($odds);
                    $oddsList[$val['id']] = $val;
                }
            }

            $data = [];
            //排序
            foreach ($oddsCompany as $val){
                if(isset($temp[$val['id']])){
                    foreach($temp[$val['id']] as $v){
                        $data[] = $oddsList[$v];
                    }
                }
            }
            $oddsList = $data;
        }
        return $this->retSucc('user.arena_odds_list',array_values($oddsList));
    }

    /**
     * 发布擂台
     */
    public function publish_save(){
        if($this->request->isPost()){
            $playId = input("post.play_id/d");
            $ruleId = input("post.rule_id/d");
            $oddsId = input("post.odds_id/d");
            $companyId = input("post.company_id/d");
            $deposit = input("post.deposit/d");
            $minBet = input("post.min_bet/d");
            $maxBet = input("post.max_bet/d");
            $private = input("post.private/d");
            $inCode = input("post.invit_code");
            $hasHide = intval(input("post.has_hide/d"));
            $intro = input("post.intro");
            $odds = input("post.odds");
            $auto_update_odds = input("post.auto_update_odds/d",0);
            $classify = input("post.classify");
            $odds = json_decode(base64_decode($odds),true);
            if(!$deposit && $classify != ARENA_CLASSIFY_CREDIT){
                return $this->retErr('user.arena_publish_save',40101);
            }
            if(!$odds && !$auto_update_odds){
                return $this->retErr('user.arena_publish_save',40100);
            }
            if(!$this->checkCsrf(input("csrf"))){ //防止重复提交
                $msg = lang(10005);
               // return $this->retErr('csrf',"{$msg}(CSRF)");
            }
            $data = [
                'has_sys' => 0,
                'has_robot' => 0,
                'play_id' => $playId,
                'rule_id' => $ruleId,
                'odds_id' => $oddsId,
                'company_id' => $companyId,
                'deposit' => $deposit,
                'min_bet' => $minBet,
                'max_bet' => $maxBet,
                'private' => $private,
                'invit_code' => $inCode,
                'has_hide' => $hasHide,
                'intro' => $intro,
                'odds' => $odds,
                'odds_id' => $oddsId,
                'classify' => $classify,
                'auto_update_odds' => $auto_update_odds,
            ];
            $arenaSvr = new \library\service\Arena();
            $guid = (new Oauth())->getBindTokenData($this->token,'guid');
            //if(false !== $ret = $arenaSvr->publish($deposit,$odds,$this->myUserId,$playId,$ruleId,$minBet,$maxBet,$oddsId,$private,$inCode,0,0,$hasHide,$intro,$classify)){
            if(false !== $ret = $arenaSvr->publish($data,$this->myUserId,$guid)){
                return $this->retSucc('user.arena_publish_save',[
                    'id' => $ret['id'],
                    'mark' => $ret['mark'],
                    'deposit' => intval($deposit),
                ],40109);
            }else{
                return $this->retErr('user.arena_publish_save',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }

            /** 赔率格式
             * 亚盘
             * {['target' => 'home','item' => '','odds' => 0.85],['target' => 'handicap','item' => '','odds' =>-0.75],['target' => 'guest','item' => '','odds' => 0.90]
             * //欧盘
             *{['target' => 'home','item' => '','odds' => 1.85],['target' => 'guest','item' => '','odds' =>1.75],['target' => 'same','item' => '','odds' => 1.80]}
             *
             */
        }
        return $this->retErr('user.arena_publish_save',10000);
    }

    /**
     * 修改赔率
     */
    public function modify_odds(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $odds = input("post.odds");
            $odds = json_decode(base64_decode($odds),true);


            if(!$odds){
                return $this->retErr('user.arena_modify_odds',40100);
            }
            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->modifyOdds($arenaId,$odds,$this->myUserId)){
                return $this->retSucc('user.arena_modify_odds',[
                    'id' => $arenaId,
                    'odds' => $odds
                ],40116);
            }else{
                return $this->retErr('user.arena_modify_odds',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }
        }
        return $this->retErr('user.arena_modify_odds',10000);
    }

    /**
     * 追加擂台保证金
     */
    public function append_deposit(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $deposit = input("post.deposit/d");
            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->appendDeposit($arenaId,$deposit,$this->myUserId)){
                return $this->retSucc('user.arena_append_deposit',[
                    'id' => $arenaId,
                    'deposit' => $deposit
                ],40125);
            }else{
                return $this->retErr('user.arena_append_deposit',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }

        }
        return $this->retErr('user.arena_append_deposit',10000);
    }

    /**
     * 停止投注
     */
    public function stop(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->sealArena($arenaId,$this->myUserId)){
                return $this->retSucc('user.arena_seal',[
                    'id' => $arenaId,
                ],40128);
            }else{
                return $this->retErr('user.arena_seal',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }

        }
        return $this->retErr('user.arena_seal',10000);
    }

    /**
     * 开启投注
     */
    public function open(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->unsealArena($arenaId,$this->myUserId)){
                return $this->retSucc('user.arena_unseal',[
                    'id' => $arenaId,
                ],40131);
            }else{
                return $this->retErr('user.arena_unseal',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }

        }
        return $this->retErr('user.arena_unseal',10000);
    }

    /**
     * 擂台设置
     */
    public function conf(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $minBet = input("post.min_bet/d");
            $maxBet = input("post.max_bet/d");
            $private = input("post.private/d");
            $auto_update_odds = input("post.auto_update_odds/d",0);
            $hasHide = input("post.has_hide");
            $odds_id = input("post.odds_id/d",0);
            
            $data = [
                'min_bet' => $minBet,
                'max_bet' => $maxBet,
                'private' => $private,
                'auto_update_odds' => $auto_update_odds,
                'has_hide' => $hasHide,
                'odds_id' => $odds_id,
            ];

            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->Conf($arenaId,$data,$this->myUserId)){
                return $this->retSucc('user.arena_conf',[
                    'id' => $arenaId,
                ],'OK');
            }else{
                return $this->retErr('user.arena_conf',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }

        }
        return $this->retErr('user.arena_conf',10000);
    }

    public function recommend(){
        $arenaId = input("arena_id/d");
        $arenaSvr = new \library\service\Arena();
        $arena = $arenaSvr->getCacheArenaById($arenaId);
        if(!$arena){
            return $this->retErr('user.arena_recommend',10005);
        }
        $ret = Db::name('arena')->field('id,rules_type')->where(['has_hide'=>0,'has_sys' => 1,'status' => ARENA_START,'classify' => ARENA_CLASSIFY_GOLD,'play_id' => $arena['play_id'],'id'=>['neq',$arenaId],'rules_type' => $arena['rules_type']])->order("(deposit+bet_money) desc")->limit(1)->find();
        $limit = 3;
        if($ret){$limit--;}
        $otherArena = Db::name('arena')->field('id,rules_type')->where(['has_hide'=>0,'has_sys' => 1,'status' => ARENA_START,'classify' => ARENA_CLASSIFY_GOLD,'play_id' => $arena['play_id'],'id'=>['neq',$arenaId],'rules_type' => ['neq',$arena['rules_type']]])->order("(deposit+bet_money) desc")->limit($limit)->select();
        $ret = array_merge([$ret],$otherArena);
        return $this->retSucc('user.arena_recommend',$ret);
    }

    /**
     * 授信用户列表
     */
    public function auth_user_list(){
        $arenaId = input("arena_id/d");
        $page = max(1,input("page/d"));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $arenaSvr = new \library\service\Arena();
        $arena = $arenaSvr->getCacheArenaById($arenaId);
        if(!$arena || $arena['user_id'] != $this->myUserId){
            return $this->retErr('user.arena_auth_user_list',10005);
        }
        $lists = Db::name('arena_credit')->where(['arena_id' => $arenaId])->limit($offset,$limit)->select();
        $ruleSvr = (new Rule())->factory($arena['game_type']);
        $teams = (new Play())->getTeams($arena['play_id']);
        foreach($lists as $key => $val){
            $val['bet_target'] = [];
            if($val['user_id']){
                $betList = Db::name('arena_bet_detail')->where(['arena_id' => $arenaId, 'user_id' => $val['user_id']])->select();
                if($betList){
                    $tmp = [];
                    foreach($betList as $bv){
                        $_ = $ruleSvr->getBetTargetText($arena['rules_type'],$arena['play_id'],$teams,$bv['target'],$bv['item'],$arena['rules_id']);
                        $winMoney = 0;
                        if($bv['status'] == DEPOSIT_LOSE){
                            $winMoney -= $bv['money'];
                        }elseif($bv['status'] == DEPOSIT_LOST_HALF){
                            $winMoney -= numberFormat($bv['money']/2,2);
                        }elseif(in_array($bv['status'],[DEPOSIT_WIN,DEPOSIT_WIN_HALF])){
                            $winMoney = $bv['win_money'] - $bv['money'];// + $bv['fee'];
                        }
                        $_['odds'] = $bv['odds'];
                        $_['handicap'] = $bv['handicap'];
                        $_['under'] = $bv['under'];
                        $_['money'] = $bv['money'];
                        $_['win'] = $winMoney;
                        $_['fee'] = $bv['fee'];
                        $tmp[] = $_;
                    }
                    $val['bet_target'] = $tmp;
                    unset($tmp);
                    unset($_);
                }
            }

            unset($val['create_time']);
            unset($val['update_time']);
            if($val['user_id']){
                $val['nickname'] = getUser($val['user_id'],'nickname');
            }else{
                $val['nickname'] = '';
            }
            $val['arena_url'] = $arenaSvr->getArenaUrlByCredit($arenaId,$this->token,$val['mark']);//(new \library\service\Arena())->getArenaUrl($arenaId,$this->token).;
            $val['qr_code_url'] = get_image_thumb_url($arenaSvr->getQrCodeByCredit($arenaId,$this->token,$val['mark']));
            $val['share_url'] = $arena['share_url']."?credit_mark={$val['mark']}";
            $lists[$key] = $val;
        }
        $total = Db::name('arena_credit')->where(['arena_id' => $arenaId])->count();
        $totalPage = ceil($total / $limit);
        $nextPage = $totalPage > $page ? 1: 0;
        return $this->retSucc('user.arena_auth_user_list',$lists,'',['nextPage' => $nextPage,'total_page' => $totalPage]);
    }
    /**
     * 添加授信用户
     */
    public function auth_user_add(){
        if($this->request->isPost()){
            $arenaId = input("post.arena_id/d");
            $name = input("post.name");
            $phone = input("post.phone");
            $gold = input("post.gold/d");
            $arenaSvr = new \library\service\Arena();
            if(false !== $ret = $arenaSvr->addAuthUser($arenaId,$this->myUserId,[
                    'name' => $name,
                    'phone' => $phone,
                    'gold' => $gold,
                ])){
                return $this->retSucc('user.arena_auth_user_add',[
                    'id' => $arenaId,
                    'name' => $name,
                    'phone' => $phone,
                    'gold' => $gold,
                    'code' => $ret['code'],
                    'arena_url' => $arenaSvr->getArenaUrlByCredit($arenaId,$this->token,$ret['mark']),
                    'qr_code_url' => get_image_thumb_url($arenaSvr->getQrCodeByCredit($arenaId,$this->token,$ret['mark'])),
                    'share_url' => $arenaSvr->getArenaShareUrl($arenaId)."?credit_mark={$ret['mark']}",
                ],'OK');
            }else{
                return $this->retErr('user.arena_auth_user_add',$arenaSvr->getError(),$arenaSvr->getErrorData());
            }
        }
        return $this->retErr('user.arena_auth_user_add',10000);
    }

    /**
     * 撤销授信用户
     */
    public function auth_user_cancel(){
        if($this->request->isPost()){
            $arenaId = input("arena_id/d");
            $arena_credit_id = input("arena_credit_id/d");
            $arenaSvr = new \library\service\Arena();

        if(false !== $arenaSvr->cancelAuthUser($arenaId,$this->myUserId,$arena_credit_id)){
            return $this->retSucc('user.arena_auth_user_cancel',[],'OK');
        }else{
            return $this->retErr('user.arena_auth_user_cancel',$arenaSvr->getError(),$arenaSvr->getErrorData());
        }
    }
        return $this->retErr('user.arena_auth_user_cancel',10000);

    }

   /**
     * 授信用户详情
     */
    public function auth_user_info(){
        $arenaId = input("arena_id/d");
        $userId = input("user_id/d");
        $ret = Db::name('arena_credit')->where(['id' => $userId,'arena_id' => $arenaId])->find();
        if(!$ret){
            return $this->retErr('user.arena_auth_user_info',10005);
        }else{
            unset($ret['create_time']);
            unset($ret['update_time']);
        }
        $arenaSvr = new \library\service\Arena();
        $arena = $arenaSvr->getCacheArenaById($arenaId);
        $ret['arena_url'] = $arenaSvr->getArenaUrlByCredit($arenaId,$this->token,$ret['mark']);
        $ret['qr_code_url'] = get_image_thumb_url($arenaSvr->getQrCodeByCredit($arenaId,$this->token,$ret['mark']));
        $ret['share_url'] = $arena['share_url']."?credit_mark={$ret['mark']}";
        return $this->retSucc('user.arena_auth_user_info',$ret);
    }

}