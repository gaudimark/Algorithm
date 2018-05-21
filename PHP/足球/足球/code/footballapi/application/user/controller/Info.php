<?php
namespace app\user\controller ;
use app\library\logic\Safe;
use library\service\Arena;
use library\service\Rule;
use library\service\User;
use think;

class Info extends Safe{
    private $user_id = "";
    public function __construct(){
        parent::__construct();
    }

    //根据用户ID或昵称获取用户信息
    public function show(){
        $userId = input("user_id/d");
        $nickname = input("nickname");
        if(!$userId && !$nickname){
            return $this->retErr('user.info_show',10004);
        }
        $fields = ['id','nickname','avatar','level','friends',
            'arena_total','deposit_total','win_total','most_win','record',
            'bet_view_total','profit'
        ];
        if($userId){
            $user = getUserField($userId,$fields);
        }elseif($nickname){
            $user = getUserByNickname($nickname,null,true,$fields);
        }

        if(!$user){
            return $this->retErr('user.info_show',20050);
        }
        $user['hit_rate'] = intval($user['win_total']) ? round(($user['win_total'] / intval($user['deposit_total'])) * 100,2) : 0.00;
        $user['relation']['friend'] = 0; //是否与当前登录用户是好友
        $user['relation']['follow'] = 0; //当前登录用户是否关注了当前用户
        $myUserId = $this->getUserId();
        if($myUserId){
            if (in_array($myUserId, $user['friends'])){
                $user['relation']['friend'] = 1;
            }
            $myUser = getUser($myUserId);
            if ($myUser && $myUser['follows'] && isset($myUser['follows'][FOLLOW_TYPE_USER]) && in_array($user['id'],$myUser['follows'][FOLLOW_TYPE_USER])){
                $user['relation']['follow'] = 1;
            }
        }

        return $this->retSucc('user.info_show',$user);

    }

    public function search(){
        $nickname = input("nickname");
        if(!$nickname){
            return $this->retErr('user.info_search',10004);
        }
        $myUserId = $this->getUserId();
        $friends = [];
        $follows = [];
        if($myUserId){
            $user = (new User())->getUser($myUserId);
            $friends = $user['friends'];
            $follows = $user['follows'];
        }
        $limit = 20;
        $where['nickname'] = ['like',"$nickname%"];
        $where['status'] = STATUS_ENABLED;
        $lists = think\Db::name('user')->field("id")->limit($limit)->where($where)->select();
        $temp = [];
        foreach($lists as $key => $val){
            $user = getUser($val['id'],null,true,['id','nickname','avatar','level','status']);
            $user['relation']['friend'] = 0; //是否与当前登录用户是好友
            $user['relation']['follow'] = 0; //当前登录用户是否关注了当前用户
            if(in_array($user['id'],$friends)){
                $user['relation']['friend'] = 1;
            }
            if(in_array($user['id'],$follows)){
                $user['relation']['follow'] = 1;
            }
            $temp[] = $user;
        }
        return $this->retSucc('user.info_search',$temp);
    }

    /**
     * 用户战绩
     */
    public function record(){
        $userId = input("user_id/d");
        $nickname = input("nickname");
        if(!$userId && !$nickname){
            return $this->retErr('user.info_record',10004);
        }
        if($userId){
            $user = getUser($userId);
        }elseif($nickname){
            $user = getUserByNickname($nickname);
        }

        if(!$user){
            return $this->retErr('user.info_record',20050);
        }

        $page = max(1,input("page/d",0,'intval'));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $lists = think\Db::name('arena_bet_detail')->where([
            'user_id' => $user['id'],
            'create_time' => ['egt',strtotime("-7 day")],
            'status' => DEPOSIT_NOT_START,
        ])->limit($offset,$limit)->order("create_time DESC")->select();

        $myUserId = $this->getUserId();
        $ids = [];
        $temp = [];
        $plays = [];
        $arenaSvr = (new Arena());
        foreach($lists as $val){
            $arena = $arenaSvr->getCacheArenaById($val['arena_id']);
            $play_id = $arena['play_id'];
            $ids[] = $val['id'];
            if(!isset($plays[$play_id])){
                $plays[$play_id] = (new \library\service\Play())->getTeams($play_id,['id','name','logo','has_home']);
            }
            $val['teams'] = $plays[$play_id];
            $val['sport_name'] = getSport($arena['game_type']);
            $val['bet_target'] = (new Rule())->getBetTargetText($arena['game_type'],$arena['rules_type'],$play_id,$plays[$play_id],$val['target'],$val['item']);
            $val['has_buy'] = 0;
            $val['match'] = getMatch($arena['match_id'],null,['id','name']);


            unset($val['arena_id']);
            unset($val['agent_id']);
            unset($val['agent_sign']);
            unset($val['agent_remark']);
            unset($val['odds']);
            unset($val['handicap']);
            unset($val['money']);
            unset($val['win_money']);
            unset($val['fee']);
            //unset($val['buy']);
            unset($val['follow_user_id']);
            unset($val['follow_bet_id']);

            if($myUserId == $userId){
                $val['has_buy'] = 1;
            }

            $temp[$val['id']] = $val;
        }
        $lists = $temp;
        $buyIds = [];
        //判断那些数据是已购买的
        if($myUserId && $ids && $myUserId != $userId){
            $buyLists = think\Db::name('arena_bet_view')->where(['buy_user_id' => $myUserId,'bet_id' => ['in',array_values($ids)]])->select();
            if($buyLists){
                foreach ($buyLists as $val){
                    if(isset($lists[$val['bet_id']])){
                        $buyIds[] = $val['bet_id'];
                        $lists[$val['bet_id']]['has_buy'] = 1;
                    }else{
                        $lists[$val['bet_id']]['bet_target'] = '';
                    }

                }
            }
        }
        foreach ($lists as $key => $val){
            if(!in_array($val['id'],$buyIds)){
                $lists[$key]['bet_target'] = '';
            }
        }
        return $this->retSucc('user.info_record',array_values($lists));
    }

    //用户的摆擂列表
    public function arena(){
        $userId = input("user_id/d");
        $page = max(1,input("page/d"));
        $limit = 10;

        if(!$userId){
            return $this->retErr('user.info_record',10004);
        }
        $offset = ($page - 1) * $limit;

        $lists = think\Db::name('arena')->where(['user_id' => $userId])->limit($offset,$limit)->order("id desc")->select();
        dump($lists);


    }

    public function game_vote(){
        $userId = $this->getUserId('sys');
        $gameId = input("game_id/d",0);
        $item = input("item/d",0);
        if(!$userId || !$gameId){
            return $this->retErr('user.game_vote',500);
        }

        $user = think\Cache::hGet("game_vote_{$gameId}",$userId);
        if($user){
            return $this->retSucc('user.game_vote',9999);
        }
        $res = think\Db::name('vote_game')->where(['index' => date('Ymd'),'game_id' => $gameId])->find();

        if($res) {
            $data = [
                'update_time' => time()
            ];
            if($item == 1){
                $data['yes_total'] = ['exp','yes_total+1'];
            }else{
                $data['no_total'] = ['exp','no_total+1'];
            }
            think\Db::name('vote_game')->where(['index' => date('Ymd'),'game_id' => $gameId])->update($data);
        }else{
            $data = [
                'index' => date('Ymd'),
                'game_id' => $gameId,
                'create_time' => time(),
                'update_time' => time()
            ];
            if($item == 1){
                $data['yes_total'] = 1;
            }else{
                $data['no_total'] = 1;
            }
            think\Db::name('vote_game')->insert($data);
        }
        think\Cache::hSet("game_vote_{$gameId}",$userId,1);
        return $this->retSucc('user.info_game_vote',9999);
    }



}