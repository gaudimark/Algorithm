<?php

namespace app\index\controller;

use app\library\logic\Safe;
use library\service\Arena;
use library\service\Layout;
use library\service\Rule;
use library\service\User;
use think\Db;
use think\cache;

class Play extends Safe
{

    /**
     * 获取比赛列表-有擂台的比赛
     */
    public function lists()
    {
        // \think\Debug::remark("begin");
        $play_ids = input("play_ids"); //比赛Id
        $item_id = input("item_id/d"); //项目ID
        $limit = input("limit/d"); //显示条数
        $hot = input("hot/d"); //比赛热度
        $item_value = input("item_value"); //项目标识
        $item_index = input("item_index"); //项目模块序号
        $game_id = input("game_id"); //所属游戏
        $match_id = input("match_id"); //赛事ID
        $status = input("status/d");
        $page = max(1, input("page/d", 0, 'intval'));
        $limit = $limit ? $limit : 20;
        $offset = ($page - 1) * $limit;
        $where = [];
        $where['status'] = $status ? $status : PLAT_STATUS_NOT_START;
        if ($item_id) {
            $where['game_type'] = $item_id;
        }
        if ($game_id) {
            $game_id = array_map('intval', explode(",", $game_id));
            $where['game_id'] = ['in', [implode(",", array_values($game_id))]];
        }
        if ($match_id) {
            $match_id = explode(",", $match_id);
            $where['match_id'] = ['in', [implode(",", array_values($match_id))]];
        }
        if ($play_ids) {
            $play_ids = array_map("intval", array_unique(explode(",", $play_ids)));
            $where['id'] = ['in', implode(",", array_values($play_ids))];
        }
        if ($hot && $hot > 0 && in_array($hot, [1, 2, 3])) {
            $where['hot'] = $hot;
        }

        if ($item_value) {
            $item_value = explode("_", $item_value);
            if ($item_value && count($item_value) > 1) {
                if ($item_value[0] == 'game') {
                    $where['game_id'] = intval($item_value[1]);
                } elseif ($item_value[0] == 'layout') { //布局模块
                    $layoutId = $item_value[1];
                    $layout = (new Layout())->getLayout($layoutId);
                    unset($where['game_type']);
                    unset($where['game_id']);
                    unset($where['match_id']);
                    unset($where['id']);
                    $item_index = max(0, $item_index - 1);
                    $detail = $layout['detail'][$item_index];
                    $where['game_type'] = $detail['item_id'];
                    if (isset($detail['lib_ids']) && $detail['lib_ids']) {
                        if ($detail['item_type'] == 'match') {
                            $where['match_id'] = ['in', implode(",", array_values($detail['lib_ids']))];
                        } elseif ($detail['item_type'] == 'game') {
                            $where['game_id'] = ['in', implode(",", array_values($detail['lib_ids']))];
                        }
                    }
                }
            }
        }
        $where['has_arena'] = 1; //必须有擂台
        $where['has_sys_arena'] = 1; //必须是系统擂台
        $playSvr = new \library\service\Play();
        $lists = model('play')->field("id,game_id,game_type as item_id,match_id,play_time,status,arena_total,has_arena,match_time,has_play_rules,live_type,live,bo")->where($where)->limit($offset, $limit)->order("play_time asc")->select();
        //$playSvr = new \library\service\Play();
        //$sqlStr = Db::name('play')->getLastSql();
        // $total = cache("index_play_list_total_".md5($sqlStr));
        // if(!$total){
        $total = model('play')->where($where)->count();
        //cache("index_play_list_total_".md5($sqlStr),$total,600);
        //}
        $matchs = [];
        $total_pages = ceil($total / $limit);
        $userSvr = new User();
        $myUserId = $this->getUserId('sys');
        if ($lists) {
            foreach ($lists as $key => $val) {
                $teams = $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'logo_big', 'has_home', 'score', 'half_score', 'red', 'yellow'], $this->getUserId('sys'));
                if (isset($matchs[$val['match_id']])) {
                    $match = $matchs[$val['match_id']];
                } else {
                    $match = getMatch($val['match_id'], null, ['name']);
                    $matchs[$val['match_id']] = $match;
                }
                if ($val['item_id'] == GAME_TYPE_WCG && $val['bo'] && $val['bo'] > 0) {
                    $val['bo'] = "BO{$val['bo']}";
                } else {
                    $val['bo'] = '';
                }
                $getPlayRules = [];
                /* if($val['has_play_rules']){
                  $getPlayRules = $playSvr->getPlayRules($val['id']);
                  } */
                $arenaId = 0;
                foreach ($getPlayRules as $r) {
                    if ($r['arena_id']) {
                        $arenaId = $r['arena_id'];
                        break;
                    }
                }
                if (!$arenaId) {
                    /* $arena = (new Arena())->getTopArena($val['id']);
                      $arenaId = $arena['id']; */
                }
                $val['arena_id'] = $arenaId;
                $val['my_whether_arena'] = intval($userSvr->checkPublishArenaByPlay($myUserId, $val['id'])); //当前比赛是否坐庄
                $val['my_whether_bet'] = intval($userSvr->checkBetPlay($myUserId, $val['id'])); //当前比赛是否投注
                $val['teams'] = $teams;
                $val['match'] = $match;
                $val['live'] = $playSvr->getPlayLive($val['id'], $val['live']);
                unset($val['has_play_rules']);
                if ($val['status'] == PLAT_STATUS_START) {
                    $val['match_time'] = getMatchRunTime($val['match_time'], $val['play_time']);
                }
                $lists[$key] = $val;
            }
        }
        $next_page = ($total_pages > $page) ? 1 : 0;
        /*
          \think\Debug::remark("end");
          echo \think\Debug::getRangeMem('begin','end');
          echo \think\Debug::getRangeTime('begin','end'); */
        return $this->retSucc('play.lists', $lists, '', ['next_page' => $next_page, 'total_page' => $total_pages]);
    }

    /**
     * 获取指定比赛列表
     */
    public function lists2()
    {
        // \think\Debug::remark("begin");
        $play_ids = input("play_ids");
        $retType = input("ret_type");
        if (!$play_ids) {
            return $this->retErr('play.lists2', 10013);
        }
        $play_ids = array_map("intval", array_unique(explode(",", $play_ids)));
        $where['id'] = ['in', array_values($play_ids)];
        $playSvr = new \library\service\Play();
        $lists = model('play')->field("id,game_id,game_type as item_id,match_id,play_time,status,arena_total,has_arena,match_time,has_play_rules,live_type,live,bo")->where($where)->select();
        $matchs = [];
        $userSvr = new User();
        $myUserId = $this->getUserId('sys');
        $data = [];
        if ($lists) {
            foreach ($lists as $key => $val) {
                $teams = $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'logo_big', 'has_home', 'score', 'half_score', 'red', 'yellow'], $this->getUserId('sys'));
                if (isset($matchs[$val['match_id']])) {
                    $match = $matchs[$val['match_id']];
                } else {
                    $match = getMatch($val['match_id'], null, ['name']);
                    $matchs[$val['match_id']] = $match;
                }
                if ($val['item_id'] == GAME_TYPE_WCG && $val['bo'] && $val['bo'] > 0) {
                    $val['bo'] = "BO{$val['bo']}";
                } else {
                    $val['bo'] = '';
                }

                $getPlayRules = [];
                if ($val['has_play_rules']) {
                    $getPlayRules = $playSvr->getPlayRules($val['id']);
                }
                $arenaId = 0;
                foreach ($getPlayRules as $r) {
                    if ($r['arena_id']) {
                        $arenaId = $r['arena_id'];
                        break;
                    }
                }
                if (!$arenaId) {
                    $arena = $val['status'] == PLAT_STATUS_NOT_START ? (new Arena())->getTopArena($val['id']) :
                        (new Arena())->getTopArena($val['id'], null, true);
                    $arenaId = $arena['id'];
                }
                $val['arena_id'] = intval($arenaId);

                if ($retType) {
                    $val['my_whether_arena'] = intval($userSvr->checkPublishArenaByPlay($myUserId, $val['id'])); //当前比赛是否坐庄
                    $val['my_whether_bet'] = intval($userSvr->checkBetPlay($myUserId, $val['id'])); //当前比赛是否投注
                } else {
                    $val['my_whether_arena'] = 0;
                    $val['my_whether_bet'] = 0;
                }
                $val['teams'] = $teams;
                $val['match'] = $match;
                $val['game_id'] = intval($val['game_id']);
                unset($val['has_play_rules']);
                $val['live'] = $playSvr->getPlayLive($val['id'], $val['live']);
                if ($val['status'] == PLAT_STATUS_START) {
                    $val['match_time'] = getMatchRunTime($val['match_time'], $val['play_time']);
                }
                $lists[$val['id']] = $val;
            }
        }
        foreach ($play_ids as $key => $val) {
            $data[$key] = $lists[$val];
        }


        return $this->retSucc('play.lists2', array_values($data));
    }

    public function search()
    {

    }

    /**
     * 获取比赛列表
     */
    public function all()
    {
        $match_id = input("match_id"); //赛事ID
        $status = input("status", 1); //比赛状态
        $test = input('test',0);
        $hot = input('hot\d');
        $team_name = input("team_name");
        $where = [];
        $where['p.game_type'] = 1;
        if ($match_id) {
            $match_id = explode(",", $match_id);
            $where['p.match_id'] = ['in', $match_id];
        }

        $where['p.has_odds'] = 1;

        if ($team_name) {
            $where['p.team_home_name|p.team_guest_name'] = array('like', '%' . $team_name . '%');
        } else {
            $where['p.status'] = array('NOT IN',[PLAT_STATUS_CUT,PLAT_STATUS_STATEMENT_BEGIN,PLAT_STATUS_STATEMENT]);
            $where['p.play_time'] = ['gt', time()-10800];
            if(!$match_id){
                $matchId = 'all';
            }else{
                $matchId = $match_id;
            }

            if($test != 1){
               // $cache = Cache::hGet('match_list', $matchId);
             //   if (!empty($cache)) {
                   // return $this->retSucc('play.all', $cache, '');
               // }
            }
        }
       
       /* if($hot > 0) {
            $where['hot'] = $hot;
        }
        if ($status) {
            if (is_numeric($status)) {
                $where['status'] = intval($status);
            } else {
                $where['status'] = ['in', array_map('intval', array_values(explode(",", $status)))];
            }

        }*/
//        if ($btime && $btime = strtotime($btime)) {
//            $where['play_time'] = ['gt', $btime];
//        }
        $where['o.odds_type'] = 1;
        //$lists = Db::name('play')->field("*,game_type as item_id")->where($where)->order("status asc,play_time asc")->select();
        $lists = Db::name('play')->alias("p")
            ->join("__ODDS__ o","o.play_id=p.id",'LEFT')
            ->field("p.*,p.game_type as item_id")
            ->where($where)
            ->order("p.status asc,p.play_time asc")
            -> group('o.play_id')
            ->select();
        //echo Db::name('play')->getLastSql();exit;
        //echo Db::name('play')->getLastSql();
        $playSvr = new \library\service\Play();
        /* $sqlStr = Db::name('play')->getLastSql();
         $total = cache("index_play_list_total_" . md5($sqlStr));
         if (!$total) {
         $total = Db::name('play')->where($where)->count();
         cache("index_play_list_total_" . md5($sqlStr), $total, 600);
         }
         $total_pages = ceil($total / $limit); */
        $matchs = [];

        if ($lists) {
            foreach ($lists as $key => $val) {
               // if($test != 1){
                    //删除if时不能删除以下代码
                    $odds_type = Db::name('odds')->where(['play_id' => $val['id']])->value('odds_type');
                    /*if ($odds_type != 1) {
                        unset($lists[$key]);
                        continue;
                    }*/
               // }
                $teams = $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'has_home', 'score', 'half_score', 'red', 'yellow'], $this->getUserId('sys'));
                if (isset($matchs[$val['match_id']])) {
                    $match = $matchs[$val['match_id']];
                } else {
                    $match = getMatch($val['match_id'], null, ['name']);
                    $matchs[$val['match_id']] = $match;
                }

                if ($val['item_id'] == GAME_TYPE_WCG && $val['bo'] && $val['bo'] > 0) {
                    $val['bo'] = "BO{$val['bo']}";
                } else {
                    $val['bo'] = '';
                }

                $getPlayRules = [];
                if ($val['has_play_rules']) {
                    $getPlayRules = $playSvr->getPlayRules($val['id']);
                }
                $arenaId = 0;
                foreach ($getPlayRules as $r) {
                    if ($r['arena_id']) {
                        $arenaId = $r['arena_id'];
                        break;
                    }
                }

                if (!$arenaId) {
                    $arena = (new Arena())->getTopArena($val['id']);
                    $arenaId = $arena['id'];
                }
                $val['arena_id'] = $arenaId;
                $val['teams'] = $teams;
                $val['match'] = $match;
                unset($val['has_play_rules']);
                if ($val['status'] == PLAT_STATUS_START) {
                    $val['match_time'] = getMatchRunTime($val['match_time'], $val['play_time']);
                }
                $lists[$key] = $val;
            }
        }
        $data = array_values($lists);
        if (!$team_name) {
            Cache::hSet('match_list', $matchId, json_encode($data));
        }
        return $this->retSucc('play.all', $data, '');
    }

    /**
     * 返回指定队伍比赛列表
     */
    public function team_play_list()
    {
        $teamId = input("team_id/d");
        $status = input("status/d");
        $status = [PLAT_STATUS_NOT_START, PLAT_STATUS_START, PLAT_STATUS_INTERMISSION];


        $lists = Db::view('play_team', 'play_id,team_id')
            ->view("play", 'id,game_type as item_id,game_id,play_time,status,arena_total,has_arena,has_play_rules,match_time,match_id,live_type,live', 'play_team.play_id = play.id')
            ->where("status in(" . implode(",", array_values($status)) . ") and team_id={$teamId} and has_sys_arena=1")
            ->order("play_time asc")
            ->limit(10)
            ->select();
        $data = [];
        $playSvr = new \library\service\Play();
        $userSvr = new User();
        $myUserId = $this->getUserId('sys');
        foreach ($lists as $key => $val) {
            unset($val['play_id']);
            unset($val['team_id']);
            $val['game_id'] = intval($val['game_id']);
            $teams = $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'has_home', 'score', 'half_score', 'red', 'yellow'], $this->getUserId('sys'));
            if (isset($matchs[$val['match_id']])) {
                $match = $matchs[$val['match_id']];
            } else {
                $match = getMatch($val['match_id'], null, ['name']);
                $matchs[$val['match_id']] = $match;
            }
            $getPlayRules = [];
            if ($val['has_play_rules']) {
                $getPlayRules = $playSvr->getPlayRules($val['id']);
            }
            $arenaId = 0;
            foreach ($getPlayRules as $r) {
                if ($r['arena_id']) {
                    $arenaId = $r['arena_id'];
                    break;
                }
            }

            if (!$arenaId) {
                $arena = $val['status'] == PLAT_STATUS_NOT_START ? (new Arena())->getTopArena($val['id']) : (new Arena())->getTopArena($val['id'], null, true);
                $arenaId = $arena['id'];
            }

            $val['arena_id'] = intval($arenaId);
            $val['teams'] = $teams;
            $val['match'] = $match;
            $val['live'] = $playSvr->getPlayLive($val['id'], $val['live']);
            unset($val['has_play_rules']);
            $lists[$key] = $val;
        }
        return $this->retSucc('play.team_play_list', array_values($lists));
    }

    /**
     * 比赛详情
     */
    public function info()
    {
        $playId = input("play_id/d");
        if (!$playId) {
            return $this->retErr('play.info', 10000);
        }

        $playSvr = new \library\service\Play();

        //Db::name('play')->where(['id' => $playId])->find();//
        $info = $playSvr->getPlay($playId);
        if (!$info) {
            return $this->retErr('play.info', 30001);
        }

        $info['item_id'] = $info['game_type'];
        $info['item_name'] = getSport($info['game_type']);
        $info['game_id'] = $info['game_id'] ? $info['game_id'] : '';
        $info['game_name'] = $info['game_id'] ? getGame($info['game_id'], 'name') : '';
        $info['match'] = getMatch($info['match_id'], null, ['id', 'name', 'logo', 'bgcolor']);
        $info['teams'] = $playSvr->getTeams($info['id'], ['id', 'name', 'logo', 'logo_big', 'has_home', 'score', 'half_score', 'red', 'yellow', 'score_json'], $this->getUserId('sys'));
        $info['result'] = $info['status'] == PLAT_STATUS_STATEMENT ? $playSvr->getResult($info['game_type'], $info['id'], 'array') : [];
        $info['live'] = $playSvr->getPlayLive($playId, $info['live']);
        //获取玩法
        //$rules = getRuleData($info['game_type'],null,null,true,true);
        return $this->retSucc('play.info', $this->reField($info));
    }

    /**
     * 玩法大厅
     */
    public function rule_hall()
    {
        $playId = input("play_id/d");
        if (!$playId) {
            return $this->retErr('play.rule', 10000);
        }
        $playSvr = new \library\service\Play();
        $info = $playSvr->getPlay($playId); //Db::name('play')->where(['id' => $playId])->find();
        if (!$info) {
            return $this->retErr('play.rule', 30001);
        }

        $info['item_id'] = $info['game_type'];
        if ($info['item_id'] == GAME_TYPE_WCG && $info['bo'] && $info['bo'] > 0) {
            $info['bo'] = "BO{$info['bo']}";
        } else {
            $info['bo'] = '';
        }
        $match = getMatch($info['match_id'], '', ['id', 'name', 'bgcolor']);
        $info['match'] = $match;
        /* if (!$info['match_time']){
          $info['match_time'] =  getPlayStatus($info['status'],$info['play_time']);
          } */

        if ($info['status'] == PLAT_STATUS_START) {
            $info['match_time'] = getMatchRunTime($info['match_time'], $info['play_time']);
        }

        //获取队伍信息
        $info['teams'] = $playSvr->getTeams($playId, ['id', 'name', 'logo', 'has_home', 'score', 'half_score', 'red', 'yellow'], $this->getUserId('sys'));

        $info['live'] = $playSvr->getPlayLive($playId, $info['live']);
        //获取玩法$gameType,$rule_id = null,$key = null,$isDelete = false,$status = null,$game_id = null
        $rules = getRuleData($info['game_type'], null, null, true, true, $info['game_id']);
        //获取当前比赛下的玩法配置
        $playRules = Db::name('play_rules')->where(['play_id' => $playId])->order("sort asc,total_prize desc")->select();
        $data = [];
        foreach ($playRules as $val) {
            $data[$val['rules_id']] = $rules[$val['rules_id']];
            $data[$val['rules_id']]['arena_id'] = $val['arena_id'];
            unset($rules[$val['rules_id']]);
        }
        if ($rules) {
            $data = array_merge($data, $rules);
        }
        $arenaSvr = (new Arena());
        foreach ($data as $key => $val) {
            unset($val['game_id']);
            unset($val['game_type']);
            unset($val['explain']);
            unset($val['is_delete']);
            unset($val['status']);
            if (isset($val['arena_id']) && $val['arena_id']) {
                $_ = $arenaSvr->getCacheArenaById($val['arena_id']);
                $val['arena'] = [
                    'id' => $_['id'],
                    'total' => $_['deposit'] + $_['bet_money'],
                    'mark' => $_['mark'],
                    'user_id' => $_['user_id'],
                ];
            } else {
                $_ = $arenaSvr->getTopArena($playId, $val['type'], false, $val['id']);
                $val['arena_id'] = $_['id'];
                $val['arena'] = $_;
            }

            $data[$key] = $val;
        }

        $temp = [];
        foreach ($data as $key => $val) {
            if (!isset($val['arena_id']) || !$val['arena_id']) {
                $temp[] = $val;
                unset($data[$key]);
            }
        }

        $data = array_merge($data, $temp);
        $info['rules'] = $data;
        return $this->retSucc('play.rule', $this->reField($info));
    }

    private function reField($info)
    {
        unset($info['team_home_id']);
        unset($info['team_home_name']);
        unset($info['team_guest_id']);
        unset($info['team_guest_name']);
        unset($info['game_type']);
        unset($info['md5_play']);
        unset($info['create_time']);
        unset($info['update_time']);
        unset($info['has_odds']);
        unset($info['odds_key']);
        unset($info['team_home_score']);
        unset($info['team_guest_score']);
        unset($info['team_home_half_score']);
        unset($info['team_guest_half_score']);
        unset($info['score_json']);
        unset($info['home_yellow']);
        unset($info['home_red']);
        unset($info['guest_yellow']);
        unset($info['guest_red']);
        return $info;
    }

    /**
     * 投注大厅
     */
    public function arena()
    {
        // \think\Debug::remark("begin");
        $playId = input("play_id/d");
        $rules_type = input("rules_type/d");
        $arenaId = input("arena_id/d", 0);
        $nickname = input("nickname");
        $page = max(1, input("page/d", 0, 'intval'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        if (!$playId) {
            return $this->retErr('play.arena', 10000);
        }

        $playSvr = new \library\service\Play();
        $info = $playSvr->getPlay($playId);
        if (!$info) {
            return $this->retErr('play.arena', 30001);
        }
        /* if (!$info['match_time']){
          $info['match_time'] =  getPlayStatus($info['status'],$info['play_time']);
          } */

        if ($info['status'] == PLAT_STATUS_START) {
            $info['match_time'] = getMatchRunTime($info['match_time'], $info['play_time']);
        }

        $info['item_id'] = $info['game_type'];

        if ($info['item_id'] == GAME_TYPE_WCG && $info['bo'] && $info['bo'] > 0) {
            $info['bo'] = "BO{$info['bo']}";
        } else {
            $info['bo'] = '';
        }

        $match = getMatch($info['match_id'], null, ['id', 'name', 'logo']);
        $info['match'] = $match;
        $info['teams'] = (new \library\service\Play())->getTeams($playId, ['id', 'name', 'logo', 'score', 'red', 'yellow', 'has_home'], $this->getUserId('sys'));
        $where = [];
        // $where['private'] = ['in',[ARENA_DISPLAY_ALL,ARENA_DISPLAY_FRIENDS]];
        $where['play_id'] = $playId;
        $where['has_hide'] = STATUS_NO; //非隐私擂台
        $where['has_sys'] = 1;
        $where['classify'] = ARENA_CLASSIFY_GOLD;
        $where['status'] = ['not in', [ARENA_DEL, ARENA_DIS]];
        if ($rules_type) {
            $where['rules_type'] = $rules_type;
        }
        if ($arenaId) {
            $where['id'] = $arenaId;
        }
        $info['live'] = $playSvr->getPlayLive($playId, $info['live']);
        $info['my_whether_arena'] = 0;
        $info['my_whether_bet'] = 0;
        //$where['status'] = ARENA_START;
        //获取擂台列表
        $field = "id,mark,has_sys,rules_type,rules_id,game_type,match_id,game_id,user_id,odds,status,private,deposit,intro,bet_money,create_time";
        if ($nickname) {
            $where['nickname'] = ['like', "%{$nickname}%"];
            $lists = Db::view("arena", $field)
                ->view("user", "nickname", "user.id=arena.user_id")
                ->where($where)
                ->limit($offset, $limit)->order("has_sys desc,private asc,create_time desc")->select();

            $total = Db::view("arena", $field)
                ->view("user", "nickname", "user.id=arena.user_id")
                ->where($where)->count();
        } else {
            $lists = Db::name('arena')->field($field)->where($where)->limit($offset, $limit)->order("has_sys desc,private asc,create_time desc")->select();
            $total = Db::name('arena')->where($where)->count();
        }
        //$sqlStr = Db::name('arena')->getLastSql();
        $ruleSvr = (new Rule())->factory($info['game_type']);
        if ($lists) {
            $arenaSvr = new \library\service\Arena();
            foreach ($lists as $key => $val) {
                //$arenaSvr->setArenaId($val['id']);
                //$val['rules_id'] = $val['rules_type'];
                // $val['rules_type'] = $ruleSvr->getRuleType($val['rules_id']);
                $val['rules_name'] = $ruleSvr->getRuleText($val['rules_id'], 'name');
                $val['friend'] = $arenaSvr->checkArenaFriend($val['id'], $this->getUserId());
                $val['invite'] = $arenaSvr->checkArenaInvite($val['id'], $this->getUserId());
                $val['user'] = getUserField($val['user_id'], ['id', 'nickname', 'avatar']);
                //unset($val['rules_type']);
                $odds = @json_decode($val['odds'], true);
                $val['odds'] = $ruleSvr->parseOddsWords($odds, $val['rules_id'], $val['game_type'] == GAME_TYPE_FOOTBALL ? [] : $info['teams']);
                //$val['bet_total'] = @json_decode($val['bet_total'],true);
                $val['odds'] = array_values($val['odds']);
                $val['item_value'] = getItemValue($val['game_type'], [
                    ['type' => 'match', 'value' => $val['match_id']],
                    ['type' => 'game', 'value' => $val['game_id']],
                ]);
                $val = $this->reField($val);
                $lists[$key] = $val;
            }
        }
        $info['arena_list'] = $lists;
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;

        //\think\Debug::remark("end");
        //echo \think\Debug::getRangeMem('begin','end');
        //echo \think\Debug::getRangeTime('begin','end');
        return $this->retSucc('play.arena', $this->reField($info), '', ['next_page' => $next_page, 'total_page' => $total_pages]);
    }

    /**
     * 某场比赛赔率列表,赔率监控
     */
    public function odds()
    {
        $playId = input("play_id/d");
        $rules_type = input("rules_type/d");
        $play = (new \library\service\Play())->getPlay($playId);
        if (!$play) {
            return $this->retErr("play.odds", 10013);
        }
        $teams = $play['game_type'] == GAME_TYPE_FOOTBALL ? [] : (new \library\service\Play())->getTeams($playId);
        $company = cache('odds_company');
        $ruleSvr = (new Rule())->factory($play['game_type']);
        $oddsList = Db::name('odds')->where(['play_id' => $playId, 'rules_type' => $rules_type])->select();
        $data = [];
        if ($oddsList) {
            foreach ($oddsList as $val) {
                $odds = @json_decode($val['odds'], true);
                $oddsInit = $ruleSvr->parseOddsWords($odds['init'], $val['rules_id'], $teams, $val['rules_type']);
                $oddsTime = $ruleSvr->parseOddsWords($odds['time'], $val['rules_id'], $teams, $val['rules_type']);
                $temp = [];
                foreach ($oddsTime as $k1 => $v1) {
                    unset($v1['win_money']);
                    unset($v1['money']);
                    unset($v1['money_total']);
                    $v1['odds_init'] = $oddsInit[$k1]['odds']; //初始赔率
                    $oddsTime[$k1] = $v1;
                }
                $data[] = [
                    'id' => $val['id'],
                    'company_name' => isset($company[$val['odds_company_id']]) ? $company[$val['odds_company_id']]['name'] : '',
                    'odds' => array_values($oddsTime)//array_values($odds),
                ];
            }
        }
        return $this->retSucc("play.odds", $data);
    }

    /**
     * 赔率id
     * */
    public function newDataList()
    {
        //获取赔率
       // $oddsConfig = DB::name('config')->where(['var' => 'odds'])->value('value');
        //比赛id
        $playId = input("play_id/d");
        if (!$playId) {
            return $this->retErr('play.arena', 10000);
        }
        $oauth = (new \library\service\Oauth());
        $user_id = $oauth->getUserIdByToken(input('token'));
        $cache = Cache::hget('play_cache_list', $playId . '_' . $user_id);
        if (!empty($cache)) {
            //return $this->retSucc("play.odds", $cache);
        }


        $itemId = 1;
        $where = array(
            'game_type' => $itemId,
            'is_delete' => 0,
            'status' => 1
        );
        $ruleSvr = (new Rule())->factory($itemId);
        $disRuleTypeList = $ruleSvr->disRulesType;
        if ($disRuleTypeList && is_array($disRuleTypeList)) {
            $where['type'] = ['not in', array_values($disRuleTypeList)];
        }
        $order = "sort asc,id asc";
        $data = DB::name('rules')->where($where)->field('id,name,alias,min_deposit')->order($order)->select();
        foreach ($data as $key => &$value) {
            $where = array(
                'game_type' => 1,
                'rules_id' => $value['id'],
                'play_id' => $playId,
                'odds_type' => 1
            );
            $newData = DB::name('odds')->where($where)->field('id,odds')->select();
            if (empty($newData)) {
                $value['odds'] = array();
                continue;
            }
            $value['odds'] = array();
            $where = array(
                'classify' => 1,
                'play_id' => $playId,
                'rules_id' => $value['id'],
            );
            $info = DB::name('arena')->where($where)->find();
            $value['min_bet'] = $info['min_bet'] ? $info['min_bet'] : 0;
            $value['max_bet'] = $info['max_bet'] ? $info['max_bet'] : 0;
            $value['arena_id'] = $info['id'];
            if ($newData) {
                foreach ($newData as $k => $v) {
                    $json = json_decode($v['odds'], true);
                    $oddsInfo = $json['time'];
                    if ($value['name'] == "比分") {
                        $result = [];
                        foreach ($oddsInfo as $key => $item) {
                            $insert = [];
                            if ($key == 'other') {
                                $insert['label'] = "其他";
                                $insert['value'] = $item['other'];
                                $insert['key'] = $key;
                                $result[] = $insert;
                            } else {
                                foreach ($item as $tips => $point) {
                                    $exps = explode("_", $tips);
                                    if (count($exps) == 3) {
                                        $insert["value"] = $point;
                                        if ($key == "home") {
                                            $insert["label"] = $exps[1] . "-" . $exps[2];
                                        } else if ($key == "guest") {
                                            $insert["label"] = $exps[2] . "-" . $exps[1];
                                        } else {
                                            $insert["label"] = $exps[1] . "-" . $exps[2];
                                        }
                                        $insert['key'] = $key;
                                        $insert['target_item'] = $tips;
                                        $result[] = $insert;
                                    }
                                }
                            }
                        }
                        $value['odds'] = $result;
                    } else if ($value['name'] == "主进球" || $value['name'] == "客进球" || $value['name'] == "全场进球总数") {
                        $points = $oddsInfo;

                        $result = [];
                        foreach ($points as $key => $item) {
                            $insert = [];
//                foreach ($value as $tips => $point) {
                            $exps = explode("_", $key);
//                            if (count($exps) == 3) {
                            $insert["value"] = $item;
                            if ($value['name'] == "全场进球总数") {
                                if (count($exps) > 3) {
                                    $insert["label"] = $exps[2] . "-" . $exps[3];
                                } else {
                                    $insert["label"] = $exps[2];
                                }
                            } else {
                                $insert["label"] = $exps[1];
                            }
                            $insert['key'] = $key;
                            $result[] = $insert;
//                            }
                        }
                        $value['odds'] = $result;
                    } else {
                        if (!array_key_exists('handicap', $oddsInfo)) {
                            $oddsInfo['handicap'] = "";
                        }
                        switch ($oddsInfo['handicap']) {
                            case '0':
                                $oddsInfo['type_name'] = '平手(0)';
                                break;
                            case '0.25':
                                $oddsInfo['type_name'] = '受让平手/半球(0/0.5)';
                                break;
                            case '-0.25':
                                $oddsInfo['type_name'] = '>平手/半球(-0/0.5)';
                                break;
                            case '0.5':
                                $oddsInfo['type_name'] = '受让半球(0.5)';
                                break;
                            case '-0.5':
                                $oddsInfo['type_name'] = '半球(-0.5)';
                                break;
                            case '0.75':
                                $oddsInfo['type_name'] = '受让半球/一球(0.5/1)';
                                break;
                            case '-0.75':
                                $oddsInfo['type_name'] = '半球/一球(-0.5/1)';
                                break;
                            case '1':
                                $oddsInfo['type_name'] = '受让一球(1)';
                                break;
                            case '1':
                                $oddsInfo['type_name'] = '受让一球(1)';
                                break;
                            case '-1':
                                $oddsInfo['type_name'] = '一球(-1)';
                                break;
                            case '1.25':
                                $oddsInfo['type_name'] = '受让一球/球半(1/1.5)';
                                break;
                            case '-1.25':
                                $oddsInfo['type_name'] = '一球/球半(-1/1.5)';
                                break;
                            case '1.5':
                                $oddsInfo['type_name'] = '受让球半(1.5)';
                                break;
                            case '-1.5':
                                $oddsInfo['type_name'] = '球半(-1.5)';
                                break;
                            case '1.75':
                                $oddsInfo['type_name'] = '受让球半/两球(1.5/2)';
                                break;
                            case '-1.75':
                                $oddsInfo['type_name'] = '球半/两球(-1.5/2)';
                                break;
                            case '2':
                                $oddsInfo['type_name'] = '受让两球(2)';
                                break;
                            case '-2':
                                $oddsInfo['type_name'] = '两球(-2)';
                                break;
                            case '2.25':
                                $oddsInfo['type_name'] = '受让两球/两球半(2/2.5)';
                                break;
                            case '-2.25':
                                $oddsInfo['type_name'] = '两球/两球半(-2/2.5)';
                                break;
                            case '2.5':
                                $oddsInfo['type_name'] = '受让两球半(2.5)';
                                break;
                            case '-2.5':
                                $oddsInfo['type_name'] = '两球半(-2.5)';
                                break;
                            case '2.75':
                                $oddsInfo['type_name'] = '受让两球半/三球(2.5/3)';
                                break;
                            case '-2.75':
                                $oddsInfo['type_name'] = '两球半/三球(-2.5/3)';
                                break;
                            case '3':
                                $oddsInfo['type_name'] = '受让三球(3)';
                                break;
                            case '-3':
                                $oddsInfo['type_name'] = '三球(-3)';
                                break;
                            case '3.25':
                                $oddsInfo['type_name'] = '受让三球/三球半(3/3.5)';
                                break;
                            case '-3.25':
                                $oddsInfo['type_name'] = '三球/三球半(-3/3.5)';
                                break;
                            case '3.5':
                                $oddsInfo['type_name'] = '受让三球半(3.5)';
                                break;
                            case '-3.5':
                                $oddsInfo['type_name'] = '三球半(-3.5)';
                                break;
                            case '3.75':
                                $oddsInfo['type_name'] = '受让三球半/四球(3.5/4)';
                                break;
                            case '-3.75':
                                $oddsInfo['type_name'] = '三球半/四球(-3.5/4)';
                                break;
                            case '4':
                                $oddsInfo['type_name'] = '受让四球(4)';
                                break;
                            case '-4':
                                $oddsInfo['type_name'] = '四球(-4)';
                                break;
                            case '4.25':
                                $oddsInfo['type_name'] = '受让四球/四球半(4/4.5)';
                                break;
                            case '-4.25':
                                $oddsInfo['type_name'] = '四球/四球半(-4/4.5)';
                                break;
                            case '4.5':
                                $oddsInfo['type_name'] = '受让四球半(4.5)';
                                break;
                            case '-4.5':
                                $oddsInfo['type_name'] = '四球半(-4.5)';
                                break;
                            case '4.75':
                                $oddsInfo['type_name'] = '受让四球半/五球(4.5/5)';
                                break;
                            case '-4.75':
                                $oddsInfo['type_name'] = '四球半/五球(-4.5/5)';
                                break;
                            case '5':
                                $oddsInfo['type_name'] = '受让五球(5)';
                                break;
                            case '-5':
                                $oddsInfo['type_name'] = '五球(-5)';
                                break;
                            case '5.25':
                                $oddsInfo['type_name'] = '受让五球/五球半(5/5.5)';
                                break;
                            case '-5.25':
                                $oddsInfo['type_name'] = '五球/五球半(-5/5.5)';
                                break;
                            case '5.5':
                                $oddsInfo['type_name'] = '受让五球半(5.5)';
                                break;
                            case '-5.5':
                                $oddsInfo['type_name'] = '五球半(-5.5)';
                                break;
                            case '5.75':
                                $oddsInfo['type_name'] = '受让五球半/六球(5.5/6)';
                                break;
                            case '-5.75':
                                $oddsInfo['type_name'] = '五球半/六球(-5.5/6)';
                                break;
                            case '6':
                                $oddsInfo['type_name'] = '受让六球(6)';
                                break;
                            case '-6':
                                $oddsInfo['type_name'] = '六球(-6)';
                                break;
                            case '6.25':
                                $oddsInfo['type_name'] = '受让六球/六球半(6/6.5)';
                                break;
                            case '-6.25':
                                $oddsInfo['type_name'] = '六球/六球半(-6/6.5)';
                                break;
                            case '6.5':
                                $oddsInfo['type_name'] = '受让六球半(6.5)';
                                break;
                            case '-6.5':
                                $oddsInfo['type_name'] = '六球半(-6.5)';
                                break;
                            case '6.75':
                                $oddsInfo['type_name'] = '受让六球半/七球(6.5/7)';
                                break;
                            case '-6.75':
                                $oddsInfo['type_name'] = '六球半/七球(-6.5/7)';
                                break;
                            case '7':
                                $oddsInfo['type_name'] = '受让七球(7)';
                                break;
                            case '-7':
                                $oddsInfo['type_name'] = '七球(-7)';
                                break;
                            case '7.25':
                                $oddsInfo['type_name'] = '受让七球/七球半(7/7.5)';
                                break;
                            case '-7.25':
                                $oddsInfo['type_name'] = '七球半(-7.5)';
                                break;
                            case '7.5':
                                $oddsInfo['type_name'] = '受让七球半/八球(7.5/8)';
                                break;
                            case '-7.75':
                                $oddsInfo['type_name'] = '七球半/八球(-7.5/8)';
                                break;
                            case '8':
                                $oddsInfo['type_name'] = '受让八球(8)';
                                break;
                            case '-8':
                                $oddsInfo['type_name'] = '八球(-8)';
                                break;
                            case '8.25':
                                $oddsInfo['type_name'] = '受让八球/八球半(8/8.5)';
                                break;
                            case '-8.25':
                                $oddsInfo['type_name'] = '八球/八球半(-8/8.5)';
                                break;
                            case '8.5':
                                $oddsInfo['type_name'] = '受让八球半(8.5)';
                                break;
                            case '-8.5':
                                $oddsInfo['type_name'] = '八球半(-8.5)';
                                break;
                            case '8.75':
                                $oddsInfo['type_name'] = '受让八球半/九球(8.5/9)';
                                break;
                            case '-8.75':
                                $oddsInfo['type_name'] = '八球半/九球(-8.5/9)';
                                break;
                            case '9':
                                $oddsInfo['type_name'] = '受让九球(9)';
                                break;
                            case -'9':
                                $oddsInfo['type_name'] = '九球(-9)';
                                break;
                            case '9.25':
                                $oddsInfo['type_name'] = '受让九球/九球半(9/9.5)';
                                break;
                            case '-9.25':
                                $oddsInfo['type_name'] = '九球/九球半(-9/9.5)';
                                break;
                            case '9.5':
                                $oddsInfo['type_name'] = '受让九球半(9.5)';
                                break;
                            case '-9.5':
                                $oddsInfo['type_name'] = '九球半(-9.5)';
                                break;
                            case '9.75':
                                $oddsInfo['type_name'] = '受让九球半/十球(9.5/10)';
                                break;
                            case '-9.75':
                                $oddsInfo['type_name'] = '九球半/十球(-9.5/10)';
                                break;
                            case '10':
                                $oddsInfo['type_name'] = '受让十球(10)';
                                break;
                            case '-10':
                                $oddsInfo['type_name'] = '十球(-10)';
                                break;
                            case '10.25':
                                $oddsInfo['type_name'] = '受让十球/十球半(10/10.5)';
                                break;
                            case '-10.25':
                                $oddsInfo['type_name'] = '十球/十球半(-10/10.5)';
                                break;
                            case '10.5':
                                $oddsInfo['type_name'] = '受让十球半(10.5)';
                                break;
                            case '-10.5':
                                $oddsInfo['type_name'] = '十球半(-10.5)';
                                break;
                            case '10.75':
                                $oddsInfo['type_name'] = '受让十球半/十一球(10.5/11)';
                                break;
                            case '-10.75':
                                $oddsInfo['type_name'] = '十球半/十一球(-10.5/11)';
                                break;
                            case '11':
                                $oddsInfo['type_name'] = '受让十一球(11)';
                                break;
                            case '-11':
                                $oddsInfo['type_name'] = '十一球(-11)';
                                break;
                        }

                        switch ($value['name']) {
                            case '胜负(让球)':
                            case '大小(亚盘)':
                            case '单双(亚盘)':
                               // if ($oddsConfig == 0) {
                                    $oddsInfo['home'] = $oddsInfo['home'] + 1;
                              //  }
                                //if ($oddsConfig == 0) {
                                    $oddsInfo['guest'] = $oddsInfo['guest'] + 1;
                               // }
                                break;
                        }
                        $value['odds'][] = $oddsInfo;
                    }
//                     $oddsInfo['min_bet'] =$info['min_bet'] ? $info['min_bet'] : 0;
//                     $oddsInfo['max_bet'] =$info['max_bet'] ? $info['max_bet'] : 0;
                }
            }
        }
        unset($value);

        Cache::hSet('play_cache_list', $playId . '_' . $oauth->getUserIdByToken(input('token')), json_encode($data));
        return $this->retSucc("play.odds", $data);
    }
}
