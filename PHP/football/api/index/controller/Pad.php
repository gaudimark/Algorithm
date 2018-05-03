<?php
/**
 * 提供给PAD服务器调用接口
 */
namespace app\index\controller;
use app\library\logic\Basic;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;

class Pad extends Basic{

    /**
     * 用户充值
     */
    public function recharge(){
        if($this->request->isPost()){
            $data = input('post.');
            $result = (new \library\service\Pad([]))->userUpdate($data);
            return $this->resultData($result);
        }
        return $this->resultData('pad.recharge',10000);
    }

    /**
     * 强制游戏中拿回金币
     */
    public function force(){
        if($this->request->isPost()){
            $data = input('post.');
            $result = (new \library\service\Pad([]))->userForceGold($data);
            return $this->resultData($result);
        }
        return $this->retErr('pad.force',10000);
    }

    /**
     * 游戏查币
     */
    public function query(){
        if($this->request->isPost()){
            $data = input('post.');
            $result = (new \library\service\Pad([]))->userQuery($data);
            return $this->resultData($result);
        }
        return $this->retErr('pad.query',10000);
    }

    /**
     * 游戏金币回到平板服务器，回调
     */
    public function callbackpoints(){
        if($this->request->isPost()){
            $data = input('post.');
            $result = (new \library\service\Pad([]))->padSvrCallbackPoints($data);
            return $this->resultData($result);
        }
        return $this->retErr('pad.callbackPoints',10000);
    }



    private function resultData($data,$header = []){
        $response = Response::create($data, 'json')->header($header);
        throw new HttpResponseException($response);
    }

}


