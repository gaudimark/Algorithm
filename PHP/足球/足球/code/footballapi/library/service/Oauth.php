<?php
namespace library\service;
use org\Crypt;
use think\Request;
use think\Cache;

class Oauth{

    public $expire = 86400; //有效时间，秒
    public function __construct(){
    }

    public function login($user){
        $userId = $user['id'];
        $token = $this->getTokenByUserId($userId);
        if($token){
            $this->delCache($token);
        }
        $token = $this->getToken($user);
        $user['time'] = time();
        $this->setCache($token,$user);
/*        cache("oauth_{$token}",$user,$this->expire);
        \think\Cache::hSet('user_list_userId',$userId,$token,$this->expire);
        \think\Cache::hSet('user_list_token',$token,$userId,$this->expire);
        \think\Cache::hSet('user_list_uuid',$token,$user['uuid'],$this->expire);
        \think\Cache::hSet('user_pad_info',$token,$user['pad_info'],$this->expire);*/
        return $token;
    }

    /**
     * 获取当前登录用户信息
     * @param $token
     */
    public function getUser($token){
        if(false !== $this->checkToken($token)){
            return cache("oauth_{$token}");
        }
        return false;
    }

    public function getPad($uuid){
        return Cache::hGet('user_pad_info',$uuid);
    }

    public function getTokenByUserId($userId){
        return Cache::hGet('user_list_userId',$userId);
    }

    public function getUserIdByToken($token){
        return Cache::hGet('user_list_token',$token);
    }
    public function getUuidByToken($token){
        return Cache::hGet('user_list_uuid',$token);
    }

    /**
     * token检查
     * @param $token
     * @return bool
     */
    public function checkToken($token){
        if(!$token){return false;}
        $time =  (int)\think\Cache::hGet('user_list_user_expire',$token);
        if(time() - $time > $this->expire){
            $this->delCache($token);
            return false;
        }
        //防止过期
        \think\Cache::hSet('user_list_user_expire',$token,time());
        return true;
    }

    public function logout($token){
        \think\Cache::hDel('user_list_token',$token);
        \think\Cache::hDel('user_list_uuid',$token);
        \think\Cache::hDel('user_pad_info',$token);
        \think\Cache::hDel('user_pad_uuid',$token);
        \think\Cache::hDel('user_list_user_expire',$token);
    }

    private function setCache($token,$user){
        $userId = $user['id'];
        cache("oauth_{$token}",$user);
        \think\Cache::hSet('user_list_user_expire',$token,time());
        \think\Cache::hSet('user_list_userId',$userId,$token);
        \think\Cache::hSet('user_list_token',$token,$userId);
        \think\Cache::hSet('user_list_uuid',$token,$user['uuid']);
        \think\Cache::hSet('user_pad_info',$user['uuid'],$user['pad_info']);
        \think\Cache::hSet('user_pad_uuid',$user['uuid'],$userId);
    }

    private function delCache($token){
        $userId = $this->getTokenByUserId($token);
        \think\Cache::hDel('user_list_token',$token);
        \think\Cache::hDel('user_list_uuid',$token);
        \think\Cache::hDel('user_pad_info',$token);
        \think\Cache::hDel('user_pad_uuid',$token);
        \think\Cache::hDel('user_list_user_expire',$token);
        if($userId){
            cache("oauth_{$token}",null);
            \think\Cache::hDel('user_list',$userId);
            \think\Cache::hDel('user_list_userId',$userId);
        }
    }

    private function getToken($user){
        $data = [
            'user_id' => $user['id'],
            'uuid' => $user['uuid'],
            'imei' => $user['imei'],
            'time' => microtime(),
            'client_ip' => Request::instance()->ip(),
        ];
        return md5(Crypt::encrypt(json_encode($data),DEFAULT_KEY,0,'url'));
    }

}