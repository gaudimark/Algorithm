<?php

namespace app\admin\controller\agent;
use app\admin\logic\Basic;
use think\Db;
use think\Exception;

class Recharge extends Basic{

    public function _initialize(){
        $this->assign('controller','agent.recharge');
    }

    public function index(){
        $user_type = input('user_type');
        $username = input('username');
        $where = [];
        if($user_type == 1){
            $where['username'] = ['like',"%{$username}%"];
        }elseif($user_type == 2){
            $where['nickname'] = ['like',"%{$username}%"];
        }
        $lists = modelN('recharge_agent_user')->where($where)->order('id desc')->paginate(20,false,['query' => input()]);
        $this->assign('lists',$lists);
        $this->assign('user_type',$user_type);
        $this->assign('username',$username);
        return $this->fetch();
    }


    public function add(){
        if($this->request->isPost()){
            $id = input('id/d');
            $data['username'] = input('username');
            $data['password'] = input('password');
            $data['nickname'] = input('nickname');
            $data['status'] = input('status/d');
            $data['has_recharge'] = input('has_recharge/d',0);
            $data['has_withdrawal'] = input('has_withdrawal/d',0);
            $data['withdrawal_weights'] = input('withdrawal_weights/d',0);
            $data['bank_withdrawal_weights'] = input('bank_withdrawal_weights/d',0);
            $model = modelN('recharge_agent_user');
            if($id){
                unset($data['username']);
                $result = $model->save($data,['id' => $id]);
            }else{
                $result = $model->save($data);
            }
            if($result === false){
                return $this->error($model->getError());
            }
            return $this->success('操作成功');
        }
        $id = input('id/d');
        $res = [];
        if($id){
            $res = modelN('recharge_agent_user')->where(['id' => $id])->find();
        }
        $this->assign('res',$res);
        return $this->fetch();
    }

    public function del(){
        if($this->request->isPost()){
            $id = input("id");
            model("recharge_agent_user")->where(['id' => $id])->delete();
            return $this->success("删除成功");
        }
    }


    public function weights(){
        if($this->request->isPost()){
            $list = modelN('recharge_agent_user')->select();
            $res = modelN('recharge_agent_user')->field('sum(withdrawal_weights) as withdrawal_weights,sum(bank_withdrawal_weights) as bank_withdrawal_weights')->find();
            $weightsTotal = $res['withdrawal_weights'];
            $bankWeightsTotal = $res['bank_withdrawal_weights'];
            $aNum = 0;
            $bNum = 0;
            $data = [];
            foreach($list as $val){
                $af = $weightsTotal ? round(($val['withdrawal_weights'] / $weightsTotal) * 100,2) : 0;
                $bf = $bankWeightsTotal ? round(($val['bank_withdrawal_weights'] / $bankWeightsTotal) * 100,2) : 0;
                $aMax = $af ? min(100,$aNum + ceil($af)) : 0;
                $bMax = $bf ? min(100,$aNum + ceil($bf)) : 0;
                $wights = [
                    'weights' => $af,
                    'weights_min' => ($af ? $aNum + 1 : 0),
                    'weights_max' => ($af ? $aMax : 0),
                    'bank_weights' => $bf,
                    'bank_weights_min' => ($bf ? $bNum + 1 : 0),
                    'bank_weights_max' => $bf ? $bMax : 0,
                ];
                modelN('recharge_agent_user')->cache(false)->where([
                    'id' => $val['id']
                ])->update($wights);
                $data[$val['id']] = [
                    'id' => $val['id'],
                    'name' => $val['nickname'],
                    'weights_min' => ($af ? $aNum + 1 : 0),
                    'weights_max' => ($af ? $aMax : 0),
                    'bank_weights_min' => ($bf ? $bNum + 1 : 0),
                    'bank_weights_max' => $bf ? $bMax : 0,
                ];
                $aNum = $aMax;
                $bNum = $bMax;
            }
            cache('recharge_agent_user',$data);
            return $this->success("更新成功");
        }
    }

    public function up(){
        if($this->request->isPost()){
            $id = input('id');
            $opt = input('opt');
            $value = input('value');
            $game = modelN('recharge_agent_user')->where(['id' => $id])->find();
            if(!$game){return $this->error("游戏查找失败");}
            $data = [];
            if($opt == 'man'){
                $data['has_man'] = $game['has_man'] == STATUS_YES ? STATUS_NO : STATUS_YES;
            }
            modelN('recharge_agent_user')->where(['id' => $id])->update($data);
            return $this->success('更新成功');
        }
    }

    /**
     * 充值
     */
    public function pay(){
        if($this->request->isPost()){
            $id = input('post.id/d');
            $gold = input('post.gold/f');
            $gold = abs($gold);
            Db::startTrans();
            try{
                $res = modelN('recharge_agent_user')->where(['id' => $id])->find();
                if(!$res){
                    throw new Exception('无效充值对像');
                }

                modelN("recharge_agent_user")->save([
                    'gold' => ['exp',"gold+{$gold}"],
                    'gold_total' => ['exp',"gold_total+{$gold}"],
                ],['id' => $id]);
                
                modelN("recharge_agent_order")->save([
                    'ra_user_id' => $id,
                    'number' => $gold,
                    'to_user_id' => 0,
                    'type' => RECHARGE_AGENT_ORDER_TYPE_RECHARGE
                ]);

                modelN("recharge_agent_log")->save([
                    'ra_user_id' => $id,
                    'number' => $gold,
                    'before_num' => $res['gold'],
                    'after_num' => $res['gold'] + $gold,
                    'explain' => '系统充值',
                    'data' => @json_encode([
                        'admin_id' => $this->admin_id,
                        'gold' => $gold,
                        'username' => $res['username']
                    ])
                ]);

                Db::commit();
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success("充值成功");
        }
        $id = input('id/d');
        $res = [];
        if($id){
            $res = modelN('recharge_agent_user')->where(['id' => $id])->find();
        }
        if(!$res){
            return $this->error('无效充值对像');
        }
        $this->assign('res',$res);
        return $this->fetch();
    }

    public function debit(){

        if($this->request->isPost()){
            $id = input('post.id/d');
            $gold = input('post.gold/f');
            $explain = input('post.explain');
            $gold = abs($gold);
            Db::startTrans();
            try{
                $res = modelN('recharge_agent_user')->where(['id' => $id])->find();
                if(!$res){
                    throw new Exception('无效扣款用户');
                }
                if($res['gold'] < $gold){
                    throw new Exception('扣款用户账户余额不足');
                }

                modelN("recharge_agent_user")->save([
                    'gold' => ['exp',"gold-{$gold}"],
                    //'gold_total' => ['exp',"gold_total+{$gold}"],
                ],['id' => $id]);

                modelN("recharge_agent_order")->save([
                    'ra_user_id' => $id,
                    'number' => -$gold,
                    'to_user_id' => 0,
                    'type' => RECHARGE_AGENT_ORDER_TYPE_RECHARGE
                ]);

                modelN("recharge_agent_log")->save([
                    'ra_user_id' => $id,
                    'number' => -$gold,
                    'before_num' => $res['gold'],
                    'after_num' => $res['gold'] - $gold,
                    'explain' => "系统扣款({$explain})",
                    'data' => @json_encode([
                        'admin_id' => $this->admin_id,
                        'gold' => -$gold,
                        'username' => $res['username']
                    ])
                ]);

                Db::commit();
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success("扣款成功");
        }

        $id = input('id/d');
        $res = [];
        if($id){
            $res = modelN('recharge_agent_user')->where(['id' => $id])->find();
        }
        if(!$res){
            return $this->error('无效扣款对像');
        }
        $this->assign('res',$res);
        return $this->fetch();
    }

    public function logs(){
        $user_type = input('user_type');
        $log_type = input('log_type/d');
        $username = input('username');
        $btime = input('btime');
        $etime = input('etime');
        $export = input('export');
        if($export){
            return $this->_total_logs_export();
        }
        $where = [];
        if($user_type == 1){
            $where['ru.username'] = ['like',"%{$username}%"];
        }elseif($user_type == 2){
            $where['ru.nickname'] = ['like',"%{$username}%"];
        }
        if($log_type){
            $where['l.type'] = (int)$log_type;
        }

        if($btime && $etime){
            $t = $etime;
            if($btime == $etime){
                $etime = $etime .' 23:59:59';
            }
            $where['l.create_time'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
            $etime = $t;
        }elseif ($btime){
            $where['l.create_time'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['l.create_time'] = ['elt',strtotime($etime)];
        }

        $lists = modelN('recharge_agent_log')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.ra_user_id')
            ->where($where)->order('l.id desc')->paginate(20,false,['query' => input()]);

        $total = modelN('recharge_agent_log')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.ra_user_id')
            ->where($where)->where('number > 0')->sum('number');

        $this->assign('lists',$lists);
        $this->assign('user_type',$user_type);
        $this->assign('log_type',$log_type);
        $this->assign('username',$username);
        $this->assign('total',$total);
        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        return $this->fetch();
    }

    public function _total_logs_export(){
        $user_type = input('user_type');
        $username = input('username');
        $btime = input('btime');
        $etime = input('etime');
        $where = [];
        if($user_type == 1){
            $where['ru.username'] = ['like',"%{$username}%"];
        }elseif($user_type == 2){
            $where['ru.nickname'] = ['like',"%{$username}%"];
        }
        if($btime && $etime){
            $t = $etime;
            if($btime == $etime){
                $etime = $etime .' 23:59:59';
            }
            $where['l.create_time'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
            $etime = $t;
        }elseif ($btime){
            $where['l.create_time'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['l.create_time'] = ['elt',strtotime($etime)];
        }

        $lists = modelN('recharge_agent_log')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.ra_user_id')
            ->where($where)->order('l.id desc')->select();
        foreach ($lists as $key => $val){
            $val = $val->toArray();
            $val['type'] = $val['number'] > 0 ? '收入' : '支出';
            $val['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
            $lists[$key] = $val;
        }
        $title = array(
            'create_time' => '日期',
            'nickname' => '消费者',
            'type' => '收支',
            'before_num' => '操作前金额',
            'number' => '金额',
            'after_num' => '操作后金额',
            'explain' => '原因',
        );
        (new \library\service\Misc())->toXls('代理充值日志',$title,$lists);
    }

    public function withdrawal_log(){

        $agentId = input('agent_id/d',0);
        $platform = input('platform');
        $btime = input('btime');
        $etime = input('etime');
        $export = input('export');
        $total = 0;

        if($export){
            return $this->_withdrawal_log_export();
        }
        $where = [];
        if($agentId){
            $where['l.agent_id'] = $agentId;
        }
        if($platform){
            $where['l.platform'] = $platform;
        }
        if($btime && $etime){
            $t = $etime;
            if($btime == $etime){
                $etime = $etime .' 23:59:59';
            }
            $where['l.update_time'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
            $etime = $t;
        }elseif ($btime){
            $where['l.update_time'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['l.update_time'] = ['elt',strtotime($etime)];
        }

        $lists = modelN('user_withdrawal')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.agent_id')
            ->where($where)->order('l.id desc')->paginate(20,false,['query' => input()]);

        $total = modelN('user_withdrawal')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.agent_id')
            ->where($where)->where(['l.status' => WD_SUCCESS])->sum('money-(money*fee)');

        $this->assign("recharge_agent_user",cache('recharge_agent_user'));

        $bank = config("bank");

        $this->assign('lists',$lists);
        $this->assign('agent_id',$agentId);
        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        $this->assign('total',$total);
        $this->assign('platform',$platform);
        $this->assign('bank',$bank);
        return $this->fetch();
    }
    public function _withdrawal_log_export(){
        $user_type = input('user_type');
        $username = input('username');
        $btime = input('btime');
        $etime = input('etime');
        $where = [];
        $where['agent_id'] = ['gt',0];
        if($user_type == 1){
            $where['ru.username'] = ['like',"%{$username}%"];
        }elseif($user_type == 2){
            $where['ru.nickname'] = ['like',"%{$username}%"];
        }
        if($btime && $etime){
            $t = $etime;
            if($btime == $etime){
                $etime = $etime .' 23:59:59';
            }
            $where['l.update_time'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
            $etime = $t;
        }elseif ($btime){
            $where['l.update_time'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['l.update_time'] = ['elt',strtotime($etime)];
        }

        $lists = modelN('user_withdrawal')->alias('l')
            ->field('l.*,ru.nickname,ru.username')
            ->join('recharge_agent_user ru','ru.id=l.agent_id')
            ->where($where)->order('l.id desc')->select();
        if($lists){
            foreach($lists as $key => $val){
                $val = $val->toArray();
                $val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $val['update_time'] = date('Y-m-d H:i:s',$val['update_time']);
                $val['user_nickname'] = getUser($val['user_id'],'nickname');
                $status = '';
                switch ($val['status']){
                    case WD_NOT_AUDITED:
                        $status = '未审核';
                        break;
                    case WD_UNTREATED:
                        $status = '未处理';
                        break;
                    case WD_LOCK:
                        $status = '锁定';
                        break;
                    case WD_PROCESS:
                        $status = '处理中';
                        break;
                    case WD_SUCCESS:
                        $status = '完成';
                        break;
                    case WD_FAIL:
                        $status = '失败';
                        break;
                    case WD_RECEDE:
                        $status = '已退款';
                        break;
                }
                $val['status'] = $status;
                $lists[$key] = $val;
            }
        }
        $title = array(
            'create_time' => '申请时间',
            'update_time' => '更新时间',
            'agent_id' => '代理ID',
            'nickname' => '代理用户',
            'user_id' => '用户ID',
            'user_nickname' => '申请用户',
            'money' => '金额(元)',
            'fee' => '手续费(元)',
            'platform' => '提现方式',
            'wd_account' => '提现账号',
            'wd_name' => '提现姓名',
            'status' => '状态',
            'remark' => '备注',
        );
        (new \library\service\Misc())->toXls('代理兑换日志',$title,$lists);
    }
}