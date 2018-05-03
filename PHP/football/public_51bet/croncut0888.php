<?php
// 定时任务入口文件
error_reporting(E_ERROR);
// [ 应用入口文件 ]
define('COMMON_MODULE', 'common');

// 定义应用目录
define('SITE_PATH',__DIR__."/");
define('APP_PATH', SITE_PATH . '../51bet/');
define('CONF_PATH',APP_PATH.'library/conf/');
define("BIND_MODULE","console");
//加载常量配置文件
include(SITE_PATH . '../library/conf/const.php');
// 开启调试模式
define('APP_DEBUG', false);
define('APP_HOOK',true);//开启行为扩展
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
//require  __DIR__ .'/../thinkphp/base.php';
//\think\Route::bind("console");
//\think\App::run()->send();
