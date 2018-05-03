<?php

namespace app\admin\controller;
use app\admin\logic\Basic;
use app\admin\logic\Upload;
use library\service\Data;
use library\service\sms\Yunpian;
use think\Config;
use think\Db;

class Misc extends Basic{

    public function __construct(){
        parent::__construct();
    }

    public function upload(){
        $this->view->engine->layout(false);
        $uploadLimit = 9999;
        $multi = input('get.multi');
        $type = input('get.type');
        $auto = input('get.auto');
        $iframe = input('get.iframe');
        $callback = input('get.callback');
        if($type == 'image' || $type == 'img') {
            $ext = "*.jpg;*.jpeg;*.gif;*.png";
        }else{
            $ext = config("system.upload_backstage_exts");
            $ext = "*." . str_replace("|", ";*.", $ext);
        }
        if(!floatval($multi)){$uploadLimit = 1;}
        $this->assign('multi',$multi);
        $this->assign('type',$type);
        $this->assign('auto',$auto);
        $this->assign('iframe',$iframe);
        $this->assign('callback',$callback);
        $this->assign('ext',$ext);
        $this->assign('uploadLimit',$uploadLimit);
        $this->assign('upload_size',config('system.upload_backstage_size'));
        $unID = \org\Stringnew::keyGen();
        $this->assign('unID','upload_'.$unID);
        return $this->fetch();
    }

    public function Ueditor(){
        $action = input('get.ue_action');
        //$callback = input('get.callback');
        switch ($action) {
            case 'config':
                $file = CONF_PATH."ueditor_config.json";
                $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($file)), true);
                $config['imageFieldName'] = 'file_post_name';
                $config['scrawlFieldName'] = 'file_post_name';
                $config['catcherFieldName'] = 'file_post_name';
                $config['fileFieldName'] = 'file_post_name';
                $result = $config;
                break;
            case 'uploadimage';
                set_time_limit(0);
                $upload = new Upload('file_post_name');
                if(false == $result = $upload->save()){
                    return json(['code' => 0,'state' => 'ERROR','msg' => $upload->getError()]);
                }else{
                    $result['state'] = 'SUCCESS';
                    $result['type'] = ".{$result['ext']}";
                    return json($result);
                }
        }
        return json($result);
    }


    public function uploadsave(){
        //{"code":1,"msg":"站点配置更新成功","data":"","url":"http:\/\/cp.stuif.cn\/config\/system.html","wait":3}
        set_time_limit(0);
        $filePostName = $this->request->param('file_post_name');
        $upload = new Upload($filePostName);
        if(false == $result = $upload->save()){
            return json(['code' => 0,'msg' => $upload->getError()]);
        }else{
            return json(['code' => 1,'msg' => '上传成功','data' => $result]);
        }
    }

    public function uploadNetImage(){
        set_time_limit(0);
        $exts = ['jpg','png','gif',"jpeg"];
        $saveserver = input("saveserver");
        $url = input("url");
        $urlInfo = pathinfo($url);
        $ext = isset($urlInfo['extension']) ? $urlInfo['extension'] : "jpg";

        if(!$saveserver){
            $result = [
                'url' => $url,
                'ext' => $ext,
                'original' => $urlInfo['basename'],
                'name' => $urlInfo['basename'],
                'path' => $url,
                'upload_type' => "remote",
            ];
            return json(['code' => 1,'msg' => '上传成功','data' => $result]);
        }

        if(!in_array($ext,$exts)){
            return $this->error("无效网络图片信息");
        }
        $upload = new Upload();
        if(false == $result = $upload->downImg($url,$ext)){
            return json(['code' => 0,'msg' => $upload->getError()]);
        }else{
            return json(['code' => 1,'msg' => '上传成功','data' => $result]);
        }



        //var_dump($urlInfo);

    }

    /**
     * 比赛游戏（电竞）弹窗，非小游戏
     */
    public function select_game(){
        $name = input('name');
        $item_id = input('item_id/d');
        $type = input('type');
        if($name){//'game_type' => $item_id,
            $where = ['status' => STATUS_ENABLED,'name|alias' => ['like',"%{$name}%"]];
            if($item_id){
                $where['game_type'] = $item_id;
            }
            $lists = Db::name('game')->field('id,name,alias')->where($where)->limit(20)->select();
            $this->assign("lists",$lists);
        }
        $this->assign("item_id",$item_id);
        $this->assign("name",$name);
        $this->assign("type",$type);
        return $this->fetch('select_game');
    }
    /**
     * 赛事弹窗
     */
    public function select_match(){
        $name = input('name');
        $item_id = input('item_id/d');
        $type = input('type');
        $this->assign("type",$type);
        if($name){//'game_type' => $item_id,
            $where = ['name' => ['like',"%{$name}%"]];
            if($item_id){
                $where['game_type'] = $item_id;
            }
            $lists = Db::name('match')->field('id,name')->where($where)->limit(20)->select();
            $this->assign("lists",$lists);
        }
        $this->assign("item_id",$item_id);
        $this->assign("name",$name);
        $this->assign("type",$type);
        return $this->fetch('select_match');
    }
    /**
     * 队伍弹窗
     */
    public function select_team(){
        $name = input('name');
        $type = input('type');
        $item_id = input('item_id/d');
        if($name){//'game_type' => $item_id,
            $where = ['name' => ['like',"%{$name}%"]];
            if($item_id){
                $where['game_type'] = $item_id;
            }
            $lists = Db::name('team')->field('id,name')->where($where)->limit(20)->select();
            $this->assign("lists",$lists);
        }
        $this->assign("item_id",$item_id);
        $this->assign("name",$name);
        $this->assign("type",$type);
        return $this->fetch('select_team');
    }
    /**
     * 某场比赛赔率列表,赔率监控
     */
    public function odds(){
        $playId = input("play_id/d");
        $rules_type = input("rules_type/d");
        $play = (new \library\service\Play())->getPlay($playId);
        if(!$play){
            return $this->retErr("play.odds",10013);
        }
        $teams = $play['game_type'] == GAME_TYPE_FOOTBALL ? [] : (new \library\service\Play())->getTeams($playId);
        $company = cache('odds_company');
        $ruleSvr = (new  \library\service\Rule())->factory($play['game_type']);
        $oddsList = Db::name('odds')->where(['play_id' => $playId,'rules_type' => $rules_type])->select();
        $data = [];
        if($oddsList){
            foreach($oddsList as $val){
                $odds = @json_decode($val['odds'],true);
                $oddsInit = $ruleSvr->parseOddsWords($odds['init'],$val['rules_id'],$teams,$val['rules_type']);
                $oddsTime = $ruleSvr->parseOddsWords($odds['time'],$val['rules_id'],$teams,$val['rules_type']);
                $temp = [];
                foreach ($oddsTime as $k1 => $v1) {
                    unset($v1['win_money']);
                    unset($v1['money']);
                    unset($v1['money_total']);
                    $v1['odds_init'] = $oddsInit[$k1]['odds']; //初始赔率
                    $oddsTime[$k1] = $v1;
                }
                $data[] = [
                    'id' => $val['id'],
                    'company_name' => isset($company[$val['odds_company_id']]) ? $company[$val['odds_company_id']]['name'] : '',
                    'odds' => array_values($oddsTime)//array_values($odds),
                ];
            }
        }
        return $this->result($data);

    }


//自动运行日志
    public function crontabLog(){
        $param = input("param.");
        $limit = 10;
        $list = \think\Db::name('crontab_log')->where(["is_read"=>0])->order("create_time")->paginate($limit,false,['query' => $param]);
        \think\Db::name('crontab_log')->where(["is_read"=>0])->update(["is_read"=>"1"]);
        $this->assign("lists",$list);
        return $this->fetch();
    }
    public function crontabLogTotal(){
        $total = \think\Db::name('crontab_log')->where(["is_read"=>0])->count();
        return $this->success('','',['total' => $total]);
    }
    public function arena_stat(){
        $where = [];
        $type = input( 'type' );
        $btime = input( 'start' );
        $etime = input( 'end' );
        if(!$btime || !strtotime($btime)){
            $btime = strtotime("-30 days");
        }else{
            $btime = strtotime($btime);
        }

        if(!$etime || !strtotime($etime)){
            $etime =  time();
        }else{
            $etime =  strtotime($etime );
        }
        $where['s_date'] = [['egt',$btime],['elt',$etime]];
        $where['item_id'] = 0;
        $result = [];
        $result[ 'data' ][ 0 ][ 'name' ] = '房间押金';
        $result[ 'data' ][ 0 ][ 'type' ] = 'spline';
        $result[ 'data' ][ 1 ][ 'name' ] = '投注金额';
        $result[ 'data' ][ 1 ][ 'type' ] = 'spline';
        $filed = 'arena_deposit as t1,bet_money as t2,s_date';
        if($type == 2){
            $filed = 'arena_total as t1,bet_total as t2,s_date';
            $result[ 'data' ][ 0 ][ 'name' ] = '房间数';
            $result[ 'data' ][ 1 ][ 'name' ] = '投注数';
        }elseif($type == 3){
            $filed = 'arena_brok as t1,bet_bork as t2,s_date';
            $result[ 'data' ][ 0 ][ 'name' ] = '房间佣金';
            $result[ 'data' ][ 1 ][ 'name' ] = '投注佣金';
        }
        $lists = modelN('stat_user_arena')->field($filed)->where($where)->order('s_date asc')->select();
        $diff = intval( ( $etime - $btime ) / 86400 );
        for ( $i = 1; $i < $diff; $i ++ ) {
            $t =  date( 'Y-m-d',$btime + ( $i * 86400 ));
            $result['date'][$t] = $t;
            $result['data'][0]['data'][$t] = 0;
            $result['data'][1]['data'][$t] = 0;
        }
        if($lists){
            foreach($lists as $val){
                $d = date( "Y-m-d", $val[ 's_date' ] );
                $result['date'][$d] = $d;
                $result['data'][0]['data'][$d] = (double)$val['t1'];
                $result['data'][1]['data'][$d] = (double)$val['t2'];
            }
        }
        $result[ 'start' ] = date( "Y-m-d", $btime );
        $result[ 'end' ] = date( "Y-m-d", $etime );
        $result['date'] = array_values($result['date']);
        $result['data'][0]['data'] = array_values($result['data'][0]['data']);
        $result['data'][1]['data'] = array_values($result['data'][1]['data']);
        return $this->result($result,1,'OK','json');
    }
}