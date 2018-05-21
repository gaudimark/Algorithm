<?php

namespace library\service;

use org\Stringnew;
use think\Config;
use think\Db;
use think\Exception;
use think\Request;

class Passport {

    public $token = null;
    private $error = '';
    private $model = null;
    private $avatarDefault = 'avatar/1.png'; //默认头像地址

    public function __construct() {
        $this->model = Db::name('user');
    }

    //登录
    public function doLogin($userInfo = []) {
        if (!$userInfo) {
            $this->error = 10004;
            return false;
        }
        $user = $this->Register($userInfo);
        if ($user === false) {
            $this->error = 10027;
            return false;
        }
        //用户帐号检查
        if ($user['status'] != STATUS_ENABLED) {
            $this->error = 20005;
            return false;
        }
        //是否是机器人
        if ($user['has_robot']) {
            $this->error = 10005;
            return false;
        }
        //向PAD服务器发送用户金币请求
        $padResult = (new \library\service\Pad([
            'imei' => $userInfo['imei'],
            'uuid' => $userInfo['uuid'],
            'gameId' => PAD_GAME_ID
                ]))->getPonints();
        if (!$padResult) {
            $this->error = 10045;
            return false;
        }

        $last_login_time = time();
        $userUpData = [
            'has_online' => 1,
            'last_login_time' => $last_login_time,
            'last_login_ip' => $userInfo['ip'],
            'login_total' => ['exp', 'login_total+1'],
            'gold' => $padResult['account'],
            'nouseaccount' => 0,
        ];
        $this->model->where(['id' => $user['id']])->update($userUpData);
        Log::UserLog($user['id'], "登录成功", [
            'ip' => $userInfo['ip'],
            'imei' => $userInfo['imei']
                ], USER_LOG_LOGIN);
        unset($user['password']);
        unset($user['salt']);
        $user['pad_info'] = [
            'uuid' => $userInfo['uuid'],
            'imei' => $userInfo['imei'],
            'key' => $userInfo['key'],
        ];
        $user['gold'] = $padResult['account'];
        $user['token'] = (new Oauth())->login($user);
        if (!$user['nickname'] || $user['nickname'] == 'undefined') {
            $user['nickname'] = $user['cpid'];
        }
        (new User())->setCacheUser($user['id']);
        return $this->parseUserData($user);
    }

    /**
     * 注册
     * @param $userInfo
     */
    public function Register($userInfo) {
        $uuId = $userInfo['uuid'];
        $user = $this->model->where(['uuid' => $uuId])->find();
        if ($user) { //如果用户不存在
            return $user;
        }

        $userId = $this->model->insertGetId([
            'has_bind' => 1,
            'imei' => $userInfo['imei'],
            'uuid' => $userInfo['uuid'],
            'cpid' => $userInfo['cpid'],
            //'mobile' => $userInfo['mobile'],
            'nickname' => $userInfo['nickname'],
            'reg_ip' => $userInfo['ip'],
            'location' => $userInfo['location'],
            'sex' => $userInfo['sex'],
            'remarks' => $userInfo['extra'],
            'gold' => 0,
            'reg_time' => time(),
            'create_time' => time(),
            'update_time' => time(),
        ]);
        if ($userId) {
            return $this->model->where(['id' => $userId])->find();
        }
        return false;
    }

    public function parseUserData($data) {
        unset($data['id']);
        unset($data['has_robot']);
        unset($data['has_online']);
        unset($data['has_bind']);
        unset($data['reg_ip']);
        unset($data['reg_platform']);
        unset($data['login_total']);
        unset($data['last_login_ip']);
        unset($data['create_time']);
        unset($data['update_time']);
        return $data;
    }

    public function getError() {
        $error = $this->error;
        if (!is_array($error)) {
            return ['code' => $error, 'vars' => []];
        }
        return $error;
    }

}
