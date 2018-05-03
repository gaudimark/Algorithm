<?php

/**
 * 项目操作父类
 */

namespace app\admin\logic;

use library\service\Arena;
use library\service\Log;
use library\service\Misc;
use library\service\Odds;
use library\service\Play;
use library\service\Rule;
use library\service\Statement;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Items extends Basic {

    public $itemId = 0; //项目ID
    public $playSvr = null;

    public function __construct($itemId) {
        parent::__construct();
        $this->itemId = $itemId;
        $this->assign("item_id", $this->itemId);
        $this->playSvr = new Play();
    }

    /**
     * 比赛列表
     * @return mixed
     */
    public function play() {
        $type = input("type", 1, 'intval');
        $match = input("match");
        $item = input("item");
        $opt_value = input("opt_value", '');
        $game_id = input("game_id/d");
        $has_odds = intval(input("has_odds/d",1));
        $btime = input("btime");
        $etime = input("etime");
        $export = input("export");
        $status = input("status", 0, 'intval');
        $where = [];
        $query = [];

        if ($export && !$status) {
            return $this->error("导出excel不支持比赛状态为全部");
        }


        $where['p.game_type'] = $this->itemId;
        if ($match) {
            $where['m.name'] = ['like', "%{$match}%"];
            $query['match'] = $match;
        }
        if ($item) {
            $where['p.team_home_name|p.team_guest_name'] = ['like', "%{$item}%"];
            $query['item'] = $item;
        }
        if ($game_id) {
            $where['p.game_id'] = $game_id;
            $query['game_id'] = $game_id;
        }
        if ($btime && $etime) {
            $where['p.play_time'] = [['egt', strtotime($btime)], ['elt', strtotime($etime)]];
            $query['btime'] = $btime;
            $query['etime'] = $etime;
        } elseif ($btime) {
            $where['p.play_time'] = ['egt', strtotime($btime)];
            $query['etime'] = $etime;
        } elseif ($etime) {
            $where['p.play_time'] = ['elt', strtotime($etime)];
            $query['etime'] = $etime;
        }
        if ($opt_value) {
            $query['opt_value'] = $opt_value;
            if ($opt_value == 'today') {
                $btime = mktime(0, 0, 0);
                $etime = mktime(23, 59, 59);
                $where['p.play_time'] = [['egt', ($btime)], ['elt', ($etime)]];
            } elseif ($opt_value == 'tomorrow') {
                $btime = mktime(0, 0, 0, date("m"), date("d") + 1);
                $etime = mktime(23, 59, 59, date("m"), date("d") + 1);
                $where['p.play_time'] = [['egt', ($btime)], ['elt', ($etime)]];
            } elseif ($opt_value == 'month') {
                $btime = mktime(0, 0, 0, date("m"), 1);
                $etime = mktime(0, 0, 0, date("m") + 1, 1) - 1;
                $where['p.play_time'] = [['egt', ($btime)], ['elt', ($etime)]];
            }
            /* if($btime){
              $btime = date("Y-m-d H:i:s",$btime);
              $query['btime'] = $btime;
              }
              if($etime){
              $etime = date("Y-m-d H:i:s",$etime);
              $query['etime'] = $etime;
              } */
        }



        if ($has_odds) {
            $where['p.has_odds'] = $has_odds == 1 ? 1 : 0;
            $query['has_odds'] = $has_odds;
        }
        if ($type) {
            $query["type"] = $type;
            if ($type == 5) {
                $matchHotIds = Cache("match_hot");
                if ($matchHotIds) {
                    $ids = implode(",", array_unique(array_values($matchHotIds)));
                    if (!$ids) {
                        $ids = 0;
                    }
                    $where['m.id'] = ['in', $ids];
                }
            }
        }
        if ($status) {
            if ($status == PLAT_STATUS_START)
                $where["p.status"] = ["in", [PLAT_STATUS_START, PLAT_STATUS_INTERMISSION]];
            //elseif ($status == PLAT_STATUS_END)
            //$where["p.status"] = PLAT_STATUS_END;//["in",[PLAT_STATUS_END,PLAT_STATUS_STATEMENT]];
            else
                $where["p.status"] = $status;
            $query["status"] = $status;
        }
        $model = model("play");
        $limit = 50;
        if ($export) { //如果是导出，最大值为65535
            $limit = 10000;
        }
        switch ($type) {
            case 1:
                $list = $model->getPlayWithMatchList($where, $limit, $query, 'p.play_time DESC');
                break;
            case 2:
                $list = $model->getPlayWithArenaList($where, $limit, $query, 'p.play_time DESC');
                break;
            case 3:
                $list = $model->getPlayRecommend($where, $limit, $query, 'p.play_time DESC');
                break;
            case 4:
                $list = $model->getPlayEnd($where, $limit, $query, 'p.play_time DESC');
                break;
            default:
                $list = $model->getPlayWithMatchList($where, $limit, $query, 'p.play_time DESC');
                break;
        }

        if ($export) {
            $title = array(
                'id' => 'ID',
                'date' => '比赛日期',
                'match' => '赛事',
                'home' => '主场',
                'guest' => '客场',
                'game' => '游戏',
            );
            $data = [];
            foreach ($list as $key => $val) {
                $game = getGame($val['game_id'], 'name');
                $data[] = [
                    'id' => $val['id'],
                    'date' => date("Y-m-d H:i", $val['play_time']),
                    'match' => $val['match_name'],
                    'home' => $val['team_home_name'],
                    'guest' => $val['team_guest_name'],
                    'game' => $game ? $game : '--',
                ];
            }
            return (new Misc())->toXls('比赛', $title, $data);
        } else {
            foreach ($list as $key => $val) {
                //计算比赛进行的时间
                if ($val["status"] == PLAT_STATUS_START) {
                    $list[$key]["match_time"] = getMatchRunTime($val["match_time"], $val["play_time"]);
                }
            }
        }



        //获取项目下的游戏
        $games = getSportGames($this->itemId);
        $this->assign($query);
        $this->assign("type", $type);
        $this->assign("list", $list);
        $this->assign("has_odds", $has_odds);
        $this->assign("status", $status);
        $this->assign("games", $games);
        $this->assign("opt_value", $opt_value);
        return $this->fetch('items/play');
    }

    /**
     * 比赛设置
     */
    public function play_conf() {

        if ($this->request->isPost()) {
            $playId = input("post.play_id/d");
            if (!$playId) {
                return $this->error('保存比赛数据失败');
            }
            $play = Db::name('play')->where(['id' => $playId])->find();
            if (in_array($play['status'], [PLAT_STATUS_STATEMENT, PLAT_STATUS_STATEMENT_BEGIN])) {
                return $this->error('比赛已结算，无法修改比赛数据');
            }

            $data = input();
            if (in_array($data['status'], [PLAT_STATUS_SUSP, PLAT_STATUS_WAIT, PLAT_STATUS_CUT])) {
                $data['status'] = PLAT_STATUS_END;
            }

            if ($data['status'] == PLAT_STATUS_END && !$play['end_time']) { //比赛结束时，无法比赛结束时间，则直接赋当前时间
                $data['end_time'] = time();
            }

            if (true === $result = $this->playSvr->factory($this->itemId)->upConf($playId, $data, $this->admin_id)) {
                return $this->success("保存成功");
            } else {
                return $this->error($result);
            }
        }
        $play_id = input('play_id', 0, 'intval');
        $play = $this->playSvr->getPlay($play_id);
        if (!$play) {
            return $this->error("无效比赛信息");
        }
        $playRuleList = [];
        $rulesTypeList = [];
        $playResult = [];
        $playRuleList = getPlayRule($play_id);
        //dump($playRuleList);exit;
        $rulesTypeList = getRuleData($this->itemId);
        $temp = [];
        if (isset($play['game_id']) && $play['game_id']) {
            foreach ($rulesTypeList as $val) {
                if ($val['game_id'] == $play['game_id']) {
                    $val['explain'] = @json_decode($val['explain']);
                    $temp[$val['id']] = $val;
                }
            }
            $rulesTypeList = $temp;
        }


        $playResult = Db::name('play_result')->where(['play_id' => $play_id])->find();
        if ($playResult) {
            $playResult['result'] = @json_decode($playResult['result'], true);
        }

        $team_home_header = 0;
        $team_guest_header = 0;
        $fb = -1;
        $home_win = "";
        $guest_win = "";
        $playTeam = Db::name('play_team')->where(['play_id' => $play_id])->select();
        if ($playTeam) {
            foreach ($playTeam as $pt) {
                $score_json = json_decode($pt["score_json"], true);
                $fb = isset($score_json["fb"]) ? $score_json["fb"] : -1;
                if ($pt["has_home"] == 1) {
                    $team_home_header = isset($score_json["header"]) ? $score_json["header"] : 0;
                } elseif ($pt["has_home"] == 0) {
                    $team_guest_header = isset($score_json["header"]) ? $score_json["header"] : 0;
                }
            }
        }

        $teams = $this->playSvr->getTeams($play_id);
        $this->assign("rulesTypeList", $rulesTypeList);
        $this->assign("playRuleList", $playRuleList);
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("teams", $teams);
        $this->assign("playResult", $playResult);
        $this->assign("match", getMatch($play["match_id"]));
        $this->assign("team_home_header", $team_home_header);
        $this->assign("team_guest_header", $team_guest_header);
        $this->assign("fb", $fb);
        $this->assign("home_win", $home_win);
        $this->assign("guest_win", $guest_win);
        return $this->fetch('items/play_conf');
    }

    /**
     * 比赛预测
     * */
    public function play_dope() {
        if ($this->request->isPost()) {
            $playId = input("post.play_id/d");
            $id = input("post.id/d");
            $content = trim(input("post.content", ''));
            //替换域名
            $content = str_replace(config("site_source_domain"), "__RES_DOMAIN__", $content);
            if (!$playId) {
                return $this->error('保存比赛数据失败');
            }
            if (!$content) {
                return $this->error('请输入内容！');
            }
            $play = Db::name('play')->where(['id' => $playId])->find();
            if (in_array($play['status'], [PLAT_STATUS_STATEMENT, PLAT_STATUS_STATEMENT_BEGIN])) {
                return $this->error('比赛已结算，无法修改预测');
            }
            $data = array();
            $data["content"] = htmlspecialchars_decode($content);
            $data["update_time"] = time();
            if ($id) {
                if (Db::name('play_dope')->where(['id' => $id])->update($data)) {
                    return $this->success("修改成功");
                } else {
                    return $this->error("修改失败");
                }
            } else {
                $data["play_id"] = $playId;
                $data["create_time"] = time();
                if (Db::name('play_dope')->insert($data)) {
                    return $this->success("添加成功");
                } else {
                    return $this->error("添加失败");
                }
            }
        }
        $play_id = input('play_id', 0, 'intval');
        if (!$play_id) {
            return $this->error('无效比赛数据');
        }
        $dope = array();
        $dope = Db::name('play_dope')->where(['play_id' => $play_id])->find();
        if ($dope) {
            $dope["content"] = str_replace("__RES_DOMAIN__", config("site_source_domain"), $dope["content"]);
        }
        $this->assign("play_id", $play_id);
        $this->assign("dope", $dope);
        return $this->fetch("items/play_dope");
    }

    /**
     * 比赛结果
     */
    public function play_result() {
        $play_id = input('play_id', 0, 'intval');
        $play = $this->playSvr->getPlay($play_id);
        if (!$play) {
            return $this->error("无效比赛信息");
        }
        $playRuleList = getPlayRule($play_id);
        $rulesTypeList = getRuleData($this->itemId);
        $temp = [];
        foreach ($rulesTypeList as $val) {
            if ($val['game_id'] == $play['game_id']) {
                $val['explain'] = @json_decode($val['explain']);
                $temp[$val['id']] = $val;
            }
        }
        $rulesTypeList = $temp;

        $playResult = Db::name('play_result')->where(['play_id' => $play_id])->find();
        if ($playResult) {
            $playResult['result'] = @json_decode($playResult['result'], true);
        }
        $teams = $this->playSvr->getTeams($play_id);
        $this->assign("rulesTypeList", $rulesTypeList);
        $this->assign("playRuleList", $playRuleList);
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("teams", $teams);
        $this->assign("playResult", $playResult);
        $this->assign("match", getMatch($play["match_id"]));
        return $this->fetch('items/play_result');
    }

    /**
     * 比赛直播
     */
    public function play_live() {

        if ($this->request->isPost()) {
            $play_id = input("play_id/d");
            $live = input("live");
            $live_type = input("live_type/d");
            if ($play_id) {
                Db::name('play')->where(['id' => $play_id])->update([
                    'live' => $live,
                    'live_type' => $live_type
                ]);
                (new Play())->upCache($play_id);
                return $this->success("操作成功");
            }
            return $this->success("操作失败");
        }
        $play_id = input("play_id/d");
        $play = (new Play())->getPlay($play_id);
        if (!$play) {
            return $this->error("无效比赛信息");
        }
        $this->assign("play", $play);
        return $this->fetch('items/play_live');
    }

    //批量添加直播地址
    public function batch_play_live() {
        if ($this->request->isPost()) {
            $play_id = trim(input("id"));
            $play_list = explode(",", rtrim($play_id, ","));
            $i = 0;
            $live = input("post.live");
            $live_type = input("post.live_type");
            foreach ($play_list as $id) {
                if ($id) {
                    Db::name('play')->where(['id' => $id])->update([
                        'live' => $live,
                        'live_type' => $live_type
                    ]);
                    (new Play())->upCache($id);
                    $i ++;
                }
            }
            if ($i) {
                return $this->success("操作成功");
            } else {
                return $this->success("操作失败");
            }
        }

        $play_id = trim(input("id"));
        $play_list = explode(",", rtrim($play_id, ","));

        if (!$play_list) {
            return $this->error("无效比赛信息");
        }
        $live = "";
        $live_type = "";
        foreach ($play_list as $id) {
            if ($id) {
                $play = (new Play())->getPlay($id);
                if ($play) {
                    if ($play["live"])
                        $live = $play["live"];
                    if ($play["live_type"])
                        $live_type = $play["live_type"];
                }
            }
        }
        $this->assign("live", $live);
        $this->assign("live_type", $live_type);
        $this->assign("id", $play_id);
        return $this->fetch('items/batch_play_live');
    }

    /**
     * 添加比赛
     */
    public function play_add() {
        $playSvr = new Play();
        if ($this->request->isPost()) {
            $match_id = input("match_id/d");
            $home_id = input("home_id/d");
            $guest_id = input("guest_id/d");
            $play_time = input("play_time");
            if (false !== $playSvr->addPlay($match_id, $home_id, $guest_id, $play_time)) {
                return $this->success("比赛添加成功");
            }
            return $this->error($playSvr->getError());
        }
        $id = input("id/d");
        $res = [];
        if ($id) {
            $res = $playSvr->getPlay($id);
            $res['match'] = getMatch($res['match_id']);
            $res['teams'] = $playSvr->getTeams($id);
            $res['play_time'] = date("Y-m-d H:i:s", $res['play_time']);
        }
        $this->assign("res", $res);
        $this->assign("id", $id);
        return $this->fetch('items/play_add');
    }

    public function play_del() {
        if ($this->request->isPost()) {
            $id = input("id/d");
            $playSvr = new Play();
            if (false !== $playSvr->delPlay($id)) {
                return $this->success("比赛添加成功");
            }
            return $this->error($playSvr->getError());
        }
    }

    /**
     * 可结算的比赛
     * @return mixed
     */
    public function statement() {
        $match = input("match");
        $item = input("item");
        $has_odds = intval(input("has_odds"));
        $play_time = input("play_time");
        $status = input("status", 0, 'intval');
        $where = [];
        $query = [];

        $where['p.game_type'] = $this->itemId;
        if ($match) {
            $where['m.name'] = ['like', "%{$match}%"];
            $query['match'] = $match;
        }
        if ($item) {
            $where['p.team_home_name|p.team_guest_name'] = ['like', "%{$item}%"];
            $query['item'] = $item;
        }
        if ($play_time) {
            $where['p.play_time'] = strtotime($play_time);
            $query['play_time'] = $play_time;
        }
        if ($has_odds) {
            $where['p.has_odds'] = $has_odds == 1 ? 1 : 0;
            $query['has_odds'] = $has_odds;
        }
        $where['status'] = ['neq', PLAT_STATUS_STATEMENT];
        if ($status) {
            if ($status == PLAT_STATUS_START)
                $where["p.status"] = ["in", [PLAT_STATUS_START, PLAT_STATUS_INTERMISSION]];
            elseif ($status == PLAT_STATUS_END)
                $where["p.status"] = ["in", [PLAT_STATUS_END, PLAT_STATUS_STATEMENT]];
            else
                $where["p.status"] = $status;
            $query["status"] = $status;
        }
        $where['arena_total'] = ['gt', 0];
        $model = model("play");
        $list = $model->getPlayEnd($where, 18, $query, 'p.play_time DESC');
        foreach ($list as $key => $val) {
            //计算比赛进行的时间
            if ($val["status"] == PLAT_STATUS_START) {
                if ($val["match_time"] == '' || $val["match_time"] == 0) {
                    $match_time = 0;
                    $long = time() - $val["play_time"];
                    $min = floor($long / 60);
                    if ($min <= 45) {
                        $match_time = $min;
                    } elseif ($min <= 60) {
                        $match_time = "45+";
                    } elseif ($min <= 105) {
                        $match_time = $min - 15;
                    } else {
                        $match_time = "90+";
                    }
                    if ($match_time < 0)
                        $match_time = 0;
                    $list[$key]["match_time"] = $match_time;
                }
            }
        }
        //自动结算开启时间
        $config = config("system.arena_auto_statement");
        $auto_time = isset($config[GAME_TYPE_FOOTBALL]) ? $config[GAME_TYPE_FOOTBALL] * 60 : 0;

        $this->assign($query);
        $this->assign("type", 4);
        $this->assign("list", $list);
        $this->assign("has_odds", $has_odds);
        $this->assign("status", $status);
        $this->assign("auto_time", $auto_time);
        return $this->fetch('items/statement');
    }

    /**
     * 手动结算
     */
    public function statement_manual() {

        if ($this->request->isPost()) {
            set_time_limit(0);
            $play_id = input("post.play_id/d");
            if (!$play_id) {
                return $this->error("无效比赛信息");
            }

            $statementSvr = new Statement();

            if ($statementSvr->play($play_id)) {
                return $this->success("OK", '', ['next' => false]);
            } else {
                return $this->error($statementSvr->getError());
            }
        }
        $play_id = input("play_id/d");
        if (!$play_id) {
            return $this->error("无效比赛信息");
        }
        $play = $this->playSvr->getPlay($play_id);
        $play = $this->playSvr->factory($play['game_type'])->getStatementResult($play);
        $playRuleList = getPlayRule($play_id);
        $rulesTypeList = getRuleData($this->itemId);
        $temp = [];
        foreach ($rulesTypeList as $val) {
            if ($val['game_id'] == $play['game_id']) {
                $val['explain'] = @json_decode($val['explain']);
                $temp[$val['id']] = $val;
            }
        }
        $rulesTypeList = $temp;
        $playResult = [];
        if ($this->itemId != GAME_TYPE_FOOTBALL) {
            $playResult = Db::name('play_result')->where(['play_id' => $play_id])->find();
            if ($playResult) {
                $playResult['result'] = @json_decode($playResult['result'], true);
            }
            $playResult = $playResult ? $playResult : [];
        }
        $teams = $this->playSvr->getTeams($play_id);

        $team_home_header = 0;
        $team_guest_header = 0;
        $fb = -1;
        $home_win = "";
        $guest_win = "";
        $playTeam = Db::name('play_team')->where(['play_id' => $play_id])->select();
        if ($playTeam) {
            foreach ($playTeam as $pt) {
                $score_json = json_decode($pt["score_json"], true);
                $fb = isset($score_json["fb"]) ? $score_json["fb"] : -1;

                if ($pt["has_home"] == 1) {
                    $team_home_header = isset($score_json["header"]) ? $score_json["header"] : 0;
                } elseif ($pt["has_home"] == 0) {
                    $team_guest_header = isset($score_json["header"]) ? $score_json["header"] : 0;
                }
            }
        }
        //$arenaTotal = model("Arena")->where(['play_id' => $play_id,'status'=> ['not in',[ARENA_STATEMENT_END,ARENA_DIS,ARENA_DEL]]])->count();
        //$numberTotal = model("Arena")->where(['play_id' => $play_id,'status'=> ['not in',[ARENA_STATEMENT_END,ARENA_DIS,ARENA_DEL]]])->sum('bet_number');
        //$this->assign('arenaTotal',$arenaTotal);
        //$this->assign('numberTotal',$numberTotal);
        $this->assign('play_id', $play_id);
        $this->assign('play', $play);
        $this->assign("teams", $teams);
        $this->assign("match", getMatch($play["match_id"]));
        $this->assign("rulesTypeList", $rulesTypeList);
        $this->assign("playRuleList", $playRuleList);
        $this->assign("playResult", $playResult);
        $this->assign("has_statement", 1);
        $this->assign("team_home_header", $team_home_header);
        $this->assign("team_guest_header", $team_guest_header);
        $this->assign("fb", $fb);
        $this->assign("home_win", $home_win);
        $this->assign("guest_win", $guest_win);
        return $this->fetch('items/statement_manual');
    }

    /**
     * 玩法-赔率列表
     * @return mixed
     */
    public function odds() {
        $play_id = input('play_id', 0, 'intval');
        $rules = input('rules', 0, 'intval');
        if (!$play_id) {
            return $this->error("无效比赛信息");
        }
        $play = (new Play())->getPlay($play_id);
        $play['match'] = getMatch($play['match_id']);
        //获取玩法类型列表
        $ruleSvr = (new Rule())->factory($this->itemId);
        $rulesList = $ruleSvr->rulesList();
        foreach ($rulesList['list'] as $key => $val) {
            if ($ruleSvr->checkRuleTypeDisabled($key)) {
                unset($rulesList['list'][$key]);
            }
            $rules = $rules ? $rules : $key;
        }
        //获取玩法选项列表,足球
        $rulesOptions = $ruleSvr->getRuleOption($rules);
        if ($play['game_type'] != $this->itemId) {
            return abort(404);
        }
        $oddsList = model("Odds")->where(['rules_type' => $rules, 'play_id' => $play_id, 'user_id' => 0])->paginate(30, false, ['query' => input()]);
        foreach ($oddsList as $key => $val) {
            $val->odds = @json_decode($val->odds, true);
            $val['match'] = $val->company; //->toArray();
            $oddsList[$key] = $val;
        }
        $bodanSame = [];
        if ($rules == RULES_TYPE_BODAN) {
            $bodanSame = array_values(getBodanSameScore());
        }
        $this->assign("bodanSame", $bodanSame);
        $this->assign("rulesList", $rulesList['list']);
        $this->assign("list", $oddsList);
        $this->assign("rules", $rules);
        $this->assign("currRules", $rulesOptions);
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("is_opt", 1);
        return $this->fetch('items/odds');
    }

    /**
     * 添加、更新赔率
     */
    public function odds_add() {
        if ($this->request->isPost()) {
            $id = input("id");
            $play_id = input("play_id");
            $rulesId = input("rules_id");
            $rulesType = input("rules_type");
            $game_id = input("game_id");
            $rules = input("rules");
            $item = input("item/a");
            $odds = input("data/a");
            if (!$rules && !$rulesId) {
                return $this->error('更新失败');
            }
            if (!$rules) {
                $rules = $rulesId;
            }
            if (!$odds) {
                return $this->error('赔率不能为空');
            }
            $oddsSvr = new \library\service\Odds();
            if ($id) {
                if ($oddsSvr->updateOddsById($id, $odds, $rulesId)) {
                    Cache::rm('play_cache_list');
                    return $this->success("赔率更新成功");
                }
            } else {
                if ($oddsSvr->addOdds($rules, $rulesType, $play_id, SYS_COMPANY, $this->itemId, $odds)) {
                    (new Rule())->factory($this->itemId)->playRule($play_id, $game_id, $rules, $item);
                    return $this->success("赔率添加成功");
                }
            }
            return $this->error($oddsSvr->getError());
        }

        $id = input('id', 0, 'intval');
        $rules = input('rules', 0, 'intval');
        $play_id = input('play_id', 0, 'intval');
        $play = $this->playSvr->getPlay($play_id);
        if (!$play) {
            return abort(404);
        }
        $ruleSvr = (new Rule())->factory($this->itemId);
        //获取玩法选项列表
        $rulesList = getRuleData($this->itemId, null, null, true, true, $play['game_id']);
        $teams = $this->playSvr->getTeams($play_id);
        $odds = [];
        $rules_type = 0;
        if ($id) {
            $odds = model("odds")->get($id)->toArray();
            $rules = $odds['rules_id'];
            $rules_type = $odds['rules_type'];
            if ($odds) {
                $odds = json_decode($odds['odds'], true);
                if (isset($odds['time']) && $odds['time']) {
                    $odds = $odds['time'];
                } else {
                    $odds = $odds['init'];
                }
                if ($this->itemId != GAME_TYPE_FOOTBALL) {
                    $odds = $ruleSvr->parseOddsTableToOddsData($odds);
                }
                $odds = $ruleSvr->parseOddsWords($odds, $rules, $teams, $rules_type);
                //获取玩法选项列表,足球
                $rulesOptions = $ruleSvr->getRuleOption($rules_type);
            }
        } else {
            foreach ($rulesList as $val) {
                $temp = $ruleSvr->getDefaultOdds($val['id'], $teams);
                $odds[$val['id']] = $ruleSvr->parseOddsWords($temp['time'], $val['id'], $teams, $val['type']);
            }
            //获取玩法选项列表,足球
            $rulesOptions = $ruleSvr->getRuleOption($rules);
        }
        //获取玩法选项列表
        //$rulesTypes = getRuleData($this->itemId,null,null,true,true);

        $bodanSame = [];
        if ($rules == RULES_TYPE_BODAN) {
            $bodanSame = array_values(getBodanSameScore());
        }
        if ($play['game_id']) {
            $temp = [];
            foreach ($rulesList as $val) {
                if ($val['game_id'] == $play['game_id']) {
                    $val['explain'] = @json_decode($val['explain']);
                    $temp[$val['id']] = $val;
                }
            }
            $rulesTypes = $temp;
        }
        $playRules = getPlayRule($play_id); //当前比赛对应玩法数据
        $handicap = config("handicap");
        foreach ($handicap as $key => $val) {
            if (!$val[1]) {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "{$val[0]}($val[1])"];
            } else {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "受让{$val[0]}($val[1])"];
                $handicapList["-{$key}"] = ['value' => -$key, 'cnt' => "让 -{$val[1]}", 'text' => "{$val[0]}(-$val[1])"];
            }
        }
        $this->assign("id", $id);
        $this->assign("bodanSame", $bodanSame);
        $this->assign("rules_list", $rulesList);
        $this->assign("rules_type", $rules_type);
        $this->assign("rules_id", $rules);
        $this->assign("currRules", $rulesOptions);
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("playRules", $playRules);
        $this->assign("game_id", $play['game_id']);
        $this->assign("handicapList", array_values($handicapList));
        $this->assign("odds", $odds);

        return $this->fetch("items/odds_add");
    }

    /**
     * 赔率监控
     */
    public function odds_monitor() {
        $play_id = input('play_id', 0, 'intval');
        $rulesType = input('rules_type', 0, 'intval');
        $opt = input('opt', '');
        if (!$play_id) {
            return $this->error("无效比赛信息");
        }
        $play = (new Play())->getPlay($play_id);
        $ruleSvr = (new Rule())->factory($this->itemId);
        $rulesOptions = $ruleSvr->getRuleOption($rulesType);
        $oddsList = model("Odds")->where(['rules_type' => $rulesType, 'play_id' => $play_id, 'user_id' => 0])->paginate(30, false, ['query' => input()]);
        foreach ($oddsList as $key => $val) {
            $val->odds = @json_decode($val->odds, true);
            $val['match'] = $val->company; //->toArray();
            $oddsList[$key] = $val;
        }
        $bodanSame = [];
        if ($rulesType == RULES_TYPE_BODAN) {
            $bodanSame = array_values(getBodanSameScore());
        }

        $this->assign("list", $oddsList);
        $this->assign("currRules", $rulesOptions);
        $this->assign("play_id", $play_id);
        $this->assign("rules", $rulesType);
        $this->assign("play", $play);
        $this->assign("bodanSame", $bodanSame);
        $this->assign("token", $this->getApiToken());
        $this->assign("is_opt", 0);
        $this->assign("opt", $opt);
        return $this->fetch('items/odds_monitor');
    }

    /**
     * 批量开房
     */
    public function batch_arena_publish() {

        if ($this->request->isPost()) {
            set_time_limit(0);
            $libs = input('libs/a');
            $odds = input('odds/a');
            $arenaSvr = new Arena();
            $has_sys = 1;
            $has_robot = 0;
            if (!$libs || !$odds) {
                return $this->error("无效开房数据");
            }
            $success = 0;
            $error = 0;
            $err = '';
            $arenaSvr->admin_id = $this->admin_id;
            foreach ($odds as $key => $oddsId) {
                if (!isset($libs[$oddsId]) || (isset($libs[$oddsId]) &&
                        (!$libs[$oddsId]['play_id'] || !$libs[$oddsId]['rules_id'] || !$libs[$oddsId]['deposit']
                        ))
                ) {
                    $error++;
                    continue;
                }
                $data = [
                    'has_sys' => $has_sys,
                    'has_robot' => $has_robot,
                    'play_id' => $libs[$oddsId]['play_id'],
                    'rule_id' => $libs[$oddsId]['rules_id'],
                    'odds_id' => $oddsId,
                    'company_id' => $libs[$oddsId]['company_id'],
                    'deposit' => $libs[$oddsId]['deposit'],
                    'min_bet' => $libs[$oddsId]['min_bet'],
                    'max_bet' => 0,
                    'private' => ARENA_DISPLAY_ALL,
                    'invit_code' => 0,
                    'has_hide' => 0,
                    'intro' => '',
                    'odds' => [],
                    'classify' => ARENA_CLASSIFY_GOLD,
                    'auto_update_odds' => 1,
                ];
                if (false !== $ret = $arenaSvr->publish($data, 0)) {
                    $success++;
                } else {
                    $err .= "<br/>" . lang($arenaSvr->getError(), $arenaSvr->getErrorData());
                    $error++;
                }
            }
            //清理擂台缓存
            Cache::rm('match_list');
            Cache::rm('play_cache_list');
            return $this->success("批量开房提交成功，本次成功{$success}条，失败{$error}条" . $err);
        }

        $play_id = input('play_id', 0, 'intval');
        $rules = input('rules', 0, 'intval');
        if (!$play_id) {
            return $this->error("无效比赛信息");
        }
        $play = (new Play())->getPlay($play_id);
        $play['match'] = getMatch($play['match_id']);
        $companyList = cache('odds_company');
        $oddsList = model("Odds")->where(['play_id' => $play_id, 'user_id' => 0])->select();
        $data = [];
        $ruleSvr = (new Rule())->factory($this->itemId);
        $teams = (new Play())->getTeams($play_id);
        foreach ($oddsList as $key => $val) {
            $val = $val->toArray();
            $odds = @json_decode($val['odds'], true);

            if ($ruleSvr->checkRuleTypeDisabled($val['rules_type'])) {
                continue;
            }

            if (isset($odds['time']) && $odds['time']) {
                $odds = $odds['time'];
            } else {
                $odds = $odds['init'];
            }
            if ($this->itemId != GAME_TYPE_FOOTBALL) {
                $odds = $ruleSvr->parseOddsTableToOddsData($odds);
            }
            $odds = $ruleSvr->parseOddsWords($odds, $val['rules_id'], $teams, $rules);
            $val['odds'] = $odds;
            $company = $companyList[$val['odds_company_id']];
            $oddsList[$key] = $val;
            if (!isset($data[$company['id']])) {
                $data[$company['id']] = $company;
                $data[$company['id']]['odds_list'] = [];
            }
            $val['rule'] = getRuleData($val['game_type'], $val['rules_id']);
            $data[$company['id']]['odds_list'][$val['id']] = $val;
        }
        $sys_arena_min_deposit = config('system.sys_arena_min_deposit');
        if (is_string($sys_arena_min_deposit)) {
            $sys_arena_min_deposit = @json_decode($sys_arena_min_deposit, true);
        }
        $sys_arena_min_bet_money = config('system.sys_arena_min_bet_money');
        if (is_string($sys_arena_min_bet_money)) {
            $sys_arena_min_bet_money = @json_decode($sys_arena_min_bet_money, true);
        }
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("oddsList", $data);
        $this->assign("sys_arena_min_deposit", $sys_arena_min_deposit);
        $this->assign("sys_arena_min_bet_money", $sys_arena_min_bet_money);
        return $this->fetch('items/batch_arena_publish');
    }

    /**
     * 摆擂
     */
    public function arena_publish() {

        if ($this->request->isPost()) {
            $oddsId = input("id", 0, 'intval');
            $playId = input("play_id", 0, 'intval');
            $companyId = input("company_id", 0, 'intval');
            $rulesId = input("rules_id", 0, 'intval');
            $gameId = input("game_id", 0, 'intval');
            $user = input("user", 0, 'intval');
            $deposit = input("deposit", 0, 'intval');
            $private = input("private", 0, 'intval');
            $auto_update_odds = input("auto_update_odds", 0, 'intval');
            $hasHide = input("has_hide", STATUS_NO, 'intval');
            $odds = input("data/a");
            $has_sys = $user == 1 ? 1 : 0;
            $has_robot = $user == 2 ? 1 : 0;
            $arenaSvr = new Arena();
            $min_bet = input("post.min_bet/d"); //每单最少投注
            $max_bet = input("post.max_bet/d"); //每人最多累计投注
            //$odds = (new Rule())->factory($this->itemId)->parseOdds($odds,$rulesId);
            $arenaSvr->admin_id = $this->admin_id;

            $data = [
                'has_sys' => $has_sys,
                'has_robot' => $has_robot,
                'play_id' => $playId,
                'rules_id' => $rulesId,
                'rule_id' => $rulesId,
                'odds_id' => $oddsId,
                'company_id' => $companyId,
                'deposit' => $deposit,
                'min_bet' => $min_bet,
                'max_bet' => $max_bet,
                'private' => $private,
                'invit_code' => 0,
                'has_hide' => $hasHide,
                'intro' => '',
                'odds' => $odds,
                'classify' => ARENA_CLASSIFY_GOLD,
                'auto_update_odds' => $auto_update_odds,
            ];
   
            //if(false !== $ret = $arenaSvr->publish($deposit,$odds,0,$playId,$rulesId,$min_bet,$max_bet,$oddsId,$private,'',$has_sys,$has_robot,$hasHide)){
            if (false !== $ret = $arenaSvr->publish($data, 0)) {
                //清理擂台缓存
                Cache::rm('play_cache_list');
                Cache::rm('match_list');
                return $this->success("擂台发布成功");
            } else {
                return $this->error(lang($arenaSvr->getError(), $arenaSvr->getErrorData()));
            }
        }
        $id = input('id', 0, 'intval');
        $play_id = input('play_id', 0, 'intval');
        $play = $this->playSvr->getPlay($play_id);
        if (!$play) {
            return abort(404);
        }
        //获取玩法选项列表
        $rulesTypes = getRuleData($this->itemId, null, null, true, true);
        $teams = $this->playSvr->getTeams($play_id);
        $odds = [];
        $rules_id = '';
        $company_id = 0;
        $odds_id = 0;
        if ($id) {
            $odds = model("odds")->get($id)->toArray();
            if ($odds) {
                $rules = $odds['rules_type'];
                $rules_id = $odds['rules_id'];
                $company_id = $odds['odds_company_id'];
                $odds_id = $odds['id'];

                $odds = json_decode($odds['odds'], true);
                if (isset($odds['time']) && $odds['time']) {
                    $odds = $odds['time'];
                } else {
                    $odds = $odds['init'];
                }

                if ($this->itemId != GAME_TYPE_FOOTBALL) {
                    $odds = (new Rule())->factory($this->itemId)->parseOddsTableToOddsData($odds);
                }

                $odds = (new Rule())->factory($this->itemId)->parseOddsWords($odds, $rules_id, $teams, $rules);
            }
        }
        //获取玩法类型列表
        $rulesList = (new Rule())->factory($this->itemId)->rulesList();
        //获取玩法选项列表,足球
        $rulesOptions = (new Rule())->factory($this->itemId)->getRuleOption($rules);
        //获取玩法选项列表
        $rulesTypes = getRuleData($this->itemId, null, null, true, true);
        $bodanSame = [];
        if ($rules == RULES_TYPE_BODAN) {
            $bodanSame = array_values(getBodanSameScore());
        }
        if ($play['game_id']) {
            foreach ($rulesTypes as $val) {
                if ($val['game_id'] == $play['game_id']) {
                    $val['explain'] = @json_decode($val['explain']);
                    $temp[$val['id']] = $val;
                }
            }
            $rulesTypes = $temp;
        }
        $playRules = getPlayRule($play_id); //当前比赛对应玩法数据
        $handicap = config("handicap");
        foreach ($handicap as $key => $val) {
            if (!$val[1]) {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "{$val[0]}($val[1])"];
            } else {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "受让{$val[0]}($val[1])"];
                $handicapList["-{$key}"] = ['value' => -$key, 'cnt' => "让 -{$val[1]}", 'text' => "{$val[0]}(-$val[1])"];
            }
        }

        $company = [];
        if ($company_id) {
            $oddsCompany = cache('odds_company');
            $company = isset($oddsCompany[$company_id]) ? $oddsCompany[$company_id] : [];
        }
        $sys_arena_min_deposit = config('system.sys_arena_min_deposit');
        if (is_string($sys_arena_min_deposit)) {
            $sys_arena_min_deposit = @json_decode($sys_arena_min_deposit, true);
        }
        $sys_arena_min_bet_money = config('system.sys_arena_min_bet_money');
        if (is_string($sys_arena_min_bet_money)) {
            $sys_arena_min_bet_money = @json_decode($sys_arena_min_bet_money, true);
        }

        $this->assign("id", $id);
        $this->assign("bodanSame", $bodanSame);
        $this->assign("rulesList", $rulesList['list']);
        $this->assign("rules_types", $rulesTypes);
        $this->assign("rules", $rules_id);
        $this->assign("currRules", $rulesOptions);
        $this->assign("play_id", $play_id);
        $this->assign("company_id", $company_id);
        $this->assign("company", $company);
        $this->assign("play", $play);
        $this->assign("playRules", $playRules);
        $this->assign("game_id", $play['game_id']);
        $this->assign("handicapList", array_values($handicapList));
        $this->assign("odds", $odds);
        $this->assign("odds_id", $odds_id);
        $this->assign("sys_arena_min_deposit", $sys_arena_min_deposit);
        $this->assign("sys_arena_min_bet_money", $sys_arena_min_bet_money);
        return $this->fetch('items/arena_publish');
    }

    /**
     * 擂台列表
     * @return mixed
     */
    public function arena_list() {
        $opt = input("opt"); //玩法ID
        $rulesId = input("rules/d"); //玩法ID
        $rulesType = input("rules_type/d"); //玩法ID
        $playId = input("play_id/d"); //比赛ID
        $gameId = input("game_id/d"); //比赛ID
        $status = input('status', 0, 'intval'); //擂台状态
        $iOrder = input("order");
        $mark = input("mark");
        $match_name = input("match_name");
        $team_name = input("team_name");
        $play_time = input("play_time");
        $nickname = input("nickname");
        $private = input("private/d");
        $iOrderField = input("order_field");
        $param = input("param.");
        //获取玩法类型列表
        $rulesList = (new Rule())->factory($this->itemId)->rulesList();

        $play = '';
        $match = '';
        $where = [];
        $where['game_type'] = $this->itemId;
        if ($playId) {
            $where['play_id'] = $playId;
            if (!$opt) {
                $play = (new Play())->getPlay($playId);
                $match = getMatch($play['match_id']);
            }
            $play['match'] = $match;
        }
        if ($gameId) {
            $where['game_id'] = $gameId;
        }
        if ($rulesId) {
            $where['rules_id'] = $rulesId;
        }
        if ($rulesType) {
            $where['rules_type'] = $rulesType;
        }
        if ($mark) {
            $where['mark'] = $mark;
        }
        if ($match_name) {
            $where['match'] = $match_name;
        }
        if ($team_name) {
            $where['team_name'] = $team_name;
        }
        if ($play_time) {
            $where['play_time'] = $play_time;
        }
        if ($status) {
            $where['status'] = $status;
        }
        if ($nickname) {
            $where['nickname'] = $nickname;
        }
        if ($private) {
            $where['private'] = $private;
        }
        if ($iOrder && $iOrderField) {
            $iOrder = strtolower($iOrder);
            $order = "{$iOrderField} {$iOrder},a.id desc";
            $iOrder = $iOrder == "desc" ? "asc" : "desc";
        } else {
            $order = "a.id desc";
        }
        //$order = 'a.id desc';
        $limit = $opt ? 8 : 16;
        $list = model('arena')->findAll($where, $order, $limit, $param);

        //获取项目下的游戏
        $games = getSportGames($this->itemId);

        unset($param['order']);
        unset($param['order_field']);
        unset($param['_pjax']);
        $param = http_build_query($param);
        if ($param) {
            $param .= "&";
        }
        $this->assign('rules_id', $rulesId);
        $this->assign('rules_type', $rulesType);
        $this->assign('play_id', $playId);
        $this->assign('game_id', $gameId);
        $this->assign("iOrder", $iOrder);
        $this->assign("iOrderField", $iOrderField);
        $this->assign("param", $param);
        $this->assign("status", $status);
        $this->assign("mark", $mark);
        $this->assign("match_name", $match_name);
        $this->assign("team_name", $team_name);
        $this->assign("play_time", $play_time);
        $this->assign("nickname", $nickname);

        $this->assign("rulesList", $rulesList['list']);
        $this->assign('games', $games);
        $this->assign('play', $play);
        $this->assign('match', $match);
        $this->assign('list', $list);
        return $opt == 'dialog' ? $this->fetch('items/arena_list_dialog') : $this->fetch('items/arena_list');
    }

    /**
     * 追加保证金
     */
    public function arena_deposit() {

        if ($this->request->isPost()) {
            $id = input("post.id", 0, 'intval');
            $target = input("post.target", 0, 'intval');
            $deposit = input("post.deposit", 0, 'intval');
            if (!$id) {
                return $this->error("无效擂台信息");
            }if (!$target || ($target != MEMBER && $target != SYSTEM)) {
                return $this->error("无效扣除对象");
            }
            if (!$deposit) {
                return $this->error("追加保证金不能为空");
            }
            $arenaSvr = new \library\service\Arena($id);
            $hasSystem = $target == SYSTEM ? SYS_USER_ID : 0;
            $arenaSvr->admin_id = $this->admin_id;
            if (false === $result = $arenaSvr->appendDeposit($id, $deposit, $hasSystem)) {
                return $this->error(lang($arenaSvr->getError(), $arenaSvr->getErrorData()));
            }
            return $this->success("追加保证金成功!");
        }



        $arenaId = input("id/d");
        if (!$arenaId) {
            $this->error("参数不正确");
        }
        $arena = (new Arena())->getCacheArenaById($arenaId);
        if (!$arena || $arena['game_type'] != $this->itemId) {
            $this->error("获取擂台数据失败");
        }
        if ($arena['status'] != ARENA_START) {
            return $this->error("当前擂台状态不可追加保证金");
        }
        $this->assign("id", $arenaId);
        return $this->fetch('items/arena_deposit');
    }

    /**
     * 擂台设置
     */
    public function arena_conf() {
        if ($this->request->isPost()) {

            $odds = input("data/a");
            $id = input("id");
            $user = input("user", 0, 'intval');
            $private = input("post.private", ARENA_DISPLAY_ALL, 'intval');
            $has_sys = $user == 1 ? 1 : 0;
            $has_robot = $user == 2 ? 1 : 0;
            $arenaSvr = new Arena();
            $has_hide = input("post.has_hide/d", STATUS_NO); //是否隐藏
            $min_bet = input("post.min_bet/d"); //每单最少投注
            $max_bet = input("post.max_bet/d"); //每人最多累计投注
            $auto_update_odds = input("post.auto_update_odds/d"); //是否自动更新
            $oddsId = input("post.odds_id/d"); //赔率ID
            $arenaSvr = new \library\service\Arena();
            $arenaSvr->admin_id = $this->admin_id;
            $arena = $arenaSvr->getCacheArenaById($id);
            $ret = true;
            if (!$auto_update_odds) { //取消自动更新才会更新赔率
                $ret = $arenaSvr->modifyOdds($id, $odds, 0);
            }
            if (false !== $ret) {
                if ($arena['classify'] != ARENA_CLASSIFY_CREDIT) {
                    if ($arenaSvr->Conf($id, [
                                'private' => $private,
                                'min_bet' => $min_bet,
                                'max_bet' => $max_bet,
                                'has_hide' => $has_hide,
                                'auto_update_odds' => $auto_update_odds,
                                'odds_id' => $oddsId,
                                    ], 0)) {
                        return $this->success('擂台更新成功');
                    } else {
                        return $this->error(lang($arenaSvr->getError()));
                    }
                } else {
                    return $this->success('擂台更新成功');
                }
            } else {
                return $this->error(lang($arenaSvr->getError(), $arenaSvr->getErrorData()));
            }
        }

        $arenaId = input("id/d");
        if (!$arenaId) {
            $this->error("参数不正确");
        }
        $arena = (new Arena())->getCacheArenaById($arenaId);
        if (!$arena || $arena['game_type'] != $this->itemId) {
            $this->error("获取擂台数据失败");
        }

        $play_id = $arena['play_id'];
        $play = $this->playSvr->getPlay($play_id);
        if (!$play) {
            return abort(404);
        }
        $teams = $this->playSvr->getTeams($play_id);
        //获取玩法选项列表
        $rulesTypes = getRuleData($this->itemId, null, null, true, true);
        $odds = $arena['odds'];
        $odds = (new Rule())->factory($this->itemId)->parseOddsWords($odds, $arena['rules_id'], $teams);
        //获取玩法类型列表
        $rulesList = (new Rule())->factory($this->itemId)->rulesList();
        //获取玩法选项列表,足球
        $rulesOptions = (new Rule())->factory($this->itemId)->getRuleOption($arena['rules_type']);
        //获取玩法选项列表
        $rulesTypes = getRuleData($this->itemId, null, null, true, true);
        $bodanSame = [];
        if ($arena['rules_id'] == RULES_TYPE_BODAN) {
            $bodanSame = array_values(getBodanSameScore());
        }
        if ($play['game_id']) {
            foreach ($rulesTypes as $val) {
                if ($val['game_id'] == $play['game_id']) {
                    $val['explain'] = @json_decode($val['explain']);
                    $temp[$val['id']] = $val;
                }
            }
            $rulesTypes = $temp;
        }
        $playRules = getPlayRule($play_id); //当前比赛对应玩法数据
        $handicap = config("handicap");
        foreach ($handicap as $key => $val) {
            if (!$val[1]) {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "{$val[0]}($val[1])"];
            } else {
                $handicapList["{$key}"] = ['value' => $key, 'cnt' => "让 {$val[1]}", 'text' => "受让{$val[0]}($val[1])"];
                $handicapList["-{$key}"] = ['value' => -$key, 'cnt' => "让 -{$val[1]}", 'text' => "{$val[0]}(-$val[1])"];
            }
        }
        $company = [];
        if ($arena['company_id']) {
            $oddsCompany = cache('odds_company');
            $company = isset($oddsCompany[$arena['company_id']]) ? $oddsCompany[$arena['company_id']] : [];
        }

        $this->assign("bodanSame", $bodanSame);
        $this->assign("rulesList", $rulesList['list']);
        $this->assign("rules_types", $rulesTypes);
        $this->assign("rules", $arena['rules_id']);
        $this->assign("currRules", $rulesOptions);
        $this->assign("play_id", $play_id);
        $this->assign("play", $play);
        $this->assign("playRules", $playRules);
        $this->assign("game_id", $play['game_id']);
        $this->assign("handicapList", array_values($handicapList));
        $this->assign("odds", $odds);
        $this->assign("id", $arenaId);
        $this->assign("arena", $arena);
        $this->assign("company", $company);
        $this->assign("has_update", 1);
        return $this->fetch('items/arena_publish');
    }

    /**
     * 擂台投注项统计
     */
    public function arena_bet_stat() {
        $arenaId = input("id/d");
        $arena = (new \library\service\Arena())->getCacheArenaById($arenaId);
        if (!$arena) {
            return $this->error("获取擂台数据失败");
        }
        //投注项统计
        $arena_target = Db::name('arena_target')->where(['arena_id' => $arenaId])->select();
        $maxBonus = 0; //最高支付奖金
        $betTotal = $arena['bet_money']; //总投注金额
        $winTotal = $arena['win'];
        foreach ($arena_target as $val) {
            $maxBonus = max($maxBonus, $val['bonus']);
        }

        $teams = isset($arena['teams']) && $arena['teams'] ? $arena['teams'] : (new Play())->getTeams($arena['play_id']);
        $odds = $arena['odds'];
        $odds = (new Rule())->factory($this->itemId)->parseOddsWords($odds, $arena['rules_id'], $teams);
        $arena['win_target'] = @json_decode($arena['win_target'], true);
        $this->assign("arena", $arena);
        $this->assign("arena_target", $arena_target);
        $this->assign("maxBonus", $maxBonus);
        $this->assign("betTotal", $betTotal);
        $this->assign("winTotal", $winTotal);
        $this->assign("odds", $odds);
        return $this->fetch('items/arena_bet_stat');
    }

    /**
     * 擂台投注用户列表
     */
    public function arena_bet_user() {
        $nickname = input("nickname");
        $btime = input("btime");
        $etime = input("etime");
        $arenaId = input("id/d");
        $arena = (new \library\service\Arena())->getCacheArenaById($arenaId);
        if (!$arena) {
            return $this->error("获取擂台数据失败");
        }
        $where = [];
        if ($nickname) {
            $where['arena_bet_detail.arena_id'] = $arenaId;
            if ($btime && $etime) {
                $where['arena_bet_detail.create_time'] = ['between', [strtotime($btime), strtotime($etime)]];
            } elseif ($btime) {
                $where['arena_bet_detail.create_time'] = ['>=', strtotime($btime)];
            } elseif ($etime) {
                $where['arena_bet_detail.create_time'] = ['<', strtotime($etime)];
            }
            $where['user.nickname'] = ['like', "%{$nickname}%"];
            $arenaUser = Db::view('arena_bet_detail', '*')
                    ->view('user', 'nickname', 'arena_bet_detail.user_id=user.id', 'LEFT')
                    ->where($where)
                    ->order('id desc')
                    ->paginate(10);
        } else {
            $where['arena_id'] = $arenaId;
            if ($btime && $etime) {
                $where['create_time'] = ['between', [strtotime($btime), strtotime($etime)]];
            } elseif ($btime) {
                $where['create_time'] = ['>=', strtotime($btime)];
            } elseif ($etime) {
                $where['create_time'] = ['<', strtotime($etime)];
            }
            $arenaUser = Db::name('arena_bet_detail')->where($where)->order('id desc')->paginate(10, false, ['query' => input()]);
        }
        $teams = isset($arena['teams']) && $arena['teams'] ? $arena['teams'] : (new Play())->getTeams($arena['play_id']);
        $odds = $arena['odds'];
        $ruleSvr = (new Rule())->factory($this->itemId);
        //$val['rule_type'] = $ruleSvr->getRuleType($arena['rules_id']);
        $odds = $ruleSvr->parseOddsWords($odds, $arena['rules_id'], $teams);
        $arena['win_target'] = @json_decode($arena['win_target'], true);
        //投注项统计
        $arena_target = Db::name('arena_target')->where(['arena_id' => $arenaId])->select();
        $maxBonus = 0; //最高支付奖金
        $betTotal = $arena['bet_money']; //总投注金额
        $winTotal = $arena['win'];
        foreach ($arena_target as $val) {
            $maxBonus = max($maxBonus, $val['bonus']);
        }


        $this->assign("arena", $arena);
        $this->assign("odds", $odds);
        $this->assign("list", $arenaUser);
        $this->assign("arena_id", $arenaId);
        $this->assign("maxBonus", $maxBonus);
        $this->assign("rules_type", $arena['rules_type']);
        return $this->fetch('items/arena_bet_user');
    }

    /**
     * 系统补注
     */
    public function arena_sys_bet() {
        if ($this->request->isPost()) {

            return $this->success("系统补注功能关闭");
            $arenaId = input("arena_id/d");
            $target = input("target");
            $item = input("item");
            $money = input("money/d");
            $arenaSvr = new \library\service\Arena();
            $arenaSvr->admin_id = $this->admin_id;
            //随机获取机器人
            $user = Db::name("user")->where(['has_robot' => 1])->order("RAND()")->find();
            if (false !== $ret = $arenaSvr->betting($arenaId, $money, $user['id'], $target, $item)) {
                return $this->success("投注成功");
            } else {
                $code = $arenaSvr->getError();
                $vars = $arenaSvr->getErrorData();
                return $this->error(lang($code, $vars));
            }
        }

        $arenaId = input("arena_id/d");
        $target = input("target");
        $item = input("item");
        $this->assign("arena_id", $arenaId);
        $this->assign("target", $target);
        $this->assign("item", $item);
        return $this->fetch('items/arena_sys_bet');
    }

    /**
     * 擂台详情
     */
    public function arena_info() {
        $id = input('id/d');
        if (!$id) {
            return $this->error('无效参数');
        }
        $arenaSvr = new Arena();
        $arena = $arenaSvr->findArena($id);
        if (!$arena) {
            return $this->error("未查找到{$id}房间信息");
        }
        $arena['odds'] = @json_decode($arena['odds'], true);
        $play = $arena['play'];
        //获取最新赔率
        $odds = [];
        $arenaOdds = Db::name('arena_odds')->where(['arena_id' => $id])->order('create_time asc')->find();
        $temp['init'] = @json_decode($arenaOdds['odds'], true);
        $temp['time'] = $arena['odds'];
        $odds = $temp;
        $arenaOdds = (new Rule())->factory($arena['game_type'])->parseOddsWords($arena['odds'], $arena['rules_id'], $arena['teams']);
        //$oddsList = (new Rule())->factory($arena['game_type'])->parseOddsWords($odds['time'],$arena['rules_id'],$arena['teams']);
        $arenaTarget = $arena['arena_target'] = Db::name('arena_target')->where(['arena_id' => $id])->select();
        //最高支付奖金
        $arena['max_pay_money'] = 0;
        //最低支付奖金
        $arena['min_pay_money'] = 0;

        foreach ($arenaTarget as $val) {
            if (!in_array($val['target'], ['handicap', 'over', 'under'])) {
                $arena['max_pay_money'] = max($arena['max_pay_money'], $val['bonus']);
                $arena['min_pay_money'] = !is_null($arena['min_pay_money']) ? min($arena['min_pay_money'], $val['bonus']) : $val['bonus'];
            }
            if (isset($arenaOdds[$val['target'] . $val['item']])) {
                //$total = $arena['deposit'];//+$arena['bet_money']-$val['money'];
                //可接收的总投注$val['deposit'];//
                $arenaOdds[$val['target'] . $val['item']]['bet_total'] = $arenaSvr->factory($arena['game_type'])->betMaxLimit($arenaOdds[$val['target'] . $val['item']]['odds'], $val['deposit'], $arena['rules_type']);
                //已投注
                //$arenaOdds[$val['target'] . $val['item']]['bet'] = $arenaSvr->factory($arena['game_type'])->betMaxLimit($arenaOdds[$val['target'].$val['item']]['odds'],($arena['deposit']+$arena['bet_money']-$arenaOdds[$val['target'] . $val['item']]['money']),$arena['rules_id']);
                if ($arena['classify'] == ARENA_CLASSIFY_CREDIT) { //征信局扣除本金
                    $arenaOdds[$val['target'] . $val['item']]['bonus'] = $val['bonus'] - $val['money'];
                } else {
                    $arenaOdds[$val['target'] . $val['item']]['bonus'] = $val['bonus'];
                }
                $arenaOdds[$val['target'] . $val['item']]['number'] = $val['number'];
                $arenaOdds[$val['target'] . $val['item']]['money'] = $val['money'];
                $arenaOdds[$val['target'] . $val['item']]['real_money'] = 0;
                $arenaOdds[$val['target'] . $val['item']]['rate'] = ($arena['deposit'] + $arena['bet_money']) ? numberFormat($val['bonus'] / ($arena['deposit'] + $arena['bet_money']) * 100, 2) : 0;
            }
        }

        //如果擂台已结算
        $returnGold = 0;
        if ($arena['status'] == ARENA_STATEMENT_END) {
            $returnGold = Db::name('arena_bet_detail')->where(['arena_id' => $arena['id'], 'status' => DEPOSIT_SAME])->sum('money');
            $lst = Db::name('arena_bet_detail')->field("sum(money) as money,sum(win_money) as win_money,sum(fee) as fee,target,item,status")->group("status,target,item")->where(['arena_id' => $arena['id'], 'status' => ['in', [DEPOSIT_WIN, DEPOSIT_SAME, DEPOSIT_LOST_HALF, DEPOSIT_WIN_HALF]]])->select();
            if ($arena['classify'] == ARENA_CLASSIFY_CREDIT) {
                foreach ($lst as $val) {
                    if (isset($odds[$val['target'] . $val['item']])) {
                        if (!isset($odds[$val['target'] . $val['item']]['real_money'])) {
                            $odds[$val['target'] . $val['item']]['real_money'] = 0;
                        }
                        if (in_array($val['status'], [DEPOSIT_WIN, DEPOSIT_WIN_HALF])) {
                            $odds[$val['target'] . $val['item']]['real_money'] += ($val['win_money'] - $val['money']);
                            $arena['win_target']['win'] = $arena['win_target']['win'] - $val['money'];
                        }/* elseif ($val['status'] == DEPOSIT_LOST_HALF) { //征信局支付资金扣除本金中包括了输一半的本金，此处再加一半回来
                          $arena['win_target']['win'] += numberFormat($val['money'] / 2);
                          } */
                    }
                }
            } else {
                foreach ($lst as $val) {
                    if (isset($odds[$val['target'] . $val['item']])) {
                        if (!isset($odds[$val['target'] . $val['item']]['real_money'])) {
                            $odds[$val['target'] . $val['item']]['real_money'] = 0;
                        }

                        if ($val['status'] == DEPOSIT_SAME) {
                            $odds[$val['target'] . $val['item']]['real_money'] += $val['money'];
                        } elseif ($val['status'] == DEPOSIT_LOST_HALF) {
                            $returnGold += numberFormat($val['money'] / 2);
                            $odds[$val['target'] . $val['item']]['real_money'] += numberFormat($val['money'] / 2);
                        } elseif (in_array($val['status'], [DEPOSIT_WIN, DEPOSIT_WIN_HALF])) {
                            $odds[$val['target'] . $val['item']]['real_money'] += ($val['win_money'] + $val['fee']);
                        }
                    }
                }
            }
        }
        $arena['return_gold'] = floatval($returnGold); //中一半退回投注都本金合计
        $arena['ret_credit_gold'] = isset($arena['ret_credit_gold']) ? floatval($arena['ret_credit_gold']) : 0; //征信局收回本金

        $chartData = [];
        $chartData['last']['name'] = '剩余';
        $chartData['been']['name'] = '已投';
        $chartCategories = [];
        $chartCategoriesName = [];
        foreach ($arenaOdds as $key => $val) {

            //$chartData['last']['data'][] = ['y' => (double)$val['bet_total'],'color' => '#f9a455'];
            //$chartData['been']['data'][] =  ['y' => (double)$val['money'],'color' => '#50B432'];

            $chartData['last']['data'][] = (double) $val['bet_total'];
            $chartData['been']['data'][] = (double) $val['money'];
            $chartCategories[$key] = [
                'name' => $val['name'] . (!is_numeric($val['target_name']) ? $val['target_name'] : ''),
                'target' => $val['target'],
                'item' => $val['item'],
            ];
            $chartCategoriesName[$key] = $val['name'] . (!is_numeric($val['target_name']) ? $val['target_name'] : '');
        }
        //投注用户列表
        $arenaUser = Db::name('arena_bet_detail')->where(['arena_id' => $id])->order('id desc')->paginate(10, false, ['query' => input()]);

        $this->assign("list", $arenaUser);

        $this->assign('odds', $odds);
        $this->assign('arena', $arena);
        $this->assign('play', $play);
        $this->assign('chartData', $chartData);
        $this->assign('teams', $arena['teams']);
        $this->assign('chartCategories', ($chartCategories));
        $this->assign('chartCategoriesName', ($chartCategoriesName));
        $this->assign('arenaOdds', ($arenaOdds));
        $this->assign("sub_title", "房间{$id}");
        return $this->fetch('items/arena_info');
    }

    /**
     * 投注列表
     * @return mixed
     */
    public function betting_list() {
        $nickname = input('nickname');
        $play_id = input('play_id');
        $arena = input('arena');
        $bet_id = input('bet_id');
        $status = input('status');
        $inputRulesType = $rules_type = input('rules_type');
        $btime = input('btime');
        $etime = input('etime');


        $ruleSvr = (new Rule())->factory($this->itemId);
        $rulesList = $ruleSvr->rulesList();
        /* $rulesTypes = getRuleData($this->itemId,null,null,true,true);
          if($this->itemId != GAME_TYPE_FOOTBALL){
          $ruleIds = [];
          foreach($rulesTypes as $val){
          $val['explain'] = @json_decode($val['explain']);
          $temp[$val['id']] = $val;
          if($val['type'] == $rules_type){
          $ruleIds[] = $val['id'];
          }
          }
          $rulesTypes = $temp;
          $rules_type = $ruleIds;
          } */

        if (!$btime) {
            $btime = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - 30));
        }

        if (!$etime) {
            $etime = date("Y-m-d H:i:s");
        }

        $this->assign("rulesList", $rulesList['list']);
        $where = [];
        $where['a.game_type'] = $this->itemId;
        if ($nickname) {
            $where['u.nickname'] = ['like', "%{$nickname}%"];
        }
        if ($play_id) {
            $where['a.play_id'] = $play_id;
        }
        if ($bet_id) {
            $where['abd.id'] = $bet_id;
        }
        if ($status) {
            $where['abd.status'] = $status == DEPOSIT_WIN ? ['in', [DEPOSIT_WIN, DEPOSIT_WIN_HALF]] : $status;
        }
        if ($rules_type) {
            $where['a.rules_type'] = $rules_type; //is_array($rules_type) ? ['in',array_values($rules_type)] : $rules_type;
        }
        if ($btime && $etime) {
            $where['abd.create_time'] = [['egt', strtotime($btime)], ['elt', strtotime($etime)]];
        } elseif ($btime) {
            $where['abd.create_time'] = ['egt', strtotime($btime)];
        } elseif ($etime) {
            $where['abd.create_time'] = ['elt', strtotime($etime)];
        }
        if ($arena) {
            if ($arena) {
                $where['a.id'] = intval($arena);
            } else {
                $where['a.mark'] = $arena;
            }
        }

        $query = [
            'nickname' => $nickname,
            'arena' => $arena,
            'bet_id' => $bet_id,
            'status' => $status,
            'rules_type' => $inputRulesType,
            'btime' => $btime,
            'etime' => $etime,
        ];
        $list = model('bet')->getBetList($where, "abd.create_time DESC", 20, $query);
        $list = modelToArray($list);
        foreach ($list as $key => $val) {
            $odds = @json_decode($val['arena_odds'], true);
            $teams = $this->playSvr->getTeams($val['play_id'], ['name']);
            $val['teams'] = $teams;
            $val['arena_odds'] = $ruleSvr->parseOddsWords($odds, $val['rules_id'], $teams);
            $val['match'] = getMatch($val['match_id'], null, ['name']);
            $list[$key] = $val;
        }
        $play = [];
        if ($play_id) {
            $play = (new Play())->getPlay($play_id);
            $match = getMatch($play['match_id']);
            $play['match'] = $match;
        }
        //-abd.money

        $total = model('bet')->getBetCount($where, '
            SUM(abd.money) as money_total,
                            sum(case when abd.`status`in(' . DEPOSIT_WIN . ',' . DEPOSIT_WIN_HALF . ') then abd.win_money end) AS win,
                            SUM(case when abd.`status`=' . DEPOSIT_LOSE . ' then abd.money end) as lose,
                            SUM(case when abd.`status`=' . DEPOSIT_WIN_HALF . ' then (abd.money/2) end) as lose_half,
                            SUM(abd.fee) as fee');
        $this->assign("list", $list);
        $this->assign("nickname", $nickname);
        $this->assign("play_id", $play_id);
        $this->assign("arena", $arena);
        $this->assign("bet_id", $bet_id);
        $this->assign("status", $status);
        $this->assign("rules_type", $inputRulesType);
        $this->assign("btime", $btime);
        $this->assign("etime", $etime);
        $this->assign("play", $play);
        $this->assign("total", $total);
        $this->assign($query);
        return $this->fetch('items/betting_list');
    }

    /**
     * 玩法管理
     */
    public function rules() {
        $game_id = input("game_id/d");
        $gameList = [];
        $model = model("rules");
        $where = ['is_delete' => 0, 'game_type' => $this->itemId];
        if ($game_id) {
            $where['game_id'] = $game_id;
        }
        $ruleSvr = (new Rule())->factory($this->itemId);
        $disRuleTypeList = $ruleSvr->disRulesType;
        if ($disRuleTypeList && is_array($disRuleTypeList)) {
            $where['type'] = ['not in', array_values($disRuleTypeList)];
        }
        $order = "sort asc,id asc";

        $list = $model->where($where)->order($order)->paginate(18, false, [
            'query' => ['game_id' => $game_id]
        ]);
        /* foreach($list as $key =>$val){
          if($ruleSvr->checkRuleTypeDisabled($val['type'])){
          $val['status'] = STATUS_DISABLED;
          }
          $list[$key] = $val;
          } */
        //游戏列表
        $gameList = getSportGames($this->itemId, true);
        //玩法类型列表
        $ruleType = $ruleSvr->rulesList();

        $this->assign("list", $list);
        $this->assign("gameList", $gameList);
        $this->assign("game_id", $game_id);
        $this->assign("ruleType", $ruleType['list']);
        return $this->fetch('items/rules');
    }

    /**
     * 添加玩法
     */
    public function rules_add() {
        $model = model("rules");
        if ($this->request->isPost()) {
            $min_deposit = input('post.min_deposit/d', 0, 'intval');
            $name = input("post.name");
            $alias = input("post.alias");
            $intro = input("post.intro");
            $help_intro = input("post.help_intro", '', null);
            $type = input("post.type");
            $status = input("post.status");
            $sort = intval(input("post.sort/d"));
            $game_id = input('post.game');
            if ($min_deposit < 0) {
                return $this->error("最低保证金不能少于0");
            }
            if (mb_strlen($alias, "UTF8") > 5) {
                return $this->error("别名长度需5个字以内");
            }
            if (mb_strlen($intro, "UTF8") > 30) {
                return $this->error("简介长度需30个字以内");
            }
            //玩法选项
            $explain = input("post.rule_items/a");
            if ($ret = (new Rule)->factory($this->itemId)->getDefaultExplain($type)) {
                $explain = $ret;
            }
            $data = [
                'game_type' => $this->itemId,
                'id' => input("post.id/d", 0, 'intval'),
                'name' => parseRuleName($name, [], ["主队" => "#team_home_name#", "客队" => "#team_guest_name#"]),
                'alias' => parseRuleName($alias, [], ["主队" => "#team_home_name#", "客队" => "#team_guest_name#"]),
                'game_id' => $game_id,
                'type' => $type,
                'status' => $status,
                'min_deposit' => $min_deposit,
                'explain' => json_encode(array_values($explain)),
                'intro' => $intro,
                'help_intro' => $help_intro,
                'sort' => $sort ? $sort : 999,
            ];
            if (isset($data['id']) && $data['id']) {
                unset($data['type']);
                unset($data['game_id']);
                $rule = $model->where(['id' => $data['id']])->find();
                if ($rule->is_edit <> 1) { //此状态下只能修改最低保证金
                    unset($data['name']);
                    unset($data['explain']);
                }
                $result = $model->isUpdate(true)->save($data);
            } else {
                if (!$name) {
                    return $this->error("玩法名称不能为空");
                }
                $data['is_edit'] = 1;
                $result = $model->isUpdate(false)->save($data);
            }
            if ($result) {
                return $this->success("操作成功");
            } else {
                return $this->error($model->getError());
            }
        }
        $id = input("id/d");
        $res = [];
        if ($id) {
            $res = $model->where(['id' => $id])->find()->toArray();
            $res['explain'] = @json_decode($res['explain']);
            $res['rulesItem'] = $model->item()->where(['rules_id' => $id])->order("id asc")->select();
        }
        $gameList = [];
        //获取玩法列表
        $rulesList = (new Rule())->factory($this->itemId)->rulesList();
        //游戏列表
        $gameList = getSportGames($this->itemId, true);

        $this->assign("res", $res);
        $this->assign("gameList", $gameList);
        $this->assign("rulesList", $rulesList['list']);
        return $this->fetch('items/rules_add');
    }

    /**
     * 赛事管理
     */
    public function match() {
        $param = input("param.");
        $name = input("name");
        $is_hot = input("is_hot");
        $is_recommend = input("is_recommend");
        $is_show = input("is_show");
        $where = [];
        if ($name) {
            $where["m.name"] = ["like", "%$name%"];
        }
        if ($is_hot) {
            if ($is_hot == 1)
                $where["m.is_hot"] = 1;
            elseif ($is_hot == 2)
                $where["m.is_hot"] = 0;
        }
        if ($is_recommend) {
            if ($is_recommend == 1)
                $where["m.is_recommend"] = 1;
            elseif ($is_recommend == 2)
                $where["m.is_recommend"] = 0;
        }
        if ($is_show) {
            if ($is_show == 1)
                $where["m.is_show"] = 1;
            elseif ($is_recommend == 2)
                $where["m.is_show"] = 0;
        }
        $where["m.game_type"] = $this->itemId;
        //$matchModel = new matchModel();
        $list = model('match')->getMatchListByWhere($where, 18, "m.id desc", $param);
        $system = config("system");
        $this->assign('domain', $system['upload_local_domain']);

        $this->assign("is_hot", $is_hot);
        $this->assign("is_recommend", $is_recommend);
        $this->assign("is_show", $is_show);
        $this->assign("name", $name);
        $this->assign("list", $list);
        return $this->fetch('items/match');
    }

    /**
     * 赛事添加
     * @return mixed|void
     */
    public function match_add() {
        if ($this->request->isPost()) {
            $id = input("post.id", 0, "intval");
            $country_id = input("post.country");
            $logo = input("post.logo");
            $logo_hover = input("post.logo_hover");
            $name = input("post.name", '');
            $begin_time = strtotime(input("post.begin_time"));
            $end_time = strtotime(input("post.end_time"));
            $address = input("post.address");
            $is_hot = input("post.is_hot");
            $is_recommend = input("post.is_recommend");
            $is_show = input("post.is_show");
            $game_id = input("post.game_id/d");
            $explain = input("post.explain");
            $alias = trim(input("post.alias"));
            if ($id) {
                $data = array();
                $data["country_id"] = $country_id;
                if ($logo) {
                    $data["logo"] = $logo;
                }
                if ($logo_hover) {
                    $data["logo_hover"] = $logo_hover;
                }
                /* if(input("post.name")){
                  $data["name"] = trim(input("post.name"));
                  } */
                $data["begin_time"] = $begin_time;
                $data["end_time"] = $end_time;
                $data["address"] = $address;
                $data["explain"] = $explain;
                $data["is_hot"] = $is_hot;
                $data["is_recommend"] = $is_recommend;
                $data["is_show"] = $is_show;
                if ($alias)
                    $data["alias"] = $alias;
                if (model('match')->updateMatch($data, $id)) {
                    model("match")->upCacheOnly($id);
                    return $this->success("编辑成功");
                } else {
                    return $this->error("编辑失败");
                }
            } else {
                $data = array();
                if ($alias)
                    $data["alias"] = $alias;
                $data["country_id"] = $country_id;
                $data["game_id"] = $country_id;
                if ($logo) {
                    $data["logo"] = $logo;
                }
                if (!$name) {
                    return $this->error("请填写赛事名称");
                }

                $data["game_type"] = $this->itemId;
                $data["name"] = $name;
                $data["game_id"] = $game_id;
                $data["begin_time"] = $begin_time;
                $data["end_time"] = $end_time;
                $data["address"] = $address;
                $data["explain"] = $explain;
                $data["is_hot"] = $is_hot;
                $data["is_show"] = $is_show;
                if (model('match')->save($data)) {
                    $id = model('match')->id;
                    model("match")->upCacheOnly($id);
                    return $this->success("添加成功");
                } else {
                    return $this->error("添加失败");
                }
            }
            return $this->error("参数异常");
        }

        $info = [];
        $id = input("id", 0, "intval");
        if ($id) {
            $info = model('match')->getMatchInfoByID($id);
            $info['game'] = getGame($info['game_id']);
        }
        $country = Db::name('country')->select();

        $this->assign("info", $info);
        $this->assign("country", $country);
        return $this->fetch('items/match_add');
    }

    /**
     * 队伍管理
     */
    public function teams() {
        $team_name = isset($_GET["item"]) ? trim($_GET["item"]) : "";

        $whereData = [];
        if ($team_name) {
            $whereData["t.name"] = ["like", "%{$team_name}%"];
        }
        $whereData['t.game_type'] = $this->itemId;
        $param = input("param.");
        $system = config("system");
        $this->assign('domain', $system['upload_local_domain']);

        $list = model('team')->getTeamListByWhere($whereData, 10, "t.id desc", $param);
        $this->assign("list", $list);
        $this->assign("item", $team_name);
        $this->assign("param", $param);

        return $this->fetch('items/teams');
    }

    /**
     * 队伍管理
     */
    public function teams_add() {
        if ($this->request->isPost()) {
            $id = input("id");
            $name = input("name");
            $country_id = input("country");
            $logo = input("logo");
            $alias = trim(input("alias"));
            if (!$name) {
                $this->error("队伍名称不能为空");
            }
            if ($id) {
                $data = array();
                $data["name"] = $name;
                $data["country_id"] = $country_id;
                $data["logo"] = $logo;
                $data['game_type'] = $this->itemId;
                $data["alias"] = $alias;
                if (false !== model("team")->updateTeam($data, $id)) {
                    model("team")->upCacheOnly($id);
                    $this->success("编辑成功");
                } else {
                    $this->error("编辑失败");
                }
            } else {
                $res = model("team")->where(['name' => $name])->find();
                if ($res) {
                    return $this->error("队伍名称已存在");
                }
                $data = array();
                $data["name"] = $name;
                $data["country_id"] = $country_id;
                $data["logo"] = $logo;
                $data['game_type'] = $this->itemId;
                $data["alias"] = $alias;
                if (false !== model("team")->save($data)) {
                    $id = model("team")->id;
                    model("team")->upCacheOnly($id);
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            }
        }

        $id = input("id", 0, 'intval');
        $team_info = [];
        if ($id) {
            $team_info = model("Team")->where(['id' => $id, 'game_type' => $this->itemId])->find();
        }
        $country = Db::name('country')->select();
        $this->assign("country", $country);
        $this->assign("team_info", $team_info);
        $this->assign("id", $id);

        return $this->fetch('items/teams_add');
    }

    /**
     * 比赛玩法管理
     */
    public function play_rule() {
        if ($this->request->isPost()) {
            $play_id = input('post.play_id');
            $sort = input('post.sort/a');
            $arena = input('post.arena/a');
            //获取当前比赛下的玩法信息
            $playRules = Db::name("play_rules")->order("sort asc")->where(['play_id' => $play_id])->select();
            $temp = [];
            foreach ($playRules as $val) {
                $temp[$val['rules_id']] = $val;
            }
            foreach ($sort as $key => $val) {
                $val = intval($val) ? $val : 999;
                if (isset($temp[$key])) {
                    Db::name('play_rules')->where(['play_id' => $play_id, 'rules_id' => $key])->update([
                        'sort' => $val,
                        'arena_id' => isset($arena[$key]) ? intval($arena[$key]) : 0
                    ]);
                } else {
                    Db::name('play_rules')->insert([
                        'play_id' => $play_id,
                        'rules_id' => $key,
                        'sort' => $val,
                        'arena_id' => isset($arena[$key]) ? intval($arena[$key]) : 0
                    ]);
                }
            }
            Db::name('play')->where(['id' => $play_id])->update(['has_play_rules' => 1]);
            return $this->success("操作成功", "");
        }
        $play_id = input('play_id');
        if (!$play_id) {
            return $this->error("无效比赛信息");
        }
        $playModel = model("play")->get($play_id);
        $play = $playModel->toArray();
        $play['match'] = $playModel->match->toArray();
        //该分类下所有玩法
        $rules = getRuleData($play['game_type'], null, null, true, true, $play['game_id']);
        //var_dump($rules);
        //获取当前比赛下的玩法信息
        $playRules = Db::name("play_rules")->order("sort asc")->where(['play_id' => $play_id])->select();
        $temp = [];
        $data = [];
        foreach ($playRules as $val) {
            $temp[$val['rules_id']] = $val;
            $data[$val['rules_id']] = $rules[$val['rules_id']];
            unset($rules[$val['rules_id']]);
        }
        if ($rules) {
            $data = array_merge($data, $rules);
        }
        /* foreach ($data as $k=>$v){
          $list = Db::name("arena")->where(['play_id' => $play_id,"rules_id"=>$v["id"]])->select();
          $v["arena"] = $list;
          $data[$k] = $v;
          } */
        $this->assign('play', $play);
        $this->assign('rules', $data);
        $this->assign('playRules', $temp);
        return $this->fetch('items/play_rule');
    }

    /**
     * 擂台封擂
     */
    public function sealArena() {
        if ($this->request->isPost()) {
            $id = input("post.id/d", 0, 'intval'); //擂台ID
            $playId = input("post.play_id/d", 0, 'intval'); //比赛ID
            if ($id) {
                $arenaSvr = new \library\service\Arena($id);
                $arenaSvr->admin_id = $this->admin_id;
                if (!$result = $arenaSvr->sealArena($id, 0)) {
                    return $this->error($arenaSvr->getError());
                }
            } elseif ($playId) {
                $arenaSvr = new \library\service\Arena();
                $arenaSvr->admin_id = $this->admin_id;
                $arenaSvr->sealPlayArena($playId);
            }
            Cache::rm('play_cache_list');
            Cache::rm('match_list');
            return $this->success("封擂成功!");
        }
    }

    /**
     * 擂台解封
     */
    public function unsealArena() {
        if ($this->request->isPost()) {
            $id = input("post.id", 0, 'intval');
            $arenaSvr = new \library\service\Arena($id);
            $arenaSvr->admin_id = $this->admin_id;
            if (!$arenaSvr->unsealArena($id, 0)) {
                return $this->error($arenaSvr->getError());
            }
            Cache::rm('match_list');
            Cache::rm('play_cache_list');
            return $this->success("擂台解封成功!");
        }
    }

    /**
     * 擂台取消
     */
    public function arena_disabled() {
        if ($this->request->isPost()) {
            $id = input("post.id/d", 0, 'intval'); //擂台ID
            $libs = input("post.libs/a"); //擂台ID
            $playId = input("post.play_id/d", 0, 'intval'); //比赛ID
            if ($id) {
                $arenaSvr = new \library\service\Arena($id, $this->admin_id);
                if (false === $result = $arenaSvr->disabled($id)) {
                    return $this->error($arenaSvr->getError());
                }
                Cache::rm('match_list');
                Cache::rm('play_cache_list');
                return $this->success("房间取消成功!");
            } elseif ($playId) {
                $arenaSvr = new \library\service\Arena();
                $arenaSvr->admin_id = $this->admin_id;
                if (false === $result = $arenaSvr->disabledPlay($playId)) {
                    return $this->error($arenaSvr->getError());
                }
                Cache::rm('match_list');
                Cache::rm('play_cache_list');
                return $this->success("房间取消成功!");
            } elseif ($libs) {
                $err = '';
                foreach ($libs as $id) {
                    $arenaSvr = new \library\service\Arena($id, $this->admin_id);
                    if (false === $result = $arenaSvr->disabled($id)) {
                        $err .= "<br/>房间ID：{$id}," . $arenaSvr->getError();
                    }
                }
                Cache::rm('match_list');
                Cache::rm('play_cache_list');
                return $this->success("房间批量取消成功!{$err}");
            }
        }
    }

    /**
     * 擂台删除
     */
    public function arena_del() {
        if ($this->request->isPost()) {
            $id = input("post.id", 0, 'intval');
            $libs = input("post.libs/a"); //擂台ID
            if ($id) {
                $arenaSvr = new \library\service\Arena($id, $this->admin_id);
                if (false === $result = $arenaSvr->del($id)) {
                    return $this->error($arenaSvr->getError());
                }
                Cache::rm('match_list');
                Cache::rm('play_cache_list');
                return $this->success("房间删除成功!");
            } elseif ($libs) {
                $err = '';
                foreach ($libs as $id) {
                    $arenaSvr = new \library\service\Arena($id, $this->admin_id);
                    if (false === $result = $arenaSvr->del($id)) {
                        $err .= "<br/>房间ID：{$id}," . $arenaSvr->getError();
                    }
                }
                Cache::rm('match_list');
                Cache::rm('play_cache_list');
                return $this->success("房间批量删除成功!{$err}");
            }
        }
    }

    /**
     * 默认擂台
     */
    public function rdef() {
        if ($this->request->isPost()) {
            $id = input("id", 0, 'intval');
            $arena = (new \library\service\Arena())->getCacheArenaById($id);
            if (!$arena) {
                return $this->error("设置失败!");
            }

            if ($arena['private'] != ARENA_DISPLAY_ALL) {
                return $this->error("公开擂台才能设置为默认擂台!");
            }


            Db::name('arena')->where(['play_id' => $arena['play_id'], 'rules_id' => $arena['rules_id']])->update([
                'has_default' => STATUS_NO
            ]);
            Db::name('arena')->where(['id' => $id])->update([
                'has_default' => STATUS_YES
            ]);
            $playRules = Db::name('play_rules')->where(['play_id' => $arena['play_id'], 'rules_id' => $arena['rules_id']])->find();
            if ($playRules) {
                Db::name('play_rules')->where(['play_id' => $arena['play_id'], 'rules_id' => $arena['rules_id']])->update([
                    'arena_id' => $id
                ]);
            } else {
                Db::name('play_rules')->insert([
                    'play_id' => $arena['play_id'],
                    'rules_id' => $arena['rules_id'],
                    'arena_id' => $id,
                ]);
                Db::name("play")->where(['id' => $arena['play_id']])->update(['has_play_rules' => 1]);
                (new Play())->upCache($arena['play_id']);
            }
            return $this->success("设置成功!");
        }
    }

    /**
     * 推荐擂台
     */
    public function arena_recommend() {
        if ($this->request->isPost()) {
            $id = input("id", 0, 'intval');
            $arena = (new \library\service\Arena())->getCacheArenaById($id);
            if (!$arena) {
                return $this->error("获取擂台数据失败");
            }
            if ($arena['private'] != ARENA_DISPLAY_ALL) {
                return $this->error("公开擂台才能设置为推荐擂台!");
            }
            $res = Db::name('arena_recommend')->where(['arena_id' => $id])->find();
            if ($res) {
                return $this->error("当前擂台已推荐");
            }
            Db::name('arena_recommend')->insert([
                'arena_id' => $id,
                'create_time' => time(),
            ]);
            Db::name('arena')->where(['id' => $id])->update(['has_recommend' => 1]);
            (new Arena())->cacheArena($id);
            Cache::rm('play_cache_list');
            return $this->success("推荐成功");
        }
    }

    /**
     * 推荐擂台
     */
    public function un_arena_recommend() {
        if ($this->request->isPost()) {
            $id = input("id", 0, 'intval');
            $arena = (new \library\service\Arena())->getCacheArenaById($id);
            if (!$arena) {
                return $this->error("获取擂台数据失败");
            }
            Db::name('arena_recommend')->where(['arena_id' => $id])->delete();
            Db::name('arena')->where(['id' => $id])->update(['has_recommend' => 0]);
            Cache::rm('play_cache_list');
            return $this->success("取消推荐成功");
        }
    }

    /**
     * 推荐比赛
     */
    public function recommend() {
        if ($this->request->isPost()) {
            $play_id = input("post.play_id/d");
            $is_recommend = input("post.is_recommend/d");
            if (!$play_id) {
                return $this->error("参数异常");
            }
            if (\think\Db::name('play')->where("id", $play_id)->update(["is_recommend" => $is_recommend])) {
                return $this->success("操作成功");
            } else {
                return $this->error("操作失败");
            }
        }
    }

    /**
     * 批量操作
     */
    public function batch_opt() {
        $batch_opt_type = input('batch_opt_type');
        switch (strtolower($batch_opt_type)) {
            case 'cancel_arena':
                if (!checkPermit($this->controller, 'arena_disabled')) {
                    return $this->error('你无权限访问和操作当前地址');
                }
                return $this->arena_disabled();
            case 'del_arena':
                if (!checkPermit($this->controller, 'arena_del')) {
                    return $this->error('你无权限访问和操作当前地址');
                }
                return $this->arena_del();
        }
        return $this->error('参数异常');
    }

    public function play_hot() {
        if ($this->request->isPost()) {
            $play_id = input("play_id/d");
            $value = input("value/d");
            if (!$play_id) {
                return $this->error("参数异常");
            }
            if (false !== \think\Db::name('play')->where("id", $play_id)->update(["hot" => $value])) {
                return $this->success("操作成功");
            } else {
                return $this->error("操作失败");
            }
        }
    }

}
