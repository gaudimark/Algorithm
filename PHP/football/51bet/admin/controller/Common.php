<?php

/**
 * 项目汇集，不区分项目
 */

namespace app\admin\controller;
use app\admin\logic\Basic;

class Common extends Basic{
    /**
     * 刷新缓存
     */
    public function refresh(){
        set_time_limit(0);
        $cacheList = [
            'config' => '系统配置',
            'rules' => '房间玩法',
            'oddsCompany' => '赔率公司',
            'Layout'  => '模块',
            'team' => '球队/队伍',
            'match' => '赛事',
            //'user' => '用户',
        ];

        if($this->request->isPost()){
            $data = input("data");
            try{
                if($data == 'runtime_template'){
                    $this->delTemp();
                    return $this->success("更新成功");
                }elseif($data == 'runtime_log'){
                    $this->delLog();
                    return $this->success("更新成功");
                }else {
                    //$class = \think\Loader::parseClass('admin', 'model', $data);
                    $b = null;
                    if(stripos($data,".") !== false){
                        list($b,$a) = explode(".", $data);
                    }else{
                        $a = $data;
                    }

                    if(modelN($a,$b)->upCache()){
                        return $this->success("数据缓存更新成功");
                    }else{
                        return $this->error("数据缓存更新失败");
                    }
                }
            }catch (Exception $e){
                return $this->error($e->getMessage());
            }
        }
        $this->assign('cacheList',$cacheList);
        return $this->fetch();
    }
}