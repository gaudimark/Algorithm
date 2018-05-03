<?php

/**
 * 项目汇集，不区分项目
 */

namespace app\admin\controller\items;
use app\admin\logic\Basic;
use library\service\Arena;

class All extends Basic{

    /**
     * 风险提醒
     */
    public function risk(){
        $page = max(1,input("page/d",0));
        $limit = 8;
        $arenaSvr = new Arena();
        if($this->request->isPost()){
            $arenaId = input("arena_id/d");
            $arenaData = $arenaSvr->getCacheArenaById($arenaId);
            $arenaSvr->rmArenaRiskList($arenaId);
            if(!$arenaData){
                $this->error("无效房间数据");
            }
            $itemsConf = config("items");
            $item = isset($itemsConf[$arenaData['game_type']]) ? $itemsConf[$arenaData['game_type']] : [];
            if(!$item){
                $this->error("无效房间数据");
            }

            $url = url("items.{$item['mark']}/arena_list")."?mark={$arenaId}";
            $this->success("",$url);
        }
        $dataList = $arenaSvr->getArenaRiskList();
        $total = count($dataList);
        $offset = ($page - 1) * $limit;
        $dataList = array_slice($dataList,$offset,$limit);
        $class =  '\\think\\paginator\\driver\\Bootstrap';
        $config = [];
        $config['path'] = isset($config['path']) ? $config['path'] : call_user_func([$class, 'getCurrentPath']);
        $paginate = $class::make($dataList,$limit,$page,$total,false,$config);
        $this->assign('lists',$dataList);
        $this->assign('paginate',$paginate);
        return $this->fetch();
    }

    public function risk_total(){
        $arenaSvr = new Arena();
        $total = $arenaSvr->getArenaRiskListTotal();
        return $this->success('','',['total' => $total]);
    }
}