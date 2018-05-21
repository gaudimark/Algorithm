<?php
/**
 * 数据统计
 * */
namespace app\admin\controller\stat;

use app\admin\logic\Basic;
use think\Db;

class Count extends Basic{
    private $time = 7;
    public function __construct(){
        parent::__construct();
    }

    public function arpuRate(){
        $beginTime = strtotime(date("Y-m-d",strtotime("-{$this->time} day")));
        $endTime = time();
        $recharge_type = [FUNDS_CLASSIFY_REC,FUNDS_CLASSIFY_SYS_REC];

        $userModel = model("user");
        $all_recharge_sum = \think\Db::name('user_funds_log')->where(["classify"=>["in",$recharge_type]])->sum("number");
        //时间段内用户充值的金额
        $time_recharge_sum = \think\Db::name('user_funds_log')->where(["classify"=>["in",$recharge_type],"create_time"=>[[">=",$beginTime],["<=",$endTime]]])->sum("number");
        //活跃ARPU（总）= 充值金额(总)/活跃独立用户（总）
        $all_arpu_where = [];
        $all_arpu_where["ul.create_time"][] = [">=",$beginTime];
        $all_arpu_where["ul.create_time"][] = ["<",$endTime];
        $all_arpu_where["u.has_robot"] = 0;
        $all_num = $userModel->getUserActive($all_arpu_where);//活跃独立用户（总）
        if($all_num > 0)
            $active_arpu = round($all_recharge_sum/$all_num,2);
        else 
            $active_arpu = 0;
        //活跃ARPU=充值金额/活跃独立用户
        if($all_num > 0)
            $time_arpu = round($time_recharge_sum/$all_num,2);
        else 
            $time_arpu = 0;
        //新增活跃ARPU= 新增用户充值金额/新增活跃独立用户
        $newReg = Db::name('user')->where(["has_robot"=>0,"status"=>1,"reg_time"=>[[">=",$beginTime],["<=",$endTime]]])->count();
        $new_arpu_where = [];
        $new_arpu_where["u.has_robot"] = 0;
        $new_arpu_where["u.reg_time"] = [">=",$beginTime];
        $new_arpu_where["ufl.create_time"] = [">=",$beginTime];
        $new_reg_recharge = $userModel->getUserCharge($new_arpu_where);
        if($newReg > 0)
            $new_reg_arpu = round($new_reg_recharge/$newReg,2);
        else 
            $new_reg_arpu = 0;
        
        $this->assign("active_arpu",$active_arpu);
        $this->assign("time_arpu",$time_arpu);
        $this->assign("new_reg_arpu",$new_reg_arpu);
        return $this->fetch();
    }
    
    //LTV
    public function ltvData(){
        $recharge_type = [FUNDS_CLASSIFY_REC,FUNDS_CLASSIFY_SYS_REC];
        $userModel = model("user");
        //*LTV（首日）= 新增用户充值金额/新增独立用户
        $newReg = $userModel->where(["has_robot"=>0,"status"=>1,"reg_time"=>[">=",strtotime(date("Y-m-d"))]])->count();//新增人数
        $new_where = [];
        $new_where["ufl.classify"] = ["in",$recharge_type];
        $new_where["u.reg_time"] = [">=",strtotime(date("Y-m-d"))];
        $new_recharge_sum = $userModel->getUserCharge($new_where);
        if($newReg > 0)
            $first_ltv = round($new_recharge_sum/$newReg,2);
        else 
            $first_ltv = 0;
        
        //*LTV（总）= 充值金额(总)/独立用户（总）
        $all_recharge_sum = \think\Db::name('user_funds_log')->where(array("classify"=>["in",$recharge_type]))->sum("number");
        $user_number = $userModel->where(["has_robot"=>0,"status"=>1])->count();
        if($user_number >0)
            $all_ltv = round($all_recharge_sum/$user_number,2);
        else 
            $all_ltv = 0;
        
        //LTV（15日）= 15日累计充值金额/新增独立用户
        $user_number_half_month = $userModel->where(["has_robot"=>0,"status"=>1,"reg_time"=>[">=",strtotime(date("Y-m-d",strtotime("-15 day")))]])->count();
        $recharge_sum_half_month = \think\Db::name('user_funds_log')->where(["classify"=>["in",$recharge_type],"create_time"=>[">=",strtotime(date("Y-m-d",strtotime("-15 day")))]])->sum("number");
        if($user_number_half_month > 0)
            $half_month_ltv = round($recharge_sum_half_month/$user_number_half_month,2);
        else 
            $half_month_ltv = 0;
        
        //LTV（30日）= 30日累计充值金额/新增独立用户
        $user_number_month = $userModel->where(["has_robot"=>0,"status"=>1,"reg_time"=>[">=",strtotime(date("Y-m-d",strtotime("-30 day")))]])->count();
        $recharge_sum_month = \think\Db::name('user_funds_log')->where(["classify"=>["in",$recharge_type],"create_time"=>[">=",strtotime(date("Y-m-d",strtotime("-30 day")))]])->sum("number");
        if($user_number_month >0)
            $month_ltv = round($recharge_sum_month/$user_number_month,2);
        else 
            $month_ltv = 0;
        
        $this->assign("first_ltv",$first_ltv);
        $this->assign("all_ltv",$all_ltv);
        $this->assign("half_month_ltv",$half_month_ltv);
        $this->assign("month_ltv",$month_ltv);
        return $this->fetch();
        
    }
    
    //摆擂总数
    public function arena(){
        $beginTime = strtotime(date("Y-m-d",strtotime("-{$this->time} day")));
        $endTime = time();
        $arenaModel = model("arena");
       // 时间段内擂台总数
       $time_arena_count = $arenaModel->where(["create_time"=>[[">=",$beginTime],["<=",$endTime]],"status"=>["neq",ARENA_DEL]])->count();
       // 擂台保证金总额
       $all_arena_deposit = $arenaModel->where(["status"=>["neq",ARENA_DEL]])->sum("deposit");
       // 擂主输赢总额
       $all_arena_win = $arenaModel->where(["status"=>["eq",ARENA_STATEMENT_END]])->sum("win");
       // 时间段内新增擂主数
       $time_arena_user = $arenaModel->where(["create_time"=>[[">=",$beginTime],["<=",$endTime]],"status"=>["neq",ARENA_DEL]])->group("user_id")->count();
       $this->assign("time_arena_count",$time_arena_count);
       $this->assign("all_arena_deposit",$all_arena_deposit);
       $this->assign("all_arena_win",$all_arena_win);
       $this->assign("time_arena_user",$time_arena_user);
       return $this->fetch();
    }
    
    //输赢统计
    public function bet(){
        //时间段内输赢人数、金额统计
        $betModel = model("bet");
        $beginTime = strtotime(date("Y-m-d",strtotime("-{$this->time} day")));
        $endTime = time();
        $win_sum = $betModel->where(["status"=>DEPOSIT_WIN])->sum("win_money");
        $win_count = $betModel->where(["status"=>DEPOSIT_WIN])->group("user_id")->count();
        
        $all_lose_sum = $betModel->where(["status"=>DEPOSIT_LOSE])->sum("money");
        $half_lose_sum = $betModel->where(["status"=>DEPOSIT_LOST_HALF])->sum("money");
        $lose_sum = $all_lose_sum+$half_lose_sum/2;
        $lose_count = $betModel->where(["status"=>DEPOSIT_LOSE])->whereOr(["status"=>DEPOSIT_LOST_HALF])->group("user_id")->count();
        
        $this->assign("win_sum",$win_sum);
        $this->assign("win_count",$win_count);
        $this->assign("lose_sum",$lose_sum);
        $this->assign("lose_count",$lose_count);
        return $this->fetch();
    }
    
    //佣金
    public function commission(){
        $com = array();
        for($i=0;$i<=$this->time;$i++){
            $beginTime = strtotime(date("Y-m-d",strtotime("-{$i} day")));
            $endTime = $beginTime+24*3600;
            $query = [];
            $query["category"] = 1;
            $query["create_time"][] = [">=",$beginTime];
            $query["create_time"][] = ["<",$endTime];
            $com_sum = \think\Db::name('system_income')->where($query)->field("sum(number)  as t")->find();
            $com[date("Ymd",$beginTime)] = $com_sum["t"];
        }
        
        $this->assign("commission",$com);
        
        return $this->fetch();
    }
    
}

?>