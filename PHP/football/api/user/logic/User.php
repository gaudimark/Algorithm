<?php
namespace app\user\logic;
use app\library\logic\Safe;
use library\service\Oauth;

class User extends Safe{
    public $myUserId = "";
    public function __construct(){
        parent::__construct();
        $this->myUserId = $this->getUserId('sys'); //当前登录用户
        $action = strtolower($this->request->action());
        $controller = strtolower($this->request->controller());
        if (!$this->checkLogin('sys') || !$this->myUserId){
            return $this->retErr("user.{$controller}_{$action}", 20040);
        }
        
    }
}