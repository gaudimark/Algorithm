<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Exception;
use think\Model;

class Bet extends BasicModel{
    public $name = 'arena_bet_detail';
    public function getBetList($where = [],$order="abd.create_time DESC",$limit = 10,$query = []){
        $this->name('arena_bet_detail')->alias("abd");
        $this->where($where);
        $this->join("__ARENA__ a","a.id = abd.arena_id","LEFT");
        $this->join("__USER__ u","abd.user_id = u.id","LEFT");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
       // $this->join("__MATCH__ m","p.match_id = m.id","LEFT");
        $this->field("abd.*,u.nickname,a.status as arena_status,a.odds as arena_odds,a.mark as arena_mark,a.rules_type,a.rules_id,a.game_type,p.status as play_status,a.play_id,p.play_time as play_time,a.match_id,a.classify,a.has_sys,a.has_robot,u.has_robot as user_has_robot");
        $this->order($order);
        $result = $this->paginate($limit,false,[
            'query' => $query
        ]);
        return $result;
    }
    public function getBetCount($where = [],$filed = "*"){
        $this->cache(false)->name('arena_bet_detail')->alias("abd");
        $this->where($where);
        $this->join("__ARENA__ a","a.id = abd.arena_id","LEFT");
        $this->join("__USER__ u","abd.user_id = u.id","LEFT");
        $this->join("__PLAY__ p","a.play_id = p.id","LEFT");
        $this->field($filed);
        $result = $this->find();
        if($result){
            $result = $result->toArray();
        }
        return $result;
    }

    public function cancel($id,$txt){
        $this->db()->startTrans();
        try{
            $detail = $this->where(['id' => $id])->find();
            if(!$detail){
                throw new Exception('未找到投注信息');
            }
            if($detail['status'] == DEPOSIT_CANCEL){
                throw new Exception('该投注已取消');
            }
            $arena = $this->name("arena_bet_detail")->where(['id' => $detail['arena_id']])->find();
            if(!$arena){
                throw new Exception('未找到擂台信息');
            }
            if($arena['status'] == ARENA_END){
                throw new Exception('所属擂台已结束，无法取消');
            }
            if($arena['status'] == ARENA_DEL){
                throw new Exception('所属擂台已封禁，无法取消');
            }
            if($arena['status'] == ARENA_DEL){
                throw new Exception('所属擂台已删除，无法取消');
            }
            $this->where(['id' => $id])->update(['status' => DEPOSIT_CANCEL]);
            $betList = json_decode($arena['bet_total'],true);
            $target = $detail['target'];
            $item = $detail['item'];

        }catch (\Exception $e){
            $this->db()->rollback();
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }
    
    
}