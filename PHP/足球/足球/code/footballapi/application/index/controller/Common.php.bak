<?php

namespace app\index\controller;

use app\library\logic\Safe;
use library\service\Arena;
use library\service\Games;
use library\service\Layout;
use library\service\Match;
use library\service\Misc;
use library\service\Odds;
use library\service\Play;
use library\service\Rule;
use library\service\Sms;
use library\service\Task;
use library\service\User;
use org\Stringnew;
use think\Cache;
use think\captcha\Captcha;
use think\Db;
use think\Debug;
use think\Exception;

class Common extends Safe
{

    /**
     * 获取项目玩法
     */
    public function rules()
    {
        $item_id = input("item_id/d"); //可以玩目ID
        $game_id = input("game_id/d"); //游戏ID
        if (!$item_id) {
            return $this->retErr('common.rules', 10000);
        }
        //$rules = (new Rule())->factory($item_id)->rulesListAll(true);


        $rules = getRuleData($item_id, null, null, true, true, $game_id);
        if ($rules) {
            foreach ($rules as $key => $val) {
                $val['game_id'] = intval($val['game_id']);
                unset($val['is_delete']);
                unset($val['status']);
                unset($val['explain']);
                $rules[$key] = $val;
            }
        }
        return $this->retSucc('common.rules', array_values($rules));
    }

    /**
     * 获取游戏
     */
    public function games()
    {
        $item_id = input("item_id/d"); //可以玩目ID
        if (!$item_id) {
            return $this->retErr('common.game', 10000);
        }
        $game = getSportGames($item_id, true);
        $indexRec = config("index_rec");
        if (isset($indexRec[GAME_TYPE_WCG])) {
            $indexRec = $indexRec[GAME_TYPE_WCG];
        } else {
            $indexRec = [];
        }
        if ($game) {
            foreach ($game as $key => $val) {
                if ($indexRec && in_array($val['id'], array_values($indexRec['game']))) {
                    unset($game[$key]);
                    continue;
                }
                unset($val['create_time']);
                unset($val['status']);
                unset($val['update_time']);
                unset($val['game_type']);
                $val['icon'] = get_image_thumb_url($val['icon']);
                $game[$key] = $val;
            }
        }
        return $this->retSucc('common.game', array_values($game));
    }

    /**
     * 获取项目赛事
     */
    public function matchs()
    {
        $where = [];
        $where['p.status'] = array('NOT IN',[PLAT_STATUS_CUT,PLAT_STATUS_STATEMENT_BEGIN,PLAT_STATUS_STATEMENT]);
        $where['m.game_type'] = 1;
        $where['m.is_show'] = 1;
        $where["p.play_time"] = ["gt",time()-10800];
        $where["p.id"] = ["gt",0];
        $where["o.odds_type"] = 1;
        $where['p.has_odds'] = 1;
        $lists = Db::name('match')->alias("m")
            ->field("m.id,  m.country_id,  m.name,  m.alias,  m.logo,  m.logo_hover,  m.begin_time,  m.end_time,  m.address")
            ->join("__PLAY__ p","p.match_id = m.id",'LEFT')
            ->join("__ODDS__ o","o.play_id=p.id",'LEFT')
            ->where($where)
            ->group("m.id")
            ->order("m.is_hot DESC,p.play_time asc")
            ->select();
       // echo Db::name('match')->getLastSql();
        if ($lists) {
            foreach ($lists as $key => $val) {
                $val['logo'] = $val['logo'];
//                $val['logo_hover'] = get_image_thumb_url($val['logo_hover']);
                $lists[$key] = $val;
            }
        }
        return $this->retSucc('common.match', array_values($lists), '');
        //return ;
//        $item_id = input("item_id/d"); //可以玩目ID
//        if (!$item_id) {
//            return $this->retErr('common.match', 10000);
//        }
//        $game_id = input('game_id/d');
//        $page = max(1, input("page/d", 0, 'intval'));
//        $limit = 20;
//        $offset = ($page - 1) * $limit;
      //  $where = [];
//        if ($game_id) {
//            $where['game_id'] = $game_id;
//        }
       // $where['game_type'] = 1;
       // $where['is_show'] = 1;


//        $is_hot = input("is_hot/d"); //是否热门
//        if ($is_hot)
//            $where['is_hot'] = $is_hot;
//
//        $is_recommend = input("is_recommend/d"); //是否推荐
//        if ($is_recommend)
//            $where['is_recommend'] = $is_recommend;

//        $where["p.play_time"] = ["gt",time()];
//        $where["p.id"] = ["gt",0];
       // $where ['m.country_id']=1;
//        $sql = "select m.id,m.country_id,m.name,m.alias,m.logo,m.logo_hover,m.begin_time,m.end_time,m.address
//from lt_match
//left join lt_play p ON p.match_id = m.id left join lt_odds o ON p.match_id=m.id
//";
//        echo $sql;exit;
//        $lists = Db::name('match')->alias("m")
//            ->join("__PLAY__ p","p.match_id = m.id",'LEFT')
//            ->join("__ODDS__ O","O.play_id = p.id",'LEFT')
//            ->field("m.id,m.country_id,m.name,m.alias,m.logo,m.logo_hover,m.begin_time,m.end_time,m.address")
//            ->where($where)
//            ->group("m.id")
//           ->select();
       // $lists = Db::name('match')->where($where)->field('')->select();
        //$sql = "SELECT  m.id,  m.country_id,  m.name,  m.alias,  m.logo,  m.logo_hover,  m.begin_time,  m.end_time,  m.address FROM lt_match m LEFT JOIN lt_play p ON p.match_id = m.id LEFT JOIN lt_odds o ON o.play_id = m.id WHERE m.game_type=1 AND m.is_show=1 AND o.odds_type = 1 GROUP BY m.id ";
       // $lists = Db::query($sql);
       // if ($lists) {
       //     foreach ($lists as $key => $val) {
          //      $val['logo'] = $val['logo'];
//      //          $val['logo_hover'] = get_image_thumb_url($val['logo_hover']);
        //        $lists[$key] = $val;
         //   }
       // }

        //$total = Db::name('match')->where($where)->count();
        //$total_pages = ceil($total / $limit);

        //return $this->retSucc('common.match', array_values($lists), '');
        //return $this->result($lists,0,'','',[],['total_pages' => $total_pages]);
    }

    /**
     * 获取有比赛的赛事
     */
    public function match_play()
    {
        $item_id = input("item_id/d"); //可以玩目ID
        if (!$item_id) {
            return $this->retErr('common.match', 10000);
        }
        $game_id = input('game_id/d', 0, 'intval');
        $matchList = (new Match())->getMatchByPlay($item_id, $game_id);
        return $this->retSucc('common.match_play', $matchList);
    }

    /**
     * 玩法盘口列表
     */
    public function handicap()
    {
        $itemId = input("item_id/d", GAME_TYPE_FOOTBALL);
        $ruleId = input("rule_id/d", RULES_TYPE_ASIAN);
        $handicapList = config("handicap");
        $data = [];
        foreach ($handicapList as $key => $val) {
            if (!$val[1]) {
                $data["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "{$val[0]}($val[1])"];
            } else {
                $data["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "受让{$val[0]}($val[1])"];
                if (stripos($val[1], "/") !== false) {
                    $tmp = explode("/", $val[1]);
                    $data["-{$key}"] = ['value' => -$key, 'cnt' => "让 -{$tmp[0]}/-{$tmp[1]}", 'text' => "{$val[0]}(-$val[1])"];
                } else {
                    $data["-{$key}"] = ['value' => -$key, 'cnt' => "让 -{$val[1]}", 'text' => "{$val[0]}(-$val[1])"];
                }
            }
        }

        return $this->retSucc('common.handicap', array_values($data));
    }

    /**
     * 投注筹码额度
     */
    public function chips()
    {
        $chips = config("system.sys_chip");
        if ($chips) {
            $chips = @json_decode($chips, true);
        } else {
            $chips = [300, 400, 1000, 2000, 4000];
        }
        return $this->retSucc('common.chips', $chips);
    }

    /**
     * 公告
     */
    public function notice()
    {
        $data = Db::name('notice')->limit(5)->order('update_time DESC,create_time') -> select();
        return $this->retSucc('common.notice', $data);
    }

    /**
     * 财富榜
     *
     * 指最近3个月赢得的奖金总额
     * 财富榜排列前10名
     */
    public function win_top()
    {
        $top = Db::name('top_bonus')->where(['type' => TOP_BONUS_THREE_MONTH, 'total' => ['gt', 0]])->order("total desc")->limit(10)->select();
        $data = [];
        foreach ($top as $val) {
            $user = getUser($val['user_id']);
            if (!$user) {
                continue;
            }
            $data[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'total' => $val['total'],
                'avatar' => $user['avatar'],
            ];
        }
        return $this->retSucc('common.win_top', $data);
    }

    /**
     * 大神,盈利率
     */
    public function god_top()
    {
        $type = input("type/d");
        if (!$type) {
            $type = 3;
        }
        $top = Db::name('top_leitai_win')->field('user_id,total')->where(['type' => $type])->order("id asc")->limit(15)->select();
        $data = [];
        foreach ($top as $val) {
            $user = getUser($val['user_id']);
            $data[] = [
                'id' => $val['user_id'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'total' => $val['total'],
                'level' => $user['level'],
            ];
        }

        return $this->retSucc('common.god_top', $data);
    }

    /**
     * 开奖
     */
    public function award()
    {
        $page = max(1, input("page/d", 0));
        $time = mktime(0, 0, 0, null, date("d") - 30); //近30天数据
        $limit = 30;

        $offset = ($page - 1) * $limit;
        $where = [
            'status' => ['in', [PLAT_STATUS_STATEMENT, PLAT_STATUS_START, PLAT_STATUS_END, PLAT_STATUS_CUT, PLAT_STATUS_INTERMISSION]],
            'arena_total' => ['gt', 0]
            //'statement_time' => ['gt',$time]
        ];
        $list = Db::name('play')->where($where)->order('play_time desc,statement_time desc')->limit($offset, $limit)->select();
        $total_page = 0;
        if (count($list) >= $limit) {
            $total = Db::name('play')->where($where)->count();
            $total_page = ceil($total / $limit);
        }
        $result = [];
        $playSvr = new Play();
        foreach ($list as $val) {
            $result[] = [
                'id' => $val['id'],
                'play_time' => $val['play_time'],
                'status' => $val['status'],
                'statement_time' => $val['statement_time'],
                'item_id' => $val['game_type'],
                'item_name' => getSport($val['game_type']),
                'game_id' => $val['game_id'] ? $val['game_id'] : '',
                'game_name' => $val['game_id'] ? getGame($val['game_id'], 'name') : '',
                'match_time' => $val['match_time'],
                'teams' => $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'logo_big', 'has_home', 'score'], $this->getUserId('sys')),
                'match' => getMatch($val['match_id'], null, ['id', 'name', 'logo', 'bgcolor'])
            ];
        }
        return $this->retSucc('common.award', $result, '', [
            'next_page' => $total_page > $page ? 1 : 0,
            'total_page' => $total_page
        ]);
    }

    /**
     * 热门比赛
     */
    public function play_hot()
    {
        //Debug::remark("begin");
        $page = max(1, input("page/d", 0));
        $itemId = input("item_id/d");
        $isRecommend = input("is_recommend/d");
        $item_value = input("item_value"); //项目标识
        $limit = 20;
        $diffLimit = 0;
        $offset = ($page - 1) * $limit;

        $oddsCompany = cache('odds_company');
        $Ids = [];
        foreach ($oddsCompany as $val) {
            $Ids[] = $val['id'];
        }


        $order = "is_recommend DESC,has_sys_arena DESC,play_time ASC,arena_total desc";
        $where = [];
        $where['status'] = ['in', [PLAT_STATUS_NOT_START, PLAT_STATUS_INTERMISSION, PLAT_STATUS_START]]; //PLAT_STATUS_WAIT
        //$where['has_sys_arena'] = 1; //只显示系统摆擂的比赛

        $where['play_time'] = ['gt', time() - 18000]; // - 86400
        if ($itemId) {
            $where['game_type'] = $itemId;
        }
        if ($isRecommend) {
            $where['is_recommend'] = 1;
            $where['game_type'] = ['in', [GAME_TYPE_FOOTBALL, GAME_TYPE_WCG]];
            $order = "game_type asc,has_sys_arena DESC,play_time ASC,arena_total desc";
        }
        $where['has_odds'] = 1; //有赔率的比赛

        if ($item_value) {
            $item_value = explode("_", $item_value);
            if ($item_value && count($item_value) > 1) {
                if ($item_value[0] == 'game') {
                    $where['game_id'] = intval($item_value[1]);
                }
                unset($where['has_odds']);
            }
        }
        $lists = Db::name('play')->limit($offset, $limit)->field('id,play_time,status,match_id,arena_total,total_prize,is_recommend,game_type as item_id,match_time,game_id,has_play_rules,live_type,live,has_sys_arena,bo')->where($where)->order($order)->select();
        //echo Db::name('play')->getLastSql();
        $oddsSvr = new Odds();
        $playSvr = (new Play());
        $matchs = [];
        $rules_type = '';
        $data = [];
        $data[GAME_TYPE_FOOTBALL] = [];
        $data[GAME_TYPE_WCG] = [];
        $data[GAME_TYPE_BASKETBALL] = [];
        $ruleSvrData = [];
        foreach ($lists as $key => $val) {
            $itemId = $val['item_id'];

            if (!isset($ruleSvrData[$itemId])) {
                $ruleSvrData[$itemId] = (new Rule())->factory($itemId);
            }
            $ruleSvr = $ruleSvrData[$itemId];
            $rules_type = RULES_TYPE_ASIAN;
            //$ruleSvr = (new Rule())->factory($val['item_id']);
            $teams = $playSvr->getTeams($val['id'], ['id', 'name', 'logo', 'log_big', 'score', 'has_home'], $this->getUserId('sys'));
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
                //$getPlayRules = $playSvr->getPlayRules($val['id']);
            }
            $arenaId = 0;
            /* foreach($getPlayRules as $r){
              if($r['arena_id']){
              $arenaId = $r['arena_id'];
              break;
              }
              }
              if(!$arenaId){
              $arena = (new Arena())->getTopArena($val['id']);
              $arenaId = $arena['id'];
              } */
            $val['item_name'] = getSport($itemId);
            $val['item_value'] = getItemValue($val['item_id'], [
                ['type' => 'match', 'value' => $val['match_id']],
                ['type' => 'game', 'value' => $val['game_id']],
            ]);
            $val['arena_id'] = intval($arenaId);
            $val['match_name'] = $itemId == GAME_TYPE_WCG ? getGame($val['game_id'], 'name') : $match['name'];
            $val['teams'] = $teams;
            $oddsData = [];
            /* if($Ids){
              $ruleIds = $ruleSvr->getRuleTypeChild($rules_type);
              $ruleIds = array_keys($ruleIds);
              $oddsData = $oddsSvr->getPlayRecommendOdds($val['id'],$ruleIds,$Ids);
              } */
            $odds = [];
            if ($oddsData) {
                $odds = @json_decode($oddsData['odds'], true);
                $odds = isset($odds['time']) ? $odds['time'] : $odds['init'];
                $odds = $ruleSvr->parseOddsWords($odds, $oddsData['rules_id'], $itemId == GAME_TYPE_FOOTBALL ? [] : $teams);
            }
            $val['odds'] = array_values($odds);
            $val['live'] = $playSvr->getPlayLive($val['id'], $val['live']);
            if ($val['status'] == PLAT_STATUS_START) {
                $val['match_time'] = getMatchRunTime($val['match_time'], $val['play_time']);
            }
            unset($val['has_play_rules']);
            unset($val['has_sys_arena']);
            $data["{$itemId}"][] = $val;
        }

        $total = Db::name('play')->where($where)->count();
        $total_page = ceil($total / $limit);
        // Debug::remark("end");
        //echo Debug::getRangeMem('begin','end');
        //echo Debug::getRangeTime('begin','end');
        return $this->retSucc('common.play_hot', $data, '', [
            'next_page' => $total_page > $page ? 1 : 0,
            'total_page' => $total_page,
            'page_size' => $limit
        ]);
    }

    /**
     * 竞技模块
     */
    public function sport_layout()
    {
        $itemId = input('item_id/d', 0);
        $lists = (new Layout())->getSportLayout();
        $data = [];
        if ($lists) {
            foreach ($lists as $val) {
                if ($val['item_id'] == $itemId) {
                    $ids = [];
                    if ($val['detail']) {
                        foreach ($val['detail'] as $detail) {
                            $ids[] = $detail['id'];
                        }
                    }
                    $data[] = [
                        'item_id' => $val['item_id'],
                        'name' => $val['name'],
                        'type' => $val['type'],
                        'is_hot' => $val['is_hot'],
                        'ids' => implode(",", array_values($ids))
                    ];
                }
            }
        }
        return $this->retSucc('common.sport_layout', $data);
    }

    /**
     * 消息列表
     */
    public function msglist()
    {
        $last_id = input("last_id/d", 0);
        if (!$last_id)
            return $this->retErr('common.msglist', 10000);
        $where = array("id" => $last_id, "type" => 2);
        $data = Db::name('help')->where($where)->select();
        return $this->retSucc('common.msglist', $data);
    }

    /**
     * 帮助列表
     */
    public function helplist_type()
    {
        $data = Db::name('help_type')->select();
        return $this->retSucc('common.helplist_type', $data);
    }

    /**
     * 帮助列表
     */
    public function helplist()
    {
        $type_id = input("type_id/d", 0);
        if (!$type_id)
            return $this->retErr('common.msglist', 10000);

        $data = Db::name('help')->where("type_id", $type_id)->select();
        foreach($data as $key => &$value){
            $value['content'] = html_entity_decode($value['content']);
        }
        unset($value);
        return $this->retSucc('common.helplist', $data);

    }

    /**
     * 心跳
     */
    public function heartbeat()
    {
        $uid = $this->getUserId();
        $user = Db::name('user')->field('gold,has_online')->where('id', $uid)->find();
        $msg = Db::name('help')->where(['type' => 2])->order('add_time desc')->find();
        $data = array("msg_id" => $msg['id'], "money" => $user['gold'], "has_online" => $user['has_online']);
        return $this->retSucc('common.heartbeat', $data);
    }

}