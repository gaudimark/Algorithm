<?php
/**
 * 国家管理
 * */
namespace app\admin\controller;

class Country extends \app\admin\logic\Basic{
    
    public function __construct(){
        parent::__construct();
        $this->model = model("country");
    }
    
    public function index(){
        $param = input("param.");
        $name = input("name");
        $where = [];
        if($name){
            $where["name"] = ["like","%$name%"];
        }
        $list = $this->model->where($where)->order("id desc")->paginate(10,false,$param);//($where,10,"id desc",$param);
        foreach ($list as $k=>$v){
            $d = $this->getInfoByCountry($v["id"]);
            $v["relation"] = 0;
            if(count($d["match"]) > 0 || count($d["team"]) > 0 ){
                $v["relation"] = 1;
            }
            $list[$k] = $v;
        }
        $system = \think\Cache::get("system");
        $this->assign('domain',$system['upload_local_domain']);
        $this->assign("list",$list);
        $this->assign("name",$name);
        return $this->fetch();
    }
    
    public function addCountry(){
        if($this->request->isPost()){
            $id = input("id",0,"intval");
            $logo = input("post.logo");
            $parent_id = input("post.parent_id");
            $name = input("post.name");
            $ename = input("post.ename");
            $first = input("post.first");
            if(!$name){
                return $this->error("国家名称不能为空");
            }
        
            $country = array();
            $country["name"] = $name;
            $country["ename"] = $ename;
            $country["parent_id"] = $parent_id;
            $country["first"] = $first;
            if($logo)
                $country["logo"] = $logo;
        
            if($id){
                //判断是否存在
                $info = $this->model->where(['name' => $name])->find();
                if($info&&$info["id"]!=$id){
                    return $this->error($name."已经存在");
                }
                if($this->model->save($country,['id' => $id])){
                    return $this->success("修改成功");
                }else{
                    return $this->error("修改失败");
                }
            }else{
                //判断是否存在
                $info = $this->model->where(['name' => $name])->find();
                if($info){
                    return $this->error($name."已经存在");
                }
                if($this->model->save($country)){
                    return $this->success("新增成功");
                }else{
                    return $this->error("新增失败");
                }
            }
        }else{
            $id = input("id",0,"intval");
            $info = array();
            $logo = "";
            $parent_id = "";
            if($id){
                $info = $this->model->where(['id' => $id])->find();
                $logo = $info["logo"];
                $parent_id = $info["parent_id"];
            }
            $this->assign("parent_id",$parent_id);
            $this->assign("logo",$logo);
            $this->assign("info",$info);
            $this->assign("id",$id);
            return $this->fetch();
        }
    }
    
    //删除国家
    public function delCountry(){
        $id = input("id",0,"intval");
        if(!$id){
            return $this->error("参数异常");
        }
        $data = $this->getInfoByCountry($id);
        if(count($data["match"]) > 0 || count($data["team"]) > 0 ){
            return $this->error("有关联数据，不能删除");
        }
        if(\think\Db::name('country')->where("id",$id)->delete()){
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }
    
    //根据国家查询联赛及球队信息
    public function getInfoByCountry($id){
        $match = \think\Db::name('match')->where(["country_id"=>$id])->select();
        $team = \think\Db::name('team')->where(["country_id"=>$id])->select();
        return ["match"=>$match,"team"=>$team];
    }
    
    
    
}

?>