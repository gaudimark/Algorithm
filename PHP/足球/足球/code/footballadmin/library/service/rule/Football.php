<?php
namespace library\service\rule;
class Football{
    private static $rulesData = [];
    private $gameType = GAME_TYPE_FOOTBALL;
    private $rulesList = [];
    public $disRulesType = [];

    public function __construct(){
        $this->rulesList = $this->rulesListAll();
        $this->disRulesType = [
            RULES_TYPE_ALL_YELLOW,
            RULES_TYPE_FIRST_GOALS,
            RULES_TYPE_ALL_SCORE,
            RULES_TYPE_HALF_SCORE
        ];
    }

    /**
     * 玩法类型列表
     * @return array
     */
    public function rulesList(){
        $data = [];
        $data['list'] = [
            RULES_TYPE_ASIAN => '胜负(让球)',
            RULES_TYPE_EUROPE => '胜平负',
            RULES_TYPE_OU => '大小',
            RULES_TYPE_BODAN => '比分',
            RULES_TYPE_BODAN_COMB => '比分组合',
            RULES_TYPE_HOME_GOALS => '主进球',
            RULES_TYPE_GUEST_GOALS => '客进球',
            //RULES_TYPE_ALL_SCORE => '全场比分',
            //RULES_TYPE_HALF_SCORE => '半场比分',
            //RULES_TYPE_ALL_YELLOW => '全场黄牌',
            //RULES_TYPE_FIRST_GOALS => '最先进球',
            RULES_TYPE_ALL_GOALS => '全场进球',
            RULES_TYPE_SINGLE_DOUBLE => '单双',
            RULES_TYPE_MAX_GOALS => '上/下半场进球数比较',
        ];
        return $data;
    }

    public function checkRuleTypeDisabled($ruleType){
        if(in_array($ruleType,$this->disRulesType)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查玩法是否存在
     * @param $ruleId
     * @return bool
     */
    public function checkRule($ruleId){
        $ruleList = $this->rulesListAll();
        if(isset($ruleList[$ruleId])){
            return true;
        }
        return false;
    }
    /**
     * 获取玩法类型名称
     * @param $ruleType
     * @return string
     */
    public function getRuleType($ruleId){
        $rules = $this->rulesListAll();
        if(isset($rules[$ruleId])){
            return $rules[$ruleId]['type'];
        }
        return null;
    }

    /**
     * 获取指定玩法类型下的玩法列表
     */
    public function getRuleTypeChild($ruleType){
        $ruleList = $this->rulesListAll();
        $temp = [];
        foreach($ruleList as $val){
            if($val['type'] == $ruleType){
                $temp[$val['id']] = $val;
            }
        }
        return $temp;
    }


    /**
     * 获取玩法类型名称
     * @param $ruleType
     * @return string
     */
    public function getRuleTypeText($ruleType){
        $ruleType = intval($ruleType);
        $ruleType = intval($ruleType);
        $data = $this->rulesList();
        if(isset($data['list'][$ruleType])){
            return $data['list'][$ruleType];
        }
        return '未知';
    }

    /**
     * 获取玩法名称
     * @param $ruleId
     * @param string $field
     * @return array|mixed|null|string
     */
    public function getRuleText($ruleId,$field = ''){
        $rulesData = $this->rulesListAll();
        if(!isset($rulesData[$ruleId])){
            return '';
        }
        $rulesData = $rulesData[$ruleId];
        if($field && isset($rulesData[$field])){
            $rulesData = $rulesData[$field];
        }
        return $rulesData;
    }

    public function rulesListAll(){
        $rulesData  = self::$rulesData;
        if(!$rulesData){
            $rulesData  = cache("rules_{$this->gameType}");
            self::$rulesData = $rulesData;
        }
        if(!$rulesData){return null;}
        if(!isset($rulesData[$this->gameType])){return '';}
        $rulesData = $rulesData[$this->gameType];
        if($rulesData){
            foreach ($rulesData as $key => $val){
                if($this->checkRuleTypeDisabled($val['type'])){
                    $val['status'] = STATUS_DISABLED;
                }
                $rulesData[$key] = $val;
            }
        }
        return $rulesData;
    }
    /**
     * 获取玩法默认玩法选项
     * @param $ruleType
     */
    public function getDefaultExplain($ruleType){
        return [];
    }

    /*比赛与玩法对应*/
    public function playRule($play_id,$game_id,$rules_id,$item,$odds_id = 0){}

    /**
     * 将平行结构赔率数据转换成odds表数据格式
     * @param $odds
     * @param $ruleId
     * @return array
     */
    public function parseOdds($odds,$ruleId){

        $ret = [];
        if(in_array($ruleId,[RULES_TYPE_BODAN,RULES_TYPE_BODAN_COMB])){
            foreach($odds as $val){
                $ret[$val['target']][$val['item']] = str_replace("+","",$val['odds']);
            }
        }else{
            foreach($odds as $val){
                if(in_array($val['target'],['under','over'])){
                    $val['odds'] = under($val['odds'],false,false);
                }
                $ret[$val['target']] = str_replace("+","",$val['odds']);
            }
        }
        return $ret;
    }

    /**
     * 初始化赔率列表
     * @param $ruleType
     * @param null $play_id
     * @return array|bool
     */
    public function getDefaultOdds($rule_id,$play_id = null){
        $ret = ['init' => [],'time' => []];
        $odds = [];
        $same = [];
        $other = [];
        $rules = config("rules.".$this->gameType);
        if(!$rules){return false;}
        $rule = $this->rulesList[$rule_id];
        $ruleType = $rule['type'];
        if(!isset($rules['list'][$ruleType]) || !$rules['list'][$ruleType]){return false;}
        $list = $rules['list'][$ruleType];
        if(in_array($ruleType,$rules['com'])){
            $sameScore = getBodanSameScore();
            foreach($list As $key => $val){
                if($ruleType == RULES_TYPE_BODAN && in_array($key,$sameScore)){
                    $same[$key] = "";
                }elseif($key == 'other'){
                    $other[$key] = "";
                }else{
                    $odds[$key] = "";
                }
            }
            $ret['init']['guest'] = $ret['init']['home'] = $odds;
            $ret['time']['guest'] = $ret['time']['home'] = $odds;
            if($ruleType == RULES_TYPE_BODAN){
                $ret['init']['same'] = $ret['time']['same'] = $same;
                $ret['init']['other'] = $ret['time']['other'] = $other;
            }
        }else{
            foreach($list As $key => $val){
                $odds[$key] = "";
            }
            $ret['init'] = $ret['time'] = $odds;
        }
        return $ret;
    }

    /**
     * 将odds表内的数据格式转换成统一格式
     * @param $odds
     * @return array
     */
    public function parseOddsTableToOddsData($odds){
        $result = [];
        foreach($odds as $key => $val){
            $target = $key;
            $result[] = $val['odds'];
            if(isset($val['handicap']) && !isset($result['handicap'])){
                $result['handicap'] = $val['handicap'];
            }
            if(isset($val['under']) && !isset($result['under'])){
                $result['under'] = $val['under'];
            }
            if(isset($val['over']) && !isset($result['over'])){
                $result['over'] = $val['over'];
            }
        }
        return $result;

    }

    /**
     * 转换odds数据,水平化数据格式
     * @param $odds
     * @param $rules_id
     */
    public function parseOddsWords($odds,$rules_id,$teams,$rules_type = null){
        $result = [];
        if(!isset($this->rulesList[$rules_id])){return  [];}
        $rule = $this->rulesList[$rules_id];
        $rules_type = $rule['type'];
        if(in_array($rules_type,[RULES_TYPE_ASIAN,RULES_TYPE_EUROPE])){
            $handicap = '';
            foreach($odds as $k => $o){
                if($k != 'handicap'){
                    $result[$k] = [
                        'target' => $k,
                        'item' => '',
                        'handicap' => '',
                        'handicap_value' => '',
                        'win_money' => 0, //当前用户投注预计收益
                        'money' => 0, //当前用户投注总金额
                        'money_total' => 0, //全部投注总金额
                        'odds' => $o,
                        'target_name' => $k == 'same' ? 0 : 1,
                        'name' => '',
                    ];

                    if($teams){
                        $result[$k]['name'] = $k == 'home' ? ($teams && isset($teams[0]['name']) ? $teams[0]['name'] : '') : ($k == 'guest' ? ($teams && isset($teams[1]['name']) ? $teams[1]['name'] : '') : getRule($this->gameType, $rules_id, $k));
                    }else{
                        $result[$k]['name'] = $this->getBetTargetToRulesName($rules_id,$k,'');
                    }

                }else{
                    $handicap = $o;
                }
            }
            if($rules_type == RULES_TYPE_ASIAN){
                list($key,$val) = each($result);
                $result[$key]['handicap'] = $this->handicap($handicap);//"让球 {$handicap}";
                $result[$key]['handicap_value'] = $handicap;
            }
        }elseif($rules_type == RULES_TYPE_OU){
            $under = '';
            $over = '';
            foreach($odds as $k => $o){
                if($k && in_array($k,['under','over'])){
                    $under = is_array($o) ? $o['odds'] : $o;
                }else{
                    if(!$under && isset($o['under'])){
                        $under = $o['under'];
                    }
                    $result[$k] = [
                        'target' => $k,
                        'item' => '',
                        'handicap' => '',
                        'win_money' => 0, //当前用户投注预计收益
                        'money' => 0,
                        'money_total' => 0,
                        'odds' => is_array($o) ? $o['odds'] : $o,
                        'target_name' => '',
                        'name' => $k =='home' ? '大' : '小',
                    ];
                }
            }
            foreach($result as $key => $val){
                $result[$key]['under'] = $under ? $under : 0;
                $result[$key]['over'] = $under ? $under : 0;
            }
        }elseif(in_array($rules_type,[RULES_TYPE_BODAN,RULES_TYPE_BODAN_COMB])){
            $temp = [];
            foreach($odds as $k => $o){
                $_odds = $this->_parseScoreOdds($o,$rules_type);

                if($_odds){
                    foreach($_odds as $val){

                        $name = '';
                        if($k == 'home'){
                            $name = $teams && isset($teams[0]['name']) ? $teams[0]['name'] : '主';
                        }elseif($k == 'guest'){
                            $name = $teams && isset($teams[1]['name']) ? $teams[1]['name'] : '客';
                        }elseif($k == 'same'){
                            $name =  '平';
                        }
                        if(!$name){
                            $name = getRule($this->gameType,$rules_type,$k);
                        }
                        $result[$k.$val['item']] = [
                            'target' => $k,
                            'item' => $val['item'],
                            'handicap' => '',
                            'win_money' => 0, //当前用户投注预计收益
                            'money' => 0,
                            'money_total' => 0,
                            'name' => $name,
                            'odds' => $val['odds'],
                            'target_name' => $teams ? ($k == 'other' ? '其他' : $val['name']) : $this->getBetTargetToRulesName($rules_type,$val['item'],''),
                        ];
                    }
                }
            }
        }else{
            foreach($odds as $k => $o){
                $name = $k == 'home' ? ($teams && isset($teams[0]['name']) ? $teams[0]['name'] : '') : ($k == 'guest' ? ($teams && isset($teams[1]['name']) ? $teams[1]['name'] : '') : getRule($this->gameType, $rules_id, $k));
                $result[$k] = [
                    'target' => $k,
                    'item' => '',
                    'handicap' => '',
                    'win_money' => 0, //当前用户投注预计收益
                    'money' => 0,
                    'money_total' => 0,
                    'odds' => $o,
                    //'target_name_z' => $this->getBetTargetToRulesName($rules_id,$k,''),
                    'target_name' => $this->getBetTargetToRulesName($rules_type,$k,''),
                    'name' => '',
                ];
            }
        }
        return $result;
    }

    private function _parseScoreOdds($odds,$rules_type){
        $rules = config("rules.".$this->gameType);
        if(!$rules || !isset($rules['list'][$rules_type])){return $odds;}
        $rules = $rules['list'][$rules_type];
        if(!is_array($odds)){return $odds;}
        $ret = [];
        foreach($odds as $key => $val){
            $ret[] = [
                'odds' => $val,
                'name' => $rules[$key][0],
                'item' => $key
            ];
        }
        return $ret;

    }

    /**
     * 转换投注项,根据队伍返回投注名称，将投注标识转换成中文说明
     * @param $rule_id
     * @param $playId
     * @param $teams
     * @param $target
     * @param $item
     * @return array
     */
    public function getBetTargetText($rule_type,$playId,$teams,$target,$item,$ruleID = null){
        $ret = [];
        $ret['rules_name'] = $this->getRuleTypeText($rule_type);
        /*if($target == 'home'){
            $ret['target'] = '主队';//$teams[0]['name'];
        }elseif($target == 'guest'){
            $ret['target'] = '客队';//$teams[1]['name'];
        }elseif($target == 'same'){
            $ret['target'] = '平';
        }elseif($target == "other"){
            $str = "其它";
        }else{
            $ret['target'] = $this->getBetTargetToRulesName($rule_type,$target,$item);//getRule($this->gameType,$rule_id,$target);
        }*/
        //$target = $this->getBetTargetToRulesName($rule_type,$target,$item);
        $ret['target'] = $this->getBetTargetToRulesName($rule_type,$target,$item);
       /* if($item){
            preg_match('/(.*)\(([^\)]*)\)/',$target,$matchs);
            $ret['target'] = $matchs[1];
            $ret['item'] = $matchs[2];
            //$ret['item'] = $this->getBetTargetToRulesName($rule_type,$target,$item);
        }else{
            $ret['item'] = '';
        }*/
        $ret['item'] = '';
        return $ret;
    }

    /**
     * 将投注项转换成玩法中文名称
     * @param $ruleId
     * @param $target
     * @param null $item
     * @return mixed|string
     */
    public function getBetTargetToRulesName($rule_type,$target,$item = null,$teams = []){
        $target = strtolower($target);
        $item = strtolower($item);
        $str = '';
        if($item){
            if($target == 'home'){
                $str = "主队";
            }elseif($target == "guest"){
                $str = "客队";
            }elseif($target == "same"){
                $str = "平";
            }elseif($target == "other"){
                $str = "其它";
            }


            $rules = config("rules.".$this->gameType);
            if(!isset($rules['list'])){return $str;}
            $rules = $rules['list'];
            if(!isset($rules[$rule_type])){return $str;}
            $rules = $rules[$rule_type][$item];

            if(is_array($rules)){
                $rules = $rules[0];
            }
            $str .= "({$rules})";
        }else{
            $rules = config("rules.".$this->gameType);
            if(!isset($rules['list'])){return $str;}
            $rules = $rules['list'];
            if(!isset($rules[$rule_type])){return $str;}
            $rules = $rules[$rule_type][$target];

            if(is_array($rules)){
                $rules = $rules[0];
            }
            $str = $rules;
        }
        return $str;
    }

    public function handicap($key,$prefix = true,$positive = true){ //受让 客让主   负数 主让客
        $str = '';
        if($prefix){
            $str = $key >= 0 ? "得分 +" : "得分 -";
        }else{
            $str = $key >= 0 ? "+" : "-";
        }
        $f = $key > 0 ? false : true;
        $key = abs($key);
        $conf = config("handicap");
        $txt = $conf["{$key}"][1];

        if(stripos($txt,"/") !== false){
            $tmp = explode("/",$txt);
            if($f){
                $txt = $tmp[0]."/-{$tmp[1]}";
            }else{
                $txt = $tmp[0]."/+{$tmp[1]}";
            }
        }
        $str = "{$str}{$txt}";
        if(!$positive){
            $str = str_replace("+",'',$str);
        }
        return $str;
    }

    /**
     * 获取玩法选项列表
     */
    public function getRuleOption($ruleType = null){
        $rules = config("rules.".$this->gameType);
        if($ruleType  && isset($rules['list'][$ruleType])){
            return $rules['list'][$ruleType];
        }
        return [];
    }
}