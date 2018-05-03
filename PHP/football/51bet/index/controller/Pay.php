<?php
/**
 * 支付入口
 * Date: 2017/4/28
 * Time: 16:11
 */
namespace app\index\controller;
use library\service\Oauth;
use think\Controller;
class Pay extends Controller{
    private $paySvr = null;
    public function __construct(){
        parent::__construct();
        $this->paySvr = new \library\service\Pay();
    }

    /**
     * h5充值
     */
    public function h5_pay_type(){
        $token = input("token");
        $code = input("code");
        $ts = input("ts");
        $money = floatval(input("money",0));
        $system = config("system");
        $recharge_on = isset($system['recharge_on']) ? intval($system['recharge_on']) : 0;
        if(!$recharge_on){
            exit(lang(11002));
        }
        $oauth = new Oauth();
        $pay = new \library\service\Pay();
        $user = $oauth->getUser($token);
        if(!$user){
            exit("用户登录失效，请重新登录");
        }
        if($pay->checkMobilePayCode($code,$token,$money,$user['id'],$ts)){ //数据安全验证
            if($system['recharge_min_money'] > $money){
                exit("充值金额不能小于{$system['recharge_min_money']}");
            }
            $productName = '我要投注金币充值';
            $productInfo = "我要投注金币充值,充值金额：{$money}";
            $productUrl = config('site_domain');
            $payPlatform = $system['recharge_platform'];
            if(false === $this->paySvr->mobilePay($user['id'],$money,$productName,$productInfo,$productUrl,$payPlatform,'alipay')){
                exit('获取支付信息失败');
            }
        }else{
            exit("非法操作");
        }
    }

    public function mobile(){
        $ret = $this->paySvr->mobilePay(4,0.01,'测试充值','我要投注充值',config('site_domain'),'playgame178','alipay');
        var_dump($ret);
    }

    /**
     * playgame178平台回调入口
     */
    public function playgame178(){
        $data = input();
        if($data && isset($data['mchId']) && isset($data['pdorderid'])){
            $mchId = $data['mchId'];
            $fee = $data['fee'];
            $pdorderid = $data['pdorderid'];
            $mchorderid = $data['mchorderid'];
            $unit = $data['unit'];
            $status = $data['status'];
            $channel = $data['channel'];
            $paychannel = $data['paychannel'];
            $retSign = $data['sign'];
            $sign = md5($mchId . $fee . $pdorderid . $mchorderid . $unit . $status . $channel . $paychannel);
            $data['_sign_'] = $sign;
            if ($sign == $retSign || $retSign == strtolower($sign)){
                $this->paySvr->callback($mchorderid, [
                    'status' => $status == 1 ? PAY_STATUS_SUCCESS : PAY_STATUS_ERROR,
                    'platform_order' => $pdorderid,
                    'pay_money' => $unit == 'fen' ? $fee / 100 : $fee,
                    'pay_time' => time(),
                    'pay_type' => strtolower($paychannel),
                    'expand1' => $data,
                ]);
                exit('success');
            }
        }
        exit('error');
    }

    /**
     * 聚汇宝回调入口
     */
    public function juhuibao(){
        $data = [];
        $data['merchno'] = input('merchno');
        $data['amount'] = input('amount');
        $data['traceno'] = input('traceno');
        $data['orderno'] = input('orderno');
        $data['status'] = input('status');
        $data['signature'] = input('signature');
        ksort($data);//对数组进行排序
        //遍历数组进行字符串的拼接
        $temp = '';
        foreach ($data as $x => $x_value){
            if ($x != 'signature' && $x_value != null && $x_value != 'null'){
                $temp = $temp. $x . "=" .$x_value . "&";
            }
        }
        $conf = config("pay.juhuibao");
        $md5 = strtoupper(md5(iconv('UTF-8','GBK//IGNORE',$temp.$conf['merchKey'])));
        $this->paySvr->log("merchno = {$data['merchno']}\namount={$data['amount']}\ntraceno={$data['traceno']}\norderno={$data['orderno']}\nstatus={$data['status']}\nsignature={$data['signature']}\nmd5={$md5}");
        if($md5 == $data['signature']){
            $this->paySvr->callback(input('traceno'), [
                'status' => $data['status'] == 2 ? PAY_STATUS_SUCCESS : PAY_STATUS_ERROR,
                'platform_order' => $data['orderno'],
                'pay_money' => $data['amount'],
                'pay_time' => time(),
                'pay_type' => 'juhuibao',
                'expand1' => $data,
            ]);
            exit('success');
        }
        exit('error');
    }
}