<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +-----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


/*$data = file_get_contents('./2.json');
$data = json_decode($data,true);
$newData = array();
foreach($data as $key =>$value){
    $newData[] = $value['name'];
}
echo '<Pre>';
print_r(implode('',$newData));
exit;*/
#error_reporting(E_ERROR);
// [ 应用入口文件 ]
 ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
   set_time_limit(0);  
define('COMMON_MODULE', 'common');

// 定义应用目录
define('SITE_PATH',__DIR__."/");
define('APP_PATH', SITE_PATH . '../51bet/');
define('CONF_PATH',APP_PATH.'library/conf/');
define("BIND_MODULE","index");
//加载常量配置文件
include(SITE_PATH . '../library/conf/const.php');
// 开启调试模式
define('APP_DEBUG', true);
define('APP_HOOK',true);//开启行为扩展
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
#require  __DIR__ .'/../thinkphp/base.php';

#\think\Route::bind("index");
#\think\App::run()->send();

