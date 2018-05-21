<?php
/**
 * Auto create Model
 * Date: 2018-04-26 18:38
 */
namespace library\model\main;
use think\Model;
class ArenaDepositDetail extends Model {
    protected $name = "arena_deposit_detail";
    protected $connection = [];
    //模型初始化前调用
    public function _setModelConfig(){
        $this->connection = config("database");
    }
}