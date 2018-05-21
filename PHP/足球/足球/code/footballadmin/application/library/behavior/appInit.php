<?php
/**
 * 加载配置文件
 * Class Config
 * @package app\library\behavior
 */
namespace app\library\behavior;
use think\Cache;
use think\Lang;
use think\Config;
use think\Db;
class AppInit{
    public function run(&$params){
        //加载语言包
        //Lang::load(APP_PATH.'library/lang/'.LANG.'.php');
        //加载系统配置
        $sys = Cache::get('system');
        if(!$sys){
            model("admin/config")->upCache();
            $sys = Cache::get('system');
        }



        Config::set("system",$sys);
        session([]); //初始化session,防止跨域丢失session
        cookie([]); //初始化session,防止跨域丢失session
    }

}