<?php

namespace app\admin\model;
use app\library\model\BasicModel;

class Layout extends BasicModel{
    protected $name = "layout";

    public function _afterWrite($model){
    //(new \library\service\Layout())->setCacheAll();
    }
    public function upCache(){
        (new \library\service\Layout())->setCacheAll();
        return true;
    }
}

Layout::event('after_write',function($model){
    //return $model->_afterWrite($model);
});
Layout::event('after_delete',function($model){
    //return $model->_afterWrite($model);
});