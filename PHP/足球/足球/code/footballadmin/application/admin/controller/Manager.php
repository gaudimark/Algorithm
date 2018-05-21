<?php

namespace app\admin\controller;
class Manager extends \app\admin\logic\Basic{

    public function index(){

        $model = model("Manager");
        $list = $model->paginate();
        $roleList = model("Role")->select();
        $roleList = arrayIndex($roleList,"id");
        $this->assign("roleList",$roleList);
        $this->assign("list",$list);
        return $this->fetch();
    }

    public function add(){
        $model = model("Manager");
        if($this->request->isPost()){
            $id = input("post.id");
            $data['role_id'] = input("post.role_id");
            $data['username'] = input("post.username");
            $data['password'] = input("post.password");
            $data['nickname'] = input("post.nickname");
            $data['status'] = input("post.status");

            if(!$data['role_id']){
                return $this->error("请选择所属角色");
            }

            if(!$id && !$data['username']){
                return $this->error("请输入用户名");
            }

            if(!$id && !$data['password']){
                return $this->error("请输入密码");
            }

            $where = [];
            if($id){
                $where['id'] = $id;
            }
            if($model->save($data,$where)){
                return $this->success("管理员设置成功");
            }else{
                return $this->error("操作失败(".$model->getError().")");
            }
        }

        $id = input("id");
        if($id){
            $res = $model->get($id);
            $this->assign("res",$res);
        }

        $roleList = model("Role")->where(['status' => STATUS_ENABLED])->select();
        $this->assign("roleList",$roleList);
        $this->assign("id",$id);
        return $this->fetch();
    }

    public function del(){
        if($this->request->isPost()){
            $id = input("id");
            if(model("Manager")->where(['id' => $id])->delete()){
                return $this->success("删除成功");
            }else{
                return $this->error("删除失败");
            }
        }
    }
}