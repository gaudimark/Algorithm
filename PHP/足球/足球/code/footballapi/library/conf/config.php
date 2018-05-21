<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
return [
    'app_debug' => true,
    'datetime_format' => false,
    'default_filter' => 'htmlspecialchars,safeFilter', //过滤
    'var_ajax'  => 'ajax',
    'var_pjax'  => 'pjax',
    'site_domain' => 'http://ftgly.syhaidijiaju.com:33002/', //主域名
    'site_source_domain' => 'http://ftgly.syhaidijiaju.com:33002/assets/',
    'assets_path' => ROOT_PATH."public/assets",//静态资源目录

    'default_jsonp_handler' => 'jsonp',
    'session' => [
        'prefix'    => 'qUm_tablet',
        //'domain' => '51bet.com',
        'auto_start'    => true,

    ],
    'cookie' => [
        'prefix'    => 'tablet_',
        //'domain' => '51bet.com',
    ],
    'pathinfo_depr' => '/',
    'root_namespace' => [
        'org' => EXTEND_PATH."org".DS,
        'library' => ROOT_PATH."library".DS,
        //'service' => ROOT_PATH."library".DS."service".DS
    ],
    'lang_switch_on' => true,
    //'lang_list' => [APP_PATH.'library/lang/glod.php'],
    'url_domain_deploy' => true,//启用域名部署路由功能
    'base_url' => '',
    'url_route_on' => true,
    'show_error_msg'        =>  true,
    //'extra_config_list' => ['database', 'validate','rules'],
    'deny_module_list'       => [COMMON_MODULE, 'runtime','admin','library','console'],
    //'url_module_map' => [],
    'log'          => [
        'type' => 'file', // 关闭日志
       // 'level' => ['sql']
    ],
    'captcha' => [
        'codeSet' => '0123456789',
        'length' => 4,
        'fontttf' => '4.ttf',
        'fontSize' => 14,
        //'imageW' => 80,
        //'imageH' => 38
    ],
	'cache' => [
        'type'      => 'redis',
        'host'       => '192.168.1.82',
        'port'       => 16979,
        'password'   => 'Hj4alaGZ1cRLG6M',
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '_tablet_',
    ],
    'send_email_code_expire' => 1800, //发送邮箱验证码有效时间（秒）
    'think_hook_call' => false,
    //socket推送配置
    'socket' => [
        'register_address' => '192.168.188.172:1236', //注册服务器地址及端口
        'web_socket_address' => '192.168.188.172:8282', //web socket监听端口
        'log_file' => '/var/log/tablet/socket.log',//socket日志文件
    ],
    'sms_handle' => 'yunpian', //短信运营商
];
