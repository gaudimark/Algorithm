<?php
namespace app\index\controller;
use app\library\logic\Basic;
use library\service\Forms;
use library\service\Image;
use library\service\Layout;
use library\service\Live;
use library\service\Misc;
use library\service\Order;
use library\service\Passport;
use library\service\Pay;
use library\service\pay\Laitoy;
use library\service\pay\Rxlicai;
use library\service\Socket;
use library\service\User;
use org\Stringnew;
use think\Cache;
use think\captcha\Captcha;
use think\Db;
use think\Loader;

class Index extends Basic{

    private $appId = '';
    private $appSecret = '';

    public function __construct(){
        parent::__construct();
        config('default_return_type','html');
        $authConf = config("auth");
        $authConf = $authConf['51bet'];
        $this->appId = $authConf['app_id'];
        $this->appSecret = $authConf['secret'];
    }

    public function index(){
//        dump(config());
        $this->assign("access_token",'d6d5c8497eaec6883eb9405bca074050');
        return $this->fetch();
    }

    public function resetToken(){

    }

    public function test(){
        $userData = [
            'uuid' => 'pad_8dgf4m41g7fpra57b22c655565c07157',
            'imei' => 'SN2017042500033',
            'gameId' => PAD_GAME_ID,
        ];
        //$result = (new \library\service\Pad($userData))->getPonints();
       // dump($result);
        //强拿金币
        /*$result = (new \library\service\Pad($userData))->userForceGold([
            'uuid' => 'pad_8dgf4m41g7fpra57b22c655565c07157',
            'cpOrderId' => 'PAD20180301233523598175',
            'ext' => '',
            'signMsg' => md5('PAD20180301233523598175pad_8dgf4m41g7fpra57b22c655565c07157'),
        ]);*/
        //退出游戏
        //$result = (new \library\service\Pad($userData))->callbackPoints(1,$userData);
        //发送跑马灯
        //$result = (new \library\service\Pad($userData))->slideMsg([['amount' => 1000,'cpid' => 229415548]]);
        //更新用户金币
        $result = (new \library\service\Pad([]))->userUpdate([
  /*          'cpOrderId' => 'PAD20180301233523598175',
            'uuid' => 'pad_8dgf4m41g7fpra57b22c655565c07157',
            'addAccount' => 1000,
            'ext' => '',
            'receiveUrl' => '',
            'signMsg' => md5('1000PAD20180301233523598175pad_8dgf4m41g7fpra57b22c655565c07157'),*/

            'cpOrderId' => 'PAD20180311223228707130',
            'uuid' => 'pad_w7f2ac3dca755hdr22725a118ca6ca2e',
            'addAccount' => '1000',
            'ext' => '',
            'signMsg' => md5('1000PAD20180311223228707130pad_w7f2ac3dca755hdr22725a118ca6ca2e'),
            'receiveUrl' => '',

        ]);
        /*$result = (new \library\service\Pad($userData))->testStatement([
            'cpid' => '229415548',
            'uuid' => 'pad_8dgf4m41g7fpra57b22c655565c07157',
            'imei' => 'SN2017042500033',
            'gold' => 30,
        ]);*/
        dump($result);
    }

    /**
     * 获取验证码
     */
    public function captcha(){
        $length = input("length",0,'intval');
        $type = input("type",0,'intval');
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        if($type == 1){
            $codeSet = '0123456789';
        }elseif($type == 2){
            $codeSet = 'abcdefghijlkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';
        }
        if($length < 4){$length = 4;}
        if($length > 6){$length = 6;}
        return $this->getCaptcha($length,$codeSet);
    }
}