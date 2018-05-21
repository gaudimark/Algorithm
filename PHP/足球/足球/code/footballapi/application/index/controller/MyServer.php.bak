<?php

namespace app\index\controller;

use app\library\logic\Basic;
use think\Db;
use think\Cache;
use think\Request;

class MyServer extends Basic {

    private $gameId = PAD_GAME_ID; //后期更改为PAD_GAME_ID
    //密钥
    private $key = '';

    /**
     *  游戏中直接给用户加金币
     */
    public function recharge() {
        if ($this->request->isPost()) {
            $returnDatetime = date('Ymdhis');
            $data['payResult'] = '000000';
            $data['cpOrderId'] = trim(input('post.cpOrderId'));
            $data['uuid'] = trim(input('post.uuid'));
            $data['imei'] = '';
            $data['gameId'] = trim(input('post.gameId'));
            $data['signMsg'] = trim(input('post.signMsg'));
            $data['receiveUrl'] = trim(input('post.receiveUrl'));
            $data['addAccount'] = trim(input('post.addAccount/d'));

            $ReturnSignMsg = md5($data['addAccount'] . $data['cpOrderId'] . $data['gameId'] . $data['imei'] . $data['payResult'] . $returnDatetime . $data['uuid'] . $this->key);

            //检查平板服务器发来的签名
            $getSignMsg = md5($data['addAccount'] . $data['cpOrderId'] . $data['gameId'] . $data['receiveUrl'] . $data['uuid'] . $this->key);
            if ($getSignMsg != $data['signMsg']) {
                $data['payResult'] = '签名验证错误';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }

            //检查签名
            //        if ('' == ($data['cpOrderId'] || $data['uuid'] || $data['imei'] || $data['signMsg'])) {
            //            $data['payResult'] = '签名验证错误';
            //            $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            //        }
            //获取用户基础信息
            $info = Db::name('user')->where(array('uuid' => $data['uuid']))->find();
            if (!$info) {
                $data['payResult'] = '用户信息获取失败';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }
            $data['imei'] = $info['imei'];

            //检查回调地址
            if ('' == $data['payResult']) {
                $data['payResult'] = '回调地址错误';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }

            //检查金额
            if (0 >= (int) $data['addAccount']) {
                $data['payResult'] = '增加金额错误';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }

            Db::startTrans();
            //修改用户金额
            $result = Db::name('user')->where('id', $info['id'])->setInc('gold', $data['addAccount']);
            if (!$result) {
                $data['payResult'] = '用户金额增加失败';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }

            //记录日志
            $logData = array(
                'user_id' => $info['id'],
                'classify' => FUNDS_CLASSIFY_SYS_REC,
                'type' => 1,
                'number' => $data['addAccount'],
                'before_num' => $info['gold'],
                'after_num' => $info['gold'] + $data['addAccount'],
                'explain' => '系统充值',
                'create_time' => time()
            );
            if (!Db::table('lt_user_funds_log')->insert($logData)) {
                Db::rollback();
                $data['payResult'] = '日志记录失败';
                $this->returnData($data, $ReturnSignMsg, $returnDatetime);
            }
            Db::commit();
            //返回结果

            $this->returnData($data, $ReturnSignMsg, $returnDatetime);
        }
    }

    /**
     * 游戏中详细下注和结算记录
     * */
    public function settlement($account, $exitType, $user_id, $cpOrderId, $flow = 0) {
        $info = DB::name('user')->where('id', $user_id)->find();
        $online = DB::name('user')->where('has_online', 1)->count();
        //请求数据
        $data = array(
            'uuid' => $info['uuid'],
            'imei' => $info['imei'],
            'type' => 4,
            'online' => $online,
            'tax' => 0,
            'banker' => 0,
            'flow' => $this->goldToPad($flow),
            'ext' => '',
            'account' => $this->goldToPad($account),
            'exitType' => $exitType,
        );
        $data['gameId'] = $this->gameId;
        $data['cpOrderId'] = $cpOrderId;
        $data['signMsg'] = md5($data['account'] . $data['banker'] . $data['cpOrderId'] . $data['exitType'] . $data['flow'] . $data['gameId'] . $data['imei'] . $data['online'] . $data['tax'] . $data['type'] . $data['uuid'] . $this->key);
        $result = $this->post('/listen/game/bet/msg', $data);
        $fh_result = json_decode($result, true);

        return $fh_result;
    }

    private function goldToPad($gold) {
        return $gold = $gold * 100;
    }

    private function padGoldChange($gold) {
        return $gold = round($gold / 100, 2);
    }

    public function bets() {
        $result = Cache::lrange('user_bet_pad_data', 0, 19);
        if (!empty($result)) {
            foreach ($result as $value) {
                $data = json_decode($value, true);
                $fh_data = $this->settlement($data["money"], 1, $data["user_id"], $data["cpOrderId"]);
                $err_total = $data["err_total"];
                if ($fh_data['payResult'] != '000000') {
                    if ($fh_data['ext']['isexit'] == 1) {
                        //删除错误下注
                        $this->DeleteThisBet($data["user_id"], $data["cpOrderId"]);
                        //执行退出登录
                        (new \library\service\Pad([]))->logout($data['user_id']);
                    } else if ($fh_data['ext']['isexit'] == 0) {
                        $err_total++;
                        if ($err_total == 3) {
                            //删除错误下注
                            $this->DeleteThisBet($data["user_id"], $data["cpOrderId"]);
                            //第三次失败 执行退出登录
                            (new \library\service\Pad([]))->logout($data['user_id']);
                        } else {
                            //重新加入队列
                            $data["err_total"] = $err_total;
                            Cache::rpush('user_bet_pad_data', json_encode($data));
                        }
                    }
                }
            }
            $count = Cache::llen('user_bet_pad_data');
            Cache::redisLtrim('user_bet_pad_data', $count - ($count - 20));
        }
    }

    /**
     * 下注出错删除下注消息
     * */
    private function DeleteThisBet($userid, $orderId) {
        Db::name('arena_bet_detail')->where(['user_id' => $userid, 'order_id' => $orderId])->delete();
        Db::name('user_funds_log')->where('data', 'like', '%' . $orderId . '%')->delete();
    }

    /**
     * 重新发起结算请求
     * */
    public function settlementAgainSend() {
        $result = Cache::lrange('settlement_err_data', 0, 19);
        if (!empty($result)) {
            foreach ($result as $value) {
                $data = json_decode($value, true);
                $fh_data = $this->settlement($data["money"], 2, $data["user_id"], $data["cpOrderId"], $data["flow"]);
                $err_total = $data["err_total"];
                if ($fh_data['payResult'] != '000000') {
                    if ($fh_data["ext"]['isexit'] == 1) {
                        //删除错误下注
                        $this->DeleteThisBet($data["user_id"], $data["cpOrderId"]);
                        //执行退出登录
                        (new \library\service\Pad([]))->logout($data['user_id']);
                    } else if ($fh_data["ext"]['isexit'] == 0) {
                        $err_total++;
                        if ($err_total == 3) {
                            //删除错误下注
                            $this->DeleteThisBet($data["user_id"], $data["cpOrderId"]);
                            //第三次失败 执行退出登录
                            (new \library\service\Pad([]))->logout($data['user_id']);
                        } else {
                            //重新加入队列
                            $data["err_total"] = $err_total;
                            Cache::rpush('settlement_err_data', json_encode($data));
                        }
                    }
                }
            }
            $count = Cache::llen('settlement_err_data');
            Cache::redisLtrim('settlement_err_data', $count - ($count - 20));
        }
    }

    /**
     * 跑马灯推送接口
     * */
    public function slide() {
        $data = Cache::lrange('user_earn_side_data', 0, 19);
        if (empty($data)) {
            return false;
        }
        $dataMsg = array();
        foreach ($data as $key => $value) {
            $dataMsg[] = json_decode($value, true);
        }
        //请求数据
        $data = array(
            'dataMsg' => json_encode($dataMsg),
            'key' => '',
            'ext' => '',
        );

        $data['gameId'] = $this->gameId;
        $data['cpOrderId'] = 'PAD' . date('YmdHis') . mt_rand(100000, 999999);
        $data['signMsg'] = md5($data['cpOrderId'] . $data['dataMsg'] . $data['gameId'] . $data['key']);
        $result = $this->post('/listen/slide/msg', $data);
        $count = Cache::llen('user_earn_side_data');
        Cache::redisLtrim('user_earn_side_data', $count - ($count - 20));
        die($result);
    }

    /**
     *  通知游戏服务器退出
     *  玩家登录其他游戏，但是游戏未退出需要通知游戏服务器强制下线操作。那个这个时候我向游戏服务器发送请求，游戏服务器按照接口3标准采取退出结算操作
     */
    public function notice_logout_game() {
        if ($this->request->isPost()) {
            $returnDatetime = date('Ymdhis');
            $data['payResult'] = '000000';
            $data['cpOrderId'] = trim(input('post.cpOrderId'));
            $data['uuid'] = trim(input('post.uuid'));
            $data['gameId'] = trim(input('post.gameId'));
            $data['ext '] = trim(input('post.ext'));
            $data['signMsg'] = trim(input('post.signMsg'));
            //echo md5($data['cpOrderId']  .$data['gameId']. $data['uuid'] . $this->key);exit;
            //检查签名. $data['gameId']
            $setSignMsg = md5($data['cpOrderId'] . $data['gameId'] . $data['uuid'] . $this->key);
            if ($setSignMsg != $data['signMsg']) {
                $data['payResult'] = '签名验证错误';
                $this->returnData($data, '', $returnDatetime);
            }

            $info = Db::name('user')->where('uuid', $data['uuid'])->find();
            if (!$info) {
                $data['payResult'] = 'uuid错误';
                $this->returnData($data, '', $returnDatetime);
            } else {
                //退出登录
                $result_bool = (new \library\service\Pad([]))->logout($info['id']);
                if ($result_bool) {
                    $this->returnData($data, '', $returnDatetime);
                } else {
                    $data['payResult'] = '退出状态修改失败';
                    $this->returnData($data, '', $returnDatetime);
                }
            }
            //返回结果
        }
    }

    /**
     * 心跳接口
     */
    public function heartbeat() {
        $data = array(
            'state' => 1,
            'key' => $this->key
        );
        $data['gameId'] = $this->gameId;
        $data['signMsg'] = md5($data['gameId'] . $data['state'] . $data['key']);
//模拟请求
        $result = $this->post('/listen/heartbeat', $data);
        die($result);
    }

    public function image() {
        $imgPath = '/cs.png';
        get_image_thumb_url();
    }

    /**
     * 模拟post请求
     * */
    private function post($action, Array $params) {
        $host = 'http://103.97.33.17:99';
        $header = array("Content-type:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . $action);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function returnData($data = array(), $signMsg = '', $returnDatetime = '') {
        $data['returnDatetime'] = $returnDatetime;
        $data['signMsg'] = $signMsg;
        die(json_encode($data));
    }

}
