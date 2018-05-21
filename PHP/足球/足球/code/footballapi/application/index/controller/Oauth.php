<?php
namespace app\index\controller;
use app\library\logic\Basic;
use think\Controller;

class Oauth extends Basic{
    private $appId = '280kkl8GsvQKttEE3eakTCpoDwl9MveY';
    private $appSecret = 'lMaal2zUVFT96Fhji2mhuEtPYFewJJKv';

    /**
     * 获取签名Token
     */
    public function access_token(){
        $platform = 'h5';
        $ref = input("ref");
        $ditch = input("ditch");
        $deviceType = input('device_type');
        $time = input('time');
        $sign = input('sign');
        $oauth = (new \library\service\Oauth());
        if(!$oauth->checkSign($sign,$this->appSecret,$time,$ditch,$deviceType)){
            return $this->retErr('token',10004);
        }
        //$sign = $oauth->getSignature($ticket, $this->appId, $this->appSecret);
        //$oauth = (new \library\service\Oauth());
        $info = $oauth->getToken($this->appId,$platform, $ref, '','',$ditch,$deviceType);
        session("text_api_info",$info);
        $this->token = $info['access_token'];
        return $this->retSucc('token',['access_token' => $info['access_token']]);


        //if($this->request->isPost()){
            $appId = input('app_id'); //appKey
            $sign = input('sign'); //签名字符串
            $scope = input('post.scope');
            $state = input('post.state');
            if (!$appId || !$sign){
                return $this->result(null, 10000, config("error.10000"));
            }
            $oauth = (new \library\service\Oauth());
            if (false === $info = $oauth->getToken($appId, $sign, $scope, $state)){
                return $this->result(null, 10001, config("error.10001"));
            }
            return $this->result($info);
       // }
        //return $this->result(null, 10000, config("error.10000"));
    }

    public function sign(){
        $oauth = (new \library\service\Oauth());
        $ticket = $oauth->getTicket();
        $sign = $oauth->getSignature($ticket,$this->appId,$this->appSecret);
        return $this->result($sign);
    }
}