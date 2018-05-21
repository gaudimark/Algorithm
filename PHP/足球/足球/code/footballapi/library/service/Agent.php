<?php

namespace library\service;
use org\Crypt;
use org\Stringnew;
use think\Db;
use think\Exception;
use think\Request;

class Agent{
    private $initPasswordSafeCode = 'odv7KsSU'; //加密初始密码秘钥
    private $error = '';
    private $errorData = [];
    /**
     * 创建代理
     * @param $user_id 所属用户
     * @param $username 代理用户名
     * @param $password 代理登录密码
     * @param $rate 代理提层比例
     * @param $arenaType 擂台展示方式 ，1-全部，2-单个
     * @param array $arenaList 擂台列表
     */
    public function create($user_id,$username,$password,$rate,$arenaType,$arenaIds = ''){
        $rate = floatval($rate);
        if(!$username){
            $this->error = 41001;
            return  false;
        }
        if(!$password){
            $this->error = 41002;
            return false;
        }
        $username = strtolower($username);
        if($rate<0 || $rate >100){
            $this->error = 41000;
            return false;
        }
        /*if($arenaType == AGENT_USER_ARENA_TYPE_SINGLE && !trim($arenaIds)){
            $this->error = 41003;
            return false;
        }*/

        if(true !== $ret = $this->checkUsername($username)){
            $this->error = $ret;
            return false;
        }
        if(true !== $ret = $this->checkPassword($password)){
            $this->error = $ret;
            return false;
        }

        $user = Db::name('agent_user')->where(['username' => $username])->find();
        if($user){
            $this->error = 41004;
            return false;
        }
        $salt = $this->getSalt();
        $password = $this->enInitPassword($password);
        $mark = uniqid('p');
        $agentUserId = Db::name('agent_user')->insertGetId([
            'user_id' => $user_id,
            'mark' => $mark,
            'username' => $username,
            'init_pwd' => $password,
            'password' => '',
            'salt'  => $salt,
            'rate'  => $rate,
            'arena_type'  => $arenaType,
        ]);
        if($agentUserId){
            while (true){
                $agentNo = Stringnew::randNumber(10000,999999);
                $agentNo = $agentNo + $agentUserId;
                $res = Db::name('agent_user')->where(['mark' => $agentNo])->find();
                if(!$res){
                    Db::name('agent_user')->where(['id' => $agentUserId])->update(['mark' => $agentNo]);
                    break;
                }
            }

            $arenaSvr = new Arena();
            $inData = [];
            if($arenaType == AGENT_USER_ARENA_TYPE_SINGLE){ //单个
                $this->addArena($arenaIds,$user_id,$agentUserId);
            }else{//全部
                $limit = 50;
                $page = 0;
                while(true){
                    $inData = [];
                    $offset = $page * $limit;
                    $arenaList = Db::name("arena")->limit($offset,$limit)->where(['user_id' => $user_id,'status' => ['in',[ARENA_START,ARENA_SEAL]]])->select();
                    if(!$arenaList){break;}
                    foreach($arenaList as $arena){
                        $inData[] = ['user_id' => $user_id,'agent_user_id' => $agentUserId, 'arena_id' => $arena['id'], 'arena_status' => $arena['status'],'status' => STATUS_ENABLED];
                    }
                    if($inData){
                        Db::name("agent_arena")->insertAll($inData);
                        $inData = [];
                    }
                    $page++;
                }
            }
        }else{
            $this->error = 41005;
            return false;
        }
        $this->cacheAgentUser($agentUserId);
        return $agentUserId;
    }


    public function modifyRate($userId,$agentUserId,$rate){
        $rate = floatval($rate);
        if($rate<0 || $rate >100){
            $this->error = 41000;
            return false;
        }
        $agentUser = $this->getAgentUserById($agentUserId);
        if(!$agentUser || $agentUser['user_id'] != $userId){
            $this->error =  41006;
            return false;
        }
        Db::name('agent_user')->where(['id' => $agentUserId])->update(['rate' => $rate]);
        $this->cacheAgentUser($agentUserId);
        return true;
    }


    /**
     * 添加擂台
     * @param $arenaIds
     */
    public function addArena($arenaIds,$user_id,$agentUserId){
        $arenaIds = explode(",", $arenaIds);
        $arenaSvr = new Arena();
        $ids = [];
        foreach ($arenaIds as $id) {
            $id = intval($id);
            if($id){
                $ids[$id] = $id;
            }
        }
        if(!$ids){
            $this->error =  10004;
            return false;
        }
        $agentArena = Db::name("agent_arena")->field('id,arena_id')->where(['agent_user_id' => $agentUserId,'arena_id' => ['in',array_values($ids)]])->select();
        foreach($agentArena as $val){
            if(isset($ids[$val['arena_id']])){
                unset($ids[$id]);
            }
        }
        $inData = [];
        if($ids){
            foreach($ids as $id) {
                $arena = $arenaSvr->getCacheArenaById($id);
                if ($arena['user_id'] == $user_id){
                    $inData[] = ['user_id' => $user_id, 'agent_user_id' => $agentUserId, 'arena_id' => $id, 'arena_status' => $arena['status'], 'status' => STATUS_ENABLED];
                }
            }
        }
        if($inData){
            Db::name("agent_arena")->insertAll($inData);
        }
        return true;
    }

    /**
     * 添加擂台
     * @param $arenaIds
     */
    public function removeArena($arenaIds,$user_id,$agentUserId){
        $arenaIds = explode(",", $arenaIds);
        $arenaSvr = new Arena();
        $ids = [];
        foreach ($arenaIds as $id) {
            $id = intval($id);
            if($id){
                $ids[$id] = $id;
            }
        }
        if(!$ids){
            $this->error =  10004;
            return false;
        }
        Db::name("agent_arena")->where([
            'user_id' => $user_id,
            'agent_user_id'=>$agentUserId,
            'arena_id' => ['in',array_values($ids)]
        ])->delete();

        return true;
    }

    /**
     * 代理后台地址
     * @param $agentUserId
     */
    public function getManagerCenterUrl($agentUserId,$platform = 'h5'){
        $agentUser = $this->getAgentUserById($agentUserId);
        if (!$agentUser){return '';}
        $domains = config("domains");
        $domain = config('site_domain');
        if(isset($domains[$platform]) && $domains[$platform] != ''){
            $domain = $domains[$platform];
        }
        return $domain."?action=agent_manager&platform={$platform}";
    }

    /**
     * 代理个人中心地址
     * @param $agentUserId
     */
    public function getUserCenterUrl($agentUserId,$platform = 'h5'){
        $agentUser = $this->getAgentUserById($agentUserId);
        if (!$agentUser){return '';}
        $domains = config("domains");
        $domain = config('site_domain');
        if(isset($domains[$platform]) && $domains[$platform] != ''){
            $domain = $domains[$platform];
        }
        return $domain."?action=agent_center&platform={$platform}&agent_no={$agentUser['mark']}";
    }

    /**
     * 更新代理用户投注收益
     */
    public function upUserBetWin($money,$agentUserId,$arenaUserId,$arenaId,$data = []){
        $agentUser = $this->getAgentUserById($agentUserId);
        //如果代理用户存在，并且用户状态为可用，而且所属主代与擂主一至时
        if($agentUser && $agentUser['status'] == STATUS_ENABLED && $agentUser['user_id'] == $arenaUserId){
            $agent_win_total = $money * ($agentUser["rate"]/100);
            Db::name('agent_user')->where(['id' => $agentUser['id']])->update([
                'bet_money' => ['exp',"bet_money+{$money}"], //总投注金额
                'win_total' => ['exp',"win_total+{$agent_win_total}"], //总收益
                'win_unsettlement' => ['exp',"win_unsettlement+{$agent_win_total}"], //未结算的收益
            ]);
            Db::name('agent_arena')->where(['agent_user_id' => $agentUserId,'user_id' => $agentUser['user_id'],'arena_id' => $arenaId])->update([
                'bet_money' => ['exp',"bet_money+{$money}"],
                'bet_number' => ['exp',"bet_number+1"],
                'win_total' => ['exp',"win_total+{$agent_win_total}"],
            ]);
            //写入代理日志
            $explain = isset($data['explain']) ? $data['explain'] : "";
            unset($data['explain']);
            $data = array_merge($data,[
                'win' => $agent_win_total,
                'money' => $money,
                'rate' => $agentUser["rate"]]);
            $this->logs($agentUserId,FUNDS_CLASSIFY_DEP,FUNDS_TYPE_GOLD,$agent_win_total,$agentUser['win_unsettlement'],$agentUser['win_unsettlement']+$agent_win_total,$explain,$data);
            $this->cacheAgentUser($agentUserId);
        }
    }

    /**
     * 结算
     * @param $userId
     * @param $agentUserId
     * @param $money
     */
    public function unSettlement($userId,$agentUserId,$money){
        $money = intval($money);
        $agentUser = $this->getAgentUserById($agentUserId);
        //如果代理用户存在，并且用户状态为可用，而且所属主代与擂主一至时
        if(!$money || !$agentUser || $agentUser['user_id'] != $userId){
            $this->error =  10004;
            return false;
        }

        if($money > $agentUser['win_unsettlement']){
            $this->error =  41007;
            return false;
        }
        Db::startTrans();
        try{
            $user = Db::name('user')->where(['id' => $userId])->find();
            if($user['gold'] < $money){
                $this->error =  41008;
                return false;
            }
            //扣除主代用户金币
            Db::name('user')->where(['id' => $userId])->setDec("gold",$money);
            //更新代理用户金币并扣除可结算金币
            Db::name('agent_user')->where(['id' => $agentUserId])->update([
                'gold' => ['exp',"gold+{$money}"],
                'win_unsettlement' => ['exp',"win_unsettlement-{$money}"],
            ]);
            //代理日志
            $explain = '';
            $data = [
                'money' => $money
            ];
            $this->logs($agentUserId
                ,FUNDS_CLASSIFY_AGENT_SETTLE_ONLINE
                ,FUNDS_TYPE_GOLD
                ,$money
                ,$agentUser['gold']
                ,$agentUser['gold']+$money
                ,lang(41009)
                ,$data
            );
            //用户日志
            Log::UserFunds($userId
                ,FUNDS_CLASSIFY_AGENT_SETTLE_ONLINE
                ,FUNDS_TYPE_GOLD
                ,-$money
                ,$user['gold']
                ,$user['gold']-$money
                ,lang(41010,['username' => $agentUser['username']])
                ,$data
            );
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            $this->error =  10000;
            return false;
        }
        $this->cacheAgentUser($agentUserId);
        return true;

    }

    /**
     * 写入缓存
     * @param $user_id
     */
    public function cacheAgentUser($user_id){
        $user_id = intval($user_id);
        if(!$user_id){return false;}
        $user = Db::name('agent_user')->where(['id' => $user_id])->find();
        if($user){
            //$user = $user->toArray();
            unset($user['password']);
            unset($user['salt']);
            cache("agent_user_{$user_id}",$user);
            cache("agent_user_mark_{$user['mark']}",$user_id);
            return $user;
        }
    }

    /**
     * 根据代理用户ID获取代理用户信息
     * @param $user_id
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public function getAgentUserById($user_id){
        $user_id = intval($user_id);
        if(!$user_id){return false;}
        $user = cache("agent_user_{$user_id}");
        if(!$user){
            return $this->cacheAgentUser($user_id);
        }
        return $user;
    }

    /**
     * 根据代理用户唯一码获取代理用户信息
     * @param $mark
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public function getAgentUserByMark($mark){
        $user_id = cache("agent_user_{$mark}");
        if(!$user_id){
            $user = Db::name('agent_user')->field("id")->where(['mark' => $mark])->find();
            if($user){
                return $this->getAgentUserById($user['id']);
            }
        }else{
            return $this->getAgentUserById($user_id);
        }
        return false;
    }

    /**
     * 加密初始密码
     * @param $password
     */
    public function enInitPassword($password){
        return Crypt::encrypt($password,$this->initPasswordSafeCode,0);
    }

    /**
     * 反解初始密码
     * @param $password
     */
    public function deInitPassword($password){
        return Crypt::decrypt($password,$this->initPasswordSafeCode);
    }



    public function doLogin($username,$password,$platform = 'h5'){
        $username = strtolower($username);
        $user = Db::name('agent_user')->where(['username' => $username])->find();
        if(!$user){
            $this->error = 20002;
            return false;
        }
        if(!$user['password']){
            if($password != $this->deInitPassword($user['init_pwd'])){
                $this->userLog($user['id'],lang('20002'),[
                    'ip' => Request::instance()->ip(),
                ]);
                $this->error = 20002;
                return false;
            }
        }else{
            if($user['password'] != $this->parsePassword($password,$user['salt'])){
                $this->userLog($user['id'],lang('20002'),[
                    'ip' => Request::instance()->ip(),
                ]);
                $this->error = 20002;
                return false;
            }
        }
        $last_login_time = time();
        Db::name('agent_user')->where(['id' => $user['id']])->update([
            'last_login_time' => $last_login_time,
        ]);
        $this->userLog($user['id'],"登录成功",['ip' => Request::instance()->ip()]);
        $user['manager_center_url'] = $this->getManagerCenterUrl($user['id'],$platform);
        $user['user_center_url'] = $this->getUserCenterUrl($user['id'],$platform);
        return $user;
    }

    public function resetPwd($userId,$old_pwd,$new_pwd){
        $user = Db::name('agent_user')->where(['id' => $userId])->find();
        if(!$user){
            $this->error = 20002;
            return false;
        }
        if(true !== $ret = $this->checkPassword($new_pwd)){
            $this->error = $ret;
            return false;
        }
        if(!$user['password']){
            if($old_pwd != $this->deInitPassword($user['init_pwd'])){
                $this->userLog($user['id'],lang(20075),[
                    'ip' => Request::instance()->ip(),
                ]);
                $this->error = 20076;
                return false;
            }
        }else{
            if($user['password'] != $this->parsePassword($old_pwd,$user['salt'])){
                $this->userLog($user['id'],lang(20073),[
                    'ip' => Request::instance()->ip(),
                ]);
                $this->error = 20076;
                return false;
            }
        }

        $salt = $this->getSalt();
        $password = $this->parsePassword($new_pwd,$salt);
        Db::name('agent_user')->where(['id' => $userId])->update([
            'password' => $password,
            'salt' => $salt
        ]);
        $this->userLog($user['id'],lang(20074),[
            'ip' => Request::instance()->ip(),
        ]);
        return true;
    }


    public function resetPassword($user_id,$agent_user_id){
        $salt = $this->getSalt();
        $pwdStr = \org\Stringnew::randString(6);
        $pwdStr = strtolower($pwdStr);
        $password = $this->parsePassword($pwdStr,$salt);
        Db::name('agent_user')->where(['id' => $agent_user_id,'user_id' => $user_id])->update([
            'salt'  => $salt,
            'password'  => $password
        ]);
        return $pwdStr;
    }

    //密码生成
    public function parsePassword($pwd,$salt){
        $pwd1 = md5($pwd);
        $salt1 = md5($salt);
        return md5($salt1.$pwd.$salt.$pwd1);
    }
    //密码辅助生成
    private function getSalt(){
        return \org\Stringnew::randNumber(100000,999999);
    }
    /**
     * 写入日志
     * @param $user_id
     * @param $classify
     * @param $type
     * @param $number
     * @param $before_num
     * @param $after_num
     * @param $explain
     * @param array $data
     */
    public function logs($user_id,$classify,$type,$number,$before_num,$after_num,$explain,$data = []){
        Db::name("agent_user_funds_log")->insert([
            'user_id' => $user_id,
            'classify' => $classify,
            'type' => $type,
            'number' => $number,
            'before_num'=>$before_num,
            'after_num'=>$after_num,
            'explain' => $explain,
            'data' => json_encode($data),
            'create_time' => time()
        ]);
    }
    /**
     * 写入日志
     * @param $user_id
     * @param $classify
     * @param $type
     * @param $number
     * @param $before_num
     * @param $after_num
     * @param $explain
     * @param array $data
     */
    public function userLog($user_id,$explain,$data = []){
        Db::name("agent_user_log")->insert([
            'user_id' => $user_id,
            'explain' => $explain,
            'data' => json_encode($data),
            'create_time' => time()
        ]);
    }

    public function getError(){
        return $this->error;
    }
    public function getErrorData(){
        return $this->errorData;
    }

    public function checkUsername($username){
        $username = strtolower($username);
        if(!$username){return 20000;}//为空
        if(!preg_match("/^[\w]+$/",$username)){
            return 20011; //输入包含了非字母、数字、下划线的字符，
        }
        if(strlen($username) < 6 || strlen($username) > 20){
            return 20012;//长度不为6-20位
        }

        if(preg_match("/^\d.*$/i",$username)){
            return 20014; //为数字开头
        }
        return true;
    }
    public function checkPassword($password){
        if(strlen($password) < 8 || strlen($password) > 20){
            return 20021;
        }
        return true;

    }
}