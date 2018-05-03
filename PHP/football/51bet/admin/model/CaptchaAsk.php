<?php
/**
 * Auto create Model
 * Date: 2017-12-04 17:44
 */
namespace app\admin\model;
use think\Model;
class CaptchaAsk extends Model {
    protected $name = "captcha_ask";
    public function upCache(){
        $limit = 100;
        $page = 0;
        $data = [];
        while(true){
            $offset = $page * $limit;
            $res = $this->limit($offset,$limit)->field("*")->select();
            if($res){
                foreach($res as $val){
                    $val['content'] = @json_decode($val['content'],true);
                    $data[$val['no']] = @json_encode($val);
                }
            }else{
                break;
            }
            $page++;
        }
        cache('captcha_ask_key',array_keys($data));
        \think\Cache::hmSet('captcha_ask_list',$data);
        return true;
    }

}
Ditch::event('after_write',function($model){
    //$model->upCache();
});
Ditch::event('after_delete',function($model){
    //$model->upCache();
});