<?php

namespace app\admin\model;
use app\library\model\BasicModel;
use think\Db;

class Agent extends BasicModel{
    protected $name = "agent_user";

    public function getUser($where,$limit=10,$order="",$query=[]){
        $this->alias('au');
        $this->field("au.*");
        $this->where($where);
        $this->join("__USER__ u","au.user_id = u.id",'LEFT');
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }

    public function getAgentArenaList($where,$limit=10,$order="",$query=[]){
        $this->table(getTrueTableName('agent_arena'))->alias("aa");
        $this->join("__ARENA__ a","a.id = aa.arena_id","LEFT");
        $this->join("__PLAY__ p","p.id = a.play_id","LEFT");
        $this->field("a.*,p.play_time,p.status as play_status,p.team_home_name,p.team_guest_name,aa.id as agent_id,aa.bet_money as agent_bet_money,aa.bet_number as agent_bet_number");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    public function upCache(){
        $limit = 100;
        $page = 0;
        while(true){
            $offset = $page * $limit;
            $res = Db::name("agent_user")->limit($offset,$limit)->field("*")->select();
            if($res){
                foreach($res as $val){
                    unset($val['password']);
                    unset($val['salt']);
                    Cache("agent_{$val['id']}",$val);
                    cache("agent_{$val['mark']}",$val['id']);
                }
            }else{
                break;
                return true;
            }
            $page++;
        }
        return true;
    }
}