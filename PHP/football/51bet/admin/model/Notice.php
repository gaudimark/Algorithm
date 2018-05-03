<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;
class Notice extends BasicModel{

    protected $name = 'system_notice';


    public function upCache(){
        $time = time();
        $lists = $this->where([
            'btime' => ['elt',$time],
            'etime' => ['egt' , $time],
            'classify' => ['neq',NOTICE_CLASSIFY_MARQUEE]
        ])->select();
        cache("Notice",$lists);
        return true;
    }

}
Notice::event('after_write',function($model){
    $model->upCache();
});
Notice::event('after_delete',function($model){
    $model->upCache();
});
?>