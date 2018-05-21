<?php
/**
 * 系统消息
 * Date: 2017/4/1
 * Time: 10:33
 */
namespace library\service;
use think\Db;
use think\Exception;

class Message{
    const recAll = 1; //全部用户
    const recUser = 2; //指定用户
    
    private $sendData = [];
    private $userIds = [];

    public function sendUser($userId,$title,$message){
        $messageId = $this->addMessage(self::recUser,$title,$message);
        $this->addSendData($messageId,$userId);
        $this->send();
    }

    /**
     * 像特定的比赛下的投注、坐庄用户发送消息
     */
    public function sendToPlayGroup($playId,$type,$data = []){
        Db::name('sys_message_queue')->insert([
            'opt' => 'play',
            'type' => $type,
            'value' => $playId,
            'data' => json_encode($data),
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }

    /**
     * 向某个擂台的投注、坐庄用户发送消息
     */
    public function sendToArenaGroup($arenaId,$type,$data = []){
        Db::name('sys_message_queue')->insert([
            'opt' => 'arena',
            'type' => $type,
            'value' => $arenaId,
            'data' => json_encode($data),
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }
    /**
     * 向某个渠道用户发送消息
     */
    public function sendToDitch($ditchId,$type,$data = []){
        Db::name('sys_message_queue')->insert([
            'opt' => 'ditch',
            'type' => $type,
            'value' => $ditchId,
            'data' => json_encode($data),
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }


    public function addMessage($type,$title,$content,$creater = 0){
        return Db::name('sys_message')->insertGetId([
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'create_time' => time(),
            'update_time' => time(),
            'creater' => $creater,
        ]);
    }

    /**
     * 向指定用户发送消息
     */
    public function addSendData($message_id,$userId){
        $this->sendData[] = [
            'message_id' => $message_id,
            'user_id' => $userId,
            'is_read' => 0,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $this->userIds[$userId] = $userId;
        //Db::name('user')->where(['id' => $userId])->setInc('sys_message_total',1);
        /*try{

            (new Socket())->sendToUid($userId,['type' => 'message','time' => time()],'socket.to_send_message');

        }catch (Exception $e){}*/
    }

    /**
     * 向用户发送消息
     */
    public function send(){
        if($this->sendData){
            try {
                Db::name('sys_message_detail')->insertAll($this->sendData);
                if($this->userIds){
                    Db::name('user')->where(['id' => ['in',array_keys($this->userIds)]])->setInc('sys_message_total',1);
                }
                $this->resetSendData();
                return true;
            }catch (Exception $e){}
        }
        return false;
    }
    /**
     * 向用户发送消息
     */
    public function resetSendData(){
        $this->sendData = [];
    }
}