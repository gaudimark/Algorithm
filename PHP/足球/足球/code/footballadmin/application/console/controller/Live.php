<?php
/**
 * 采集中国足彩网的足球比分
 * */
namespace app\console\controller;

class Live extends \app\console\logic\Basic{
    
    public function index(){
        
        set_time_limit(0);
        $url = "http://live.zgzcw.com/ls/AllData.action";
        $today = array();
        $today["code"] = "all";
        $today["date"] = date("Y-m-d");
        $today["ajax"] = true;
        
        //前天
        $tyestoday = $today;
        $tyestoday["date"] = date("Y-m-d",strtotime("-2 day"));
        $tyestoday_content = $this->curl($url,$tyestoday,"http://live.zgzcw.com/qb/");
        $this->ajaxData($tyestoday_content);
        //昨天
        $yestoday = $today;
        $yestoday["date"] = date("Y-m-d",strtotime("-1 day"));
        $yestoday_content = $this->curl($url,$yestoday,"http://live.zgzcw.com/qb/");
        $this->ajaxData($yestoday_content);
        //今天
        $today_content = $this->curl($url,$today,"http://live.zgzcw.com/qb/");
        $this->ajaxData($today_content);
        
    }
    
    //采集正在比赛的数据
    public function ajaxData($content){
        set_time_limit(0);
        $playSrv = new \library\service\Play();
        if($content){
            $content = $this->trimall($content);
            //比赛
            $bs_reg = "/<tr class=\"matchTr\"[\s\S]*?\<\/tr>/";
            preg_match_all($bs_reg, $content, $bs_arr);
            if($bs_arr){
                foreach ($bs_arr[0] as $bs){
                    //比赛ID
                    preg_match_all("/matchid=\"(\d+)\"|matchid = \"(\d+)\"/", $bs, $zc_arr);
                    $zcid = 0;
                    if(isset($zc_arr[1][0]) && isset($zc_arr[2][0])){
                        if($zc_arr[1][0]!= ""){
                            $zcid = $zc_arr[1][0];
                        }elseif($zc_arr[2][0] != ""){
                            $zcid = $zc_arr[2][0];
                        }
                    }
                    
                    //判断是否已经存在对应比赛
                    $md5_play = md5("zgzcw".$zcid);
                    $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5_play))->find();
                    
                    //比赛时间
                    $time = 0;
                    $time_reg = "/date=\"(.*?)\"|date = \"(.*?)\"/";
                    preg_match_all($time_reg, $bs, $time_arr);
                    if(isset($time_arr[1][0]) && isset($time_arr[2][0])){
                        if($time_arr[1][0] != ""){
                            $time = strtotime($time_arr[1][0]);
                        }elseif($time_arr[2][0] != ""){
                            $time = strtotime($time_arr[2][0]);
                        }
                    }
                    //主队
                    $home_reg = "/<em class=\"paim\">[\s\S]*?<a .*?>(.*?)<\/a>/";
                    preg_match_all($home_reg, $bs, $home_arr);
                    $home_name = isset($home_arr[1][0])?$home_arr[1][0]:'';
                    
                    //主队红牌
                    $home_red_reg = "/<span class=\"sptr\"><span class=\"hongpai\".*?\>(\d+)<\/span>/";
                    preg_match_all($home_red_reg, $bs , $home_red_arr);
                    $home_red = isset($home_red_arr[1][0])?$home_red_arr[1][0]:0;
                    //客队
                    $guest_reg = "/<span class=\"sptl\">.*?<a .*?\>(.*?)<\/a>/";
                    preg_match_all($guest_reg, $bs, $guest_arr);
                    $guest_name = isset($guest_arr[1][0])?$guest_arr[1][0]:'';
                    
                    //客队红牌
                    $guest_red_reg = "/<span class=\"sptl\">[\s\S]*?<span class=\"hongpai\".*?\>(\d+)<\/span>/";
                    preg_match_all($guest_red_reg, $bs , $guest_red_arr);
                    $guest_red = isset($guest_red_arr[1][0])?$guest_red_arr[1][0]:0;
                    
                    //状态
                    $status_reg = "/<td class=\"matchStatus\">(.*?)<\/td>/";
                    preg_match_all($status_reg, $bs , $status_arr);
                    $status = isset($status_arr[1][0])?$status_arr[1][0]:'';
                    if($status)
                        $status = preg_replace("/<strong.*?>|<\/strong>|<img.*?>/", "", trim($status));
                    //echo $status."\n";
                    //比赛是否在进行中
                    $match_status_reg = "/status=\"(.*?)\"/";
                    preg_match_all($match_status_reg, $bs , $match_status_arr);
                    $match_status = isset($match_status_arr[1][0])?$match_status_arr[1][0]:0;
                    
                    //比分
                    $score_reg = "/<span class=\"boldbf.*?\">(\d{1,2})-(\d{0,2})<\/span>/";
                    preg_match_all($score_reg, $bs , $score_arr);
                    $home_score = isset($score_arr[1][0])?$score_arr[1][0]:0;
                    $guest_score = isset($score_arr[2][0])?$score_arr[2][0]:0;
                    
                    //半场比分
                    $bc_score_reg = "/<span class=\"bcbf.*?\">(\d{1,2})-(\d{0,2})<\/span>/";
                    preg_match_all($bc_score_reg, $bs , $bc_score_arr);
                    $home_half_score = isset($bc_score_arr[1][0])?$bc_score_arr[1][0]:0;
                    $guest_half_score = isset($bc_score_arr[2][0])?$bc_score_arr[2][0]:0;
                    
                    $data = array();
                    $data["team_home_score"] = $home_score;
                    $data["team_guest_score"] = $guest_score;
                    if($status>0 && $status <=45){
                        $data["team_home_half_score"] = $home_score;
                        $data["team_guest_half_score"] = $guest_score;
                    }else{
                        $data["team_home_half_score"] = $home_half_score;
                        $data["team_guest_half_score"] = $guest_half_score;
                    }
                    $data["home_red"] = $home_red;
                    $data["guest_red"] = $guest_red;
                    $data["play_time"] = $time;
                    //比赛状态
                    if($match_status >= 1){
                        $data["status"] = PLAT_STATUS_START;
                        if(trim($status) == '中')
                            $data["match_time"] = "中场休息";
                        elseif(trim($status) != "") 
                            $data["match_time"] = trim($status)."'";
                        elseif(trim($status) == "")
                        $data["match_time"] = '';
                    }elseif ($match_status == '-1'){
                        $data["status"] = PLAT_STATUS_END;
                        $data["end_time"] = time();
                    }elseif ($match_status == '-14'){
                        $data["status"] = PLAT_STATUS_EXC;
                    }elseif ($match_status == 0){
                        $data["status"] = PLAT_STATUS_NOT_START;
                    }elseif ($match_status == '-12'){//腰斩
                        $data["status"] = PLAT_STATUS_CUT;
                    }
                    if($status == "待定" || $match_status == "-11"){
                        $data["status"] = PLAT_STATUS_WAIT;
                    }
                    if($data["status"] == PLAT_STATUS_NOT_START && time() >= $time){
                        $data["status"] == PLAT_STATUS_START;
                    }
                    if(!isset($data["status"])){
                        continue;
                    }
                    
                    if(!$play_info){
                        continue;
                    }
                    //比赛结束，结算完成，结算中不更新
                    if($play_info["status"] == PLAT_STATUS_END || $play_info["status"] == PLAT_STATUS_STATEMENT || $play_info["status"] == PLAT_STATUS_STATEMENT_BEGIN){
                        continue;
                    }
                    $data["update_time"] = time();
                    $res = \think\Db::name('play')->where("id",$play_info["id"])->update($data);
                    
                    
                    $this->insertPlayTeam($play_info["id"],$play_info["team_home_id"],1,$home_score,$data["team_home_half_score"],$home_red,0,"");
                    $this->insertPlayTeam($play_info["id"],$play_info["team_guest_id"],0,$guest_score,$data["team_guest_half_score"],$guest_red,0,"");
                    //写入缓存
                    $this->checkArenaStatusByPlayStatus($play_info["id"], $data["status"]);
                    if($res){
                        
                        echo iconv("UTF-8","UTF-8",$home_name." VS ".$guest_name."\n");
                    }
                    
                }
            }
        }else{
            echo "数据获取异常";
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

?>