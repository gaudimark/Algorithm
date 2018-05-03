<?php
namespace app\library\logic;
use library\service\Misc;
use library\service\Oauth;
use library\service\Socket;
use think\Controller;
use think\Request;

class Safe extends Basic{

    public $domain = ''; //当前访问域名

    public function __construct(){
        parent::__construct();
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());
        if(!($controller == 'passport' && $action == 'login')){ //停服
            $this->checkToken();
        }
    }

    /**
     * 检查token
     */
    private function checkToken(){
        if($this->request->isPost()){
            $data = input('post.');
        }else{
            $data = input("get.");
        }
        if(!isset($data['token']) || !$data['token']){
            return $this->retErr('token_expire',10002);
        }
        $token = $data['token'];
        $oauth = (new \library\service\Oauth());
        $ret = $oauth->checkToken($token);
        if($ret === 'sso'){
            return $this->retErr('not_login',10003);
        }elseif(!$ret){
            return $this->retErr('token_expire',10002);
        }
        $this->token = $token;
        return true;
    }

    public function getUserId($_ = ''){
        $oauth = (new \library\service\Oauth());
        return $oauth->getUserIdByToken($this->token);
    }

    public function checkLogin($_ = ''){
        $userId = $this->getUserId();
        return $userId ? true : false;
    }


}