<?php
namespace app\admin\model;
use think\Model;
use think\Db;

class User extends \app\library\model\User{
   
    public function getUserLogList($where,$limit=10,$order="ul.id desc",$query){
        $this->name("user_log")->alias("ul");
        //$where['ul.classify'] = USER_LOG_LOGIN; //登录日志
        $this->join("__USER__ u","ul.user_id=u.id","LEFT");
        $this->order($order);
        $this->field("ul.*,u.nickname as nickname");
        
        return $this->where($where)->paginate($limit,false,[
            "query"=>$query
        ]);
    }
    
    public function upCache(){
        (new \library\service\User())->setCacheAll();
        return true;
    }
    
    //查询活跃用户数
    public function getUserActive($where){
        $this->name("user_log")->alias("ul");
        $this->join("__USER__ u","ul.user_id=u.id","LEFT");
        $where['ul.classify'] = USER_LOG_LOGIN; //登录日志
        $this->group("ul.user_id");
        $ret = $this->where($where)->count();
        return $ret;
    }
    
    //统计新注册用户充值金额
    public function getUserCharge($where){
        $this->name("user")->alias("u");
        $this->join("__USER_FUNDS_LOG__ ufl","ufl.user_id=u.id","LEFT");
        return $this->where($where)->sum("number");
    }
    
    //统计充值用户数
    public function countUserCharge($where,$group=""){
        $this->name("user")->alias("u");
        $this->join("__USER_FUNDS_LOG__ ufl","ufl.user_id=u.id","LEFT");
        if($group){
            $this->group($group);
        }
        return $this->where($where)->count();
    }
    
}