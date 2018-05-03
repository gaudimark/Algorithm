<?php
namespace library\service;

use think\Db;

class Layout{

    public function setCacheAll(){
        $list = Db::name('layout')->order("position asc,id asc")->where(['status' => STATUS_ENABLED])->select();
        $data = [];
        foreach($list as $key => $val){
            $detail = @json_decode($val['detail'],true);
            if($detail){
                foreach($detail as $k => $v){
                    $ids = [];
                    $v['img'] = $v['img'] ? get_image_thumb_url($v['img']) : '';
                    $res = isset($v['res']) ? $v['res'] : [];
                    if($res){
                        foreach ($res as $r => $rv) {
                            $res[$r] = $rv ? get_image_thumb_url($rv) : '';
                        }
                    }
                    $v['res'] = $res;
                    if($v['lib']){
                        foreach ($v['lib'] as $lib) {
                            $ids[] = $lib['id'];
                        }
                    }
                    $v['lib_ids'] = array_values($ids);
                    $detail[$k] = $v;
                }
            }
            $val['detail'] = $detail;
            $val['inv_img'] = $val['inv_img'] ? get_image_thumb_url($val['inv_img']) : '';
            $data[$val['id']] = $val;
        }
        cache("layout",$data);

        //竞技模块
        $list = Db::name('layout_sports')->order("sort asc,id desc")->where(['status' => STATUS_ENABLED])->select();
        foreach($list as $key => $val){
            $val['detail'] = @json_decode($val['detail'],true);
            $list[$key] = $val;
        }
        cache("sports_layout",$list);
        return $data;
    }

    public function getLayout($id = null){
        $list = cache("layout");
        if(!$list){
            $this->setCacheAll();
            $list = cache("layout");
        }
        if($id){
            $list = isset($list[$id]) ? $list[$id] : [];
        }
        return $list;
    }
    public function getSportLayout($id = null){
        $list = cache("sports_layout");
        if(!$list){
            $this->setCacheAll();
            $list = cache("sports_layout");
        }
        if($id){
            $list = isset($list[$id]) ? $list[$id] : [];
        }
        return $list;
    }
}