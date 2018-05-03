<?php
/**
 *
 * 公共任务模块
 *
 */
namespace app\console\controller;
use app\console\logic\Basic;
use library\service\Socket;
use think\Cache;
use think\Db;
use think\Exception;

class Misc extends Basic{
    private $ditchlist = [];
    private $ditchclassify = [];
    private $system = [];
    public function __construct(){
        $this->ditchlist = cache('Ditch');
        $this->system = config('system');
        $this->ditchclassify = cache('ditch_classify');
    }



    /**************擂台统计 开始******************/
    public function arena(){
        $ymd = input('ymd');
        $items = [GAME_TYPE_FOOTBALL];
        $this->_arena($ymd,0);
        foreach($items as $val){
            $this->_arena($ymd,$val);
        }
    }
    public function _arena($ymd,$item_id = 0){
        $dateArr = $this->getTimespace($ymd, 1);
        $data = [];
        $data['item_id'] = $item_id;
        $data['arena_total'] = 0; //擂台数
        $data['arena_win'] = 0;//擂台输赢
        $data['arena_deposit'] = 0;//擂台总保证金
        $data['arena_brok'] = 0;//擂台佣金
        $data['bet_total'] = 0;//投注注数
        $data['bet_money'] = 0;//总投注金额
        $data['bet_win'] = 0;//投注总赢金额
        $data['bet_lost'] = 0;//投注总输金额
        $data['bet_bork'] = 0;//投注佣金
        $data['arena_deposit_add'] = 0;//擂台押金追加
        $arenaWhere = [];
        $depositWhere = [];
        if($item_id){
            $arenaWhere['game_type'] = $item_id;
            $depositWhere['a.game_type'] = $item_id;
        }
        //擂台统计
        $row = modelN('arena')->field('COUNT(*) as total,SUM(deposit) as deposit')->where([
            'create_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
            'status' => ['not in',[ARENA_DEL]]
        ])->where($arenaWhere)->find();
        $data['arena_total'] += (int)$row['total'];
        $data['arena_deposit'] += (double)$row['deposit'];


        $row = modelN('arena_deposit_detail')->alias('d')->field('SUM(d.number) as number')
            ->join('arena a','a.id=d.arena_id','left')
            ->where($depositWhere)
            ->where([
            'd.create_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
            'a.create_time' => ['lt',$dateArr['begin']],
            'a.status' => ['not in',[ARENA_DEL]]
        ])->find();
        $data['arena_deposit_add'] += (double)$row['number'];

        //擂台输赢
        $row = modelN('arena')->field('SUM(win) as win,SUM(win_brok) as win_brok')
            ->where($arenaWhere)
            ->where([
            'update_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
            'classify' => ARENA_CLASSIFY_GOLD,
            'status' => ARENA_STATEMENT_END
        ])->find();
        $data['arena_win'] += (double)$row['win'];
        $data['arena_brok'] += (double)$row['win_brok'];
        //擂台输赢，征信局只计算佣金
        $row = modelN('arena')->field('SUM(win_brok) as win_brok')
            ->where($arenaWhere)
            ->where([
            'update_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
            'classify' => ARENA_CLASSIFY_CREDIT,
            'status' => ARENA_STATEMENT_END
        ])->find();
        $data['arena_brok'] += (int)$row['win_brok'];


        //投注统计
        $row = modelN('arena_bet_detail')->alias('ab')
            ->join('arena a','a.id=arena_id','LEFT')
            ->field('COUNT(*) as total,SUM(ab.money) as money')
            ->where($depositWhere)
            ->where([
                'ab.create_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
        ])->find();
        $data['bet_total'] += $row['total'];
        $data['bet_money'] += $row['money'];
        //投注统计-赢
        $row = modelN('arena_bet_detail')->alias('ab')
            ->field('SUM(ab.win_money-ab.money) as win,SUM(ab.fee) as fee')
            ->join('arena a','a.id=arena_id','LEFT')
            ->where($depositWhere)
            ->where([
                'a.classify' => ARENA_CLASSIFY_GOLD,
                'ab.win_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
                'ab.status' => ['in',[DEPOSIT_WIN,DEPOSIT_WIN_HALF]]
            ])->find();
        $data['bet_win'] += $row['win'];
        $data['bet_bork'] += $row['fee'];
        //投注统计-赢，征信局只计算佣金
        $row = modelN('arena_bet_detail')->alias('ab')
            ->field('SUM(ab.fee) as fee')
            ->join('arena a','a.id=arena_id','LEFT')
            ->where($depositWhere)
            ->where([
                'a.classify' => ARENA_CLASSIFY_CREDIT,
                'ab.win_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
                'ab.status' => ['in',[DEPOSIT_WIN,DEPOSIT_WIN_HALF]]
            ])->find();
        $data['bet_bork'] += $row['fee'];

        //全输
        $row = modelN('arena_bet_detail')->alias('ab')
            ->join('arena a','a.id=arena_id','LEFT')
            ->where($depositWhere)
            ->field('COUNT(*) as total,SUM(ab.money) as money')->where([
                'a.classify' => ARENA_CLASSIFY_GOLD,
                'ab.win_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
                'ab.status' => ['in',[DEPOSIT_LOSE]]
            ])->find();

        $data['bet_lost'] += abs($row['money']);

        //输一半
        $row = modelN('arena_bet_detail')->alias('ab')
            ->join('arena a','a.id=arena_id','LEFT')
            ->field('COUNT(*) as total,SUM(ab.money) as money')
            ->where($depositWhere)
            ->where([
                'a.classify' => ARENA_CLASSIFY_GOLD,
                'ab.win_time' => [['egt',$dateArr['begin']],['elt',$dateArr['end']]],
                'ab.status' => ['in',[DEPOSIT_LOST_HALF]]
            ])->find();
        $data['bet_lost'] += abs($row['money'] / 2);
        $data['s_date'] = strtotime(date("Y-m-d",$dateArr['begin']));
        Db::name('stat_user_arena')->insert($data,true);
        $sportName = getSport($item_id);
        $this->console("{$sportName}擂台统计完成");
    }

    /**************擂台统计 结束******************/





}
