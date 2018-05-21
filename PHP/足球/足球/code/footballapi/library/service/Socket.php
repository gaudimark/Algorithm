<?php
namespace library\service;

use think\Cache;
use think\Db;
use think\Exception;
use Workerman\Lib\Timer;
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Lib\Gateway;
use \Workerman\Autoloader;
use \Workerman\Protocols\Websocket;


class Socket{
    private $worker = null;
    private static $db = null;
    private static $gameSvrGroupId = 999; // 小游戏特定socket组
    public function __construct(){
        Gateway::$registerAddress = config('socket.register_address');
    }

    /**
     * token失效时，关闭连接客户端
     * @param $token
     */
    public function closeClient($clientId){
        Gateway::closeClient($clientId);

    }

    /**
     * 将客户端ID与用户ID绑定
     */
    public function bindUserId($clientId,$userId,$token){
        Gateway::bindUid($clientId,$userId);
        Gateway::updateSession($clientId,[
            'user_id' => $userId,
            'time' => time(),
            'token' => $token,
        ]);
        $ret = Gateway::getSession($clientId);
        if($ret && isset($ret['auth_timer_id'])){
            Timer::del($ret['auth_timer_id']);
        }
        self::log("{$userId} Bind {$clientId}");
    }

    public function kickGameUser($userId){
        //绑定用户，踢出游戏中的当前用户
        $data = $this->parseData(self::getMark('socket.to_send_group'),['type' => 'kick', 'user_id' => $userId]);
        Gateway::sendToGroup(self::$gameSvrGroupId,$data);
    }

    /**
     * 将客户端ID与用户ID解除绑定
     */
    public function unbindUid($userId){
        $clientIds = Gateway::getClientIdByUid($userId);
        if($clientIds){
            foreach( $clientIds as $clientId){
                Gateway::unbindUid($clientId,$userId);
                Gateway::closeClient($clientId); //关闭连接
            }
        }
    }

    public function logout($userId){
        $this->sendToUid($userId, 'off', 'socket.to_off'); //发送消息到客户端。服务器主动断开，不需要重新连接
        $clientIds = Gateway::getClientIdByUid($userId);
        if($clientIds){
            foreach($clientIds as $clientId){
                Gateway::unbindUid($clientId,$userId);
                Gateway::closeClient($clientId); //关闭连接
            }
        }
    }

    /**
     * 将客户端ID加入组
     */
    public function joinGroup($group,$userId){
        $clientIds = Gateway::getClientIdByUid($userId);
        if($clientIds){
            foreach( $clientIds as $clientId){
                Gateway::joinGroup($clientId,$group);
                Gateway::updateSession($clientId,['group_id' => $group,'time' => time()]);
            }
        }

    }

    /**
     * 将客户端ID加入组
     */
    public function joinGroupByClient($group,$clientId){
        Gateway::joinGroup($clientId,$group);
        Gateway::updateSession($clientId,['group_id' => $group]);
    }
    /**
     * 将客户端ID离开组
     */
    public function leaveGroup($group,$userId){
        $clientIds = Gateway::getClientIdByUid($userId);
        if($clientIds){
            foreach( $clientIds as $clientId){
                Gateway::leaveGroup($clientId,$group);
            }
        }
    }

    /**
     * 向指定用户发送消息
     * @param $userId
     * @param $message
     */
    public function sendToUid($userId,$message,$mark = 'socket.to_send_uid'){
        try{
            self::staticSendToUid($userId,$message,$mark);
        }catch (Exception $e){

        }
    }
    /**
     * 向指定用户发送消息
     * @param $userId
     * @param $message
     */
    public static function staticSendToUid($userId,$message,$mark = 'socket.to_send_uid'){
        $clientIds = Gateway::getClientIdByUid($userId);
        $data = self::parseDataStatic(self::getMark($mark),$message);
        //self::log("sendToUid \$userId=$userId,mark={$mark},{$data}");
        if($clientIds){
            foreach( $clientIds as $clientId){
                Gateway::sendToClient($clientId,$data);
            }
        }
    }

    /**
     * 向一个群组发送消息
     * @param $groupId
     * @param $message
     * @throws \Exception
     */
    public function sendToGroup($groupId,$message,$mark = 'socket.to_send_group'){
        if($mark == 'socket.to_all_notice' || $mark == 99997){
            $message = $this->parseMarqueeNoticeData($message,0);
        }
        Gateway::sendToGroup($groupId,$this->parseData(self::getMark($mark),$message));
    }

    public function sendToDitchGroup($ditchId,$message,$mark = 'socket.to_send_group'){
        if($mark == 'socket.to_all_notice' || $mark == 99997){
            $message = $this->parseMarqueeNoticeData($message,0);
        }
        $groupId = "ditch_{$ditchId}";
        Gateway::sendToGroup($groupId,$this->parseData(self::getMark($mark),$message));
    }

    public function sendToDitchClassifyGroup($ditchClassifyId,$message,$mark = 'socket.to_send_group'){
        if($mark == 'socket.to_all_notice' || $mark == 99997){
            $message = $this->parseMarqueeNoticeData($message,0);
        }
        $groupId = "ditch_classify_{$ditchClassifyId}";
        Gateway::sendToGroup($groupId,$this->parseData(self::getMark($mark),$message));
    }

    public function parseMarqueeNoticeData($message,$level = 0){
        return [
            'type' => '',
            'message' => $message,
            'msg_level' => $level,
            'msg_type' => 0,
            'delay_time' => 0,
        ];
    }

    /**
     * 向所有用户发送消息
     * @param $message
     * @throws \Exception
     */
    public function sendToAll($message,$mark = 'socket.to_send_all'){
        if($mark == 'socket.to_all_notice' || $mark == 99997){
            $message = $this->parseMarqueeNoticeData($message,0);
        }
        Gateway::sendToAll($this->parseData(self::getMark($mark),$message));
    }


    /**
     * 获取在线用户数
     */
    public function getOnlineCount(){
        return Gateway::getAllClientCount();
    }


    public function getOnlineList(){
        return Gateway::getAllClientSessions();
    }

    /**
     * 获取群组内在线用户数
     * @param $groupId
     * @return int
     */
    public function getGroupOnlineCount($groupId){
        return Gateway::getClientCountByGroup($groupId);
    }

    /**
     * 判断用户是否在线
     * @param $userId
     */
    public function checkUserOnline($userId){
        return Gateway::isUidOnline($userId);
    }

    static public function parseDataStatic($mark,$message){
        if(!is_numeric($mark)){
            $mark  = self::getMark($mark);
        }
        $result = [
            'code' => 0,
            'mark' => $mark,
            'msg'  => '',
            'time' => time(),
        ];
        $result['data'] = $message;
        return json_encode($result);
    }

    public function parseData($mark,$message){
        return $this->parseDataStatic($mark,$message);
    }

    public static function log($message){
        $fileName = date("Ymd").".txt";
        $dir = RUNTIME_PATH."log/socket/";
        @mkdir($dir,0777,true);
        if(is_array($message)){
            $message = @json_encode($message);
        }
        $message = "[".date("Y-m-d H:i:s")."]\t".$message."\r\n";
        //error_log($message,3,$dir.$fileName);
       // file_put_contents($dir.$fileName,$message);
    }

    static private function getMark($markCode){
        $markArr = config("mark");
        if(stripos($markCode,".") === false){
            $mark = $markArr[$markCode];
        }else{
            list($a,$b) = explode(".",$markCode);
            $mark = $markArr[$a][$b];
        }
        return $mark;
    }

    //发送用户金币变化
    public function userGold($userId,$gold){
        try {
            $this->sendToUid($userId, ['type' => 'gold', 'gold' => $gold], "socket.to_send_gold_change");
            self::log("sendToUid \$userId=$userId,".json_encode(['type' => 'gold', 'gold' => $gold]));
            //向小游戏组发送更新消息,扣金币不推给游戏
            if($gold > 0) {
                $data = $this->parseData(self::getMark('socket.to_send_group'), ['type' => 'gold', 'gold' => $gold, 'user_id' => $userId]);
                Gateway::sendToGroup(self::$gameSvrGroupId, $data);
            }
        }catch (Exception $e){
            self::log("sendToUid \$userId=$userId,".json_encode(['type' => 'gold', 'gold' => $gold]).",Error:".$e->getMessage());
        }
    }

    public function alert($userId,$message){
        try {
            $this->sendToUid($userId, ['type' => 'alert', 'message' => $message], "socket.alert");
        }catch (Exception $e){

        }
    }

    /**
     * 系统弹框
     * @param $message 系统消息内容
     * @param int $isStop 是否是停服消息
     */
    public function alertToAll($message,$isStop = 0){
        try {
            $data = $this->parseData(self::getMark('socket.alert'),[
                'message' => $message,
                'stop' => (int)$isStop,
            ]);
            self::log($data);
            Gateway::sendToAll($data);
        }catch (Exception $e){

        }
    }

    /**
     * 发送消息给指定在线用户
     * @param $message 系统消息内容
     * @param int $isStop 是否踢出用户
     */
    public function kickedUser($userId,$message,$isStop = 0){
        try {
            $data = $this->parseData(self::getMark('socket.alert'),[
                'message' => $message,
                'stop' => (int)$isStop,
            ]);
            Gateway::sendToUid($userId,$data);
        }catch (Exception $e){

        }
    }

}