<?php

namespace library\service;
class Rule{
    /**
     * 亚盘玩法
     */
    public $asianRules = [
        GAME_TYPE_FOOTBALL => [RULES_TYPE_ASIAN,RULES_TYPE_OU,RULES_TYPE_SINGLE_DOUBLE],
    ];


    public function factory($gameType){
        $handle = null;
        switch ($gameType){
            case GAME_TYPE_FOOTBALL:
                $handle = new \library\service\rule\Football();
                break;
        }
        return $handle;
    }

    public function getRuleTypeText($gameType,$ruleType){
        return $this->factory($gameType)->getRuleTypeText($ruleType);
    }
    public function getRuleIcon($gameType,$ruleType){
        return $this->factory($gameType)->getRuleIcon($ruleType);
    }

    

    /**
     * 转换投注项
     * @param $gameType
     * @param $ruleType
     * @return string
     */
    public function getBetTargetText($gameType,$rules_type,$playId,$teams = [],$target,$item = '',$ruleID = null){
        return $this->factory($gameType)->getBetTargetText($rules_type,$playId,$teams,$target,$item,$ruleID);
    }

    /**
     * 大小预设总分转换（针对 *.25 *.75）
     * @param $key
     * @param $prefix
     * @param $positive
     */
    public function under($key,$prefix,$positive){
        if(strpos($key,'.') === false){
            return $key;
        }
        $isGtZero = false;
        if($key > 0){$isGtZero = true;}
        if($prefix){
            $str = $isGtZero ? "得分 +" : "得分 -";
        }else{
            $str = $isGtZero ? "+" : "-";
        }
        list($int,$dec) = explode(".",$key);
        $int = abs($int);
        $result = $key;
        if($dec == '25'){
            $result = $str.$int."/".($isGtZero ? '+' : '-').($int+0.5);
        }elseif($dec == '75'){
            $result = $str.($int+0.5)."/".($isGtZero ? '+' : '-').($int+1);
        }
        if(!$positive){
            $result = str_replace("+",'',$result);
        }
        return $result;
    }
}