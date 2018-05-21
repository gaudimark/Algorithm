<?php

/**
 * 搜索
 */

namespace app\index\controller;
use app\library\logic\Safe;
use library\service\Arena;
use library\service\Match;
use library\service\Play;
use library\service\User;
use think\Db;

class Search extends Safe{

    public function q(){
        $type = input('type');
        $keyword = input('keyword');
        if(!$type || !$keyword){
            return $this->retErr('search.q',10004);
        }
        $today = mktime(0,0,0);
        $result = [];
        switch ($type){
            case 'arena':
                $keyword = intval($keyword);
                $arena = (new Arena())->getCacheArenaById($keyword);
                if($arena && !in_array($arena['status'],[ARENA_DEL,ARENA_DIS]) && $arena['classify'] != ARENA_CLASSIFY_CREDIT){
                    $user = getUser($arena['user_id']);
                    $result = [
                        'id' => $arena['id'],
                        'mark' => $arena['mark'],
                        'status' => $arena['status'],
                        'create_time' => $arena['create_time'],
                        'game_id' => $arena['game_id'],
                        'has_sys' => $arena['has_sys'],
                        'intro' => $arena['intro'],
                        'item_id' => $arena['game_type'],
                        'rules_id' => $arena['rules_id'],
                        'rules_type' => $arena['rules_type'],
                        'status' => $arena['status'],
                        'user_id' => $user['id'],
                        'user_level' => $user['level']['name'],
                        'nickname' => $user['nickname'],
                        'avatar' => $user['avatar'],
                        'teams' => [],
                        'play' => [
                            'id' => $arena['play']['id'],
                            'play_time' => $arena['play']['play_time'],
                            'match_time' => $arena['play']['match_time']
                        ],
                        'match' => [
                            'id' => $arena['match']['id'],
                            'name' => $arena['match']['name'],
                            'logo' => $arena['match']['logo'],
                            'bgcolor' => $arena['match']['bgcolor'],
                        ],

                    ];

                    foreach ($arena['teams'] as $team) {
                        $result['teams'][] = [
                            'id' => $team['id'],
                            'name' => $team['name'],
                            'logo' => $team['logo'],
                            'logo_big' => $team['logo_big'],
                        ];
                    }
                }
                break;
            case 'match':
                $data = Db::name('match')->field('id,name,logo,bgcolor')->where(['game_type' => ['in',[GAME_TYPE_FOOTBALL,GAME_TYPE_WCG]],'name' => ['like',"%{$keyword}%"]])->limit(50)->select();
                foreach ($data as $key => $val) {
                    $val['logo'] = get_image_thumb_url($val['logo']);
                    $val['name_kw'] = $this->_parseKeyword($val['name'], $keyword);
                    $result[] = $val;
                }
                break;
            case 'team' :
                $result = '';//cache('index_search_team'.md5($keyword));
                if(!$result){
                    $today = strtotime("-2 day");
                    //'p.status' => PLAT_STATUS_NOT_START,
                    $data = Db::name('team')->alias('t')
                        ->field('p.id,p.play_time,p.game_type as item_id,p.game_id,p.arena_total,p.status,p.game_type as item_id,p.has_play_rules,p.match_time,p.bo')
                        ->join('__PLAY_TEAM__ pl', 't.id = pl.team_id', 'LEFT')
                        ->join('__PLAY__ p', 'p.id = pl.play_id', 'LEFT')
                        ->where(['p.game_type' => ['in',[GAME_TYPE_FOOTBALL,GAME_TYPE_WCG]],'t.name' => ['like', "%{$keyword}%"], 'p.id' => ['gt', 0], 'p.play_time' => ['gt', $today]])
                        ->order('has_arena desc,p.play_time asc')->group("p.id")->limit(100)->select();
                    if ($data){
                        $playSvr = new Play();
                        foreach ($data as $key => $val) {
                            $teams = (new Play())->getTeams($val['id'], ['id', 'name', 'logo','has_home','score','half_score','red','yellow'],$this->getUserId('sys'));
                            //推荐擂台
                            $getPlayRules = [];
                            if($val['has_play_rules']){
                                $getPlayRules = $playSvr->getPlayRules($val['id']);
                            }
                            $arenaId = 0;
                            foreach($getPlayRules as $r){
                                if($r['arena_id']){
                                    $arenaId = $r['arena_id'];
                                    break;
                                }
                            }
                            if(!$arenaId){
                                $arena = (new Arena())->getTopArena($val['id']);
                                $arenaId = $arena['id'];
                            }
                            $val['arena_id'] = intval($arenaId);

                            foreach ($teams as $tk => $team) {
                                $team['name_kw'] = $this->_parseKeyword($team['name'], $keyword);
                                $teams[$tk] = $team;
                            }
                            $val['teams'] = $teams;
                            unset($val['has_play_rules']);
                            if($val['item_id'] == GAME_TYPE_WCG && $val['bo'] && $val['bo'] > 0){
                                $val['bo'] = "BO{$val['bo']}";
                            }else{
                                $val['bo'] = '';
                            }
                            $result[] = $val;
                        }
                    }
                    //cache('index_search_team'.md5($keyword), $result, 3600);
                }
                break;
            case 'user' :
                $data = Db::name('user')->field('id,nickname,avatar,level,deposit_money')->where(['nickname' => ['like',"%{$keyword}%"]])->limit(100)->select();
                $result = '';
                if($data){
                    foreach ($data as $key => $val){
                        $level = (new User())->exper($val);
                        $result[] = [
                            'id' => $val['id'],
                            'nickname' => $this->_parseKeyword($val['nickname'],$keyword),
                            'avatar' => getUserAvatar($val['avatar'],$val['id']),
                            'level' => $level,
                        ];
                    }
                }

                break;
            default:
                $result = [];
                break;
        }

        return $this->retSucc('search.q',$result);
    }

    private function _parseKeyword($content,$keyword){
        $content = str_replace($keyword,'<span style="color:#FF0000">'.$keyword.'</span>',$content);
        return $content;
    }

}