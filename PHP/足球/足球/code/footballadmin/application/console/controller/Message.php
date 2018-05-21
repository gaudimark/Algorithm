<?php
/**
 * 系统消息队列
 * Date: 2017/4/1
 * Time: 16:07
 */

namespace app\console\controller;
use app\console\logic\Basic;
use library\service\Arena;
use library\service\Play;
use library\service\Rule;
use library\service\Socket;
use think\Db;
use library\service\Message as Msg;
use think\Exception;

class Message extends Basic{
    private $handle = null;
    private $ruleSvr = null;
    public function __construct(){
        $this->handle = new Msg();
    }

    /**
     * 系统消息分发到用户
     */
    public function sendToAll(){
    //exit('');
        $this->console("Distribute Message Begin");
        $limit = 20;
        $page = 1;
        $time = time();
        while (true){
            $offset = ($page - 1) * $limit;
            $lists = Db::name('sys_message')->where(['is_out' => 0,'create_time' => ['lt',$time]])->order("id desc")->limit($offset,$limit)->select();
            if(!$lists){break;}
            foreach ($lists as $msg){
                if($msg['type'] == MESSAGE_RECEIVE_ALL){
                    $this->sendToAllUser($msg['id']);
                }elseif($msg['type'] == MESSAGE_RECEIVE_ASSIGN){
                    $this->sendToUser($msg['id'],$msg['data']);
                }elseif($msg['type'] == MESSAGE_RECEIVE_DITCH){
                    $this->sendToDitch($msg['id'],$msg['data']);
                }
                Db::name('sys_message')->where(['id' => $msg['id']])->update(['is_out' => 1]);
            }
            $page++;
        }
        $this->console("Distribute Message End");
    }

    private function sendToAllUser($msgId){
        $uLimit = 500;
        $uPage = 1;
        $lastLoginTime = mktime(0,0,0,date("m"),date("d") - 7,date("Y"));
        while (true){
            $uOffset = ($uPage - 1) * $uLimit;
            $uList = Db::name('user')->field("id")->where([
                'has_robot' => 0,
                'last_login_time' => ['egt',$lastLoginTime],
                'status' => STATUS_ENABLED
            ])->order("id desc")->limit($uOffset,$uLimit)->select();
            if(!$uList){break;}
            $inData = [];
            $userIds = [];
            foreach ($uList as $u){
                $userIds[$u['id']] = $u['id'];
                $inData[] = [
                    'message_id' => $msgId,
                    'user_id' => $u['id'],
                    'is_read' => 0,
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }
            if($inData){
                try {
                    Db::name('sys_message_detail')->insertAll($inData);
                }catch (Exception $e){
                    $this->console($e->getMessage());
                }
            }
            if($userIds){
                Db::name('user')->where(['id' => ['in',array_values($userIds)]])->setInc('sys_message_total',1);
            }
            $uPage++;
        }
    }
    private function sendToUser($msgId,$data){
        $userIds = @json_decode($data,true);
        if(!$userIds){return;}
        $inData = [];
        foreach($userIds as $userId){
            $userId = (int)$userId;
            if(!$userId){continue;}
            $inData[] = [
                'message_id' => $msgId,
                'user_id' => $userId,
                'is_read' => 0,
                'create_time' => time(),
                'update_time' => time(),
            ];
        }
        if($inData){
            try {
                Db::name('sys_message_detail')->insertAll($inData);
            }catch (Exception $e){
                $this->console($e->getMessage());
            }
            Db::name('user')->where(['id' => ['in',array_values($userIds)]])->setInc('sys_message_total',1);
        }

    }

    private function sendToDitch($msgId,$data){
        $uLimit = 500;
        $uPage = 1;
        $lastLoginTime = mktime(0,0,0,date("m"),date("d") - 7,date("Y"));
        $ditchIds = @json_decode($data,true);
        if(!$ditchIds){return;}
        foreach($ditchIds as $ditchId) {
            $ditchId = (int)$ditchId;
            if(!$ditchId){continue;}
            while (true) {
                $uOffset = ($uPage - 1) * $uLimit;
                $uList = Db::name('user')->field("id")->where([
                    'has_robot' => 0,
                    'ditch_number' => $ditchId,
                    'last_login_time' => ['egt', $lastLoginTime],
                    'status' => STATUS_ENABLED
                ])->order("id desc")->limit($uOffset, $uLimit)->select();
                if (!$uList) {
                    break;
                }
                $inData = [];
                $userIds = [];
                foreach ($uList as $u) {
                    $userIds[$u['id']] = $u['id'];
                    $inData[] = [
                        'message_id' => $msgId,
                        'user_id' => $u['id'],
                        'is_read' => 0,
                        'create_time' => time(),
                        'update_time' => time(),
                    ];
                }
                if ($inData) {
                    try {
                        Db::name('sys_message_detail')->insertAll($inData);
                    } catch (Exception $e) {
                        $this->console($e->getMessage());
                    }
                }

                if($userIds){
                    Db::name('user')->where(['id' => ['in',array_values($userIds)]])->setInc('sys_message_total',1);
                }
                $uPage++;
            }
        }
    }



    /**
     * 滚动公告推送
     */
    public function notice(){
        $time = time();
        $limit = 20;
        $page = 0;
        $socket = new \library\service\Socket();
        while (true) {
            $offset = $page * $limit;
            $lists = Db::name('system_notice')->where([
                'btime' => ['elt', $time],
                'etime' => ['egt', $time],
                'classify' => ['eq', NOTICE_CLASSIFY_MARQUEE]
            ])->limit($offset,$limit)->select();
            if(!$lists){break;}
            foreach($lists as $val){
                $this->console("渠道分组：{$val['ditch_classify']},消息:{$val['title']}");
                if(isset($val['ditch_classify']) && $val['ditch_classify']){
                    $socket->sendToDitchClassifyGroup($val['ditch_classify'],$val['title'],'socket.to_all_notice');
                }else{
                    $socket->sendToAll($val['title'],'socket.to_all_notice');
                }
            }
            if(count($lists) < $limit){break;}
        }
    }

    /**
     * 消息队列处理
     */
    public function queue(){
        $this->console("Message Queue Begin");
        $limit = 100;
        $page = 1;
        $btime = time();
        while (true) {
            $offset = ($page - 1) * $limit;
            $lists = Db::name('sys_message_queue')->where(['status' => 0,'create_time' => ['lt',$btime]])->order("id asc")->limit($offset, $limit)->select();
            if (!$lists){
                break;
            }
            foreach ($lists as $val) {
                $val['data'] = @json_decode($val['data'],true);
                switch ($val['opt']){
                    case 'play':
                        $this->queuePlay($val);
                        break;
                    case 'arena':
                        $this->queueArena($val);
                        break;
                    case 'small_game':
                        //$this->queueSmallGame($val);
                        break;
                }
                Db::name('sys_message_queue')->where(['id' => $val['id']])->update(['status' => 1]);
            }
            $page++;
        }
        $this->console("Message Queue End");
    }

    private function queuePlay($queue){
        $playId = $queue['value'];
        $type = $queue['type'];
        $data = $queue['data'];
        if(!is_array($data)){
            $data = [$data];
        }
        $play = (new Play())->getPlay($playId);
        if(!$play){
            $this->console("Play({$playId}) Not Found");
            return false;
        }
        $this->ruleSvr = (new Rule())->factory($play['game_type']);
        if($type == MESSAGE_QUEUE_TYPE_SEAL){
            $this->_queueArenaSeal($data);
        }elseif($type == MESSAGE_QUEUE_TYPE_DISABLED){
            $this->_queueArenaDisabled($data);
        }elseif($type == MESSAGE_QUEUE_TYPE_STATEMENT){
            $this->_queueArenaStatement($data);
        }
    }

    /**
     * 封擂，只发给擂主
     * @param $data
     */
    private function _queueArenaSeal($data){
        if($data){
            $arenaSvr = new Arena();
            foreach($data as $val){
                $this->handle->resetSendData();
                $arena = $arenaSvr->getCacheArenaById($val);
                $message = lang('99000',['arenaId' => $val]);
                $messageId = $this->handle->addMessage(Msg::recUser,'房间封盘',$message);
                $this->handle->addSendData($messageId,$arena['user_id']);
                $this->handle->send();
            }
        }
    }
    /**
     * 擂台删除，只发给擂主
     * @param $data
     */
    private function _queueArenaDel($data){
        if($data){
            $arenaSvr = new Arena();
            foreach($data as $val){
                $this->handle->resetSendData();
                $arena = $arenaSvr->getCacheArenaById($val);
                $message = lang('99015',['arenaId' => $val]);
                $messageId = $this->handle->addMessage(Msg::recUser,'删除房间',$message);
                $this->handle->addSendData($messageId,$arena['user_id']);
                $this->handle->send();
            }
        }
    }

    /**
     * 取消擂台
     * @param $data
     */
    private function _queueArenaDisabled($data){
        if($data){
            $arenaSvr = new Arena();
            foreach($data as $val){
                //发给擂主
                $this->handle->resetSendData();
                $arena = $arenaSvr->getCacheArenaById($val);
                if(!$arena){return;}
                $message = lang('99002',['arenaId' => $val]);
                $messageId = $this->handle->addMessage(Msg::recUser,'取消房间',$message);
                $this->handle->addSendData($messageId,$arena['user_id']);
                $this->handle->send();

                //发给投注用户
                $limit = 20;
                $page = 1;
                while (true) {
                    $offset = ($page - 1) * $limit;
                    $list = Db::name('arena_bet_detail')->field('user_id')->group("user_id")->where(['arena_id' => $val])->limit($offset,$limit)->select();
                    if(!$list){
                        break;
                    }
                    foreach($list as $bet){
                        $this->handle->resetSendData();
                        $message = lang('99001',['arenaId' => $val]);
                        $messageId = $this->handle->addMessage(Msg::recUser,'取消房间',$message);
                        $this->handle->addSendData($messageId,$bet['user_id']);
                        $this->handle->send();
                    }
                    $page++;
                }
            }
        }
    }
    /**
     * 结算
     * @param $data
     */
    private function _queueArenaStatement($data){
        if($data){
            $arenaSvr = new Arena();
            foreach($data as $val){
                //发给擂主
                $this->handle->resetSendData();
                $arena = $arenaSvr->getCacheArenaById($val);
                if(!$arena || $arena['status'] != ARENA_STATEMENT_END){return;}
                $teams = $arena['teams'];
                $match = $arena['match'];

                $langData = [];
                $langData['arenaId'] = $val;
                if(count($teams) > 2){
                    $langData['team'] = $match['name'];
                }else{
                    $langData['team'] = "{$teams[0]['name']} VS {$teams[1]['name']}";
                }
                $langData['win_money'] = $arena['win'];
                $langData['lost_money'] = $arena['win'];
                $langData['brok'] = $arena['win_brok'];
                if($arena['win'] > 0){
                    $message = lang('99005', $langData);
                }else{
                    $message = lang('99006', $langData);
                }
                $messageId = $this->handle->addMessage(Msg::recUser,'房间结算',$message);
                $this->handle->addSendData($messageId,$arena['user_id']);
                $this->handle->send();

                //发给投注用户
                $limit = 20;
                $page = 1;
                //$message = lang('99001',['arenaId' => $val]);
                //$messageId = $this->handle->addMessage(Msg::recUser,'擂台结算',$message);
                while (true) {
                    $offset = ($page - 1) * $limit;
                    $list = Db::name('arena_bet_detail')->where(['arena_id' => $val])->limit($offset,$limit)->select();
                    if(!$list){
                        break;
                    }
                    foreach($list as $bet){
                        $this->handle->resetSendData();
                        $betTarget = $this->ruleSvr->getBetTargetText($arena['rules_id'],$arena['play_id'],$teams,$bet['target'],$bet['item']);
                        $message = '';
                        $langData['target_name'] = $betTarget['target'].($betTarget['item'] ? "({$betTarget['item']})" : '');
                        $langData['money'] = $bet['money'];
                        $langData['brok'] = $bet['fee'];
                        $langData['win_money'] = $bet['win_money'];
                        if(in_array($bet['status'],[DEPOSIT_WIN,DEPOSIT_WIN_HALF])){
                            $message = lang('99003',$langData);
                        }elseif($bet['status'] == DEPOSIT_LOSE){
                            $langData['win_money'] = 0;
                            $langData['lost_money'] = $bet['money'];
                            $message = lang('99004',$langData);
                        }elseif($bet['status'] == DEPOSIT_SAME){ //平手,退全部本金
                            $langData['win_money'] = 0;
                            $langData['lost_money'] = 0;
                            $message = lang('99007',$langData);
                        }elseif($bet['status'] == DEPOSIT_LOST_HALF){ //输一半本金
                            $langData['win_money'] = 0;
                            $langData['lost_money'] = round($bet['money'] / 2,2);
                            $message = lang('99008',$langData);
                        }
                        $messageId = $this->handle->addMessage(Msg::recUser,'投注结算',$message);
                        $this->handle->addSendData($messageId,$bet['user_id']);
                        $this->handle->send();
                    }
                    $page++;
                }
            }
        }
    }

    private function queueArena($queue){
        $arenaId = $queue['value'];
        $type = $queue['type'];
        if($type == MESSAGE_QUEUE_TYPE_SEAL){
            $this->_queueArenaSeal([$arenaId]);
        }elseif($type == MESSAGE_QUEUE_TYPE_DISABLED){
            $this->_queueArenaDisabled([$arenaId]);
        }elseif($type == MESSAGE_QUEUE_TYPE_DELETE){
            $this->_queueArenaDel([$arenaId]);
        }
    }


    private function queueSmallGame($data){
        $winMoney = $data['value'] ; //输赢金额
        $userId = $data['data']['user_id'];
        $game = $data['data']['game_name'];
        $text = '';
        if($winMoney < 0){
            $text = lang(99101,['game' => $game,'money' => abs($winMoney)]);
        }else{
            $text = lang(99100,['game' => $game,'money' => abs($winMoney)]);
        }
        $messageId = $this->handle->addMessage(Msg::recUser,'房间结算',$text);
        $this->handle->addSendData($messageId,$userId);
        $this->handle->send();
    }

    /**
     * 清除邮箱内容，只保留5天
     */
    public function remove(){
        //队列记录为1天
        $btime = mktime(0,0,0,date("m"),date("d") - 1);
        Db::name('sys_message_queue')->where(['create_time' => ['lt',$btime]])->delete();

        //消息记录为5天
        $btime = mktime(0,0,0,date("m"),date("d") - 3);
        $limit = 100;
        $page = 0;
        $count = 0;
        while (true){
            $this->console("{$page}");
            $offset = $page * $limit;
            $lists = Db::name('sys_message')->field('id')->where(['create_time' => ['lt',$btime]])->limit($limit)->select();
            if(!$lists){break;}
            $ids = [];
            foreach($lists as $val){
                $count++;
                $ids[] = $val['id'];
            }
            if($ids){
                Db::name('sys_message')->where([
                    'id' => ['in',array_values($ids)]
                    ])->delete();
                Db::name('sys_message_detail')->where([
                    'message_id' => ['in',array_values($ids)]
                ])->delete();
            }
            $page++;
        }
        $this->console("清除邮箱内容成功,本次共清除{$count}条记录");


    }

}