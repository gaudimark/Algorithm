<?php

namespace library\service\arena;
use library\service\Rule;
use think\Db;

class Football{
    private $gameType = GAME_TYPE_FOOTBALL;
    private $rulesList = [];
    private $rulesListData = [];
    //赔率未包含本金的玩法
    public $capital = [];//[RULES_TYPE_ASIAN,RULES_TYPE_OU,RULES_TYPE_SINGLE_DOUBLE];

    public function __construct(){
        $this->rulesList = config("rules.{$this->gameType}");
        $this->rulesListData = (new Rule())->factory($this->gameType)->rulesListAll();
        $capital = (new Rule())->asianRules;
        if(isset($capital[$this->gameType])){
            $this->capital = $capital[$this->gameType];
        }
    }
    
    /**
     * 检查当前投注项目是不否存在擂台投注项的列表中
     * @param $target
     * @param $tatgetList
     * @return 返回匹配的擂台投注项数据
     */
    public function checkArenaTarget($target,$targetList,$item = ''){
        foreach($targetList as $val){
            if((!$val['item'] && $val['target'] == $target) || ($val['item'] && $val['item'] == $item && $val['target'] == $target)){
                return $val;
            }
        }
        return false;
    }

    /**
     * 发布擂台检查玩法是否存在
     * @param $rules_id
     * @return bool
     */
    public function postCheck($rules_id){
        if(isset($this->rulesList['list'][$rules_id])){
            return true;
        }
        return false;
    }


    public function betMaxLimit($odds,$totalBet,$rulesType = 0){
        if(!$odds || $odds == 0){return 0;}
        if($rulesType && !in_array($rulesType,$this->capital)){$odds = $odds -1;}//非亚盘计算的时候扣除本金
        if(!$odds){return 0;}
        return intval($totalBet / $odds);
    }
    

    public function addOddsCheck($rules,$odds){
        return $this->checkPublishOdds($odds,$rules);
    }

    /**
     * 发布擂台时，数据检查
     * @param $odds
     * @param $ruleId
     */
    public function checkPublishOdds($odds,$ruleId){
        if(!isset($this->rulesListData[$ruleId])){
            return '玩法未知';
        }
        $rulesList = $this->rulesList;
        $rule = $this->rulesListData[$ruleId];
        $list = $rulesList['list'][$rule['type']];
        $isUnder = false;
        $isOver = false;
        $isOverKey = null;
        $isHandicap = false;
        if(!in_array($ruleId,$rulesList['com'])){
            $hasHandicap = $rule['type'] == RULES_TYPE_ASIAN ? false : true;
            foreach ($odds as $key => $v) {
                $v['target'] = (string)$v['target'];
                if($v['target'] == 'under'){
                    $isUnder = true;
                }if($v['target'] == 'over'){
                    $isOver = true;
                    $isOverKey = $key;
                }elseif($v['target'] == 'handicap'){
                    $isHandicap = true;
                }

                if(!is_numeric($v['odds']) && !in_array($v['target'],['handicap','over','under'])){
                    return '赔率必须是数字';
                }

                if($v['target'] == 'handicap'){
                    $hasHandicap = true;
                }

                if($v['target'] == 'handicap' && $rule['type'] == RULES_TYPE_ASIAN && (!is_numeric($v['odds']))){
                    //return '未设置盘口(让球)';
                    return '盘口(让球)填写错误';
                }

                if($v['target'] == 'under' && $rule['type'] == RULES_TYPE_OU){
                    $todds = $v['odds'];
                    $isErr = false;
                    if(stripos($todds,"/") !== false){
                        list($a,$b) = explode("/",$todds);
                        if(!is_numeric($a) || !is_numeric($b)){
                            $isErr = true;
                        }elseif(($a > 0 && $b < 0) || ($a < 0 && $b > 0)){
                            $isErr = true;
                        }elseif(abs($a) > abs($b)){
                            $isErr = true;
                        }
                    }elseif(!is_numeric($todds)){
                        $isErr = true;
                    }
                    if($isErr){
                        return '大小玩法预设总分填写错误';
                    }
                    //return '大小玩法未设置界值';
                }

                if($v['odds'] <= 0 && !in_array($v['target'],['handicap','over','under']) ){
                    return '当前玩法赔率必须大于0';
                }

                if(!in_array($rule['type'],$this->capital) && $v['odds'] <= 1){
                    return '当前玩法赔率必须大于1';
                }/*



                if (
                    !isset($list[$v['target']]) || ($ruleId != RULES_TYPE_ASIAN && $v['odds']-1 <= 0)
                ){
                    return '当前玩法赔率必须大于1';
                }elseif (
                    ($ruleId == RULES_TYPE_ASIAN && $v['target'] != 'handicap' && $v['odds'] <= 0)
                ){
                    return '当前玩法赔率必须大于0';
                    //return false;
                }*/
            }
            if(!$hasHandicap){
                return '未设置盘口(让球)';
            }
        }else{ //比分、比分组合
            foreach ($odds as $v){
                if(!is_numeric($v['odds'])){
                    return '赔率必须是数字';
                }
                if(!isset($list[$v['item']]) || $v['odds']-1 <= 0){
                    return '当前玩法赔率必须大于1';
                    //return false;
                }
            }
        }

        if($isOverKey && !$isUnder){ //统一预设总分标识
            $odds[$isOverKey]['target'] = 'under';
        }
        if($rule['type'] == RULES_TYPE_ASIAN && !$isHandicap){
            return '未设置让分值';
        }
        if(in_array($rule['type'],[RULES_TYPE_OU,RULES_TYPE_KILL_NUM]) && !$isUnder && !$isOver){
            return '未设置预设总分值';
        }
        return $odds;
    }

    /**
     * 初始化擂台TargetList数据列表
     * @param $rules_type
     * @param $deposit
     * @param $odds
     * @return array
     */
    public function getTargetData($rules_id,$deposit,$odds){
        $bodanSame = getBodanSameScore();
        $targetData = [];
        $bet_total = [];
        $rules = $this->rulesListData;
        $ruleType = $this->rulesListData[$rules_id]['type'];
        foreach($this->rulesList['list'][$ruleType] as $kr => $r){
            if(in_array($rules_id,$this->rulesList['com'])){ //分队分别统计
                if($kr == 'other'){
                    $bet_total['other'][$kr] = ['number' => 0,'bonus' => 0,'money' => 0,'deposit' => $deposit];
                    $targetData[] = [
                        'target' => 'other',
                        'item' => $kr,
                        'number' => 0,
                        'money' => 0,
                        'bonus' => 0,
                        'deposit' => $deposit,
                    ];

                }elseif(in_array($kr,$bodanSame)){
                    $bet_total['same'][$kr] = ['number' => 0,'bonus' => 0,'money' => 0,'deposit' => $deposit];
                    $targetData[] = [
                        'target' => 'same',
                        'item' => $kr,
                        'number' => 0,
                        'money' => 0,
                        'bonus' => 0,
                        'deposit' => $deposit,
                    ];
                }else{
                    $bet_total['home'][$kr] = ['number' => 0,'bonus' => 0,'money' => 0,'deposit' => $deposit];
                    $bet_total['guest'][$kr] = ['number' => 0,'bonus' => 0,'money' => 0,'deposit' => $deposit];
                    $targetData[] = [
                        'target' => 'home',
                        'item' => $kr,
                        'number' => 0,
                        'money' => 0,
                        'bonus' => 0,
                        'deposit' => $deposit,
                    ];
                    $targetData[] = [
                        'target' => 'guest',
                        'item' => $kr,
                        'number' => 0,
                        'money' => 0,
                        'bonus' => 0,
                        'deposit' => $deposit,
                    ];
                }
            }else{
                $bet_total[$kr] = ['number' => 0,'bonus' => 0,'money' => 0,'deposit' => $deposit];
                $targetData[] = [
                    'rules_type' => $rules_id,
                    'target' => $kr,
                    'item' => '',
                    'number' => 0,
                    'money' => 0,
                    'bonus' => 0,
                    'deposit' => $deposit,
                ];
            }
        }
        return [$bet_total,$targetData];
    }

    /**
     * 将表arena_target数据列表转换成擂台表bet_total数据内容（json)
     * @param $targetList
     * @return mixed
     */
    public function arenaTargetListToBetTotal($targetList){
        $bet = [];
        foreach($targetList as $val){
            $temp = ['money' => $val['money'],'bonus' => $val['bonus'],'number' => $val['number'],'deposit' => $val['deposit']];
            if($val['item']){
                $bet[$val['target']][$val['item']] = $temp;
            }else{
                $bet[$val['target']] = $temp;
            }
        }
        return json_encode($bet);
    }
}