<?php
namespace app\admin\model;
use think\Model;
use app\library\model\BasicModel;

class LookConfig extends BasicModel{
    public $table = 'look_config';

    public function upCache(){
        $data = $this->order("condition","asc")->select();
        $result = array();
        foreach($data as $val){
            $result[$val->condition] = $val->max_limit;
        }
        return cache("lookConfig",$result);
    }
}
LookConfig::event("after_write",function($model){
    $model->upCache();
});
LookConfig::event("after_delete",function($model){
    $model->upCache();
});