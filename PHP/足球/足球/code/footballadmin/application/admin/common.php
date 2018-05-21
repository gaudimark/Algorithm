<?php

/**
 * 后台操作权限检查
 * @param $controller
 * @param $action
 */
function checkPermit($controller,$action){
    $request = \think\Request::instance();
    $admin_user = session("cp.user");
    $admin_role = session("cp.role");
    $menu = new \app\admin\logic\Menu($request,$admin_user,$admin_role);
    $ret = $menu->checkSpecifyPrivate($controller,$action);
    if(!$ret){
        return false;
    }
    return true;
}

function getArenaRiskStyle($deposit,$risk){
    if($risk > 0){return 'risk_0';}
    $risk = abs($risk);
    $deposit = intval($deposit);
    if(!$deposit || !$risk){return 'risk_0';}
    $num = $deposit ? numberFormat(($risk / $deposit) * 100,1) : 0;
    $result = 'risk_0';
    switch ($num){
        case ($num > 0.01 && $num <= 10):
            $result = 'risk_1';
            break;
        case ($num > 10 && $num <= 30):
            $result = 'risk_2';
            break;
        case ($num > 30 && $num <= 50):
            $result = 'risk_3';
            break;
        case ($num > 50 && $num <= 70):
            $result = 'risk_4';
            break;
        case ($num > 70 && $num <= 90):
            $result = 'risk_5';
            break;
        case $num > 90:
            $result = 'risk_6';
            break;
    }
    return $result;
}

/**
 * 解析秒
 * 返回 小时:分钟:秒的格式
 *
 * @param $second
 *
 * @return string
 */
function parse_second2($second) {
    $hour = 0;
    $minute = 0;
    if ($second >= 3600) {
        $hour = (int) ($second / 3600);
        $second = $second % 3600;
    }
    if ($second >= 60) {
        $minute = (int) ($second / 60);
        $second = $second % 60;
    }
    return $hour . '：' . sprintf('%02d', $minute) . '：' . sprintf('%02d', $second);
}

//查询用户常用菜单
function commonMenu($user_id,$leftMenu,$topMenu){
    $commonMenu = cache("user_common_menu_{$user_id}");
    if(!$commonMenu){
        $common = \think\Db::name("common_menu")->where("user_id",$user_id)->find();
        $menu = array();
        if($common){
            $commonMenu = json_decode($common["menu"],true);
        }
    }
    $list = array();
    if($commonMenu){
        foreach ($commonMenu as $cm){
            $urlArr = explode("_", $cm);
            $key = $urlArr[0]."_".$urlArr[1];
            $data = $leftMenu[$urlArr[0]];
            foreach ($data as $d){
                if($d["ename"] == $key){
                    $dlist = array();
                    foreach ($d["list"] as $dl){
                        if($dl["ename"] == $cm){
                            $dl["name"] = $topMenu[$urlArr[0]]["name"]."-".$dl["name"];
                            $dlist = $dl;
                        }
                    }
                    $list[] = $dlist;
                }
            }
    
        }
    }
    return $list;
}

/**
 * 提现渠道预警，按用户注册时间
 */
function userWithdrawalDitchWarn($ditchId,$userRegTime){
    static $warnData;
    if(!$warnData){
        $warnData = cache('user_withdrawal_ditch_warn');
    }
    if(!isset($warnData[$ditchId])){return false;}
    $data = $warnData[$ditchId];
    foreach($data as $val){
        if($val['btime'] && $userRegTime >= $val['btime']){
            if($val['etime'] && $val['etime'] >= $userRegTime){
                return true;
            }elseif(!$val['etime']){
                return true;
            }
        }
    }
    return false;
}