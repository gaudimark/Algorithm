<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static $db = null;
    private static $gameSvrGroupId = 999; // 小游戏特定socket组
    private static $svrPrivateKey = 'oaVHanF0Py*ZDq&i'; //服务器发送消息，私钥
    public static function onWorkerStart($worker){
        $host = '192.168.188.172';
        $port = '3306';
        $user = 'root';
        $password = 'ISUVjkg6Da1QrkBRNffV';
        $db_name = 'leitai';
        self::$db = new \Workerman\MySQL\Connection($host, $port, $user, $password, $db_name);
        self::log("WorkerStart");
    }


    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id){

        // 连接到来后，定时30秒关闭这个链接，需要60秒内发认证并删除定时器阻止关闭连接的执行
        $auth_timer_id = \Workerman\Lib\Timer::add(120, function($client_id){
            $ret = Gateway::getSession($client_id);
            if($ret && !isset($ret['user_id'])){
                $data = [
                    'code' => 0,
                    'mark' => 92999,
                    'msg'  => '',
                    'time' => time(),
                    'data' => 'off',
                ];
                Gateway::sendToClient($client_id,@json_encode($data));
                Gateway::closeClient($client_id);
            }

        }, array($client_id), false);

        // 向当前client_id发送数据
        $data = [
            'code' => 0,
            'mark' => 92001,
            'msg'  => '',
            'time' => time(),
            'data' => [
                'type' => 'connect',
                'client_id' => $client_id,
            ],
        ];
        Gateway::sendToClient($client_id,json_encode($data));
        Gateway::setSession($client_id,[
            'time' => time(),
            'auth_timer_id' => $auth_timer_id
        ]);
        self::log("ClientId:{$client_id};IP:".$_SERVER['REMOTE_ADDR']."连接");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\r\n");
    }
    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {
        try{
            $message = str_replace("\0","",$message);
            $obj = @json_decode($message,true);
            $key = isset($obj['key']) ? $obj['key'] : '';
            $random = isset($obj['random']) ? $obj['random'] : '';
            self::log("IP:".$_SERVER['REMOTE_ADDR']." 消息");
            self::log($obj);
            switch(strtolower($obj['type'])){
                case 'pong':
                    break;
                case 'svr_group'://小游戏服务器连接，绑定到特定的组
                    if(!self::checkSvrSign($key,$random)){self::log("svr_group 验证失败");return false;}
                    self::log("svr_group 验证通过");
                    $ret = Gateway::getSession($client_id);
                    if($ret && isset($ret['auth_timer_id'])){
                        \Workerman\Lib\Timer::del($ret['auth_timer_id']);
                    }
                    Gateway::joinGroup($client_id,self::$gameSvrGroupId);
                    break;
                case 'svr_game_kick_user':
                    if(!self::checkSvrSign($key,$random)){self::log("svr_game_kick_user 验证失败");return false;}
                    self::log("svr_game_kick_user 验证通过");
                    $userId = (int)$obj['message'];
                    if($userId){
                        //禁用用户
                        self::$db->query("Update `lt_user`  set `status` = 2 WHERE `id` = '{$userId}'");
                        self::$db->query("Insert into `lt_user_log`(`classify`,`user_id`,`explain`,`data`,`create_time`,`update_time`)VALUES(4,'{$userId}','游戏内非法操作，账号已被禁用','','".time()."','".time()."')");
                        //踢出当前用户；//socket关闭socket
                        $result = [
                            'code' => 0,
                            'mark' => 99999,//92999,
                            'msg'  => '',
                            'time' => time(),
                            'data' => [
                                'type' => '', //防止游戏服务器接收消息出错
                                'message' => '由于你的非法操作，你的账号已被禁用，如有疑问请联系客服！',
                                'stop' => 1,
                            ]
                        ];
                        Gateway::sendToUid($userId,@json_encode($result));
                    }
                    break;
                case 'game_svr_notice':
                    if(!self::checkSvrSign($key,$random)){self::log("game_svr_notice 验证失败");return false;}
                    self::log("game_svr_notice 验证通过");
                    self::log($obj['message']);
                    // {"delay_time":0,"key":"BA9D6D019C1573990896E082F3494B37","message":"\u6d4b\u8bd5\u6e38\u620f\u8dd1\u9a6c\u706f","msg_level":0,"msg_type":16,"random":567537,"type":"game_svr_notice"}
                    $result = [
                        'code' => 0,
                        'mark' => 99997,//92999,
                        'msg'  => '',
                        'time' => time(),
                        'data' => [
                            'type' => '', //防止游戏服务器接收消息出错
                            'message' => $obj['message'],
                            'msg_level' => $obj['msg_level'],
                            'msg_type' => $obj['msg_type'],
                            'delay_time' => $obj['delay_time'],
                        ]
                    ];
                    Gateway::sendToAll(@json_encode($result));
                    break;
                case 'gold' :
                    break;

            }
        }catch (Exception $e){
            self::log("ClientId:{$client_id};IP:".$_SERVER['REMOTE_ADDR']."接收消息：{$message}".";错误提示：".($e->getMessage()));
            //echo "[".date("Y-m-d H:i:s")."]ClientId:{$client_id};IP:".$_SERVER['REMOTE_ADDR']."接收消息：{$message}".";错误提示：".($e->getMessage()).PHP_EOL."======================\r\n";
        }
    }


    private static function checkSvrSign($sign,$slat){
        if(!$sign || !$slat){return false;}
        if(strtolower($sign) == strtolower(md5(self::$svrPrivateKey.$slat))){return true;}
        return false;
    }
    public static function onWorkerStop($businessworker){
        self::log("onWorkerStop");
    }


    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id){
        self::log("ClientId:{$client_id};IP:".$_SERVER['REMOTE_ADDR']."断开");




    }

    public static function log($message){
        $fileName = date("Ymd").".txt";
        $dir = ROOT_PATH."runtime/log/socket/";
        @mkdir($dir,0777,true);
        if(is_array($message)){
            $message = @json_encode($message);
        }
        $message = "[".date("Y-m-d H:i:s")."]\t".$message."\r\n";
        error_log($message,3,$dir.$fileName);
    }
}
