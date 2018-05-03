<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;

class Play extends BasicModel{
    protected $name = 'play';
    /**
     * 获取今天的比赛
     */
    public function getTodayPlay($where = [],$limit = 15,$offset = 0,$order = 'p.play_time ASC'){
        $this->name("play")->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name,m.bgcolor");
        $this->where($where);
        $this->order($order);
        $this->limit($offset,$limit);
        return $this->select();
    }

    /**
     * 获取进行中的比赛
     */
    public function getPlayBegin($where = [],$limit = 15,$query = [],$order = 'p.play_time desc'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'play_time' => ['elt',time()],
            'status' => ['in',[PLAT_STATUS_START,PLAT_STATUS_INTERMISSION]]
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    /**
     * 获取已结束的比赛
     */
    public function getPlayEnd($where = [],$limit = 15,$query = [],$order = 'p.play_time desc'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'play_time' => ['elt',time()],
            'status' => ['in',[PLAT_STATUS_END,PLAT_STATUS_STATEMENT]]
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }
    /**
     * 获取未开始的比赛
     */
    public function getPlayNotBegin($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        //$this->join(["__TEAM_RANK__" => 'rh'],"p.match_id = rh.match_id AND p.team_home_id = rh.team_id","LEFT");
        //$this->join(['__TEAM_RANK__' => 'rg'],"p.match_id = rg.match_id AND p.team_guest_id = rg.team_id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'play_time' => ['egt',time()],
            'status' => PLAT_STATUS_NOT_START
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    //所有未开始的比赛
    public function getAllPlayNotBegin($where = [],$order = 'p.play_time ASC',$group = FALSE){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("m.*,p.play_time as play_time");
        $this->where([
            //'play_time' => ['egt',time()],
            'status' => PLAT_STATUS_NOT_START
        ]);
        $this->where($where);
        $this->order($order);
        if($group){
            $this->group($group);
        }
        $result = $this->select();
        return $result;
    }

    public function getPlayNotBeginWithOdds($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        //$this->join("__TEAM_RANK__ rh","p.match_id = rh.match_id AND p.team_home_id = rh.team_id","LEFT");
        //$this->join("__TEAM_RANK__ rg","p.match_id = rg.match_id AND p.team_guest_id = rg.team_id","LEFT");
        $this->join("__ODDS__ o","o.play_id = p.id","LEFT");
        $this->field("distinct p.id,p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'play_time' => ['egt',time()],
            'status' => PLAT_STATUS_NOT_START
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    /**
     * 获取其它状态比赛
     */
    public function getPlayOther($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'play_time' => ['egt',time()],
            'status' => ["not in",[PLAT_STATUS_START,PLAT_STATUS_INTERMISSION,PLAT_STATUS_END,PLAT_STATUS_NOT_START,PLAT_STATUS_STATEMENT]]
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    /**
     * 获取其它状态比赛
     */
    public function getPlayRecommend($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            //'p.is_recommend' => 1,
            'hot' => 3
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    public function match(){
        return $this->belongsTo("match");
    }

    public function getPlayList($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->join("__ODDS__ o","o.play_id = p.id","LEFT");
        $field = "distinct p.id,p.game_type,p.match_id ,p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,p.team_home_score,p.team_guest_score,p.team_home_half_score,p.team_guest_half_score,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red,p.min_deposit,p.`status`,p.has_statement,p.first_goals,p.arena_total,m.name as match_name,m.bgcolor as match_bgcolor";
        $this->field($field);
        $this->order($order);
        $result = $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

    public function getPlayWithMatchList($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->name("play")->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->order($order);
        $result = $this->where($where)->paginate($limit,false,[
            'query' => $query,
        ]);
        return $result;
    }

    public function getPlayWithMatchSearchList($where=[],$limit = 15,$query = [],$order = 'p.play_time DESC',$join=''){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        if($join == 'game'){
            $this->join("__GAME__ g",'g.id=p.game_id');
        }
        $this->order($order);
        $result = $this->where($where)->paginate($limit,false,[
            'query' => $query,
        ]);
        return $result;
    }

    public function getPlayInfo($play_id){
        $play_id = intval($play_id);
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        return $this->where("p.id",$play_id)->find();
    }

    //历史数据
    public function getPlayHistoryList($where ,$order="ph.play_time desc", $limit=10,$query=[]){
        $this->table(getTrueTableName("play_history"))->alias("ph");
        $this->join("__MATCH__ m","ph.match_id=m.id","LEFT");
        $this->field("ph.*,m.name as match_name");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);

    }

    //有擂台的比赛
    public function getPlayWithArenaList($where = [],$limit = 15,$query = [],$order = 'p.play_time ASC'){
        $this->alias("p");
        $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("p.*,m.name as match_name,m.bgcolor as match_bgcolor");
        $this->where([
            'arena_total' => [">=",1]
        ]);
        $this->where($where);
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }

}