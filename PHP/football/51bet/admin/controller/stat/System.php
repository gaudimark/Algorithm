<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/27
 * Time: 14:27
 */
namespace app\admin\controller\stat;
use app\admin\logic\Basic;
use library\service\Misc;
use think\Db;

class System extends Basic{

    public function income(){
        $type = input("type",0,'intval');
        $toxls = input("toxls",0,'intval');
        $way = input("way",0,'intval');
        $btime = input("btime");
        $etime = input("etime");
        $dataType = input("date_type");

        $where = [];
        if($type){
            $where['number'] = $type == 1 ? ['>',0] : ['<',0];
        }
        if($way){
            $where['type'] = $way;
        }

        if($dataType){
            if ($dataType == 'today'){//今天
                $btime = mktime(0, 0, 0);
                $etime = $btime + 86399;
            } elseif ($dataType == 'yesterday') {//昨天
                $btime = mktime(0, 0, 0, date("m"), date("d") - 1);
                $etime = $btime + 86399;
            } elseif ($dataType == 'month') {//昨天
                $btime = mktime(0, 0, 0, date("m"), 1);
                $etime = mktime(0, 0, 0, date("m") + 1, 1) - 1;
            }
            $btime = date("Y-m-d H:i:s",$btime);
            $etime = date("Y-m-d H:i:s",$etime);
        }
        if($btime && $etime){
            $where['create_time'] = [['>=',strtotime($btime)],['<=',strtotime($etime)]];

        }elseif($btime){
            $where['create_time'] = ['>=',strtotime($btime)];
        }elseif($etime){
            $where['create_time'] = ['<=',strtotime($etime)];
        }


        if($toxls){
            $title = [
                'create_time' => '时间',
                'type' => '收支',
                'number' => '金额',
                'explain' => '原因',
            ];
            $lists = Db::name('system_income')->field("create_time,number,explain")->where($where)->select();
            foreach($lists as $key => $val){
                $val['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
                $val['type'] = $val['number'] > 0 ? '收入' : '支出';
                $lists[$key] = $val;
            }
            return (new Misc())->toXls("系统收支统计",$title,$lists);
        }else{
            $lists = Db::name('system_income')->where($where)->order("id desc")->paginate(20,false,[
                'query' => input("get.")
            ]);
            $total = [];
            //总收入
            unset($where['number']);
            unset($where['type']);
            $temp = $where;
            $total['gold_income'] = Db::name('system_income')->where($temp)->where(['number' => ['>',0],'type' => FUNDS_TYPE_GOLD])->sum('number');
            $total['gold_outlay'] = Db::name('system_income')->where($temp)->where(['number' => ['<',0],'type' => FUNDS_TYPE_GOLD])->sum('number');

            $total['money_income'] = Db::name('system_income')->where($temp)->where(['number' => ['>',0],'type' => FUNDS_TYPE_MONEY])->sum('number');
            $total['money_outlay'] = Db::name('system_income')->where($temp)->where(['number' => ['<',0],'type' => FUNDS_TYPE_MONEY])->sum('number');
        }
        $this->assign('type',$type);
        $this->assign('way',$way);
        $this->assign('dataType',$dataType);
        $this->assign('btime',$btime);
        $this->assign('etime',$etime);
        $this->assign('total',$total);
        $this->assign('lists',$lists);
        return $this->fetch();
    }
}