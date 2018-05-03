<?php

/**
 * socket推送接口
 */

namespace app\index\controller;
use app\library\logic\Safe;
use library\service\Socket;
use think\Db;

class Push extends Safe{
    private $socket = null;
    public function __construct(){
        parent::__construct();
        $this->socket = new Socket();
    }

    /**
     * 绑定用户ID
     * @return array
     */
    public function bind_uid(){
        $clientId = input("client_id");//用户客户端ID
        $userId = $this->getUserId(); //当前登录用户
        if(!$clientId){
            return $this->retErr('push.bind_uid',10004);
        }
        if(!$userId){
            return $this->retErr('push.bind_uid',10022);
        }
        $user = Db::name('user')->field('gold')->where(['id' => $userId])->find();
        $this->socket->bindUserId($clientId,$userId,$this->token);
        $this->socket->sendToUid($userId,['type' => 'bind','message' => 'OK']);


        $msg = [];
        return $this->retSucc('push.bind_uid',[
            'gold' => (double)$user['gold'],
        ],$msg);
    }

    /**
     * 用户解除绑定
     */
    public function unbind_uid(){
        $userId = $this->getUserId(); //当前登录用户
        if(!$userId){
            return $this->retErr('push.unbind_uid',10022);
        }
        $this->socket->sendToUid($userId,['type' => 'unbind','message' => lang(91001)]);
        $this->socket->unbindUid($userId);
        return $this->retSucc('push.unbind_uid',[],'91001');
    }


}