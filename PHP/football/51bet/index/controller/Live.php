<?php
namespace app\index\controller;
use library\service\Play;
use think\Controller;

class Live extends Controller{

    public function play(){
        $playId = input("play_id");
        $play = (new Play())->getPlay($playId);
        $live = [];
        $liveIframe = '';
        if(isset($play['live']) && $play['live']){
            $live = (new \library\service\Live())->parse($play['live']);
            if(!$live){
                $liveIframe = $play['live'];
            }
        }
        $this->assign("live",$live);
        $this->assign("live_iframe",$liveIframe);
        return $this->fetch();
    }
}