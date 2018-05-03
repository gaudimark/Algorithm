<?php
namespace app\admin\model;
use think\Model;
use app\library\model\BasicModel;

class OddsCompany extends BasicModel{
    public $name = 'odds_company';

    public function upCache(){
        $list = $this->order("id asc")->select();
        $data = [];
        if($list){
            foreach($list as $val){
                $data[$val->id] = $val->toArray();
            }
        }
        return cache('odds_company',$data);
    }
}