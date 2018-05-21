<?php
namespace app\admin\widget;
use think\Controller;
class Editor extends Controller{

    public function single($name,$value='',$height = 150,$toolbars = []){
        $this->view->engine->layout(false);
        $this->assign('name',$name);
        $this->assign('value',$value);
        $this->assign('height',$height);
        $this->assign('toolbars',array_merge(['fullscreen', 'source', 'undo', 'redo', 'bold','link', 'unlink'],$toolbars));
        $unID = \org\Stringnew::keyGen();
        $this->assign('unID','ED_'.$unID);
        return $this->fetch('widget/editor_single');
    }
}