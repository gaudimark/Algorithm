<?php
/**
 * 基础数据统计
 * Date: 2017/6/7
 * Time: 15:12
 */
namespace app\admin\controller\stat;
use library\service\Data;
use library\service\Misc;
use think\Db;

class Basic extends \app\admin\logic\Basic{



    public function arena(){
        if(input('export')){
            return $this->_export_arena();
        }
        $btime = input('btime');
        $etime = input('etime');
        $where = [];
        $where['item_id'] = 0;
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif ($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        $list = modelN('stat_user_arena')->where($where)->order('s_date DESC')->paginate(20,false,[
            'query' => input()
        ]);
        if($list){
            foreach($list as $key => $val){
                $val = $val->toArray();
                $val['items'] = modelN('stat_user_arena')->where(['s_date' => $val['s_date'],'item_id' => ['gt',0]])->order('item_id asc')->select();
                $list[$key] = $val;
            }
        }

        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        $this->assign('list',$list);
        return $this->fetch();
    }

    private function _export_arena(){

        $btime = input('btime');
        $etime = input('etime');
        $where = [];
        if($btime && $etime){
            $where['s_date'] = [['egt',strtotime($btime)],['elt',strtotime($etime)]];
        }elseif ($btime){
            $where['s_date'] = ['egt',strtotime($btime)];
        }elseif ($etime){
            $where['s_date'] = ['elt',strtotime($etime)];
        }
        $where['item_id'] = 0;
        $list = modelN('stat_user_arena')->where($where)->order('s_date DESC')->select();
        if($list){
            foreach($list as $key => $item){
                $item = $item->toArray();
                $item['s_date'] = date("Y-m-d",$item['s_date']);
                $item['bet_win_lost'] = $item['bet_win'] - $item['bet_lost'];
                $list[$key] = $item;
            }
        }
        $title = array(
            's_date' => '日期',
            'arena_total' => '房间数',
            'arena_deposit' => '房间押金',
            'arena_deposit_add' => '房间押金追加',
            'arena_win' => '房间输赢',
            'arena_brok' => '房间佣金',
            'bet_total' => '投注注数',
            'bet_money' => '总投注金额',
            'bet_win_lost' => '投注输赢',
            'bet_bork' => '投注佣金',
        );
        (new \library\service\Misc())->toXls('房间统计',$title,$list);
    }
}