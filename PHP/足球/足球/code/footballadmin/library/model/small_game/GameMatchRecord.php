<?php
/**
 * Auto create Model
 * Date: 2018-01-27 14:23
 */
namespace library\model\small_game;
use think\Model;
class GameMatchRecord extends Model {
    protected $name = "game_match_record";
    protected $connection = [];
    //模型初始化前调用
    public function _setModelConfig(){
        $this->connection = config("db.small_game");
    }
}