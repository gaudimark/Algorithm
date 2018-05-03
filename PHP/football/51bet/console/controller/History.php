<?php
namespace app\console\controller;

use think\Controller;

class History extends \app\console\logic\Basic{
    
    //联赛球队最近30场比赛采集
    public function getPlayHistory(){
        set_time_limit(0);
        $url = "http://saishi.zgzcw.com/soccer";
        $content = $this->curl($url);
        $content = $this->trimall($content);
        //echo "start:".date("Y-m-d H:i:s")."<br>";
        for($i=0;$i<1;$i++){
            if($i == 0){
                $reg = "/<div id=\"div_oTab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            }else
                $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            preg_match($reg, $content,$all_data);
    
            $country_reg = "/<a class=\"first-link\">[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>.*?<div class=\"lslogo fl\">([\s\S]*?)<\/div><\/div><\/a><\/div>/";
            preg_match_all($country_reg, $all_data[0],$country);
            for($j=0;$j<count($country[0]);$j++){//国家
                //for($j=0;$j<1;$j++){
                $ls_reg = "/<a href=\"(.*?)\"[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($ls_reg,$country[0][$j],$ls_arr);
                $data = array();
                for($k=0;$k<count($ls_arr[0]);$k++){//联赛
                    //联赛对应信息
                    $match_array = \think\Db::name('match')->where("name",$ls_arr[2][$k])->select();
                    $ls_url = "http://saishi.zgzcw.com".$ls_arr[1][$k];
                    $this->getPlayHistoryData($ls_url,$match_array[0]["country_id"],$match_array[0]["id"]);
    
                    //print_r($team_list);
                }
            }
        }
        //echo "end:".date("Y-m-d H:i:s");
    }
    
    public function getPlayHistory1(){
        set_time_limit(0);
        $url = "http://saishi.zgzcw.com/soccer";
        $content = $this->curl($url);
        $content = $this->trimall($content);
        //echo "start:".date("Y-m-d H:i:s")."<br>";
        for($i=1;$i<2;$i++){
            if($i == 0){
                $reg = "/<div id=\"div_oTab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            }else
                $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            preg_match($reg, $content,$all_data);
    
            $country_reg = "/<a class=\"first-link\">[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>.*?<div class=\"lslogo fl\">([\s\S]*?)<\/div><\/div><\/a><\/div>/";
            preg_match_all($country_reg, $all_data[0],$country);
            for($j=0;$j<count($country[0]);$j++){//国家
                //for($j=0;$j<1;$j++){
                $ls_reg = "/<a href=\"(.*?)\"[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($ls_reg,$country[0][$j],$ls_arr);
                $data = array();
                for($k=0;$k<count($ls_arr[0]);$k++){//联赛
                    //联赛对应信息
                    $match_array = \think\Db::name('match')->where("name",$ls_arr[2][$k])->select();
                    $ls_url = "http://saishi.zgzcw.com".$ls_arr[1][$k];
                    $this->getPlayHistoryData($ls_url,$match_array[0]["country_id"],$match_array[0]["id"]);
    
                    //print_r($team_list);
                }
            }
        }
        //echo "end:".date("Y-m-d H:i:s");
    }
    public function getPlayHistory2(){
        set_time_limit(0);
        $url = "http://saishi.zgzcw.com/soccer";
        $content = $this->curl($url);
        $content = $this->trimall($content);
        //echo "start:".date("Y-m-d H:i:s")."<br>";
        for($i=2;$i<3;$i++){
            if($i == 0){
                $reg = "/<div id=\"div_oTab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            }else
                $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            preg_match($reg, $content,$all_data);
    
            $country_reg = "/<a class=\"first-link\">[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>.*?<div class=\"lslogo fl\">([\s\S]*?)<\/div><\/div><\/a><\/div>/";
            preg_match_all($country_reg, $all_data[0],$country);
            for($j=0;$j<count($country[0]);$j++){//国家
                //for($j=0;$j<1;$j++){
                $ls_reg = "/<a href=\"(.*?)\"[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($ls_reg,$country[0][$j],$ls_arr);
                $data = array();
                for($k=0;$k<count($ls_arr[0]);$k++){//联赛
                    //联赛对应信息
                    $match_array = \think\Db::name('match')->where("name",$ls_arr[2][$k])->select();
                    $ls_url = "http://saishi.zgzcw.com".$ls_arr[1][$k];
                    $this->getPlayHistoryData($ls_url,$match_array[0]["country_id"],$match_array[0]["id"]);
    
                    //print_r($team_list);
                }
            }
        }
        //echo "end:".date("Y-m-d H:i:s");
    }
    public function getPlayHistory3(){
        set_time_limit(0);
        $url = "http://saishi.zgzcw.com/soccer";
        $content = $this->curl($url);
        $content = $this->trimall($content);
        //echo "start:".date("Y-m-d H:i:s")."<br>";
        for($i=3;$i<4;$i++){
            if($i == 0){
                $reg = "/<div id=\"div_oTab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            }else
                $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"clear\"><\/div>/";
            preg_match($reg, $content,$all_data);
    
            $country_reg = "/<a class=\"first-link\">[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>.*?<div class=\"lslogo fl\">([\s\S]*?)<\/div><\/div><\/a><\/div>/";
            preg_match_all($country_reg, $all_data[0],$country);
            for($j=0;$j<count($country[0]);$j++){//国家
                //for($j=0;$j<1;$j++){
                $ls_reg = "/<a href=\"(.*?)\"[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($ls_reg,$country[0][$j],$ls_arr);
                $data = array();
                for($k=0;$k<count($ls_arr[0]);$k++){//联赛
                    //联赛对应信息
                    $match_array = \think\Db::name('match')->where("name",$ls_arr[2][$k])->select();
                    $ls_url = "http://saishi.zgzcw.com".$ls_arr[1][$k];
                    $this->getPlayHistoryData($ls_url,$match_array[0]["country_id"],$match_array[0]["id"]);
    
                    //print_r($team_list);
                    //break;
                }
                //break;
            }
        }
        //echo "end:".date("Y-m-d H:i:s");
    }
    
    
    //国家队最近30场比赛
    public function countryHistoryData(){
        set_time_limit(0);
        //echo "start:".date("Y-m-d H:i:s")."<br>";
        $url = "http://saishi.zgzcw.com/soccer";
        $content = $this->curl($url);
        $content = $this->trimall($content);
        //杯赛
        for($i=4;$i<=4;$i++){
            /*if($i<4){
                if($i == 0){
                    $reg = "/<div id=\"div_oTab\"[\s\S]*?<div class=\"bs\">([\s\S]*?)<div class=\"clear\"><\/div>/";
                }else
                    $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"bs\">([\s\S]*?)<div class=\"clear\"><\/div>/";
                preg_match($reg, $content,$all_data);
                $cup_reg = "/<a href=\"(.*?)\"[\s\S]*?src=\"(.*?)\"[\S\s]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($cup_reg, $all_data[1],$cup_data);
    
                $data = array();
                for($j=0;$j<count($cup_data[0]);$j++){
                    //联赛对应信息
                    $match_array = \think\Db::name('match')->where("name",$cup_data[3][$j])->select();
                    //print_r($match_array);
                    $data_url = "http://saishi.zgzcw.com".$cup_data[1][$j];
                    $this->getPlayHistoryData($data_url,$match_array[0]["country_id"],$match_array[0]["id"]);
                }
            }*/
            if($i==4){
                $reg = "/<div id=\"div_".$i."Tab\"[\s\S]*?<div class=\"ls\">([\s\S]*?)<div class=\"clear\"><\/div>/";
                preg_match($reg, $content,$all_data);
                $gj_reg = "/<div class=\"lslogo fl\">[\s\S]*?href=\"(.*?)\"[\s\S]*?src=\"(.*?)\"[\s\S]*?<div class=\"lstitle\">(.*?)<\/div>/";
                preg_match_all($gj_reg, $all_data[1],$gj_data);
                //print_r($gj_data);
                $data = array();
                for($j=0;$j<count($gj_data[0]);$j++){
                    if($j != 16){
                        //联赛对应信息
                        $match_array = \think\Db::name('match')->where("name",$gj_data[3][$j])->select();
                        $data_url = "http://saishi.zgzcw.com".$gj_data[1][$j];
                        $this->getPlayHistoryData($data_url,$match_array[0]["country_id"],$match_array[0]["id"]);
                    }
                }
            }
        }
        //echo "end:".date("Y-m-d H:i:s");
    }
    
    public function getPlayHistoryData($url,$country_id,$match_id){
        $this->logTxt("历史比赛采集      操作时间:".date("Y-m-d H:i:s").",  采集国家id：".$country_id."  \r\n");
        set_time_limit(0);
        $content = $this->curl($url);
        if($content){
            $content = $this->trimall($content);
            $reg = "/<div>球队列表<\/div>[\s\S]*?<ul>([\s\S]*?)<\/ul>/";
            preg_match($reg, $content,$t_array);
            if(isset($t_array[1])){
                $qd_reg = "/href=\"(.*?)\"[\s\S]*?<li>(.*?)<\/li>/";
                preg_match_all($qd_reg, $t_array[1],$teams_arr);
                if(isset($teams_arr[0][0])){
                    for($i=0;$i<count($teams_arr[0]);$i++){
                        echo iconv("UTF-8","UTF-8",trim($teams_arr[2][$i]))."\n";
                        //判断球队是否已经存在
                        $team_array = \think\Db::name('team')->where("name",trim($teams_arr[2][$i]))->find();
                        $team_id = "";
                        $team_url = $teams_arr[1][$i];
                        $tcontent = $this->curl($team_url);
                        if(!isset($team_array["id"])){
                            //球队数据
                            if($tcontent){
                                $tcontent = $this->trimall($tcontent);
                                $t_reg = "/<dl class=\"star_dl\">[\s\S]*?src=\"(.*?)\"[\s\S]*?球队成立：<\/span><var>(.*?)<\/var>[\s\S]*?主教练：<\/span><var>(.*?)<\/var>[\S\s]*?<a rel=\"nofollow\" href=\"(.*?)\"/";
                                preg_match_all($t_reg, $tcontent ,$team_data);
                    
                                $teams = array();
                                $teams["country_id"] = $country_id;
                                $teams["name"] = $teams_arr[2][$i];
                    
                                if($team_data[0]){
                    
                                    $teams["coach"] = $team_data[3][0]?$team_data[3][0]:'';
                                    $teams["website"] = $team_data[4][0]?$team_data[4][0]:'';
                                    $teams["found"] = $team_data[2][0]?$team_data[2][0]:'';
                                    if($team_data[1][0])
                                        $teams["logo"] = $this->saveImg($team_data[1][0]);
                                    else
                                        $teams["logo"] = '';
                    
                                }
                                $team_id = \think\Db::name('team')->insert($teams,false,true);
                                model("admin/team")->upCacheOnly($team_id);
                            }
                        }else {
                            $team_id = $team_array["id"];
                        }
                         
                        //获取战绩情况
                        if($tcontent){
                            $tcontent = $this->trimall($tcontent);
                            $reg = "/<tr id=\"m_\d+\" home=\"\d+\"><td title=\"(.*?)\">.*?<\/td><td title=\"(.*?)\">.*?<\/td><td class=\"team_logo\" style=\"float:right;\">[\s\S]*?title=\"(.*?)\"[\s\S]*?<td class=\"team_logo\" style=\"float:left;\">[\s\S]*?title=\"(.*?)\"[\s\S]*?<td><span class=\"\">(\d+):(\d+)<\/span><\/td><td><span class=\"\">(\d+):(\d+)<\/span><\/td>[\s\S]*?<a href=\"http:\/\/fenxi.zgzcw.com\/(\d+)\/bsls\" class=\"more\">.*?<\/tr>/";
                            preg_match_all($reg, $tcontent ,$bs_data);//最近比赛
                            //print_r($bs_data);  
                            $play = array();
                            if($bs_data[0]){
                                for($t=0;$t<count($bs_data[0]);$t++){
                                    //比赛
                                    $play_match_info = \think\Db::name('match')->where("name",$bs_data[1][$t])->find();
                                    if(!$play_match_info){
                                        continue;
                                    }
                                    //echo $bs_data[9][$t]."\n";
                                    $md5_play = md5("zgzcw".$bs_data[9][$t]);
                                    //判断是否有比赛
                                    $old_match_info = \think\Db::name('play_history')->where("md5_play",$md5_play)->find();
                                    if(!$old_match_info){
                                        $team_home_info = \think\Db::name('team')->where("name",str_replace(" ", "", $bs_data[3][$t]))->find();
                                        $team_guest_info = \think\Db::name('team')->where("name",str_replace(" ", "", $bs_data[4][$t]))->find();
                                        $team_home_id = $team_home_info["id"];
                                        $team_guest_id = $team_guest_info["id"];
                                        if(!$team_home_info){
                                            $team_home_id = \think\Db::name('team')->insert(array("game_type"=>1,"name"=>str_replace(" ", "", $bs_data[3][$t]),"country_id"=>1),false,true);
                                            model("admin/team")->upCacheOnly($team_home_id);
                                        }
                                        if(!$team_guest_info){
                                            $team_guest_id = \think\Db::name('team')->insert(array("game_type"=>1,"name"=>str_replace(" ", "", $bs_data[4][$t]),"country_id"=>1),false,true);
                                            model("admin/team")->upCacheOnly($team_guest_id);
                                        }
                                        $history = array();
                                        $history["md5_play"] = $md5_play;
                                        $history["game_type"] = GAME_TYPE_FOOTBALL;
                                        $history["play_time"] = strtotime($bs_data[2][$t]);
                                        $history["team_home_name"] = str_replace(" ", "", $bs_data[3][$t]);
                                        $history["team_guest_name"] = str_replace(" ", "", $bs_data[4][$t]);
                                        $history["create_time"] = time();
                                        $history["update_time"] = time();
                                        $history["status"] = PLAT_STATUS_END;
                    
                                        if($play_match_info){
                                            $history["match_id"] = $play_match_info["id"];
                                        }
                                        if($team_home_info){
                                            $history["team_home_id"] = $team_home_id;
                                        }
                                        if($team_guest_info){
                                            $history["team_guest_id"] = $team_guest_id;
                                        }
                                        $history["team_home_score"] = $bs_data[5][$t];
                                        $history["team_guest_score"] = $bs_data[6][$t];
                                        $history["team_home_half_score"] = $bs_data[7][$t];
                                        $history["team_guest_half_score"] = $bs_data[8][$t];
                                        
                                        //$play[] = $history;
                                        \think\Db::name('play_history')->insert($history);
                                    }
                                    //print_r($play);
                                    //\think\Db::name('play_history')->insertAll($play);
                                }
                            }
                        }
                    }
                }
                  
            }
                
        }
    }
    
    
    //历史对战
    public function Fligt(){
        set_time_limit(0);
        $url = "http://live.zgzcw.com/qb/";
        $content = $this->curl($url);
        if($content){
            $content = $this->trimall($content);
            preg_match_all("/<tbody>([\s\S]*?)<\/tbody>/", $content,$body);
            if(isset($body[1][0])){
                $match_reg = "/matchid = \"(\d+)\"|matchid=\"(\d+)\"/";
                preg_match_all($match_reg, $body[1][0],$match_ids);
                for ($i=0;$i<count($match_ids[0]);$i++){
                    $match_id = 0;
                    if(isset($match_ids[1][$i])){
                        if($match_ids[1][$i] != ""){
                            $match_id = $match_ids[1][$i];
                        }elseif($match_ids[2][$i] != ""){
                            $match_id = $match_ids[2][$i];
                        }
                    }
                    //echo $match_id."\n";
                    $url = "http://fenxi.zgzcw.com/".$match_id."/bfyc";
                    $this->getFligtData($url);
                    //break;
                }
            }
        }
    
    }
    
    public function getFligtData($url){
        set_time_limit(0);
        $this->logTxt("对战数据采集      操作时间:".date("Y-m-d H:i:s").",  采集url：".$url."  \r\n");
        if($url){
            $content = $this->curl($url);
            if($content){
                $content = $this->trimall($content);
                $reg = "/<div class=\"newstyle-s1\" id=\"recordBox\">(.*?)<\/div>/";
                preg_match_all($reg, $content,$fligt);
                //print_r($fligt);
                if(isset($fligt[1][0])){
                    $match_reg = "/<tr data=\"\d\">.*?<\/tr>/";
                    preg_match_all($match_reg, $fligt[1][0],$match_list);
                    //print_r($match_list);
                    if(isset($match_list[0][0])){
                        foreach ($match_list[0] as $matchs){
                            //echo $match."\n";
                            $data_reg = "/<tr data=\"\d\"><td >(.*?)<\/td><td >\d+<\/td><td><script>document.write\(\"(\d+)-(\d+)-(\d+)\".*?<\/script><\/td><td><a.*?>(.*?)<\/a><\/td><td.*?><a.*?><strong>(\d+):(\d+)<\/strong>\((\d+):(\d+)\)<\/a><\/td><td><a.*?>(.*?)<\/a>.*?<\/tr>/";
                            preg_match_all($data_reg,$matchs,$data);
                            
                            if(!isset($data[1][0])){
                                continue;
                            }
                            $match = str_replace(" ", "", $data[1][0]);//赛事
                            $year = str_replace(" ", "", $data[2][0]);//年
                            $month = str_replace(" ", "", $data[3][0]);//月
                            $day = str_replace(" ", "", $data[4][0]);//日
                            $team_home_name = str_replace(" ", "", $data[5][0]);//主队
                            $team_home_score = str_replace(" ", "", $data[6][0]);//全场主队得分
                            $team_guest_score = str_replace(" ", "", $data[7][0]);//全场客队得分
                            $team_home_half_score = str_replace(" ", "", $data[8][0]);//半场主队得分
                            $team_guest_half_score = str_replace(" ", "", $data[9][0]);//半场客队得分
                            $team_guest_name = str_replace(" ", "", $data[10][0]);//客队
    
                            //查询赛事信息
                            $match_info = \think\Db::name('match')->where("name",$match)->find();
                            if(!$match_info){//如果不存在对应赛事，则先添加赛事
                                $mid = \think\Db::name('match')->insert(array("country_id"=>1,"name"=>$match,"game_type"=>GAME_TYPE_FOOTBALL),false,true);
                                model("admin/match")->upCacheOnly($mid);
                                $match_info = getMatch($mid);
                            }
                            //处理比赛时间
                            if(strlen($year) == 2){
                                $year = "20".$year;
                            }
                            if(strlen($month) == 1){
                                $month = "0".$month;
                            }
                            if(strlen($day) == 1){
                                $day = "0".$day;
                            }
                            $play_time = strtotime($year.$month.$day);
                            //查询主队信息
                            $team_home_info = \think\Db::name('team')->where("name",$team_home_name)->find();
                            if(!$team_home_info){
                                $htid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_home_name),false,true);
                                model("admin/team")->upCacheOnly($htid);
                                $team_home_info = getTeam($htid);
                            }
                            //查询客队信息
                            $team_guest_info = \think\Db::name('team')->where("name",$team_guest_name)->find();
                            if(!$team_guest_info){
                                $gtid = \think\Db::name('team')->insert(array("country_id"=>1,"name"=>$team_guest_info),false,true);
                                model("admin/team")->upCacheOnly($gtid);
                                $team_guest_info = getTeam($gtid);
                            }
                            //获取历史对战的比赛id
                            $mreg = "/href=\"\/(.*?)\/zrtj/";
                            preg_match_all($mreg, $matchs, $his_data);
                            if(!isset($his_data[1][0])){
                                continue;
                            }
                            //echo $his_data[1][0]."\n";
                            $md5_play = md5("zgzcw".$his_data[1][0]);
                            $play_info = \think\Db::name('play_history')->where("md5_play",$md5_play)->find();
                            if(!$play_info){
                                $data1 = array();
                                $data1["md5_play"] = $md5_play;
                                $data1["game_type"] = GAME_TYPE_FOOTBALL;
                                $data1["play_time"] = $play_time;
                                $data1["team_home_id"] = $team_home_info["id"];
                                $data1["team_home_name"] = $team_home_name;
                                $data1["team_guest_id"] = $team_guest_info["id"];
                                $data1["team_guest_name"] = $team_guest_name;
                                $data1["match_id"] = $match_info["id"];
                                $data1["team_home_score"] = $team_home_score;
                                $data1["team_guest_score"] = $team_guest_score;
                                $data1["team_home_half_score"] = $team_home_half_score;
                                $data1["team_guest_half_score"] = $team_guest_half_score;
                                $data1["status"] = PLAT_STATUS_END;
                                $data1["create_time"] = time();
                                $data1["update_time"] = time();
                                ///print_r($data);
                                \think\Db::name('play_history')->insert($data1);
                                echo $team_home_name." VS ".$team_guest_name."\n\r";
                            }
                        }
                    }
                }
    
            }
    
        }
    }
    
    public function getContent($url){
        set_time_limit(0);
        //模拟请求
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        //curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        return $return;
    }
    
    
    public function trimall($str){
        $qian = array("  ","\t","\n","\r","   ");
        return str_replace($qian, '', $str);
    }
    
    
    public function saveImg($url){
        if(strstr($url, "http://img.zgzcw.com") && !strstr($url, "nopic")){
            $save_src = "assets/attach/team_logo/";
            $md5_data = md5($url);
            $md5_path = substr($md5_data,0,2)."/".substr($md5_data,2,2)."/".substr($md5_data,4,2)."/";
            //$img = file_get_contents($url);
            $img = $this->getImg($url);
            if($img){
                $data = explode(".", $url);
                $i = count($data);
                $new_img = rand(100,999).time().rand(100,999).".".$data[$i-1];
                if(!file_exists($save_src.$md5_path))
                    mkdir($save_src.$md5_path,0777,true);
                
                $fp= @fopen($save_src.$md5_path.$new_img,"a"); 
                fwrite($fp,$img);
                //file_put_contents($save_src.$md5_path.$new_img, $img);
                return "team_logo/".$md5_path.$new_img;
            }else{
                return '';
            }
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
        
    }
                
    
    
}

?>