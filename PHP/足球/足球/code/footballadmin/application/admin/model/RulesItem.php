<?php

namespace app\admin\model;
use think\Model;
use app\library\model\BasicModel;

class RulesItem extends BasicModel{
    public $name = 'rules_item';
    public function rules(){
        return $this->belongsTo("Rules");
    }
}