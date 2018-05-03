<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
class Article extends Controller{
    public function __construct(){
        @header('Access-Control-Allow-Origin:*');
        parent::__construct();
    }

    public function index(){
        $page = input("p/d");
        $type = input("t",1,"intval");
        $limit = 10;
        $order = ($page-1)*10;
        if($order < 0)
            $order = 0;
        $list = Db::name("article")->limit($order,$limit)->where(['status' => 1,"type"=>$type])->order("update_time desc")->select();
        $str = "";
        if($list){
            foreach ($list as $k=>$v){
                $content = strip_tags($v["content"]);
                $list[$k]["detail"] = mb_substr($content, 0,22);
            }
            //print_r($list);
            $str = $this->fetch("article/index",["list"=>$list,"page"=>$page,"type"=>$type]);
        }
        return json_encode(array("str"=>$str,"page"=>$page),true);
    }
    
    public function view(){
        $id = input("id/d");
        $str = "";
        if($id){
            $data = Db::name("article")->where(['status' => 1 , "id"=>$id])->find();
            if($data){
                $str = $this->fetch("article/view",["data"=>$data]);
            }
        }
        return json_encode(array("str"=>$str));
    }
    
    public function wz(){
        return $this->fetch();
    }
    
    public function football(){
        return $this->fetch();
    }
    
}

?>