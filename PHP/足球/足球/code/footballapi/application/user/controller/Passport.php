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
use think\Cache;
use think\Log;

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
            if(!$imei || !$uuid || !$key){
                return $this->retErr('user.login',20050);
            }
            config('GAME_KEY_FOOTBALL',$key);
            Cache::set('GAME_KEY_FOOTBALL',$key);
            Log::write('GAME_KEY :'.$key);
            if($userName == 'undefined' || !$userName){$userName = $cpid;}
            $user = $this->svr->doLogin([
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
            ]);
            Log::write('login $user :'.json_encode($user));
            if($user){
                //$user['pad_info']['gameId'] = $key;
                Cache::set('paduser_'.$user['token'],json_encode($user['pad_info']));
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
            Log::write('$userId :'.json_encode($userId));
            $oauth->logout($this->token);
            $info = Cache::get('paduser_'.$this->token);
            (new \library\service\Pad($info))->logout($userId);
            return $this->retSucc('user.logout',[],9999);
        }
        return $this->retErr('user.logout',10000);
    }

}