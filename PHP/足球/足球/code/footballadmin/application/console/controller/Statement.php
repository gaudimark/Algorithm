<?php
/**
 *
 * 系统自动擂台结算
 *
 * 每10分钟执行一次
 * /
 */
namespace app\console\controller;
use app\console\logic\Basic;
use think\Db;
use think\Log;

define("AUTO_STATEMENT",1);
class Statement extends Basic{

    /**
     * 检查是否开启了自动结算
     */
    private function checkAuto($Item){
        $power = config('system.sys_arena_auto_statement');
        if(!$power){
            echo '自动结算未开启';
            return false;
        }
        $config = json_decode(config("system.arena_auto_statement"),true);
        if(!$config){
            echo "未配置自动结算设置";
            return false;
        }
        if(!$config){
            echo "未配置自动结算设置";
            return false;
        }
        if(!isset($config[$Item]) && !$config[$Item]){
            echo "未配置自动结算设置({$Item})";
            return false;
        }
        return $config[$Item];
    }


    public function football(){
        if(false == $ret = $this->checkAuto(GAME_TYPE_FOOTBALL)){
            return '';
        }
        set_time_limit(0);
        $this->console('Statement Begin');
        $svr = new \library\service\Statement();
        $whereOr = [];
        $where = ['status' => PLAT_STATUS_END,'has_statement' => 0];//,'statement_status' => 0
        $time = $ret * 60;
        //$where['end_time'] = ['elt',time() - $time];
        $where['game_type'] = GAME_TYPE_FOOTBALL;
        $where['arena_total'] = ['gt',0];
        //$where['id'] = 37368;

        while(true){
            $play = Db::name("play")
                ->where($where)
                ->where('end_time', ['elt', time() - $time], ['=', 'null'], 'or')
                ->order("id asc")->limit(10)
                ->select();
            if(!$play){
                $this->console("Statement End");
                break;
            }

            foreach($play as $p){
                $this->console("比赛【{$p['id']}】 自动结算开始");
                if(true === $svr->play($p['id'])){
                    $this->console("比赛【{$p['id']}】 自动结算完成");
                    //Log::write("比赛【{$p['id']}】 自动结算完成");
                }else{
                    $this->console("比赛【{$p['id']}】 自动结算失败，原因：".$svr->getError());
                    //Log::write("比赛【{$p['id']}】 自动结算失败，原因：".$svr->getError());
                }
            }
        }
    }
    
    //电竞
    public function wcg(){

    }
    
}
