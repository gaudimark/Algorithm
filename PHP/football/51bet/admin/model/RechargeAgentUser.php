<?php

namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;
class RechargeAgentUser extends BasicModel {
    protected $name = "recharge_agent_user";
    public function _beforeInsert($model){
        $data = $model->getData();
        $user = $this->where(['username' => $data['username']])->find();
        if($user){
            $this->error = "代理{$data['username']}已存在";
            return false;
        }

        if(!isset($data['password']) || !$data['password']){
            $this->error = "请输入密码";
            return false;
        }
        $data['salt'] = \org\Stringnew::randNumber(111111,999999);
        $data['password'] = $this->parsePassword($data['password'],$data['salt']);
        $model->data($data);
        return true;
    }

    public function _beforeUpdate($model){
        $data = $model->getData();
        unset($data['username']);
        if(isset($data['password']) && $data['password']){
            $salt = \org\Stringnew::randNumber(111111,999999);
            $data['salt'] = $salt;
            $newPassword = $this->parsePassword($data['password'],$salt);
            $data['password'] = $newPassword;
        }else{
            unset($data['password']);
        }
        $model->data($data,true); //标记记录字段
        $model->data($data);//更新数据
        return true;
    }

    public function parsePassword($password,$salt){
        $pwd = md5($password);
        $s = md5($salt.$pwd);
        return md5($s.$password.md5($salt).$pwd);
    }
}

RechargeAgentUser::event('before_update',function($model){
    return $model->_beforeUpdate($model);
});
RechargeAgentUser::event('before_insert',function($model){
    return $model->_beforeInsert($model);
});