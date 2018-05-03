<?php

namespace library\service;
use think\Db;
use think\Exception;

class Play{

    private $error = '';
    private $expire = 365 * 86400;
    /**
     * 获取比赛下的队伍列表
     * @param $playId
     * @return array
     */
    public function getTeams($playId,$field = [],$userId = 0){

        $teams = '';//cache("play_teams_{$playId}");
        if(!$teams){
            $teams = $this->cacheTeams($playId);
        }
        $userSvr = (new User());
        if($field){ //保留可用字段
            foreach ($teams as $key => $val) {
                $data = [];
                $teamId = $val['id'];
                foreach($field as $f){
                    if(array_key_exists($f,$val)){
                        $data[$f] = is_null($val[$f]) ? '' : $val[$f];
                    }
                }
                $data['has_follow'] = $userId ? intval($userSvr->checkFollow($userId,$teamId,FOLLOW_TYPE_TEAM)) : 0;
                $teams[$key] = $data;
            }
        }else{
            foreach ($teams as $key => $val) {
                $val['has_follow'] = $userId ? intval($userSvr->checkFollow($userId,$val['id'],FOLLOW_TYPE_TEAM)) : 0;
                $teams[$key] = $val;
            }
        }


        return $teams;
    }

    /**
     * 根据ID获取比赛详情
     * @param $play_id
     * @param array $field
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    public function getPlay($play_id,$field = []){
        $result = '';//cache("play_{$play_id}");
        if(!$result){
            $result = $this->upCache($play_id);
        }
        if(!$result){return [];}
        if(!isset($result['live']) || !$result['live'] ){
            $result['live'] = '';
        }
        
        if(!isset($result['live_type']) || !$result['live_type'] ){
            $result['live_type'] = 0;
        }
        $result['arena_total'] = $result['sys_arena_total']; //将擂台数换成系统擂台数
        unset($result['sys_arena_total']);
        $result['game_id'] = intval($result['game_id']);
        if($field){ //保留可用字段
            $data = [];
            foreach($field as $f){
                if(array_key_exists($f,$result)){
                    $data[$f] = is_null($result[$f]) ? '' : $result[$f];
                }else{
                    $data[$f] = '';
                }
            }
            $result = $data;
        }
        return $result;
    }

    public function getPlayLive($playId,$live){
        return $live ? config("share_domain")."live/play/{$playId}.html" : '';
    }

    /**
     * 更新比赛缓存
     * @param $playId
     */
    public function upCache($playId){
        $result = Db::name("play")->where(['id' => $playId])->find();
        cache("play_{$playId}",null);
        cache("play_{$playId}", $result, $this->expire); //有效期10天
        return $result;
    }

    /**
     * 将擂台ID添加到比赛下
     * @param $playId
     * @param $arenaId
     */
    public function arenapublish($playId,$arenaId){
        \think\Cache::hSet("Play_Arena_list_{$playId}",$arenaId,1,$this->expire);
    }

    /**
     * 缓存比赛队伍信息,并返回队伍数据
     * @param $playId
     */
    public function cacheTeams($playId){
        
        $playTeam = Db::name('play_team')->where(['play_id' => $playId])->select();
        $home = [];
        $temp = [];
        foreach ($playTeam as $val) {
            unset($val['md5']);
            $_ = getTeam($val['team_id']);
            $_['id'] = $val['id'];
            $_['has_home'] = 0;
            $_['score'] = $val['score'];
            $_['half_score'] = $val['half_score'];
            $_['red'] = $val['red'];
            $_['yellow'] = $val['yellow'];
            $_['score_json'] = @json_decode($val['score_json'],true);
            if(!$_['score_json']){
                $_['score_json'] = [];
            }
            if ($val['has_home']){
                $_['has_home'] = 1;
                $home[] = $_;
            } else {
                $temp[] = $_;
            }
        }
        $teams = array_merge($home, $temp);
        cache("play_teams_{$playId}", $teams, $this->expire); //缓存分钟
        return $teams;
    }

    /**
     * @param $playId
     * @return string
     */
    public function upRulesDetailCacheByPlayId($playId){
        $playId = intval($playId);
        if(!$playId){return '';}
        $list = Db::name('play_rules_detail')->where(['play_id' => $playId])->field("game_id,rules_id,rules_explain,odds_id")->order("id asc")->select();
        $data = [];
        foreach($list as $key =>$val){
            $data[$val["rules_id"]] = array("game_id" => $val["game_id"], "rules_explain" => json_decode($val["rules_explain"], true));
        }
        cache("play_rules_detail_{$playId}",$data,30*86400);
        return $data;
    }
    
    /**
     * 获取所有玩法，包括用户添加的玩法
     * @param $playId
     * @return string
     */
    public function upAllRulesDetailCacheByPlayId($playId){
        $playId = intval($playId);
        if(!$playId){return '';}
        $data = [];
        $list = Db::name('arena')->alias('a')
            ->join('__RULES__ r','a.rules_id=r.id')
            ->join('__PLAY__ p','a.play_id=p.id')
            ->where(['a.play_id' => $playId])
            ->field("a.game_id,a.rules_id,r.explain as rules_explain,a.odds_id,p.team_home_name,p.team_guest_name")
            ->order("a.id asc")->group("a.rules_id")->select();
        foreach($list as $key =>$val){
            $rules_explain = json_decode($val["rules_explain"], true);
            $explain = [];
            if($rules_explain){
                foreach ($rules_explain as $re) {
                    if ($re == 'home'){
                        $explain[] = $val["team_home_name"];
                    } elseif ($re == 'guest') {
                        $explain[] = $val["team_guest_name"];
                    } else {
                        $explain[] = $re;
                    }
                }
            }
            $data[$val["rules_id"]] = array("game_id" => $val["game_id"], "rules_explain" => $explain);
        }
        cache("play_all_rules_detail_{$playId}",$data,30*86400);
        return $data;
    }

    /**
     * 获取比赛下的玩法内容,推荐擂台
     * @param $play_id
     * @param int $rules_id
     * @return array|false|mixed|\PDOStatement|string|\think\Collection|\think\Model
     */
    public function getPlayRules($play_id,$rules_id = 0){
        $data = '';//cache("play_rules_{$play_id}");
        if(!$data){
            $where = [];
            $where['play_id'] = $play_id;
            if($rules_id){
                $where['rules_id'] = $rules_id;
                return Db::name('play_rules')->where($where)->find();
            }
            $data = Db::name('play_rules')->order("sort ASC")->where($where)->column("*","id");
            //cache("play_rules_{$play_id}",$data,1800);
        }
        if($rules_id && isset($data[$rules_id])){
            $data = $data[$rules_id];
        }
        return $data;

    }

    /**
     * 比赛结果
     * @param $playId
     * @param $retType 返回数据类型，string,array
     */
    public function getResult($gameType,$playId,$retType = 'string'){
        return $this->factory($gameType)->getResult($playId,$retType);
    }

    public function factory($gameType){
        $handle = null;
        switch ($gameType){
            case GAME_TYPE_FOOTBALL:
                $handle = new \library\service\play\Football();
                break;
            case GAME_TYPE_WCG:
                $handle = new \library\service\play\Wcg();
                break;
            case GAME_TYPE_BASKETBALL:
                $handle = new \library\service\play\Basketball();
                break;
            case GAME_TYPE_PUCK:
                $handle = new \library\service\play\Puck();
                break;
            case GAME_TYPE_TENNIS:
                $handle = new \library\service\play\Tennis();
                break;
            case GAME_TYPE_AMERICAN_FOOTBALL:
                $handle = new \library\service\play\American_football();
                break;
        }
        return $handle;
    }

    /**
     * 获取当前比赛最低保证金
     * @param $item_id
     * @param $rulesType
     * @return int
     */
    public function getMinDeposit($item_id,$rulesId){
        $min = 0;
        $rulesList = (new Rule())->factory($item_id)->rulesListAll();
        if($rulesList && $rulesId && isset($rulesList[$rulesId])){
            $min = $rulesList[$rulesId]['min_deposit'];
        }
        if(!$min){
            $min = config("system.sys_min_deposit");
        }
        return $min;
    }

    /**
     * 添加\更新比赛
     * @param $matchId
     * @param $homeId
     * @param $guestId
     * @param $playTime
     */
    public function addPlay($matchId,$homeId,$guestId,$playTime){
        $match = getMatch($matchId);
        $home = getTeam($homeId);
        $guest = getTeam($guestId);
        if(!$match){
            $this->error = '选择的赛事不存在';
            return false;
        }
        if(!$home || !$guest){
            $this->error = '选择的比赛队伍不存在';
            return false;
        }
        if($guestId == $homeId){
            $this->error = '选择的比赛队伍相同';
            return false;
        }
        $playTime = strtotime($playTime);
        if($playTime < time()){
            $this->error = '选择的比赛开赛时间不能小于当前时间';
            return false;
        }

        Db::startTrans();
        $play_id = 0;
        try{
            $play_id = Db::name('play')->insertGetId([
                'md5_play' => '',
                'has_manual' => STATUS_YES,
                'game_id' => intval($match['game_id']),
                'game_type' => intval($match['game_type']),
                'match_id' => intval($match['id']),
                'status' => PLAT_STATUS_NOT_START,
                'play_time' => $playTime,
                'end_time' => 0,
                'team_home_id' => $homeId,
                'team_home_name' => $home['name'],
                'team_guest_id' => $guestId,
                'team_guest_name' => $guest['name'],
                'create_time' => time(),
                'update_time' => time(),
            ]);

            Db::name('play_team')->insert([
                'play_id' => $play_id,
                'team_id' => $homeId,
                'has_home' => 1,
            ]);

            Db::name('play_team')->insert([
                'play_id' => $play_id,
                'team_id' => $guestId,
                'has_home' => 0,
            ]);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        $this->upCache($play_id);
        return true;
    }


    /**
     * 删除比赛
     * @param $playId
     */
    public function delPlay($playId){
        $play = $this->getPlay($playId);
        if($play['has_manual'] != STATUS_YES){
            $this->error = '非人工添加的比赛不能删除';
            return false;
        }
        $arenaTotal = Db::name('arena')->where(['play_id' => $playId])->count();
        if($arenaTotal > 0){
            $this->error = '该比赛下已发布擂台无法删除';
            return false;
        }
        Db::startTrans();
        try{
            Db::name('play')->where(['id' => $playId])->delete();
            Db::name('play_team')->where(['play_id' => $playId])->delete();
            Db::name('play_rules')->where(['play_id' => $playId])->delete();
            Db::name('play_rules_detail')->where(['play_id' => $playId])->delete();
            Db::name('play_result')->where(['play_id' => $playId])->delete();
            Db::name('odds')->where(['play_id' => $playId])->delete();
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        cache("play_{$playId}",null);
        cache("play_teams_{$playId}",null);
        return true;
    }


    public function getError(){
        return $this->error;
    }

}