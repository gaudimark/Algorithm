<?php

namespace app\admin\controller\stat;
use app\admin\logic\Basic;
use library\service\Misc;
use think\Db;

class User extends Basic{
    private $time = 15;
    //注册
    public function userReg(){        
        $userModel = model("user");
        //注册数量
        $allReg = $userModel->where(["has_robot"=>0,"has_visitor"=>0,"status"=>1])->count();
        $newReg = array();
        for($i=0;$i<=$this->time;$i++){
            $beginTime = strtotime(date("Y-m-d",strtotime("-{$i} day")));
            $endTime = $beginTime+24*3600;
            $query = [];
            $query["has_robot"] = 0;
            $query["status"] = 1;
            $query["reg_time"][] = [">=",$beginTime];
            $query["reg_time"][] = ["<",$endTime];
            $query["has_visitor"] = 0;
            $query["has_robot"] = 0;
            $data = $userModel->where($query)->select();
            $newReg[date("Ymd",$beginTime)] = ['total' => count($data),'date' => date("m月d",$beginTime)];
        }
        //print_r($newReg);
        $this->assign("allReg",$allReg);
        $this->assign("newReg",$newReg);
        return $this->fetch();
    }

    //用户活跃数
    public function userActiveOLD(){
        $userModel = model("user");
        //活跃用户
        $sevenActive = array();
        $ouserWeekActive = 0;
        $ovisitorWeekActive = 0;
        for($i=0;$i<=$this->time;$i++){
            if($i == 0)
                $time = strtotime(date("Y-m-d"));
            else
                $time = strtotime(date("Y-m-d",strtotime("-{$i} day")));
            //注册用户
            $where = [];
            $where["ul.create_time"][] = [">=",$time];
            $where["ul.create_time"][] = ["<",$time+24*3600];
            $user_where = $where;
            $user_where["u.has_visitor"] = 0;
            $user_num = $userModel->getUserActive($user_where);
            //游客
            $visitor_where = $where;
            $visitor_where["u.has_visitor"] = 1;
            $visitor_num = $userModel->getUserActive($visitor_where);
            
            $sevenActive[date("Ymd",$time)] = ['user_total' => $user_num,'visitor_total' => $visitor_num,'date' => date("m月d",$time)];
            //$ouserWeekActive += $user_num;
            //$ovisitorWeekActive += $visitor_num;  
        }
        //最近一周活跃用户数量
        $ouserWeekWhere = [];
        $ouserWeekWhere["ul.create_time"][] = [">=",strtotime(date("Y-m-d",strtotime("-7 day")))];
        $ouserWeekWhere["ul.create_time"][] = ["<=",time()];
        $ouserWeekWhere["u.has_visitor"] = 0;
        $ouserWeekActive = $userModel->getUserActive($ouserWeekWhere);
        //最近一周活跃游客数量
        $ovisitorWeekWhere = [];
        $ovisitorWeekWhere["ul.create_time"][] = [">=",strtotime(date("Y-m-d",strtotime("-7 day")))];
        $ovisitorWeekWhere["ul.create_time"][] = ["<=",time()];
        $ovisitorWeekWhere["u.has_visitor"] = 1;
        $ovisitorWeekActive = $userModel->getUserActive($ovisitorWeekWhere);

        $tuserWeekActive = 0;
        $tvisitorWeekActive = 0;
        /*for($i=$this->time+1;$i<$this->time+8;$i++){
            $time = strtotime(date("Y-m-d",strtotime("-{$i} day")));
            $where = [];
            $where["ul.create_time"][] = [">=",$time];
            $where["ul.create_time"][] = ["<",$time+24*3600];
            //注册用户
            $user_where = $where;
            $user_where["u.has_visitor"] = 0;
            $tuserWeekActive += $userModel->getUserActive($user_where);
            //游客
            $visitor_where = $where;
            $visitor_where["u.has_visitor"] = 1;
            $tvisitorWeekActive += $userModel->getUserActive($visitor_where);
        }*/
        
        //后一周活跃用户数量
        $vendTime = $this->time-7;
        $tuserWeekWhere = [];
        $tuserWeekWhere["ul.create_time"][] = [">=",strtotime(date("Y-m-d",strtotime("-{$this->time} day")))];
        $tuserWeekWhere["ul.create_time"][] = ["<=",strtotime(date("Y-m-d",strtotime("-{$vendTime} day")))];
        $tuserWeekWhere["u.has_visitor"] = 0;
        $tuserWeekActive = $userModel->getUserActive($tuserWeekWhere);
        //后一周活跃游客数量
        $tvisitorWeekWhere = [];
        $tvisitorWeekWhere["ul.create_time"][] = [">=",strtotime(date("Y-m-d",strtotime("-{$this->time} day")))];
        $tvisitorWeekWhere["ul.create_time"][] = ["<=",strtotime(date("Y-m-d",strtotime("-{$vendTime} day")))];
        $tvisitorWeekWhere["u.has_visitor"] = 1;
        $tvisitorWeekActive = $userModel->getUserActive($tvisitorWeekWhere);

        $this->assign("weekActive",array($ouserWeekActive,$ovisitorWeekActive,$tuserWeekActive,$tvisitorWeekActive));
        $this->assign("sevenActive",$sevenActive);
        return $this->fetch();
    }
    //充值
    public function recharge(){
        $beginTime = strtotime(date("Y-m-d",strtotime("-{$this->time} day")));
        $endTime = time();
        //$recharge_type = [FUNDS_CLASSIFY_REC,FUNDS_CLASSIFY_SYS_REC];
        $userModel = model("user");

        //用户充值总数
        $all_recharge_sum = \think\Db::name('user_funds_log')->where(array("classify"=>FUNDS_CLASSIFY_REC))->sum("number");
        //时间段内用户充值的金额
        $time_recharge_sum = \think\Db::name('user_funds_log')->where(["classify"=>FUNDS_CLASSIFY_REC,"create_time"=>[[">=",$beginTime],["<=",$endTime]]])->sum("number");
        //新增用户充值金额
        $new_where = [];
        $new_where["ufl.classify"] = FUNDS_CLASSIFY_REC;
        $new_where["u.reg_time"][] = [">=",$beginTime];
        $new_where["u.reg_time"][] = ["<=",$endTime];
        $new_recharge_sum = $userModel->getUserCharge($new_where);
        //充值人数（总）
        $all_recharge_where = [];
        $all_recharge_where["ufl.classify"] = FUNDS_CLASSIFY_REC;
        $all_recharge_where["u.status"] = 1;
        $all_recharge_where["u.has_robot"] = 0;
        $all_recharge_count = $userModel->countUserCharge($all_recharge_where,"u.id");
        //充值人数
        $time_recharge_where = [];
        $time_recharge_where["ufl.classify"] = FUNDS_CLASSIFY_REC;
        $time_recharge_where["u.status"] = 1;
        $time_recharge_where["u.has_robot"] = 0;
        $time_recharge_where["ufl.create_time"][] = [">=",$beginTime];
        $time_recharge_where["ufl.create_time"][] = ["<=",$endTime];
        $time_recharge_count = $userModel->countUserCharge($time_recharge_where,"u.id");
        //新增用户充值人数
        $new_user_where = [];
        $new_user_where["ufl.classify"] = FUNDS_CLASSIFY_REC;
        $new_user_where["u.status"] = 1;
        $new_user_where["u.has_robot"] = 0;
        $new_user_where["u.reg_time"][] = [">=",$beginTime];
        $new_user_where["u.reg_time"][] = ["<=",$endTime];
        $new_recharge_count = $userModel->countUserCharge($new_user_where,"u.id");
        //付费ARPPU（总）
        if($all_recharge_count > 0)
            $all_arppu = round($all_recharge_sum/$all_recharge_count,2);
        else
            $all_arppu = 0;
        //付费ARPPU
        if($time_recharge_count > 0)
            $time_arppu = round($time_recharge_sum/$time_recharge_count,2);
        else
            $time_arppu = 0;
        //新增付费ARPPU
        if($new_recharge_count > 0)
            $new_user_arppu = round($new_recharge_sum/$new_recharge_count,2);
        else
            $new_user_arppu = 0;
        //充值率（总）= 充值人数（总）/独立用户（总）x100%
        $user_number = Db::name('user')->where(["has_robot"=>0,"status"=>1])->count();
        if($user_number > 0){
            $all_recharge_rate = round($all_recharge_count/$user_number,4)*100;
        }else{
            $all_recharge_rate = 0;
        }
        //充值率=充值人数/活跃独立用户x100%
        $active_num = $userModel->getUserActive(["ul.create_time"=>[[">=",$beginTime],["<=",$endTime]],"u.has_robot"=>0]);
        if($active_num > 0){
            $time_recharge_rate = round($time_recharge_count/$active_num,4)*100;
        }else{
            $time_recharge_rate = 0;
        }
        //新增用户充值率= 新增用户充值人数/新增独立用户x100%
        $new_user_number = Db::name('user')->where(["has_robot"=>0,"status"=>1,"reg_time"=>[[">=",$beginTime],["<=",$endTime]]])->count();
        if($new_user_number > 0){
            $new_recharge_rate = round($new_recharge_count/$new_user_number,4)*100;
        }else{
            $new_recharge_rate = 0;
        }
        $this->assign("all_recharge_sum",$all_recharge_sum);
        $this->assign("time_recharge_sum",$time_recharge_sum);
        $this->assign("new_recharge_sum",$new_recharge_sum);
        $this->assign("all_recharge_count",$all_recharge_count);
        $this->assign("time_recharge_count",$time_recharge_count);
        $this->assign("new_recharge_count",$new_recharge_count);
        $this->assign("all_arppu",$all_arppu);
        $this->assign("time_arppu",$time_arppu);
        $this->assign("new_user_arppu",$new_user_arppu);
        $this->assign("all_recharge_rate",$all_recharge_rate);
        $this->assign("time_recharge_rate",$time_recharge_rate);
        $this->assign("new_recharge_rate",$new_recharge_rate);
        return $this->fetch();
    }

    //各等级人数
    public function level(){
        $userModel = model("user");
        $user = $userModel->where(["has_robot"=>0,"status"=>1])->field("level,count(*) as a")->group("level")->select();
        $userSrv = new \library\service\User;
        $level = $userSrv->getUserLevel();
        foreach ($level as $k=>$v){
            $v["num"] = 0;
            foreach ($user as $u){
                if($v["name"] == "LV".$u["level"]){
                    $v["num"] = $u["a"];
                }
            }
            $level[$k] = $v;
        }
        $this->assign("level",$level);
        return $this->fetch();
    }

//=========================================

    /**
     * 新增用户统计
     */
    public function new_user(){
        $export = input("export");
        $btime = input("btime");
        $etime = input("etime");
        $ditch = input("ditch/d");
        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        if($ditch){
            $where['ditch_number'] = $ditch;
        }
        if($export){
            $title = [
                's_date' => '日期',
                'ditch_name' => '渠道',
                'new_user_num' => '新增账号',
                'new_ip_num' => '新增独立用户(IP)',
                'total_visitor_num' => '新增游客数',
                'total_user_num' => '总账号数',
                'total_ip_num' => '独立用户(IP)',
            ];
            $list = modelN('stat_user_new')->where($where)->order('s_date desc')->select();
            foreach($list as  $key => $val){
                $val = $val->toArray();
                $val['s_date'] = date("Y-m-d",$val['s_date']);
                $val['ditch_name'] = $val['ditch_number'] && isset($this->ditchlist[$val['ditch_number']]) ? $this->ditchlist[$val['ditch_number']]['name'] : '--';
                $list[$key] = $val;
            }
            return (new Misc())->toXls('新增用户',$title,$list);
        }
        $list = modelN('stat_user_new')->where($where)->order('s_date desc')->paginate(20,false,[
            'query' => input()
        ]);
        $this->assign('lists',$list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('ditch',$ditch);
        return $this->fetch();
    }

    /**
     * 日活跃用户
     */
    public function user_active_day(){
        $this->userActive(1);
        return $this->fetch();
    }

    /**
     * 周活跃用户
     * @return mixed
     */
    public function user_active_week(){
        $this->userActive(2);
        return $this->fetch();
    }

    /**
     * 月活跃用户
     * @return mixed
     */
    public function user_active_month(){
        $this->userActive(3);
        return $this->fetch();
    }
    private function userActive($type){
        $btime = input("btime");
        $etime = input("etime");
        $ditch = input("ditch");
        $where = [];

        if(!$btime){
            switch ($type){
                case 1:
                    $btime = date("Y-m-d",strtotime('-15 day'));
                    break;
                case 2:
                    $btime = date("Y-m-d",mktime(0,0,0,date('m')-3,1,date("Y")));
                    break;
                case 3:
                    $btime = date("Y-m-d",mktime(0,0,0,date('m')-6,1,date("Y")));
                    break;
            }
        }
        if(!$etime){
            $etime = date("Y-m-d");
        }



        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        if($ditch){
            $where['ditch'] = $ditch;
        }

        if(input('export')){
            $title = '';
            if($type == 1){
                $list = modelN('stat_user_active_day')->where($where)->order('s_date desc')->select();
                $title = '日活跃用户';
            }elseif($type == 2){
                $list = modelN('stat_user_active_week')->where($where)->order('s_date desc')->select();
                $title = '周活跃用户';
            }elseif($type == 3){
                $list = modelN('stat_user_active_month')->where($where)->order('s_date desc')->select();
                $title = '月活跃用户';
            }
            $header = [
                's_date' => '日期',
                'ditch_name' => '渠道',
                'active_user_num' => '活跃账号',
                'active_mac_num' => '活跃独立用户(IP)',
            ];
            if($list){
                foreach ($list as $key => $val){
                    $val = $val->toArray();
                    $val['ditch_name'] = $val['ditch'] ? $this->ditchlist[$val['ditch']]['name'] : '--';
                    $val['s_date'] = date("Y-m-d",$val['s_date']);
                    $list[$key] = $val;
                }
            }
            return (new Misc())->toXls($title,$header,$list);
        }

        if($type == 1){
            $list = modelN('stat_user_active_day')->where($where)->order('s_date desc')->paginate(20, false, ['query' => input()]);
        }elseif($type == 2){
            $list = modelN('stat_user_active_week')->where($where)->order('s_date desc')->paginate(20, false, ['query' => input()]);
        }elseif($type == 3){
            $list = modelN('stat_user_active_month')->where($where)->order('s_date desc')->paginate(20, false, ['query' => input()]);
        }
        $this->assign('lists',$list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('ditch',$ditch);
    }

    /**
     * 用户留存
     */
    public function re_user(){
        $btime = input("btime");
        $etime = input("etime");
        $ditch = input("ditch");

        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        if($ditch){
            $where['ditch'] = $ditch;
        }else{
            $where['ditch'] = 0;
        }
        $field = array(
            's_date',
            'ditch',
            'KindID',
            'platform',
            'user_add',
            'keep2days',
            '(keep2days/user2days) as keep2days_percent',
            'keep3days',
            '(keep3days/user3days) as keep3days_percent',
            'keep7days',
            '(keep7days/user7days) as keep7days_percent',
            'keep15days',
            '(keep15days/user15days) as keep15days_percent',
            'keep30days',
            '(keep30days/user30days) as keep30days_percent',
            'keep45days',
            '(keep45days/user45days) as keep45days_percent',
            'mac_add',
            'mac_keep2days',
            '(mac_keep2days/usermac2days) as mac_keep2days_percent',
            'mac_keep3days',
            '(mac_keep3days/usermac3days) as mac_keep3days_percent',
            'mac_keep7days',
            '(mac_keep7days/usermac7days) as mac_keep7days_percent',
            'mac_keep15days',
            '(mac_keep15days/usermac15days) as mac_keep15days_percent',
            'mac_keep30days',
            '(mac_keep30days/usermac30days) as mac_keep30days_percent',
            'mac_keep45days',
            '(mac_keep45days/usermac45days) as mac_keep45days_percent',
            'pay_add',
            'pay_keep2days',
            '(pay_keep2days/pay_add) as pay_keep2days_percent',
            'pay_keep3days',
            '(pay_keep3days/pay_add) as pay_keep3days_percent',
            'pay_keep7days',
            '(pay_keep7days/pay_add) as pay_keep7days_percent',
            'pay_keep15days',
            '(pay_keep15days/pay_add) as pay_keep15days_percent',
            'pay_keep30days',
            '(pay_keep30days/pay_add) as pay_keep30days_percent',
            'pay_keep45days',
            '(pay_keep45days/pay_add) as pay_keep45days_percent',
        );
        if(input('export')){
            $list = modelN('stat_user_keep')->field($field)->where($where)->order('s_date desc')->select();
            foreach($list as $key => $item){
                $item = $item->toArray();
                $item['s_date'] = date("Y-m-d",$item['s_date']);
                $item['ditch_name'] = $item['ditch'] ? $this->ditchlist[$item['ditch']]['name'] : '--';
                $item = $this->_parseUserKeep($item);
                $list[$key] = $item;
            }
            $header = array(
                's_date' => '日期',
                'user_add' => '新增登录账号数',
                'keep2days' => '次日留存人数',
                'keep2days_percent' => '次日留存率',
                'keep3days' => '3日留存人数',
                'keep3days_percent' => '3日留存率',
                'keep7days' => '7日留存人数',
                'keep7days_percent' => '7日留存率',
                'keep15days' => '15日留存人数',
                'keep15days_percent' => '15日留存率',
                'keep30days' => '30日留存人数',
                'keep30days_percent' => '30日留存率',
                'keep45days' => '45日留存人数',
                'keep45days_percent' => '45日留存率',
                'mac_add' => '新增独立用户数',
                'mac_keep2days' => '次日留存独立用户数',
                'mac_keep2days_percent' => '次日留存率',
                'mac_keep3days' => '3日留存独立用户数',
                'mac_keep3days_percent' => '3日留存率',
                'mac_keep7days' => '7日留存独立用户数',
                'mac_keep7days_percent' => '7日留存率',
                'mac_keep15days' => '15日留存独立用户数',
                'mac_keep15days_percent' => '15日留存率',
                'mac_keep30days' => '30日留存独立用户数',
                'mac_keep30days_percent' => '30日留存率',
                'mac_keep45days' => '45日留存独立用户数',
                'mac_keep45days_percent' => '45日留存率',
                'pay_add' => '新增充值用户',
                'pay_keep2days' => '次日留存新增充值用户数',
                'pay_keep2days_percent' => '次日留存率',
                'pay_keep3days' => '3日留存新增充值用户数',
                'pay_keep3days_percent' => '3日留存率',
                'pay_keep7days' => '7日留存新增充值用户数',
                'pay_keep7days_percent' => '7日留存率',
                'pay_keep15days' => '15日留存新增充值用户数',
                'pay_keep15days_percent' => '15日留存率',
                'pay_keep30days' => '30日留存新增充值用户数',
                'pay_keep30days_percent' => '30日留存率',
                'pay_keep45days' => '45日留存新增充值用户数',
                'pay_keep45days_percent' => '45日留存率',
            );
            return (new Misc())->toXls('用户留存导出数据',$header,$list);
        }else{
            $list = modelN('stat_user_keep')->where($where)->order('s_date desc')->paginate(20, false, ['query' => input()]);
            foreach($list as $key => $item){
                $item = $item->toArray();
                $item['ditch_list'] = [];
                $item['ditch_list_count'] = 1;
                if(!$ditch){
                    $where['ditch'] = ['gt',0];
                    $where['s_date'] = $item['s_date'];
                    $ditchData = modelN('stat_user_keep')->where($where)->order('s_date desc')->select();
                    if($ditchData){
                        foreach($ditchData as $val){
                            $val = $val->toArray();
                            $item['ditch_list'][] = $val;
                        }
                    }
                    $item['ditch_list_count'] = count($ditchData) + 1;
                }
                $list[$key] = $item;
            }
        }

        $this->assign('lists',$list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('ditch',$ditch);
        $this->assign('dateArr',[2,3,7,15,30,45]);
        return $this->fetch();
    }

    private function _parseUserKeep($item){
        if ($item['s_date'] + 86400 * 45 > strtotime(date('Y-m-d'))) {
            $item['keep45days_percent'] = '--';
            $item['keep45days'] = '--';
            $item['mac_keep45days_percent'] = '--';
            $item['mac_keep45days'] = '--';
            $item['win_keep45days_percent'] = '--';
            $item['win_keep45days'] = '--';
            $item['lose_keep45days_percent'] = '--';
            $item['lose_keep45days'] = '--';
            $item['pay_keep45days_percent'] = '--';
            $item['pay_keep45days'] = '--';
        } else {
            $item['keep45days_percent'] = number_format((@$item['keep45days_percent'] * 100), 2) . '%';
            $item['mac_keep45days_percent'] = number_format((@$item['mac_keep45days_percent'] * 100), 2) . '%';
            $item['win_keep45days_percent'] = number_format((@$item['win_keep45days_percent'] * 100), 2) . '%';
            $item['lose_keep45days_percent'] = number_format((@$item['lose_keep45days_percent'] * 100), 2) . '%';
            $item['pay_keep45days_percent'] = number_format((@$item['pay_keep45days_percent'] * 100), 2) . '%';
        }
        if ($item['s_date'] + 86400 * 30 > strtotime(date('Y-m-d'))) {
            $item['keep30days_percent'] = '--';
            $item['keep30days'] = '--';
            $item['mac_keep30days_percent'] = '--';
            $item['mac_keep30days'] = '--';
            $item['win_keep30days_percent'] = '--';
            $item['win_keep30days'] = '--';
            $item['lose_keep30days_percent'] = '--';
            $item['lose_keep30days'] = '--';
            $item['pay_keep30days_percent'] = '--';
            $item['pay_keep30days'] = '--';
        } else {
            $item['keep30days_percent'] = number_format((@$item['keep30days_percent'] * 100), 2) . '%';
            $item['mac_keep30days_percent'] = number_format((@$item['mac_keep30days_percent'] * 100), 2) . '%';
            $item['win_keep30days_percent'] = number_format((@$item['win_keep30days_percent'] * 100), 2) . '%';
            $item['lose_keep30days_percent'] = number_format((@$item['lose_keep30days_percent'] * 100), 2) . '%';
            $item['pay_keep30days_percent'] = number_format((@$item['pay_keep30days_percent'] * 100), 2) . '%';
        }
        if ($item['s_date'] + 86400 * 15 > strtotime(date('Y-m-d'))) {
            $item['keep15days_percent'] = '--';
            $item['keep15days'] = '--';
            $item['mac_keep15days_percent'] = '--';
            $item['mac_keep15days'] = '--';
            $item['win_keep15days_percent'] = '--';
            $item['win_keep15days'] = '--';
            $item['lose_keep15days_percent'] = '--';
            $item['lose_keep15days'] = '--';
            $item['pay_keep15days_percent'] = '--';
            $item['pay_keep15days'] = '--';
        } else {
            $item['keep15days_percent'] = number_format((@$item['keep15days_percent'] * 100), 2) . '%';
            $item['mac_keep15days_percent'] = number_format((@$item['mac_keep15days_percent'] * 100), 2) . '%';
            $item['win_keep15days_percent'] = number_format((@$item['win_keep15days_percent'] * 100), 2) . '%';
            $item['lose_keep15days_percent'] = number_format((@$item['lose_keep15days_percent'] * 100), 2) . '%';
            $item['pay_keep15days_percent'] = number_format((@$item['pay_keep15days_percent'] * 100), 2) . '%';
        }
        if ($item['s_date'] + 86400 * 7 > strtotime(date('Y-m-d'))) {
            $item['keep7days_percent'] = '--';
            $item['keep7days'] = '--';
            $item['mac_keep7days_percent'] = '--';
            $item['mac_keep7days'] = '--';
            $item['win_keep7days_percent'] = '--';
            $item['win_keep7days'] = '--';
            $item['lose_keep7days_percent'] = '--';
            $item['lose_keep7days'] = '--';
            $item['pay_keep7days_percent'] = '--';
            $item['pay_keep7days'] = '--';
        } else {
            $item['keep7days_percent'] = number_format((@$item['keep7days_percent'] * 100), 2) . '%';
            $item['mac_keep7days_percent'] = number_format((@$item['mac_keep7days_percent'] * 100), 2) . '%';
            $item['win_keep7days_percent'] = number_format((@$item['win_keep7days_percent'] * 100), 2) . '%';
            $item['lose_keep7days_percent'] = number_format((@$item['lose_keep7days_percent'] * 100), 2) . '%';
            $item['pay_keep7days_percent'] = number_format((@$item['pay_keep7days_percent'] * 100), 2) . '%';
        }
        if ($item['s_date'] + 86400 * 3 > strtotime(date('Y-m-d'))) {
            $item['keep3days_percent'] = '--';
            $item['keep3days'] = '--';
            $item['mac_keep3days_percent'] = '--';
            $item['mac_keep3days'] = '--';
            $item['win_keep3days_percent'] = '--';
            $item['win_keep3days'] = '--';
            $item['lose_keep3days_percent'] = '--';
            $item['lose_keep3days'] = '--';
            $item['pay_keep3days_percent'] = '--';
            $item['pay_keep3days'] = '--';
        } else {
            $item['keep3days_percent'] = number_format((@$item['keep3days_percent'] * 100), 2) . '%';
            $item['mac_keep3days_percent'] = number_format((@$item['mac_keep3days_percent'] * 100), 2) . '%';
            $item['win_keep3days_percent'] = number_format((@$item['win_keep3days_percent'] * 100), 2) . '%';
            $item['lose_keep3days_percent'] = number_format((@$item['lose_keep3days_percent'] * 100), 2) . '%';
            $item['pay_keep3days_percent'] = number_format((@$item['pay_keep3days_percent'] * 100), 2) . '%';
        }
        if ($item['s_date'] + 86400 > strtotime(date('Y-m-d'))) {
            $item['keep2days_percent'] = '--';
            $item['keep2days'] = '--';
            $item['mac_keep2days_percent'] = '--';
            $item['mac_keep2days'] = '--';
            $item['win_keep2days_percent'] = '--';
            $item['win_keep2days'] = '--';
            $item['lose_keep2days_percent'] = '--';
            $item['lose_keep2days'] = '--';
            $item['pay_keep2days_percent'] = '--';
            $item['pay_keep2days'] = '--';
        } else {
            $item['keep2days_percent'] = number_format((@$item['keep2days_percent'] * 100), 2) . '%';
            $item['mac_keep2days_percent'] = number_format((@$item['mac_keep2days_percent'] * 100), 2) . '%';
            $item['win_keep2days_percent'] = number_format((@$item['win_keep2days_percent'] * 100), 2) . '%';
            $item['lose_keep2days_percent'] = number_format((@$item['lose_keep2days_percent'] * 100), 2) . '%';
            $item['pay_keep2days_percent'] = number_format((@$item['pay_keep2days_percent'] * 100), 2) . '%';
        }
        return $item;
    }

    public function user_leave(){
        $btime = input("btime");
        $etime = input("etime");
        $export = input("export");
        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }

        if($export){
            $title = [
                's_date' => '日期',
                'leave_user_num' => '流失账号数',
                'leave_user_num_avg' => '账号流失率',
                'leave_mac_num' => '流失独立用户数',
                'leave_mac_num_avg' => '独立用户流失率',
            ];
            $list = modelN('stat_user_leave')->where($where)->order('s_date desc')->select();
            foreach($list as  $key => $val){
                $val = $val->toArray();
                $val['s_date'] = date("Y-m-d",$val['s_date']);
                $val['leave_user_num_avg'] = $val['login_user_num'] ? numberFormat(($val['leave_user_num']/$val['login_user_num']) * 100,2)."%" : '0.00%';
                $val['leave_mac_num_avg'] = $val['login_mac_num'] ? numberFormat(($val['leave_mac_num']/$val['login_mac_num']) * 100,2)."%" : '0.00%';
                $list[$key] = $val;
            }
            return (new Misc())->toXls('用户流失',$title,$list);
        }


        $list = modelN('stat_user_leave')->where($where)->order('s_date desc')->paginate(20, false, ['query' => input()]);
        $this->assign('lists',$list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        return $this->fetch();
    }


    //用户使用流失统计
    public function user_lost(){
        if(input('export')){
            return $this->_export_user_lost();
        }
        $btime = input("btime");
        $etime = input("etime");
        $ditch = input("ditch");

        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        if($ditch){
            $where['ditch'] = $ditch;
        }
        $list = modelN('stat_user_lost')->where($where)->order('s_date desc')->paginate(20,false,['query' => input()]);
        if($list){
            foreach($list as $key => $item){
                $item = $item->toArray();
                $item['data'] = json_decode($item['data'],true);
                $list[$key] = $item;
            }
        }

        $lostList = config('game_lost');
        $this->assign('lostList',$lostList);
        $this->assign('lists',$list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('ditch',$ditch);
        return $this->fetch();
    }

    private function _export_user_lost(){
        $btime = input("btime");
        $etime = input("etime");

        $lostList = config('game_lost');
        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }

        $data = [];
        $list = modelN('stat_user_lost')->where($where)->order('s_date')->paginate(20,false,['query' => input()]);
        if($list){
            foreach($list as $key => $item){
                $item = $item->toArray();
                $ld = json_decode($item['data'],true);
                $tmp = [
                    's_date' => date("Y-m-d",$item['s_date']),
                    'ditch_name' => $item['ditch'] ? $this->ditchlist[$item['ditch']]['name'] : '--',
                ];
                foreach($lostList as $lk => $l){
                    $tmp[$lk] = isset($ld[$lk]) ? (int)$ld[$lk] : 0;
                }
                $data[] = $tmp;

            }
        }
        $title = [];
        $title['s_date'] = '日期';
        $title['ditch_name'] = '渠道';
        foreach($lostList as $key => $val){
            $title[$key] = $val;
        }
        (new \library\service\Misc())->toXls('用户使用流失统计',$title,$data);
    }

    public function promoter(){
        $btime = input("btime");
        $etime = input("etime");
        $user_type = input("user_type");
        $user = input("user");
        $where = [];
        if($btime && $etime){
            $where['p.s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['p.s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['p.s_date'] = ['elt',strtotime($etime)];
        }
        if($user){
            if($user_type == 1){
                $where['u.user_number'] = $user;
            }elseif($user_type == 2){
                $where['u.nickname'] = $user;
            }elseif($user_type == 3){
                $where['u.id'] = $user;
            }
        }

        $list = modelN('stat_user_promoter')->alias('p')
            ->field('p.*,u.nickname,u.promoter_brokerage_total')
            ->join('__USER__ u','u.id=p.user_id','LEFT')
            ->where($where)->order('p.s_date desc')->paginate(20,false,['query' => input()]);

        $total = modelN('stat_user_promoter')->alias('p')->join('__USER__ u','u.id=p.user_id','LEFT')
            ->where($where)->SUM('p.brokerage');

        $this->assign('lists', $list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('user_type',$user_type);
        $this->assign('user',$user);
        $this->assign('total',$total);
        return $this->fetch();
    }

    public function promoter_info(){
        $btime = input("btime");
        $etime = input("etime");
        $user_type = input("user_type");
        $user = input("user");
        $user_id = input("user_id/d",0);
        $where = [];
        $where['p.parent_id'] = $user_id;
        if($btime && $etime){
            $where['p.s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif($btime){
            $where['p.s_date'] = ['egt',strtotime($btime)];
        }elseif($etime){
            $where['p.s_date'] = ['elt',strtotime($etime)];
        }
        if($user){
            if($user_type == 1){
                $where['u.user_number'] = $user;
            }elseif($user_type == 2){
                $where['u.nickname'] = $user;
            }elseif($user_type == 3){
                $where['u.id'] = $user;
            }
        }

        $list = modelN('stat_user_promoter_info')->alias('p')
            ->field('p.*,u.nickname,up.brokerage_total')
            ->join('__USER__ u','u.id=p.user_id','LEFT')
            ->join('__USER_PROMOTER__ up','up.user_id=p.user_id','LEFT')
            ->where($where)->order('p.s_date desc')->paginate(20,false,['query' => input()]);

        $this->assign('lists', $list);
        $this->assign('etime',$etime);
        $this->assign('btime',$btime);
        $this->assign('user_type',$user_type);
        $this->assign('user',$user);
        return $this->fetch();
    }





}
