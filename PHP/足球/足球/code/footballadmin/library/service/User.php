<?php
/**
 * 用户接口
 */

namespace library\service;
use org\Crypt;
use org\Stringnew;
use think\Cache;
use think\Db;
use think\Exception;

class User{
    private $upCacheAll = false;
    private $error = '';
    private $errorData = [];
    /**
     * 添加好友
     * @param $friend_user_id
     * @param $my_user_id
     * @return bool|int
     */
    public function addFriend($friend_user_id,$my_user_id){
        $user = $this->getUser($my_user_id);
        //检查双方是否已是好友
        if(in_array($friend_user_id,$user['friends'])){
            return 20092;
        }

        if($friend_user_id == $my_user_id){
            return 20091;
        }


        $friendUser = $this->getUser($friend_user_id);
        if(!$friendUser){
            return 10005;
        }
        $res = Db::name("user_friend")->where(['user_id' => $my_user_id,"user_friend_id" => $friend_user_id])->find();
        if($res){
            return 20090;
        }

        $res = Db::name("user_friend")->where(['user_friend_id' => $my_user_id,"user_id" => $friend_user_id])->find();
        if($res){
            return 20089;
        }

        $res = Db::name('user_friend')->insert(["user_id" => $my_user_id,"user_friend_id" => $friend_user_id,"status"=>0]);
        if($res){
            (new Socket())->sendToUid($friend_user_id,['type' => 'apply_friend','time' => time()],'socket.to_send_friend');
            $this->upUserCache($friend_user_id,'apply_friend_total',1,'inc');
            return true;
        }else{
            return 20091;
        }

    }

    /**
     * 解除好友关系
     * @param $friend_user_id
     * @param $my_user_id
     * @return bool
     * @throws \think\Exception
     */
    public function unFriend($friend_id,$my_user_id){
        $friend = Db::name("user_friend")->where(['user_friend_id' => $my_user_id,'user_id' => $friend_id])->find();
        if(!$friend){
            return 10013;
        }
        //$friend_user_id = $friend['user_friend_id'];

        Db::name("user_friend")->where(function($query) use($my_user_id,$friend_id){
            $query->where(['user_id' => $my_user_id,"user_friend_id" => $friend_id]);
        })->whereOr(function($query) use($my_user_id,$friend_id){
            $query->where(['user_friend_id' => $my_user_id,"user_id" => $friend_id]);
        })->delete();
        $this->upCacheUserFriend($friend_id);
        $this->upCacheUserFriend($my_user_id);
        return true;
    }

    public function applyFriend($applyID){
        $res = Db::name("user_friend")->where(['id' => $applyID])->find();
        if(!$res){
            return 10013;
        }
        if($res['status'] == 1){
            return 20092;
        }

        //更新好友状态
        Db::name("user_friend")->where(['id' => $res['id']])->update([
            'status' => 1,
        ]);
        //写入对方好友数据
        Db::name("user_friend")->insert([
            'user_id' => $res['user_friend_id'],
            'user_friend_id' => $res['user_id'],
            'status' => 1,
        ]);
        $this->upCacheUserFriend($res['user_friend_id']);
        $this->upCacheUserFriend($res['user_id']);
        return true;

    }

    /**
     * 关注
     * @param $follow_user_id
     * @param $my_user_id
     * @return bool|int
     */
    public function addFollow($follow_user_id,$targetType = FOLLOW_TYPE_USER,$my_user_id){
        $user = $this->getUser($my_user_id);
        //检查双方是否已是关注
        if($user['follows'] && isset($user['follows'][$targetType]) && in_array($follow_user_id,$user['follows'][$targetType])){
            return 20095;
        }

        if($follow_user_id == $my_user_id && $targetType == FOLLOW_TYPE_USER){
            return 20099;
        }

        if($targetType == FOLLOW_TYPE_USER){
            $followUser = $this->getUser($follow_user_id);
            if (!$followUser){
                return 10005;
            }
        }elseif($targetType == FOLLOW_TYPE_TEAM){
            $followUser = getTeam($follow_user_id);
            if (!$followUser){
                return 10005;
            }
        }

        $res = Db::name("user_follow")->where(['user_id' => $my_user_id,"user_follow_id" => $follow_user_id,'type' => $targetType])->find();
        if($res){
            return 20095;
        }
        $res = Db::name('user_follow')->insert(["user_id" => $my_user_id,"user_follow_id" => $follow_user_id,"status"=>1,'type' => $targetType]);
        $this->upCacheUserFollow($my_user_id);
        return true;
    }

    /**
     * 解除关注
     * @param $follow_user_id
     * @param $my_user_id
     * @return bool|int
     */
    public function unFollow($follow_user_id,$targetType = FOLLOW_TYPE_USER,$my_user_id){
        $res = Db::name('user_follow')->where(["user_id" => $my_user_id,"user_follow_id" => $follow_user_id,'type' => $targetType])->delete();
        $this->upCacheUserFollow($my_user_id);
        return true;
    }

    /**
     * 检查是否关注
     * @param $userId
     * @param $targetId
     * @param $type
     */
    public function checkFollow($userId,$targetId,$type){
        $user = $this->getUser($userId);
        $follow = isset($user['follows']) ? $user['follows'] : [];
        if($follow && isset($follow[$type]) && in_array($targetId,$follow[$type])){
            return true;
        }
        return false;
    }



    public function getUserByDb($user_id){
        $user_id = intval($user_id);
        return Db::name('user')->where(['id' => $user_id])->find();
    }

    public function getUser($user_id,$field = [],$avatarPrefix = true){
        $user = cache("user_{$user_id}");
        if(!$user){
            return $this->setCacheUser($user_id);
        }
        if($field && $user){
            $data = [];
            foreach($field as $val){
                if(array_key_exists($val,$user)){
                    $data[$val] = is_null($user[$val]) ? '' : $user[$val];
                }
            }
            $user = $data;
        }
        if(isset($user['avatar']) && $avatarPrefix){
            $user['avatar'] = getUserAvatar($user['avatar'],$user_id);
        }
        return $user;
    }

    public function setCacheUser($user_id){
        $user_id = intval($user_id);
        $user = Db::name("user")->where(["id" => $user_id])->find();
        if($user){
            $this->_setUserCache($user);
            return $user;
        }
        return false;
    }

    /**
     * 更新用户单个数据缓存
     * @param $userId
     * @param $key
     * @param $value
     */
    public function upUserCache($userId,$key,$value,$calc = ''){
        $user = $this->getUser($userId,[],false);
        if($user && is_array($user)){
            if($calc == 'inc'){
                $value = isset($user[$key]) ? $user[$key] : 0;
                $value = $value + 1;
            }elseif($calc == 'dec'){
                $value = isset($user[$key]) ? $user[$key] : 0;
                $value = $value - 1;
            }
            $user[$key] = $value;
            Cache("user_{$userId}",$user);
        }
    }

    public function setCacheAll(){
        $limit = 100;
        $page = 0;
        $this->upCacheAll = true;
        while(true){
            $offset = $page * $limit;
            $res = Db::name("user")->limit($offset,$limit)->field("*")->select();
            if($res){
                foreach($res as $val){
                   // Cache("user_{$val['id']}",null);
                    $val['record'] = $this->setUserDetailCache($val['id'],false);
                    $this->_setUserCache($val);
                }
            }else{
                break;
                return true;
            }
            $page++;
        }
        return true;
    }

    private function _setUserCache($user){
        unset($user['passport']);
        $user['task'] = [];
        $user['task_complete_total'] = 0;
        $user['customer_read'] = 0;
        //好友申请数量
        $where['user_friend_id'] = $user['id'];
        $where['status'] = 0;
        $total = Db::name('user_friend')->where($where)->count();
        $user['apply_friend_total'] = $total;
        $user = $this->upCacheUserFriend($user,true);
        $user = $this->upCacheUserFollow($user,true);
        $this->_cache($user);
    }

    /**
     * 更新好友缓存
     * @param $userId
     * @param bool $ret
     * @return array|bool|false|mixed|\PDOStatement|string|\think\Model
     */
    public function upCacheUserFriend($userId,$ret = false){
        $user = is_array($userId) ? $userId : $this->getUser($userId,[],false);
        //好友
        $friends = Db::name("user_friend")->where(['user_id' => $user['id'],'status' => 1])->column('user_friend_id');
        $user['friends'] = $friends;
        if($ret){
            return $user;
        }
        $this->_cache($user);
    }

    /**
     * 更新关注缓存
     * @param $userId
     * @param bool $ret
     * @return array|bool|false|mixed|\PDOStatement|string|\think\Model
     */
    public function upCacheUserFollow($userId,$ret = false){
        $user = is_array($userId) ? $userId : $this->getUser($userId,[],false);
        //关注
        $follows = Db::name("user_follow")->where(['user_id' => $user['id']])->field('type,user_follow_id')->select();
        $data = [];
        foreach($follows as $val) {
            $data[$val['type']][] = $val['user_follow_id'];
        }
        $user['follows'] = $data;
        if($ret){
            return $user;
        }
        $this->_cache($user);
    }

    //缓存用户其他详情数据
    public function setUserDetailCache($userId,$cache = true){
        $record = [];
        //战绩-近7天
        $weekTime = strtotime("-7 day");
        $allNum = (int)Db::name('arena_bet_detail')->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId])->count();
        $winNum = (int)Db::name("arena_bet_detail")->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId,"status"=>DEPOSIT_WIN])->count();
        $record['d7'] = ['total' => $allNum,'win' => $winNum];
        //战绩-近15天
       /* $weekTime = strtotime("-15 day");
        $allNum = (int)Db::name('arena_bet_detail')->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId])->count();
        $winNum = (int)Db::name("arena_bet_detail")->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId,"status"=>DEPOSIT_WIN])->count();
        $record['d15'] = ['total' => $allNum,'win' => $winNum];
        //战绩-近30天
        $weekTime = strtotime("-30 day");
        $allNum = (int)Db::name('arena_bet_detail')->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId])->count();
        $winNum = (int)Db::name("arena_bet_detail")->where(['status' => ['not in',[DEPOSIT_NOT_START,DEPOSIT_CANCEL]] ,"create_time"=>[">",$weekTime],"user_id"=>$userId,"status"=>DEPOSIT_WIN])->count();
        $record['d30'] = ['total' => $allNum,'win' => $winNum];*/
        if($cache){
            $user = Cache("user_info_{$userId}");
            if(!is_array($user)){
                $user = Db::name("user")->where(['id' => $userId])->find();
                $user['record'] = $record;
                Cache("user_{$userId}",$user);
            }
        }
        return $record;
    }


    private function _cache($user){
        $_user = Cache("user_{$user['id']}");
        if($_user && isset($_user['customer_read'])){
            $user['customer_read'] = $_user['customer_read'];
        }
        $record = isset($_user['record']) ? $_user['record'] : [];
        if(!$record && !$this->upCacheAll){
            $record = $this->setUserDetailCache($user['id'],false);
        }
        $user['record'] = $record;
        Cache("user_{$user['id']}",$user);
    }

    public function cacheLevel(){
        $level = Db::name('user_level')->select();
        $data = [];
        foreach($level as $val){
            $data[$val['id']] = $val;
        }
        Cache("user_level",$data);
        return true;
    }

    public function getUserLevel(){
        $level = Cache("user_level");
        if(!$level){
            $this->cacheLevel();
            $level = Cache("user_level");
        }
        return $level;
    }

    /**
     * 更新用户等级
     * @param $userId
     * @param $deposit_money
     */
    public function upUserLevel($userId){
        $user = $this->getUserByDb($userId);
        if($user){
            $level = $this->calUserCurrentLevel($user['deposit_money']);
            if($level && $level['id'] != $user['level']){
                Db::name('user')->where(['id' => $userId])->update(['level' => $level['id']]);
                //更新用户缓存
                //$this->_setUserCache($user);
                $this->setCacheUser($userId);
            }
        }
    }

    /**
     * 在线
     * @param $userId
     */
    public function Online($userId){
    }
    /**
     * 在线
     * @param $userId
     */
    public function Offline($userId){
    }


    /**
     * 计算用户当前等级
     * @param $deposit_money
     * @return mixed
     */
    public function calUserCurrentLevel($deposit_money){
        $deposit_money = intval($deposit_money);
        $levels = $this->getUserLevel();
        foreach($levels as $key => $val){
            if($deposit_money >= $val['min'] && $deposit_money <= $val['max']){
                return $val;
            }
        }
        return false;
    }

    /**
     * 用户等级经验
     * @param $user
     */
    public function exper($user){
        if(is_numeric($user)){
            $user = $this->getUser(intval($user));
        }
        $deposit_money = floatval($user['deposit_money']);
        $levels = $this->getUserLevel();
        if(!$user['level']){
            reset($levels);
            $level = current($levels);
        }else{
            $level = $levels[$user['level']];
        }
        $exp = floatval(numberFormat(($deposit_money / $level['max']) * 100,2));
        $endLevel = end($levels);
        if($endLevel['id'] == $user['level']){ //用户达到最大等级
            $deposit_money = $level['max'];
        }
        return ['name' => $level['name'],'exp' => $exp,'number' => $deposit_money,'min' => $level['min'],'max' => $level['max'],'look' => $level['lookbet']];
    }
    
    //判断用户有无中奖，擂台被投注等情况
    public function getUserNewStatus($user_id){
        $result = 0;
        //判断投注有无新开奖
        if (getMyBetOrder($user_id) >0){
            $result = 1;
        }
        //判断擂台有没有新投注
        if(getMyArenaNewbetOrder($user_id) > 0){
            $result = 1;
        }
        return $result;
    }

    /**
     * 缓存用户坐庄数据
     * @param $playId
     * @param $arenaId
     */
    public function cachePublishArena($userId,$playId,$arenaId){
        $cacheName = "user_publish_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            $data = [];
        }
        $play = isset($data['play']) ? $data['play'] : [];
        if(!in_array($playId,$play)){
            array_push($play, $playId);
        }
        $data['play'] = $play;

        $arena = isset($data['arena']) ? $data['arena'] : [];
        if(!in_array($arenaId,$arena)){
            array_push($arena, $arenaId);
        }
        $data['arena'] = $arena;
        cache($cacheName,$data);
    }

    /**
     * 检查用户某场比赛是否坐庄
     * @param $userId
     * @param $playId
     * @return bool
     */
    public function checkPublishArenaByPlay($userId,$playId){
        if(!$userId){return false;}
        $userId = intval($userId);
        $playId = intval($playId);
        $cacheName = "user_publish_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            return false;
        }
        $play = isset($data['play']) ? $data['play'] : [];
        if(in_array($playId,$play)){
            return true;
        }
        return false;
    }

    /**
     * 移除用户缓存的坐庄数据
     * @param $userId
     * @param null $playId
     * @param null $arenaId
     * @return bool
     */
    private function rmPlayOrArenaInUserPublish($userId,$playId = null,$arenaId = null){
        $cacheName = "user_publish_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            return true;
        }
        $play = isset($data['play']) ? $data['play'] : [];
        if($playId && in_array($playId,$play)){
            foreach($play as $key => $val){
                if($val == $playId){
                    unset($play[$key]);
                }
            }
            $data['play'] = $play;
        }
        $arena = isset($data['arena']) ? $data['arena'] : [];
        if($arenaId && in_array($arenaId,$arena)){
            foreach($arena as $key => $val){
                if($val == $arenaId){
                    unset($arena[$key]);
                }
            }
            $data['arena'] = $arena;
        }

        cache($cacheName,$data);
        return true;
    }

    /**
     * 缓存用户投注信息
     * @param $userId
     * @param $playId
     * @param $arenaId
     */
    public function cacheArenaBet($userId,$playId,$arenaId,$betId){
        $cacheName = "user_betting_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            $data = [];
        }
        //比赛
        $play = isset($data['play']) ? $data['play'] : [];
        if($playId && !in_array($playId,$play)){
            array_push($play, $playId);
        }
        $data['play'] = $play;
        //擂台
        $arena = isset($data['arena']) ? $data['arena'] : [];
        if($arenaId && !in_array($arenaId,$arena)){
            array_push($arena, $arenaId);
        }
        $data['arena'] = $arena;
        cache($cacheName,$data);
    }
    /**
     * 检查用户某场比赛是否投注
     * @param $userId
     * @param $playId
     * @return bool
     */
    public function checkBetPlay($userId,$playId){
        $cacheName = "user_betting_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            return false;
        }
        $play = isset($data['play']) ? $data['play'] : [];
        if(in_array($playId,$play)){
            return true;
        }
        return false;
    }
    /**
     * 检查用户某个擂台比赛是否投注
     * @param $userId
     * @param $playId
     * @return bool
     */
    public function checkBetArena($userId,$arenaId){
        $cacheName = "user_betting_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            return false;
        }
        $arena = isset($data['arena']) ? $data['arena'] : [];
        if(in_array($arenaId,$arena)){
            return true;
        }
        return false;
    }


    /**
     * 移除用户缓存的坐庄数据
     * @param $userId
     * @param null $playId
     * @param null $arenaId
     * @return bool
     */
    private function rmPlayOrArenaInUserBet($userId,$playId = null,$arenaId = null,$betId = null){
        $cacheName = "user_betting_arena_{$userId}";
        $data = cache($cacheName);
        if(!$data){
            return true;
        }
        $play = isset($data['play']) ? $data['play'] : [];
        if($playId && in_array($playId,$play)){
            foreach($play as $key => $val){
                if($val == $playId){
                    unset($play[$key]);
                }
            }
            $data['play'] = $play;
        }
        $arena = isset($data['arena']) ? $data['arena'] : [];
        if($arenaId && in_array($arenaId,$arena)){
            foreach($arena as $key => $val){
                if($val == $arenaId){
                    unset($arena[$key]);
                }
            }
            $data['arena'] = $arena;
        }
        cache($cacheName,$data);
        return true;
    }

    /**
     * 申请成功房主
     * @param $userId
     */
    public function Homeowner($userId){
        if($this->checkLockUser($userId)){
            $this->error = 10005;
            return false;
        }
        $this->lockUser($userId,true);
        Db::startTrans();
        $user = [];
        try{
            $userId = intval($userId);
            $user = Db::name('user')->where(['id' => $userId])->find();//lock(true)->cache(false)->
            if($user['has_homeowner']){
                throw new Exception(20110);
            }
            //$freezeHomeGold = \library\service\Misc::system('sys_freeze_home');
            $freezeHomeGold = config("system.sys_freeze_home");
            if($user['gold'] < $freezeHomeGold){
                $this->errorData = ['number' => $freezeHomeGold];
                throw new Exception(20111);
            }

            Db::name('user')->where(['id' => $userId])->update([
                'has_homeowner' => 1,
                'gold' => ['exp',"gold-$freezeHomeGold"],
                'freeze_home' => ['exp',"freeze_home+$freezeHomeGold"],
            ]);
            Db::commit();
            $this->lockUser($userId,false);
        }catch (Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            $this->lockUser($userId,false);
            return false;
        }
        if($user) {
            $this->setCacheUser($userId);
            (new Socket())->userGold($userId, -$freezeHomeGold);
            @Log::UserFunds(
                $userId,
                FUNDS_CLASSIFY_FREEZE,
                FUNDS_TYPE_GOLD,
                -$freezeHomeGold,
                $user['gold'],
                $user['gold'] - $freezeHomeGold,
                lang('99030', ['money' => $freezeHomeGold]),
                []
            );
        }
        $this->lockUser($userId,false);
        return true;
    }

    /**
     * 取消房主
     * @param $userId
     */
    public function unHomeowner($userId){
        if($this->checkLockUser($userId)){
            $this->error = 10005;
            return false;
        }
        $this->lockUser($userId,true);
        Db::startTrans();
        $freezeHomeGold = 0;
        $user =[];
        try{
            $userId = intval($userId);
            $user = Db::name('user')->where(['id' => $userId])->find();//->lock(true)->cache(false)
            if(!$user['has_homeowner']){
                throw new Exception(20115);
            }
            $freezeHomeGold = $user['freeze_home'];
            Db::name('user')->where(['id' => $userId])->update([
                'has_homeowner' => 0,
                'gold' => ['exp',"gold+{$freezeHomeGold}"],
                'freeze_home' => 0,
            ]);
            Db::commit();
            $this->lockUser($userId,false);
        }catch (Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            $this->lockUser($userId,false);
            return false;
        }
        if($user) {
            $this->setCacheUser($userId);
            (new Socket())->userGold($userId, $freezeHomeGold);
            @Log::UserFunds(
                $userId,
                FUNDS_CLASSIFY_UNFREEZE,
                FUNDS_TYPE_GOLD,
                $freezeHomeGold,
                $user['gold'],
                $user['gold'] + $freezeHomeGold,
                lang('99032', ['money' => $freezeHomeGold]),
                []
            );
        }
        $this->lockUser($userId,false);
        return true;
    }

    /**
     * 锁定用户当前操作
     * @param $userId
     * @param bool $isLock
     */
    public function lockUser($userId,$isLock = true){
        if($isLock){
            \think\Cache::hSet('Lock_User',$userId,600);
        }else{
            \think\Cache::hDel('Lock_User',$userId);
        }
    }

    public function checkLockUser($userId){
        $isLock = \think\Cache::hGet('Lock_User',$userId);
        if($isLock){
            return true;
        }
        return false;
    }

    public function getError(){
        return $this->error;
    }
    public function getErrorData(){
        return $this->errorData;
    }
}