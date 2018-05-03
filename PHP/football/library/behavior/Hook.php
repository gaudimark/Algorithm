<?php

/**
 * 插件
 */
namespace library\behavior;
use think\Config;
use think\Hook as thinkHook;
use think\Cache;
use think\Loader;

class Hook{

    /**
     * 插件列表
     * @var array
     */
    static protected $hookList = array();

    public static function getHookList(){
        return self::$hookList;
    }

    static public function run($params){
        $list = config("hook");
        if($list){
            foreach($list as $name => $var){
                self::add($var[2],$name,$var);
            }
        }
    }

    /**
     * 注册钩子
     * @param $type
     * @param $name
     * @param $param
     */
    static public function add($type , $name , $param){
        self::$hookList[$type][$name] = $param;
        // 兼容系统钩子
        if(Config::get('think_hook_call')) {
            $key = strtolower($type .'_'.$name);
            $class = $param[0];
            thinkHook::add($key,$class);
        }
        return;

    }

    /**
     * 执行钩子
     * @param $type
     * @param string $name
     * @param array $array
     * @return mixed|void
     */
    static public function call($type , $name = '', $array = array()){
        if(Config::get('think_hook_call')) {
            if($name){
                $key = strtolower($type .'_'.$name);
                return thinkHook::listen($key , $array);
            }
            if(isset(self::$hookList[$type])){
                foreach (self::$hookList[$type] as $name => $r) {
                    $key = strtolower($type .'_'.$name);
                    thinkHook::listen($key , $array);
                }
            }

        }else{
            if(isset(self::$hookList[$type])){
                if($name){
                    if(!isset(self::$hookList[$type][$name])){return $array;}
                    $func = isset(self::$hookList[$type][$name][1]) && self::$hookList[$type][$name][1] ? self::$hookList[$type][$name][1] : 'run';
                    return self::invoke(self::$hookList[$type][$name][0],$func,$array);
                }else {
                    foreach (self::$hookList[$type] as $r) {
                        $func = isset($r[1]) && $r[1] ? $r[1] : 'run';
                        self::invoke($r[0],$func,$array);
                    }
                }
            }
        }
        return $array;
    }

    static private function invoke($class,$func,$array){
        if(class_exists($class)){
            $class = self::invokeClass($class);
            $reflect = new \ReflectionMethod($class, $func);
            return $reflect->invokeArgs(isset($class) ? $class : null, array($array));
        }
        return $array;
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @access public
     * @param string    $class 类名
     * @param array     $vars  变量
     * @return mixed
     */
    public static function invokeClass($class)
    {
        $reflect     = new \ReflectionClass($class);
        return $reflect->newInstanceArgs();
    }



}