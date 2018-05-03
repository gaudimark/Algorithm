<?php
namespace app\admin\controller\items;
use app\admin\logic\Items;

class Football extends Items{
    public function __construct(){
        parent::__construct(GAME_TYPE_FOOTBALL);
        $this->itemId = GAME_TYPE_FOOTBALL;
    }
}