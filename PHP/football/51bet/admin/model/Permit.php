<?php
namespace app\admin\model;
use app\library\model\BasicModel;

class Permit extends BasicModel{


    public function findAll($field = ""){
        $menu = [];
        if($field){
            $this->field($field);
        }
        $data = $this->order("id asc")->select();
        foreach($data As $val){
            $menu[$val->id] = $val->toArray();
        }
        return arrayTree($menu,'id','parent_id');
        //dump($menu);
    }

}