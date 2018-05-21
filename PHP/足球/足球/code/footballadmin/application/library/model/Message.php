<?php
namespace app\library\model;

use think\Model;
Class Message extends BasicModel{
    
    protected $name = "sys_message";
    //查询系统消息
    public function getSysMessageListByWhere($where , $limit=10 , $order="m.id desc" , $query=[]){
        $this->alias("m");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
    }
    
    //根据id查询系统消息
    public function getSysMessageInfoByID($id){
        return $this->where("id",$id)->find();
    }
    
    //查询消息详情
    public function getDetail($where ,$limit=15 , $order="md.id desc",$query=[]){
        $this->table("sys_message_detail")->alias("md");
        $this->join("__SYS_MESSAGE__ m","md.message_id = m.id","LEFT");
        $this->field("md.*,m.content as content,m.title as title,m.create_time as send_time");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]); 
    }
    
    public function updateMessageDetail($data){
        return $this->table("sys_message_detail")->update($data);
        
    }
    
    //统计消息数量
    public function getMessageDetailCount($where){
        $this->table("sys_message_detail")->alias("md");
        $this->join("__SYS_MESSAGE__ m","md.message_id = m.id","LEFT");
        $this->field("md.*,m.content as content,m.title as title");
        return $this->where($where)->count();
    }
    
    
}