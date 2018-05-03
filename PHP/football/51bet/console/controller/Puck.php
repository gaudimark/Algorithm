<?php
namespace app\console\controller;

class Puck extends \app\console\logic\Basic{
    public $gameType = GAME_TYPE_PUCK;
    
    public function index(){
        set_time_limit(0);
        $url = "https://sb.188188188188b.com/zh-cn/Service/CentralService?GetData&ts=1480470975503";
        $data = array();
        $data["IsFirstLoad"] = false;
        $data["VersionL"]=-1;
        $data["VersionU"]=0;
        $data["VersionS"]=-1;
        $data["VersionF"]=-1;
        $data["VersionH"]= "1:0,2:0,7:0,9:0,13:0,21:0,26:0";
        $data["VersionT"]=-1;
        $data["IsEventMenu"]=false;
        $data["SportID"]=1;
        $data["CompetitionID"]=-1;
        $data["reqUrl"]="/zh-cn/sports/ice-hockey/competition/full-time-asian-handicap-and-over-under";
        $data["oIsInplayAll"]=false;
        $data["oIsFirstLoad"]= false;
        $data["oSortBy"]=1;
        $data["oOddsType"]=0;
        $data["oPageNo"]=0;

        $result = $this->curl($url,$data,'https://sb.188188188188b.com/zh-cn/sports/ice-hockey/competition/full-time-asian-handicap-and-over-under');
        $odds_list = json_decode($result,true);
        $playSrv = new \library\service\Play();
        //print_r($odds_list);
        
        if(isset($odds_list["mod"])){//未开始
            //print_r($odds_list["mod"]["d"]["c"]);
            foreach ($odds_list["mod"]["d"]["c"] as $key=>$val){
                $data = array();
                $match_name = trim($val["n"]);//联赛名称
                $md5_match = "";
                if($val["k"])
                    $md5_match = md5("188puck".trim($val["k"]));
                
                //联赛
                $match_info = \think\Db::name('match')->where("md5_match",$md5_match)->find();
                if(!$match_info || !$md5_match){
                    $match_where = [];
                    $match_where["name"] = $match_name;
                    $match_where["alias"] = $match_name;
                    $match_info = \think\Db::name('match')->whereOr($match_where)->find();
                    if($match_info && $md5_match){
                        \think\Db::name('match')->where("id",$match_info["id"])->update(array("md5_match"=>$md5_match));
                        model("admin/match")->upCacheOnly($match_info["id"]);
                    }
                }
                if(!$match_info){//如果不存在对于赛事，新增
                    $match_data = array();
                    $match_data["country_id"] = 1;
                    $match_data["game_type"] = $this->gameType;
                    $match_data["md5_match"] = $md5_match;
                    $match_data["name"] = $match_name;
                    $match_id = \think\Db::name('match')->insert($match_data,false,true);
                    //$match_info = \think\Db::name('match')->where("id",$match_id)->find();
                    model("admin/match")->upCacheOnly($match_id);
                    $match_info = getMatch($match_id);
                }else{
                    foreach ($val["e"] as $k=>$v){
                        //print_r($v);
                        if($v["heid"] == 0){
                            //判断是否已经存在对应比赛
                            $eventId = $v["pk"];
                            $md5_play = md5("188puck".$eventId);
                            $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5_play))->find();
                            $match_time = strtotime(str_replace("T"," ",$v["edt"]))+12*3600;
                            $team_home = str_replace(" ","",$v["i"][0]);
                            $team_guest = str_replace(" ","",$v["i"][1]);
                            //主场球队
                            $team_home_info = \think\Db::name('team')->where("name",$team_home)->find();
                            //客场球队
                            $team_guest_info = \think\Db::name('team')->where("name",$team_guest)->find();
                            //如果不存在，则先添加
                            if(!$team_home_info){
                                $htid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_home,"game_type"=>$this->gameType),false,true);
                                //$team_home_info = \think\Db::name('team')->where("id",$htid)->find();
                                model("admin/team")->upCacheOnly($htid);
                                $team_home_info = getTeam($htid);
                            }
                            if(!$team_guest_info){
                                $gtid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_guest,"game_type"=>$this->gameType),false,true);
                                //$team_guest_info = \think\Db::name('team')->where("id",$gtid)->find();
                                model("admin/team")->upCacheOnly($gtid);
                                $team_guest_info = getTeam($gtid);
                            }
        
                            if($play_info){//更新数据
                                //判断比赛状态是否已经结束，结束则不更新
                                if($play_info["status"] != PLAT_STATUS_END && $play_info["status"] != PLAT_STATUS_STATEMENT){
                                    $this->getData($play_info["id"], $v,$team_home,$team_guest);
                                    $this->insertPlayTeam($play_info["id"],$team_home_info["id"],1);
                                    $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],0);
                                }
        
                            }else{//插入新数据
                                if($team_home_info && $team_guest_info){
                                    $play = array();//比赛
                                    $play["md5_play"] =  $md5_play;
                                    $play["game_type"] = $this->gameType;
                                    $play["match_id"] = $match_info["id"];
                                    $play["play_time"] = $match_time;
                                    $play["team_home_id"] = $team_home_info["id"];
                                    $play["team_home_name"] = $team_home_info["name"];
                                    $play["team_guest_id"] = $team_guest_info["id"];
                                    $play["team_guest_name"] = $team_guest_info["name"];
                                    $play["has_odds"] = 1;
                                    $play["status"] = PLAT_STATUS_NOT_START;
                                    $play_id = \think\Db::name('play')->insert($play, false, true , null);
                                    $this->getData($play_id, $v,$team_home,$team_guest);
                                    $this->insertPlayTeam($play_id,$team_home_info["id"],1);
                                    $this->insertPlayTeam($play_id,$team_guest_info["id"],0);
                                    //写入缓存
                                    $playSrv->upCache($play_id);
                                    $playSrv->cacheTeams($play_id);
                                }
                            }
                        }
                        //break;
                    }
                }
                //break;
            }
        
        }else{
            echo date("Y-m-d H:i:s")."未获取到数据\n";
        }
        $this->inPlay();
        model("admin/match")->upCache();
        model("admin/rules")->upCache();
    }
    
    //更新正在进行的比赛
    public function inPlay(){
        set_time_limit(0);
        $url = "https://sb.188188188188b.com/zh-cn/Service/CentralService?GetData&ts=1480555226166";
        $data = array();
        $data["IsFirstLoad"] = false;
        $data["VersionL"]=-1;
        $data["VersionU"]=0;
        $data["VersionS"]=-1;
        $data["VersionF"]=-1;
        $data["VersionH"]= "1:0,2:0,7:0,9:0,13:0,21:0,26:0";
        $data["VersionT"]=-1;
        $data["IsEventMenu"]=false;
        $data["SportID"]=1;
        $data["CompetitionID"]=-1;
        $data["reqUrl"]="/zh-cn/sports/26/in-play/full-time-asian-handicap-and-over-under";
        $data["oIsInplayAll"]=false;
        $data["oIsFirstLoad"]= false;
        $data["oSortBy"]=1;
        $data["oOddsType"]=0;
        $data["oPageNo"]=0;
    
        $result = $this->curl($url,$data,'https://sb.188188188188b.com/zh-cn/sports/26/in-play/full-time-asian-handicap-and-over-under');
        $odds_list = json_decode($result,true);
        $playSrv = new \library\service\Play();
        //print_r($odds_list);
        if(isset($odds_list["mod"]["d"])){
            $this->checkEnd($odds_list["mod"]["d"]["c"]);
            foreach ($odds_list["mod"]["d"]["c"] as $key=>$val){
                $data = array();
                $match_name = trim($val["n"]);//联赛名称
                //联赛
                $md5_match = "";
                if($val["k"])
                    $md5_match = md5("188puck".trim($val["k"]));
                
                //联赛
                $match_info = \think\Db::name('match')->where("md5_match",$md5_match)->find();
                if(!$match_info || !$md5_match){
                    $match_where = [];
                    $match_where["name"] = $match_name;
                    $match_where["alias"] = $match_name;
                    $match_info = \think\Db::name('match')->whereOr($match_where)->find();
                    if($match_info && $md5_match){
                        \think\Db::name('match')->where("id",$match_info["id"])->update(array("md5_match"=>$md5_match));
                        model("admin/match")->upCacheOnly($match_info["id"]);
                    }
                }
                if(!$match_info){//如果不存在对于赛事，新增
                    $match_data = array();
                    $match_data["country_id"] = 1;
                    $match_data["game_type"] = $this->gameType;
                    $match_data["md5_match"] = $md5_match;
                    $match_data["name"] = $match_name;
                    $match_id = \think\Db::name('match')->insert($match_data,false,true);
                    //$match_info = \think\Db::name('match')->where("id",$match_id)->find();
                    model("admin/match")->upCacheOnly($match_id);
                    $match_info = getMatch($match_id);
                }else{
                    foreach ($val["e"] as $k=>$v){
                        if($v["heid"] == 0){
                            //判断是否已经存在对应比赛
                            $eventId = $v["pk"];
                            $md5_play = md5("188puck".$eventId);
                            $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5_play))->find();
                            $match_time = strtotime(str_replace("T"," ",$v["edt"]))+12*3600;
                            $team_home = str_replace(" ","",$v["i"][0]);
                            $team_guest = str_replace(" ","",$v["i"][1]);
                            //主场球队
                            $team_home_info = \think\Db::name('team')->where("name",$team_home)->find();
                            //客场球队
                            $team_guest_info = \think\Db::name('team')->where("name",$team_guest)->find();
                            //如果不存在，则先添加
                            if(!$team_home_info){
                                $htid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_home,"game_type"=>$this->gameType),false,true);
                                //$team_home_info = \think\Db::name('team')->where("id",$htid)->find();
                                model("admin/team")->upCacheOnly($htid);
                                $team_home_info = getTeam($htid);
                            }
                            if(!$team_guest_info){
                                $gtid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_guest,"game_type"=>$this->gameType),false,true);
                                //$team_guest_info = \think\Db::name('team')->where("id",$gtid)->find();
                                model("admin/team")->upCacheOnly($gtid);
                                $team_guest_info = getTeam($gtid);
                            }
                            //print_r($v);
                            $play = array();//比赛
                            $play["team_home_score"] = isset($v["i"][10])?$v["i"][10]:0;
                            $play["team_guest_score"] = isset($v["i"][11])?$v["i"][11]:0;
                            //$play["team_home_half_score"] = isset($v["sb"]["ps"][0]["h"])?$v["sb"]["ps"][0]["h"]:0;
                            //$play["team_guest_half_score"] = isset($v["sb"]["ps"][0]["a"])?$v["sb"]["ps"][0]["a"]:0;
                            
                            //$cp_time = isset($v["sb"]["ct"])?$v["sb"]["ct"]:"20:00";
                            //$play["match_time"] = $cp_time;
                            if($play_info){//更新数据
                                //判断比赛状态是否已经结束，结束则不更新
                                if($play_info["status"] != PLAT_STATUS_END && $play_info["status"] != PLAT_STATUS_STATEMENT){
                                    \think\Db::name('play')->where("id",$play_info["id"])->update($play);
                                    $this->getData($play_info["id"], $v,$team_home,$team_guest,2);
                                    $this->insertPlayTeam($play_info["id"],$team_home_info["id"],1,$play["team_home_score"],0,0,0,"");
                                    $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],0,$play["team_guest_score"],0,0,0,"");
                                    //写入缓存
                                    $playSrv->upCache($play_info["id"]);
                                    $playSrv->cacheTeams($play_info["id"]);
                                }
    
                            }else{//插入新数据
                                if($team_home_info && $team_guest_info){
                                    //比赛
                                    $play["md5_play"] =  $md5_play;
                                    $play["game_type"] = $this->gameType;
                                    $play["match_id"] = $match_info["id"];
                                    $play["play_time"] = $match_time;
                                    $play["team_home_id"] = $team_home_info["id"];
                                    $play["team_home_name"] = $team_home_info["name"];
                                    $play["team_guest_id"] = $team_guest_info["id"];
                                    $play["team_guest_name"] = $team_guest_info["name"];
                                    $play["has_odds"] = 1;
                                    $play["status"] = PLAT_STATUS_START;
                                    $play_id = \think\Db::name('play')->insert($play, false, true , null);
                                    $this->getData($play_id, $v,$team_home,$team_guest,2);
                                    $this->insertPlayTeam($play_id,$team_home_info["id"],1,$play["team_home_score"],0,0,0,"");
                                    $this->insertPlayTeam($play_id,$team_guest_info["id"],0,$play["team_guest_score"],0,0,0,"");
                                    //写入缓存
                                    $playSrv->upCache($play_id);
                                    $playSrv->cacheTeams($play_id);
                                }
                            }
                        }
                    }
                }
            }
        }
    
    }
    
    //判断比赛是否结束
    public function checkEnd($data){
        $playSrv = new \library\service\Play();
        if(isset($data[0])){
            $play_list = \think\Db::name('play')->where(["play_time"=>["<",time()],"game_type"=>$this->gameType,"status"=>["neq",PLAT_STATUS_END],"has_statement"=>["neq",1]])->select();
            $now_match = array();
            foreach ($data as $key=>$val){
                foreach ($val["e"] as $k=>$v){
                    $eventId = $v["pk"];
                    $md5_play = md5("188puck".$eventId);
                    $now_match[] = $md5_play;
                }
            }
            if(isset($now_match[0])){
                foreach ($play_list as $p){
                    $md5 = $p["md5_play"];
                    if(!in_array($md5, $now_match)){
                        \think\Db::name('play')->where("id",$p["id"])->update(["status"=>PLAT_STATUS_END,"end_time"=>time(),"match_time"=>""]);
                        //写入缓存
                        $playSrv->upCache($p["id"]);
                    }
                }
            }else{
                $end_list = \think\Db::name('play')->where(["play_time"=>["<",time()],"game_type"=>$this->gameType,"status"=>["neq",PLAT_STATUS_END],"has_statement"=>["neq",1]])->select();
                \think\Db::name('play')->where(["play_time"=>["<",time()],"game_type"=>$this->gameType,"status"=>["neq",PLAT_STATUS_END],"has_statement"=>["neq",1]])->update(["status"=>PLAT_STATUS_END,"end_time"=>time(),"match_time"=>""]);
                foreach ($end_list as $end){
                    $playSrv->upCache($end["id"]);
                }
            }
    
        }else{
            $end_list = \think\Db::name('play')->where(["play_time"=>["<",time()],"game_type"=>$this->gameType,"status"=>["neq",PLAT_STATUS_END],"has_statement"=>["neq",1]])->select();
            \think\Db::name('play')->where(["play_time"=>["<",time()],"game_type"=>$this->gameType,"status"=>["neq",PLAT_STATUS_END],"has_statement"=>["neq",1]])->update(["status"=>PLAT_STATUS_END,"end_time"=>time(),"match_time"=>""]);
            foreach ($end_list as $end){
                $playSrv->upCache($end["id"]);
            }
        }
    }
    
    //查询具体一场比赛的页面内容
    public function getData($play_id,$v,$team_home,$team_guest,$status=1){
        set_time_limit(0);
        $url = "https://sb.188188188188b.com/zh-cn/Service/CentralService?GetData&ts=1480474607934";
        $data = array();
        $data["IsFirstLoad"] = false;
        $data["VersionL"]=-1;
        $data["VersionU"]=0;
        $data["VersionS"]=-1;
        $data["VersionF"]=-1;
        $data["VersionH"]= "1:0,2:0,7:0,9:0,13:0,21:0,26:0";
        $data["VersionT"]=-1;
        $data["IsEventMenu"]=false;
        $data["SportID"]=1;
        $data["CompetitionID"]=-1;
        if($status == 1)
            $data["reqUrl"]="/zh-cn/sports/".$v["pk"]."/Calgary-Flames-vs-Anaheim-Ducks";
        elseif($status == 2)
            $data["reqUrl"]="/zh-cn/sports/".$v["pk"]."/in-play/Boston-Celtics-vs-Detroit-Pistons";
        $data["oIsInplayAll"]=false;
        $data["oIsFirstLoad"]= false;
        $data["oSortBy"]=1;
        $data["oOddsType"]=0;
        $data["oPageNo"]=0;
        $result = json_encode(array());
        if($status == 1)
            $result = $this->curl($url,$data,'');
        elseif($status == 2)
            $result = $this->curl($url,$data,'');
    
        $odds_list = json_decode($result,true);
        //print_r($odds_list["mbd"]["d"]["c"]);
        if(isset($odds_list["mbd"])){
             foreach ($odds_list["mbd"]["d"]["c"][0]["e"][0]["o"] as $o){//默认玩法
                 $rules_name = str_replace($team_home, "#team_home_name#",str_replace(" ", "", $o["n"]));
                 $rules_name = str_replace($team_guest, "#team_guest_name#",$rules_name);
                 //判断是哪种玩法
                 $rules_number = $this->checkRules($rules_name);
                 $rules_array = $this->setRules($rules_name,$rules_number,$team_home,$team_guest);
                 $rules_info = $rules_array["rules_info"];
                 $play_rules_explain = $rules_array["play_rules_explain"];
            
                 $this->rulesDetail($play_id, $rules_info, $play_rules_explain);
                 $this->setOdds($o, $rules_number, $rules_info,$team_home,$team_guest,$play_id);
             }
             //自定义玩法
             $cel = $odds_list["mbd"]["d"]["c"][0]["e"][0]["cel"];
             //0为主队，1为客队
             if(isset($cel[0])){
                 foreach ($cel[0]["o"] as $cel_home){
                     $cel_rules_name = str_replace($team_home, "#team_home_name#", str_replace(" ", "", $cel_home["n"]));
                     $cel_rules_name = str_replace($team_guest, "#team_guest_name#", $cel_rules_name);
                     $cel_rules_number = $this->checkRules($cel_rules_name);
                     $cel_rules_array = $this->setRules($cel_rules_name,$cel_rules_number,$team_home,$team_guest);
                     $cel_rules_info = $cel_rules_array["rules_info"];
                     $cel_play_rules_explain = $cel_rules_array["play_rules_explain"];
                
                     $this->rulesDetail($play_id, $cel_rules_info, $cel_play_rules_explain);
                     $this->setOdds($cel_home, $cel_rules_number, $cel_rules_info,$team_home,$team_guest,$play_id);
            
                 }
             }
        
             if(isset($cel[1])){//客队
                 foreach ($cel[1]["o"] as $cel_guest){
                     $cel_rules_name = str_replace($team_guest, "#team_guest_name#", str_replace(" ", "", $cel_guest["n"]));
                     $cel_rules_name = str_replace($team_home, "#team_home_name#", $cel_rules_name);
                     $cel_rules_number = $this->checkRules($cel_rules_name);
                     $cel_rules_array = $this->setRules($cel_rules_name,$cel_rules_number,$team_home,$team_guest);
                     $cel_rules_info = $cel_rules_array["rules_info"];
                     $cel_play_rules_explain = $cel_rules_array["play_rules_explain"];
                
                     $this->rulesDetail($play_id, $cel_rules_info, $cel_play_rules_explain);
                     $this->setOdds($cel_home, $cel_rules_number, $cel_rules_info,$team_home,$team_guest,$play_id);
            
                 }
             }
        
             //自定义玩法
             //print_r($odds_list["mbd"]["d"]["c"][0]["e"][0]["n-o"]);
             foreach ($odds_list["mbd"]["d"]["c"][0]["e"][0]["n-o"] as $no){
                 $no_rules_name = $no["n"];
                 //判断是哪种玩法
                 if(strpos($no["mn"], "Home") !== false ){
                    $no_rules_name = str_replace($team_home, "#team_home_name#", str_replace(" ", "", $no_rules_name));
                 }elseif ( strpos($no["mn"], "Away") !== false ){
                    $no_rules_name = str_replace($team_guest, "#team_guest_name#", str_replace(" ", "", $no_rules_name));
                 }
                 $no_rules_number = $this->checkRules($no_rules_name);
                 $rules_explain = array();
                 if($no_rules_number == RULES_PUCK_OTHER){
                     foreach ($no["o"] as $other){
                        $rules_explain[] = $other[0];
                     }
                 }
                 $no_rules_array = $this->setRules($no_rules_name,$no_rules_number,$team_home,$team_guest,$rules_explain);
                 $no_rules_info = $no_rules_array["rules_info"];
                 $no_play_rules_explain = $no_rules_array["play_rules_explain"];
            
                 $this->rulesDetail($play_id, $no_rules_info, $no_play_rules_explain);
                 $this->setOdds($no, $no_rules_number, $no_rules_info,$team_home,$team_guest,$play_id);
             }
        }
    }
    
    //数据
    public function setOdds($o,$rules_number,$rules_info,$team_home,$team_guest,$play_id){
        if($rules_number == RULES_PUCK_LET || $rules_number == RULES_PUCK_LET_OTHER){//让球
            $num = count($o["v"])/8;
            for ($i=0;$i<$num;$i++){
                $ah = array();
                $home_odds = isset($o["v"][$i*8+5])?$o["v"][$i*8+5]:0;
                $guest_odds = isset($o["v"][$i*8+7])?$o["v"][$i*8+7]:0;
                $ah[] = array("name"=>$team_home,"odds"=>$home_odds,"handicap"=>isset($o["v"][$i*8+1])?str_replace("+","" , $o["v"][$i*8+1]):0);
                $ah[] = array("name"=>$team_guest,"odds"=>$guest_odds,"handicap"=>isset($o["v"][$i*8+3])?str_replace("+","" , $o["v"][$i*8+3]):0);
                if($home_odds >0 && $guest_odds >0){
                    //赔率入库
                    $this->oddsData($ah, $play_id, $rules_info["id"],$i+1);
                }
            }
        }elseif ($rules_number == RULES_PUCK_OU || $rules_number == RULES_PUCK_OU_OTHER){//大小
            $num = count($o["v"])/8;
            for ($i=0;$i<$num;$i++){
                $ou = array();
                $home_odds = isset($o["v"][$i*8+5])?$o["v"][$i*8+5]:0;
                $guest_odds = isset($o["v"][$i*8+7])?$o["v"][$i*8+7]:0;
                $ou[] = array("name"=>$team_home,"odds"=>$home_odds,"over"=>isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0,"under"=>isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0);
                $ou[] = array("name"=>$team_guest,"odds"=>$guest_odds,"over"=>isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0,"under"=>isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0);
                if($home_odds >0 && $guest_odds >0){
                    //赔率入库
                    $this->oddsData($ou, $play_id, $rules_info["id"],$i+1);
                }
            }
    
        }elseif ($rules_number == RULES_PUCK_EUROPE){
            $num = count($o["v"])/4;
            for ($i=0;$i<$num;$i++){
                $eu = array();
                $home_odds = isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0;
                $guest_odds = isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0;
                $eu[] = array("name"=>$team_home,"odds"=>$home_odds);
                $eu[] = array("name"=>$team_guest,"odds"=>$guest_odds);
                if($home_odds >0 && $guest_odds >0){
                    //赔率入库
                    $this->oddsData($eu, $play_id, $rules_info["id"],$i+1);
                }
            }
        }elseif ($rules_number == RULES_PUCK_DOUBLE){
            $num = count($o["v"])/4;
            for ($i=0;$i<$num;$i++){
                $sd = array();
                $home_odds = isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0;
                $guest_odds = isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0;
                $sd[] = array("name"=>"单","odds"=>$home_odds);
                $sd[] = array("name"=>"双","odds"=>$guest_odds);
                if($home_odds >0 && $guest_odds >0){
                    //赔率入库
                    $this->oddsData($sd, $play_id, $rules_info["id"],$i+1);
                }
            }
        }else{
            $other = array();
            $ds = explode("单/双", $rules_info["name"]);
            $dy = explode("独赢", $rules_info["name"]);
            if(count($ds) >1){
                $num = count($o["v"])/4;
                for ($i=0;$i<$num;$i++){
                    $sd = array();
                    $home_odds = isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0;
                    $guest_odds = isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0;
                    $sd[] = array("name"=>"单","odds"=>$home_odds);
                    $sd[] = array("name"=>"双","odds"=>$guest_odds);
                    if($home_odds >0 && $guest_odds >0){
                        //赔率入库
                        $this->oddsData($sd, $play_id, $rules_info["id"],$i+1);
                    }
                }
            }elseif(count($dy) >1){
                $num = count($o["v"])/4;
                for ($i=0;$i<$num;$i++){
                    $eu = array();
                    $home_odds = isset($o["v"][$i*8+1])?$o["v"][$i*8+1]:0;
                    $guest_odds = isset($o["v"][$i*8+3])?$o["v"][$i*8+3]:0;
                    $eu[] = array("name"=>$team_home,"odds"=>$home_odds);
                    $eu[] = array("name"=>$team_guest,"odds"=>$guest_odds);
                    if($home_odds >0 && $guest_odds >0){
                        //赔率入库
                        $this->oddsData($eu, $play_id, $rules_info["id"],$i+1);
                    }
                }
            }else{
                foreach ($o["o"] as $ot){
                    $other[] = array("name"=>$ot[0],"odds"=>$ot[2]);
                }
                $this->oddsData($other, $play_id, $rules_info["id"],1);
            }
        }
    }
    
    //判断玩法
    public function checkRules($str){
        $rq = explode("让球", $str);
        $dx = explode("大/小", $str);
        
        if(count($rq)>1){
            if($str == '让球'){
                return RULES_PUCK_LET;
            }else{
                return RULES_PUCK_LET_OTHER;
            }
        }elseif(count($dx)>1){
            if($str == '进球:大/小'){
                return RULES_PUCK_OU;
            }else{
                return RULES_PUCK_OU_OTHER;
            }
        }elseif ($str == '进球:单/双'){
            return RULES_PUCK_DOUBLE;
        }elseif($str == '独赢盘'){
            return RULES_PUCK_EUROPE;
        }else{
            return RULES_PUCK_OTHER;
        }
        
    }
    
    //入库玩法
    public function setRules($rules_name,$rules_number,$team_home,$team_guest,$rules_explain=array()){
        //玩法信息
        $rules_info = \think\Db::name('rules')->where(array("name"=>$rules_name,"game_type"=>$this->gameType))->find();
        $play_rules_explain = array();
        if($rules_number == RULES_PUCK_LET || $rules_number == RULES_PUCK_LET_OTHER){//让球
            $rules_explain = array("home","guest","handicap");
            $play_rules_explain = array($team_home,$team_guest);
        }elseif ($rules_number == RULES_PUCK_OU || $rules_number == RULES_PUCK_OU_OTHER){//大小
            $rules_explain = array("大","小","over","under");
            $play_rules_explain = $rules_explain;
        }elseif ($rules_number == RULES_PUCK_EUROPE){
            $rules_explain = array("home","guest");
            $play_rules_explain = array($team_home,$team_guest);
        }elseif ($rules_number == RULES_PUCK_DOUBLE){
            $rules_explain = array("单","双");
            $play_rules_explain = $rules_explain;
        }else{//其它
            $ds = explode("单/双", $rules_name);
            $dy = explode("独赢", $rules_name);
            if(count($ds) >1){
                $rules_explain = array("单","双");
                $play_rules_explain = $rules_explain;
            }elseif(count($dy) >1){
                $rules_explain = array("home","guest");
                $play_rules_explain = array($team_home,$team_guest);
            }else{
                $play_rules_explain = $rules_explain;
            }
        }
        if(!$rules_info){//插入新数据
            $rules_data = array("is_default"=>0,"is_edit"=>1,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>$rules_name,"alias"=>$rules_name,"explain"=>json_encode($rules_explain),"type"=>$rules_number,"create_time"=>time(),"update_time"=>time(),"game_id"=>0,"game_type"=>$this->gameType);
            $rules_id = \think\Db::name('rules')->insert($rules_data, false, true , null);
            $rules_info = \think\Db::name('rules')->where("id",$rules_id)->find();
        }
        return array("rules_info"=>$rules_info,"play_rules_explain"=>$play_rules_explain);
    }
    
    public function rulesDetail($play_id,$rules_info,$rules_explain){
        //玩法详情表
        $detail_info = \think\Db::name('play_rules_detail')->where(["game_type"=>$this->gameType,"play_id"=>$play_id,"rules_id"=>$rules_info["id"]])->find();
        $detail = array();
        $md5 = md5($this->gameType.$play_id.$rules_info["id"]."0".json_encode($rules_explain));
        $detail["md5"] = $md5;
        $detail["update_time"] = time();
        $detail["rules_explain"] = json_encode($rules_explain);
    
        if(!$detail_info){
            $detail["game_type"] = $this->gameType;
            $detail["game_id"] = 0;
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
    
    //赔率
    public function oddsData($odds,$play_id,$rules_type,$loop=1){
        $odds_info = \think\Db::name('odds')->where(array("game_type"=>$this->gameType,"play_id"=>$play_id,"odds_company_id"=>8,"loop"=>$loop,"rules_type"=>$rules_type))->find();
        if($odds_info){
            //重新组装JSON
            $new_data = array();
            $odds_data = json_decode($odds_info["odds"],true);
            $new_data["init"] = $odds_data["init"];
            $new_data["time"] = $odds;
            //echo md5(json_encode($new_data))."   ".$odds_info["md5"]."\n";
            if(md5(json_encode($new_data)) != $odds_info["md5"]){//赔率发生变化
                $this->updateOdds($new_data,$odds_info["id"]);
                $this->insertOddsDetail($odds, $odds_info["id"]);
            }
        }else{//新增
            $new_data = array();
            $new_data["init"] = $odds;
            $new_data["time"] = $odds;
            $odds_id = $this->insertOdds($new_data, $rules_type, $play_id, $loop);
            $this->insertOddsDetail($odds, $odds_id);
        }
    }
    
    //插入odds表
    public function insertOdds($data,$rules,$play_id,$loop){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["play_id"] = $play_id;
        $odds_json["game_type"] = $this->gameType;
        $odds_json["odds_company_id"] = 8;
        $odds_json["loop"] = $loop;
    
        $odds_json["rules_type"] = $rules;
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
    
    public function updateOdds($data,$id){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["odds"] = json_encode($data);
        $odds_json["update_time"] = time();
    
        \think\Db::name('odds')->where("id",$id)->update($odds_json);
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
    
    
}    