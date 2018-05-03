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
    'extra_file_list'        => [ROOT_PATH."library".DS."common.php",THINK_PATH . 'helper' . EXT],
    'deny_module_list'       => [COMMON_MODULE, 'runtime','library'],
    'pathinfo_depr' => '/',
    'root_namespace' => [
        'org' => EXTEND_PATH."org".DS,
        'library' => ROOT_PATH."library".DS,
    ],
    'app_debug' => true,
    'lang_switch_on' => true,
    'url_domain_deploy' => true,//启用域名部署路由功能
    'base_url' => '',
    'url_route_on' => true,
    'show_error_msg'        =>  true,
    'log'          => [
        //'type' => 'trace', // 支持 socket trace file
    ],
    'http_exception_template'   => [
        404 => APP_PATH.'library/view/mobile_404.html',
        401 => APP_PATH.'library/view/mobile_404.html',
        500 => APP_PATH.'library/view/mobile_404.html',
    ],
    //api配置
    'api_51bet_com' => [
        'app_id' => '',
        'secret' => '',
    ],
    
];
