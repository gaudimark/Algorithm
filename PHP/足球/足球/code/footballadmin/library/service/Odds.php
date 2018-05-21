<?php

//赔率
namespace library\service;
use think\Db;
use think\Exception;

class Odds{
    private $error = '';
    public function __construct(){ }

    /**
     * 新增赔率
     * @param $rules 玩法
     * @param $play_id 比赛
     * @param $company_id 赔率公司
     * @param $game_type 所属比赛类型
     * @param $odds 赔率数组
     * @return bool
     */
    public function addOdds($rulesID,$rulesType,$play_id,$company_id,$game_type,$odds,$user_id = 0){
        Db::startTrans();
        $oddsID = 0;
        $user_id = intval($user_id);
        try{
            $ret = (new Arena())->factory($game_type)->addOddsCheck($rulesID,$odds);
            if(!is_array($ret) && $ret !== true){
                $ret = is_string($ret) ? $ret : '赔率填写错误';
                throw new Exception($ret);
            }

            $teams = (new Play())->getTeams($play_id);
            //格式化赔率数据
            $oddsData = (new Rule())->factory($game_type)->parseOdds($odds,$rulesID,$teams);
            $data = [
                'init' => $oddsData,
                'time' => $oddsData
            ];
            $oddsID = Db::name('odds')->insertGetId([
                'md5' => md5(json_encode($data)),
                'game_type' => $game_type,
                'user_id' => $user_id,
                'play_id' => $play_id,
                'odds_company_id' => $company_id,
                'rules_id' => $rulesID,
                'rules_type' => $rulesType,
                'odds' => json_encode($data),
                'modify' => ODDS_USER_UNMODIFY,
                'create_time'=>time(),
                'update_time' => time(),
            ]);
            if($oddsID){
                Db::name("odds_detail")->insert([
                    'odds_id' => $oddsID,
                    'odds' => json_encode($odds),
                    'update_time' => time(),
                ]);
            }
            Db::name("play")->where(['id' => $play_id])->update(['has_odds' => 1]);
            Db::commit();
            return $oddsID;
        }catch (Exception $e){
            $this->error = $e->getMessage();//.$e->getFile().$e->getLine();
            Db::rollback();
            return false;
        }
        (new Play())->upCache($play_id);
        return $oddsID;
    }

    /**
     * 将数据转换成odds表的数据
     * @param $odds
     */
    public function parseOdds($odds){
        $data = [];
        foreach ($odds as $key => $val){
            if(isset($val['item']) && $val['item']){
                $data[$val['target']][$val['item']] = $val['odds'];
            }else{
                $data[$val['target']] = $val['odds'];
            }
        }
        return $data;
    }

    public function updateOddsById($odds_id,$oddsData){
        $odds_id = intval($odds_id);
        $odds = Db::name('odds')->where(['id' => $odds_id])->find();
        if(!$odds){
            $this->error = '未找到所要更新的赔率信息';
            return false;
        }
        $isUpdate = false;
        Db::startTrans();
        try{
            //检查赔率数据格式
            $ret = (new Arena())->factory($odds['game_type'])->addOddsCheck($odds['rules_id'],$oddsData);
            if(!is_array($ret) && $ret !== true){
                $ret = is_string($ret) ? $ret : '赔率填写错误';
                throw new Exception($ret);
            }
            $teams = (new Play())->getTeams($odds['play_id']);
            //格式化赔率数据
            $oddsData = (new Rule())->factory($odds['game_type'])->parseOdds($oddsData,$odds['rules_id'],$teams);
            $data = json_decode($odds['odds'],true);
            $data['time'] = $oddsData;

            if($odds['md5'] == md5(json_encode($data))){
                return true;
            }
            $oddsID = Db::name('odds')->where(['id' => $odds_id])->update([
                'md5' => md5(json_encode($data)),
                'odds' => json_encode($data),
                'modify' => $odds['modify'] == ODDS_USER_UNMODIFY ? ODDS_USER_UNMODIFY : ODDS_ZGZCW_UNMODIFY,
            ]);
            if($oddsID){
                Db::name("odds_detail")->insert([
                    'odds_id' => $odds_id,
                    'odds' => json_encode($oddsData),
                    'update_time' => time(),
                ]);
            }
            Db::name("play")->where(['id' => $odds['play_id']])->update(['has_odds' => 1]);
            $isUpdate = true;
            Db::commit();
        }catch (Exception $e){
            $this->error = $e->getMessage();//.$e->getFile().$e->getLine();
            Db::rollback();
            return false;
        }
        $isUpdate && $this->updateArenaOddsByAutoArena($odds_id,$odds['play_id']);
        return true;
    }


    public function getOddsById($odds_id){
        $odds_id = (int)$odds_id;
        $data = Db::name('odds')->where(['id' => $odds_id])->find();
        $data['odds'] = @json_decode($data['odds'],true);
        return $data;
    }

    /**
     * 获取博彩公司信息
     * @param $company
     */
    public function getCompanyByName($company_name,$game_type){
        $company = Db::name("odds_company")->where(['name' => $company_name])->find();
        if($company){
            $rel = Db::name("odds_company_game")->where(['odds_company_id' => $company['id'],'game_type' => $game_type])->find();
            if(!$rel){
                Db::name("odds_company_game")->insert(['odds_company_id' => $company_name,'game_type' => $game_type]);
            }
            return $company['id'];
        }
        $company_id = Db::name("odds_company")->insert(['name' => $company_name]);
        Db::name("odds_company_game")->insert(['odds_company_id' => $company_id,'game_type' => $game_type]);
        return $company_id;
    }

    public function getError(){
        return $this->error;
    }

    public function getCompanyOdds(){
        
    }

    /**
     * 获取比赛的推荐赔率
     */
    public function getPlayRecommendOdds($playId,$ruleIds = [],$odds_company_id = []){
        $where = ['play_id' => $playId];
        if(!is_array($ruleIds)){
            $ruleIds[] = intval($ruleIds);
        }
        //$where['rules_type'] = ['in',implode(",",array_values($ruleIds))];
        if($odds_company_id){
            $where['odds_company_id'] = ['in',array_values($odds_company_id)];
        }

        $mark = md5(serialize($where));
        $oddsData = '';//cache("play_recommend_odds_{$mark}");
        if(!$oddsData){
            $oddsData = Db::name('odds')->where($where)->field('id,odds,rules_type,rules_id')->find();
            cache("play_recommend_odds_{$mark}",$oddsData,1800);
        }
        return $oddsData;
    }

    /**
     * 用于自动更新擂台赔率
     * @param $oddsId 赔率ID
     * @param $playId 比赛ID
     */
    public function updateArenaOddsByAutoArena($oddsId,$playId){
        $oddsData = Db::name('odds')->where(['id' => $oddsId,'play_id' => $playId])->find();
        if(!$oddsData){
            return false;
        }
        $tempOdds = @json_decode($oddsData['odds'],true);
        if(isset($tempOdds['time'])){
            $odds = $tempOdds['time'];
        }else {
            $odds = $tempOdds['init'];
        }
        $page = 1;
        $limit = 10;
        $arenaSvr = new Arena();
        $arenaSvr->admin_id = 1;
        while (true){
            $offset = ($page - 1) * $limit;
            $list = Db::name('arena')->field("id")->where(['status' => ['in',[ARENA_START,ARENA_SEAL]],'play_id' => $playId,'odds_id' => $oddsId,'auto_update_odds' => 1])->limit($offset,$limit)->select();
            if(!$list){break;}
            foreach($list as $arena){
                $arenaSvr->autoArenaOdds($arena['id'],$odds);
            }
            $page++;
        }

    }
    
    
}