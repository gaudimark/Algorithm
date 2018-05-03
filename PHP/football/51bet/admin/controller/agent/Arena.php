<?php

namespace app\admin\controller\agent;
use app\admin\logic\Basic;
use library\service\Play;
use library\service\Rule;
use think\Db;

class Arena extends Basic{

    public function index(){
        $puser = input("puser");
        $where = [];
        if($puser){
            $where['au.username'] = ['like',"%{$puser}%"];
            $lists = model("Agent")->getUser($where,20,'au.id desc',[
                'puser' => $puser
            ]);
        }else{
            $lists = model("Agent")->where($where)->order("id desc")->paginate(20,false,['query' => input()]);
        }
        $this->assign("lists",$lists);
        $this->assign("puser",$puser);
        return $this->fetch();
    }

    public function info(){

        if ($this->request->isPost()){
            $id = input("post.id/d");
            $rate = input("post.rate/d");
            if(!$id || !$rate){
                return $this->error("无效参数");
            }
            Db::name("agent_user")->where(['id' => $id])->update([
                'rate' => $rate
            ]);
            //cacheAgentUser
            (new \app\library\service\Agent())->cacheAgentUser($id);
            return $this->success("提成比例修改成功");
        }


        $id = input("id/d");
        if(!$id){
            return $this->error("无效参数");
        }
        $user = model("Agent")->where(['id' => $id])->find();
        $this->assign("user",$user);
        return $this->fetch();
    }

    public function modifypassword(){
        $id = input("post.id/d");
        if(!$id){return $this->error("无效参数");}
        $user = model("Agent")->where(['id' => $id])->find();
        if(!$user){
            return $this->error("查看用户信息失败");
        }
        $svr = new \library\service\Agent();
        if(false !== $pwd = $svr->resetPassword($user['user_id'],$id)){
            return $this->success("密码重置成功",'',['pwd' => $pwd]);
        }
        return $this->error($svr->getError());
    }

    public function arena(){
        $id = input("id/d");
        $param = '';
        $list = [];
        $model = model("arena");

        $rulesID = input('rule',0,'intval');
        $status = input('status',0,'intval');
        $rulesList = cache("rules.".GAME_TYPE_FOOTBALL);
        $where = [];
        if($rulesID){
            $where['rules_type'] = $rulesID;
        }

        $where['agent_user_id'] = $id;
        $where['match'] = input("match");
        $where['item'] = input("item");
        $where['play_time'] = input("play_time");
        $where['risk'] = input("risk");
        $where['mark'] = input("mark");
        $where['nickname'] = input("nickname");
        $where['status'] = input("status");
        $iOrder = input("order");
        $iOrderField = input("order_field");
        if($iOrder && $iOrderField){
            $iOrder = strtolower($iOrder);
            $order = "{$iOrderField} {$iOrder},a.id desc";
            $iOrder = $iOrder== "desc" ? "asc" : "desc";
        }else {
            $order = "a.id desc";
        }
        $param = input("param.");
        unset($param['id']);
        $list = $model->findAgentArenaAll($where,$order,20,$param);
        
        unset($param['order']);
        unset($param['order_field']);
        unset($param['page']);
        $param1 = http_build_query($param);
        if($param1){
            $param1 .= "&";
        }
        $this->assign("iOrder",$iOrder);
        $this->assign("iOrderField",$iOrderField);
        $this->assign("list",$list);
        $this->assign("param",$param1);
        $this->assign("rulesList",$rulesList);
        $this->assign("rule",$rulesID);
        $this->assign("status",$status);
        $this->assign("id",$id);
        $this->assign($where);
        return $this->fetch();
    }
    
    //投注列表
    public function bet(){
        $agent_id = input("id/d");
        $arena_id = input("aid/d");
        if(!$agent_id){
            return $this->error("无效参数");
        }
        if(!$arena_id){
            return $this->error("无效参数");
        }
        
        $play_id = input('play_id');
        $nickname = input('nickname');
        $bet_id = input('bet_id');
        $status = input('status');
        $where = [];
        if($play_id){
            $where['p.id'] = $play_id;
        }
        if($nickname){
            $where['u.nickname'] = ['like',"%{$nickname}%"];
        }
        if($bet_id){
            $where['abd.id'] = $bet_id;
        }
        if($status){
            $where['abd.status'] = $status;
        }
        $where["abd.arena_id"] = $arena_id;
        $where["abd.agent_id"] = $agent_id;
        $query = [
            'nickname' => $nickname,
            'play_id' => $play_id,
            'bet_id' => $bet_id,
            'status' => $status,
        ];
        $playSvr = (new Play());
        $list = model('bet')->getBetList($where,"abd.create_time DESC",20,$query);
        $list = modelToArray($list);
        foreach($list as $key => $val){
            $ruleSvr = (new Rule())->factory($val['game_type']);
            $odds = @json_decode($val['arena_odds'],true);
            $teams = $playSvr->getTeams($val['play_id'],['name']);
            $val['teams'] = $teams;
            $val['arena_odds'] = $ruleSvr->parseOddsWords($odds,$val['rules_id'],$teams);
            //$val->play = $this->playSvr->getPlay($val['play_id'],['play_time','status']);
            $val['match'] = getMatch($val['match_id'],null,['name']);

            //$val['rules_id'] = $val['rules_type'];
           // $val['rules_type'] = $ruleSvr->getRuleType($val['rules_id']);
            $list[$key] = $val;
        }
        $play = [];
        if($play_id){
            $play = $playSvr->getPlay($play_id);
            $match = getMatch($play['match_id']);
            $play['match'] = $match;
        }
        $this->assign("play",$play);
        $this->assign("list",$list);
        $this->assign("nickname",$nickname);
        $this->assign($query);
        
        $this->assign("list",$list);
        return $this->fetch();
    }

}