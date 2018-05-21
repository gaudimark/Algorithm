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
    'default_return_type' => 'json',
    'extra_file_list'        => [ROOT_PATH."library".DS."common.php",THINK_PATH . 'helper' . EXT],
    'deny_module_list'       => [COMMON_MODULE, 'runtime','library'],
    'pathinfo_depr' => '/',
    'root_namespace' => [
        'org' => EXTEND_PATH."org".DS,
        'library' => ROOT_PATH."library".DS,
    ],
    //'auto_bind_module' => true,
    'app_multi_module' => true,
    'app_debug' => true,
    'lang_switch_on' => true,
    'url_domain_deploy' => true,//启用域名部署路由功能
    'base_url' => '',
    'url_route_on' => true,
    'show_error_msg'        =>  true,


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

	
    //'exception_handle'  => '\\app\\library\\exception\\Http' //异常处理
];
