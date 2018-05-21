<?php
/**
 * Auto create Model
 * Date: 2017-08-12 10:09
 */
namespace app\admin\model;
use think\Model;
class LayoutSports extends Model {
    protected $name = "layout_sports";
    public function _afterWrite($model){
        return true;
        //(new \library\service\Layout())->setCacheAll();
    }
    public function upCache(){
        (new \library\service\Layout())->setCacheAll();
        return true;
    }
}

LayoutSports::event('after_write',function($model){
    return $model->_afterWrite($model);
});
LayoutSports::event('after_delete',function($model){
    return $model->_afterWrite($model);
});