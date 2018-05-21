<?php
date_default_timezone_set("Asia/Shanghai");
//关闭脚本程序正常执行
ignore_user_abort();
set_time_limit(0);
ini_set('display_errors',1);            //错误信息  
ini_set('display_startup_errors',1);    //php启动错误信息  
error_reporting(-1);                    //打印出所有的 错误信息  
ini_set('error_log','../runtime/crontab/error_log.txt'); //将出错信息输出到一个文本文件 
$type = $_GET['type'];
$config = include('./config.php');
switch ($type) {
    case 1:
        //游戏向平板服务器发送心跳接口5分钟发送一次
        //休眠时间(秒)
        if(true != heartbeat){
            endTask('heartbeat');
        }
        $sleep = 300;
        $url = 'http://foot.tmttg.com/listen/heartbeat';
        run($url, $sleep, 'heartbeat', '心跳');
        break;
    case 2:
        //指定时间获取开奖队列，推送跑马灯
        //休眠时间(秒)
        if(true != slide_msg){
            endTask('slide');
        }
        $sleep = 180;
        $url = 'http://foot.tmttg.com/listen/slide/msg';
        run($url,$sleep, 'slide', '监测跑马灯');
        break;
    case 3:
        if(true != settlement){
            endTask('settlement');
        }
        $sleep = 60;
        $url = 'http://foot.tmttg.com/again/settlement';
        run($url,$sleep, 'settlement', '检测是否有未成功结算');
        break;
    case 4:
        if(true != bets){
            endTask('bets');
        }
        $sleep = 60;
        $url = 'http://foot.tmttg.com/again/bets';
        run($url,$sleep, 'bets', '下注');
        break;
}
//运行定时任务
function run($url,$sleep, $fileName, $returnData)
{
    $i = 1;
    do{
        $data = file_get_contents($url);
        writeLogFile($fileName,'第:'.$i.'次'.$returnData.' 执行时间: '.date('Y-m-d H:i:s'));
        $i++;
        sleep($sleep);
    }while(true);
}
//写入日志
function writeLogFile($fileName, $content)
{
    //文件保存目录
    $dirPath = dirname(__FILE__) . '/../runtime/crontab/';
    file_put_contents($dirPath.$fileName.'_'.date('Ymd').'.log', $content.PHP_EOL, FILE_APPEND);
}
function endTask($fileName)
{
    ignore_user_abort(false);
    writeLogFile($fileName,'程序退出, 时间：'.date('Y-m-d H:i:s'));
    exit;
}