<?php
/**
 * Auto create Model
 * Date: 2018-01-27 14:36
 */
namespace library\model\main;
use think\Model;
class StatUserArena extends Model {
    protected $name = "stat_user_arena";
    protected $connection = [];
    //模型初始化前调用
    public function _setModelConfig(){
        $this->connection = config("database");
    }
}