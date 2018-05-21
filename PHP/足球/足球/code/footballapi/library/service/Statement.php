<?php
/**
 * 结算
 */
namespace library\service;
use think\Db;
use think\Exception;

class Statement{
    private $error = '';
    private $handle = null;

    /**
     * 结算某场比赛
     * @param $playId
     */
    public function play($playId){
        $playId = intval($playId);
        $play =  Db::name("play")->where(['id' => $playId])->find();

        if(!$play){
            $this->error = "未找到比赛信息，无法结算({$playId})";
            if(defined('AUTO_STATEMENT')){echo($this->error);}
            return false;
        }
        if($play['status'] != PLAT_STATUS_END){
            $this->error = "比赛非结束状态，无法结算({$playId})";
            if(defined('AUTO_STATEMENT')){echo($this->error);}
            return false;
        }
        

        //连接结算处理程序
        $this->handle = $this->factory($play['game_type']);
        $this->handle->setPlay($play);//设置结算的比赛

        //检查比赛结果是否正确
        if(false === $this->handle->checkPlayData($play)){
            $this->error = $this->handle->getError();
            Db::name("play")->where(['id' => $playId])->update([
                'statement_status' => STATEMENT_STATUS_ERROR,
                'statement_status_text' => $this->error
            ]);
            if(defined('AUTO_STATEMENT')){echo($this->error);}
            return false;
        }
        //更新比赛状态为结算中
        Db::name("play")->where(['id' => $playId])->update(['status' => PLAT_STATUS_STATEMENT_BEGIN]);
        //开启事务
        Db::startTrans();
        $limit = 10;
        $page = 1;
        $errTotal = 0;
        $errData = [];
        $ids = [];

        try{
            while (true){
                $offset = ($page - 1) * $limit;
                $arena = Db::name("Arena")->limit($offset,$limit)->order("id desc")->where([
                    'play_id' => $playId,
                    'status'=> ['in',[ARENA_END,ARENA_START,ARENA_SEAL,ARENA_PLAY]],//ARENA_END,//['in',[ARENA_END]]
                ])->select();
                if(!$arena){break;}
                foreach ($arena as $val){
                    if(true !== $ret = $this->checkArena($val)){
                        throw new Exception($ret);
                    }
                    $this->handle->reset();
                    $this->handle->setArena($val);//设置结算的擂台
                    if(true === $result =$this->handle->run()){ //执行结算;
                        (new Arena())->cacheArena($val['id']);
                        $ids[] = $val['id'];
                    }//else{
                        //$this->error = $this->handle->getError();
                        //throw new Exception($this->error);
                    //}
                }
                
     
            }
            //更新比赛数据
            Db::name("play")->where(['id' => $playId])->update([
                'status' => PLAT_STATUS_STATEMENT,
                'has_statement' => 1,
                'statement_time' => time(),
                'statement_status' => STATEMENT_STATUS_SUCCESS,
            ]);
            Db::commit();
       }catch (Exception $e){
            //echo $e->getMessage().$e->getLine().$e->getFile();
            //更新比赛数据
            Db::rollback();
            Db::name("play")->where(['id' => $playId])->update([
                'status' => PLAT_STATUS_END,
                'statement_time' => time(),
                'statement_status' => STATEMENT_STATUS_ERROR,
                'statement_status_text' => $e->getMessage(),
            ]);
            $this->error = $e->getMessage();
            if(defined('AUTO_STATEMENT')){echo($this->error);}
            return false;
        }
        //写入日志
        \library\service\Log::sysLog(0, SYSTEM_LOG_STATEMENT, '比赛结算-成功', ['play_id' => $playId]);
        //更新比赛缓存
        (new Play())->upCache($playId);
        //写入消息队列
        (new Message())->sendToPlayGroup($playId,MESSAGE_QUEUE_TYPE_STATEMENT,$ids);
        if($ids){
            foreach ($ids as $id) { //结算后将擂台移除风险提醒列表
                (new Arena())->rmArenaRiskList($id);
            }
        }
        $this->handle->socketSend();
        return true;
    }

    /**
     * 擂台结算
     * @param $arena 擂台数组
     * @param null $play
     * @return bool
     */
    private function checkArena($arena){
        if(!$arena){
            return "未找到擂台信息，无法结算";
        }
        if($arena['status'] == ARENA_STATEMENT_END){
            return "擂台已结算，无法结算";
        }
        if($arena['status'] == ARENA_DIS){
            return "擂台已封禁，无法结算";
        }
        if($arena['status'] == ARENA_DEL){
            return "擂台已删除，无法结算";
        }
        return true;
    }


    private function factory($itemId){
        switch ($itemId){
            case GAME_TYPE_FOOTBALL:
                $handle = new \library\service\statement\Football();
                break;
            case GAME_TYPE_WCG:
                $handle = new \library\service\statement\Wcg();
                break;
            case GAME_TYPE_BASKETBALL:
                $handle = new \library\service\statement\Basketball();
                break;
        }
        return $handle;
    }

    public function getError(){
        return $this->error;
    }
}