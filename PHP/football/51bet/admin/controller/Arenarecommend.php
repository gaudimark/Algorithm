<?php
namespace app\admin\controller;

use app\admin\logic\Basic;
class Arenarecommend extends Basic{
    
    private $model = "";
    public function __construct(){
        parent::__construct();
        $this->model = model("arenarecommend");
    }
    
    public function index(){
        $param = input("param.");
        $list = $this->model->getList([],15,"sort desc",$param);
        
        $this->assign("list",$list);
        return $this->fetch();
    }
    
    //新增或者修改推荐擂台
    public function addArena(){
        if($this->request->isPost()){
            $id = input("id/d");
            $arena_id = input("post.arena_id/d");
            $sort = input("post.sort",1,"intval");
            $data = array();
            $data["arena_id"] = $arena_id;
            $data["sort"] = $sort;
            if($id){
                if(!false === $this->model->where("id",$id)->update($data)){
                    return $this->error("修改失败");
                }else{
                    return $this->success("修改成功");
                }
            }
            $data["create_time"] = time();
            if($this->model->insert($data)){
                return $this->success("新建成功");
            }else{
                return $this->error("新建失败");
            }
            
        }else{
            $id = input("id",0,"intval");
            $data = array();
            if($id){
                $data = $this->model->where("id",$id)->find();
            }
            $this->assign("data",$data);
            $this->assign("id",$id?$id:"");
            return $this->fetch();
        }
    }
    
    //判断擂台
    public function checkArena(){
        if($this->request->isPost()){
            $id = input("post.id/d");
            $arena_id = input("post.arena_id/d");
            if(!$arena_id){
                return $this->error("<span style='color:red'>请输入擂台ID</span>");
            }
            //判断是否已经存在推荐
            $check_recommend = $this->model->where(["arena_id"=>$arena_id,"id"=>["neq",$id]])->find();
            if(isset($check_recommend["id"])){
                return $this->error("<span style='color:red'>该擂台已经被推荐</span>");
            }
            //判断擂台是否存在
            $check_arena = model("arena")->where(["id"=>$arena_id,"status"=>["neq",ARENA_DEL]])->find();
            if(!isset($check_arena["id"])){
                return $this->error("<span style='color:red'>不存在该擂台</span>");
            }
            if($check_arena["status"] == ARENA_DIS){
                return $this->error("<span style='color:red'>该擂台被封禁</span>");
            }
            return $this->success("擂台数据正确");
        }
        return false;
    }
    
    //删除擂台
    public function deleteArena(){
        if($this->request->isPost()){
            $id = input("post.id/d");
            if($id){
                if($this->model->where("id",$id)->delete()){
                    return $this->success("删除成功");
                }else{
                    return $this->error("删除失败");
                }
            }
            return $this->error("参数异常");
        }
    }
    
}

?>