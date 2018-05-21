<?php

namespace app\admin\controller;
use app\admin\logic\Basic;

class Role extends Basic{
    public function index(){
        $model = model("Role");
        $list = $model->paginate();
        $this->assign("list",$list);
        return $this->fetch();
    }
    public function add(){

        $model = model("Role");
        if($this->request->isPost()){
            $id = input("post.id");
            $name = input("post.name");
            $status = input("post.status");
            $lib = input("post.lib/a");
            $other = input("post.other/a");
            if(!$lib){
                return $this->error("没有选择权限，无法新增角色");
            }
            $temp = [];
            if($lib) {
                foreach ($lib as $key => $val) {
                    $key = strtolower($key);
                    $temp[$key] = array_keys($val);
                }
            }
            $data['limit'] = json_encode($temp);
            $temp = [];
            if($other) {
                foreach ($other as $key => $val) {
                    $key = strtolower($key);
                    $temp[$key] = array_keys($val);
                }
            }
            $data['other'] = json_encode($temp);
            $data['name'] = $name;
            $data['status'] = $status;
            $where = [];
            if($id){
                $where['id'] = $id;
            }
            if($model->save($data,$where)){
                return $this->success("操作成功",url("role/index"));
            }else{
                return $this->error("操作失败");
            }
        }

        $permit = model("permit")->select();
        if(!$permit){
            return $this->error("未找到权限点，无法新增角色");
        }
        $permit = modelToArray($permit);
        foreach($permit as $key => $val){
            $permit[$key]['content'] = @json_decode($val['content'],true);
        }
        $permit = arrayTree($permit,'id','parent_id');
        
        $id = input("id",0,'intval');
        if($id){
            $res = $model->get($id);
            if($res){
                $res = $res->toArray();
                $res['limit'] = @json_decode($res['limit'],true);
                $res['other'] = @json_decode($res['other'],true);
            }
            $this->assign("res",$res);
        }

        $items = [
            GAME_TYPE_FOOTBALL => 'items.football',
        ];

        $this->assign("items",$items);
        $this->assign("permit",$permit);
        return $this->fetch();
    }

    public function del(){
        if($this->request->post()){
            $id = input("id",0,'intval');
            $manager = model("manager")->where(['role_id' => $id])->find();
            if($manager){
                return $this->error("角色删除失败，角色下还拥有管理成员");
            }
            $model = model("Role");
            if($model->where(['id' => $id])->delete()){
                return $this->success("删除成功");
            }else{
                return $this->error("删除失败".$model->getError().",");
            }
        }
    }
}