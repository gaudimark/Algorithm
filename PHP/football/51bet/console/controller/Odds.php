<?php
/***
 * 赔率数据采集
 * */
namespace app\console\controller;

use think\Controller;
class Odds extends \app\console\logic\Basic{
    public $gameType = GAME_TYPE_FOOTBALL;
    private $isProxy = 0;//是否开启代理0否，1是，需要升级增加正向代理
    
    //获取当天赔率
    public function odds_today(){
        $ymd = input("ymd");
        $ymd = $ymd ? $ymd : date('Y-m-d');
        echo date("Y-m-d H:i:s")."\n";
        set_time_limit(540);
        //对应数据公司
        $c_where = [];
        $c_where["zc_id"] = [">",0];
        $odds_company = \think\Db::name('odds_company')->where($c_where)->select();
        $companys = array();
        foreach ($odds_company as $o){
            $companys[] = $o["zc_id"];
        }
        
        $url = "http://odds.zgzcw.com/odds/oyzs_ajax.action";//数据来源
        //传递参数
        $data = array();
        $data["type"] = "qb";
        $data["issue"] = $ymd;
        $data["date"] = "";//查询日期
        $data["companys"] = implode(",", $companys);
        //昨天
        $yestoday = array();
        $yestoday = $data;
        //$yestoday["date"] = date("Y-m-d",strtotime("-1 day"));
        
        $data["date"] = date("Y-m-d");

        $this->getFootballOdds_zc($yestoday,$url);
        $this->getFootballOdds_zc($data,$url);
        $two_day = $data;
        $two_day["date"] = date("Y-m-d",strtotime("+1 day"));
        //$this->getFootballOdds_zc($two_day,$url);
        //$this->oneData();
        echo date("Y-m-d H:i:s")."\n";
    }
    
    //采集中国足彩网的足球赔率
    public function getFootballOdds_zc($data,$url){
        $this->logTxt("赔率采集      操作时间:".date("Y-m-d H:i:s").",  采集日期：".(isset($data["date"]) && $data["date"] ? $data["date"] : $data["issue"])."  \r\n");
        //set_time_limit(540);
        //清理过期未开始的比赛
        $this->checkEnd();
        
        //模拟请求
        $return = $this->curl($url,$data,"http://odds.zgzcw.com", '' , '',$this->isProxy);
        $this->console('获取数据完成');
        //json转换
        $odds_list = json_decode($return,true);
        if($odds_list){
            $this->checkCancel($odds_list);
            foreach ($odds_list as $odds){
                //对应赛事
                if(!isset($odds["LEAGUE_NAME_SIMPLY"])){
                    continue;
                }
                $match_name = str_replace(" ","",$this->replaceStr($odds["LEAGUE_NAME_SIMPLY"]));
                $md5_match = "";
                if($odds["SOURCE_MATCH_ID"])
                    $md5_match = md5("zgzcw".$odds["SOURCE_MATCH_ID"]);
                $match_info = $this->getMatchInfo($match_name, $this->gameType, $odds["SOURCE_MATCH_ID"], $md5_match,$odds["COLOR"]);
                
                //判断是否已经存在对应比赛
                $md5_play = md5("zgzcw".$odds["ID"]);
                $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5_play))->find();
                $match_status = PLAT_STATUS_NOT_START;
                //对应比赛状态
                if($odds["MATCH_STATUS"]== -1){
                    $match_status = PLAT_STATUS_END;
                }elseif($odds["MATCH_STATUS"]== 1){
                    $match_status = PLAT_STATUS_START;
                }elseif($odds["MATCH_STATUS"]== 2){
                    $match_status = PLAT_STATUS_INTERMISSION;
                }elseif($odds["MATCH_STATUS"]== 3){
                    $match_status = PLAT_STATUS_START;
                }elseif($odds["MATCH_STATUS"]== -14){
                    $match_status = PLAT_STATUS_EXC;
                }elseif ($odds["MATCH_STATUS"] == -12){
                    $match_status = PLAT_STATUS_CUT;
                }elseif ($odds["MATCH_STATUS"] == -11){
                    $match_status = PLAT_STATUS_WAIT;
                }
                $this->console("md5:{$md5_play},状态：{$match_status},比赛时间：{$odds["MATCH_TIME"]},比赛ID:".($play_info ? $play_info['id'] : '--').",{$odds["HOST_NAME"]} VS {$odds["GUEST_NAME"]}");

                if($match_status == PLAT_STATUS_NOT_START && time() >= strtotime($odds["MATCH_TIME"])){
                    $match_status = PLAT_STATUS_START;
                }
                //亚盘，欧赔，大小
                $rule_asian_id = 0;
                $rule_ou_id = 0;
                $rule_dx_id = 0;//大小
                $rules_info = \think\Db::name('rules')->where(["game_type"=>$this->gameType,"status"=>1])->select();
                foreach ($rules_info as $ri){
                    if($ri["type"] == RULES_TYPE_ASIAN){
                        $rule_asian_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_EUROPE){
                        $rule_ou_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_OU){
                        $rule_dx_id = $ri["id"];
                    }
                    
                }
                
                
                if($play_info){//更新
                    $this->console("比赛:{$play_info['id']}");
                    //判断比赛状态是否已经结束，结束则不更新
                    if($play_info["status"] != PLAT_STATUS_END && $play_info["status"] != PLAT_STATUS_STATEMENT && $play_info["status"] != PLAT_STATUS_STATEMENT_BEGIN){
                        //更新比赛的比分
                        $play = array();//比赛
                        $play["team_home_score"] = $odds["HOST_GOAL"];
                        $play["team_guest_score"] = $odds["GUEST_GOAL"];
                        $play["play_time"] = strtotime($odds["MATCH_TIME"]);
                        $play["status"] = $match_status;
                        $play["update_time"] = time();
                        if($match_status == PLAT_STATUS_END){
                            $play["end_time"] = time();
                        }
                        \think\Db::name('play')->where("id",$play_info["id"])->update($play);
                        
                        $this->insertPlayTeam($play_info["id"],$play_info["team_home_id"],1,$odds["HOST_GOAL"]);
                        $this->insertPlayTeam($play_info["id"],$play_info["team_guest_id"],0,$odds["GUEST_GOAL"]);     
                        //更新缓存
                        if(isset($match_status)){
                            $this->checkArenaStatusByPlayStatus($play_info["id"], $match_status);
                        }
                        
                        //判断是否有无赔率
                        $hasOdds = 0;
                        foreach ($odds["listOdds"] as $company){
                            if(!isset($company["SOURCE_MATCH_ID"])){
                                continue;
                            }
                            //判断lt_odds_list表有无对应数据
                            $company_info = $this->odds_company[$company["SOURCE_COMPANY_ID"]];
                            if(!isset($company_info["id"])){
                                $company_info = \think\Db::name('odds_company')->where("zc_id",$company["SOURCE_COMPANY_ID"])->find();
                            }    
                            $is_odds_list = \think\Db::name('odds')->where(array("play_id"=>$play_info["id"],"odds_company_id"=>$company_info["id"],"modify"=>["in",[ODDS_ZGZCW_MODIFY,ODDS_ZGZCW_UNMODIFY]]))->select();
                            if($is_odds_list){//修改
                                //亚盘
                                if($company_info["has_asia"]){
                                    $asia_odds_data = $this->asiaDetail($company);
                                    if($asia_odds_data){
                                        $is_asia = 0;
                                        $hasOdds = 1;
                                        foreach ($is_odds_list as $is){
                                            if($is["rules_id"] == $rule_asian_id && $rule_asian_id != ""){
                                                if(md5(json_encode($asia_odds_data)) != $is["md5"] && $is["modify"] == ODDS_ZGZCW_MODIFY){//赔率有变化再去修改
                                                    $this->updateOdds($asia_odds_data, $is["id"],RULES_TYPE_ASIAN,$play_info["id"]);
                                                    //lt_odds_detail表
                                                    $this->insertOddsDetail($asia_odds_data["time"],$is["id"]);
                                                }
                                                $is_asia = 1;
                                            }
                                        }
                                        if($is_asia == 0){
                                            $asia_odds_id = $this->insertOdds($asia_odds_data, $rule_asian_id,RULES_TYPE_ASIAN, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                            //lt_odds_detail表
                                            $this->insertOddsDetail($asia_odds_data["init"],$asia_odds_id);
                                            $this->insertOddsDetail($asia_odds_data["time"],$asia_odds_id);
                                        }
                                        
                                    }
                                }
                                    
                                //欧赔
                                if($company_info["has_europe"]){
                                    $eur_odds_data = $this->eurDetail($company);
                                    if($eur_odds_data){
                                        $is_eur = 0;
                                        $hasOdds = 1;
                                        foreach ($is_odds_list as $is){
                                            if($is["rules_id"] == $rule_ou_id && $rule_ou_id != ""){
                                                if(md5(json_encode($eur_odds_data)) != $is["md5"] && $is["modify"] == ODDS_ZGZCW_MODIFY){//赔率有变化再去修改
                                                    $this->updateOdds($eur_odds_data, $is["id"],RULES_TYPE_EUROPE,$play_info["id"]);
                                                    //lt_odds_detail表
                                                    $this->insertOddsDetail($eur_odds_data["time"],$is["id"]);
                                                }
                                                $is_eur = 1;
                                            }  
                                        }
                                        if($is_eur == 0){
                                            $eur_odds_id = $this->insertOdds($eur_odds_data, $rule_ou_id,RULES_TYPE_EUROPE, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                            //lt_odds_detail表
                                            $this->insertOddsDetail($eur_odds_data["init"],$eur_odds_id);
                                            $this->insertOddsDetail($eur_odds_data["time"],$eur_odds_id);
                                        }
                                        
                                    }
                                }
                                //大小
                                if(isset($company["BIG"])){
                                    $ou_odds_data = $this->OuDetail($company);
                                    if($ou_odds_data){
                                        $is_ou = 0;
                                        $hasOdds = 1;
                                        foreach ($is_odds_list as $is){
                                            if($is["rules_id"] == $rule_dx_id && $rule_dx_id != ""){
                                                if(md5(json_encode($ou_odds_data)) != $is["md5"] && $is["modify"] == ODDS_ZGZCW_MODIFY){//赔率有变化再去修改
                                                    $this->updateOdds($ou_odds_data, $is["id"],RULES_TYPE_OU,$play_info["id"]);
                                                    //lt_odds_detail表
                                                    $this->insertOddsDetail($ou_odds_data["time"],$is["id"]);
                                                }
                                                $is_ou = 1;
                                            }
                                        }
                                        if($is_ou == 0){
                                            $ou_odds_id = $this->insertOdds($ou_odds_data, $rule_dx_id,RULES_TYPE_OU, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                            //lt_odds_detail表
                                            $this->insertOddsDetail($ou_odds_data["init"],$ou_odds_id);
                                            $this->insertOddsDetail($ou_odds_data["time"],$ou_odds_id);
                                        }
                                    }
                                }
                                
                            }else{//新增
                                if($company_info["has_asia"]){
                                    /*   亚盘                 **/
                                    $asia_odds_data = $this->asiaDetail($company);
                                    if($asia_odds_data && $rule_asian_id != ""){
                                        $hasOdds = 1;
                                        $asia_odds_id = $this->insertOdds($asia_odds_data, $rule_asian_id,RULES_TYPE_ASIAN, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                        //lt_odds_detail表
                                        $this->insertOddsDetail($asia_odds_data["init"],$asia_odds_id);
                                        $this->insertOddsDetail($asia_odds_data["time"],$asia_odds_id);
                                    }
                                }
                                if($company_info["has_europe"]){
                                    /*    欧赔                                               **/
                                    $eur_odds_data = $this->eurDetail($company);
                                    if($eur_odds_data && $rule_ou_id != ""){
                                        $hasOdds = 1;
                                        $eur_odds_id = $this->insertOdds($eur_odds_data, $rule_ou_id,RULES_TYPE_EUROPE, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                        //lt_odds_detail表
                                        $this->insertOddsDetail($eur_odds_data["init"],$eur_odds_id);
                                        $this->insertOddsDetail($eur_odds_data["time"],$eur_odds_id);
                                    }
                                }
                                //大小
                                if(isset($company["BIG"])){
                                    $ou_odds_data = $this->OuDetail($company);
                                    if($ou_odds_data && $rule_dx_id != ""){
                                        $hasOdds = 1;
                                        $ou_odds_id = $this->insertOdds($ou_odds_data, $rule_dx_id,RULES_TYPE_OU, $play_info["id"],$this->gameType,1, $company_info["id"]);
                                        //lt_odds_detail表
                                        $this->insertOddsDetail($ou_odds_data["init"],$ou_odds_id);
                                        $this->insertOddsDetail($ou_odds_data["time"],$ou_odds_id);
                                    }
                                    
                                }
                                
                            }
                        }
                        //更新比赛是否有赔率
                        \think\Db::name('play')->where("id",$play_info["id"])->update(array("has_odds"=>$hasOdds));
                        //八方预测
                        $bf_url = "http://fenxi.zgzcw.com/".$odds["ID"]."/bfyc";
                        $this->play_fenxi($bf_url,$play_info["id"],SYS_COMPANY);
                    }
                }else{//新增
                    $this->console("新增比赛");
                    //主场球队
                    $team_home_name = str_replace(" ","",$this->replaceStr($odds["HOST_NAME"]));
                    $team_home_info = \think\Db::name('team')->where("name",$team_home_name)->find();
                    //客场球队
                    $team_guest_name = str_replace(" ","",$this->replaceStr($odds["GUEST_NAME"]));
                    $team_guest_info = \think\Db::name('team')->where("name",$team_guest_name)->find();
                    //如果不存在对应球队，则先添加球队
                    if(!$team_home_info){
                        $zc_home_id = $odds["SOURCE_HOST_ID"];
                        $zc_url = "http://saishi.zgzcw.com/soccer/team/".$zc_home_id;
                        $htid = $this->getTeamInfo($zc_url, $team_home_name);
                        model("admin/team")->upCacheOnly($htid);
                        $team_home_info = getTeam($htid);
                        
                    }
                    if(!$team_guest_info){
                        $zc_guest_id = $odds["SOURCE_GUEST_ID"];
                        $zc_url = "http://saishi.zgzcw.com/soccer/team/".$zc_guest_id;
                        $gtid = $this->getTeamInfo($zc_url, $team_guest_name);
                        model("admin/team")->upCacheOnly($gtid);
                        $team_guest_info = getTeam($gtid);
                    }
                    //判断是否有无赔率
                    $hasOdds = 0;
                    
                    $play = array();//比赛
                    $play["md5_play"] =  $md5_play;
                    $play["game_type"] = GAME_TYPE_FOOTBALL;
                    $play["match_id"] = $match_info["id"];
                    $play["play_time"] = strtotime($odds["MATCH_TIME"]);
                    $play["team_home_id"] = $team_home_info["id"];
                    $play["team_home_name"] = $team_home_info["name"];
                    $play["team_guest_id"] = $team_guest_info["id"];
                    $play["team_guest_name"] = $team_guest_info["name"];
                    $play["team_home_score"] = $odds["HOST_GOAL"];
                    $play["team_guest_score"] = $odds["GUEST_GOAL"];
                    $play["status"] = $match_status;
                    $play["first_goals"] = 0;
                    $play["create_time"] = time();
                    $play["update_time"] = time();
                    if($match_status == PLAT_STATUS_END){
                        $play["end_time"] = time();//比赛结束时间
                    }
                    
                    
                    $play_id = \think\Db::name('play')->insert($play, false, true , null);
                    $this->insertPlayTeam($play_id,$team_home_info["id"],1,$odds["HOST_GOAL"]);
                    $this->insertPlayTeam($play_id,$team_guest_info["id"],0,$odds["GUEST_GOAL"]);
                    //更新缓存
                    if(isset($match_status)){
                        $this->checkArenaStatusByPlayStatus($play_id, $match_status);
                    }
                    
                    foreach ($odds["listOdds"] as $company){
                        if(!isset($company["SOURCE_MATCH_ID"])){
                            continue;
                        }
                        $company_info = $this->odds_company[$company["SOURCE_COMPANY_ID"]];
                        if(!isset($company_info["id"])){
                            $company_info = \think\Db::name('odds_company')->where("zc_id",$company["SOURCE_COMPANY_ID"])->find();
                        }
                        if($company_info["has_asia"]){//亚盘
                            $asia_odds_data = $this->asiaDetail($company);
                            if($asia_odds_data && $rule_asian_id != ""){
                                $hasOdds = 1;
                                $asia_odds_id = $this->insertOdds($asia_odds_data, $rule_asian_id,RULES_TYPE_ASIAN, $play_id,$this->gameType,1, $company_info["id"]);
                                //lt_odds_detail赔率详情表
                                $this->insertOddsDetail(array($asia_odds_data["init"],$asia_odds_data["time"]),$asia_odds_id,1);
                            }
                        }
                        if($company_info["has_europe"]){//欧赔
                            $eur_odds_data = $this->eurDetail($company);
                            if($eur_odds_data && $rule_ou_id != ""){
                                $hasOdds = 1;
                                $eur_odds_id = $this->insertOdds($eur_odds_data, $rule_ou_id,RULES_TYPE_EUROPE, $play_id,$this->gameType,1, $company_info["id"]);
                                //lt_odds_detail表
                                $this->insertOddsDetail(array($eur_odds_data["init"],$eur_odds_data["time"]),$eur_odds_id,1);
                            }
                        }
                        //大小
                        if(isset($company["BIG"])){//大小
                            $ou_odds_data = $this->OuDetail($company);
                            if($ou_odds_data && $rule_dx_id != ""){
                                $hasOdds = 1;
                                $ou_odds_id = $this->insertOdds($ou_odds_data, $rule_dx_id,RULES_TYPE_OU, $play_id,$this->gameType,1, $company_info["id"]);
                                //lt_odds_detail表
                                $this->insertOddsDetail(array($ou_odds_data["init"],$ou_odds_data["time"]),$ou_odds_id,1);
                            }
                        }
                    }
                    //更新比赛是否有赔率
                    \think\Db::name('play')->where("id",$play_id)->update(array("has_odds"=>$hasOdds));
                    //八方预测
                    $bf_url = "http://fenxi.zgzcw.com/".$odds["ID"]."/bfyc";
                    $this->play_fenxi($bf_url,$play_id,SYS_COMPANY);
                }
                $this->console("比赛:本次采集完成");
            }
            
        }else{
            $this->logTxt("empty data! ");
        }
    }
    
    //获取玩法id
    public function getRulesInfo($type,$gameType=1){
        $id = "";
        if($type){
            $data = \think\Db::name('rules')->where(["type"=>$type,"game_type"=>$gameType,"status"=>1])->find();
            $id = isset($data["id"])?$data["id"]:"";
        }
        return $id;
    }
    
    //判断比赛是不是取消
    public function checkCancel($data){
        if(isset($data[0])){
            $num = count($data);
            $beginTime = strtotime($data[0]["MATCH_TIME"]);
            $endTime = strtotime($data[$num-1]["MATCH_TIME"]);
            $btime = strtotime($data[0]["MATCH_TIME"]);
            $etime = strtotime($data[$num-1]["MATCH_TIME"]);//最后一场比赛的开始时间
            //中国足彩网的比赛，是从第一天中午12:00到第二天的中午12:00
            if(date("d",$btime) != date("d",$etime)){
                $beginTime = strtotime(date("Y-m-d",$btime)." 12:00:00");
                $endTime = strtotime(date("Y-m-d",$etime)." 12:00:00");
            }else{
                if(date("H",$btime) >= 12){
                    $beginTime = strtotime(date("Y-m-d",$btime)." 12:00:00");
                    $endTime = $beginTime + 12*3600;
                }else{
                    $beginTime = strtotime(date("Y-m-d",$btime)." 00:00:00");
                    $endTime = $beginTime + 12*3600;
                }
            }
            
            $play_list = \think\Db::name('play')->where(["game_type"=>$this->gameType,"status"=>PLAT_STATUS_NOT_START,"play_time"=>[[">=",$beginTime],["<",$endTime]]])->select();
            $now_match = array();
            if($play_list){
                foreach ($data as $odds){
                    if($odds["MATCH_STATUS"] == 0 || $odds["MATCH_STATUS"] == -14){
                        $md5_play = md5("zgzcw".$odds["ID"]);
                        $now_match[] = $md5_play;
                    }
                }            
                if(isset($now_match[0])){//数据库中的比赛如果没有出现在足彩网的比赛中，会将比赛状态设为待定
                    foreach ($play_list as $p){
                        $md5 = $p["md5_play"];
                        if(!in_array($md5, $now_match)){
                            \think\Db::name('play')->where("id",$p["id"])->update(["status"=>PLAT_STATUS_WAIT]);
                            //写入缓存
                            $this->checkArenaStatusByPlayStatus($p["id"], PLAT_STATUS_WAIT);
                        }
                    }
                }
            }
        }
    }
    
    //清理未开始的比赛
    public function checkEnd(){
        return false;
        //$playSrv = new \library\service\Play();
       // $time = time()-3*3600;
        //$end_list = \think\Db::name('play')->where(["play_time"=>["<=",$time],"status"=>["in",[PLAT_STATUS_NOT_START,PLAT_STATUS_START,PLAT_STATUS_INTERMISSION]],"game_type"=>$this->gameType])->select();
        //\think\Db::name('play')->where(["play_time"=>["<=",$time],"status"=>["in",[PLAT_STATUS_NOT_START,PLAT_STATUS_START,PLAT_STATUS_INTERMISSION]],"game_type"=>$this->gameType])->update(array("status"=>PLAT_STATUS_END));
        //foreach ($end_list as $end){
            //$this->checkArenaStatusByPlayStatus($end["id"], PLAT_STATUS_END);
        //}
    }
    
    //188对应采集
    public function oneData(){
        set_time_limit(180);
        $url = "https://sb.188188188188b.com/zh-cn/Service/CentralService?GetData&ts=1480470975503";
        $data = array();
        $data["IsFirstLoad"] = false;
        $data["VersionL"]=-1;
        $data["VersionU"]=0;
        $data["VersionS"]=-1;
        $data["VersionF"]=-1;
        $data["VersionH"]= "1:0,2:0,3:0,4:0,9:0,13:0,14:0,21:0,23:0";
        $data["VersionT"]=-1;
        $data["IsEventMenu"]=false;
        $data["SportID"]=1;
        $data["CompetitionID"]=-1;
        $data["reqUrl"]="/zh-cn/sports/football/competition/full-time-asian-handicap-and-over-under";
        $data["oIsInplayAll"]=false;
        $data["oIsFirstLoad"]= false;
        $data["oSortBy"]=1;
        $data["oOddsType"]=0;
        $data["oPageNo"]=0;
        
        $result = $this->curl($url,$data,'https://sb.188188188188b.com/zh-cn/sports/football/competition/full-time-asian-handicap-and-over-under','','',$this->isProxy);
        $odds_list = json_decode($result,true);
        if(isset($odds_list["mod"]["d"]["c"])){
            foreach ($odds_list["mod"]["d"]["c"] as $key=>$val){
                $match_name = $this->replaceStr(trim($val["n"]));//联赛名称
                //判断有无对应赛事
                $match_info = \think\Db::name('match')->where(["game_type"=>$this->gameType,"name|alias"=>$match_name])->find();
                if(!$match_info){
                    continue;
                }else{
                    foreach ($val["e"] as $k=>$v){
                        if($v["heid"] == 0){
                            $team_home = str_replace(" ","",$this->replaceStr($v["i"][0]));
                            $team_guest = str_replace(" ","",$this->replaceStr($v["i"][1]));
                            //主场球队
                            $team_home_info = \think\Db::name('team')->where("name",$team_home)->find();
                            //客场球队
                            $team_guest_info = \think\Db::name('team')->where("name",$team_guest)->find();
                            if(!$team_home_info || !$team_guest_info){
                                continue;
                            }
                            $match_time = strtotime(str_replace("T"," ",$v["edt"]))+12*3600;//比赛时间
                            //查询有无对应比赛
                            $play_where = [];
                            $play_where["match_id"] = $match_info["id"];
                            $play_where["team_home_id"] = $team_home_info["id"];
                            $play_where["team_guest_id"] = $team_guest_info["id"];
                            $play_where["play_time"] = $match_time;
                            $play_where["status"] = ["in",[PLAT_STATUS_NOT_START,PLAT_STATUS_EXC,PLAT_STATUS_WAIT]];
                            $play_info = \think\Db::name('play')->where($play_where)->find();
                            if(!$play_info){
                                continue;
                            }else{
                                echo $team_home."\n";
                                if($v["o"]["ou"]){//有大小赔率
                                    $num = count($v["o"]["ou"])/8;
                                    for ($i=0;$i<$num;$i++){
                                        $ou = array();
                                        $home_odds = isset($v["o"]["ou"][$i*8+5])?$v["o"]["ou"][$i*8+5]:0;
                                        $guest_odds = isset($v["o"]["ou"][$i*8+7])?$v["o"]["ou"][$i*8+7]:0;
                                        if(!is_numeric($v["o"]["ou"][$i*8+1])){
                                            continue;
                                        }
                                        $ou = array("home"=>$home_odds,"guest"=>$guest_odds,"over"=>isset($v["o"]["ou"][$i*8+1])?under($v["o"]["ou"][$i*8+1],false,false):0,"under"=>isset($v["o"]["ou"][$i*8+3])?under($v["o"]["ou"][$i*8+3],false,false):0);
                                        
                                        if($home_odds >0 && $guest_odds >0){
                                            $ou_id = $this->getRulesInfo(RULES_TYPE_OU,$this->gameType);
                                            //判断有无对应赔率
                                            $odds_info = \think\Db::name('odds')->where(["play_id"=>$play_info["id"],"odds_company_id"=>8,"user_id"=>0,"loop"=>$i+1,"rules_id"=>$ou_id,"game_type"=>$this->gameType,"modify"=>["in",[ODDS_ZGZCW_MODIFY,ODDS_ZGZCW_UNMODIFY]]])->find();
                                            if(!$odds_info){//新增
                                                $odds_data = array("init"=>$ou,"time"=>$ou);
                                                $odds_id = $this->insertOdds($odds_data, $ou_id,RULES_TYPE_OU, $play_info["id"], $this->gameType,$i+1,8);
                                                $this->insertOddsDetail($odds_data, $odds_id);
                                                
                                            }else{//修改
                                                $old_odds = json_decode($odds_info["odds"],true);
                                                $odds_data = array("init"=>$old_odds["init"],"time"=>$ou);
                                                $md5 = md5(json_encode($odds_data));
                                                if($md5 != $odds_info["md5"] && $odds_info["modify"] == ODDS_ZGZCW_MODIFY){
                                                    $this->updateOdds($odds_data, $odds_info["id"],RULES_TYPE_OU,$play_info["id"]);
                                                    $this->insertOddsDetail($odds_data, $odds_info["id"]);
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                    
                                    
                                }
                                
                            }
                            
                            
                        }
                    }     
                }
            }
        }
        
    }
    
    //亚盘数据
    public function asiaDetail($company){
        /*   亚盘                 **/
        $asia_oddslist = array();//lt_odds_list表
        $asia_oddslist["home"] = isset($company["HOST"])?$company["HOST"]:0;
        $asia_oddslist["guest"] = isset($company["GUEST"])?$company["GUEST"]:0;
        $asia_oddslist["handicap"] = isset($company["HANDICAP"])?$company["HANDICAP"]:0;
        if($asia_oddslist["home"]==0 && $asia_oddslist["guest"]==0 && $asia_oddslist["handicap"]==0){
            $asia_oddslist = array();
        }
        
        //初始赔率
        $asia_odds_default = array();
        $asia_odds_default["home"] = isset($company["FIRST_HOST"])?$company["FIRST_HOST"]:0;
        $asia_odds_default["guest"] = isset($company["FIRST_GUEST"])?$company["FIRST_GUEST"]:0;
        $asia_odds_default["handicap"] = isset($company["FIRST_HANDICAP"])?$company["FIRST_HANDICAP"]:0;
        if($asia_odds_default["home"]==0 && $asia_odds_default["guest"]==0 && $asia_odds_default["handicap"]==0){
            $asia_odds_default = array();
        }
        if($asia_oddslist||$asia_odds_default){
            return array("init"=>$asia_odds_default,"time"=>$asia_oddslist);
        }else{
            return array();
        }
    }
    
    //欧赔数据
    public function eurDetail($company){
        /*    欧赔                                               **/
        $eur_oddslist = array();
        $eur_oddslist["home"] = isset($company["WIN"])?$company["WIN"]:0;
        $eur_oddslist["same"] = isset($company["SAME"])?$company["SAME"]:0;
        $eur_oddslist["guest"] = isset($company["LOST"])?$company["LOST"]:0;
        
        
        if($eur_oddslist["home"]==0 && $eur_oddslist["guest"]==0 && $eur_oddslist["same"]==0){
            $eur_oddslist = array();
        }
        //初始赔率
        $eur_odds_default = array();
        $eur_odds_default["home"] = isset($company["FIRST_WIN"])?$company["FIRST_WIN"]:0;
        $eur_odds_default["same"] = isset($company["FIRST_SAME"])?$company["FIRST_SAME"]:0;
        $eur_odds_default["guest"] = isset($company["FIRST_LOST"])?$company["FIRST_LOST"]:0;
        
        if($eur_odds_default["home"]==0 && $eur_odds_default["guest"]==0 && $eur_odds_default["same"]==0){
            $eur_odds_default = array();
        }
        if($eur_oddslist||$eur_odds_default){
            return array("init"=>$eur_odds_default,"time"=>$eur_oddslist);
        }else{
            return array();
        }
        
    }
    
    //大小数据
    public function OuDetail($company){
        $ou_oddslist = array();
        $ou_oddslist["home"] = isset($company["BIG"])?$company["BIG"]:0;
        $ou_oddslist["guest"] = isset($company["SMALL"])?$company["SMALL"]:0;
        $ou_oddslist["over"] = isset($company["DW_HANDICAP"])?under($company["DW_HANDICAP"],false,false):0;
        $ou_oddslist["under"] = isset($company["DW_HANDICAP"])?under($company["DW_HANDICAP"],false,false):0;
        
        
        if($ou_oddslist["home"]==0 && $ou_oddslist["guest"]==0 && $ou_oddslist["over"]==0){
            $ou_oddslist = array();
        }
        
        //初始赔率
        $ou_odds_default = array();
        $ou_odds_default["home"] = isset($company["FIRST_BIG"])?$company["FIRST_BIG"]:0;
        $ou_odds_default["guest"] = isset($company["FIRST_LOST"])?$company["FIRST_SMALL"]:0;
        $ou_odds_default["over"] = isset($company["DW_FIRST_HANDICAP"])?under($company["DW_FIRST_HANDICAP"],false,false):0;
        $ou_odds_default["under"] = isset($company["DW_FIRST_HANDICAP"])?under($company["DW_FIRST_HANDICAP"],false,false):0;
        
        if($ou_odds_default["home"]==0 && $ou_odds_default["guest"]==0 && $ou_odds_default["over"]==0){
            $ou_odds_default = array();
        }
        if($ou_oddslist||$ou_odds_default){
            return array("init"=>$ou_odds_default,"time"=>$ou_oddslist);
        }else{
            return array();
        }
        
    }
    
    
    
    //查询odds表
    public function getOdds($play_id,$rules_type,$company_id){
        $where = [];
        $where["play_id"] = ["=",$play_id];
        $where["rules_type"] = ["=",$rules_type];
        $where["odds_company_id"] = ["=",$company_id];
        return \think\Db::name('odds')->where($where)->find();
    }
    
    //比赛分析
    public function play_fenxi($url,$play_id,$company_id){
        //set_time_limit(0);
        $content = $this->curl($url,'','','','',$this->isProxy);
        $data = array();
        if($content){
            $content_reg = "/<p class=\"only-bfyc-img\"><table width=\"100%\"[\s\S]*?<\/p>/";
            preg_match_all($content_reg, $content,$yuce);   
            
            if($yuce[0]){
                //更新比赛是否有赔率
                \think\Db::name('play')->where("id",$play_id)->update(array("has_odds"=>1));
                //比赛下的所有玩法信息
                $bodan_comb_id = 0;
                $all_goals_id = 0;
                $single_double_id = 0;
                $home_goals_id = 0;
                $guest_goals_id = 0;
                $max_goals_id = 0;
                $f_goals_id = 0;
                $bodan_id = 0;
                $rules_info = \think\Db::name('rules')->where(["game_type"=>$this->gameType,"status"=>1])->select();
                foreach ($rules_info as $ri){
                    if($ri["type"] == RULES_TYPE_BODAN_COMB){
                        $bodan_comb_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_ALL_GOALS){
                        $all_goals_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_SINGLE_DOUBLE){
                        $single_double_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_HOME_GOALS){
                        $home_goals_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_GUEST_GOALS){
                        $guest_goals_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_MAX_GOALS){
                        $max_goals_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_FIRST_GOALS){
                        $f_goals_id = $ri["id"];
                    }elseif($ri["type"] == RULES_TYPE_BODAN){
                        $bodan_id = $ri["id"];
                    }
                }
                
                $play_odds_list = \think\Db::name('odds')->where(["play_id"=>$play_id,"odds_company_id"=>$company_id,"user_id"=>0,"rules_id"=>["in",[$bodan_id,$f_goals_id,$max_goals_id,$guest_goals_id,$home_goals_id,$single_double_id,$bodan_comb_id,$all_goals_id]],"modify"=>["in",[ODDS_ZGZCW_MODIFY,ODDS_ZGZCW_UNMODIFY]]])->select();
                
                $odds_list = array();
                $odds_detail_list = array();
                foreach ($play_odds_list as $pol){
                    $npol = array();
                    $npol["md5"] = $pol["md5"];
                    $npol["id"] = $pol["id"];
                    $npol["user_id"] = $pol["user_id"];
                    $npol["game_type"] = $pol["game_type"];
                    $npol["play_id"] = $pol["play_id"];
                    $npol["odds_company_id"] = $pol["odds_company_id"];
                    $npol["loop"] = $pol["loop"];
                    $npol["rules_id"] = $pol["rules_id"];
                    $npol["odds"] = $pol["odds"];
                    $npol["modify"] = $pol["modify"];
                    $npol["create_time"] = $pol["create_time"];
                    $npol["update_time"] = $pol["update_time"];
                    
                    if(!isset($odds_list[$pol["rules_id"]])){
                        $odds_list[$pol["rules_id"]] = $npol;
                    }
                }


                $table_reg = "/<table border=\"0\"[\s\S]*?<\/table>/";
                preg_match_all($table_reg, $yuce[0][0] ,$table_list);
                
                
                //波胆组合
                $bdzh_odds = array();
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][5],$bdzh_arr);
                if($bdzh_arr[2][1] && $bdzh_arr[3][1] && $bdzh_arr[4][1] && $bdzh_arr[2][2] && $bdzh_arr[3][2] && $bdzh_arr[4][2]){
                    $bdzh_odds["home"] = array("bd_1"=>$bdzh_arr[2][1],"bd_2"=>$bdzh_arr[3][1],"bd_3"=>$bdzh_arr[4][1]);
                    $bdzh_odds["guest"] = array("bd_1"=>$bdzh_arr[2][2],"bd_2"=>$bdzh_arr[3][2],"bd_3"=>$bdzh_arr[4][2]);
                    $bdzh_list = isset($odds_list[$bodan_comb_id])?$odds_list[$bodan_comb_id]:array();
                    if($bdzh_list && $bodan_comb_id != ""){
                        if($bdzh_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $bdzh_init = json_decode($bdzh_list["odds"],true);
                            $this->updateOdds(array("init"=>$bdzh_init["init"],"time"=>$bdzh_odds), $bdzh_list["id"],RULES_TYPE_BODAN_COMB,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$bdzh_list["id"],"odds"=>json_encode($bdzh_odds),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($bodan_comb_id != ""){
                            $bdzh_odds_id = $this->insertOdds(array("init"=>$bdzh_odds,"time"=>$bdzh_odds), $bodan_comb_id,RULES_TYPE_BODAN_COMB, $play_id,$this->gameType,1,$company_id);
                            $odds_detail_list[] = array("odds_id"=>$bdzh_odds_id,"odds"=>json_encode($bdzh_odds),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                //全场入球总数
                
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][6],$qcrq_arr);
                if($qcrq_arr[1][1] && $qcrq_arr[2][1] && $qcrq_arr[3][1] && $qcrq_arr[4][1]){
                    $qcrq = array();
                    $qcrq["all_goals_0_1"] = $qcrq_arr[1][1];
                    $qcrq["all_goals_2_3"] = $qcrq_arr[2][1];
                    $qcrq["all_goals_4_6"] = $qcrq_arr[3][1];
                    $qcrq["all_goals_7"] = $qcrq_arr[4][1];
                    $qcrq_list = isset($odds_list[$all_goals_id])?$odds_list[$all_goals_id]:array();
                    if($qcrq_list && $all_goals_id != ""){
                        if($qcrq_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $qcrq_init = json_decode($qcrq_list["odds"],true);
                            $this->updateOdds(array("init"=>$qcrq_init["init"],"time"=>$qcrq), $qcrq_list["id"],RULES_TYPE_ALL_GOALS,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$qcrq_list["id"],"odds"=>json_encode($qcrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($all_goals_id != ""){
                            $qcrq_odds_id = $this->insertOdds(array("init"=>$qcrq,"time"=>$qcrq), $all_goals_id,RULES_TYPE_ALL_GOALS, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$qcrq_odds_id,"odds"=>json_encode($qcrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                //单双数
                
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][7],$dss_arr);
                if($dss_arr[1][1] && $dss_arr[2][1]){
                    $dss = array();
                    
                    if($dss_arr[1][1] > 1 && $dss_arr[2][1] >1){
                        $dss["sd_1"] = $dss_arr[1][1]-1;
                        $dss["sd_2"] = $dss_arr[2][1]-1;
                        $dss_list =  isset($odds_list[$single_double_id])?$odds_list[$single_double_id]:array();
                        if($dss_list && $single_double_id != ""){
                            if($dss_list["modify"] == ODDS_ZGZCW_MODIFY){
                                $dss_init = json_decode($dss_list["odds"],true);
                                $this->updateOdds(array("init"=>$dss_init["init"],"time"=>$dss), $dss_list["id"],RULES_TYPE_SINGLE_DOUBLE,$play_id);
                                $odds_detail_list[] = array("odds_id"=>$dss_list["id"],"odds"=>json_encode($dss),"create_time"=>time(),"update_time"=>time());
                            }
                        }else{
                            if($single_double_id != ""){
                                $dss_odds_id = $this->insertOdds(array("init"=>$dss,"time"=>$dss), $single_double_id,RULES_TYPE_SINGLE_DOUBLE, $play_id,$this->gameType,1, $company_id);
                                $odds_detail_list[] = array("odds_id"=>$dss_odds_id,"odds"=>json_encode($dss),"create_time"=>time(),"update_time"=>time());
                            }
                        }
                    }
                }
                //球队入球数
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][8],$qdrq_arr);
                //主队入球数
                if($qdrq_arr[2][1] && $qdrq_arr[3][1] && $qdrq_arr[4][1] && $qdrq_arr[5][1] && $qdrq_arr[6][1] && $qdrq_arr[7][1]){
                    $zqdrq = array();
                    $zqdrq["home_0"] = $qdrq_arr[2][1];
                    $zqdrq["home_1"] = $qdrq_arr[3][1];
                    $zqdrq["home_2"] = $qdrq_arr[4][1];
                    $zqdrq["home_3"] = $qdrq_arr[5][1];
                    $zqdrq["home_4"] = $qdrq_arr[6][1];
                    $zqdrq["home_5"] = $qdrq_arr[7][1];
                    $zqdrq_list = isset($odds_list[$home_goals_id])?$odds_list[$home_goals_id]:array();
                    if($zqdrq_list && $home_goals_id != ""){
                        if($zqdrq_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $zqdrq_init = json_decode($zqdrq_list["odds"],true);
                            $this->updateOdds(array("init"=>$zqdrq_init["init"],"time"=>$zqdrq), $zqdrq_list["id"],RULES_TYPE_HOME_GOALS,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$zqdrq_list["id"],"odds"=>json_encode($zqdrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($home_goals_id != ""){
                            $zqdrq_odds_id = $this->insertOdds(array("init"=>$zqdrq,"time"=>$zqdrq), $home_goals_id,RULES_TYPE_HOME_GOALS, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$zqdrq_odds_id,"odds"=>json_encode($zqdrq),"create_time"=>time(),"update_time"=>time());
                    
                        }
                    }
                }
                //客队入球数
                if($qdrq_arr[2][2] && $qdrq_arr[3][2] && $qdrq_arr[4][2] && $qdrq_arr[5][2] && $qdrq_arr[6][2] && $qdrq_arr[7][2]){
                    $kqdrq = array();
                    $kqdrq["guest_0"] = $qdrq_arr[2][2];
                    $kqdrq["guest_1"] = $qdrq_arr[3][2];
                    $kqdrq["guest_2"] = $qdrq_arr[4][2];
                    $kqdrq["guest_3"] = $qdrq_arr[5][2];
                    $kqdrq["guest_4"] = $qdrq_arr[6][2];
                    $kqdrq["guest_5"] = $qdrq_arr[7][2];
                    $kqdrq_list = isset($odds_list[$guest_goals_id])?$odds_list[$guest_goals_id]:array();
                    if($kqdrq_list && $guest_goals_id != ""){
                        if($kqdrq_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $kqdrq_init = json_decode($kqdrq_list["odds"],true);
                            $this->updateOdds(array("init"=>$kqdrq_init["init"],"time"=>$kqdrq), $kqdrq_list["id"],RULES_TYPE_GUEST_GOALS,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$kqdrq_list["id"],"odds"=>json_encode($kqdrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($guest_goals_id != ""){
                            $kqdrq_odds_id = $this->insertOdds(array("init"=>$kqdrq,"time"=>$kqdrq), $guest_goals_id,RULES_TYPE_GUEST_GOALS, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$kqdrq_odds_id,"odds"=>json_encode($kqdrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                //上/下半场入球较多
                
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][9],$sxbc_arr);
                if($sxbc_arr[1][1] && $sxbc_arr[2][1] && $sxbc_arr[3][1]){
                    $sxbc = array();
                    $sxbc["max_goals_1"] = $sxbc_arr[1][1];
                    $sxbc["max_goals_2"] = $sxbc_arr[2][1];
                    $sxbc["max_goals_3"] = $sxbc_arr[3][1];
                    $sxbc_list = isset($odds_list[$max_goals_id])?$odds_list[$max_goals_id]:array();
                    if($sxbc_list && $max_goals_id != ""){
                        if($sxbc_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $sxbc_init = json_decode($sxbc_list["odds"],true);
                            $this->updateOdds(array("init"=>$sxbc_init["init"],"time"=>$sxbc), $sxbc_list["id"],RULES_TYPE_MAX_GOALS,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$sxbc_list["id"],"odds"=>json_encode($sxbc),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($max_goals_id != ""){
                            $sxbc_odds_id = $this->insertOdds(array("init"=>$sxbc,"time"=>$sxbc), $max_goals_id,RULES_TYPE_MAX_GOALS, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$sxbc_odds_id,"odds"=>json_encode($sxbc),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                //最先入球球队
                
                preg_match_all("/<td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>/",$table_list[0][10],$zxrq_arr);
                if($zxrq_arr[1][1] && $zxrq_arr[2][1] && $zxrq_arr[3][1]){
                    $zxrq = array();
                    $zxrq["home"] = $zxrq_arr[1][1];
                    $zxrq["guest"] = $zxrq_arr[2][1];
                    $zxrq["zero"] = $zxrq_arr[3][1];
                    $zxrq_list = isset($odds_list[$f_goals_id])?$odds_list[$f_goals_id]:array();
                    if($zxrq_list && $f_goals_id != ""){
                        if($zxrq_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $zxrq_init = json_decode($zxrq_list["odds"],true);
                            $this->updateOdds(array("init"=>$zxrq_init["init"],"time"=>$zxrq), $zxrq_list["id"],RULES_TYPE_FIRST_GOALS,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$zxrq_list["id"],"odds"=>json_encode($zxrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($f_goals_id != ""){
                            $zxrq_odds_id = $this->insertOdds(array("init"=>$zxrq,"time"=>$zxrq), $f_goals_id,RULES_TYPE_FIRST_GOALS, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$zxrq_odds_id,"odds"=>json_encode($zxrq),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                //波胆
                
                preg_match_all("/<tr align=\"center\"><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td><td.*?>(.*?)<\/td>(.*?)<\/tr>/",$table_list[0][4],$bd_arr);
                preg_match_all("/<td.*?>(.*?)<\/td>/", $bd_arr[count($bd_arr)-1][0],$bd1_arr);
                preg_match_all("/<td.*?>(.*?)<\/td>/", $bd_arr[count($bd_arr)-1][1],$bd2_arr);
                if($bd_arr[2][1] && $bd_arr[3][1] && $bd_arr[4][1] && $bd_arr[5][1] && $bd_arr[6][1] && $bd_arr[7][1] && $bd_arr[8][1] && $bd_arr[9][1] && $bd_arr[10][1] && $bd_arr[11][1] && $bd_arr[2][2]&& $bd_arr[3][2]&& $bd_arr[4][2]&& $bd_arr[5][2]&& $bd_arr[6][2]&& $bd_arr[7][2]&& $bd_arr[8][2]&& $bd_arr[9][2]&& $bd_arr[10][2]&& $bd_arr[11][2] && $bd2_arr[1][0] && $bd2_arr[1][1] && $bd2_arr[1][2] && $bd2_arr[1][3] && $bd2_arr[1][4] && $bd2_arr[1][5] ){
                    $bd = array();
                    $bd["home"]["bd_1_0"] = $bd_arr[2][1];
                    $bd["home"]["bd_2_0"] = $bd_arr[3][1];
                    $bd["home"]["bd_2_1"] = $bd_arr[4][1];
                    $bd["home"]["bd_3_0"] = $bd_arr[5][1];
                    $bd["home"]["bd_3_1"] = $bd_arr[6][1];
                    $bd["home"]["bd_3_2"] = $bd_arr[7][1];
                    $bd["home"]["bd_4_0"] = $bd_arr[8][1];
                    $bd["home"]["bd_4_1"] = $bd_arr[9][1];
                    $bd["home"]["bd_4_2"] = $bd_arr[10][1];
                    $bd["home"]["bd_4_3"] = $bd_arr[11][1];
                    $bd["guest"]["bd_1_0"] = $bd_arr[2][2];
                    $bd["guest"]["bd_2_0"] = $bd_arr[3][2];
                    $bd["guest"]["bd_2_1"] = $bd_arr[4][2];
                    $bd["guest"]["bd_3_0"] = $bd_arr[5][2];
                    $bd["guest"]["bd_3_1"] = $bd_arr[6][2];
                    $bd["guest"]["bd_3_2"] = $bd_arr[7][2];
                    $bd["guest"]["bd_4_0"] = $bd_arr[8][2];
                    $bd["guest"]["bd_4_1"] = $bd_arr[9][2];
                    $bd["guest"]["bd_4_2"] = $bd_arr[10][2];
                    $bd["guest"]["bd_4_3"] = $bd_arr[11][2];
                    $bd["same"]["bd_0_0"] = $bd2_arr[1][0];
                    $bd["same"]["bd_1_1"] = $bd2_arr[1][1];
                    $bd["same"]["bd_2_2"] = $bd2_arr[1][2];
                    $bd["same"]["bd_3_3"] = $bd2_arr[1][3];
                    $bd["same"]["bd_4_4"] = $bd2_arr[1][4];
                    $bd["other"]["other"] = $bd2_arr[1][5];
                  
                    $bd_list = isset($odds_list[$bodan_id])?$odds_list[$bodan_id]:array();
                    if($bd_list && $bodan_id != ""){
                        if($bd_list["modify"] == ODDS_ZGZCW_MODIFY){
                            $bd_init = json_decode($bd_list["odds"],true);
                            $this->updateOdds(array("init"=>$bd_init["init"],"time"=>$bd), $bd_list["id"],RULES_TYPE_BODAN,$play_id);
                            $odds_detail_list[] = array("odds_id"=>$bd_list["id"],"odds"=>json_encode($bd),"create_time"=>time(),"update_time"=>time());
                        }
                    }else{
                        if($bodan_id != ""){
                            $bd_odds_id = $this->insertOdds(array("init"=>$bd,"time"=>$bd), $bodan_id,RULES_TYPE_BODAN, $play_id,$this->gameType,1, $company_id);
                            $odds_detail_list[] = array("odds_id"=>$bd_odds_id,"odds"=>json_encode($bd),"create_time"=>time(),"update_time"=>time());
                        }
                    }
                }
                \think\Db::name('odds_detail')->insertAll($odds_detail_list);
                
            }
            
        }
        
    }
    
    
    public function trimall($str){
        $qian = array("  ","\t","\n","\r","   ");
        return str_replace($qian, '', $str);
    }
    
    
    //入库play_team表
    public function insertPlayTeam($play_id,$team_id,$has_home=0,$score=0,$half_score=0,$red=0,$yellow=0,$score_json=""){
        if($play_id>0 && $team_id >0){
            $data = array();
            $data["score"] = $score;
            
            $play_team = \think\Db::name('play_team')->where(["play_id"=>$play_id,"team_id"=>$team_id,"has_home"=>$has_home])->find();
            if(isset($play_team["id"])){
                \think\Db::name('play_team')->where("id",$play_team["id"])->update($data);
            }else{
                $data["play_id"] = $play_id;
                $data["team_id"] = $team_id;
                $data["has_home"] = $has_home;
                \think\Db::name('play_team')->insert($data);
            }
            
            
            
        }
    }
    
    //采集球队信息
    public function getTeamInfo($url,$name){
        $tcontent = $this->curl($url,'','','','',$this->isProxy);
        if($tcontent){
            $tcontent = $this->trimall($tcontent);
            $t_reg = "/<dl class=\"star_dl\">[\s\S]*?国家：<\/span><var>(.*?)<\/var>[\s\S]*?src=\"(.*?)\"[\s\S]*?球队成立：<\/span><var>(.*?)<\/var>[\s\S]*?主教练：<\/span><var>(.*?)<\/var>[\S\s]*?<a rel=\"nofollow\" href=\"(.*?)\"/";
            preg_match_all($t_reg, $tcontent ,$team_data);
            $teams = array();
            $country_name = isset($team_data[1][0])?$team_data[1][0]:'';
            $country_id = 1;
            if($country_name){
                $country = \think\Db::name('country')->where("name",trim($country_name))->find();
                if($country){
                    $country_id = $country["id"];
                }
            }
            $teams["country_id"] = $country_id;
            $teams["name"] = $name;
    
            if($team_data[0]){
    
                $teams["coach"] = isset($team_data[4][0])?$team_data[4][0]:'';
                $teams["website"] = isset($team_data[5][0])?$team_data[5][0]:'';
                $teams["found"] = isset($team_data[3][0])?$team_data[3][0]:'';
                if(isset($team_data[2][0]))
                    $teams["logo"] = $this->saveImg($team_data[2][0],$this->isProxy);
                else
                    $teams["logo"] = '';
    
            }
            $teams["create_time"] = time();
            $htid = \think\Db::name('team')->insert($teams,false,true);
            return $htid;
        }
    
    }
    
    //插入odds表
    public function insertOdds($data,$rules,$rules_type,$play_id,$gameType,$loop=1,$company=8){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["play_id"] = $play_id;
        $odds_json["game_type"] = $gameType;
        $odds_json["odds_company_id"] = $company;
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
    public function insertOddsDetail($data,$odds_id,$is_array=0){
        if($is_array){
            $detail = array();
            foreach ($data as $d){
                $arr = array();
                $arr["odds_id"] = $odds_id;
                $arr["odds"] = json_encode($d);
                $arr["create_time"] = time();
                $arr["update_time"] = time();
                $detail[] = $arr;
            }
            \think\Db::name('odds_detail')->insertAll($detail);
        }else{
            $detail = array();
            $detail["odds_id"] = $odds_id;
            $detail["odds"] = json_encode($data);
            $detail["create_time"] = time();
            $detail["update_time"] = time();
            \think\Db::name('odds_detail')->insert($detail);
        }
    }
    //更新赔率
    public function updateOdds($data,$id,$rules_type,$play_id){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["odds"] = json_encode($data);
        $odds_json["rules_type"] = $rules_type;
        $odds_json["update_time"] = time();
    
        \think\Db::name('odds')->where("id",$id)->update($odds_json);
        $oddsSrv = new \library\service\Odds();
        $oddsSrv->updateArenaOddsByAutoArena($id,$play_id);
    }
    
}