<?php
namespace app\index\controller;
use library\service\Arena;
use library\service\Oauth;
use think\Controller;
use think\Db;

class Share extends Controller{

    public function arena(){
        $arenaSvr = new Arena();
        $arenaId = input("arena_id/d");
        $token = input("token");
        $creditMark = input("credit_mark");
        $arenaInfo = $arenaSvr->getCacheArenaById($arenaId);
        if(!$arenaInfo){return abort(404);}
        $sportName = getSport($arenaInfo['game_type']);
        $teams = $arenaInfo['teams'];

        if($creditMark && $arenaInfo['classify'] == ARENA_CLASSIFY_CREDIT){
            $arena_url = $arenaSvr->getArenaUrlByCredit($arenaId, $token, $creditMark);
            $arena_qr = get_image_thumb_url($arenaSvr->getQrCodeByCredit($arenaId, $token, $creditMark));
        }else{
            $arena_url = $arenaSvr->getArenaUrl($arenaId,$token);
            $arena_qr = get_image_thumb_url($arenaSvr->getQrCode($arenaId,$token));
        }
        $domain = (new Oauth())->getDomain($token);
        $user = (new Oauth())->getUser($token);
        $privateCode = ''; //邀请码
        $creditCode = ''; //授信码x
        $creditGold = 0;
        if($arenaInfo['classify'] == ARENA_CLASSIFY_GOLD && $arenaInfo['user_id'] == $user['id'] && $arenaInfo['private'] == ARENA_DISPLAY_CODE){
            $privateCode = $arenaInfo['invit_code'];
        }
        if($arenaInfo['classify'] == ARENA_CLASSIFY_CREDIT && $arenaInfo['user_id'] == $user['id'] && $creditMark){
            $res = Db::name('arena_credit')->where(['arena_id' => $arenaId,'mark' => $creditMark])->find();
            if($res){
                $creditGold = $res['gold'];
                $creditCode = $res['code'];
            }
        }
        if($arenaInfo['classify'] == ARENA_CLASSIFY_GOLD){
            $privateCode = $privateCode ? "邀请码：{$privateCode}，":"";
            $text = "用浏览器打开链接{$arena_url}投注，房间号:{$arenaId}，{$privateCode}比赛：[{$sportName}] {$teams[0]['name']}  VS  {$teams[1]['name']}。";
        }else{
            $text = "你的唯一授信地址：{$arena_url}，请使用浏览器打开，授信额度：{$creditGold}，房间号:{$arenaId}，授信密码：{$creditCode}，比赛：[{$sportName}] {$teams[0]['name']}  VS  {$teams[1]['name']}。";
        }



        $this->assign("sportName",$sportName);
        $this->assign("teams",$teams);
        $this->assign("arena_url",$arena_url);
        $this->assign("arena_qr",$arena_qr);
        $this->assign("arena_id",$arenaId);
        $this->assign("private_code",$privateCode);
        $this->assign("credit_code",$creditCode);
        $this->assign("token_domain",$domain);
        $this->assign("text",$text);
        return $this->fetch();
    }

    public function recharge(){
        $conf = config('system');
        $kc = input('kc');
        $token = input("token");
        $text = '';
        $domain = (new Oauth())->getDomain($token);
        $user = (new Oauth())->getUser($token);
        $recharge_agent = @json_decode($conf['recharge_agent'],true);
        $res = [];
        if($recharge_agent){
            foreach($recharge_agent as $key => $val){
                $code = md5(json_encode($val));
                if($code == $kc && $val['name']){
                    $res = [
                        'name' => $val['name'],
                        'wx' => $val['wx'],
                        'qq' => $val['qq'],
                        'alipay' => $val['alipay'],
                        'hot' => $val['hot'],
                    ];
                    $text = input("text");
                    break;
                }
            }
        }
        $this->assign("res",$res);
        $this->assign("text",$text);
        $this->assign("token_domain",$domain);
        return $this->fetch();
    }
    //比赛预测
    public function play_dope(){
        $playId = input('play_id/d');
        $dopeContent = '';
        $dope = Db::name('play_dope')->where(['play_id' => $playId])->find();
        if($dope){
            $dopeContent = $dope['content'];
            $dopeContent = str_replace('__RES_DOMAIN__',config('site_source_domain'),$dopeContent);
        }
        $token = input("token");
        $domain = (new Oauth())->getDomain($token);
        $this->assign("token_domain",$domain);
        $this->assign("dopeContent",$dopeContent);
        return $this->fetch();
    }
}