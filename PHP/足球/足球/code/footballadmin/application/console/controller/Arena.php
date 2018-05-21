<?php

/**
 *
 */
namespace app\console\controller;
use app\console\logic\Basic;
use library\service\Play;
use think\Db;

class Arena extends Basic{

    public function cacheAndroid(){
        $limit = 50;
        $page = 0;
        $data = [];
        while(true) {
            $offset = $page * $limit;
            $lists = Db::name('user')->where([
                'has_robot' => 1,
            ])->limit($offset,$limit)->column('id');
            if(!$lists){break;}
            $data = array_merge($data,$lists);
            $page++;
        }
        cache('android_list',$data);
        return $data;
    }

    /**
     * 投注机器人
     */
    public function bet_android(){
        $limit = 50;
        $page = 0;
        $time = time();
        $arenaSvr = new \library\service\Arena();
        $playSvr = new Play();
        $arenaSvr->admin_id = 1;
        $androidList = $this->getAndroid($limit);
        while(true){
            $offset = $page * $limit;
            $lists = Db::name('arena')->alias('a')
                ->field('aa.*,a.game_type as item_id,a.game_id,a.min_bet,a.play_id')
                ->join('arena_android aa','a.id=aa.arena_id','left')
                ->where([
                    'a.status' => ARENA_START,
                    'aa.id' => ['gt',0],
                    'aa.next_time' => [['egt',$time],['lt',$time + 60]]
                ])->limit($offset,$limit)->select();
            if(!$lists){break;}
            foreach($lists as $list){
                $condition = @json_decode($list['condition'],true);
                $play = $playSvr->getPlay($list['play_id']);
                if(isset($condition['next_bet']) &&  $condition['next_bet'] > 0){
                    $arena = $arenaSvr->getCacheArenaById($list['arena_id']);
                    $target = $this->getBetTarget($arena,$condition['next_bet']);


                    $userId = array_pop($androidList);
                    $bet = $condition['next_bet'];
                    if (false === $result = $arenaSvr->betting($list['arena_id'], $bet, $userId, $target['target'], $target['item'])){
                        $this->console("[失败]机器人({$userId})下注，房间ID：{$list['arena_id']}," . lang($arenaSvr->getError(), $arenaSvr->getErrorData()));
                    }
                    $this->console("[成功]机器人({$userId})下注，房间ID：{$list['arena_id']},下注金额：{$bet},下注对象：{$target['target']}({$target['item']})");
                }
                //获取下次运行数据
                $condition = $arenaSvr->betAndroid($list['item_id'], $list['min_bet'], $play['play_time'], $condition['max_bet'], $list['game_id']);
                if (!$condition){
                    continue;
                }
                $next_time = $condition['next_time'];
                Db::name('arena_android')->insert(['arena_id' => $list['arena_id'], 'next_time' => $next_time, 'create_time' => time(), 'update_time' => time(), 'condition' => @json_encode($condition)], true);

            }
            $page++;
        }

    }

    /**
     * 获取投注项
     * @param $target_list
     */
    private function getBetTarget($arena,$next_bet){
        $target_list = $arena['target_list'];
        $targetTemp = [];
        foreach($target_list as $val){
            if (in_array($val['target'], ['under', 'over', 'handicap']) || $val['deposit'] < $next_bet){
                continue;
            }
            $targetTemp[$val['target'] . ';' . $val['item']] = $val;
        }

        $odds = $arena['odds'];
        //多維数据转成一维数组
        $temp = [];
        $total = 0;
        foreach ($odds as $key => $val) {
            if(is_array($val)){
                foreach($val as $k => $v){
                    if (!in_array((string)$key, ['under', 'over', 'handicap']) && isset($targetTemp[$key . ';' . $k])){
                        $temp[$key . ';' . $k] = $v;
                        $total += $v;
                    }
                }
            }else{
                if (!in_array((string)$key, ['under', 'over', 'handicap']) && isset($targetTemp[$key . ';' ])){
                    $total += $val;
                    $temp[$key] = $val;
                }
            }
        }
        $odds = $temp;
        //计算权重
        $wight = 0;
        foreach ($odds as $val) {
            $wight += $total / $val ;
        }
        $wightList = [];
        $num = 0;
        //计算每个投注项的随机值范围
        foreach ($odds as $key => $val) {
            $max = $num + ceil((($total / $val) / $wight) * 100);
            if($max > 100){
                $max = 100;
            }
            $wightList[$key] = [
                'min' => $num + 1,
                'max' => $max,
            ];
            $num = $max;
        }
        $randNum = rand(1,100);
        $target = '';
        foreach($wightList as $key => $val){
            if( $val['min'] <= $randNum && $val['max'] >= $randNum){
                $target = explode(';',$key);
                break;
            }
        }
        $result = [];
        foreach ($target_list as $key => $val) {
            if(count($target) > 1 && $target[0] == $val['target'] && $target[1] == $val['item']){
                $result = $val;
                break;
            }elseif(count($target) == 1 && $target[0] == $val['target']){
                $result = $val;
                break;
            }
        }
        return $result;
    }

    private function getAndroid($limit = 50){
        $androidList = cache('android_list');
        if(!$androidList){
            $androidList = $this->cacheAndroid();
        }
        shuffle($androidList);
        $arrayRandKey = array_rand($androidList,$limit);
        $result = [];
        foreach($arrayRandKey as $key){
            $result[] = $androidList[$key];
        }
        return $result;

    }
}