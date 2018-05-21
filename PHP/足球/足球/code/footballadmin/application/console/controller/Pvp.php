<?php
namespace app\console\controller;

use app\console\logic\Basic;
class Pvp extends Basic{
    public $game_type = GAME_TYPE_WCG;
    public $company = 23;
    private $isProxy = 0;//是否开启代理0否，1是

    //比赛信息
    public function index(){
        echo date("Y-m-d H:i:s")."\n";
        set_time_limit(0);
        //游戏ID
        $game_info = \think\Db::name("game")->where(["name|alias"=>"王者荣耀","status"=>1])->find();
        //王者荣耀赛事(顺序不能更改)
        $match_data = array(array("match_name"=>"职业联赛","match_url"=>"http://pvp.qq.com/match/kpl/"),array("match_name"=>"冠军杯","match_url"=>"http://pvp.qq.com/match/kcc.shtml"));
            
        if($match_data){
                foreach ($match_data as $key=>$val){
                        //判断起始日期
                        $match_url = $val["match_url"];
                        $match_str = $this->curl($match_url,'','','','',$this->isProxy);
                        $match_str = $this->trimall($match_str);
                        
                        $time = array();
                        if($key == 0){
                            $time_reg = "/<div class=\".*?kpl_schedule_date clearfix\".*?>([\s\S]*?)<\/div>/";
                            preg_match_all($time_reg,$match_str,$play_list);
                            if(isset($play_list[1][0])){
                                foreach ($play_list[1] as $list){
                                    $match_time = array();
                                    $li_reg = "/<a.*?data-btime='(.*?)'.*?data-etime='(.*?)'.*?data-seasonid='(.*?)'.*?>/";
                                    preg_match_all($li_reg,$list,$time_arr);
                                    if(isset($time_arr[0][0])){
                                        for($j=0;$j<count($time_arr[0]);$j++){
                                            $match_time[] = array("beginTime"=>$time_arr[1][$j],"endTime"=>$time_arr[2][$j],"seasonid"=>$time_arr[3][$j]);
                                        }
                                    }
                                    $time[] = $match_time;
                                }
                            }
                        }elseif($key == 1){
                            $time_reg = "/<div class=\"sehedule-tab\">[\s\S]*?<ul class=\"tab-hd\">([\s\S]*?)<\/ul>/";
                            preg_match_all($time_reg,$match_str,$play_list);
                            if(isset($play_list[1][0])){
                                foreach ($play_list[1] as $list){
                                    $match_time = array();
                                    $li_reg = "/<li class=\".*?\".*?data-btime='(.*?)'.*?data-etime='(.*?)'.*?>/";
                                    preg_match_all($li_reg,$list,$time_arr);
                                    if(isset($time_arr[0][0])){
                                        for($j=0;$j<count($time_arr[0]);$j++){
                                            $match_time[] = array("beginTime"=>$time_arr[1][$j],"endTime"=>$time_arr[2][$j]);
                                        }
                                    }
                                    $time[] = $match_time;
                                }
                            }
                        }
                        //上一周
                        $lbeginTime = mktime(0,0,0,date("m"),date("d")-date("w")+1-7,date("Y"));
                        $lendTime = mktime(0,0,0,date("m"),date("d")-date("w")+8-7,date("Y"));
                        //本周
                        $beginTime = mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y"));
                        $endTime = mktime(0,0,0,date("m"),date("d")-date("w")+8,date("Y"));
                        //下周
                        $nbeginTime = mktime(0,0,0,date("m"),date("d")-date("w")+1+7,date("Y"));
                        $nendTime = mktime(0,0,0,date("m"),date("d")-date("w")+8+7,date("Y"));
                        
                        if(isset($time[0])){
                            foreach ($time as $t){
                                for ($k=0;$k<count($t);$k++){
                                    if($key ==0){
                                        if(date("w") == 1){
                                            if((strtotime($t[$k]["beginTime"]) >= $lbeginTime && strtotime($t[$k]["endTime"]) <= $lendTime) || (strtotime($t[$k]["beginTime"]) >= $beginTime && strtotime($t[$k]["endTime"]) <= $endTime) || (strtotime($t[$k]["beginTime"]) >= $nbeginTime && strtotime($t[$k]["endTime"]) <= $nendTime)){
                                                //$this->getContent("http://api.tgatv.qq.com/match_request/index.php?type=schedule&beginTime={$t[$k]["beginTime"]}&endTime={$t[$k]["endTime"]}&seasonid={$t[$k]["seasonid"]}", $val["match_name"],$game_info["id"]);
                                            }
                                        }else{//不采集上周数据
                                            if((strtotime($t[$k]["beginTime"]) >= $beginTime && strtotime($t[$k]["endTime"]) <= $endTime) || (strtotime($t[$k]["beginTime"]) >= $nbeginTime && strtotime($t[$k]["endTime"]) <= $nendTime)){
                                                echo $k."\n";
                                                //$this->getContent("http://api.tgatv.qq.com/match_request/index.php?type=schedule&beginTime={$t[$k]["beginTime"]}&endTime={$t[$k]["endTime"]}&seasonid={$t[$k]["seasonid"]}", $val["match_name"],$game_info["id"]);
                                            }
                                        }
                                    }elseif ($key == 1){
                                        if(date("w") == 1){
                                            if((strtotime($t[$k]["beginTime"]) >= $lbeginTime && strtotime($t[$k]["endTime"]) <= $lendTime) || (strtotime($t[$k]["beginTime"]) >= $beginTime && strtotime($t[$k]["endTime"]) <= $endTime) || (strtotime($t[$k]["beginTime"]) >= $nbeginTime && strtotime($t[$k]["endTime"]) <= $nendTime)){
                                                $this->getContent("http://api.tgatv.qq.com/match_request/index.php?type=allchedule&beginTime={$t[$k]["beginTime"]}&endTime={$t[$k]["endTime"]}&seasonid=KCC2017%2CKCC2017S", $val["match_name"],$game_info["id"]);
                                            }
                                        }else{//不采集上周数据
                                            if((strtotime($t[$k]["beginTime"]) >= $beginTime && strtotime($t[$k]["endTime"]) <= $endTime) || (strtotime($t[$k]["beginTime"]) >= $nbeginTime && strtotime($t[$k]["endTime"]) <= $nendTime)){
                                                echo $k."\n";
                                                $this->getContent("http://api.tgatv.qq.com/match_request/index.php?type=allchedule&beginTime={$t[$k]["beginTime"]}&endTime={$t[$k]["endTime"]}&seasonid=KCC2017%2CKCC2017S", $val["match_name"],$game_info["id"]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //赔率采集
                        if(isset($time[0])){
                            foreach ($time as $t){
                                for ($k=0;$k<count($t);$k++){
                                    if((strtotime($t[$k]["beginTime"]) >= $beginTime && strtotime($t[$k]["endTime"]) <= $endTime) || (strtotime($t[$k]["beginTime"]) >= $nbeginTime && strtotime($t[$k]["endTime"]) <= $nendTime)){
                                        $btime = strtotime($t[$k]["beginTime"]);
                                        $etime = strtotime($t[$k]["endTime"]);
                                        if($etime > time()){
                                            if($btime< time()){
                                                $btime = time();
                                            }
                                            $day = ($etime-$btime)/(24*3600);
                                            for ($i=0;$i<$day;$i++){
                                                $num = date("Ymd",$btime+$i*24*3600);
                                                //echo $num."\n";
                                                $this->pvpOdds($num);
                                            }
                                        }
                                        
                                    }
                                }
                            }
                        }
                    
                }
            }
        
        echo date("Y-m-d H:i:s")."\n";
    }
    
    public function getContent($url,$match_name,$game_id){
        $match_json = $this->curl($url,'','','','',$this->isProxy);
        preg_match_all("/\((\{.*?\})\)/", $match_json,$match_data);
        $match_list = array();
        if(isset($match_data[1][0]))
            $match_list = json_decode($match_data[1][0],true);
        //联赛
        /*$match_info = \think\Db::name('match')->where("game_type",$this->game_type)->where('name|alias','eq',$match_name)->find();
        if(!$match_info){//如果不存在对于赛事，新增
            $md5_match = md5("pvp".trim($match_name));
            $match_data = array();
            $match_data["country_id"] = 1;
            $match_data["game_type"] = $this->game_type;
            $match_data["name"] = $match_name;
            $match_data["md5_match"] = $md5_match;
            $match_data["game_id"] = $game_id;
            $match_data["alias"] = $match_name;
            $match_id = \think\Db::name('match')->insert($match_data,false,true);
            model("admin/match")->upCacheOnly($match_id);
            $match_info = getMatch($match_id);
        }*/
        if(isset($match_list["result"]["matchResults"][0])){
            foreach ($match_list["result"]["matchResults"] as $v){
                //判断是否为个人赛
                if(isset($v["title"]) && trim($v["title"]) != $match_name){
                    $match_info = \think\Db::name('match')->where("game_type",$this->game_type)->where('name|alias','eq',trim($v["title"]))->find();
                    if(!$match_info){//如果不存在对于赛事，新增
                        $md5_match = md5("pvp".trim($v["title"]));
                        $match_data = array();
                        $match_data["country_id"] = 1;
                        $match_data["game_type"] = $this->game_type;
                        $match_data["name"] = trim($v["title"]);
                        $match_data["md5_match"] = $md5_match;
                        $match_data["game_id"] = $game_id;
                        $match_data["alias"] = trim($v["title"]);
                        $match_id = \think\Db::name('match')->insert($match_data,false,true);
                        model("admin/match")->upCacheOnly($match_id);
                        $match_info = getMatch($match_id);
                    }
                }else{
                    $match_info = \think\Db::name('match')->where("game_type",$this->game_type)->where('name|alias','eq',$match_name)->find();
                    if(!$match_info){//如果不存在对于赛事，新增
                        $md5_match = md5("pvp".trim($match_name));
                        $match_data = array();
                        $match_data["country_id"] = 1;
                        $match_data["game_type"] = $this->game_type;
                        $match_data["name"] = $match_name;
                        $match_data["md5_match"] = $md5_match;
                        $match_data["game_id"] = $game_id;
                        $match_data["alias"] = $match_name;
                        $match_id = \think\Db::name('match')->insert($match_data,false,true);
                        model("admin/match")->upCacheOnly($match_id);
                        $match_info = getMatch($match_id);
                    }
                }
                
                $eventId = $v["scheduleid"];
                $md5_play = md5("pvp".$eventId);
                $match_time = strtotime($v["match_time"]);
                $team_home = trim($v["hname"]);
                $team_guest = trim($v["gname"]);
                //echo $team_home."   ".$team_guest."\n";
                if($team_home == "待定" || $team_guest == "待定"){
                    continue;
                }
                //主场球队
                $team_home_info = \think\Db::name('team')->where(array("name|alias"=>$team_home,"game_type"=>$this->game_type))->find();
                //客场球队
                $team_guest_info = \think\Db::name('team')->where(array("name|alias"=>$team_guest,"game_type"=>$this->game_type))->find();
                //如果不存在，则先添加
                if(!$team_home_info){
                    //logo
                    $logo1 = "";
                    if($v["hlogo"] != ""){
                        $logo1 = $this->saveImg($v["hlogo"],$this->isProxy);
                    }
                    $htid = \think\Db::name('team')->insert(array("country_id"=>1,"logo"=>$logo1,"name"=>$team_home,"game_type"=>$this->game_type),false,true);
                    model("admin/team")->upCacheOnly($htid);
                    $team_home_info = getTeam($htid);
                }
                if(!$team_guest_info){
                    //logo
                    $logo2 = "";
                    if($v["glogo"] != ""){
                        $logo2 = $this->saveImg($v["glogo"],$this->isProxy);
                    }
                    $gtid = \think\Db::name('team')->insert(array("country_id"=>1,"logo"=>$logo2,"name"=>$team_guest,"game_type"=>$this->game_type),false,true);
                    model("admin/team")->upCacheOnly($gtid);
                    $team_guest_info = getTeam($gtid);
                }
                $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5_play))->find();
                if(!$play_info){
                    $play_info = \think\Db::name('play')->where(["game_type"=>$this->game_type,"game_id"=>$game_id,"match_id"=>$match_info["id"],"play_time"=>$match_time,"team_home_id"=>$team_home_info["id"],"team_guest_id"=>$team_guest_info["id"]])->find();
                }
                
                $play = array();//比赛
                $play["play_time"] = $match_time;
                $play["team_home_score"] = $v["host_score"];
                $play["team_guest_score"] = $v["guest_score"];
                $status = PLAT_STATUS_NOT_START;
                if($v["match_state"] == 4){
                    $status = PLAT_STATUS_END;
                }elseif($v["match_state"] == 1){
                    $status = PLAT_STATUS_NOT_START;
                }elseif($v["match_state"] == 3){
                    $status = PLAT_STATUS_START;
                }
                if($status == PLAT_STATUS_NOT_START && time() >= $match_time){
                    $status =  PLAT_STATUS_START;
                }
                $play["status"] = $status;
                $play["update_time"] = time();
                if($play_info){
                    if($play_info["status"] == PLAT_STATUS_END || $play_info["status"] == PLAT_STATUS_STATEMENT || $play_info["status"] == PLAT_STATUS_STATEMENT_BEGIN){
                        continue;
                    }
                    \think\Db::name('play')->where("id",$play_info["id"])->update($play);
                    //写入缓存
                    $this->checkArenaStatusByPlayStatus($play_info["id"], $status);
                    $this->insertPlayTeam($play_info["id"],$team_home_info["id"],1,$v["host_score"]);
                    $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],0,$v["guest_score"]);
                }else{
                    $play["game_id"] = $game_id;
                    $play["md5_play"] =  $md5_play;
                    $play["game_type"] = $this->game_type;
                    $play["match_id"] = $match_info["id"];
                    $play["team_home_id"] = $team_home_info["id"];
                    $play["team_home_name"] = $team_home_info["name"];
                    $play["team_guest_id"] = $team_guest_info["id"];
                    $play["team_guest_name"] = $team_guest_info["name"];
                    $play["has_odds"] = 0;
                    $play["create_time"] = time();
                    if($status == PLAT_STATUS_END){
                        $play["end_time"] = time();
                    }
                    $play_id = \think\Db::name('play')->insert($play,false,true);
                    $this->insertPlayTeam($play_id,$team_home_info["id"],1,$v["host_score"]);
                    $this->insertPlayTeam($play_id,$team_guest_info["id"],0,$v["guest_score"]);
                    //写入缓存
                    $this->checkArenaStatusByPlayStatus($play_id, $status);
                }
        
            }
        }
    }
    
    
    //赔率数据
    public function pvpOdds($day){
        $url = "https://qs.888.qq.com/node_pool/?d=es&c=esHonorV2&m=getTopicInfoLoop&day={$day}&ajax=true&cms_where=600017&vb2ctag=4_2064_1_5272&reportUin=1136486126&bc_web=1568214074&t=1493198926121&g_tk=315193993&_=1493198926124";
        //echo $url;
        $header = array("Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1");
        $content = $this->curl($url,'','http://qs.888.qq.com','',$header,'','',$this->isProxy);
        $list = json_decode($content,true);
        $game_info = \think\Db::name("game")->where(["alias|name" => "王者荣耀","status"=>1])->find();
       // $game_info = \think\Db::name("game")->where(['id' => 9])->find();

        if($game_info && isset($list["data"]["guessList"]) && $list["data"]["guessList"]){//胜负
            foreach ($list["data"]["guessList"] as $odds){
                $team_home = trim($odds["homeName"]);
                $team_guest = trim($odds["awayName"]);
                $play_time = strtotime(date("Y-m-d",$odds["startTime"]).$odds["timeTxt"]);
                $play_info = \think\Db::name('play')->where(["team_home_name"=>$team_home,"team_guest_name"=>$team_guest,"play_time"=>$play_time,"game_type"=>GAME_TYPE_WCG,"game_id"=>$game_info["id"]])->find();
                if(!$play_info){
                    $play_info = \think\Db::name('play')->where(["play_time"=>$play_time,"game_type"=>GAME_TYPE_WCG,"game_id"=>$game_info["id"]])->where(function($query) use($team_home,$team_guest){
                                    $query->where("team_home_name",$team_home)->whereOr("team_guest_name" , $team_guest);
                                })->find();
                    if(!$play_info){
                        $team_short = array("仙阁"=>"AS仙阁");
                        if(isset($team_short[$team_home])){
                            $team_home = $team_short[$team_home];
                        }
                        if(isset($team_short[$team_guest])){
                            $team_guest = $team_short[$team_guest];
                        }
                        $play_info = \think\Db::name('play')->where(["team_home_name"=>$team_home,"team_guest_name"=>$team_guest,"play_time"=>$play_time,"game_type"=>GAME_TYPE_WCG,"game_id"=>$game_info["id"]])->find();
                    }
                }
                $has_odds = $play_info["has_odds"];
                if($play_info){
                    foreach ($odds["topicList"] as $t){
                        $result = $this->getRuleType($t,$game_info["id"],$team_home,$team_guest,$play_info["id"],$this->company,$game_info);
                        if($result > $has_odds){
                            $has_odds = $result;
                        }
                    }
                }
                if($play_info && $play_info["has_odds"] == 0 && $has_odds >0){
                    \think\Db::name('play')->where("id",$play_info["id"])->update(["has_odds"=>$has_odds]);
               }
                echo $play_info["id"]."\n";
            }
        }
    }
    
    //判断玩法类别
    public function getRuleType($odds,$game_id,$team_home,$team_guest,$play_id,$company_id,$game_info){
        //print_r($odds);
        $has_odds = 0;
        //玩法名称
        $rule_name = urldecode($odds["extInfo"]["guessDesc"]);
        $rule_name = str_replace("为？", "", $rule_name);
        $rule_name = str_replace("是？", "", $rule_name);
        $rule_name = str_replace("？", "", $rule_name);
        //echo $team_home."  ".$rule_name."\n";
        
        //独赢
        if($rule_name == "全场比赛结果"){
            $rule_info = \think\Db::name('rules')->where(["name|alias"=>"独赢","game_type"=>GAME_TYPE_WCG,"game_id"=>$game_id])->find();
            if(!$rule_info){
                //$dy_data = explode("全场比赛结果",$rule_name);
                //if(count($dy_data)>1){
                    $rules_data = array("is_default"=>0,"is_edit"=>0,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>"独赢","alias"=>"独赢","intro"=>$rule_name,"type"=>RULES_TYPE_EUROPE,"explain"=>json_encode(array("home","guest")),"create_time"=>time(),"update_time"=>time(),"game_id"=>$game_info["id"],"game_type"=>$this->game_type);
                    $rules_id = \think\Db::name('rules')->insert($rules_data, false, true , null);
                    $rule_info = \think\Db::name('rules')->where("id",$rules_id)->find();
                    model("admin/rules")->upCache();
                //}
            }
            if($rule_info["type"] == RULES_TYPE_EUROPE){
                //赔率入库
                $ml = array();
                $home_odds = isset($odds["optionFormat"]["A"]["odds"])?$odds["optionFormat"]["A"]["odds"]:0;
                $guest_odds = isset($odds["optionFormat"]["C"]["odds"])?$odds["optionFormat"]["C"]["odds"]:0;
                
                $ml[] = array("name"=>$team_home,"odds"=>$home_odds);
                $ml[] = array("name"=>$team_guest,"odds"=>$guest_odds);
                if($home_odds >0 && $guest_odds >0){
                    //赔率入库
                    $this->oddsData($ml, $play_id, $rule_info["id"],RULES_TYPE_EUROPE,1,array(),$company_id);
                }
                $this->rulesDetail($game_info, $play_id, $rule_info, array($team_home,$team_guest));
                $has_odds = 1;
            }
        }
        
        //大小
        
        //让分
        //echo $has_odds."\n";
        return $has_odds;
    }
    
    //赔率
    public function oddsData($odds,$play_id,$rules_id,$rules_type,$loop=1,$odds_list=array(),$company_id=8){
        $odds_info = \think\Db::name('odds')->where(array("game_type"=>$this->game_type,"play_id"=>$play_id,"odds_company_id"=>$company_id,"loop"=>$loop,"rules_id"=>$rules_id))->find();
        //echo \think\Db::name('odds')->getlastsql()."\n";
        if($odds_info){
            //重新组装JSON
            $new_data = array();
            $odds_data = json_decode($odds_info["odds"],true);
            $new_data["init"] = $odds_data["init"];
            $new_data["time"] = $odds;
            //echo md5(json_encode($new_data))."   ".$odds_info["md5"]."\n";
            if(md5(json_encode($new_data)) != $odds_info["md5"]){//赔率发生变化
                $this->updateOdds($new_data,$odds_info["id"],$rules_type,$play_id);
                $this->insertOddsDetail($odds, $odds_info["id"]);
            }
        }else{//新增
            $new_data = array();
            $new_data["init"] = $odds;
            $new_data["time"] = $odds;
            $odds_id = $this->insertOdds($new_data,$rules_id, $rules_type, $play_id, $loop);
            $this->insertOddsDetail($odds, $odds_id);
        }
    }
    
    //插入odds表
    public function insertOdds($data,$rules,$rules_type,$play_id,$loop){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["play_id"] = $play_id;
        $odds_json["game_type"] = $this->game_type;
        $odds_json["odds_company_id"] = $this->company;
        $odds_json["loop"] = $loop;
        $odds_json["rules_id"] = $rules;
        $odds_json["rules_type"] = $rules_type;
        $odds_json["odds"] = json_encode($data);
        $odds_json["create_time"] = time();
        $odds_json["update_time"] = time();
    
        $odds_list_id = \think\Db::name('odds')->insert($odds_json, false, true , null);
        return $odds_list_id;
    }
    
    public function insertOddsDetail($data,$odds_id){
        $detail = array();
        $detail["odds_id"] = $odds_id;
        $detail["odds"] = json_encode($data);
        $detail["create_time"] = time();
        $detail["update_time"] = time();
        \think\Db::name('odds_detail')->insert($detail);
    }
    
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
    
    public function rulesDetail($game_info,$play_id,$rules_info,$rules_explain){
        //玩法详情表
        $detail_info = \think\Db::name('play_rules_detail')->where(["game_type"=>$this->game_type,"play_id"=>$play_id,"game_id"=>$game_info["id"],"rules_id"=>$rules_info["id"]])->find();
        $detail = array();
        $md5 = md5($this->game_type.$play_id.$rules_info["id"].$game_info["id"].json_encode($rules_explain));
        $detail["md5"] = $md5;
        $detail["update_time"] = time();
        $detail["rules_explain"] = json_encode($rules_explain);
    
        if(!$detail_info){
            $detail["game_type"] = $this->game_type;
            $detail["game_id"] = $game_info["id"];
            $detail["play_id"] = $play_id;
            $detail["rules_id"] = $rules_info["id"];
            $detail["create_time"] = time();
            \think\Db::name('play_rules_detail')->insert($detail);
        }else{
            if($md5 != $detail_info["md5"]){
                \think\Db::name('play_rules_detail')->where("id",$detail_info["id"])->update($detail);
            }
        }
    }
    
    
    //入库play_team表
    public function insertPlayTeam($play_id,$team_id,$has_home=0,$score=0,$half_score=0,$red=0,$yellow=0,$score_json=""){
        if($play_id>0 && $team_id >0){
            $data = array();
            $data["score"] = $score;
            $data["half_score"] = $half_score;
            $data["red"] = $red;
            $data["yellow"] = $yellow;
            $data["score_json"] = $score_json;
    
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
    
    
    
    
    //王者荣耀战队及选手
    public function team(){
        set_time_limit(0);
        $url = "http://pvp.qq.com/match/kpl/";
        //$content = iconv('gbk', 'UTF-8',$this->curl($url));
        //$content = $this->trimall($content);
        
        //选手
        $team_url = "http://mapps.game.qq.com/yxzj/match/GetGuild.php?imatchId=12&r=0.5246092946303951";
        $team_json = $this->curl($team_url,'','','','',$this->isProxy);
        $team_json = str_replace("var result =", "", $team_json);
        //echo $team_json;
        $team_list = json_decode($team_json,true);
        $team = $team_list["data"]["guild_list"];
        
        //游戏ID
        $game_info = \think\Db::name("game")->where(["name|alias"=>"王者荣耀","status"=>1])->find();
        if(!isset($team[0])){
            return;
        }
        foreach ($team as $t){
            $team_info = \think\Db::name("team")->where(["game_type"=>$this->game_type,"name"=>$t["guild_name"]])->find();
            $team_arr = array();
            $detail["update_time"] = time();
            
            if(isset($team_info["id"])){
                if($team_info["logo"] == ""){
                    //logo
                    $logo = $this->saveImg($t["guild_logo"]);
                    $team_arr["logo"] = $logo;
                }
                \think\Db::name("team")->where("id",$team_info["id"])->update($team_arr);
                model("admin/team")->upCacheOnly($team_info["id"]);
                
                //队员信息
                $team_detail_arr = array();
                foreach ($t["guild_member"] as $m){
                    $team_detail_info = \think\Db::name("team_detail")->where(["name"=>$m["member_name"],"team_id"=>$team_info["id"]])->find();
                    if(isset($team_detail_info["id"])){
                        continue;
                    }
                    $detail = array();
                    $detail["team_id"] = $team_info["id"];
                    $detail["name"] = $m["member_name"];
                    $detail["nickname"] = $m["member_nickname"];
                    $detail["logo"] = $this->saveImg($m["member_logo"]);
                    $detail["create_time"] = time();
                    $detail["update_time"] = time();
                    //国家
                    $country = \think\Db::name("country")->where("name",$m["member_position"])->find();
                    $country_id = 1;
                    if($country){
                        $country_id = $country["id"];
                    }
                    $detail["country_id"] = $country_id;
                    
                    $team_detail_arr[] = $detail;
                }
                //var_dump($team_detail_arr);
                \think\Db::name("team_detail")->insertAll($team_detail_arr,false,true);
            }else{
                $team_arr["country_id"] = 1;
                $team_arr["game_type"] = $this->game_type;
                $team_arr["md5"] = md5(str_replace(" ", "", strtolower($t["guild_name"])));
                $team_arr["name"] = $t["guild_name"];
                //logo
                $logo = $this->saveImg($t["guild_logo"]);
                $team_arr["logo"] = $logo;
                
                $team_id = \think\Db::name("team")->insert($team_arr,false,true);
                model("admin/team")->upCacheOnly($team_id);
                //队员信息
                $team_detail_arr = array();
                foreach ($t["guild_member"] as $m){
                    $detail = array();
                    $detail["team_id"] = $team_id;
                    $detail["name"] = $m["member_name"];
                    $detail["nickname"] = $m["member_nickname"];
                    $detail["logo"] = $this->saveImg($m["member_logo"]);
                    $detail["create_time"] = time();
                    
                    //国家
                    $country = \think\Db::name("country")->where("name",$m["member_position"])->find();
                    $country_id = 1;
                    if($country){
                        $country_id = $country["id"];
                    }
                    $detail["country_id"] = $country_id;
                    $team_detail_arr[] = $detail;
                }
                \think\Db::name("team_detail")->insertAll($team_detail_arr,false,true);
            
            }
        }
    }
    
    
    /*public function saveImg($url){
        if(!strstr($url, "nopic")){
            $save_src = "assets/attach/team_logo/";
            $md5_data = md5($url);
            $md5_path = substr($md5_data,0,2)."/".substr($md5_data,2,2)."/".substr($md5_data,4,2)."/";
            //$img = file_get_contents($url);
            $img = $this->getImg($url);
            $data = explode(".", $url);
            $i = count($data);
            $new_img = rand(100,999).time().rand(100,999).".".$data[$i-1];
            if(!file_exists($save_src.$md5_path))
                mkdir($save_src.$md5_path,0777,true);
            //file_put_contents($save_src.$md5_path.$new_img, $img);
            $fp= @fopen($save_src.$md5_path.$new_img,"a"); //将文件绑定到流 ??
            fwrite($fp,$img);
            return "team_logo/".$md5_path.$new_img;
        }else{
            return '';
        }
    
    }
    
    public function getImg($url){
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        ob_start ();
        curl_exec ( $ch );
        $return_content = ob_get_contents ();
        ob_end_clean ();
    
        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        return $return_content;
    }*/
    
    /*public function curl($url,$postData = [],$ref = '',$proxy = [],$header = []){
        $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
        $opts = [];
        $_[] = $header ? $header : 'User-Agent:'.$agent;
        $opts[CURLOPT_HTTPHEADER] = $_;
        $opts[CURLOPT_REFERER] = $ref;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_USERAGENT] = $agent;
        $opts[CURLOPT_RETURNTRANSFER] = true;//是否将结果返回
        $opts[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
    
        if($proxy){
            $opts[CURLOPT_PROXY] = $proxy['ip'];
            $opts[CURLOPT_PROXYPORT] = $proxy['port'];
        }
    
        if($postData){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $postData;
        }
    
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $content = curl_exec($ch);
        if($content === false){
            echo "CURL ERROR:".(curl_error($ch))."</br>";
            return false;
        }
        curl_close($ch);
        return $content;
    }*/
    
    public function trimall($str){
        //过滤
        $qian=array("   ","\t","\n","\r","  ");
        return str_replace($qian, '', $str);
    }
}

?>