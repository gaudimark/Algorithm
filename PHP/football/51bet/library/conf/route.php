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
use think\Route;

return [
    '__domain__' => [
        'ftbtest'      => 'admin'//['admin','',['https' => true]],  //子域名
    ],

    'live/play/<play_id>' => 'index/live/play', //直播
    'share/arena/<arena_id>$' => 'index/share/arena', //擂台分享
    'share/play_dope$' => 'index/share/play_dope', //比赛预测
    //':tg_number$' => ['promoter/index/index',['tg_number' => "\d+"]], //比赛预测
];
