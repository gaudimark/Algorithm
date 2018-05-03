<?php
//会员管理
namespace app\admin\controller;

use library\service\Message;
use library\service\Play;
use library\service\Rule;
use library\service\Socket;
use think\Controller;
use think;
use think\Cache;
use app\library\service\Misc;

Class User extends \app\admin\logic\Basic{
    public function __construct(){
        parent::__construct();
        $this->model = model("user");
        $this->betmodel = model("bet");
        
    }
        /**
     * 用户中奖后缓存数据
     **/
    public function test()
    {
        //用户中奖并超过阀值时，写入缓存
        for($i=0;$i<=100;$i++){
            echo $i+'<br>';
            Cache::rpush('user_earn_side_data',json_encode(['cpid'=>mt_rand(1,99),'amount'=>mt_rand(1,99)]));
        }
        //echo '<pre>';
       // ECHO Cache::llen('user_earn_side_data');
       // echo '<br>';
      // print_r(Cache::redisLtrim('user_earn_side_data',3));
    }
    //用户管理
    public function index(){
        $where = [];
        $username = input("get.username");
        $online = input("get.online");
        $status = input("status/d",0);
        $user_type = input("user_type/d",0);
        $user_classify = input("user_classify/d",0);
        $btime = input("btime");
        $etime = input("etime");
        $lbtime = input("lbtime");
        $letime = input("letime");
        if($status){
            $where["status"] = $status;
        }
        if($username){
            if($user_type == 1){
                $where["user_number"] = (int)$username;
            }elseif($user_type == 2){
                $where["u.id"] = (int)$username;
            }elseif($user_type == 4){
                $where["guid"] = $username;
            }elseif($user_type == 3){
                $where["u.nickname"] = ["like","%$username%"];;
            }elseif ($user_type == 5){
                $where["mobile"] = $username;
            }elseif ($user_type == 6){
                $where['reg_ip'] = $username;
            }elseif ($user_type == 7){
                $where['last_login_ip'] = $username;
            }
            
        }
        if($user_classify == 1){
            $where["has_common"] = 0;
            $where["has_visitor"] = 0;
            $where["has_robot"] = 0;
        }elseif($user_classify == 2){
            $where["has_visitor"] = 1;
        }elseif($user_classify == 3){
            $where["has_robot"] = 1;
        }elseif($user_classify == 4){
            $where["has_common"] = 1;
        }elseif($user_classify == 5){
            $where["has_robot"] = 0;
        }elseif($user_classify == 6){
            $where["u.has_bind"] = 1;
        }elseif($user_classify == 7){
            if(cache("user_guid_reg_data")){
                $guid = implode(",",cache("user_guid_reg_data"));
                $where["u.guid"] = ["in",$guid];
            }else{
                $where["u.guid"] = [["neq",""],["eq",""]];
            }
        }elseif($user_classify == 8){
            if(cache("user_ip_reg_data")){
                $ip = implode(",",cache("user_ip_reg_data"));
                $where["u.reg_ip"] = ["in",$ip];
            }else{
                $where["u.reg_ip"] = [["neq",""],["eq",""]];
            }
        }

        if($online && $online == 2){
            $where['has_online'] = 1;
        }

        if($btime && $etime){
            $where['u.reg_time'] = [['egt',strtotime($btime)],['elt',strtotime(date("Y-m-d 23:59:59",strtotime($etime)))]];
        }elseif($btime ){
            $where['u.reg_time'] = ['egt',strtotime($btime)];
        }elseif($etime ){
            $where['u.reg_time'] = ['elt',strtotime(date("Y-m-d 23:59:59",strtotime($etime)))];
        }
        if($lbtime && $letime){
            $where['u.last_login_time'] = [['egt',strtotime($lbtime)],['elt',strtotime(date("Y-m-d 23:59:59",strtotime($letime)))]];
        }elseif($lbtime ){
            $where['u.last_login_time'] = ['egt',strtotime($lbtime)];
        }elseif($letime ){
            $where['u.last_login_time'] = ['elt',strtotime(date("Y-m-d 23:59:59",strtotime($letime)))];
        }

        $sort = [];
        //排序
        $sortOpt = input('sort_opt');
        $sortValue = input('sort_value','desc');
        if($sortOpt){
            $_GET['sort_value'] = $sortValue;
            if($sortOpt == 'bet_total'){
                $sort['deposit_money'] = $sortValue;
            }elseif($sortOpt){
                $sort[$sortOpt] = $sortValue;
            }
        }

        $sort['id'] = 'desc';
        $param = input("param.");
        //$smallGameDataBase = config('db.small_game');
        //$joinTable = $smallGameDataBase['database'].".".$smallGameDataBase['prefix']."game_user_info";
        $user_list = $this->model->alias('u') ->where($where)
            ->order($sort)->paginate(20,false,['query' => $param]);//($where,20,$sort,$param);
        foreach ($user_list as $key => $val) {
            $val = $val->toArray();
            $user_list[$key] = $val;
        }
        $this->assign("list",$user_list);
        $this->assign("username",$username);
        $this->assign("status",$status);
        $this->assign("nickname",input("nickname"));
        $this->assign("mobile",input("mobile"));
        $this->assign("online",$online);
        $this->assign("user_type",$user_type);
        $this->assign("user_classify",$user_classify);
        $this->assign("btime",$btime);
        $this->assign("etime",$etime);
        $this->assign("lbtime",$lbtime);
        $this->assign("letime",$letime);
        return $this->fetch();
    }

    /**
     * 设置为特殊用户
     */
    public function set_common(){
        if($this->request->isPost()){
            $id = input("id", 0, 'intval');
            if($id){
                $user = think\Db::name('user')->where(['id' => $id])->find();
                $result = [];
                if($user['has_common']){
                    $result['text'] = '设为特殊用户';
                    $result['value'] = 0;
                    think\Db::name('user')->where(['id' => $id])->update(['has_common' => 0]);
                }else{
                    $result['text'] = '取消特殊用户';
                    $result['value'] = 1;
                    think\Db::name('user')->where(['id' => $id])->update(['has_common' => 1]);
                }
                return $this->success("操作成功",'',$result);
            }else{
                return $this->error("操作失败");
            }
        }
        return $this->error("参数异常");
    }

    //相关帐号
    public function same_account(){
        $same = input('same');
        $userId = input('user_id/d');
        $where = [];
        if(!$userId){
            return $this->error("参数异常");
        }
        $user = think\Db::name('user')->where(['id' => $userId])->find();
        $type = 'seal_user_all_ip';
        if($same == 'ip'){
            $where['reg_ip'] = $user['reg_ip'];
        }elseif($same == 'guid'){
            $where['guid'] = $user['guid'];
            $type = 'seal_user_all_guid';
        }
        $where['id'] = ['neq',$userId];
        $user_list = $this->model->getUserListByWhere($where,20,"id desc",input());
        foreach ($user_list as $k=>$v){
            //最近七天战绩
            $top_bonus = $this->model->getTopBonusByID($user_list[$k]["id"],2);
            $user_list[$k]["top_bonus"] = $top_bonus;
        }

        $task_queue = \library\service\Task::getQueue("{$type}_{$userId}");
        $this->assign("list",$user_list);
        $this->assign("user_id",$userId);
        $this->assign("same",$same);
        $this->assign("task_queue",$task_queue);
        return $this->fetch();
    }

    /**
     * 用户详情
     */
    public function info(){
        $id = input("id/d");
        $opt_value = input("opt_value",'today');
        if(!$id){return $this->error("无效用户信息");}
        $user = $this->model->where(['id' => $id])->find()->toArray();

        $user['account'] = [];
        $user['top_bonus'] = $this->model->getTopBonusByID($id,2);
        $this->assign("user",$user);
        $this->assign("opt_value",$opt_value);
        return $this->fetch();
    }

    //登录日志
    public function login_log(){

        $id = input("id",0,"intval");
        $param = input("param.");
        $where = [];
        $where["ul.user_id"] = $id;
        $where["ul.classify"] = USER_LOG_LOGIN;
        $log_list = $this->model->getUserLogList($where, 10,"ul.id desc",$param);
        foreach($log_list as $key => $val){
            $val['data'] = @json_decode($val['data'],true);
            $log_list[$key] = $val;
        }
        $this->assign("list",$log_list);
        return $this->fetch("user/login_log");
    }
    //操作日志
    public function opt_log(){

        $id = input("id",0,"intval");
        $param = input("param.");
        $where = [];
        $where["ul.user_id"] = $id;
        $where["ul.classify"] = ['in',[USER_LOG_OPT,USER_LOG_KICK]];
        $log_list = $this->model->getUserLogList($where, 10,"ul.id desc",$param);
        $this->assign("list",$log_list);
        return $this->fetch("user/opt_log");
    }


    public function memberLog_sys(){
        $param = input("param.");
        $start_time = input("start_time");
        $end_time = input("end_time");
        $classify = input("classify");
        $user_type = input("user_type/d");
        $username = input("username");
        $type = input("type");
        $choose = input("choose");
        $toxls = input("toxls/d");

        if($start_time||$end_time){
            $choose = "";
        }
        $where = [];
        if($type){
            if($type == 1){
                $where["ufl.number"] = [">",0];
            }elseif($type == 2){
                $where["ufl.number"] = ["<",0];
            }
        }
        if($username){
            if($user_type == 1){
                $where["u.user_number"] = (int)$username;
            }elseif($user_type == 2){
                $where["u.id"] = (int)$username;
            }elseif($user_type == 3){
                $where["u.nickname"] = ["like","%$username%"];;
            }
        }
        if($classify){
            $classifyIds = [];
            switch ($classify){
                case FUNDS_CLASSIFY_SYS_REC :
                    $classifyIds = [FUNDS_CLASSIFY_SYS_REC];
                    break;
                case FUNDS_CLASSIFY_SYS_DED :
                    $classifyIds = [FUNDS_CLASSIFY_SYS_DED];
                    break;
            }
            if($classifyIds){
                $where['ufl.classify'] = ['in', $classifyIds];
            }
        }else{
            $where['ufl.classify'] = ['in', [FUNDS_CLASSIFY_SYS_REC,FUNDS_CLASSIFY_SYS_DED]];
        }



        if($start_time&&$end_time){
            $where["ufl.create_time"][] = [">=",strtotime($start_time)];
            $where["ufl.create_time"][] = ["<=",strtotime($end_time)];
        }else{
            if($start_time){
                $where["ufl.create_time"] = [">=",strtotime($start_time)];
            }
            if($end_time){
                $where["ufl.create_time"] = ["<=",strtotime($end_time)];
            }
        }
        if($choose){
            if($choose == "today"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m-d'))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d',strtotime('+1 day')))];
            }elseif($choose == "yestoday"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m-d',strtotime('-1 day')))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d'))];
            }elseif($choose == "month"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m'))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d',strtotime('+1 day')))];
            }
        }

        if($toxls){ //导出excel
            $lists = $this->model->table(getTrueTableName('user_funds_log'))->alias("ufl")
                ->join("__USER__ u","u.id = ufl.user_id","LEFT")
                ->limit(65535)
                ->field("ufl.id,u.nickname,u.user_number,ufl.type,ufl.before_num,ufl.number,ufl.after_num,ufl.explain,ufl.create_time")
                ->where($where)->order('ufl.id asc')->select();
            $lists = modelToArray($lists);
            foreach($lists as $key => $val){
                $val['type'] = $val['number'] > 0 ? '收入' : '支出';
                $val['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
                $lists[$key] = $val;
            }
            $fileName = "账户明细";
            return (new \library\service\Misc())->toXls($fileName,[
                'create_time' => '日期',
                'user_number' => '用户编号',
                'nickname' => '消费者',
                'type' => '收支',
                'before_num' => '操作前金额',
                'number' => '金额',
                'after_num' => '操作后金额',
                'explain' => '原因',
            ],$lists);
        }else{
            //$userModel = new userModel;
            $list = $this->model->getUserFundsLogList($where,20,"ufl.id desc", $param);
            //echo $this->model->getlastsql();
            //汇总数据
            $totalNum = $this->model->getUserFundsLogCount($where);
            $this->assign("total",$totalNum["total"]);
            $this->assign("list",$list);
            $this->assign("username",$username);
            $this->assign("classify",$classify);
            $this->assign("choose",$choose);
            if(!$choose){
                $this->assign("start_time",$start_time);
                $this->assign("end_time",$end_time);
            }else{
                $this->assign("start_time","");
                $this->assign("end_time","");
            }
            $this->assign("type",$type);
            $this->assign("user_type",$user_type);
            return $this->fetch("user/memberlog_sys");
        }
    }
    
    //账户明细
    public function memberLog(){
        $param = input("param.");
        $start_time = input("start_time");
        $end_time = input("end_time");
        $classify = input("classify");
        $user_type = input("user_type/d");
        $username = input("username");
        $type = input("type");
        $choose = input("choose");
        $toxls = input("toxls/d");
        
        if($start_time||$end_time){
            $choose = "";
        }
        $where = [];
        if($type){
            if($type == 1){
                $where["ufl.number"] = [">",0];
            }elseif($type == 2){
                $where["ufl.number"] = ["<",0];
            }
        }
        if($username){
            if($user_type == 1){
                $where["u.user_number"] = (int)$username;
            }elseif($user_type == 2){
                $where["u.id"] = (int)$username;
            }elseif($user_type == 3){
                $where["u.nickname"] = ["like","%$username%"];;
            }
        }
        if($classify){
            $classifyIds = [];
            switch ($classify){
                case FUNDS_CLASSIFY_DEP:
                    $classifyIds = [FUNDS_CLASSIFY_DEP,FUNDS_CLASSIFY_WIN_DEP,FUNDS_CLASSIFY_VIEW_REC,FUNDS_CLASSIFY_VIEW_DED,FUNDS_CLASSIFY_AGENT_DEP];
                    break;
                case FUNDS_CLASSIFY_ARE :
                    $classifyIds = [FUNDS_CLASSIFY_ARE,FUNDS_CLASSIFY_WIN_ARE,FUNDS_CLASSIFY_ADD_ARE,FUNDS_CLASSIFY_DIS_ARE];
                    break;
                case FUNDS_CLASSIFY_REC :
                    $classifyIds = [FUNDS_CLASSIFY_REC];
                    break;
                case FUNDS_CLASSIFY_WD :
                    $classifyIds = [FUNDS_CLASSIFY_WD];
                    break;
                case FUNDS_CLASSIFY_TASK :
                    $classifyIds = [FUNDS_CLASSIFY_TASK];
                    break;
                case FUNDS_CLASSIFY_CREDIT :
                    $classifyIds = [FUNDS_CLASSIFY_CREDIT];
                    break;
                case FUNDS_CLASSIFY_BANK_DEC :
                    $classifyIds = [FUNDS_CLASSIFY_BANK_DEC,FUNDS_CLASSIFY_BANK_INC];
                    break;
                case FUNDS_CLASSIFY_SYS_REC :
                    $classifyIds = [FUNDS_CLASSIFY_SYS_REC];
                    break;
                case FUNDS_CLASSIFY_SYS_DED :
                    $classifyIds = [FUNDS_CLASSIFY_SYS_DED];
                    break;
                case FUNDS_CLASSIFY_FREEZE :
                    $classifyIds = [FUNDS_CLASSIFY_FREEZE];
                    break;
                case FUNDS_CLASSIFY_UNFREEZE :
                    $classifyIds = [FUNDS_CLASSIFY_UNFREEZE];
                    break;
                case FUNDS_CLASSIFY_GIFT_GOLD :
                    $classifyIds = [FUNDS_CLASSIFY_GIFT_GOLD,FUNDS_CLASSIFY_SYS_REC];
                    break;
                default:
                    $classifyIds = [(int)$classify];
                    break;
            }
            if($classifyIds){
                $where['ufl.classify'] = ['in', $classifyIds];
            }
            //$where["ufl.type"] = ["=",$type];
        }
        if($start_time&&$end_time){
            $where["ufl.create_time"][] = [">=",strtotime($start_time)];
            $where["ufl.create_time"][] = ["<=",strtotime($end_time)];
        }else{
            if($start_time){
                $where["ufl.create_time"] = [">=",strtotime($start_time)];
            }
            if($end_time){
                $where["ufl.create_time"] = ["<=",strtotime($end_time)];
            }
        }
        if($choose){
            if($choose == "today"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m-d'))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d',strtotime('+1 day')))];
            }elseif($choose == "yestoday"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m-d',strtotime('-1 day')))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d'))];
            }elseif($choose == "month"){
                $where["ufl.create_time"][] = [">=",strtotime(date('Y-m'))];
                $where["ufl.create_time"][] = ["<=",strtotime(date('Y-m-d',strtotime('+1 day')))];
            }
        }
        if($toxls){ //导出excel
            $lists = $this->model->table(getTrueTableName('user_funds_log'))->alias("ufl")
                ->join("__USER__ u","u.id = ufl.user_id","LEFT")
                ->limit(65535)
                ->field("ufl.id,u.nickname,u.user_number,ufl.type,ufl.before_num,ufl.number,ufl.after_num,ufl.explain,ufl.create_time")
                ->where($where)->order('ufl.id asc')->select();
            $lists = modelToArray($lists);
            foreach($lists as $key => $val){
                $val['type'] = $val['number'] > 0 ? '收入' : '支出';
                $val['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
                $lists[$key] = $val;
            }
            $fileName = "账户明细";
            return (new \library\service\Misc())->toXls($fileName,[
                'create_time' => '日期',
                'user_number' => '用户编号',
                'nickname' => '消费者',
                'type' => '收支',
                'before_num' => '操作前金额',
                'number' => '金额',
                'after_num' => '操作后金额',
                'explain' => '原因',
            ],$lists);
        }else{
            //$userModel = new userModel;
            $list = $this->model->getUserFundsLogList($where,20,"ufl.id desc", $param);
            $numberTotal = $this->model->getUserCharge($where);//金额汇总
            foreach ($list as $k=>$l){//判断擂台所属类别
                $game_type = "";
                if($l["classify"] == FUNDS_CLASSIFY_DEP || $l["classify"] == FUNDS_CLASSIFY_WIN_DEP){
                    $data = json_decode($l["data"],true);
                    $arenaSrv = new \library\service\Arena();
                    if(isset($data["data"]["arena_id"])){
                        $arena = $arenaSrv->getCacheArenaById($data["data"]["arena_id"]);
                        if($arena){
                            $game_type = $arena["game_type"];
                        }
                    }elseif(isset($data["arena_id"])){
                        $arena = $arenaSrv->getCacheArenaById($data["arena_id"]);
                        if($arena){
                            $game_type = $arena["game_type"];
                        }
                    }
                }
                $list[$k]["game_type"] = $game_type;
            }
            $this->assign("total",round($numberTotal,2));
            $this->assign("list",$list);
            $this->assign("username",$username);
            $this->assign("classify",$classify);
            $this->assign("choose",$choose);
            if(!$choose){
                $this->assign("start_time",$start_time);
                $this->assign("end_time",$end_time);
            }else{
                $this->assign("start_time","");
                $this->assign("end_time","");
            }
            $this->assign("type",$type);
            $this->assign("user_type",$user_type);
            return $this->fetch("user/memberlog");
        }
    }



    //查看投注
    public function userArenaBet(){
        $id = input("id",0,"intval");
        $param = input("param.");
        if($id){
            $where = [];
            $where["abd.user_id"] = $id;
            $playSvr = new Play();
            $list = model('bet')->getBetList($where,"abd.create_time DESC",10,$param);
            foreach($list as $key => $val){
                $ruleSvr = (new Rule())->factory($val['game_type']);
                $odds = @json_decode($val['arena_odds'],true);
                $teams = $playSvr->getTeams($val['play_id'],['name']);
                $val['teams'] = $teams;
                $val['arena_odds'] = $ruleSvr->parseOddsWords($odds,$val['rules_id'],$teams);
                //$val->play = $this->playSvr->getPlay($val['play_id'],['play_time','status']);
                $val['match'] = getMatch($val['match_id'],null,['name']);
                //$val['rules_id'] = $val['rules_type'];
                //$val['rules_type'] = $ruleSvr->getRuleType($val['rules_id']);
                $list[$key] = $val;
            }
            $this->assign("list",$list);
            $this->assign("id",$id);
            return $this->fetch("user/userarenabet");
        }else{
            return $this->error("参数异常");
        } 
    }

    
}
