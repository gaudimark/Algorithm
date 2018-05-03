<?php
/***
 * 采集中国足彩网自己开出的足球赔率
 * */
namespace app\console\controller;

class Oddszgzcw extends \app\console\logic\Basic{
    public $gameType = GAME_TYPE_FOOTBALL;
    
    public function index(){
        $this->console('Oddszgzcw Begin');
        set_time_limit(0);
        $url = "http://cp.zgzcw.com/lottery/jchtplayvsForJsp.action?lotteryId=47&type=jcmini";
        $content = $this->trimall($this->curl($url));
        $list_reg = "/<div class=\"tz-body\" id=\"dcc\">[\s\S]*?<div class=\"footer-fix\" id=\"ggArea\" style=\"display:none\">/";
        preg_match_all($list_reg, $content,$list_data);
        if(isset($list_data[0][0])){
            $play_reg = "/<tr .*?>.*?<\/tr>/";
            preg_match_all($play_reg, $list_data[0][0] , $play_data);
            if(isset($play_data[0])){
                //足彩公司
                $company = \think\Db::name('odds_company')->where("name","中国足彩网")->find();
                foreach ($play_data[0] as $p){
                    //球队
                    $team_home_reg = "/<td class=\"wh-4 t-r\"><a href=.*?>(.*?)<\/a>/";
                    $team_guest_reg = "/<td class=\"wh-6 t-l\">.*?<a href=.*?>(.*?)<\/a>/";
                    preg_match_all($team_home_reg,$p,$team_home_arr);
                    preg_match_all($team_guest_reg,$p,$team_guest_arr);
                    $team_home = isset($team_home_arr[1][0])?trim($team_home_arr[1][0]):"";
                    $team_guest = isset($team_guest_arr[1][0])?trim($team_guest_arr[1][0]):"";
                    //比赛时间
                    $time_reg = "/<spantitle=\"比赛时间:(.*?)\"style=\"display:none\">/";
                    preg_match_all($time_reg,$p,$time_arr);
                    $time = isset($time_arr[1][0])?strtotime($time_arr[1][0]):0;
                    if(!$team_home || !$team_guest || !$time){
                        continue;
                    }
                    $where = [];
                    $where["play_time"] = $time;
                    $where["team_home_name"] = $team_home;
                    $where["team_guest_name"] = $team_guest;
                    $play_info = \think\Db::name('play')->where($where)->find();
                    if($play_info){
                        //让球
                        $rq_reg = "/<div class=\"tz-area tz-area-2 rqq\".*?<em class=.*?>(.*?)<\/em><a href=.*?class=\"weisai\">(.*?)<s><\/s><\/a><a href=.*?class=\"weisai\">(.*?)<s><\/s><\/a><a href=.*?class=\"weisai\">(.*?)<s><\/s><\/a>/";
                        preg_match_all($rq_reg,$p,$rq_arr);
                        $rq_data = array();
                        if(isset($rq_arr[1][0]) && isset($rq_arr[2][0]) && isset($rq_arr[4][0])){
                            $rq_data = array("home"=>$rq_arr[2][0],"guest"=>$rq_arr[4][0] , "handicap"=>str_replace("+", "", $rq_arr[1][0]));
                        }
                        //胜平负
                        $dy_reg = "/<div class=\"tz-area frq\".*?<a href.*?class=\"weisai\">(.*?)<s><\/s><\/a><a href=.*?class=\"weisai\">(.*?)<s><\/s><\/a><a href=.*?class=\"weisai\">(.*?)<s><\/s><\/a>/";
                        preg_match_all($dy_reg,$p,$dy_arr);
                        $dy_data = array();
                        if(isset($dy_arr[1][0]) && isset($dy_arr[2][0]) && isset($dy_arr[3][0])){
                            $dy_data = array("home"=>$dy_arr[1][0],"same"=>$dy_arr[2][0],"guest"=>$dy_arr[3][0]);
                        }
                        
                        $asian_rules = \think\Db::name('rules')->where(["type"=>RULES_TYPE_ASIAN,"game_type"=>$this->gameType,"status"=>1])->find();
                        $eur_rules = \think\Db::name('rules')->where(["type"=>RULES_TYPE_EUROPE,"game_type"=>$this->gameType,"status"=>1])->find();
                        if($asian_rules)
                            $this->oddsData($rq_data, $play_info["id"],$asian_rules["id"], RULES_TYPE_ASIAN,1,array(),$company["id"]);
                        if($eur_rules)
                            $this->oddsData($dy_data, $play_info["id"],$eur_rules["id"], RULES_TYPE_EUROPE,1,array(),$company["id"]);
                        
                    }
                }
            }
        }
        $this->console('Oddszgzcw end');
    }
    
    //赔率
    public function oddsData($odds,$play_id,$rules_id,$rules_type,$loop=1,$odds_list=array(),$company_id=8){
        $odds_info = \think\Db::name('odds')->where(array("game_type"=>$this->gameType,"play_id"=>$play_id,"odds_company_id"=>$company_id,"loop"=>$loop,"rules_id"=>$rules_id,"modify"=>["in",[ODDS_ZGZCW_MODIFY,ODDS_ZGZCW_UNMODIFY]]))->find();
        if($odds_info){
            //重新组装JSON
            $new_data = array();
            $odds_data = json_decode($odds_info["odds"],true);
            $new_data["init"] = $odds_data["init"];
            $new_data["time"] = $odds;
            //赔率发生变化才更新
            if(md5(json_encode($new_data)) != $odds_info["md5"] && $odds_info["modify"] == ODDS_ZGZCW_MODIFY){
                $this->updateOdds($new_data,$odds_info["id"],$rules_type,$play_id);
                $this->insertOddsDetail($odds, $odds_info["id"]);
            }
        }else{//新增
            $new_data = array();
            $new_data["init"] = $odds;
            $new_data["time"] = $odds;
            $odds_id = $this->insertOdds($new_data,$rules_id, $rules_type, $play_id, $loop,$company_id);
            $this->insertOddsDetail($odds, $odds_id);
        }
    }
    
    //插入odds表
    public function insertOdds($data,$rules,$rules_type,$play_id,$loop,$company_id=8){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["play_id"] = $play_id;
        $odds_json["game_type"] = $this->gameType;
        $odds_json["odds_company_id"] = $company_id;
        $odds_json["loop"] = $loop;
        $odds_json["rules_id"] = $rules;
        $odds_json["rules_type"] = $rules_type;
        $odds_json["odds"] = json_encode($data);
        $odds_json["modify"] = ODDS_ZGZCW_MODIFY;
        $odds_json["create_time"] = time();
        $odds_json["update_time"] = time();
    
        $odds_list_id = \think\Db::name('odds')->insert($odds_json, false, true , null);
        return $odds_list_id;
    }
    //插入赔率详情
    public function insertOddsDetail($data,$odds_id){
        $detail = array();
        $detail["odds_id"] = $odds_id;
        $detail["odds"] = json_encode($data);
        $detail["create_time"] = time();
        $detail["update_time"] = time();
        \think\Db::name('odds_detail')->insert($detail);
    }
    //更新赔率
    public function updateOdds($data,$id,$rules_type,$play_id){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["odds"] = json_encode($data);
        $odds_json["update_time"] = time();
        $odds_json["rules_type"] = $rules_type;
    
        \think\Db::name('odds')->where("id",$id)->update($odds_json);
        $oddsSrv = new \library\service\Odds();
        $oddsSrv->updateArenaOddsByAutoArena($id,$play_id);
    }
    
    public function trimall($str){
        $qian = array("  ","\t","\n","\r","   ");
        return str_replace($qian, '', $str);
    }
    
}