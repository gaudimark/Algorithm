<?php

namespace library\service;
use think\Db;
use think\Exception;

class Log{

    public static function sysFunds($admin_id,$type,$number,$explain,$data = []){
        self::sysLog($admin_id,SYSTEM_LOG_FUNDS,$explain,$data,$number,$type);
    }

    public static function sysOpt($admin_id,$explain,$data = []){
        self::sysLog($admin_id,SYSTEM_LOG_OPERATION,$explain,$data);
    }
    public static function sysView($admin_id,$method,$explain,$data = []){
        self::sysLog($admin_id,SYSTEM_LOG_METHOD,$explain,$data,0,0,$method);
    }

    /**
     * 系统日志
     * @param $admin_id
     * @param $classify
     * @param $explain
     * @param array $data
     * @param int $number
     * @param int $number_type
     * @param string $method
     * @return int|string
     */
    public static function sysLog($admin_id,$classify,$explain,$data = [],$number = 0,$number_type = 0,$method = 'GET'){
        return Db::name("system_log")->insert([
            'admin_id' => $admin_id,
            'classify' => $classify,
            'explain' => $explain,
            'number' => $number,
            'number_type' => $number_type,
            'method' => $method,
            'data' => json_encode($data),
            'controller' => isset($data['controller']) ? strtolower($data['controller']) : '',
            'action' => isset($data['action']) ? strtolower($data['action']) : '',
            'create_time' => time()
        ]);
    }
    
    //用户资金日志
    public static function UserFunds($user_id,$classify,$type,$number,$before_num,$after_num,$explain,$data = []){
        Db::name("user_funds_log")->insert([
            'user_id' => $user_id,
            'classify' => $classify,
            'type' => $type,
            'number' => $number,
            'before_num'=>$before_num,
            'after_num'=>$after_num,
            'explain' => $explain,
            'data' => json_encode($data),
            'create_time' => time()
        ]);
    }

    //用户日志
    public static function UserLog($user_id,$explain,$data = [],$classify = USER_LOG_OPT){
        $ditchId = isset($data['ditch_number']) ? $data['ditch_number'] : 0;
        return Db::name("user_log")->insert([
            'user_id' => $user_id,
            'ditch_number' => $ditchId,
            'classify' => $classify,
            'explain' => $explain,
            'data' => json_encode($data),
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }

    /**
     * 系统收支
     * @param $number 收支数据，大于0为收，小于0为支
     * @param $explain 说明
     * @param array $data 核对数据
     * @param int $type 类型，1金币，2金钱
     */
    public static function sysIncome($number,$explain,$data = [],$type = FUNDS_TYPE_GOLD,$category=1){
        return Db::name("system_income")->insert([
            'type' => $type,
            'number' => $number,
            'explain' => $explain,
            'data' => json_encode($data),
            'create_time' => time(),
            'category'=>$category
        ]);
    }

    /**
     * 擂台日志
     * @param $arenaId
     * @param $explain
     * @param array $data
     * @param int $type
     * @return int|string
     */
    public static function arenaLog($arenaId,$explain,$data = [],$type = 1,$number = 0){
        return Db::name("arena_log")->insert([
            'type' => $type,
            'arena_id' => $arenaId,
            'explain' => $explain,
            'number' => $number,
            'data' => json_encode($data),
            'create_time' => time(),
        ]);
    }

    /**
     * 用户骂人标注日志
     */
    public static function userCurse($userId,$adminId,$type){
        return Db::name("curse_log")->insert([
            'type' => $type,
            'user_id' => $userId,
            'admin_id' => $adminId,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }
}