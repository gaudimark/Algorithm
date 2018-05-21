<?php

namespace library\service;

use think\Db;

/**
 * 平板服务器交互类
 */
class Pad {

    const GAME_TYPE = 2; //游戏类别,1=人机，2=足球彩票，3=对战
    const GAME_EXIT_DEFAULT = 1; //正常退出游戏
    const GAME_EXIT_STATEMENT = 3; //非正常退出游戏结算通知

    /**
     * 密钥
     */

    private $key = '';

    /**
     * 平板服务器地址
     * 必须以/结束
     */
    private $server = 'http://103.97.33.17:99/';

    /**
     * 游戏启动请求游戏币接口
     */
    private $points = 'get/gameplayer/ponints';

    /**
     * 游戏上币成功后通知接口
     */
    private $points_notice = 'notice/gameplayer/ponints';

    /**
     * 游戏金币回到平板服务器接口
     */
    private $points_callback = 'get/callback/gameplayer/ponints';

    /**
     * 给游戏中玩家推送跑马灯
     */
    private $message = 'listen/slide/msg';
    private $error = '';

    /**
     * 用户数据
     * @var array
     */
    public $userData = [
//        'uuid' => 'pad_uv7d3fhs574tdes4ba73a207232e5798',
//        'imei' => 'SN20170503000011',
        'gameId' => PAD_GAME_ID,
    ];

    public function __construct($userData = array()) {
//        $this->userData = array_merge($this->userData,$userData);
        if ($userData) {
            $this->userData = $userData;
            $pad = (new Oauth())->getPad($this->userData['uuid']);
        }
        $this->key = '';
//        $this->key = $pad && isset($pad['key']) ? $pad['key'] : '';
        //$this->userData['imei'] = 'SN2017042500033';
    }

    /**
     * 游戏启动请求游戏币,用户登录从服务器获取用户金币信息
     * 玩家进入大厅打开游戏时通过http请求获取当前所有可用游戏币，统一采用post请求。
     */
    public function getPonints() {
        $data = $this->userData;
        $data['cpOrderId'] = $this->createOrder();
        $data['limit'] = 0;
        $data['ext'] = '';
        $data['signMsg'] = $this->createSign([
            $data['cpOrderId']
            , $data['gameId']
            , $data['imei']
            , $data['limit']
            , $data['uuid']
            , $this->key
        ]);
        $result = $this->post($this->points, $data);

        if (!$this->hasResultSuccess($result)) {
            file_put_contents(SITE_PATH . '../runtime/log/api/login_err_' . date('Ymd') . '.log', json_encode($result) . PHP_EOL, FILE_APPEND);
            return false;
        }
        if (!$this->checkResultSign($result['signMsg'], [
                    $result['account']
                    , $result['cpOrderId']
                    , $result['gameId']
                    , $result['imei']
                    , $result['payResult']
                    , $result['returnDatetime']
                    , $result['uuid']
                    , $this->key
                ])) {
            return false;
        }
        $this->sendSeverPointsSuccess($result['cpOrderId'], $result['account']);
        $result['account'] = $this->padGoldChange($result['account']);

        return $result;
    }

    /**
     * 戏上币成功后通知，通知服务器冻结用户金币
     * 游戏服务器上币成功后通知平板服务器，平板服务器接到通知后做相对应的金币解冻清零操作
     */
    public function sendSeverPointsSuccess($orderId, $gold) {
        $data = $this->userData;
        $data['cpOrderId'] = $orderId;
        $data['addgold'] = $gold;
        $data['ext'] = '';
        $data['signMsg'] = $this->createSign([
            $gold
            , $data['cpOrderId']
            , $data['gameId']
            , $data['imei']
            , $data['uuid']
            , $this->key
        ]);
        $result = $this->post($this->points_notice, $data);
        if (!$this->hasResultSuccess($result)) {
            return false;
        }
//        if(!$this->checkResultSign($data['signMsg'],[
//            $result['account']
//            ,$result['cpOrderId']
//            ,$data['gameId']
//            ,$result['imei']
//            ,$result['payResult']
//            ,$result['returnDatetime']
//            ,$result['uuid']
//            ,$this->key
//        ])){return false;}


        return $result;
    }

    /**
     * 给游戏中玩家推送跑马灯
     * @param array $message { [uuid :2112, imei :2132, amount :222],[uuid :2112, imei :2132, amount :222]}
     * @return bool|mixed\
     */
    public function message($message = []) {
        $data = [];
        $data['cpOrderId'] = $this->createOrder();
        $data['dataMsg'] = $message;
        $data['gameId'] = $this->userData['gameId'];
        $data['ext'] = '';
        $data['signMsg'] = $this->createSign([
            $data['cpOrderId']
            , $data['dataMsg']
            , $data['gameId']
            , $this->key
        ]);
        $result = $this->post($this->points_callback, $data);
        if (!$this->hasResultSuccess($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 用户退出
     * @param $userId
     */
    public function logout($userId) {

        $user = \think\Db::name('user')->field('id,gold,nouseaccount,imei,uuid,has_online')->where(['id' => $userId])->find();
        if (!$user) {
            return false;
        }
        if (!$user['has_online']) { // 检查是否在线，如果未在线则不推送数据
            return true;
        }
        $data = $this->userData;
        $data['gold'] = $user['gold'];
        $data['nouseaccount'] = $user['nouseaccount'];
        $data['imei'] = $user['imei'];
        $data['uuid'] = $user['uuid'];

        $data = $this->setCallbackPointsData(1, $data);

        $data = $this->sendCallbackPoints($data);

        return $data;
    }

    /**
     * 游戏金币回到平板服务器
     * 退出游戏时，向服务器发送用户剩余金币
     * $type 1=正常退出，2=非正常退出，3=非正常退出游戏结算通知
     */
    public function callbackPoints($type, $userAccount = []) {
        $data = array_merge($this->userData, $userAccount);
        $user = \think\Db::name('user')->field('id,gold,nouseaccount,imei,uuid')->where(['uuid' => $data['uuid']])->find();
        if (!$user) {
            return false;
        }
        $data['gold'] = $user['gold'];
        $data['nouseaccount'] = $user['nouseaccount'];
        $data['imei'] = $user['imei'];
        $data['uuid'] = $user['uuid'];
        $data = $this->setCallbackPointsData($type, $data);
        return $this->sendCallbackPoints($data);
    }

    private function setCallbackPointsData($type, $data) {
        $result = [];
        //$receiveUrl = rtrim(config('domain_api'),"/").'/pad/callbackpoints';
        $result['cpOrderId'] = (isset($data['cpOrderId']) && $data['cpOrderId']) ? $data['cpOrderId'] : $this->createOrder();
        //$result['type'] = self::GAME_TYPE;
        //$result['exitType'] = $type;
        $result['useAccount'] = $this->goldToPad($data['gold']);
        //$result['noUseAccount'] = $this->goldToPad($data['nouseaccount']);
        //$result['imei'] = $data['imei'];
        $result['online'] = isset($data['online']) ? $data['online'] : 0;
        //$result['tax'] = isset($data['tax']) ? $data['tax'] : 0;
        //$result['banker'] = isset($data['banker']) ? $data['banker'] : 0;
        //$result['receiveUrl'] = $receiveUrl;
        $result['ext'] = isset($data['ext']) ? $data['ext'] : 0;
        $result['gameId'] = isset($data['gameId']) ? $data['gameId'] : $this->userData['gameId'];
        $result['uuid'] = isset($data['uuid']) ? $data['uuid'] : $this->userData['uuid'];
        return $result;
    }

    private function sendCallbackPoints($data, $isQueue = false) {
        $data['signMsg'] = $this->createSign([
//            $data['banker']
            $data['cpOrderId']
//            ,$data['exitType']
            , $data['gameId']
//            ,$data['imei']
//            ,$data['noUseAccount']
            , $data['online']
//            ,$data['useAccount']
////            ,$data['receiveUrl']
//            ,$data['tax']
//            ,$data['type']
            , $data['uuid']
            , $data['useAccount']
            , $this->key
        ]);

        $result = $this->post($this->points_callback, $data);

        if (!$this->hasResultSuccess($result)) {
            !$isQueue && $this->addQueue('callback', $data, $result);
            return false;
        }
        if (!$this->checkResultSign($result['signMsg'], [
                    $result['cpOrderId']
                    , $result['gameId']
                    // ,$result['imei'] ? $result['imei'] : ''
                    , $result['payResult']
                    , $result['returnDatetime']
                    , $result['uuid']
                    , $this->key
                ])) {
            !$isQueue && $this->addQueue('callback', $data, $result);
            return false;
        }
        \think\Db::name('user')->where(['uuid' => $data['uuid']])->update([
            'gold' => 0,
            'has_online' => 0,
            'nouseaccount' => 0,
        ]);
        return $result;
    }

    public function padSvrCallbackPoints($result) {

        if (!$this->hasResultSuccess($result) && !$this->checkResultSign($result['signMsg'], [
                    $result['cpOrderId']
                    , $result['gameId']
                    // ,$result['imei']
                    , $result['payResult']
                    , $result['returnDatetime']
                    , $result['uuid']
                    , $this->key
                ])) {//回调成功
            $this->upQueue($result['cpOrderId'], $result, 1);
        }
    }

    /**
     * 游戏中直接给用户加金币
     * 玩家正在游戏时代理商通过发送http请求直接加金币或者其他途径从后台给游戏中用户直接加金币
     * @param $data
     */
    public function userUpdate($data) {
        $uuid = $data['uuid'];
        $addAccount = $data['addAccount'];
        $ext = $data['ext'];
        $data['imei'] = '';
        $data['gameId'] = $this->userData['gameId'];
        if ($this->checkResultSign($data['signMsg'], [
                    $data['addAccount']
                    , $data['cpOrderId']
                    , $data['receiveUrl']
                    , $data['uuid']
                    , $this->key
                ])) {
            $user = \think\Db::name('user')->field('id,gold,imei')->where(['uuid' => $uuid])->find();
            if ($user) {
                $addAccount = $this->padGoldChange($addAccount);
                \think\Db::name('user')->where(['id' => $user['id']])->setInc('gold', $addAccount);
                $ext = 'PAD游戏中直接给用户加金币';
                \library\service\Log::UserFunds($user['id'], FUNDS_CLASSIFY_REC, FUNDS_TYPE_GOLD
                        , $addAccount
                        , $user['gold']
                        , $user['gold'] + $addAccount
                        , $ext
                        , $data
                );
                $data['imei'] = $user['imei'];
                $data['gameId'] = $this->userData['gameId'];
                (new Socket())->userGold($user['id'], $this->padGoldChange($data['addAccount']));
                return $this->sendUserUpdateSuccess($data);
            }
            return $this->sendUserUpdateSuccess($data, "匹配用户UUID失败");
        }
        return $this->sendUserUpdateSuccess($data, "数据校验失败");
    }

    private function sendUserUpdateSuccess($setData, $payResult = '000000') {
        $data = $this->userData;
        $data['receiveUrl'] = $setData['receiveUrl'];
        $data['uuid'] = $setData['uuid'];
        $data['cpOrderId'] = $setData['cpOrderId'];
        $data['addAccount'] = $setData['addAccount'];
        $data['payResult'] = $payResult;
        $data['returnDatetime'] = date("Ymdhis");
        $data['signMsg'] = $this->createSign([
            $data['addAccount']
            , $data['cpOrderId']
            , $data['gameId']
            , $data['imei']
            , $data['payResult']
            , $data['uuid']
            , $this->key
        ]);
        $payResult == '000000' && $this->addQueue('update_user_gold', $data); //回调队列
        //$this->post($se/tData['receiveUrl'],$data);
        return $data;
    }

    /**
     * 查询游戏中金币接口
     * 玩家通过其他途径退出游戏查询金币接口
     * @param $data
     */
    public function userQuery($data) {
        $uuid = $data['uuid'];
        $data['imei'] = '';
        $data['gameId'] = $this->userData['gameId'];
        if ($this->checkResultSign($data['signMsg'], [
                    $data['cpOrderId']
                    , $data['uuid']
                    , $this->key
                ])) {
            $user = \think\Db::name('user')->field('id,gold,nouseaccount')->where(['uuid' => $uuid])->find();
            if ($user) {
                $data['acount'] = $this->goldToPad($user['gold']);
                $data['noUse'] = $this->goldToPad($user['nouseaccount']);
                return $this->sendUserQuery($data);
            }
            return $this->sendUserQuery($data, '匹配用户UUID失败');
        }
        return $this->sendUserQuery($data, '数据校验失败');
    }

    private function sendUserQuery($setData, $payResult = '000000') {
        $data = $this->userData;
        $data['cpOrderId'] = $setData['cpOrderId'];
        $data['uuid'] = $setData['uuid'];
        $data['imei'] = $setData['imei'];
        $data['acount'] = $setData['acount'];
        $data['noUse'] = $setData['noUse'];
        $data['payResult'] = $payResult;
        $data['returnDatetime'] = date("Ymdhis");
        $data['signMsg'] = $this->createSign([
            $data['acount']
            , $data['cpOrderId']
            , $data['gameId']
            , $data['imei']
            , $data['noUse']
            , $data['payResult']
            , $data['returnDatetime']
            , $data['uuid']
            , $this->key
        ]);
        return $data;
    }

    /**
     * 强制游戏中拿回金币
     * 玩家登录其他平板，但是游戏未推出需要强制拿回金币。那个这个时候我向游戏服务器发送请求，游戏服务器按照接口3标准采取退出结算操作
     * @param $data
     */
    public function userForceGold($data) {
        if ($this->checkResultSign($data['signMsg'], [
                    $data['cpOrderId']
                    , $data['uuid']
                    , $this->key
                ])) {
            return $this->callbackPoints(2, [
                        'uuid' => $data['uuid']
            ]);
        }
        return false;
    }

    /**
     * 给游戏中玩家推送跑马灯
     * @param array $msgArr { {amount :222，cpid:1213},{ amount :456，cpid:24324}}
     * @return bool
     */
    public function slideMsg($msgArr = []) {
        $data = $this->userData;
        $data['cpOrderId'] = $this->createOrder();
        $data['dataMsg'] = @json_encode($msgArr);
        $data['ext'] = '';
        $data['signMsg'] = $this->createSign([
            $data['cpOrderId']
            , $data['dataMsg']
            , $data['gameId']
            , $this->key
        ]);
        $result = $this->post($this->message, $data);
        if (!$this->hasResultSuccess($result)) {
            return false;
        }
        if (!$this->checkResultSign($result['signMsg'], [
                    $result['cpOrderId']
                    , $result['gameId']
                    , $result['payResult']
                    , $this->key
                ])) {
            return false;
        }
        return true;
    }

    /**
     * 比赛结算(启用)
     * */
    public function my_settlement($account, $exitType, $user_id, $cpOrderId, $flow = 0, $is_duilie = 0) {
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
        $data['gameId'] = $this->userData['gameId'];
        $data['cpOrderId'] = $cpOrderId;
        $data['signMsg'] = md5($data['account'] . $data['banker'] . $data['cpOrderId'] . $data['exitType'] . $data['flow'] . $data['gameId'] . $data['imei'] . $data['online'] . $data['tax'] . $data['type'] . $data['uuid'] . $this->key);

        $fh_result = $this->post('/listen/game/bet/msg', $data);
        if ($fh_result['payResult'] != '000000' && $exitType == 2 && $is_duilie == 0) {
            $duilie = array(
                'money' => $data['account'],
                'flow' => $data['flow'],
                'cpOrderId' => $data['cpOrderId'],
                'err_total' => 0
            );
            Cache::rpush('user_bet_pad_js_data', json_encode($duilie));
        }
        file_put_contents(SITE_PATH . '../runtime/log/api/jie_' . date('Ymd') . '.log', json_encode($fh_result) . PHP_EOL, FILE_APPEND);
        return $fh_result;
    }

    /**
     * 比赛结算 100 200
     * @param $userId
     * @param $gold
     */
    public function statement($userId, $gold, $capital) {
        $user = \think\Db::name('user')->field('id,gold,has_online,imei,uuid,cpid')->where(['id' => $userId])->find();
        if ($user) {
            $nouseaccount = abs($capital);
            if ($gold > 0) { //中奖，更新用户金币
                $data['gold'] = ['exp', "gold+{$gold}"];
                $nouseaccount = $gold - $capital; //流水扣除本金
            }
            $nouseaccount = abs($nouseaccount);
            if ($user['has_online']) {
                $data['nouseaccount'] = ['exp', "nouseaccount+{$nouseaccount}"];
                \think\Db::name("user")->where(['id' => $userId])->update($data);
            } else {
                $this->addQueue('statement', [
                    'cpOrderId' => $this->createOrder(),
                    'user_id' => $userId,
                    'gold' => $gold,
                    'nouseaccount' => $nouseaccount,
                    'imei' => $user['imei'],
                    'uuid' => $user['uuid'],
                    'cpid' => $user['cpid'],
                ]);
            }
        }
    }

    /**
     * 通知服务器失败后，将数据加入任务队列
     * @param $data
     * @param array $result
     */
    private function addQueue($type, $data, $result = []) {
        $orderId = $data['cpOrderId'];


        \think\Db::name('queue')->insert([
            'mark' => $orderId,
            'type' => $type,
            'count' => '0',
            'data' => json_encode($data),
            'result' => json_encode($result),
            'status' => 0,
            'create_time' => time(),
            'update_time' => time()
        ]);
    }

    public function upQueue($mark, $result, $status = 1) {
        $data = [
            'status' => $status,
            'update_time' => time(),
            'result' => @json_encode($result)
        ];
        if ($status != 1) {
            $data['count'] = ['exp', 'count+1'];
        }
        \think\Db::name('queue')->where(['mark' => $mark])->update($data);
    }

    public function runQueue($type, $data) {
        $result = null;
        switch ($type) {
            case 'callback':
                $result = $this->sendCallbackPoints($data, true);
                break;
            case 'statement':
                $data = $this->setCallbackPointsData(3, [
                    'cpid' => $data['cpid'],
                    'uuid' => $data['uuid'],
                    'imei' => $data['imei'],
                    'gold' => $data['gold'],
                    'nouseaccount' => $data['nouseaccount'],
                ]);
                $result = $this->sendCallbackPoints($data, true);
                break;
            case 'update_user_gold' :
                $result = $this->post($data['receiveUrl'], $data);
                if ($result['cpOrderId'] == $data['cpOrderId'] && $this->hasResultSuccess($result) && $this->checkResultSign($result['signMsg'], [
                            $result['addAccount'],
                            $result['cpOrderId'],
                            $result['gameId'],
                            $result['imei'],
                            $result['payResult'],
                            $result['returnDatetime'],
                            $result['uuid'],
                            $this->key,
                        ])) {
                    
                }
                break;
        }
        $this->wLog(['send' => $data, 'result' => $result], '执行队列');
        return $result;
    }

    public function testStatement($data) {
        $data = $this->setCallbackPointsData(3, [
            'cpid' => $data['cpid'],
            'uuid' => $data['uuid'],
            'imei' => $data['imei'],
            'gold' => $data['gold'],
            'nouseaccount' => $data['gold'],
        ]);
        return $this->sendCallbackPoints($data, true);
    }

    /**
     * 生成用户唯一订单号
     * @param $uuid
     * @param $imei
     * @return string
     */
    private function createOrder() {
        return 'PAD' . date("YmdHis") . rand(100000, 999999);
    }

    private function hasResultSuccess($data = []) {
        if (!$data || !is_array($data) || !isset($data['payResult'])) {
            return false;
        }
        if ($data['payResult'] == '000000') {
            return true;
        }
        $this->error = $data['payResult'];
        return false;
    }

    private function checkResultSign($sign, $data) {
        if ($sign == $this->createSign($data)) {
            return true;
        }
        $this->error = '签名校验失败';
        return false;
    }

    private function createSign($data) {
        // var_dump(implode("",$data));
        //var_dump(md5(implode("",$data)));
        return md5(implode("", $data));
    }

    /**
     * 向服务器发送请求
     * @param $api
     * @param array $data
     * @param bool $isPost
     * @return mixed
     */
    private function post($api, $data = [], $isPost = true) {
        $url = $this->server . $api;
//        $opts[CURLOPT_URL] = $url;
//        $opts[CURLOPT_RETURNTRANSFER] = true;//是否将结果返回
//        $opts[CURLOPT_SSL_VERIFYPEER] = false;
//        $opts[CURLOPT_SSL_VERIFYHOST] = false;
//        $opts[CURLOPT_TIMEOUT] = 8;
//        if($isPost) {
//            $opts[CURLOPT_POST] = 1;
//            $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
//            $opts[CURLOPT_POSTFIELDS] = $data;
//        }else{
//            $data = http_build_query($data);
//            $opts[CURLOPT_URL] = $url."?{$data}";
//        }
//        $ch = curl_init();
//        curl_setopt_array($ch, $opts);
//        $this->wLog($opts);
//        $content = curl_exec($ch);
//        curl_close($ch);
//        $this->wLog($content);
//        //var_dump($content);
//        if($content){
//            $content = @json_decode($content,true);
//        }
//        $this->wLog($content);
//        return $content;
        $header = array("Content-type:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $content = curl_exec($ch);
        if ($content) {
            $content = @json_decode($content, true);
        }
        $this->wLog($content);

        return $content;
    }

    private function goldToPad($gold) {
        return $gold = $gold * 100;
    }

    private function padGoldChange($gold) {
        return $gold = round($gold / 100, 2);
    }

    private function wLog($data, $title = '') {
        $fileSize = 2097152;
        $path = LOG_PATH . "pad/";
        $now = date('c');
        $destination = $path . date('Ym') . DS . date('d') . '.log';

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($fileSize) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }
        $depr = "---------------------------------------------------------------\r\n";
        $message = var_export($data, true);
        return error_log("[{$now}]{$title} {$message}\r\n{$depr}", 3, $destination);
    }

}
