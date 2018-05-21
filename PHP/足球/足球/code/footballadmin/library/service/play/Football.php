<?php

namespace library\service\play;
use library\service\Play;
use think\Db;
use think\Exception;

class Football{
    private $gameType = GAME_TYPE_FOOTBALL;
    /**
     * 比赛结果
     * @param $playId
     */
    public function getResult($playId,$retType = 'string'){
        //$play = Db::name('play')->where(['id' => $playId])->find();
        //if($play){return false;}

        $playSvr = (new Play());
        $playTeam = $playSvr->getTeams($playId);//Db::name('play_team')->where(['play_id' => $playId])->select();
        if(!$playTeam){return "";}
        $home = [];
        $guest = [];
        foreach($playTeam as $val){
            if($val['has_home'] == 1){
                $home = $val;
            }else{
                $guest = $val;
            }
        }
        if(!$home || !$guest){return false;}

        /*$first_score = '没有进球';
        if($home['first_score'] == 1){
            $team = getTeam($home['team_id']);
            $first_score = $team['name'];
        }elseif($guest['first_score'] == 1){
            $team = getTeam($guest['team_id']);
            $first_score = $team['name'];
        }*/

        if($retType == 'array'){
            $cnt = [];
            $cnt = [
                [
                    'title' => '全场比分',
                    'value' => "{$home['score']}:{$guest['score']}"
                ],
                [
                    'title' => '半场比分',
                    'value' => "{$home['half_score']}:{$guest['half_score']}"
                ],
               /* [
                    'title' => '最先进球',
                    'value' => "{$first_score}"
                ],
                [
                    'title' => '全场黄牌',
                    'value' => "{$home['yellow']}:{$guest['yellow']}"
                ],
                [
                    'title' => '全场红牌',
                    'value' => "{$home['red']}:{$guest['red']}"
                ],*/
                [
                    'title' => '全场进球数',
                    'value' => ($home['score'] + $guest['score']) . "球"
                ],
            ];
        }else{
            $cnt = "";
            $cnt .= "全场比分：{$home['score']}:{$guest['score']} \n";
            $cnt .= "半场比分：{$home['half_score']}:{$guest['half_score']} \n";
            //$cnt .= "最先进球：{$first_score} \n";
            //$cnt .= "全场黄牌：{$home['yellow']}:{$guest['yellow']} \n";
            //$cnt .= "全场红牌：{$home['red']}:{$guest['red']} \n";
            $cnt .= "全场进球数：" . ($home['score'] + $guest['score']) . "球 \n";
        }
        return $cnt;
    }

    /**
     * 结算前调整比赛数据
     * @param $play
     * @return mixed
     */
    public function getStatementResult($play){
        if(!$play["first_goals"] && ($play["status"] == PLAT_STATUS_START || $play["status"] == PLAT_STATUS_STATEMENT || $play["status"] == PLAT_STATUS_END)){
            $first_goals = 0;
            if(($play["team_home_half_score"]+$play["team_guest_half_score"])>0){
                if($play["team_home_half_score"] > 0 && $play["team_guest_half_score"] == 0){
                    $first_goals = 1;
                }elseif($play["team_home_half_score"] == 0 && $play["team_guest_half_score"] > 0){
                    $first_goals = 2;
                }
            }else{
                if($play["team_home_score"] > 0 && $play["team_guest_score"] == 0){
                    $first_goals = 1;
                }elseif($play["team_home_score"] == 0 && $play["team_guest_score"] > 0){
                    $first_goals = 2;
                }
            }
            if($play["status"] == PLAT_STATUS_END && ($play["team_home_score"]+$play["team_guest_score"]) == 0){
                $first_goals = 99;
            }
            $play["first_goals"] = $first_goals;
        }
        return $play;
    }

    /**
     * 更新比赛设置
     */
    public function upConf($play_id,$data,$admin_id){
        $team_home_half_score = $data["team_home_half_score"];
        $team_guest_half_score = $data["team_guest_half_score"];
        $team_home_score = $data["team_home_score"];
        $team_guest_score = $data["team_guest_score"];
        $status = $data["status"];
        $home_yellow = isset($data["home_yellow"]) ? $data["home_yellow"] : 0;
        $guest_yellow = isset($data["guest_yellow"]) ? $data["guest_yellow"] : 0;
        $home_red = isset($data["home_red"]) ? $data["home_red"] : 0;
        $guest_red = isset($data["guest_red"]) ? $data["guest_red"] : 0;
        $first_goals = 0;//isset($data["first_goals"]) ? $data["first_goals"] : 99;
        $remark = $data["remark"];

        //判断最先进球队伍
        /*if(!$first_goals){
            if (!$team_home_half_score && $team_guest_half_score){
                $first_goals = GUEST;
            } elseif ($team_home_half_score && !$team_guest_half_score) {
                $first_goals = HOME;
            }
        }*/
        
        if($status == PLAT_STATUS_END){//比赛结束
            if($team_home_half_score > $team_home_score || $team_guest_half_score > $team_guest_score){
                return '半场比分不能大于全场比分';
            }
           /* if(!$first_goals){
                return '请选择最先进球的队伍';
            }*/

            /*if(in_array($first_goals,[HOME,GUEST]) && $team_home_half_score == 0 && $team_home_score == $team_home_half_score && $team_home_half_score == $team_guest_score && $team_guest_half_score == $team_home_half_score){
                return '比分填写错误';
            }*/



        }
        $temp = $data;
        $data = [
            'team_home_half_score' => $team_home_half_score,
            'team_guest_half_score' => $team_guest_half_score,
            'team_home_score' => $team_home_score,
            'team_guest_score' => $team_guest_score,
            'home_yellow' => $home_yellow,
            'guest_yellow' => $guest_yellow,
            'home_red' => $home_red,
            'guest_red' => $guest_red,
            'first_goals' => $first_goals,
            'remark' => $remark,
            'statement_user' => $admin_id,
            'status' => $status,
        ];
        if(isset($temp['end_time']) && $temp['end_time']){
            $data['end_time'] = $temp['end_time'];
        }



        Db::startTrans();
        try{
            Db::name('play')->where(['id' => $play_id])->update($data);
            //更新球队数据
            Db::name("play_team")->where(['play_id' => $play_id,'has_home' => 1])->update([
                'score' => $team_home_score,
                'half_score' => $team_home_half_score,
                'red' => $home_red,
                'yellow' => $home_yellow,
                'first_score' => $first_goals == HOME ? 1 : 0,
            ]);
            Db::name("play_team")->where(['play_id' => $play_id,'has_home' => 0])->update([
                'score' => $team_guest_score,
                'half_score' => $team_guest_half_score,
                'red' => $guest_red,
                'yellow' => $guest_yellow,
                'first_score' => $first_goals == GUEST ? 1 : 0,
            ]);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
        (new Play())->upCache($play_id);
        (new Play())->cacheTeams($play_id);
        return true;
    }

}