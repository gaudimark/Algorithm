<?php
namespace app\admin\model;
use think\Model;
use think\Db;

class Level extends \app\library\model\User{

    
    public function upCache(){
        (new \library\service\User())->cacheLevel();
        return true;
    }
    
}