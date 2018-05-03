<?php
namespace app\admin\model;
use app\library\model\BasicModel;

class Odds extends BasicModel{
    public function company(){
        return $this->belongsTo('oddsCompany','odds_company_id');
    }


    /*
     * 根据play_id获取赔率
     * */
    public function getOddsListByPlayID($play_id,$rules_ids){
        $this->name("odds")->alias("o");
        $this->join("__ODDS_COMPANY__ oc","oc.id = o.odds_company_id","LEFT");
        $this->field("o.*,oc.`name` as company_name");
        $this->where([
            'o.play_id' => ["=",$play_id],
            'o.rules_type' => ["exp","in ".$rules_ids]
        ]);
        return $this->select();
    }
}