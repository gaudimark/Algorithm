<?php
namespace app\user\controller;

use library\service\Arena;
use library\service\Log;
use library\service\Misc;
use library\service\Passport;
use library\service\Play;
use library\service\Rule;
use library\service\Socket;
use library\service\Task;
use library\service\User;
use think\Db;

class My extends \app\user\logic\User
{
    public function __construct()
    {
        parent::__construct();

    }

    public function info(){
        $user = Db::name('user')->where(['id' => $this->myUserId])->field('gold,bank_gold')->find();
        return $this->retSucc('user.my_info', $user, '', []);
    }

    /**
     * 我的投注
     */
    public function bet()
    {
        $page = max(1, input("page/d"));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $arenaSvr = (new Arena());
        $playSvr = (new Play());//$this->myUserId
        $lists = Db::name("arena_bet_detail")->where(['user_id' => $this->myUserId])->limit($offset, $limit)->order("create_time desc")->select();
        foreach ($lists as $key => $val) {
            $arena = $arenaSvr->getCacheArenaById($val['arena_id']);
            if (!$arena){
                unset($lists[$key]);
                continue;
            }
            $ruleSvr = (new Rule())->factory($arena['game_type']);
            $play = $playSvr->getPlay($arena['play_id'], ['id', 'play_time', 'status', 'match_time','bo']);
            if(!$play){
                unset($lists[$key]);
                continue;
            }
            $val['win_money'] = floatval($val['win_money']);
            if ($val['win_money'] > 0 && isset($arena['classify']) && $arena['classify'] == ARENA_CLASSIFY_CREDIT){ //征信局扣除本金
                $val['win_money'] = $val['win_money'] - $val['money'];
            }
            $val['win_time'] = intval($val['win_time']);
            $val['follow_user_id'] = intval($val['follow_user_id']);
            $val['follow_bet_id'] = intval($val['follow_bet_id']);
            $teams = $playSvr->getTeams($arena['play_id'], ['id', 'name', 'logo', 'has_home', 'score']);
            $val['bet_target'] = (new Rule())->getBetTargetText($arena['game_type'], $arena['rules_type'], $arena['play_id'], $teams, $val['target'], $val['item'], $arena['rules_id']);
            //$val['under'] = $val['over'] = isset($arena['odds']['under']) ? $arena['odds']['under'] : '';
            //$val['rule_type'] = $val['over'] = isset($arena['odds']['under']) ? $arena['odds']['under'] : '';
            if ($play['status'] == PLAT_STATUS_START){
                $play['match_time'] = getMatchRunTime($play['match_time'], $play['play_time']);
            }

            if($arena['game_type'] == GAME_TYPE_WCG && $play['bo'] && $play['bo'] > 0){
                $play['bo'] = "BO{$play['bo']}";
            }else{
                $play['bo'] = '';
            }

            $val['play_time'] = $play['play_time'];
            $val['play'] = $play;
            $val['match_name'] = getMatch($arena['match_id'], 'name');
            $val['teams'] = $teams;
            $val['fee'] = floatval($val['fee']);
            $val['item_id'] = $arena['game_type'];
            $val['item_name'] = getSport($arena['game_type']);
            $val['rules_id'] = $arena['rules_id'];
            $val['rules_type'] = $arena['rules_type'];//$ruleSvr->getRuleType($arena['rules_id']);
            $val['handicap'] = $val['item_id'] == GAME_TYPE_FOOTBALL && $arena['rules_type'] == RULES_TYPE_ASIAN ? $ruleSvr->handicap($val['handicap'], false) : $val['handicap'];

            unset($val['agent_id']);
            unset($val['agent_remark']);
            unset($val['agent_sign']);
            unset($val['item']);
            $lists[$key] = $val;
        }
        $next_page = count($lists) >= $limit ? 1 : 0;
        return $this->retSucc('user.my_bet', $lists, '', ['next_page' => $next_page]);
    }

    /**
     * 投注详情
     */
    public function bet_detail()
    {
        $bet_id = input("bet_id/d", 0);
        $bet = Db::name("arena_bet_detail")->where(['user_id' => $this->myUserId, 'id' => $bet_id])->find();
        if (!$bet){
            return $this->retErr("user.my_bet_detail", 10013);
        }
        $arenaSvr = (new Arena());
        $playSvr = (new Play());
        $arena = $arenaSvr->getCacheArenaById($bet['arena_id']);
        if (!$arena){
            return $this->retErr("user.my_bet_detail", 10013);
        }

        $ruleSvr = (new Rule())->factory($arena['game_type']);
        $ruleType = $arena['rules_type'];
        $user = getUser($this->myUserId);

        $teams = $playSvr->getTeams($arena['play_id'], ['id', 'name', 'logo', 'has_home', 'score']);
        $play = $playSvr->getPlay($arena['play_id'], ['id', 'play_time', 'status', 'match_time','bo']);


        if($arena['game_type'] == GAME_TYPE_WCG && $play['bo'] && $play['bo'] > 0){
            $play['bo'] = "BO{$play['bo']}";
        }else{
            $play['bo'] = '';
        }


        $bet['classify'] = $arena['classify'];
        $bet['play_time'] = $play['play_time'];
        $bet['fee'] = floatval($bet['fee']);
        $bet['win_money'] = floatval($bet['win_money']);
        if ($bet['win_money'] > 0 && isset($arena['classify']) && $arena['classify'] == ARENA_CLASSIFY_CREDIT){ //征信局扣除本金
            $bet['win_money'] = $bet['win_money'] - $bet['money'];
        }
        $bet['win_time'] = intval($bet['win_time']);
        $bet['follow_user_id'] = intval($bet['follow_user_id']);
        $bet['follow_bet_id'] = intval($bet['follow_bet_id']);
        $bet['max_buy_price'] = isset($user['level']['look']) ? $user['level']['look'] : 1; //最高可设置查看价格
        $bet['bet_target'] = (new Rule())->getBetTargetText($arena['game_type'], $arena['rules_type'], $arena['play_id'], $teams, $bet['target'], $bet['item'], $arena['rules_id']);

        $win = forWin($bet['money'], $bet['odds'], $ruleType, $bet['brok'], $arena['game_type']);
        if ($arena['classify'] == ARENA_CLASSIFY_CREDIT){
            $bet['exp_win'] = $win['win_money'] - $bet['money'];
        } else {
            $bet['exp_win'] = $win['win_money'];
        }

        $bet['exp_fee'] = $win['brok'];//forBrokerage(forWin($bet['money'],$bet['odds'],$ruleType,false,true,$arena['game_type']));

        $bet['item_id'] = $arena['game_type'];
        $bet['rules_id'] = $arena['rules_id'];
        $bet['rules_type'] = $arena['rules_type'];
        $bet['handicap'] = $arena['game_type'] == GAME_TYPE_FOOTBALL && $arena['rules_type'] == RULES_TYPE_ASIAN ? $ruleSvr->handicap($bet['handicap'], false) : $bet['handicap'];
        //$bet['under'] = $bet['over'] = isset($bet['under']) ? $arena['odds']['under'] : '';
        if ($play['status'] == PLAT_STATUS_START){
            $play['match_time'] = getMatchRunTime($play['match_time'], $play['play_time']);
        }
        $bet['match_name'] = getMatch($arena['match_id'], 'name');
        $bet['play'] = $play;
        $bet['teams'] = $teams;
        return $this->retSucc('user.my_bet_detail', $bet);

    }

    /**
     * 修改投注单查看价格
     */
    public function modify_bet_price()
    {
        if ($this->request->isPost()){
            $bet_id = input("bet_id/d", 0);
            $price = input("price/d", 0);
            $arenaSvr = (new Arena());
            if ($arenaSvr->upArenaDetailBuyPrice($bet_id, $this->myUserId, $price)){
                return $this->retSucc('user.my_modify_bet_price', ['price' => $price]);
            } else {
                return $this->retErr('user.my_modify_bet_price', $arenaSvr->getError());
            }
        }
    }

    /**
     * 好友列表
     */
    public function friend()
    {
        $apply = input("apply/d");
        $page = max(1, input("page/d"));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $where = [];
        $where['user_id'] = $this->myUserId;
        $where['status'] = 1;
        $lists = Db::name('user_friend')->limit($offset, $limit)->where($where)->order("id desc")->select();
        foreach ($lists as $key => $val) {
            $user = getUser($val['user_friend_id'], null, true, ['id', 'nickname', 'avatar', 'level', 'status']);
            if (!$user){
                unset($lists[$key]);
                continue;
            }
            $user['friend_id'] = $val['id'];
            $lists[$key] = $user;

        }

        //$total = cache("user_my_friend_total");
        //if(!$total){
        $total = Db::name('user_friend')->where($where)->count();
        //cache("user_my_friend_total",$total,600);
        //}
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('user.my_friend', $lists, '', ['next_page' => $next_page]);
    }

    /**
     * 好友申请列表
     */
    public function apply_friend_list()
    {
        $apply = input("apply/d");
        $page = max(1, input("page/d"));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $where = [];
        $where['user_friend_id'] = $this->myUserId;
        $where['status'] = 0;
        $lists = Db::name('user_friend')->limit($offset, $limit)->where($where)->order("id desc")->select();
        $remove = [];
        foreach ($lists as $key => $val) {
            $user = getUser($val['user_id'], null, true, ['id', 'nickname', 'avatar', 'level', 'status']);
            if (!$user){
                unset($lists[$key]);
                continue;
            }
            $user['apply_id'] = $val['id'];
            $lists[$key] = $user;
        }

        //$total = cache("user_my_apply_friend_list_total");
        //if(!$total){
        $total = Db::name('user_friend')->where($where)->count();
        //cache("user_my_apply_friend_list_total",$total,600);
        //}
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('user.my_apply_friend_list', $lists, '', ['next_page' => $next_page]);
    }

    /**
     * 处理好友申请
     */
    public function apply_friend()
    {
        if ($this->request->isPost()){
            $apply_id = input("apply_id/d");
            if (!$apply_id){
                return $this->retErr('user.my_apply_friend', 10004);
            }
            $user = new User();
            if (true === $ret = $user->applyFriend($apply_id)){
                return $this->retSucc("user.my_apply_friend", [], 20098);
            } else {
                return $this->retErr("user.my_apply_friend", $ret);
            }
        }
        return $this->retErr('user.my_apply_friend', 10000);
    }

    /**
     * 好友申请未处理数量
     */
    public function apply_friend_total()
    {
        $where = [];
        $where['user_friend_id'] = $this->myUserId;
        $where['status'] = 0;
        $total = Db::name('user_friend')->where($where)->count();
        return $this->retSucc('user.my_apply_friend_total', ['total' => $total]);
    }

    /**
     * 添加好友
     */
    public function add_friend()
    {
        if ($this->request->isPost()){
            $user_id = input("user_id/d");
            if (!$user_id){
                return $this->retErr('user.my_add_friend', 10004);
            }

            $user = new User();
            if (true === $ret = $user->addFriend($user_id, $this->myUserId)){
                return $this->retSucc("user.my_add_friend", [], 20093);
            } else {
                return $this->retErr("user.my_add_friend", $ret);
            }
        }
        return $this->retErr('user.my_add_friend', 10000);
    }

    /**
     * 取消，拒绝好友
     */
    public function un_friend()
    {
        if ($this->request->isPost()){
            $friend_id = input("friend_id/d");
            $type = input("type/d");
            if (!$friend_id){
                return $this->retErr('user.my_un_friend', 10004);
            }

            $user = new User();
            if (true === $ret = $user->unFriend($friend_id, $this->myUserId)){
                return $this->retSucc("user.my_un_friend", [], $type ? 20105 : 20094);
            } else {
                return $this->retErr("user.my_un_friend", $ret);
            }
        }
        return $this->retErr('user.my_un_friend', 10000);
    }

    /**
     * 关注列表
     */
    public function follow()
    {
        $page = max(1, input("page/d"));
        $type = input("type/d");
        if (!in_array($type, [FOLLOW_TYPE_USER, FOLLOW_TYPE_TEAM])){
            $type = FOLLOW_TYPE_USER;
        }
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $where = [];
        $where['user_id'] = $this->myUserId;
        $where['type'] = $type;
        $lists = Db::name('user_follow')->limit($offset, $limit)->where($where)->order("id desc")->select();
        foreach ($lists as $key => $val) {
            $temp = [];
            if ($type == FOLLOW_TYPE_USER){
                $temp = getUser($val['user_follow_id'], null, true, ['id', 'nickname', 'avatar', 'level', 'status']);
            } elseif ($type == FOLLOW_TYPE_TEAM) {
                $temp = getTeam($val['user_follow_id'], false, ['id', 'name', 'logo', 'logo_big']);
            }
            if (!$temp){
                unset($lists[$key]);
            }
            $lists[$key] = $temp;
        }
        $total = Db::name('user_follow')->where($where)->count();
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('user.my_follow', array_values($lists), '', ['next_page' => $next_page, 'type' => $type]);
    }

    /**
     * 关注
     */
    public function add_follow()
    {
        if ($this->request->isPost()){
            $targetId = input("follow_user_id/d");
            $targetType = input("type/d");
            if (!$targetId){
                return $this->retErr('user.my_add_follow', 10004);
            }
            if (!$targetType){
                $targetType = FOLLOW_TYPE_USER;
            }

            $user = new User();
            if (true === $ret = $user->addFollow($targetId, $targetType, $this->myUserId)){
                return $this->retSucc("user.my_add_follow", [], 20096);
            } else {
                return $this->retErr("user.my_add_follow", $ret);
            }
        }
        return $this->retErr('user.my_add_follow', 10000);
    }

    /**
     * 关注
     */
    public function un_follow()
    {
        if ($this->request->isPost()){
            $user_id = input("follow_user_id/d");
            $targetType = input("type/d");
            if (!$user_id){
                return $this->retErr('user.my_un_follow', 10004);
            }
            if (!$targetType){
                $targetType = FOLLOW_TYPE_USER;
            }
            $user = new User();
            if (true === $ret = $user->unFollow($user_id, $targetType, $this->myUserId)){
                return $this->retSucc("user.my_un_follow", [], 20097);
            } else {
                return $this->retErr("user.my_un_follow", $ret);
            }
        }
        return $this->retErr('user.my_un_follow', 10000);
    }

    /**
     * 帐户记录
     */
    public function log_funds()
    {
        $page = max(1, input("page/d", 0, 'intval'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $where = [];
        $where["user_id"] = $this->myUserId;

        $lists = Db::name('user_funds_log')->where($where)->order("create_time desc,id desc")->limit($offset, $limit)->select();
        foreach ($lists as $key => $val) {
            unset($val['before_num']);
            unset($val['after_num']);
            unset($val['data']);
            //$val['data'] = @json_decode($val['data']);
            $lists[$key] = $val;
        }
        //$total = cache("user_my_log_funds_total");
        //if(!$total){
        $total = Db::name('user_funds_log')->where($where)->count();
        //cache("user_my_log_funds_total",$total,600);
        // }
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('user.my_log_funds', $lists, '', ['next_page' => $next_page]);
    }

    /**
     * 我的消息
     */
    public function message()
    {
        $page = max(1, input("page/d", 0, 'intval'));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        if ($page == 1){
            Db::name('sys_message_detail')->where(['user_id' => $this->myUserId])->update(['is_read' => 1, 'read_time' => time(),]);
            Db::name('user')->where(['id' => $this->myUserId])->update(['sys_message_total' => 0]);
            //(new User())->upUserCache($this->myUserId, 'sys_message_total', 0);
        }
        $lists = Db::name('sys_message_detail')->alias("md")
            ->join("__SYS_MESSAGE__ m", "md.message_id = m.id", 'LEFT')
            ->where(['md.user_id' => $this->myUserId, 'm.id' => ['gt', 0]])
            ->field('m.id,m.title,m.content,m.create_time,md.is_read,md.read_time')
            ->order("m.create_time desc")
            ->limit($offset, $limit)->select();
        $isRead = 0;
        $ids = [];
        foreach ($lists as $val) {
            if (!$val['is_read']){
                $ids[] = $val['id'];
                $isRead = 1;
            }
        }
        $total = Db::name('sys_message_detail')->alias("md")->join("__SYS_MESSAGE__ m", "md.message_id = m.id", 'LEFT')->where(['md.user_id' => $this->myUserId, 'm.id' => ['gt', 0]])->field('m.id,m.title,m.content,m.create_time,md.is_read,md.read_time')->count();
        $total_pages = ceil($total / $limit);
        $next_page = $total_pages > $page ? 1 : 0;
        return $this->retSucc('user.my_message', $lists, '', ['next_page' => $next_page]);
    }

    public function message_del(){
        if ($this->request->isPost()){
            $messageId = input('msg_id');
            if($messageId) {
                Db::name('sys_message_detail')->where(['user_id' => $this->myUserId,'message_id' => $messageId])->delete();
            }else{
                Db::name('sys_message_detail')->where(['user_id' => $this->myUserId])->delete();
            }
            return $this->retSucc('user.my_message_del',[
                'msg_id' => (int)$messageId
            ],9999);
        }

        return $this->retErr('user.my_message_del', 10000);
    }


    public function sys_task()
    {
        if ($this->request->isPost()){
            $taskId = input("task_id/d");
            if (true === $retCode = (new Task())->runByTaskId($taskId, ['user_id' => $this->myUserId])){
                return $this->retSucc('user.my_sys_task', [], 'OK');
            } else {
                return $this->retErr('user.my_sys_task', $retCode);
            }
        }
    }

    /**
     * 更新用户头像
     */
    public function avatar()
    {
        if ($this->request->isPost()){
            $id = input("id/d");
            if ($id){
                $file = "avatar/{$id}.png";
                Db::name('user')->where(['id' => $this->myUserId])->update(['avatar' => $file]);
                (new User())->upUserCache($this->myUserId, 'avatar', $file);
                //更新Token中和用户头像
                $user = cache("user_sys_{$this->token}");
                if($user){
                    $user['avatar'] = getUserAvatar($file,$this->myUserId);
                    cache("user_sys_{$this->token}",$user,86400);
                }
                //(new User())->setCacheUser($this->myUserId);
            }
            return $this->retSucc('user.my_avatar', [], 'OK');
        }
        return $this->retErr('user.my_avatar', 10000);
    }

    /**
     * 查看投注内容
     */
    public function buy_bet()
    {
        if ($this->request->isPost()){
            $bet_id = input("post.bet_id"); //投注ID
            $bet_user_id = input("post.user_id"); //投注ID
            if (!$this->checkLogin()){
                return $this->retErr('user.my_buy_bet', '20040');
            }
            $userId = $this->getUserId();
            if (!$bet_id || !$userId){
                return $this->retErr('user.my_buy_bet', '10004');
            }

            $arenaSvr = new \library\service\Arena();
            if ($arenaSvr->buyBetView($userId, $bet_id)){

                $betDetail = Db::name('arena_bet_detail')->field("id,arena_id,target,item,handicap,under,over")->where(['id' => $bet_id])->find();
                $arena = $arenaSvr->getCacheArenaById($betDetail['arena_id']);
                $teams = (new Play())->getTeams($arena['play_id']);
                $bet_target = (new Rule())->getBetTargetText($arena['game_type'], $arena['rules_type'], $arena['play_id'], $teams, $betDetail['target'], $betDetail['item'], $arena['rules_id']);
                //Db::name('user')->where([''])

                return $this->retSucc('user.my_buy_bet', ['has_buy' => 1, 'user_id' => $bet_user_id, 'bet_target' => $bet_target, 'handicap' => $betDetail['handicap'], 'under' => $betDetail['under'], 'over' => $betDetail['over'],], '10015');
            } else {
                $code = $arenaSvr->getError();
                $vars = $arenaSvr->getErrorData();
                return $this->retErr('user.my_buy_bet', $code, $vars);
            }

        }
        return $this->retErr('user.my_buy_bet', 10000);
    }

    /**
     * 申请成为房主
     */
    public function homeowner()
    {
        if ($this->request->isPost()){
            $userSvr = new User();
            if ($userSvr->Homeowner($this->myUserId)){
                return $this->retSucc('user.my_homeowner');
            } else {
                return $this->retErr('user.my_homeowner', $userSvr->getError(), $userSvr->getErrorData());
            }
        }
        return $this->retErr('user.my_homeowner', 10000);
    }

    /**
     * 取消房主
     */
    public function un_homeowner()
    {
        if ($this->request->isPost()){
            $userSvr = new User();
            if ($userSvr->unHomeowner($this->myUserId)){
                return $this->retSucc('user.my_un_homeowner');
            } else {
                return $this->retErr('user.my_un_homeowner', $userSvr->getError(), $userSvr->getErrorData());
            }
        }
        return $this->retErr('user.my_un_homeowner', 10000);
    }


}