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

Route::rule('/', 'index/index');
return [
    //获取token
    'oauth/access_token$' => 'index/oauth/access_token',
    'oauth/sign' => 'index/oauth/sign',
    'get/ponints' => 'index/MyServer/ponints',
    'notice/ponints' => 'index/MyServer/notice',
    'listen/heartbeat' => 'index/MyServer/heartbeat',
    'notice/player/recharge' => 'index/MyServer/recharge',
    'notice/logout/game'    => 'index/MyServer/notice_logout_game',
    'gameplayer_ponints'    => 'index/MyServer/gameplayer_ponints',
    'listen/slide/msg'=>'index/MyServer/slide',
    'help/index'=> 'index/Help/index',
    'again/settlement'=>'index/MyServer/settlementAgainSend',
    'again/bets'=>'index/MyServer/bets'
];