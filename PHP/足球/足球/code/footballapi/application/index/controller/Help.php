<?php
namespace app\index\controller;
use app\library\logic\Basic;
use org\Stringnew;
use think\Cache;
use think\captcha\Captcha;
use think\Db;
use think\Loader;

class Help extends Basic{

    
    public function index(){
        //获取当前系统赔率
        $config = Db::name('config')->where(['var'=>'odds'])->value('value');
        
        //获取帮助文档
        $type_id = input('type_id',1);
        $this->assign('type',$type_id);
        $info = Db::name('help')->where([
            'type' => 1,
            'type_id'=>$type_id
        
        ])->find();
        $info['content'] = $info['content'];
        $this->assign('info',$info);
        return view();
    }

   
}