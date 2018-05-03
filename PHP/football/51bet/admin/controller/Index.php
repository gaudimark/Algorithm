<?php
namespace app\admin\controller;
use app\admin\logic\Basic;
use library\service\Misc;
use app\admin\logic\Menu;

class Index extends Basic{

    public function test(){
        //var_dump(checkPermit('items.all',['risk','risk_total']));
    }

    public function index(){
        return $this->dashboard();
    }

    public function dashboard(){
        $miscSvr = new Misc();
        $todayIndex = date("Ymd");
        $yesterdayIndex = date("Ymd",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
        $todaySignTotal = $miscSvr->getCacheTotal('sign',$todayIndex); //今日注册用启
        $yesterdaySignTotal = $miscSvr->getCacheTotal('sign',$yesterdayIndex); //昨日注册用户数

        $todayArena = $miscSvr->getCacheTotal("arena",$todayIndex);//今日擂台数
        $yesterdayArena = $miscSvr->getCacheTotal("arena",$yesterdayIndex);//昨日擂台数

        $todayBetting = $miscSvr->getCacheTotal("betting",$todayIndex);//今日投注总额
        $yesterdayBetting = $miscSvr->getCacheTotal("betting",$yesterdayIndex);//昨日投注总额

        $todayTurnover = $miscSvr->getCacheTotal("turnover",$todayIndex);//今日流水
        $yesterdayTurnover = $miscSvr->getCacheTotal("turnover",$yesterdayIndex);//昨日投注总额

        $todayRecharge = $miscSvr->getCacheTotal("recharge",$todayIndex);//今日充值总额
        $yesterdayRecharge = $miscSvr->getCacheTotal("recharge",$yesterdayIndex);//昨日充值总额

        $todaySysIncome = $miscSvr->getCacheTotal("sys_income",$todayIndex);//今日系统收益
        $yesterdaySysIncome = $miscSvr->getCacheTotal("sys_income",$yesterdayIndex);//昨日系统收益

        $onLineTotal = $miscSvr->getOnline();//在线人数

        $this->assign("todaySignTotal",$todaySignTotal);
        $this->assign("yesterdaySignTotal",$yesterdaySignTotal);
        $this->assign("todayArena",$todayArena);
        $this->assign("yesterdayArena",$yesterdayArena);
        $this->assign("todayBetting",$todayBetting);
        $this->assign("yesterdayBetting",$yesterdayBetting);
        $this->assign("todayTurnover",$todayTurnover);
        $this->assign("yesterdayTurnover",$yesterdayTurnover);
        $this->assign("todayRecharge",$todayRecharge);
        $this->assign("yesterdayRecharge",$yesterdayRecharge);
        $this->assign("todaySysIncome",$todaySysIncome);
        $this->assign("yesterdaySysIncome",$yesterdaySysIncome);
        $this->assign("onLineTotal",$onLineTotal);
        return $this->fetch('index');
    }
    /**
     * 修改资料
     * @return mixed|void
     */
    public function profile(){
        if($this->request->isPost()){
            $data = [];
            $nickname = input("post.nickname");
            $avatar = input("post.avatar");
            if($nickname){
                $data['nickname'] = $nickname;
            }
            if($avatar){
                $data['avatar'] = $avatar;
            }
            if(model("Manager")->save($data,['id' => $this->admin_id])){
                model("Manager")->updateUserSession($this->admin_id);
                return $this->success("资料更新成功");
            }else{
                return $this->error("资料更新失败");
            }

        }
        return $this->fetch();
    }

    /**
     * 修改密码
     */
    public function password(){
        if($this->request->isPost()){
            $oldpwd = input("post.oldpwd");
            $newpwd = input("post.newpwd");
            $repwd = input("post.repwd");

            if(!$oldpwd || !$newpwd || !$repwd){
                return $this->error("请输入旧密码、新密码、重复新密码");
            }

            if($newpwd != $repwd){
                return $this->error("两次输入的新密码不相同，请重新输入");
            }
            $model = model('manager');
            if($model->upPwd($this->admin_id,$oldpwd,$newpwd)){
                return $this->success("密码更新成功");
            }else{
                return $this->error($model->getError());
            }


        }
        return $this->fetch();
    }
    
    /**
     * 常用模块
     * */
    public function hotmenu(){
        $menu = new Menu($this->request,$this->admin_user,$this->admin_role);
        $menu->parseMenu();
        $leftMenu = $menu->leftMenus;
        $topMenu = $menu->topMenus;
        $common = \think\Db::name("common_menu")->where("user_id",$this->admin_id)->find();
        $commonMenu = array();
        if($common){
            $commonMenu = json_decode($common["menu"],true);
        }
        foreach ($leftMenu as $k=>$val){
            foreach ($val as $t=>$m){
                if(isset($m["list"])){
                    foreach ($m["list"] as $v=>$l){
                        if(isset($l["ename"])){
                            if(in_array($l["ename"],$commonMenu)){
                                $m["list"][$v]["is_choose"] = 1;
                            }else{
                                $m["list"][$v]["is_choose"] = 0;
                            }
                            
                            $val[$t] = $m;
                        }
                    }
                    $val[$t]["name"] = $topMenu[$k]["name"]."-".$val[$t]["name"];
                }
                $leftMenu[$k] = $val;
            }
        }
        
        $this->assign("menuList",$leftMenu);
        return $this->fetch();
    }
    
    //操作常用模块
    public function chooseMenu(){
        if($this->request->isPost()){
            $ename = input("post.ename");
            $type = input("post.type");
            $is_all = input("post.is_all/d");
            if(!$ename){
                return $this->error("数据异常！");
            }
            $common = \think\Db::name("common_menu")->where("user_id",$this->admin_id)->find();
            if($is_all == 1){//批量添加
                $names = explode(",", $ename);
                if($common){
                    if(\think\Db::name("common_menu")->where("user_id",$this->admin_id)->update(array("menu"=>json_encode($names)))){
                        cache("user_common_menu_{$this->admin_id}",$names);
                        return $this->success("操作成功");
                    }else{
                        return $this->error("操作失败");
                    }
                }else{
                    $data = array();
                    $data["menu"] = json_encode($names);
                    $data["update_time"] = time();
                    $data["user_id"] = $this->admin_id;
                    $data["create_time"] = time();
                    if(\think\Db::name("common_menu")->insert($data)){
                        cache("user_common_menu_{$this->admin_id}",$names);
                        return $this->success("操作成功");
                    }else{
                        return $this->error("操作失败");
                    }
                }
            }else{
                $commonMenu = array();
                if($common){
                    $commonMenu = json_decode($common["menu"],true);
                }
                if($type == 0){//取消
                    if($commonMenu){
                        $n = array_search($ename, $commonMenu);
                        if($n !== false){
                            array_splice($commonMenu, $n,1);
                            $data = array();
                            $data["menu"] = json_encode($commonMenu);
                            $data["update_time"] = time();
                            if(\think\Db::name("common_menu")->where("user_id",$this->admin_id)->update($data)){
                                cache("user_common_menu_{$this->admin_id}",$commonMenu);
                                return $this->success("操作成功");
                            }else{
                                return $this->error("操作失败");
                            }
                        }
                    }
                }else{
                    array_push($commonMenu, $ename);
                    $data = array();
                    $data["menu"] = json_encode($commonMenu);
                    $data["update_time"] = time();
                    if($common){
                        if(\think\Db::name("common_menu")->where("user_id",$this->admin_id)->update($data)){
                            cache("user_common_menu_{$this->admin_id}",$commonMenu);
                            return $this->success("操作成功");
                        }else{
                            return $this->error("操作失败");
                        }
                    }else{
                        $data["user_id"] = $this->admin_id;
                        $data["create_time"] = time();
                        if(\think\Db::name("common_menu")->insert($data)){
                            cache("user_common_menu_{$this->admin_id}",$commonMenu);
                            return $this->success("操作成功");
                        }else{
                            return $this->error("操作失败");
                        }
                    }
                }
                
            } 
        }
    }
}