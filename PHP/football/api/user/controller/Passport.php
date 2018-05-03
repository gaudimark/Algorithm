<?php
namespace app\user\controller ;
use app\library\logic\Safe;
use library\service\Forms;
use library\service\Limit;
use library\service\Oauth;
use library\service\Socket;
use library\service\User;
use think\Db;
use think\Request;

class Passport extends Safe{
    private $svr = null;
    public function __construct(){
        parent::__construct();
        $this->svr = new \library\service\Passport();
        $this->svr->token = $this->token;
    }

    public function login(){
        if($this->request->isPost()){
            $uuid = input('post.uuid');
            $userName = input('userName','');
            $phone = input('phone','');
            $cpid = input('cpid','');
            $location = input('location','');
            $sexMode = input('sexMode','');
            $imei = input('imei','');
            $key = input('gamekey','');
            $extra = input('extra','');//!$key ||
            if(!$imei || !$uuid){
                return $this->retErr('user.login',20050);
            }
            if($userName == 'undefined' || !$userName){$userName = $cpid;}
            if($user = $this->svr->doLogin([
                'uuid' => $uuid
                ,'nickname' => $userName
                //,'mobile' => $phone
                ,'cpid' => $cpid
                ,'location' => $location
                ,'sex' => $sexMode
                ,'imei' => $imei
                ,'extra' => $extra
                ,'key' => $key
                ,'ip' => $this->request->ip()
            ])){
                return $this->retSucc('user.login',$user,9999);
            }
            $err = $this->svr->getError();
            return $this->retErr('user.login',$err['code'],$err['vars']);
        }
        return $this->retErr('user.login',10000);
    }

    public function logout(){
        if($this->request->isPost()){
            $oauth = new Oauth();
            $userId = $this->getUserId('sys');
            $oauth->logout($this->token);
            (new \library\service\Pad([]))->logout($userId);
            return $this->retSucc('user.logout',[],9999);
        }
        return $this->retErr('user.logout',10000);
    }

}