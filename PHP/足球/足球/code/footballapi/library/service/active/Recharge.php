<?php
/**
 * 充值活动
 * User: Admin
 * Date: 2017/10/16
 * Time: 16:27
 */
namespace library\service\active;
use library\service\Message;
use library\service\Socket;
use library\service\User;
use think\Cache;
use think\Db;
use think\Exception;

class Recharge{
    public function run($params = []){
        $actList = cache('activity');
        if(isset($actList[ACTIVITY_TYPE_RECHARGE]) && $actList[ACTIVITY_TYPE_RECHARGE]){
            $actList = $actList[ACTIVITY_TYPE_RECHARGE];
            foreach($actList as $val){
                $handle = $val['trigger_handle'];
                if($val['status'] != STATUS_ENABLED){continue;} //状态禁用
                if($val['btime'] && $val['btime'] > time()){continue;} //未开始
                if($val['etime'] && $val['etime'] <= time()){continue;} //已结束
                if($val['ditch_id'] && !in_array($params['ditch_id'],$val['ditch_id'])){continue;}
                try{
                    $result = $this->$handle($params,$val);
                }catch (Exception $e){
                    echo $e->getMessage();
                }
            }
        }
    }
    
    public function lists($userId){
        $actList = cache('activity');
        $result = [];
        $ids = [];
        if(isset($actList[ACTIVITY_TYPE_RECHARGE]) && $actList[ACTIVITY_TYPE_RECHARGE]){
            $actList = $actList[ACTIVITY_TYPE_RECHARGE];
            foreach($actList as $val){
                $handle = $val['trigger_handle'];
                if($val['status'] != STATUS_ENABLED){continue;} //状态禁用
                if($val['btime'] && $val['btime'] > time()){continue;} //未开始
                if($val['etime'] && $val['etime'] <= time()){continue;} //已结束
                $ids[] = $val['id'];
            }
        }
        if($ids){
            $lists = Db::name('activity_complete')->alias('c')
                ->field('a.classify,a.ditch_id,a.name,a.trigger_handle,c.number,c.money,c.data,c.status')
                ->join('__ACTIVITY__ a','a.id=c.active_id','LEFT')
                ->where([
                    'a.id' => ['in',array_values($ids)],
                    'c.status' => ['neq',STATUS_USE],
                    'c.user_id' => $userId,
                ])->select();
            if($lists){
                foreach ($lists as $val){
                    $val['data'] = @json_encode($val['data']);
                    $result[] = $val;
                }
            }
        }
        return $result;
    }

    /**
     * 推广员渠道包增加充值活动
     * 该渠道包的用户充值累计满50元额外赠送一次救济金（只限一次，优先使用赠送次数）
     * @param $params
     */
    private function ditch_tg_recharge_total($params,$data){

        $ditchAllow = $data['ditch_id'];//允许渠道范围；
        $totalNumber = 50; //累计充值
        $giftNumber = 1; //赠送次数
        if(!isset($params['ditch_id']) || !isset($params['user_id'])){ //参数未带渠道标识
            return '';
        }
        if($ditchAllow && !in_array($params['ditch_id'],$ditchAllow)){//渠道标识不在允许范围内
            return '';
        }
        $userId = (int)$params['user_id'];
        if(!$userId){return '';}
        //检查用户是否已赠送
        /*$ret = $this->getUserActivity('ditch_tg_recharge_total',$userId);
        if($ret){
            return '';
        }*/
        $ret = Db::name('activity_complete')->where(['active_id' => $data['id'],'user_id' => $userId])->find();
        if($ret && $ret['status'] != STATUS_UNDONE){//用户已完成该活动
            return '';
        }
        //检查时间段内用户累计充值
        $where = [];
        $where['user_id'] = $userId;
        $where['status'] = PAY_STATUS_SUCCESS;
        if($data['btime'] && $data['etime']){
            $where['pay_time'] = [['egt',$data['btime']],['elt',$data['etime']]];
        }elseif($data['btime']){
            $where['pay_time'] = ['egt',$data['btime']];
        }
        $rechargeTotal = Db::name('recharge_order')->where($where)->sum('money');

        $sdata = [
            'active_id' => $data['id'],
            'user_id' => $userId,
            'status' => STATUS_UNDONE,
            'number' => 0,
            'money' => $rechargeTotal,
            'trigger_handle' => $data['trigger_handle'],
            'create_time' => time(),
            'update_time' => time(),
            'data' => @json_encode([])
        ];
        if($rechargeTotal >= $totalNumber){
            $sdata['number'] = $giftNumber;
            $sdata['status'] = STATUS_USE;//领取，使用
            $this->setUserActivity($sdata['trigger_handle'],$userId,[
                'number' => $sdata['number'],
                'money' => $sdata['money']
            ]);
            (new Message())->sendUser($userId,'充值活动',"累计充值满{$totalNumber}元额外赠送一次救济金");
            $alms = (new User())->getUserAlms($userId);
            (new Socket())->sendToUid($userId,[
                'num' => $alms['number'],
                'recharge_total' => $rechargeTotal,
                'condition' => $totalNumber,
                'active_no' => $data['a_no'],
            ],'socket.ditch_tg_recharge_total');
        }else{
            $this->setUserActivity($sdata['trigger_handle'],$userId,[
                'number' => $sdata['number'],
                'money' => $sdata['money']
            ]);
        }
        if($ret){
            unset($sdata['active_id']);
            unset($sdata['user_id']);
            unset($sdata['create_time']);
            Db::name('activity_complete')->where(['id' => $ret['id']])->update($sdata);
        }else{
            Db::name('activity_complete')->insert($sdata,true);
        }
        return true;
    }

    /**
     * 从缓存中获取用户完成情况
     * @param $field
     * @param $key
     */
    public function getUserActivity($field,$key){
        return Cache::hGet("activity_{$field}",$key);
    }

    public function setUserActivity($field,$key,$value){
        Cache::hSet("activity_{$field}",$key,$value);
    }
}