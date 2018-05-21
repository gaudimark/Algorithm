<?php
namespace library\service;

use think\Db;

class Match{

    //有擂台的赛事加入缓存
    public function cacheMatchToRedis(){
        //全部重新生成缓存
        $where = [];
        $where["p.arena_total"] = [">",0];
        $where["p.status"] = PLAT_STATUS_NOT_START;
        //全部
        //$play = model('library/play')->getAllPlayNotBegin($where,"p.play_time ASC","p.match_id");
        $play = Db::name('play')->alias("p")
            ->join("__MATCH__ m","p.match_id = m.id","LEFT")
            ->field("m.*,p.play_time as play_time,p.game_type as game_type")
            ->where($where)->group("p.match_id")->select();
        
        
        Cache("recommend_match_all","");
        $allData = array();
        $dayData = array();
        foreach ($play as $k=>$v){
            $allData[$v["game_type"]][] = $v;
            $play_date = date("Ymd",$v["play_time"]);
            //$val = model("library/match")->getMatchInfoByID($v["match_id"]);
            $val = Db::name('match')->where('id' , $v["id"])->find();
            $dayData[$play_date][$v["game_type"]][] = $val; 
        }
        Cache("recommend_match_all",$allData);
        if($dayData){
            foreach ($dayData as $day=>$data){
                Cache("recommend_match_{$day}",$data);
            }
        }
        $this->cacheMatchByPlay();
    }

    /**
     * 获取有比赛的赛事
     * @param int $item_id 比赛项目ID
     * @param int $game_id 比赛项目游戏ID
     * @return array
     */
    public function getMatchByPlay($item_id = null,$game_id = null){
        $data = null;
        $data = [];//Cache("match_play_all");
        if(!$data){
            $data = $this->cacheMatchByPlay();
        }
        if($data && $item_id){
            $data = isset($data[$item_id]) ? $data[$item_id] : [];
        }
        if($data && !is_null($game_id)){
            $data = isset($data[$game_id]) ? $data[$game_id] : [];
        }

        return $data;
    }

    public function cacheMatchByPlay(){
        $where = [];
        $where["p.play_time"] = ["gt",time()];
        $where["p.id"] = ["gt",0];
        $matchList = Db::name('match')->alias("m")
            ->field("m.id,m.name,m.game_type as item_id,m.game_id")
            ->join("__PLAY__ p","p.match_id = m.id",'LEFT')
            ->where($where)
            ->group("m.id")
            ->order("m.is_hot DESC,p.play_time asc")
            ->select();
        $data = [];
        foreach($matchList as $val){
            $data[$val['item_id']][$val['game_id']][] = $val;
        }
        Cache("match_play_all",$data,3600);
        return $data;
    }

}

?>