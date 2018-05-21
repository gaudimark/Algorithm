<?php
namespace app\library\model;
use think\Model;

class User extends BasicModel{
    protected $name = "user";
    
    //查询列表
    public function getUserListByWhere($where,$limit=10,$order="id desc",$query=array()){
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    //获取所有用户
    public function getAllUserListByWhere(){
        return $this->select();
    }
    
    //根据用户名查询用户信息
    public function getUserInfoByName($name){
        return $this->where("nickname",$name)->find();
    }
    
    //根据用户ID查询用户信息
    public function getUserInfoByID($id){
        return $this->where("id",$id)->find();
    }
    
    //根据用户ID获取中奖次数
    public function getWinCountByUserID($userid){
        $this->name('arena_bet_detail');
        $where = [];
        $where["user_id"] = $userid;
        $where["win_money"] = [">","0"];
        return $this->where($where)->count(); 
    }

    
    //查询用户资金日志
    public function getUserFundsLogList($where,$limit=10,$order="ufl.id desc",$query){
        $this->name('user_funds_log')->alias("ufl");
        $this->join("__USER__ u","u.id = ufl.user_id","LEFT");
        $this->field("ufl.*,u.nickname as nickname,u.username as username");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);      
    }
    
    //用户资金日志汇总
    public function getUserFundsLogCount($where){
        $this->name('user_funds_log')->alias("ufl");
        $this->join("__USER__ u","u.id = ufl.user_id","LEFT");
        $this->field("sum(ufl.number) as total");
        return $this->where($where)->find();
        
    }
    
    //充值查询
    public function getOrderList($where,$limit=10,$order="o.id desc",$query){
        $this->name('recharge_order')->alias("o");
        $this->field("o.*,u.nickname");
        $this->join("__USER__ u","u.id = o.user_id","LEFT");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    //查询好友列表
    public function getUserFriendListByWhere($where,$limit=20,$order="uf.id desc",$query=[]){
        $this->name('user_friend')->alias("uf");
        $this->join("__USER__ u","u.id = uf.user_friend_id","LEFT");
        $this->field("u.*,uf.status as apply_status");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    //查询好友列表
    public function getUserFollowListByWhere($where,$limit=20,$order="uf.id desc",$query=[]){
        $this->name('user_follow')->alias("uf");
        $this->join("__USER__ u","u.id = uf.user_follow_id","LEFT");
        $this->field("u.*");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    //查询好友列表
    public function getUserFriendApplyListByWhere($where,$limit=20,$order="uf.id desc",$query){
        $this->name('user_friend')->alias("uf");
        $this->join("__USER__ u","u.id = uf.user_id","LEFT");
        $this->field("u.*,uf.id as ufid");
        $this->where($where);
        $this->order($order);
        return $this->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    public function getFriendInfo($where){
        $this->name('user_friend')->alias("uf");
        return $this->where($where)->find();
    }
    
    public function getUserFriendApplyByID($id){
        return $this->name("user_friend")->where("id",$id)->find();
    }
    
    public function updateUserFriendApply($id,$data){
        return $this->name("user_friend")->where("id",$id)->update($data);
    }
    
    public function insertUserFriendApply($data){
        return $this->name("user_friend")->insert($data);
    }
    
    public function deleteUserFriend($where){
        return $this->name("user_friend")->where($where)->delete();
    }
    
    public function getUserFriendApplyListCount($where){
        $this->name('user_friend')->alias("uf");
        $this->where($where);
        return $this->count();
    }
    
    public function insertUserFollow($data){
        return $this->name("user_follow")->insert($data);
    }
    
    public function deleteUserFollow($where){
        return $this->name("user_follow")->where($where)->delete();
    }
    
    //查询用户奖金
    public function getTopBonusByID($id,$type){
        $this->name("top_bonus");
        return $this->where(["user_id"=>$id,"type"=>$type])->find();
        
    }
    
    //查询大神
    public function getTopLeitaiWin($where,$limit=false){
        $this->name("top_leitai_win")->alias("tlw");
        //$this->join("__USER__ u","tlw.user_id=u.id","LEFT");
        //$this->field("tlw.*,u.avatar as avatar");
        $this->order("tlw.total desc");
        if($limit){
            $this->limit($limit);
        }
        return $this->where($where)->select();
    }


    public function cacheUserById($user_id){
        //$user = $this->where(['id' => $user_id])->find();
        //$user = $user->toArray();
        //unset($user['passport']);
        //Cache("user_{$user['id']}",$user);
        (new \library\service\User())->setCacheUser($user_id);
    }
    
    //更新用户表
    public function updateUserByUserID($data,$id){
        $result = $this->where(['id' => $id])->update($data);
        $this->cacheUserById($id);
        //(new \app\library\service\User())->setCacheUser($id);
        return $result;
    }
    
    
}