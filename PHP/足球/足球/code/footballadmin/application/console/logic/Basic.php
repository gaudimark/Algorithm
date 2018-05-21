<?php

namespace app\console\logic;
class Basic{
    public $mainDataBase = 'leitai'; //主库
    public $mainDataTablePrefix = 'lt_';//主库表名前缀
    public $odds_company = array(
                                "1"=>array("id"=>22,"name"=>"澳门","has_asia"=>1,"has_europe"=>1,"zc_id"=>1),
                                "3"=>array("id"=>2,"name"=>"ＳＢ/皇冠","has_asia"=>1,"has_europe"=>1,"zc_id"=>3),
                                "4"=>array("id"=>14,"name"=>"立博","has_asia"=>0,"has_europe"=>1,"zc_id"=>4),
                                "7"=>array("id"=>17,"name"=>"SNAI","has_asia"=>0,"has_europe"=>1,"zc_id"=>7),
                                "8"=>array("id"=>3,"name"=>"Bet365","has_asia"=>1,"has_europe"=>1,"zc_id"=>8),
                                "12"=>array("id"=>4,"name"=>"易胜博","has_asia"=>1,"has_europe"=>1,"zc_id"=>12),
                                "14"=>array("id"=>5,"name"=>"韦德","has_asia"=>1,"has_europe"=>0,"zc_id"=>14),
                                "22"=>array("id"=>7,"name"=>"10bet","has_asia"=>1,"has_europe"=>1,"zc_id"=>22),
                                "23"=>array("id"=>8,"name"=>"金宝博","has_asia"=>1,"has_europe"=>1,"zc_id"=>23),
                                "24"=>array("id"=>9,"name"=>"12bet/沙巴","has_asia"=>1,"has_europe"=>0,"zc_id"=>24),
                                "31"=>array("id"=>10,"name"=>"利记","has_asia"=>1,"has_europe"=>1,"zc_id"=>31),
                                "35"=>array("id"=>11,"name"=>"盈禾","has_asia"=>1,"has_europe"=>0,"zc_id"=>35),
                                "42"=>array("id"=>12,"name"=>"18bet","has_asia"=>1,"has_europe"=>1,"zc_id"=>42),
                            );

    public function __construct(){
        $dataBase = config('database');
        $this->mainDataBase = $dataBase['database'];
        $this->mainDataTablePrefix = $dataBase['prefix'];
    }

    public function console($message){
        echo "[".date("Y-m-d H:i:s")."]\t{$message}".(IS_CLI ? PHP_EOL : '<br/>');
    }

    //获取渠道信息
    public function getDitch($KindID,$ClientKind = 0){/*
        //查询所有渠道
        if($ClientKind > 0){
            $where = ' AND ClientKind = '.$ClientKind;
        }
        $sql = "SELECT number FROM ditch WHERE KindID='$KindID' AND stat_status=1 {$where} ORDER BY id ASC";
        $list = $this->dbObj->myQuery($sql, 1, false);
        $_ditchArr = array();
        if($list){
            foreach ($list as $value){
                $_ditchArr[] = $value['number'];
            }
        }*/
        return [[0]];
    }

    /**
     * 编码转换
     * @param type $in_str
     * @param type $coding_type 1 utf-8转gbk 2 gbk转utf-8
     */
    public function transcoding($in_str,$coding_type=1){
        $return = $in_str;
        //此处上线时要处理 注册掉，或保持不变，只有本地时才使用此转义
        //var_dump($_SERVER);
        if(stripos($_SERVER['PHP_SELF'],'sourcecode')!==false){
            if($coding_type==1){
                $return = mb_convert_encoding($in_str, 'gbk','utf-8');
            }else{
                $return = mb_convert_encoding($in_str, 'utf-8','gbk');
            }
        }
        return $return;
    }
    /**
     * 获取时间的昨天 上周 上月 开始时间 结束时间
     * @param type $ymd
     * @param type $type 1 天 2周 3月
     * @return 开始时间及结束时间
     */
    public function getTimespace($ymd='',$type=1){
        if($ymd==''){
            $ymd = date('Ymd');
        }
        $times = strtotime($ymd);
        if($type==1){
            $begin=mktime(0,0,0,date('m',$times),date('d',$times)-1,date('Y',$times));
            $end=mktime(0,0,0,date('m',$times),date('d',$times),date('Y',$times))-1;
        }elseif($type==2){
            $begin=mktime(0,0,0,date('m',$times),date('d',$times)-date('w',$times)+1-7,date('Y',$times));
            $end=mktime(23,59,59,date('m',$times),date('d',$times)-date('w',$times)+7-7,date('Y',$times));
        }elseif($type==3){
            $begin=mktime(0,0,0,date('m',$times)-1,1,date('Y',$times));
            $end=mktime(0,0,0,date('m',$times),1,date('Y',$times))-1;
        }
        return array('begin'=>$begin,'end'=>$end);
    }
    
    /**
     * 获取对应比赛
     * @param $match_id
     * @param $game_id
     * @param $play_time
     * @param $team_home_id
     * @param $team_guest_id
     * @param $game_type
     * @param $md5
     */
    public function getPlayInfo($match_id,$game_id,$play_time,$team_home_id,$team_guest_id,$game_type,$md5){
        $play_info = array();
        if($md5){
            $play_info = \think\Db::name('play')->where(array("md5_play"=>$md5))->find(); 
        }
        
        $is_change = 0;
        if(!$play_info){//比赛时间偏移10分钟，188数据与乐盈网站时间有偏差
            $etime = $play_time + 10*60;
            $btime = $etime - 20*60;
            $play_info = \think\Db::name('play')->where(["game_type"=>$game_type,"game_id"=>$game_id,"match_id"=>$match_id,"play_time"=>[[">=",$btime],["<=",$etime]],"team_home_id"=>$team_home_id,"team_guest_id"=>$team_guest_id])->find();
        }
        if(!$play_info){//188主客队可能与乐盈主客队相反
            $etime = $play_time + 10*60;
            $btime = $etime - 20*60;
            $play_info = \think\Db::name('play')->where(["game_type"=>$game_type,"game_id"=>$game_id,"match_id"=>$match_id,"play_time"=>[[">=",$btime],["<=",$etime]],"team_home_id"=>$team_guest_id,"team_guest_id"=>$team_home_id])->find();
            if($play_info){
                $is_change = 1;
            }
        }
        //echo \think\Db::name('play')->getlastsql()."\n";
        return array("play_info"=>$play_info,"is_change"=>$is_change);
    }
    
    public function logTxt($txt){
        $this->console($txt);
        /*
        $open = fopen("../runtime/log/crontab.log", "a");
        fwrite($open, $txt);
        fclose($open);*/
    }
    
    //代理
    public function cproxy(){
        //$proxy = array("ip"=>"123.59.85.220","port"=>"443");
        //$proxy = array("ip"=>"proxy.abuyun.com","port"=>"9020","username"=>"HF146ZAV1L65221D","password"=>"ACA894417EFADBB7");
        $proxy = config("proxy");
        return $proxy["proxy"];
    }

    public function curl($url,$postData = [],$ref = '',$proxy = [],$header = [],$is_proxy = 0){
        //$this->console($url);
        $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
        $opts = [];
        if($header){
            $_ = $header;
        }else{
            $_[] =  'User-Agent:'.$agent;
        }
        $opts[CURLOPT_HTTPHEADER] = $_;
        $opts[CURLOPT_REFERER] = $ref;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_USERAGENT] = $agent;
        $opts[CURLOPT_RETURNTRANSFER] = true;//是否将结果返回
        $opts[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 30;

        if($is_proxy){
            if(!$proxy){
                $proxy = $this->cproxy();
            }
            $opts[CURLOPT_PROXY] = $proxy['ip'];
            $opts[CURLOPT_PROXYPORT] = $proxy['port'];
            if(isset($proxy["username"]) && isset($proxy["password"])){
                $opts[CURLOPT_PROXYUSERPWD] = $proxy["username"].":".$proxy["password"];
            }
        }

        if($postData){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $postData;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $content = curl_exec($ch);
        if($content === false){
            //写入自动运行错误表
            $info = \think\Db::name("crontab_log")->where(["url"=>$url,"is_read"=>0])->find();
            if(!$info){
                $error_data = array();
                $error_data["url"] = $url;
                $error_data["content"] = curl_error($ch);
                $error_data["is_read"] = 0;
                $error_data["create_time"] = time();
                \think\Db::name("crontab_log")->insert($error_data);
            }
            echo "CURL ERROR:".(curl_error($ch))."</br>";
            return false;
        }
        curl_close($ch);
        return $content;
    }
    
    //比赛对应擂台状态
    public function checkArenaStatusByPlayStatus($play_id,$play_status){
        if($play_status == PLAT_STATUS_START || $play_status == PLAT_STATUS_INTERMISSION){ //比赛开始、中场休息
            $this->updateArenaStatus($play_id, ARENA_PLAY,$play_status);
        }elseif($play_status == PLAT_STATUS_END){ //比赛结束
            $this->updateArenaStatus($play_id, ARENA_END,$play_status);
        }elseif($play_status == PLAT_STATUS_EXC){ //比赛延期
            $this->updateArenaStatus($play_id, ARENA_START,$play_status);
        }elseif($play_status == PLAT_STATUS_SUSP || $play_status == PLAT_STATUS_CUT){ //比赛取消、腰斩
            $this->updateArenaStatus($play_id, ARENA_SEAL,$play_status);
        }elseif($play_status == PLAT_STATUS_WAIT){ //比赛待定
            $this->updateArenaStatus($play_id, ARENA_SEAL,$play_status);
        }elseif($play_status == PLAT_STATUS_NOT_START){ //比赛未开始
            $this->updateArenaStatus($play_id, ARENA_START,$play_status);
        }
    }
    
    //更新对应比赛的擂台状态
    public function updateArenaStatus($play_id,$status,$platStatus){
        $playSrv = new \library\service\Play();
        $playSrv->upCache($play_id);
        $playSrv->cacheTeams($play_id);
        $data = [];
        $data["status"] = $status;
        //$arena_list = \think\Db::name('arena')->where(["play_id"=>$play_id,"status"=>["not in",[ARENA_DIS,ARENA_DEL]]])->select();
        //\think\Db::name('arena')->where(["play_id"=>$play_id,"status"=>["not in",[ARENA_DIS,ARENA_DEL]]])->update($data);
        if(in_array($platStatus,[PLAT_STATUS_END,PLAT_STATUS_CUT])){
            \think\Db::name('arena')->where(["play_id" => $play_id, "status" => ["in", [ARENA_START, ARENA_END, ARENA_PLAY,ARENA_SEAL]]])->update($data);
        }elseif(in_array($platStatus,[PLAT_STATUS_WAIT])) {
            \think\Db::name('arena')->where(["play_id" => $play_id, "status" => ["in", [ARENA_START, ARENA_END, ARENA_PLAY]]])->update($data);
        }else {
            \think\Db::name('arena')->where(["play_id" => $play_id, "status" => ["in", [ARENA_START, ARENA_END, ARENA_PLAY]]])->update($data);
        }
        $arenaSrv = new \library\service\Arena();
        $arenaSrv->cacheArenaByPlay($play_id);
        /*if($status == ARENA_DIS ){//取消擂台,退还本金及投注的钱
            foreach ($arena_list as $arena ){
                $arenaSvr = new \library\service\Arena($arena["id"],SYS_USER_ID);
                $arenaSvr->disabled($arena["id"]);
            }
        }elseif($status == ARENA_DEL){//删除擂台,退还本金及投注的钱
            foreach ($arena_list as $arena ){
                $arenaSvr = new \library\service\Arena($arena["id"],SYS_USER_ID);
                $arenaSvr->del($arena["id"]);
            }
        }*/
    }
    
    //符号替换
    public function replaceStr($str){
        $dbc = array('（','）','【','】','　','０' , '１' , '２' , '３' , '４' ,'５' , '６' , '７' , '８' , '９' , 'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
                    'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,'Ｕ' , 'Ｖ' , 'Ｗ', 'Ｘ' , 'Ｙ' ,
                    'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' , 'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' , 'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
                    'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' , 'ｙ' , 'ｚ' , '－' , '　' , '：' , '．' , '，' , '／', '％' , '＃' , '！' , '＠' , '＆' , '（' , '）' ,
                    '＜' , '＞' , '＂' , '＇' , '？' , '［' , '］' , '｛' , '｝' , '＼' ,'｜' , '＋' , '＝' , '＿' , '＾' ,'￥' , '￣' , '｀');//全角
        $sbc = array('(',')','[',']',' ','0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
                    'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
                    't', 'u', 'v', 'w', 'x', 'y', 'z', '-', ' ', ':', '.', ',', '/', '%', '#', '!', '@', '&', '(', ')', '<', '>', '"', '\'','?', '[', ']', '{', '}', '\\',
                    '|', '+', '=', '_', '^', '$', '~', '`');//半角
        
        return str_replace($dbc, $sbc, $str);
    }
    
    //比赛对应赛事
    public function getMatchInfo($match_name,$game_type,$match_id,$md5_match,$bgcolor="",$game_id=""){
        $match_info = "";
        if($md5_match)
            $match_info = \think\Db::name('match')->where("md5_match",$md5_match)->find();
        if(!$match_info){
            $where = [];
            $where["game_type"] = $game_type;
            if($game_id){
                $where["game_id"] = $game_id;
            }
            $match_info = \think\Db::name('match')->where($where)->where('name|alias','eq',$match_name)->find();
            if($match_info && $md5_match && $match_info["md5_match"] == ""){
                \think\Db::name('match')->where("id",$match_info["id"])->update(array("md5_match"=>$md5_match));
                model("admin/match")->upCacheOnly($match_info["id"]);
            }
        }
        if(!$match_info){//如果不存在对应赛事，则先添加赛事
            $match_data = array();
            $match_data["country_id"] = 1;
            $match_data["game_type"] = $game_type;
            $match_data["name"] = $match_name;
            $match_data["md5_match"] = $md5_match;
            $match_data["game_id"] = $game_id;
            $mid = \think\Db::name('match')->insert($match_data,false,true);
            model("admin/match")->upCacheOnly($mid);
            $match_info = getMatch($mid);
        }
        if($match_info["bgcolor"]=='' && $bgcolor != ""){//更新颜色
            \think\Db::name('match')->where("id",$match_info["id"])->update(array("bgcolor"=>$bgcolor));
            model("admin/match")->upCacheOnly($match_info["id"]);
            $match_info = getMatch($match_info["id"]);
        }
        return $match_info;
    }
    
    //下载图片
    public function saveImg($url,$is_proxy=0){
        if(!strstr($url, "nopic")){
            $urlData = pathinfo($url);
            $is_https = 0;
            if(strstr($url, "https://")){
                $is_https = 1;
            }
            $save_src = "assets/attach/team_logo/";
            $md5_data = md5($url);
            $md5_path = substr($md5_data,0,2)."/".substr($md5_data,2,2)."/".substr($md5_data,4,2)."/";
            //$img = file_get_contents($url);
            $img = $this->getImg($url,$is_proxy,$is_https);
            
            $imgType = "jpg";
            if(isset($urlData["extension"]) && $urlData["extension"]){
                if ($urlData["extension"] == 'gif'){
                    $imgType = "png";
                }else{
                    $imgType = $urlData["extension"];
                }
            }
            $new_img = rand(100,999).time().rand(100,999).".".$imgType;
            if(!file_exists($save_src.$md5_path))
                mkdir($save_src.$md5_path,0777,true);
            if($img){
                //file_put_contents($save_src.$md5_path.$new_img, $img);
                $fp= @fopen($save_src.$md5_path.$new_img,"a"); 
                fwrite($fp,$img);
                return "team_logo/".$md5_path.$new_img;
            }else{
                return '';
            }
        }else{
            return '';
        }
    
    }
    
    public function getImg($url,$is_proxy = 0,$is_https=0){
        $ch = curl_init ();
        if($is_proxy){
            $proxy = config("proxy.proxy");
            curl_setopt ( $ch, CURLOPT_PROXY, $proxy['ip'] );
            curl_setopt ( $ch, CURLOPT_PROXYPORT, $proxy['port'] );
            if(isset($proxy["username"]) && isset($proxy["password"])){
                curl_setopt ( $ch, CURLOPT_PROXYUSERPWD, $proxy["username"].":".$proxy["password"] );
            }
        }
        if($is_https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6);
        }
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
    
    
    /*//插入odds表
    public function insertOdds($data,$rules,$play_id,$gameType,$loop=1,$company=8){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["play_id"] = $play_id;
        $odds_json["game_type"] = $gameType;
        $odds_json["odds_company_id"] = $company;
        $odds_json["loop"] = $loop;
        $odds_json["rules_type"] = $rules;
        $odds_json["odds"] = json_encode($data);
        $odds_json["create_time"] = time();
        $odds_json["update_time"] = time();
        
        $odds_list_id = \think\Db::name('odds')->insert($odds_json, false, true , null);
        return $odds_list_id;
    }*/
    
    /*public function insertOddsDetail($data,$odds_id,$is_array=0){
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
    
    public function updateOdds($data,$id){
        $odds_json = array();
        $odds_json["md5"] = md5(json_encode($data));
        $odds_json["odds"] = json_encode($data);
        $odds_json["update_time"] = time();
    
        \think\Db::name('odds')->where("id",$id)->update($odds_json);
    }*/

}