<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;

class Arena extends BasicModel{
    protected $name = 'arena';

    
    /**
     * 根据擂台ID，获取投注用户列表
     * @param $arena_id
     */
    public function getBetListByArenaId($arena_id,$where = [],$limit = 10,$query = []){
        $this->name('arena_bet_detail')->alias("a");
        $this->where(['a.arena_id' => $arena_id]);
        $this->where($where);
        $this->join("__USER__ u","a.user_id = u.id","LEFT");
        $this->field("a.*,u.nickname");
        $this->order("a.create_time DESC");
        if(is_null($limit)){
            return $this->select(10000);
        }else{
            return $this->paginate($limit,false,[
                'query' => $query
            ]);
        }
    }


    public function parseArenaOdds($datas){
        foreach($datas as $key => $data){
            $datas[$key]['odds'] = @json_decode($data['odds'],true);
            $datas[$key]['bet_total'] = @json_decode($data['bet_total'],true);
        }
        return $datas;
    }

    //查询擂台列表
    public function findArenaList($where,$order="a.id desc",$limit = 10,$query = []){
        $this->alias("a");
        //$this->join("__MATCH__ as m","a.match_id = m.id","LEFT");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
        $this->field("a.*,
        p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,p.game_id,
        p.team_home_score,p.team_guest_score,p.team_home_half_score,p.team_guest_half_score,p.status as play_status,p.first_goals,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red
        ");
        $this->order($order);
        $result = $this->parseArenaOdds($this->where($where)->paginate($limit,false,[
            'query' => $query
        ]));
        return $result;
    }

    //查询代理擂台列表
    public function findAgentArenaList($where,$order="a.id desc",$limit = 10,$query = []){
        $this->name('agent_arena')->alias("aa");
        $this->join("__ARENA__ a","a.id = aa.arena_id","LEFT");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
        $this->field("a.*,
        p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,p.game_id,
        p.team_home_score,p.team_guest_score,p.team_home_half_score,p.team_guest_half_score,p.status as play_status,p.first_goals,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red
        ");
        $this->order($order);
        $result =  $this->parseArenaOdds($this->where($where)->paginate($limit,false,[
            'query' => $query
        ]));
        return $result;
    }

    public function findArenaInfo($arena_id){
        $this->alias("a");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
        $this->field("a.*,
        p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,
        p.team_home_score,p.team_guest_score,p.team_home_half_score,p.team_guest_half_score,p.status as play_status,p.first_goals,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red
        ");
        return $this->where("a.id",$arena_id)->find();
    }



    public function findAll($whereData,$order="a.id desc",$limit = 10,$query = []){
        $this->alias("a");
        $this->join("__MATCH__ m","a.match_id = m.id","LEFT");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
        $this->field("a.*,
        m.name as match_name,m.country_id,m.game_type as match_type,m.bgcolor as match_bgcolor,
        p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,p.status as play_status,p.game_id,
        p.team_home_score,p.team_guest_score,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red
        ");
        $this->order($order);
        $where = [];
        $whereOR = [];
        if(isset($whereData['game_type']) && $whereData['game_type']){
            $where['a.game_type'] = $whereData['game_type'];
        }
        if(isset($whereData['game_id']) && $whereData['game_id']){
            $where['p.game_id'] = $whereData['game_id'];
        }
        if(isset($whereData['rules_type']) && $whereData['rules_type']){
            $where['a.rules_type'] = $whereData['rules_type'];
        }
        if(isset($whereData['rules_id']) && $whereData['rules_id']){
            $where['a.rules_id'] = $whereData['rules_id'];
        }
        if(isset($whereData['match']) && $whereData['match']){
            $where['m.name'] = ["like","%{$whereData['match']}%"];
        }
        if(isset($whereData['nickname']) && $whereData['nickname']){
            $where['a.user_nickname'] = ["like","%{$whereData['nickname']}%"];
        }
        if(isset($whereData['team_name']) && $whereData['team_name']){
            $where['p.team_home_name|p.team_guest_name'] = ["like","%{$whereData['team_name']}%"];
        }
        if(isset($whereData["user_id"]) && $whereData["user_id"]){
            $where['a.user_id'] = ["=",$whereData['user_id']];
        }

        if(isset($whereData['play_time']) && $whereData['play_time']){
            $where['p.play_time'] = strtotime($whereData['play_time']);
        }
        if(isset($whereData['play_id']) && $whereData['play_id']){
            $where['p.id'] = $whereData['play_id'];
        }
        if(isset($whereData['private']) && $whereData['private']){
            $where['a.private'] = $whereData['private'];
        }
        $whereOr = "";
        if(isset($whereData['status']) && $whereData['status']){
            if($whereData['status'] == ARENA_PLAY){
                //$whereOr['a.status'] = ARENA_PLAY;
                //$whereOr['p.status'] = ['<>',PLAT_STATUS_NOT_START];
                //$whereOr['p.play_time'] = ['<',time()];
                //$whereOr = "(a.status=".ARENA_PLAY." OR( a.status =".ARENA_START." AND (p.status !=".PLAT_STATUS_NOT_START.' OR p.play_time <= '.time().")))";
                $whereOr = "(a.status=".ARENA_PLAY." OR( a.status =".ARENA_START." AND (p.status !=".PLAT_STATUS_NOT_START.")))";
                //$where['a.status|p.status|p.play_time'] = [['=',ARENA_PLAY],['<>',PLAT_STATUS_NOT_START],['<',time()],'or'];
            }elseif($whereData['status'] == ARENA_START){
                $where['a.status'] = ARENA_START;
                $where['a.status'] = ARENA_START;
                //$where['p.play_time'] = ['>',time()];
                //$whereOr = " p.status =".PLAT_STATUS_NOT_START.' OR p.play_time > '.time();
            }else{
                $where['a.status'] = $whereData['status'];
            }

        }
        if(isset($whereData['mark']) && $whereData['mark']){
            if(is_numeric($whereData['mark'])){
                $where['a.id'] = intval($whereData['mark']);
            }else{
                $where['a.mark'] = $whereData['mark'];
            }
        }

        if(isset($whereData['risk']) && $whereData['risk']){
            $risk = $this->getRiskWhere($whereData['risk']);
            if($risk){
                if(count($risk) > 1){
                    $where["a.risk"] = [['GT',$risk[0]],['ELT',$risk[1]]];
                }else{
                    $where["a.risk"] = ['GT',$risk[0]];
                }
            }
        }
        $this->queryWhereOr = $whereOr;
        $this->where($where);
        if($whereOr){
            $this->where(function($query){
                $query->where($this->queryWhereOr);
            });
        }
        $result = $this->parseArenaOdds($this->paginate($limit,false,[
            'query' => $query
        ]));
        //echo $this->getlastSql();
        return $result;

    }

    public function findAgentArenaAll($whereData,$order="a.id desc",$limit = 10,$query = []){
        $this->name("agent_arena")->alias("aa")
            ->join("__ARENA__ a","a.id = aa.arena_id","LEFT")
            ->join("__MATCH__ m","a.match_id = m.id","LEFT")
            ->join("__PLAY__ p","a.play_id = p.id","LEFT")
            ->field("a.*,
        m.name as match_name,m.country_id,m.game_type as match_type,m.bgcolor as match_bgcolor,
        p.play_time,p.team_home_id,p.team_home_name,p.team_guest_id,p.team_guest_name,p.status as play_status,
        p.team_home_score,p.team_guest_score,p.home_yellow,p.home_red,p.guest_yellow,p.guest_red
        ")
            ->order($order);
        //$this->join("__ITEM__ as ih","p.team_home_id = p.ih","LEFT");
        //$this->join("__ITEM__ as ig","p.team_home_id = p.ig","LEFT");
        $where = [];
        $whereOR = [];
        if(isset($whereData['agent_user_id']) && $whereData['agent_user_id']){
            $where['aa.agent_user_id'] = $whereData['agent_user_id'];
        }
        if(isset($whereData['rules_id']) && $whereData['rules_id']){
            $where['a.rules_id'] = $whereData['rules_id'];
        }
        if(isset($whereData['match']) && $whereData['match']){
            $where['m.name'] = ["like","%{$whereData['match']}%"];
        }
        if(isset($whereData['nickname']) && $whereData['nickname']){
            $where['a.user_nickname'] = ["like","%{$whereData['nickname']}%"];
        }
        if(isset($whereData['item']) && $whereData['item']){
            $where['p.team_home_name|p.team_guest_name'] = ["like","%{$whereData['item']}%"];
        }
        if(isset($whereData["user_id"]) && $whereData["user_id"]){
            $where['a.user_id'] = ["=",$whereData['user_id']];
        }

        if(isset($whereData['play_time']) && $whereData['play_time']){
            $where['p.play_time'] = strtotime($whereData['play_time']);
        }
        if(isset($whereData['play_id']) && $whereData['play_id']){
            $where['p.id'] = $whereData['play_id'];
        }
        $whereOr = "";
        if(isset($whereData['status']) && $whereData['status']){
            if($whereData['status'] == ARENA_PLAY){
                //$whereOr['a.status'] = ARENA_PLAY;
                //$whereOr['p.status'] = ['<>',PLAT_STATUS_NOT_START];
                //$whereOr['p.play_time'] = ['<',time()];
                $whereOr = "(a.status=".ARENA_PLAY." OR( a.status =".ARENA_START." AND (p.status !=".PLAT_STATUS_NOT_START.' OR p.play_time <= '.time().")))";
                //$where['a.status|p.status|p.play_time'] = [['=',ARENA_PLAY],['<>',PLAT_STATUS_NOT_START],['<',time()],'or'];
            }elseif($whereData['status'] == ARENA_START){
                $where['a.status'] = ARENA_START;
                $where['a.status'] = ARENA_START;
                $where['p.play_time'] = ['>',time()];
                //$whereOr = " p.status =".PLAT_STATUS_NOT_START.' OR p.play_time > '.time();
            }else{
                $where['a.status'] = $whereData['status'];
            }

        }
        if(isset($whereData['mark']) && $whereData['mark']){
            if(is_numeric($whereData['mark'])){
                $where['a.id'] = intval($whereData['mark']);
            }else{
                $where['a.mark'] = $whereData['mark'];
            }
        }

        if(isset($whereData['risk']) && $whereData['risk']){
            $risk = $this->getRiskWhere($whereData['risk']);
            if($risk){
                if(count($risk) > 1){
                    $where["a.risk"] = [['GT',$risk[0]],['ELT',$risk[1]]];
                }else{
                    $where["a.risk"] = ['GT',$risk[0]];
                }
            }
        }
        $this->queryWhereOr = $whereOr;
        $this->where($where);
        if($whereOr){
            $this->where(function($query){
                $query->where($this->queryWhereOr);
            });
        }
        $result = $this->parseArenaOdds($this->paginate($limit,false,[
            'query' => $query
        ]));
        //echo $this->getLastSql();
        return $result;

    }

    /**
     * 获取擂台投注列表
     * @param $arena_id
     * @param int $limit
     * @param array $query
     * @param string $order
     */
    public function finBetDetail($arena_id,$limit = 10,$query = [],$order="abd.id desc"){
        return $this->name("arena_bet_detail")->alias("abd")
            ->join("__ARENA__ a","a.id = abd.arena_id","LEFT")
            ->order($order)
            ->where(['arena_id' => $arena_id])
            ->field('abd.*,a.rules_id,a.rules_type')
            ->paginate($limit,false,['query' => $query]);
    }

    public function finBetDetailByWhere($where,$limit = 10,$query = [],$order="abd.id desc"){
        return $this->name("arena_bet_detail")->alias("abd")
            ->join("__ARENA__ a","a.id = abd.arena_id","LEFT")
            ->join("__PLAY__ p","a.play_id = p.id","LEFT")
            ->order($order)
            ->where($where)
            ->field('abd.*,a.rules_type,a.rules_id,a.game_type,p.team_home_name,p.team_guest_name,p.match_id')
            ->paginate($limit,false,['query' => $query]);
    }

    //代理投注查询
    public function finAgentBetDetailByWhere($where,$limit = 10,$query = [],$order="abd.id desc",$field="abd.*,a.rules_type as rules_type,a.game_type,p.team_home_name,p.team_guest_name,p.match_id,p.id as play_id,a.odds_id as odds_id"){
        return $this->name("arena_bet_detail")->alias("abd")
            ->join("__ARENA__ a","a.id = abd.arena_id","LEFT")
            ->join("__PLAY__ p","a.play_id = p.id","LEFT")
            ->order($order)
            ->where($where)
            ->field($field)
            ->paginate($limit,false,['query' => $query]);
    }

    public function finBetDetailByMark($mark,$limit = 10,$query = [],$order="abd.id desc"){
        return $this->name("arena_bet_detail")->alias("abd")
            ->join("__ARENA__ a","a.id = abd.arena_id","LEFT")
            ->order($order)
            ->where(['a.mark' => $mark])
            ->field('abd.*,a.rules_type , a.rules_id')
            ->paginate($limit,false,['query' => $query]);
    }

    /**
     * 随机获取擂台数据
     */
    public function randArena($where = '',$limit = 1){
        if($where && is_array($where)){
            $where = implode(" AND ",$where);
        }
        $limit = intval($limit) ? intval($limit) : 1;
        $table = getTrueTableName($this->name);
        $sql = "SELECT r1.* FROM {$table} as r1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM {$table})) AS id) as r2 ";
        $sql .= " WHERE ".($where ? "{$where} AND " : "")." r1.status=".ARENA_START." ORDER BY r1.id ASC LIMIT {$limit}";
        return $this->query($sql);

    }

    //获取单场比赛下的擂台列表
    public function getArenaListWithPlayID($where=[],$limit=15,$query=[], $order="id desc" , $group="" , $field="*",$countField = '*'){
        $this->alias("a");
        if($group){
            $this->group($group);
        }
        return $this->field($field)->where($where)->order($order)->paginate($limit,false,['query' => $query],$countField);

    }

    public function getArenaInfoByID($id){
        $this->alias("a");
        $this->join("__PLAY__ p","a.play_id=p.id","LEFT");
        $this->field("a.*,p.team_home_name,p.team_guest_name");
        return $this->where("a.id",$id)->find();
    }

    //查询当前擂台下大神投注信息
    public function getArenaWithManito($where=[]){
        $this->name("top_leitai_win")->alias("tlw");
        $this->join("__ARENA_BET_DETAIL__ abd","abd.user_id = tlw.user_id","LEFT");
        $this->join("__USER__ u","u.id=tlw.user_id","LEFT");
        $this->field("tlw.*,abd.arena_id as arena_id,abd.id as bet_id,abd.buy as buy,u.avatar as avatar");
        $this->where($where);
        $this->group("tlw.user_id");
        return $this->select();
    }

    public function getArenaInfoByWhere($where){
        $this->name("arena")->alias("a");
        $this->field("a.*");
        return $this->where($where)->find();
    }

}