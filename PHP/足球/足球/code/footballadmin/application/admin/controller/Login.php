<?php

namespace app\admin\controller;
use think\Controller;
use think\Validate;

class Login extends Controller{

    public function index(){

        if($this->request->isPost()){
            $model = model("manager");
            $token = input("post.__token__");
            $username = input("post.username");
            $password = input("post.password");

            if(!Validate::is($token,'token',input())){
                //return $this->error('登录失败,令牌数据无效','',['csrf' => 1]);
            }
            if($model->doLogin($username,$password)){
                return $this->success("登录成功",url('index/dashboard'));
            }else{
                return $this->error($model->getError());
            }
        }

        return $this->fetch();
    }

    public function logout(){
        model("manager")->logout();
        $this->assign("msg",'你已成功安全退出');
        $this->assign("url",url('login/index'));
        $this->assign("wait",3);
        return $this->fetch();
    }
}