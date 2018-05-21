<?php
namespace app\admin\widget;
use think\Controller;
class Upload extends Controller{

    public function single($name,$value='',$text = '',$tips='',$show = false,$exts="",$required = false){
        $this->view->engine->layout(false);
        $system = \think\Cache::get("system");
        if(!$exts){
            $exts = "*.*";
            if($system && isset($system['upload_backstage_exts']) && $system['upload_backstage_exts']){
                $exts = explode("|",$system['upload_backstage_exts']);
                $temp = [];
                foreach($exts as $e){
                    $temp[] = "*.{$e}";
                }
                $exts = implode(";",$temp);
            }
        }
        $url = $value ? get_image_thumb_url($value) : '';
        $text = $text ? $text : '上传文件';
        $this->assign('exts',$exts);
        $this->assign('name',$name);
        $this->assign('domain','');
        $this->assign('url',$url);
        $this->assign('value',$value);
        $this->assign('text',$text);
        $this->assign('tips',$tips);
        $this->assign('show',$show);
        $this->assign('required',$required);
        $unID = \org\Stringnew::keyGen();
        $this->assign('unID','upload_'.$unID);
        return $this->fetch('widget/upload_single');
    }
}