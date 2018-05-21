<?php
namespace app\admin\controller;
use app\admin\logic\Basic;

class Permit extends Basic{

    public function index(){
        $Permit = model("Permit")->findAll();
        $this->assign("permit",$Permit);
        return $this->fetch();
    }

    public function add(){
        $model = model("Permit");
        if($this->request->isPost()){
            $id = input("post.id",0,'intval');
            $data = [];
            $data['parent_id'] = input("post.parent_id/d");
            $data['name'] = input("post.name");
            //$data['conf'] = input("post.conf/a");
            $where = [];
            if($id){
                $where = ['id' => $id];
            }
            if($model->save($data,$where)){
                return $this->success("操作成功");
            }else{
                return $this->error("操作失败");
            }
        }

        $id = input("id",0,'intval');
        if($id){
            $res = $model->get($id)->toArray();
            $pid = 0;
            if($res['parent_id']){
                $p = $model->get($res['parent_id'])->toArray();
                $pid = $p['parent_id'];
            }
            $this->assign("pid",$pid);
            $this->assign("res",$res);
        }

        $menu = $model->findAll("id,parent_id,name");
        $this->assign("id",$id);
        $this->assign("menu",$menu);
        return $this->fetch();
    }

    public function point(){
        $model = model("Permit");
        if($this->request->isPost()){
            $id = input("post.id/d");
            $conf = input('post.conf/a');
            $temp = [];
            foreach ($conf['name'] as $key => $val){
                $temp[] = [
                    'name' => $val,
                    'controller' => strtolower($conf['controller'][$key]),
                    'action' => strtolower($conf['action'][$key]),
                ];
            }
            $data['content'] = json_encode($temp);
            if($model->save($data,['id' => $id])){
                return $this->success("操作成功");
            }else{
                return $this->error("操作失败");
            }
        }
        $id = input("id",0,'intval');
        $res = $model->get($id)->toArray();
        $res['content'] = @json_decode($res['content'],true);
        $this->assign("res",$res);
        $this->assign("id",$id);
        return $this->fetch();
    }

    public function del(){
        $model = model("Permit");
        if($this->request->isPost()){
            $id = input("id");
            if(!$id){return $this->error("参数错误");}
            $res = $model->where(['parent_id' => $id])->find();
            if($res){
                return $this->error("删除失败，该模块下还有子模块");
            }

            if($model->where(["id" => $id])->delete()){
                return $this->success("删除成功");
            }
            return $this->error("删除失败");
        }
    }
}