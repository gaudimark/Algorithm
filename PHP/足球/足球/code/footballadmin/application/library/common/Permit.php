<?php
/**
 * 权限标签
 * Date: 2017/5/9
 * Time: 15:20
 */
namespace app\library\common;
use think\template\TagLib;

class Permit extends TagLib{

    protected $tags = [
        'a' => ['attr' => 'controller,action,params,href,title,class,id,onclick','close' => 0],
        'button' => ['attr' => 'href,class,id,onclick','close' => 0],
    ];

    public function tagA($tag){

    }
}