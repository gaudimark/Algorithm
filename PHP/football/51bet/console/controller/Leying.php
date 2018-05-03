<?php
/**
 * 采集乐盈的比赛及赔率
 * */
namespace app\console\controller;

use app\console\logic\Basic;
class Leying extends \app\console\logic\Basic{
    public $game_type = GAME_TYPE_WCG;
    public $company_id = 25;
    private $isProxy = 1;//是否开启代理0否，1是
    private $header = array("User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36","Accept-Language:zh-CN,zh");
    public $game_str = array("王者荣耀"=>"kg","League Of Legends"=>"lol","Dota2"=>"dota2","HearthStone"=>"hearthstone","Counter Strike:Global Offensive"=>"csgo");
    
    //采集比赛，赔率
    public function index(){
        set_time_limit(540);
        
        $url = "http://dj.13322.com/api/match/guessing";
        $game_list = \think\Db::name('game')->where(["status"=>1])->select();
        foreach ($game_list as $g){
            $game_name = trim($g["alias"]);
            //echo $game_name."\n";
            if(isset($this->game_str[$game_name])){
                $gameCode = $this->game_str[$game_name];
                echo $gameCode."\n";
                $content = json_decode($this->curl($url , array("pageIndex"=>1,"gameCode"=>$gameCode,"bGameIds:"=>'',"pageSize"=>100) , '' , '' , $this->header,$this->isProxy),true);
                if(isset($content["data"])){
                    //比赛列表
                    $matchList = $content["data"]["matchList"];
                    foreach ($matchList as $m){
                        $bName = trim($m["bMatchName"]);
                        echo $bName."   ";
                        $match_name = $bName;
                        $match_info = \think\Db::name('match')->where(["name|alias"=>$match_name,"game_type"=>$this->game_type])->find();
                        if(!$match_info){//如果不存在对于赛事，新增
                            $match_data = array();
                            $match_data["country_id"] = 1;
                            $match_data["game_type"] = $this->game_type;
                            $match_data["name"] = $match_name;
                            $match_data["md5_match"] = "";
                            $match_data["game_id"] = $g["id"];
                            $match_id = \think\Db::name('match')->insert($match_data,false,true);
                            model("admin/match")->upCacheOnly($match_id);
                            $match_info = getMatch($match_id);
                        }
                        $match_id = $match_info["id"];
                        $team_home = trim($m["teamAName"]);
                        $team_guest = trim($m["teamBName"]);
                        $play_time = $m["matchDate"]/1000;
                        
                        //选手信息
                        $team_home_info = \think\Db::name('team')->where(["name|alias"=>$team_home,"game_type"=>$this->game_type])->find();
                        $team_guest_info = \think\Db::name('team')->where(["name|alias"=>$team_guest,"game_type"=>$this->game_type])->find();
                        if(!$team_home_info){
                            $logo1 = "";
                            if($m["teamALogo"] != ""){
                                $logo1 = $this->saveImg($m["teamALogo"],$this->isProxy);
                            }
                            $htid = \think\Db::name('team')->insert(array("country_id"=>1,"logo"=>$logo1,"name"=>$team_home,"game_type"=>$this->game_type,"create_time"=>time(),"md5"=>md5(str_replace(" ","",strtolower($team_home)))),false,true);
                            model("admin/team")->upCacheOnly($htid);
                            $team_home_info = getTeam($htid);
                        }
                        
                        if(!$team_guest_info){
                            $logo2 = "";
                            if($m["teamBLogo"] != ""){
                                $logo2 = $this->saveImg($m["teamBLogo"],$this->isProxy);
                            }
                            $gtid = \think\Db::name('team')->insert(array("country_id"=>1,"logo"=>$logo2,"name"=>$team_guest,"game_type"=>$this->game_type,"create_time"=>time(),"md5"=>md5(str_replace(" ","",strtolower($team_guest)))),false,true);
                            model("admin/team")->upCacheOnly($gtid);
                            $team_guest_info = getTeam($gtid);
                        }
                        
                        //matchDetailID
                        $matchDetailID = 0;
                        $firestMatchDetailID = 0;
                        foreach ($content["data"]["matchDetailList"] as $mdl){
                            if(($mdl["bMatchId"] == $m["bMatchId"]) && $mdl["bo"] == 0){//bo值为0(总局),1为第一局，2为第二局
                                $matchDetailID = $mdl["matchDetailId"];
                            }
                        }
                        
                        foreach ($content["data"]["matchDetailList"] as $fmdl){
                            if(($fmdl["bMatchId"] == $m["bMatchId"]) && $fmdl["bo"] == 1){//bo值为0(总局),1为第一局，2为第二局
                                $firestMatchDetailID = $fmdl["matchDetailId"];
                            }
                        }
                        $md5_play = md5("leyin".$m["bMatchId"]);
                        $match_status = PLAT_STATUS_NOT_START;
                        if($m["matchStatus"] == 2){
                            $match_status = PLAT_STATUS_START;
                        }elseif($m["matchStatus"] == 3){
                            $match_status = PLAT_STATUS_END;
                        }
                        if($match_status == PLAT_STATUS_NOT_START && time() >= $play_time){
                            $match_status = PLAT_STATUS_START;
                        }
                        
                        //查询是否有对应比赛
                        $play_list = $this->getPlayInfo($match_id, $g["id"], $play_time, $team_home_info["id"], $team_guest_info["id"], $this->game_type, $md5_play);
                        $play_info = $play_list["play_info"];
                        $is_change = $play_list["is_change"];//188跟乐盈数据会出现主客队相反，暂时用不上，但不能注释
                        if(!$play_info){//新建比赛
                            $play_id = 0;
                            $play_data = array();
                            $play_data["game_id"] = $g["id"];
                            $play_data["md5_play"] = $md5_play;
                            $play_data["game_type"] = $this->game_type;
                            $play_data["match_id"] = $match_id;
                            $play_data["play_time"] = $play_time;
                            $play_data["team_home_id"] = $team_home_info["id"];
                            $play_data["team_home_name"] = $team_home_info["name"];
                            $play_data["team_guest_id"] = $team_guest_info["id"];
                            $play_data["team_guest_name"] = $team_guest_info["name"];
                            if( $match_status != PLAT_STATUS_START){//比赛开始后不更新比分
                                $play_data["team_home_score"] = isset($m["scoreA"])?$m["scoreA"]:0;
                                $play_data["team_guest_score"] = isset($m["scoreB"])?$m["scoreB"]:0;
                            }
                            $play_data["status"] = $match_status;
                            $play_data["create_time"] = time();
                            $play_data["update_time"] = time();
                            $play_data["has_odds"] = 1;
                            $play_data["bo"] = $m["bo"];
    
                            $play_id = \think\Db::name('play')->insert($play_data,false,true,null);
                            $this->insertPlayTeam($play_id,$team_home_info["id"],1);
                            $this->insertPlayTeam($play_id,$team_guest_info["id"],0);
                            //写入缓存
                            $this->checkArenaStatusByPlayStatus($play_id,$match_status);
    
                             //查询当前比赛的所有odds信息
                             $dj_odds_list = \think\Db::name('odds')->where(["play_id"=>$play_id,"odds_company_id"=>$this->company_id,"user_id"=>0])->select();
                             $dj_odds_info = array();
                             foreach ($dj_odds_list as $ol){
                                $dj_odds_info[$ol["rules_id"]][$ol["loop"]] = $ol;
                             }
                              
                             //独赢
                             $ml = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $matchDetailID) && $os["competitionTypeCode"] == "SW"){
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"];
                                         //$ml[] = $data;
                                         $mlnum = intval($os["brotherId"])+intval($os["competitionId"]);
                                         $ml["ml".$mlnum][] = $data;
                                     }
                                 }
                             }
                             if($ml){
                                 $new_ml = array();
                                 foreach ($ml as $mll){
                                     for($s=0;$s < (count($mll)/2);$s++){
                                         if(strcasecmp($mll[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                             $ml_replace = $mll[$s*2];
                                             $mll[$s*2] = $mll[$s*2+1];
                                             $mll[$s*2+1] = $ml_replace;
                                         }
                                         $new_ml[] = $mll;
                                     }
                                 }
                                $this->ml($g, $new_ml, $play_id, $team_home, $team_guest,$dj_odds_info);
                             }
                             //让分
                             $asian = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $matchDetailID) && $os["competitionTypeCode"] == "PS"){
                                         //$asian[] = $os;
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"]-1;
                                         $data["handicap"] = under($os["headers"],false,false);
                                         $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                         if($os["odds"] > 1){
                                            $asian["h".$num][] = $data;
                                         }else{
                                            unset($asian["h".$num]);
                                         }
                                     }
                                 }
                             }
                             if($asian){
                                 $new_asian = array();
                                 foreach ($asian as $as){
                                    for($s=0;$s < (count($as)/2);$s++){
                                        if(strcasecmp($as[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                            $as_replace = $as[$s*2];
                                            $as[$s*2] = $as[$s*2+1];
                                            $as[$s*2+1] = $as_replace;
                                        }
                                        $new_asian[] = $as;
                                    }
                                 }
                             }
                             if($asian){
                                $this->rq($g, $new_asian, $play_id, $team_home, $team_guest,$dj_odds_info); 
                             }   
                             //一血
                             $fblood = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $firestMatchDetailID) && $os["competitionTypeCode"] == "FB"){
                                        
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"];
                                         $fbnum = intval($os["brotherId"])+intval($os["competitionId"]);
                                         $fblood["fb".$fbnum][] = $data;
                                     }
                                 }
                             }
                             if($fblood){
                                 $new_fblood = array();
                                 foreach ($fblood as $fb){
                                     for($s=0;$s < (count($fb)/2);$s++){
                                         if(strcasecmp($fb[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                             $fb_replace = $fb[$s*2];
                                             $fb[$s*2] = $fb[$s*2+1];
                                             $fb[$s*2+1] = $fb_replace;
                                         }
                                         $new_fblood[] = $fb;
                                     }
                                 }
                                 $this->fblood($g, $new_fblood, $play_id, $team_home, $team_guest,$dj_odds_info);
                             }
                             //其它玩法
                             $bo = $m["bo"];//回合数
                             for($i=1;$i<=$bo;$i++){
                                 $win = array();
                                 $fbo = array();
                                 $fto = array();
                                 $sdo = array();
                                 foreach ($content["data"]["matchDetailList"] as $fmdl){
                                     if(($fmdl["bMatchId"] == $m["bMatchId"]) && $fmdl["bo"] == $i){
                                         if(isset($content["data"]["competitionList"])){
                                             foreach ($content["data"]["competitionList"] as $os){
                                                 //第X局胜
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "Win"){
                                         
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$win[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $win["win".$num][] = $data;
                                                 }
                                                 //第X局一血
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FB"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$fbo[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $fbo["fbo".$num][] = $data;
                                                 }
                                                 //第X局一塔
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FT"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$fto[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $fto["fto".$num][] = $data;
                                                 }
                                                 //第X局第一只小龙
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FD"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$sdo[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $sdo["sdo".$num][] = $data;
                                                 }
                                             }
                                         }
                                     }
                                 }
                                 if($win){
                                     $new_win = array();
                                     foreach ($win as $wn){
                                         for($s=0;$s < (count($wn)/2);$s++){
                                             if(strcasecmp($wn[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                                 $wn_replace = $wn[$s*2];
                                                 $wn[$s*2] = $wn[$s*2+1];
                                                 $wn[$s*2+1] = $wn_replace;
                                             }
                                             $new_win[] = $wn;
                                         }
                                     }
                                     $this->other($g, $new_win, $play_id, $team_home, $team_guest,$dj_odds_info,"win",$i);
                                 }
                                 if($fbo){
                                     $new_fbo = array();
                                     foreach ($fbo as $fo){
                                         for($s=0;$s < (count($fo)/2);$s++){
                                             if(strcasecmp($fo[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                                 $fo_replace = $fo[$s*2];
                                                 $fo[$s*2] = $fo[$s*2+1];
                                                 $fo[$s*2+1] = $fo_replace;
                                             }
                                             $new_fbo[] = $fo;
                                         }
                                     }
                                     $this->other($g, $new_fbo, $play_id, $team_home, $team_guest,$dj_odds_info,"fb",$i);
                                 }
                                 if($fto){
                                     $new_fto = array();
                                     foreach ($fto as $to){
                                         for($s=0;$s < (count($to)/2);$s++){
                                             if(strcasecmp($to[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                                 $to_replace = $to[$s*2];
                                                 $to[$s*2] = $to[$s*2+1];
                                                 $to[$s*2+1] = $to_replace;
                                             }
                                             $new_fto[] = $to;
                                         }
                                     }
                                     $this->other($g, $new_fto, $play_id, $team_home, $team_guest,$dj_odds_info,"ft",$i);
                                 }
                                 if($sdo){
                                     $new_sdo = array();
                                     foreach ($sdo as $so){
                                         for($s=0;$s < (count($so)/2);$s++){
                                             if(strcasecmp($so[$s*2]["name"], $play_data["team_guest_name"]) ==0){
                                                 $so_replace = $so[$s*2];
                                                 $so[$s*2] = $so[$s*2+1];
                                                 $so[$s*2+1] = $so_replace;
                                             }
                                             $new_sdo[] = $so;
                                         }
                                     }
                                     $this->other($g, $new_sdo, $play_id, $team_home, $team_guest,$dj_odds_info,"sd",$i);
                                 }
                             }
                             
                             
                             
                         }else{//修改比赛
                             if($play_info["status"] == PLAT_STATUS_END || $play_info["status"] == PLAT_STATUS_STATEMENT || $play_info["status"] == PLAT_STATUS_STATEMENT_BEGIN){
                                 continue;
                             }
                             
                             //查询当前比赛的所有odds信息
                             $dj_odds_list = \think\Db::name('odds')->where(["play_id"=>$play_info["id"],"odds_company_id"=>$this->company_id,"user_id"=>0])->select();
                             $dj_odds_info = array();
                             foreach ($dj_odds_list as $ol){
                                 $dj_odds_info[$ol["rules_id"]][$ol["loop"]] = $ol;
                             }
                             echo $play_info["id"]."\n";
                             //独赢
                             $ml = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $matchDetailID) && $os["competitionTypeCode"] == "SW"){
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"];
                                         $mlnum = intval($os["brotherId"])+intval($os["competitionId"]);
                                         $ml["ml".$mlnum][] = $data;
                                     }
                                 }
                             }
                             if($ml){
                                 $new_ml = array();
                                 foreach ($ml as $mll){
                                     for($s=0;$s < (count($mll)/2);$s++){
                                         if(strcasecmp($mll[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                             $ml_replace = $mll[$s*2];
                                             $mll[$s*2] = $mll[$s*2+1];
                                             $mll[$s*2+1] = $ml_replace;
                                         }
                                         $new_ml[] = $mll;
                                     }
                                 }
                                $this->ml($g, $new_ml, $play_info["id"], $team_home, $team_guest,$dj_odds_info);
                             } 
                             //让分
                             $asian = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $matchDetailID) && $os["competitionTypeCode"] == "PS"){
                                         //$asian[] = $os;
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"]-1;
                                         $data["handicap"] = under($os["headers"],false,false);
                                         $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                         if($os["odds"] > 1){
                                            $asian["h".$num][] = $data;
                                         }else{
                                             unset($asian["h".$num]);
                                         }
                                     }
                                 }
                             }
                             if($asian){
                                 $new_asian = array();
                                 foreach ($asian as $as){
                                    for($s=0;$s < (count($as)/2);$s++){
                                        if(strcasecmp($as[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                            $as_replace = $as[$s*2];
                                            $as[$s*2] = $as[$s*2+1];
                                            $as[$s*2+1] = $as_replace;
                                        }
                                        $new_asian[] = $as;
                                    }
                                 }
                                $this->rq($g, $new_asian, $play_info["id"], $team_home, $team_guest,$dj_odds_info);
                             }
                             //一血
                             $fblood = array();
                             if(isset($content["data"]["competitionList"])){
                                 foreach ($content["data"]["competitionList"] as $os){
                                     if(($os["matchDetailId"] == $firestMatchDetailID) && $os["competitionTypeCode"] == "FB"){
                             
                                         $data = array();
                                         $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                         $data["odds"] = $os["odds"];
                                         $fbnum = intval($os["brotherId"])+intval($os["competitionId"]);
                                         $fblood["fb".$fbnum][] = $data;
                                     }
                                 }
                             }
                             if($fblood){
                                 $new_fblood = array();
                                 foreach ($fblood as $fb){
                                     for($s=0;$s < (count($fb)/2);$s++){
                                         if(strcasecmp($fb[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                             $fb_replace = $fb[$s*2];
                                             $fb[$s*2] = $fb[$s*2+1];
                                             $fb[$s*2+1] = $fb_replace;
                                         }
                                         $new_fblood[] = $fb;
                                     }
                                 }
                                 $this->fblood($g, $new_fblood,$play_info["id"], $team_home, $team_guest,$dj_odds_info);
                             }
                             //其它玩法
                             $bo = $m["bo"];//回合数
                             for($i=1;$i<=$bo;$i++){
                                 $win = array();
                                 $fbo = array();
                                 $fto = array();
                                 $sdo = array();
                                 foreach ($content["data"]["matchDetailList"] as $fmdl){
                                     if(($fmdl["bMatchId"] == $m["bMatchId"]) && $fmdl["bo"] == $i){
                                         if(isset($content["data"]["competitionList"])){
                                             foreach ($content["data"]["competitionList"] as $os){
                                                 //第X局胜
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "Win"){
                                                      
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$win[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $win["win".$num][] = $data;
                                                 }
                                                 //第X局一血
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FB"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$fbo[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $fbo["fbo".$num][] = $data;
                                                 }
                                                 //第X局一塔
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FT"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$fto[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $fto["fto".$num][] = $data;
                                                 }
                                                 //第X局第一只小龙
                                                 if(($os["matchDetailId"] == $fmdl["matchDetailId"]) && $os["competitionTypeCode"] == "FD"){
                                                     $data = array();
                                                     $data["name"] = isset($this->team_match[$os["teamName"]])?$this->team_match[$os["teamName"]]:$os["teamName"];
                                                     $data["odds"] = $os["odds"];
                                                     //$sdo[] = $data;
                                                     $num = intval($os["brotherId"])+intval($os["competitionId"]);
                                                     $sdo["sdo".$num][] = $data;
                                                 }
                                             }
                                         }
                                     }
                                 }
                                 if($win){
                                     $new_win = array();
                                     foreach ($win as $wn){
                                         for($s=0;$s < (count($wn)/2);$s++){
                                             if(strcasecmp($wn[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                                 $wn_replace = $wn[$s*2];
                                                 $wn[$s*2] = $wn[$s*2+1];
                                                 $wn[$s*2+1] = $wn_replace;
                                             }
                                             $new_win[] = $wn;
                                         }
                                     }
                                     $this->other($g, $new_win, $play_info["id"], $team_home, $team_guest,$dj_odds_info,"win",$i);
                                 }
                                 if($fbo){
                                     $new_fbo = array();
                                     foreach ($fbo as $fo){
                                         for($s=0;$s < (count($fo)/2);$s++){
                                             if(strcasecmp($fo[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                                 $fo_replace = $fo[$s*2];
                                                 $fo[$s*2] = $fo[$s*2+1];
                                                 $fo[$s*2+1] = $fo_replace;
                                             }
                                             $new_fbo[] = $fo;
                                         }
                                     }
                                     $this->other($g, $new_fbo, $play_info["id"], $team_home, $team_guest,$dj_odds_info,"fb",$i);
                                 }
                                 if($fto){
                                     $new_fto = array();
                                     foreach ($fto as $to){
                                         for($s=0;$s < (count($to)/2);$s++){
                                             if(strcasecmp($to[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                                 $to_replace = $to[$s*2];
                                                 $to[$s*2] = $to[$s*2+1];
                                                 $to[$s*2+1] = $to_replace;
                                             }
                                             $new_fto[] = $to;
                                         }
                                     }
                                     $this->other($g, $new_fto, $play_info["id"], $team_home, $team_guest,$dj_odds_info,"ft",$i);
                                 }
                                 if($sdo){
                                     $new_sdo = array();
                                     foreach ($sdo as $so){
                                         for($s=0;$s < (count($so)/2);$s++){
                                             if(strcasecmp($so[$s*2]["name"], $play_info["team_guest_name"]) ==0){
                                                 $so_replace = $so[$s*2];
                                                 $so[$s*2] = $so[$s*2+1];
                                                 $so[$s*2+1] = $so_replace;
                                             }
                                             $new_sdo[] = $so;
                                         }
                                     }
                                     $this->other($g, $new_sdo, $play_info["id"], $team_home, $team_guest,$dj_odds_info,"sd",$i);
                                 } 
                             }
                             //更新play表
                             $new_play_data = array();
                             if($match_status != PLAT_STATUS_START ){//比赛开始后不更新比分
                                 if($is_change){
                                     $new_play_data["team_home_score"] = isset($m["scoreB"])?$m["scoreB"]:0;
                                     $new_play_data["team_guest_score"] = isset($m["scoreA"])?$m["scoreA"]:0;
                                 }else{
                                     $new_play_data["team_home_score"] = isset($m["scoreA"])?$m["scoreA"]:0;
                                     $new_play_data["team_guest_score"] = isset($m["scoreB"])?$m["scoreB"]:0;
                                 }
                             }else{
                                 if($is_change){
                                     $new_play_data["team_home_score"] = 0;
                                     $new_play_data["team_guest_score"] = 0;
                                 }else{
                                     $new_play_data["team_home_score"] = 0;
                                     $new_play_data["team_guest_score"] = 0;
                                 }
                             }
                             $new_play_data["status"] = $match_status;
                             if($play_info["md5_play"] == $md5_play){
                                 $new_play_data["team_home_id"] = $team_home_info["id"];
                                 $new_play_data["team_home_name"] = $team_home_info["name"];
                                 $new_play_data["team_guest_id"] = $team_guest_info["id"];
                                 $new_play_data["team_guest_name"] = $team_guest_info["name"];
                                 $new_play_data["play_time"] = $play_time;
                             }
                             $new_play_data["update_time"] = time();
                             $new_play_data["bo"] = $m["bo"];
                             
                             \think\Db::name('play')->where("id",$play_info["id"])->update($new_play_data);
                             //写入缓存
                             $this->checkArenaStatusByPlayStatus($play_info["id"], $match_status);
                             if($is_change){
                                $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],1,$new_play_data["team_home_score"]);
                                $this->insertPlayTeam($play_info["id"],$team_home_info["id"],0,$new_play_data["team_guest_score"]);
                             }else{
                                 $this->insertPlayTeam($play_info["id"],$team_home_info["id"],1,$new_play_data["team_home_score"]);
                                 $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],0,$new_play_data["team_guest_score"]);
                             }
                             //echo $play_info["id"];
                             
                         } 
                         //exit;
                    }
                }
            }
            //exit;
        }
    
    
    
    }
    
    //结束更新比分
    public function end(){
        set_time_limit(540);
        $url = "http://dj.13322.com/api/match/guess-end";
        $game_list = \think\Db::name('game')->where(["status"=>1])->select();
    
        foreach ($game_list as $g){
            $game_name = trim($g["alias"]);
            echo $game_name."\n";
            if(isset($this->game_str[$game_name])){
                $gameCode = $this->game_str[$game_name];
                for($i=1;$i<6;$i++){
                    $content = json_decode($this->curl($url,array("gameCode"=>$gameCode,"bGameIds:"=>'',"pageIndex"=>$i),'','',$this->header,$this->isProxy),true);
                    if(isset($content["data"])){
                        //比赛列表
                        $matchList = $content["data"]["matchList"];
                        $play_list = array();
                        foreach ($matchList as $m){
                            $bName = trim($m["bMatchName"]);
                            $match_name = $bName;
                            $match_info = \think\Db::name('match')->where(["name|alias"=>$match_name,"game_type"=>$this->game_type])->find();
                            if(!$match_info){
                                continue;
                            }
                            $match_id = $match_info["id"];
                            $team_home = trim($m["teamAName"]);
                            $team_guest = trim($m["teamBName"]);
                            $play_time = $m["matchDate"]/1000;
            
                            //选手信息
                            $team_home_info = \think\Db::name('team')->where(["name|alias"=>$team_home,"game_type"=>$this->game_type])->find();
                            $team_guest_info = \think\Db::name('team')->where(["name|alias"=>$team_guest,"game_type"=>$this->game_type])->find();
                            if(!$team_home_info || !$team_guest_info){
                                continue;
                            }
                            $md5_play = md5("leyin".$m["bMatchId"]);
                            $play_list = $this->getPlayInfo($match_id, $g["id"], $play_time, $team_home_info["id"], $team_guest_info["id"], $this->game_type, $md5_play);
                            $play_info = $play_list["play_info"];
                            $is_change = $play_list["is_change"];                            
                            if(!$play_info){
                                continue;
                            }
                            //如果比赛已经结算或者未结算比分一致则不更新
                            if($play_info["status"] == PLAT_STATUS_STATEMENT || $play_info["status"] == PLAT_STATUS_STATEMENT_BEGIN || ($play_info["status"] == PLAT_STATUS_END && $play_info["team_home_score"] == $m["scoreA"] && $play_info["team_guest_score"] == $m["scoreB"])){
                                continue;
                            }
                            
                            //比赛比分
                            if($is_change){
                                $team_home_score = isset($m["scoreB"])?$m["scoreB"]:0;
                                $team_guest_score = isset($m["scoreA"])?$m["scoreA"]:0;
                            }else{
                                $team_home_score = isset($m["scoreA"])?$m["scoreA"]:0;
                                $team_guest_score = isset($m["scoreB"])?$m["scoreB"]:0;
                            }
                            $status = PLAT_STATUS_END;
            
                            //更新比赛及缓存
                            $new_play_data = array();
                            $new_play_data["team_home_score"] = $team_home_score;
                            $new_play_data["team_guest_score"] = $team_guest_score;
                            $new_play_data["status"] = $status;
                            if(!$play_info["end_time"]){
                                $new_play_data["end_time"] = time();
                            }
                            $new_play_data["update_time"] = time();
                            \think\Db::name('play')->where("id",$play_info["id"])->update($new_play_data);
                            
                            $home_score_json = array();
                            $guest_score_json = array();
                            $win = array();
                            $bo = $m["bo"];//回合数
                            $full_list = array();
                            $json_data = array();
                            $home_win = array();
                            $guest_win = array();
                            if(isset($content["data"]["matchDetailList"])){
                                //总局数据
                                $full_list = $this->setScoreJson($content["data"]["matchDetailList"], $m["bMatchId"] , $bo , "0",$is_change,$gameCode);      
                                // 其它玩法
                                for($i=1;$i<=$bo;$i++){
                                    $json_list = $this->setScoreJson($content["data"]["matchDetailList"], $m["bMatchId"] , $bo , $i,$is_change,$gameCode);
                                    if($json_list[0])
                                        $json_data[$i] = $json_list;
                                    foreach ($content["data"]["matchDetailList"] as $mdl){
                                        if($mdl["bo"] == $i && $mdl["bMatchId"] == $m["bMatchId"]){
                                            if($is_change){
                                                if($mdl["winTeamId"] == $mdl["teamAId"]){
                                                    $win[$i] = $team_guest;
                                                    $guest_win[] = $i;
                                                }else{
                                                    $win[$i] = $team_home;
                                                    $home_win[] = $i;
                                                }
                                            }else{
                                                if($mdl["winTeamId"] == $mdl["teamAId"]){
                                                    $win[$i] = $team_home;
                                                    $home_win[] = $i;
                                                }else{
                                                    $win[$i] = $team_guest;
                                                    $guest_win[] = $i;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            //总局数据更新：无人头数则将每局人头数相关
                            $home_header = isset($full_list[0]["header"])?$full_list[0]["header"]:0;
                            $guest_header = isset($full_list[1]["header"])?$full_list[1]["header"]:0;
                            $home_detail = array();
                            $guest_detail = array();
                            $new_home_header = $home_header;
                            $new_guest_header = $guest_header;
                            if($json_data){
                                foreach ($json_data as $k=>$jd){
                                    if($home_header == 0 && $guest_header == 0){
                                        $new_home_header += isset($jd[0]["header"])?$jd[0]["header"]:0;
                                        $new_guest_header += isset($jd[1]["header"])?$jd[1]["header"]:0;
                                    }
                                    $home_detail[$k] = $jd[0];
                                    $guest_detail[$k] = $jd[1];
                                    
                                }
                            }
                            $home_score_json["header"] = $new_home_header;
                            $guest_score_json["header"] = $new_guest_header;
                            if($json_data){
                                $home_score_json["fb"] = $json_data[1][0]["fb"];
                                $guest_score_json["fb"] = $json_data[1][1]["fb"];
                            }
                            $home_score_json["win"] = $home_win;
                            $guest_score_json["win"] = $guest_win;
                            $home_score_json["detail"] = $home_detail;
                            $guest_score_json["detail"] = $guest_detail;
                            //写入缓存
                            echo $play_info["id"]."\n";
                            $this->checkArenaStatusByPlayStatus($play_info["id"],$status);
                            if($is_change){
                                $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],1,$new_play_data["team_home_score"],0,0,0,json_encode($home_score_json,true));
                                $this->insertPlayTeam($play_info["id"],$team_home_info["id"],0,$new_play_data["team_guest_score"],0,0,0,json_encode($guest_score_json));
                            }else{
                                $this->insertPlayTeam($play_info["id"],$team_home_info["id"],1,$new_play_data["team_home_score"],0,0,0,json_encode($home_score_json,true));
                                $this->insertPlayTeam($play_info["id"],$team_guest_info["id"],0,$new_play_data["team_guest_score"],0,0,0,json_encode($guest_score_json));
                            }
                            //更新play_result表，方便自动结算
                            $rules = array();
                            if($team_home_score >0 || $team_guest_score >0){
                                $play_result = \think\Db::name('play_result')->where("play_id",$play_info["id"])->find();
                                $result_data = array();
                                $total_ou = $team_home_score+$team_guest_score;//回合数
                                //一血
                                $result_fb = "";
                                if(isset($home_score_json["fb"])&& isset($guest_score_json["fb"])){
                                    if($home_score_json["fb"] == 1 && $guest_score_json["fb"] == 0){
                                        $result_fb = HOME;
                                    }elseif($home_score_json["fb"] == 0 && $guest_score_json["fb"] == 1){
                                        $result_fb = GUEST;
                                    }
                                }
                                //人头数
                                $result_header = array($new_home_header,$new_guest_header);
                                //其它玩法
                                if($win){
                                    foreach ($win as $k=>$v){
                                        $rules_info = \think\Db::name('rules')->where(array("name"=>"第{$k}局胜","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                        if($rules_info){
                                            $d = 0;
                                            if($v == $team_home){
                                                $d = 0;
                                            }elseif ($v == $team_guest){
                                                $d = 1;
                                            }
                                            $rules[$rules_info["id"]] = $d;
                                        }
                                    }
                                }
                                //第X局一血
                                for ($i=1;$i<=count($home_detail);$i++){
                                    $fbrules_info = \think\Db::name('rules')->where(array("name"=>"第{$i}局一血","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                    if($fbrules_info){
                                        if(isset($home_detail[$i]["fb"]) && isset($guest_detail[$i]["fb"])){
                                            if($home_detail[$i]["fb"] == 0 && $guest_detail[$i]["fb"] == 1){
                                                $rules[$fbrules_info["id"]] = 1;
                                            }elseif($home_detail[$i]["fb"] == 1 && $guest_detail[$i]["fb"] == 0){
                                                $rules[$fbrules_info["id"]] = 0;
                                            }
                                        }
                                    }
                                }
                                //第X局一塔
                                for ($i=1;$i<=count($home_detail);$i++){
                                    $ftrules_info = \think\Db::name('rules')->where(array("name"=>"第{$i}局一塔","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                    if($ftrules_info){
                                        if(isset($home_detail[$i]["ftower"]) && isset($guest_detail[$i]["ftower"])){
                                            if($home_detail[$i]["ftower"] == 0 && $guest_detail[$i]["ftower"] == 1){
                                                $rules[$ftrules_info["id"]] = 1;
                                            }elseif($home_detail[$i]["ftower"] == 1 && $guest_detail[$i]["ftower"] == 0){
                                                $rules[$ftrules_info["id"]] = 0;
                                            }
                                        }
                                    }
                                }
                                //第X局小龙
                                for ($i=1;$i<=count($home_detail);$i++){
                                    $sdrules_info = \think\Db::name('rules')->where(array("name"=>"第{$i}局第一只小龙","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                    if($sdrules_info){
                                        if(isset($home_detail[$i]["smalldra"]) && isset($guest_detail[$i]["smalldra"])){
                                            if($home_detail[$i]["smalldra"] == 0 && $guest_detail[$i]["smalldra"] == 1){
                                                $rules[$sdrules_info["id"]] = 1;
                                            }elseif($home_detail[$i]["smalldra"] == 1 && $guest_detail[$i]["smalldra"] == 0){
                                                $rules[$sdrules_info["id"]] = 0;
                                            }
                                        }
                                    }
                                }
                                //第X局人头数
                                /*for ($i=1;$i<=count($home_detail);$i++){
                                    $hdrules_info = \think\Db::name('rules')->where(array("name"=>"第{$i}局人头数","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                    if($hdrules_info){
                                        if(isset($home_detail[$i]["header"]) && isset($guest_detail[$i]["header"])){
                                           $rules[$hdrules_info["id"]] = $home_detail[$i]["header"]+$guest_detail[$i]["header"];
                                            
                                        }
                                    }
                                }*/
                                //第X局肉山
                                /*for ($i=1;$i<=count($home_detail);$i++){
                                    $rsrules_info = \think\Db::name('rules')->where(array("name"=>"第{$i}局第一座肉山","game_id"=>$g["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
                                    if($rsrules_info){
                                        if(isset($home_detail[$i]["roushan"]) && isset($guest_detail[$i]["roushan"])){
                                            if($home_detail[$i]["roushan"] == 0 && $guest_detail[$i]["roushan"] == 1){
                                                $rules[$rsrules_info["id"]] = 1;
                                            }elseif($home_detail[$i]["roushan"] == 1 && $guest_detail[$i]["roushan"] == 0){
                                                $rules[$rsrules_info["id"]] = 0;
                                            }
                                        }
                                    }
                                }*/
                                
                                
                                $result_data["result"] = json_encode(array("total_ou"=>$total_ou,"fb"=>$result_fb,"header"=>$result_header,"rules"=>$rules));
                                
                                if(!$play_result){
                                    $result_data["play_id"] = $play_info["id"];
                                    \think\Db::name('play_result')->insert($result_data);
                                }else{
                                    \think\Db::name('play_result')->where("id",$play_result["id"])->update($result_data);
                                }
                            }
                        }
                    }
                }
            }
        }
    
    }
    
    //根据比赛的详细数据得出后台显示赛果
    public function setScoreJson($list , $matchId , $bo ,$now_bo = "0",$is_change=0,$gameCode){
        $home_score_json = array();
        $guest_score_json = array();
        if($list){
            foreach ($list as $mdl){
                $homeHeader = 0; $guestHeader = 0;
                $homeHitNum = 0; $guestHitNum = 0;
                $homeAssNum = 0; $guestAssNum = 0;
                $homeFtower = 0; $guestFtower = 0;
                $homeHittower = 0; $guestHittower = 0;
                $homeSmallDra = 0; $guestSmallDra = 0;
                $homeBigDra = 0; $guestBigDra = 0;
                $homeRoushan = 0; $guestRoushan = 0;
                $homeFsd = 0; $guestFsd = 0;
                $homeFrs = 0; $guestFrs = 0;
                $homeFb = 0; $guestFb = 0;
                
                if($mdl["bo"] == $now_bo && $mdl["bMatchId"] == $matchId){
                    $homeHeader = isset($mdl["teamAHeads"])?$mdl["teamAHeads"]:0;
                    $guestHeader = isset($mdl["teamBHeads"])?$mdl["teamBHeads"]:0;//人头数
                    $homeHitNum = isset($mdl["teamALastHitNum"])?$mdl["teamALastHitNum"]:0;
                    $guestHitNum = isset($mdl["teamBLastHitNum"])?$mdl["teamBLastHitNum"]:0;//补刀数
                    $homeAssNum = isset($mdl["teamAAssistNum"])?$mdl["teamAAssistNum"]:0;
                    $guestAssNum = isset($mdl["teamBAssistNum"])?$mdl["teamBAssistNum"]:0;//助攻数
                
                    $homeHittower = isset($mdl["teamAHittower"])?$mdl["teamAHittower"]:0;
                    $guestHittower = isset($mdl["teamBHittower"])?$mdl["teamBHittower"]:0;//破塔数
                
                    if(isset($mdl["firstBloodTeamId"]) && $mdl["firstBloodTeamId"] == $mdl["teamAId"]){//一血
                        $homeFb = STATUS_YES;
                        $guestFb = STATUS_NO;
                    }elseif(isset($mdl["firstBloodTeamId"]) && $mdl["firstBloodTeamId"] == $mdl["teamBId"]){
                        $homeFb = STATUS_NO;
                        $guestFb = STATUS_YES;
                    }
                    if(isset($mdl["firstTowerTeamId"]) && $mdl["firstTowerTeamId"] == $mdl["teamAId"]){//一塔
                        $homeFtower = STATUS_YES;
                        $guestFtower = STATUS_NO;
                    }elseif(isset($mdl["firstTowerTeamId"]) && $mdl["firstTowerTeamId"] == $mdl["teamBId"]){
                        $homeFtower = STATUS_NO;
                        $guestFtower = STATUS_YES;
                    }
                    if($gameCode == "kg"){
                        if(isset($mdl["fsdTeamId"]) && $mdl["fsdTeamId"] == $mdl["teamAId"]){//暴君
                            $homeFsd = STATUS_YES;
                            $guestFsd = STATUS_NO;
                        }elseif(isset($mdl["fsdTeamId"]) && $mdl["fsdTeamId"] == $mdl["teamBId"]){
                            $homeFsd = STATUS_NO;
                            $guestFsd = STATUS_YES;
                        }
                        if(isset($mdl["firstRoushan"]) && $mdl["firstRoushan"] == $mdl["teamAId"]){//第一主宰
                            $homeFrs = STATUS_YES;
                            $guestFrs = STATUS_NO;
                        }elseif(isset($mdl["firstRoushan"]) && $mdl["firstRoushan"] == $mdl["teamBId"]){
                            $homeFrs = STATUS_NO;
                            $guestFrs = STATUS_YES;
                        }
                    }elseif($gameCode == "lol"){
                        //小龙
                        if($mdl["fsdTeamId"] == $mdl["teamAId"]){
                            $homeSmallDra = STATUS_YES;
                            $guestSmallDra = STATUS_NO;
                        }elseif($mdl["fsdTeamId"] == $mdl["teamBId"]){
                            $homeSmallDra = STATUS_NO;
                            $guestSmallDra = STATUS_YES;
                        }
                        //大龙
                        if($mdl["firstRoushan"] == $mdl["teamAId"]){
                            $homeBigDra = STATUS_YES;
                            $guestBigDra = STATUS_NO;
                        }elseif($mdl["firstRoushan"] == $mdl["teamBId"]){
                            $homeBigDra = STATUS_NO;
                            $guestBigDra = STATUS_YES;
                        }
                    }elseif ($gameCode == "dota2"){
                        if(isset($mdl["firstRoushan"]) && $mdl["firstRoushan"] == $mdl["teamAId"]){//第一座肉山
                            $homeRoushan = STATUS_YES;
                            $guestRoushan = STATUS_NO;
                        }elseif(isset($mdl["firstRoushan"]) && $mdl["firstRoushan"] == $mdl["teamBId"]){
                            $homeRoushan = STATUS_NO;
                            $guestRoushan = STATUS_YES;
                        }
                    }
                }
                if($is_change){
                    if($homeHeader > 0 || $guestHeader > 0){
                        $home_score_json["header"] = $guestHeader;
                        $guest_score_json["header"] = $homeHeader;
                    }
                    if($homeFb > 0 || $guestFb > 0){
                        $home_score_json["fb"]= $guestFb;
                        $guest_score_json["fb"] = $homeFb;
                    }
                    if($homeHitNum > 0 || $guestHitNum > 0){
                        $home_score_json["hitnum"] = $guestHitNum;
                        $guest_score_json["hitnum"] = $homeHitNum;
                    }
                    if($homeAssNum > 0 || $guestAssNum > 0){
                        $home_score_json["assnum"] = $guestAssNum;
                        $guest_score_json["assnum"] = $homeAssNum;
                    }
                    if($homeHittower > 0 || $guestHittower > 0){
                        $home_score_json["hittower"] = $guestHittower;
                        $guest_score_json["hittower"] = $homeHittower;
                    }
                    if($homeFtower > 0 || $guestFtower > 0){
                        $home_score_json["ftower"] = $guestFtower;
                        $guest_score_json["ftower"] = $homeFtower;
                    }
                    if($homeSmallDra > 0 || $guestSmallDra > 0){
                        $home_score_json["smalldra"] = $guestSmallDra;
                        $guest_score_json["smalldra"] = $homeSmallDra;
                    }
                    if($homeBigDra > 0 || $guestBigDra > 0){
                        $home_score_json["bigdra"] = $guestBigDra;
                        $guest_score_json["bigdra"] = $homeBigDra;
                    }
                    if($homeRoushan > 0 || $guestRoushan > 0){
                        $home_score_json["roushan"] = $guestRoushan;
                        $guest_score_json["roushan"] = $homeRoushan;
                    }
                    if($homeFsd > 0 || $guestFsd > 0){
                        $home_score_json["fsd"] = $guestFsd;
                        $guest_score_json["fsd"] = $homeFsd;
                    }
                    if($homeFrs > 0 || $guestFrs > 0){
                        $home_score_json["frs"] = $guestFrs;
                        $guest_score_json["frs"] = $homeFrs;
                    }
                }else{
                    if($homeHeader > 0 || $guestHeader > 0){
                        $home_score_json["header"] = $homeHeader;
                        $guest_score_json["header"] = $guestHeader;
                    }
                    if($homeFb > 0 || $guestFb > 0){
                        $home_score_json["fb"]= $homeFb;
                        $guest_score_json["fb"] = $guestFb;
                    }
                    if($homeHitNum > 0 || $guestHitNum > 0){
                        $home_score_json["hitnum"] = $homeHitNum;
                        $guest_score_json["hitnum"] = $guestHitNum;
                    }
                    if($homeAssNum > 0 || $guestAssNum > 0){
                        $home_score_json["assnum"] = $homeAssNum;
                        $guest_score_json["assnum"] = $guestAssNum;
                    }
                    if($homeHittower > 0 || $guestHittower > 0){
                        $home_score_json["hittower"] = $homeHittower;
                        $guest_score_json["hittower"] = $guestHittower;
                    }
                    if($homeFtower > 0 || $guestFtower > 0){
                        $home_score_json["ftower"] = $homeFtower;
                        $guest_score_json["ftower"] = $guestFtower;
                    }
                    if($homeSmallDra > 0 || $guestSmallDra > 0){
                        $home_score_json["smalldra"] = $homeSmallDra;
                        $guest_score_json["smalldra"] = $guestSmallDra;
                    }
                    if($homeBigDra > 0 || $guestBigDra > 0){
                        $home_score_json["bigdra"] = $homeBigDra;
                        $guest_score_json["bigdra"] = $guestBigDra;
                    }
                    if($homeRoushan > 0 || $guestRoushan > 0){
                        $home_score_json["roushan"] = $homeRoushan;
                        $guest_score_json["roushan"] = $guestRoushan;
                    }
                    if($homeFsd > 0 || $guestFsd > 0){
                        $home_score_json["fsd"] = $homeFsd;
                        $guest_score_json["fsd"] = $guestFsd;
                    }
                    if($homeFrs > 0 || $guestFrs > 0){
                        $home_score_json["frs"] = $homeFrs;
                        $guest_score_json["frs"] = $guestFrs;
                    }
                }
                if($home_score_json || $guest_score_json){
                    break;
                }
            }
        }
        return array($home_score_json,$guest_score_json);
    }
    
    //独赢
    public function ml($game_info,$data,$play_id,$team_home,$team_guest,$odds_list=array()){
        //玩法信息
        $mlrules_info = \think\Db::name('rules')->where(array("name"=>"独赢","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_EUROPE,"status"=>1))->find();
        if($mlrules_info){
            for ($i=0;$i<count($data);$i++){
                //赔率入库
                $this->oddsData($data[$i], $play_id, $mlrules_info["id"],RULES_TYPE_EUROPE,$i+1,$odds_list);
                $this->rulesDetail($game_info, $play_id, $mlrules_info, array($team_home,$team_guest));
            }
        }        
        
    }
    
    //让球
    public function rq($game_info,$data,$play_id,$team_home,$team_guest,$odds_list =array()){
        //玩法信息
        $rqrules_info = \think\Db::name('rules')->where(array("name"=>"让分","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_ASIAN,"status"=>1))->find();
        if($rqrules_info){
            for ($i=0;$i<count($data);$i++){
                //赔率入库
                $this->oddsData($data[$i], $play_id, $rqrules_info["id"],RULES_TYPE_ASIAN,$i+1,$odds_list);
                $this->rulesDetail($game_info, $play_id, $rqrules_info, array($team_home,$team_guest));
            }
        }
    }
    
    //一血
    public function fblood($game_info,$data,$play_id,$team_home,$team_guest,$odds_list =array()){
        //玩法信息
        $rules_info = \think\Db::name('rules')->where(array("name"=>"一血","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_FIRST_BLOOD,"status"=>1))->find();
        if($rules_info){
            for ($i=0;$i<count($data);$i++){
                //赔率入库
                $this->oddsData($data[$i], $play_id, $rules_info["id"],RULES_TYPE_FIRST_BLOOD,$i+1,$odds_list);
                $this->rulesDetail($game_info, $play_id, $rules_info, array($team_home,$team_guest));
            }
        }
    }
    
    //人头数
    public function killNum($game_info,$data,$play_id,$team_home,$team_guest,$odds_list =array()){
        //玩法信息
        $rules_info = \think\Db::name('rules')->where(array("name"=>"人头数","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_KILL_NUM,"status"=>1))->find();
        if($rules_info){
            //赔率入库
            $this->oddsData($data, $play_id, $rules_info["id"],RULES_TYPE_KILL_NUM,1,$odds_list);
            $this->rulesDetail($game_info, $play_id, $rules_info, array($team_home,$team_guest));
        }
    }
    
    /**
     * 其它玩法
     * type:玩法（win为胜负）
     * bo:回合
     */
    public function other($game_info,$data,$play_id,$team_home,$team_guest,$odds_list =array(),$type="win",$bo=1){
        if($type == "win"){
            //玩法信息
            $rules_info = \think\Db::name('rules')->where(array("name"=>"第{$bo}局胜","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
            /*if(!$rules_info){
                $rules_data = array("is_default"=>0,"is_edit"=>0,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>"第{$bo}局胜","alias"=>"第{$bo}局胜","intro"=>"第{$bo}局胜","type"=>RULES_TYPE_OTHER,"explain"=>json_encode(array("home","guest")),"create_time"=>time(),"update_time"=>time(),"game_id"=>$game_info["id"],"game_type"=>$this->game_type);
                $rules_id = \think\Db::name('rules')->insert($rules_data, false, true , null);
                $rules_info = \think\Db::name('rules')->where("id",$rules_id)->find();
                model("admin/rules")->upCache();
            }*/
            if($rules_info){
                for ($i=0;$i<count($data);$i++){
                    //赔率入库
                    $this->oddsData($data[$i], $play_id, $rules_info["id"],RULES_TYPE_OTHER,$i+1,$odds_list);
                    $this->rulesDetail($game_info, $play_id, $rules_info, array($team_home,$team_guest));
                }
            }
        }
        if($type == "fb"){//一血
            //玩法信息
            $fbrules_info = \think\Db::name('rules')->where(array("name"=>"第{$bo}局一血","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
            /*if(!$fbrules_info){
                $fbrules_data = array("is_default"=>0,"is_edit"=>0,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>"第{$bo}局一血","alias"=>"第{$bo}局一血","intro"=>"第{$bo}局一血","type"=>RULES_TYPE_OTHER,"explain"=>json_encode(array("home","guest")),"create_time"=>time(),"update_time"=>time(),"game_id"=>$game_info["id"],"game_type"=>$this->game_type);
                $fbrules_id = \think\Db::name('rules')->insert($fbrules_data, false, true , null);
                $fbrules_info = \think\Db::name('rules')->where("id",$fbrules_id)->find();
                model("admin/rules")->upCache();
            }*/
            if($fbrules_info){
                for ($i=0;$i<count($data);$i++){
                    //赔率入库
                    $this->oddsData($data[$i], $play_id, $fbrules_info["id"],RULES_TYPE_OTHER,$i+1,$odds_list);
                    $this->rulesDetail($game_info, $play_id, $fbrules_info, array($team_home,$team_guest));
                }
            }
        }
        if($type == "ft"){//一塔
            //玩法信息
            $ftrules_info = \think\Db::name('rules')->where(array("name"=>"第{$bo}局一塔","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
            /*if(!$ftrules_info){
                $ftrules_data = array("is_default"=>0,"is_edit"=>0,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>"第{$bo}局一塔","alias"=>"第{$bo}局一塔","intro"=>"第{$bo}局一塔","type"=>RULES_TYPE_OTHER,"explain"=>json_encode(array("home","guest")),"create_time"=>time(),"update_time"=>time(),"game_id"=>$game_info["id"],"game_type"=>$this->game_type);
                $ftrules_id = \think\Db::name('rules')->insert($ftrules_data, false, true , null);
                $ftrules_info = \think\Db::name('rules')->where("id",$ftrules_id)->find();
                model("admin/rules")->upCache();
            }*/
            if($ftrules_info){
                for ($i=0;$i<count($data);$i++){
                    //赔率入库
                    $this->oddsData($data[$i], $play_id, $ftrules_info["id"],RULES_TYPE_OTHER,$i+1,$odds_list);
                    $this->rulesDetail($game_info, $play_id, $ftrules_info, array($team_home,$team_guest));
                }
            }
        }
        if($type == "sd"){//小龙
            //玩法信息
            $sdrules_info = \think\Db::name('rules')->where(array("name"=>"第{$bo}局第一只小龙","game_id"=>$game_info["id"],"game_type"=>$this->game_type,"type"=>RULES_TYPE_OTHER,"status"=>1))->find();
            /*if(!$sdrules_info){
                $sdrules_data = array("is_default"=>0,"is_edit"=>0,"is_single"=>1,"status"=>1,"is_delete"=>0,"name"=>"第{$bo}局第一只小龙","alias"=>"第{$bo}局第一只小龙","intro"=>"第{$bo}局第一只小龙","type"=>RULES_TYPE_OTHER,"explain"=>json_encode(array("home","guest")),"create_time"=>time(),"update_time"=>time(),"game_id"=>$game_info["id"],"game_type"=>$this->game_type);
                $sdrules_id = \think\Db::name('rules')->insert($sdrules_data, false, true , null);
                $sdrules_info = \think\Db::name('rules')->where("id",$sdrules_id)->find();
                model("admin/rules")->upCache();
            }*/
            if($sdrules_info){
                for ($i=0;$i<count($data);$i++){
                    //赔率入库
                    $this->oddsData($data[$i], $play_id, $sdrules_info["id"],RULES_TYPE_OTHER,$i+1,$odds_list);
                    $this->rulesDetail($game_info, $play_id, $sdrules_info, array($team_home,$team_guest));
                }
            }
        }
        
    }
    
    
    //赔率
    public function oddsData($odds,$play_id,$rules_id,$rules_type,$loop=1,$odds_list=array()){
        $odds_info = isset($odds_list[$rules_id][$loop])?$odds_list[$rules_id][$loop]:array();
        if($odds_info){
            //重新组装JSON
            $new_data = array();
            $odds_data = json_decode($odds_info["odds"],true);
            $new_data["init"] = $odds_data["init"];
            $new_data["time"] = $odds;
            //赔率发生变化
            if(md5(json_encode($new_data)) != $odds_info["md5"]){
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
        $odds_json["odds_company_id"] = $this->company_id;
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
    //更新odds
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
    
    //更新或者插入玩法详情
    public function rulesDetail($game_info,$play_id,$rules_info,$rules_explain){
        
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
            
            $play_team = \think\Db::name('play_team')->where(["play_id"=>$play_id,"has_home"=>$has_home])->order("id DESC")->select();
            if(count($play_team)>1){
                \think\Db::name('play_team')->where(["play_id"=>$play_id,"has_home"=>$has_home,"id"=>["neq",$play_team[0]["id"]]])->delete();
            }
            $play_team = isset($play_team[0])?$play_team[0]:array();
            if(isset($play_team["id"])){
                if($play_team["team_id"] == $team_id){
                    \think\Db::name('play_team')->where("id",$play_team["id"])->update($data);
                }else{
                    $data["team_id"] = $team_id;
                    \think\Db::name('play_team')->where("id",$play_team["id"])->update($data);
                }
            }else{
                $data["play_id"] = $play_id;
                $data["team_id"] = $team_id;
                $data["has_home"] = $has_home;
                \think\Db::name('play_team')->insert($data);
            }
        }
    }
    
    
}

?>