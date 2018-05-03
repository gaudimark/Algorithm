<?php
namespace app\admin\model;

class Arenarecommend extends \app\library\model\BasicModel{
    protected $name = "arena_recommend";
    
    //查询banner列表
    public function getList($where=[],$limit=15,$order="sort desc",$query=[]){
        $this->where($where);
        $this->order($order);
        
        return $this->paginate($limit,false,[
                'query' => $query
            ]);
    }
    
}

?>