<?php

namespace app\admin\controller;
use app\admin\logic\Basic;

class Log extends Basic{

    public function index(){
        $where = [];
        $classify = input("param.classify",1,'intval');
        $method = input("get.method");
        $btime = input("get.btime");
        $etime = input("get.etime");
        $query['classify'] = $classify;
        $where['classify'] = $classify;
        if($method){
            $method = strtoupper($method);
            $where['method'] = in_array($method,['POST','GET']) ? $method : [['neq','GET'],['neq','POST']];
            $query['method'] = strtolower($method);
        }
        if($btime && $etime){
            $where['create_time'] = [['>=',strtotime($btime)],['<=',strtotime($etime)]];
        }elseif($btime){
            $where['create_time'] = ['>=',strtotime($btime)];
        }elseif($etime){
            $where['create_time'] = ['<=',strtotime($etime)];
        }
        $adminList = model("manager")->select();
        $adminList = modelToArray($adminList,'id');
        $list = model("log")->where($where)->order("id desc")->paginate(50,false,[
            'query' => input("get.")
        ]);
        $this->assign("adminList",$adminList);
        $this->assign("list",$list);
        $this->assign('method',strtolower($method));
        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        $this->assign('classify',$classify);
        return $this->fetch();
    }

    public function sms(){
        $list = modelN("send_log")->order("id desc")->paginate(50,false,[
            'query' => input("get.")
        ]);
        $this->assign("list",$list);
        return $this->fetch();
    }
    public function curse(){
        $type = input('type');
        $user_type = input('user_type');
        $nickname = input('nickname');
        $export = input('export');
        $btime = input("get.btime");
        $etime = input("get.etime");
        $where = [];
        if($btime && $etime){
            $where['l.create_time'] = [['>=',strtotime($btime)],['<=',strtotime($etime)]];
        }elseif($btime){
            $where['l.create_time'] = ['>=',strtotime($btime)];
        }elseif($etime){
            $where['l.create_time'] = ['<=',strtotime($etime)];
        }
        if($type){
            $where['l.type'] = $type;
        }
        if($user_type && $nickname){
            if($user_type == 1){
                $where["u.user_number"] = (int)$nickname;
            }elseif($user_type == 2){
                $where["u.id"] = (int)$nickname;
            }elseif($user_type == 4){
                $where["u.guid"] = $nickname;
            }elseif($user_type == 3){
                $where["u.nickname"] = ["like","%$nickname%"];;
            }
        }

        if($export) {
            $list = modelN("curse_log")->alias('l')
                ->field('l.*,u.user_number,u.nickname')
                ->join('user u', 'u.id=l.user_id', 'left')
                ->where($where)
                ->order('l.create_time asc')
                ->select();
            if($list) {
                foreach ($list as $key => $val) {
                    $temp = [
                        'create_time' => date("Y-m-d H:i:s",$val['create_time']),
                        'nickname' => $val['nickname'],
                        'user_number' => $val['user_number'],
                        'type' => $val['type'] == STATUS_ENABLED ? '标注' : '取消',
                        'manager' => isset($this->admin_user_list[$val['admin_id']]) ? $this->admin_user_list[$val['admin_id']] : $val['admin_id'],
                    ];
                    $list[$key] = $temp;
                }
            }
            $title = [];
            $title['create_time'] = '日期';
            $title['nickname'] = '用户昵称';
            $title['user_number'] = '用户编号';
            $title['manager'] = '管理员';
            $title['type'] = '标注类型';
            return (new \library\service\Misc())->toXls('骂人标注日志',$title,$list);
        }else {
            $list = modelN("curse_log")->alias('l')
                ->field('l.*,u.user_number,u.nickname')
                ->join('user u', 'u.id=l.user_id', 'left')
                ->where($where)
                ->order('l.create_time desc')->paginate(50, false, [
                    'query' => input("get.")
                ]);
        }
        $this->assign("list",$list);
        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        $this->assign("type",$type);
        $this->assign("user_type",$user_type);
        $this->assign("nickname",$nickname);
        return $this->fetch();
    }
    
}