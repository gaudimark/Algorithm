<?php

/**
 *
 */
namespace app\console\controller;
use app\console\logic\Basic;
use think\Db;

class Queue extends Basic{

    public function pad(){
        $limit = 100;
        $page = 0;
        $maxCount = 5;
        $padSvr = new \library\service\Pad([]);
        while(true){
            $offset = $page * 100;
            $lists = Db::name('queue')->where([
                'status' => 0,
                'count' => ['elt',$maxCount]
            ])->limit($offset,$limit)->select();
            if(!$lists){
                break;
            }
            foreach ($lists as $val){
                $this->console("执行PAD队列:{$val['type']}");
                $data = @json_decode($val['data'],true);
                $result = $padSvr->runQueue($val['type'],$data);
                $status = 0;
                if($result){
                    $status = 1;
                }
                Db::name('queue')->where(['id' => $val['id']])->update([
                    'status' => $status,
                    'result' => @json_encode($result),
                    'count' => ['exp','count+1']
                ]);
                $this->console("执行PAD队列:{$val['type']},状态；{$status}");
            }
            $page++;
	    break;
        }
    }
}