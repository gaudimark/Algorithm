<?php
/**
 * Auto create Model
 * Date: 2018-01-27 14:44
 */
namespace library\model\main;
use think\Model;
class StatOnlineUserDetail extends Model {
    protected $name = "stat_online_user_detail";
    protected $connection = [];
    //模型初始化前调用
    public function _setModelConfig(){
        $this->connection = config("database");
    }
}