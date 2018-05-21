<?php

namespace app\admin\model;
use app\library\model\BasicModel;
use think\Db;

class Help extends BasicModel{
    protected $name = "help";
    
    public function getHelpListByWhere($where , $limit=10 , $order="m.id desc" , $query=[]){
        $this->alias("m");
        $this->join("help_type c","m.type_id=c.id","LEFT");
        $this->field("m.*,c.name as type_name");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
    }
}