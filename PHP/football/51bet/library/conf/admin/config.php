<?php

return [
    //'controller_auto_search' => true,
    'template' => [
        'layout_on' => true,
        'layout_name'=>'layout',
        'taglib_pre_load' => 'app\library\common\Permit'
    ],
    'view_replace_str' => [
        '__formId__' => "form_".uniqid(),
    ],

    'dispatch_error_tmpl' => APP_PATH . 'admin' . DS . 'view'.DS.'public'.DS.'error.html',

    'category_level_max' => 3, //栏目分类最大级别

    'app_debug' => true,
    'show_error_msg'        =>  true,
];