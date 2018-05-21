<?php

/**
 * 擂台接口
 */

namespace library\service;

use Endroid\QrCode\QrCode;
use org\Stringnew;
use think\Cache;
use think\Db;
use think\Exception;

class Arena {

    private $arenaData = [];
    private $expire = 30 * 24 * 60 * 60; //擂台相关缓存有效期
    private $pkId = 0;
    private $model = null;
    private $error = '';
    private $errorData = [];
    public $admin_id = 0; //后台管理操作者ID
    private $userSvr = null;
    private $miscSvr = null;
    private $queue = false;

    public function __construct($arena_id = 0, $admin_id = 0) {
        $this->pkId = $arena_id;
        $this->admin_id = $admin_id;
        $this->userSvr = new User();
        $this->miscSvr = new Misc();
        if ($arena_id) {
            $this->arenaData = $this->getCacheArenaById($arena_id);
        }
    }

    public function setArenaId($arena_id) {
        if ($arena_id) {
            $this->pkId = $arena_id;
            $this->arenaData = $this->getCacheArenaById($arena_id);
        }
    }

    public function factory($gameType) {
        $handle = null;
        switch ($gameType) {
            case GAME_TYPE_FOOTBALL:

                $handle = new \library\service\arena\Football();
                break;
            case GAME_TYPE_WCG:
                $handle = new \library\service\arena\Wcg();
                break;
            case GAME_TYPE_BASKETBALL:
                $handle = new \library\service\arena\Basketball();
                break;
            case GAME_TYPE_PUCK:
                $handle = new \library\service\arena\Puck();
                break;
            case GAME_TYPE_TENNIS:
                $handle = new \library\service\arena\Tennis();
                break;
            case GAME_TYPE_AMERICAN_FOOTBALL:
                $handle = new \library\service\arena\American_football();
                break;
        }
        return $handle;
    }

    /**
     * 获取擂台信息
     * @param int $arend_id
     */
    public function findArena($arenaId = 0) {
        if (!$arenaId && !$this->pkId) {
            $this->error = "获取擂台主键ID失败";
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        $arenaData = Db::name('arena')->where('id', $this->pkId)->find();
        if (!$arenaData) {
            return false;
        }
        $arenaData['play'] = (new Play())->getPlay($arenaData['play_id']);
        $arenaData['teams'] = (new Play())->getTeams($arenaData['play_id']);
        $arenaData['match'] = Db::name('match')->where('id', $arenaData['match_id'])->find();
        return $arenaData;
    }

    /**
     * 生成邀请码
     * @return string
     */
    public function getInvitationCode() {
        return \org\Stringnew::randString(4, 1);
    }

    /**
     * 停止投注
     */
    public function sealArena($arenaId, $userId) {
        if (!$arenaId && !$this->pkId) {
            $this->error = 10025;
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if (!$arenaData) {
                throw new Exception(10025);
            }
            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40126);
            }
            if (in_array($arenaData['status'], [ARENA_DEL, ARENA_STATEMENT_BEGIN, ARENA_STATEMENT_END, ARENA_DIS])) {
                throw new Exception(40127);
            }

            if (!$this->admin_id && $arenaData['user_id'] != $userId) { //非管理员非擂主
                throw new Exception(10025);
            }

            Db::name('arena')->where(["id" => $this->pkId])->update(['status' => ARENA_SEAL]);
            $user_id = $arenaData['user_id'];
            if ($this->admin_id) {
                @Log::sysOpt($this->admin_id, lang(90015, ['arenaId' => $arenaData['id']]), []);
                @Log::arenaLog($arenaData['id'], lang(90015, ['arenaId' => $arenaData['id']]), [], 2);
                if (!$this->queue) {
                    (new Message())->sendToArenaGroup($this->pkId, MESSAGE_QUEUE_TYPE_SEAL, []);
                }
            } else {
                Log::UserLog($arenaData['user_id'], lang(90015, ['arenaId' => $arenaData['id']]), []);
                @Log::arenaLog($arenaData['id'], lang(90015, ['arenaId' => $arenaData['id']]), [], 1);
            }
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        $this->cacheArena($arenaId);
        return true;
    }

    /**
     * 停止比赛下的擂台投注
     * @param $playId
     */
    public function sealPlayArena($playId) {
        if (!$this->admin_id) {
            $this->error = 10025;
            return false;
        }
        $play = (new Play())->getPlay($playId);
        if (!$play || !in_array($play['status'], [PLAT_STATUS_SUSP, PLAT_STATUS_WAIT, PLAT_STATUS_CUT])) {
            $this->error = 10025;
        }


        $lists = Db::name('arena')->where(['play_id' => $playId])->column('id');
        $ids = [];
        if ($lists) {
            $this->queue = true;
            foreach ($lists as $val) {
                $this->sealArena($val, 0);
                $ids[] = $val;
            }
        }
        (new Message())->sendToPlayGroup($playId, MESSAGE_QUEUE_TYPE_SEAL, $ids);
        return true;
    }

    /**
     * 开启投注
     */
    public function unsealArena($arenaId, $userId) {
        if (!$arenaId && !$this->pkId) {
            $this->error = 10025;
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if (!$arenaData) {
                $this->error = 10025;
            }
            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40126);
            }
            if ($arenaData['status'] != ARENA_SEAL) {
                throw new Exception(40129);
            }
            if (in_array($arenaData['status'], [ARENA_DEL, ARENA_STATEMENT_BEGIN, ARENA_STATEMENT_END])) {
                throw new Exception(10025);
            }
            $play = (new Play())->getPlay($arenaData['play_id']);
            if ($play['status'] == PLAT_STATUS_END) {
                throw new Exception(40136);
            }
            if ($play['status'] == PLAT_STATUS_SUSP) {
                throw new Exception(40137);
            }
            if ($play['status'] == PLAT_STATUS_CUT) {
                throw new Exception(40138);
            }
            if ($play['status'] == PLAT_STATUS_WAIT) {
                throw new Exception(40135);
            }
            if ($play['status'] == PLAT_STATUS_STATEMENT_BEGIN || $play['status'] == PLAT_STATUS_STATEMENT) {
                throw new Exception(40139);
            }

            //检查比赛状态
            if ($arenaData['play']['play_time'] <= time()) {
                throw new Exception(40133);
            }
            if (!$this->admin_id && $arenaData['user_id'] != $userId) { //非管理员非擂主
                throw new Exception(10025);
            }

            Db::name('arena')->where(["id" => $this->pkId])->update(['status' => ARENA_START]);
            $user_id = $arenaData['user_id'];


            if ($this->admin_id) {
                @Log::sysOpt($this->admin_id, lang(90016, ['arenaId' => $arenaData['id']]), []);
                @Log::arenaLog($arenaData['id'], lang(90016, ['arenaId' => $arenaData['id']]), [], 2);
            } else {
                Log::UserLog($arenaData['user_id'], lang(90016, ['arenaId' => $arenaData['id']]), []);
                @Log::arenaLog($arenaData['id'], lang(90016, ['arenaId' => $arenaData['id']]), [], 1);
            }
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        $this->cacheArena($arenaId);
        return true;
    }

    /**
     * 重置风险值
     */
    public function resetRisk($arenaId, $return = false) {
        $arenaData = $this->findArena($arenaId);
        if (!$arenaData) {
            return false;
        }
        $arenaData['odds'] = json_decode($arenaData['odds'], true);
        $arenaData['bet_total'] = json_decode($arenaData['bet_total'], true);
        $target = Db::name("arena_target")->where(['arena_id' => $arenaId])->select();
        $arenaData['target_list'] = $target;
        $betMoneyTotal = $arenaData['bet_money'];
        $maxWin = 0;
        $odds = $arenaData['odds'];
        foreach ($arenaData['target_list'] as $key => $val) {
            $maxWin = max($maxWin, $val['bonus']);
        }
        $riskNum = $betMoneyTotal - $maxWin;
        if (!$return) {
            Db::name('arena')->where(['id' => $arenaId])->update(['risk' => $riskNum]);
            $this->cacheArena($arenaId);
        }
        if ($riskNum < 0 && $arenaData['has_sys']) {
            $this->setArenaRiskList($riskNum, $arenaId, $arenaData);
        } else {
            $this->rmArenaRiskList($arenaId);
        }

        return $riskNum;
    }

    /**
     * 写入风险提醒缓存
     * @param $arenaId
     * @param $arenaData
     */
    public function setArenaRiskList($riskNum, $arenaId, $arenaData) {
        $dataList = cache('arena_risk_list');
        if (!$dataList) {
            $dataList = [];
        }
        $teams = $arenaData['teams'];
        $temp = [];
        foreach ($teams as $val) {
            $temp[] = [
                'id' => $val['id'],
                'name' => $val['name']
            ];
        }
        $dataList[$arenaId] = [
            'item_id' => $arenaData['game_type'],
            'risk' => $riskNum,
            'arena_id' => $arenaId,
            'teams' => $temp,
        ];
        cache('arena_risk_list', $dataList);
        cache('arena_risk_list_total', count($dataList));
    }

    /**
     * 将房间移除风险提醒列表
     * @param $arenaId
     */
    public function rmArenaRiskList($arenaId) {
        $dataList = cache('arena_risk_list');
        if (!$dataList) {
            $dataList = [];
        }
        if (isset($dataList[$arenaId])) {
            unset($dataList[$arenaId]);
            cache('arena_risk_list', $dataList);
            cache('arena_risk_list_total', count($dataList));
        }
    }

    /**
     * 获取风险提醒列表
     */
    public function getArenaRiskList() {
        $dataList = cache('arena_risk_list');
        if (!$dataList) {
            $dataList = [];
        }
        return $dataList;
    }

    /**
     * 房间风险数
     */
    public function getArenaRiskListTotal() {
        $total = intval(cache('arena_risk_list_total'));
        if (!$total) {
            $total = 0;
        }
        return $total;
    }

    /**
     * 取消
     * @param int $arenaId
     * @return bool
     */
    public function disabled($arenaId = 0) {
        set_time_limit(0);
        if ((!$arenaId && !$this->pkId) || !$this->admin_id) {
            $this->error = "获取擂台主键ID失败";
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if (in_array($arenaData['status'], [ARENA_DEL, ARENA_STATEMENT_BEGIN, ARENA_STATEMENT_END])) {
                throw new Exception("当前擂台状态无法取消");
            }
            if (!$arenaData['has_sys'] || !$arenaData['has_robot']) { //非系统擂台
                if ($arenaData['classify'] == ARENA_CLASSIFY_CREDIT) {
                    $user = Db::name("user")->where(["id" => $arenaData['user_id']])->find();
                    $creditGoldTotal = Db::name("arena_credit")->where(['arena_id' => $this->pkId])->sum('gold');
                    $creditGoldTotal = floatval($creditGoldTotal);
                    @Log::UserFunds($arenaData['user_id'], FUNDS_CLASSIFY_DIS_ARE, FUNDS_TYPE_GOLD, $creditGoldTotal, $user['gold'], $user['gold'] + $creditGoldTotal, lang('90030', ['arenaId' => $this->pkId]), []);
                    Db::name("user")->where(["id" => $arenaData['user_id']])->setInc("gold", $creditGoldTotal);
                    (new Socket())->userGold($arenaData['user_id'], $creditGoldTotal);
                } else {
                    $depositDetail = Db::name("arena_deposit_detail")->field("SUM(number) as number,user_id,arena_id,has_sys")->where(['arena_id' => $this->pkId])->group("user_id")->select();
                    foreach ($depositDetail as $detail) {
                        if (!$detail['has_sys'] && $detail['user_id'] != SYS_USER_ID) {
                            $user = Db::name("user")->where(["id" => $detail['user_id']])->find();
                            if ($user['has_robot']) {
                                continue;
                            } //机器人也不退回到帐户
                            @Log::UserFunds($arenaData['user_id'], FUNDS_CLASSIFY_DIS_ARE, FUNDS_TYPE_GOLD, $detail['number'], $user['gold'], $user['gold'] + $detail['number'], lang('90009', ['arenaId' => $this->pkId]), $detail);
                            Db::name("user")->where(["id" => $detail['user_id']])->setInc("gold", $detail['number']);
                            (new Socket())->userGold($detail['user_id'], $detail['number']);
                            //(new Socket())->sendToUid($detail['user_id'],['type' => 'gold','gold' => $detail['number']],"socket.to_send_gold_change");
                        } elseif ($detail['user_id'] == SYS_USER_ID) {//系统追加的保证金写入系统收支
                            Log::sysIncome($detail['number'], lang('90009', ['arenaId' => $this->pkId]), ['brok' => 0, 'arena_id' => $this->pkId, 'number' => $detail['number'],], FUNDS_TYPE_GOLD, SYSTEM_INCOME_DEPOSIT);
                        }
                    }
                }
            }
            if ($arenaData['classify'] != ARENA_CLASSIFY_CREDIT) {
                //退回投注用户保证金
                $betList = Db::name("arena_bet_detail")->where(['arena_id' => $this->pkId])->field("user_id,sum(money) as money")->group("user_id")->select();
                if ($betList) {
                    foreach ($betList as $list) {
                        $user = Db::name("user")->where(["id" => $list['user_id']])->find();
                        $data = [
                            'arena_id' => $arenaData['id'],
                            'deposit' => $list['money'],
                        ];
                        @Log::UserFunds($list['user_id']
                                        , FUNDS_CLASSIFY_DIS_ARE
                                        , FUNDS_TYPE_GOLD
                                        , $list['money']
                                        , $user['gold']
                                        , $user['gold'] + $list['money']
                                        , lang('90010', ['arenaId' => $this->pkId])
                                        , $list
                        );
                        Db::name("user")->where(["id" => $list['user_id']])->setInc("gold", $list['money']);
                        (new Socket())->userGold($list['user_id'], $list['money']);
                        //(new Socket())->sendToUid($list['user_id'],['type' => 'gold','gold' => $list['money']],"socket.to_send_gold_change");
                    }
                }
            }
            //更新投注状态
            Db::name("arena_bet_detail")->where(['arena_id' => $this->pkId])->update(['status' => DEPOSIT_CANCEL]);

            //更新擂台状态
            Db::name('arena')->where(['id' => $this->pkId])->update(['status' => ARENA_DIS, 'has_default' => STATUS_NO]);
            @Log::sysOpt($this->admin_id, '封禁擂台(#' . $this->pkId . ')', $arenaData);

            if ($arenaData['has_default']) {
                Db::name('play_rules')->where(['play_id' => $arenaData['play_id'], 'arena_id' => $this->pkId])->update([
                    'arena_id' => 0
                ]);
            }
            if (!$this->queue) {
                (new Message())->sendToArenaGroup($this->pkId, MESSAGE_QUEUE_TYPE_DISABLED, []);
            }
            Db::commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage(); //.$e->getFile().$e->getLine();
            $this->errorNo = $e->getCode();
            Db::rollback();
            return false;
        }
        $this->cacheArena($arenaId);

        //更新比赛
        $data = [];
        $total = Db::name('arena')->where(['play_id' => $arenaData['play_id'], 'private' => ARENA_DISPLAY_ALL, 'status' => ARENA_START])->count();
        $data['has_arena'] = $total ? 1 : 0;
        $data['arena_total'] = ['exp', 'arena_total-1'];
        //检查是否有系统擂台
        $total = Db::name('arena')->where(['play_id' => $arenaData['play_id'], 'has_sys' => 1, 'status' => ARENA_START])->count();
        if (!$total) {
            $data['has_sys_arena'] = 0;
        }
        Db::name('play')->where(['id' => $arenaData['play_id']])->update($data);

        (new Play())->upCache($arenaData['play_id']);
        return true;
    }

    /**
     * 停止比赛下的擂台投注
     * @param $playId
     */
    public function disabledPlay($playId) {
        if (!$this->admin_id) {
            $this->error = '当前擂台状态无法取消';
            return false;
        }
        $play = (new Play())->getPlay($playId);
        if (!$play || !in_array($play['status'], [PLAT_STATUS_SUSP, PLAT_STATUS_WAIT, PLAT_STATUS_CUT])) {
            $this->error = '当前擂台状态无法封禁';
        }

        $lists = Db::name('arena')->where(['play_id' => $playId])->column('id');
        $ids = [];
        if ($lists) {
            $this->queue = true;
            foreach ($lists as $val) {
                $this->disabled($val);
                $ids[] = $val;
            }
        }
        (new Message())->sendToPlayGroup($playId, MESSAGE_QUEUE_TYPE_DISABLED, $ids);
        return true;
    }

    /**
     * 删除
     * @param int $arenaId
     */
    public function del($arenaId = 0) {
        set_time_limit(0);
        if ((!$arenaId && !$this->pkId) || !$this->admin_id) {
            $this->error = "获取擂台主键ID失败";
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if ($arenaData['bet_number'] > 0) {
                throw new Exception("当前擂台已有用户投注，无法删除");
            }
            if (in_array($arenaData['status'], [ARENA_DEL, ARENA_STATEMENT_BEGIN, ARENA_STATEMENT_END, ARENA_END])) {
                throw new Exception("当前擂台状态无法删除");
            }
            $total = Db::name("arena_bet_detail")->where(['arena_id' => $this->pkId])->count();
            if ($total > 0) {
                throw new Exception("当前擂台已有用户投注，无法删除");
            }
            if (!$arenaData['has_sys'] || !$arenaData['has_robot']) { //非系统擂台
                if ($arenaData['classify'] == ARENA_CLASSIFY_CREDIT) {
                    $user = Db::name("user")->where(["id" => $arenaData['user_id']])->find();
                    $creditGoldTotal = Db::name("arena_credit")->where(['arena_id' => $this->pkId])->sum('gold');
                    $creditGoldTotal = floatval($creditGoldTotal);
                    @Log::UserFunds($arenaData['user_id'], FUNDS_CLASSIFY_DIS_ARE, FUNDS_TYPE_GOLD, $creditGoldTotal, $user['gold'], $user['gold'] + $creditGoldTotal, lang('90031', ['arenaId' => $this->pkId]), []);
                    Db::name("user")->where(["id" => $arenaData['user_id']])->setInc("gold", $creditGoldTotal);
                    (new Socket())->userGold($arenaData['user_id'], $creditGoldTotal);
                } else {
                    //$user = Db::name("user")->where(["id" => $arenaData['user_id']])->find();
                    //$deposit = $arenaData['deposit'];
                    //统计系统追加
                    $depositDetail = Db::name("arena_deposit_detail")
                                    ->field("SUM(number) as number,user_id,arena_id,has_sys")
                                    ->where(['arena_id' => $this->pkId])
                                    ->group("user_id")->select();

                    foreach ($depositDetail as $detail) {
                        if (!$detail['has_sys'] && $detail['user_id'] != SYS_USER_ID) {
                            $user = Db::name("user")->where(["id" => $detail['user_id']])->find();
                            if ($user['has_robot']) {
                                continue;
                            } //机器人也不退回到帐户
                            @Log::UserFunds($arenaData['user_id'], FUNDS_CLASSIFY_DIS_ARE, FUNDS_TYPE_GOLD, $detail['number'], $user['gold'], $user['gold'] + $detail['number'], '删除擂台，退回擂台保证金(#' . $this->pkId . ')', $detail);
                            Db::name("user")->where(["id" => $detail['user_id']])->setInc("gold", $detail['number']);
                            (new Socket())->userGold($detail['user_id'], $detail['number']);
                            //(new Socket())->sendToUid($detail['user_id'],['type' => 'gold','gold' => $detail['number']],"socket.to_send_gold_change");
                        } elseif ($detail['user_id'] == SYS_USER_ID) {//系统追加的保证金写入系统收支
                            Log::sysIncome($detail['number'], lang('90011', ['arenaId' => $this->pkId]), [
                                'brok' => 0,
                                'arena_id' => $this->pkId,
                                'number' => $detail['number']
                                    ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_DEPOSIT);
                        }
                    }
                }
            }
            $play_id = $arenaData['play_id'];
            //更新擂台状态
            Db::name('arena')->where(['id' => $this->pkId])->update(['status' => ARENA_DEL, 'has_default' => STATUS_NO]);
            //Db::name("play")->where(['id' => $play_id])->setDec("arena_total");

            if ($arenaData['has_default']) {
                Db::name('play_rules')->where(['play_id' => $arenaData['play_id'], 'arena_id' => $this->pkId])->update([
                    'arena_id' => 0
                ]);
            }
            @Log::sysOpt($this->admin_id, '删除擂台(#' . $this->pkId . ')', $arenaData);
            if (!$this->queue) {
                (new Message())->sendToArenaGroup($this->pkId, MESSAGE_QUEUE_TYPE_DELETE, []);
            }
            Db::commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage(); //.";".$e->getLine();
            $this->errorNo = $e->getCode();
            Db::rollback();
            return false;
        }
        $this->cacheArena($arenaId);
        //更新比赛
        $data = [];
        $total = Db::name('arena')->where(['play_id' => $arenaData['play_id'], 'private' => ARENA_DISPLAY_ALL, 'status' => ARENA_START])->count();
        $data['has_arena'] = $total ? 1 : 0;
        $data['arena_total'] = ['exp', 'arena_total-1'];
        //检查是否有系统擂台
        $total = Db::name('arena')->where(['play_id' => $arenaData['play_id'], 'has_sys' => 1, 'status' => ARENA_START])->count();
        if (!$total) {
            $data['has_sys_arena'] = 0;
        }
        Db::name('play')->where(['id' => $arenaData['play_id']])->update($data);

        (new Play())->upCache($arenaData['play_id']);
        return true;
    }

    /**
     * 计算投注收益
     * @param $money  投注金额
     * @param $odds 投注赔率
     * @param $ruleType 投注玩法类型
     * @param $brok 佣金
     * @param $gameType 投注项目
     * @return mixed
     */
    public function forWin($money, $odds, $ruleType, $brok = 0.00, $gameType = GAME_TYPE_FOOTBALL) {

        //玩法类型赔率未包含本金
        /* $ruleConf = [
          GAME_TYPE_FOOTBALL => [RULES_TYPE_ASIAN,RULES_TYPE_OU,RULES_TYPE_SINGLE_DOUBLE],
          GAME_TYPE_WCG => [RULES_TYPE_ASIAN,RULES_TYPE_OU,RULES_TYPE_SINGLE_DOUBLE],
          GAME_TYPE_BASKETBALL => [RULES_TYPE_ASIAN,RULES_TYPE_OU,RULES_TYPE_SINGLE_DOUBLE],
          ]; */
        $ruleConf = (new Rule())->asianRules;
        if ($ruleType && in_array($ruleType, $ruleConf[$gameType])) {
            $odds = $odds + 1;
        }
        //var_dump($odds);
        $odds = $odds - 1; //扣除本金
        $win = $money * $odds;
        //var_dump($win);
        $brok = numberFormat($win * $brok);
        /**
         * win_money 收益：本金+收益-佣金
         * brok：佣金
         * money：本金
         * win_total：收益+本金
         */
        $result = ['win_money' => ($money + $win) - $brok, 'win' => $win, 'brok' => $brok, 'money' => $money, 'win_total' => $money + $win];
        //var_dump($result);
        return $result;
    }

    /**
     * 计算 佣金
     * @param $money
     * @return string
     */
    function forBrokerage($money) {
        //$brok = floatval(\library\service\Misc::system('sys_player_brok'));
        $brok = floatval(config("system.sys_player_brok"));
        return numberFormat($brok / 100 * $money, 2);
    }

    /**
     * 当前可投注上限
     */
    public function betMaxLimit($odds, $totalBet, $rules = 0, $gameType = GAME_TYPE_FOOTBALL) {
        return $this->factory($gameType)->betMaxLimit($odds, $totalBet, $rules);
    }

    /**
     * 购买查看投注
     * @param unknown $user_id  查看用户id
     * @param unknown $bet_id 投注id
     * */
    public function buyBetView($user_id, $bet_id) {
        $bet_id = intval($bet_id);
        $user_id = intval($user_id);
        //判断用户是否已购买过
        $hasBuy = Db::name('arena_bet_view')->where(['bet_id' => $bet_id, 'buy_user_id' => $user_id])->find();
        if ($hasBuy) {
            return true;
        }
        if ((new User())->checkPlaySmallGame($user_id)) { //玩游戏中不允许，防止金币计算错误
            $this->errorData = ['msg' => '，不允许购买投注'];
            $this->error = 10036;
            return false;
        }
        $betUserId = 0;
        $gold = 0;
        $userSvr = new User();
        if ($userSvr->checkLockUser($user_id)) {
            $this->error = 10005;
            return false;
        }
        $userSvr->lockUser($user_id, true); //加锁
        Db::startTrans();
        try {
            $user_info = Db::name('user')->lock(true)->cache(false)->where(['id' => $user_id])->find();
            $bet_info = Db::name('arena_bet_detail')->where(['id' => $bet_id])->find();
            $view_user_info = Db::name('user')->where(['id' => $bet_info['user_id']])->find();
            if (!$user_info || !$bet_info) {
                throw new Exception(10006);
                $this->error = 10006;
                return false;
            }
            if ($user_info["gold"] < $bet_info["buy"]) {
                $this->error = 10014;
                throw new Exception(10014);
                return false;
            }
            $betUserId = $bet_info['user_id'];
            $view_user_info = Db::name('user')->where(['id' => $bet_info['user_id']])->find();
            $gold = $bet_info["buy"];
            Db::name('user')->where(['id' => $user_id])->setDec('gold', $bet_info["buy"]); //扣除购买都金币
            Db::name('user')->where(['id' => $bet_info['user_id']])->update([
                'gold' => ['exp', "gold+{$bet_info["buy"]}"],
                'bet_view_total' => ['exp', "bet_view_total+1"],
            ]); //更新投注者金币
            Db::name('arena_bet_detail')->where(['id' => $bet_id])->update([
                'buy_count' => ['exp', 'buy_count+1'],
                'buy_total' => ['exp', "buy_total+{$bet_info["buy"]}"],
            ]);
            Db::name('arena_bet_view')->insert([
                'bet_id' => $bet_id,
                'buy_user_id' => $user_id,
                'buy' => $bet_info['buy'],
                'create_time' => time()
            ]);
            $playSvr = (new Play());
            $arena = $this->getCacheArenaById($bet_info['arena_id']);
            $teams = $playSvr->getTeams($arena['play_id']);
            if (count($teams) == 2) {
                $log = "{$teams[0]['name']} vs {$teams[1]['name']}";
            } else {
                $match = getMatch($arena['match_id']);
                $log = $match['name'];
            }
            //查看{:nickname}的投注单（{:team}）

            Log::UserFunds($user_id, FUNDS_CLASSIFY_VIEW_DED, FUNDS_TYPE_GOLD, '-' . $bet_info["buy"], $user_info["gold"], $user_info["gold"] - $bet_info["buy"], lang(90008, ['nickname' => $view_user_info['nickname'], 'team' => $log]), ["table" => "arena_bet_detail", "id" => $bet_id, "mark" => "查看投注详情"]
            );
            Log::UserFunds($bet_info["user_id"], FUNDS_CLASSIFY_VIEW_REC, FUNDS_TYPE_GOLD, $bet_info["buy"], $view_user_info["gold"], $view_user_info["gold"] + $bet_info["buy"], lang(90007, ['nickname' => $user_info['nickname'], 'team' => $log]), ["table" => "arena_bet_detail", "id" => $bet_id, "mark" => "投注被查看"]
            );
            Db::commit();
            $userSvr->lockUser($user_id, false);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            $userSvr->lockUser($user_id, false);
            return false;
        }
        (new User())->setCacheUser($betUserId);
        (new User())->setCacheUser($user_id);
        //投注者
        (new Socket())->userGold($betUserId, $gold);
        //(new Socket())->sendToUid($betUserId,['type' => 'gold','gold' => $gold],"socket.to_send_gold_change");
        //查看者
        // (new Socket())->sendToUid($user_id,['type' => 'gold','gold' => -$gold],"socket.to_send_gold_change");
        (new Socket())->userGold($user_id, -$gold);
        return true;
    }

    /**
     * 更新查看价格
     */
    public function upArenaDetailBuyPrice($arena_bet_detail_id, $user_id, $price) {
        $price = intval($price);
        $bet = Db::name("arena_bet_detail")->where(['user_id' => $user_id, 'id' => $arena_bet_detail_id])->find();
        if (!$bet) {
            $this->error = 10013;
            return false;
        }
        $user = (new User())->getUser($bet['user_id']);
        if ($price > $user['level']['look']) {//如果设置的价格大于了，当前用户等级可设置的价格时
            $this->error = 20101;
            return false;
        }
        Db::name("arena_bet_detail")->where(['id' => $arena_bet_detail_id, 'user_id' => $user_id])->update([
            'buy' => $price
        ]);
        return true;
    }

    /**
     * 缓存擂台数据
     */
    public function cacheArena($arena_id) {
        $arena = $this->findArena($arena_id);
        if ($arena) {
            $arena['odds'] = json_decode($arena['odds'], true);
            $arena['bet_total'] = json_decode($arena['bet_total'], true);
            $target = Db::name("arena_target")->where(['arena_id' => $arena_id])->select();
            $arena['target_list'] = $target;
            $arena['share_url'] = $this->getArenaShareUrl($arena_id);
            cache("arena_{$arena_id}", $arena, $this->expire);
            cache("arena_{$arena['mark']}", $arena['id'], $this->expire);
            return true;
        }
        return false;
    }

    /**
     * 更新比赛下的全部擂台缓存
     * @param $playId
     */
    public function cacheArenaByPlay($playId) {
        $limit = 50;
        $page = 1;
        while (true) {
            $offset = ($page - 1) * $limit;
            $list = Db::name('arena')->field("id")->where(['play_id' => $playId])->limit($offset, $limit)->select();
            if (!$list) {
                break;
            }
            foreach ($list as $val) {
                $this->cacheArena($val['id']);
            }
            if (count($list) == $limit)
                $page++;
            else
                break;
        }
    }

    private function upCacheArena($arena_id, $upData) {
        cache("arena_{$arena_id}", $upData, $this->expire);
    }

    /**
     * 获取擂台缓存数据
     * @param $arena_id
     * @return mixed
     */
    public function getCacheArenaById($arena_id) {
        $data = cache("arena_{$arena_id}");
        if (!$data) {
            $this->cacheArena($arena_id);
            return cache("arena_{$arena_id}");
        }
        if (!isset($data['share_url'])) {
            $data['share_url'] = $this->getArenaShareUrl($arena_id); //config("site_domain")."share/arena/{$arena_id}.html";
        }
        return $data;
    }

    /**
     * 获取擂台缓存数据
     * @param $arena_id
     * @return mixed
     */
    public function getCacheArenaByMark($mark) {
        $arenaId = cache("arena_{$mark}");
        if (!$arenaId) {
            $arena = Db::name("arena")->where(['mark' => $mark])->find();
            if ($arena) {
                $this->cacheArena($arena['id']);
            }
            $arenaId = $arena['id'];
        }
        return $this->getCacheArenaById($arenaId);
    }

    /**
     * 擂台邀请码验证
     * @param $arenaId
     * @param $userId
     * @return bool
     */
    public function checkArenaInvite($arenaId, $userId, $code = '') {
        $arenaId = intval($arenaId);
        $userId = intval($userId);
        if (!$arenaId || !$userId) {
            return false;
        }
        $arena = $this->getCacheArenaById($arenaId);
        if ($userId == $arena['user_id']) {
            return true;
        }
        $arenaPrivate = cache("arena_private_{$arenaId}");
        if (!$code && !$arenaPrivate) {
            $this->errorNo = NOT_INVITE;
            return false;
        }
        $arenaPrivate = $arenaPrivate ? $arenaPrivate : [];
        if (in_array($userId, $arenaPrivate)) {
            return true;
        }

        if ($code) {

            if ($code == $arena['invit_code']) {
                $arenaPrivate[] = $userId;
                cache("arena_private_{$arenaId}", $arenaPrivate);
                return true;
            }
        }
        $this->errorNo = NOT_INVITE;
        return false;
    }

    /**
     * 擂台好友验证
     * @param $arendId
     * @param $userId
     * @param null $_
     */
    public function checkArenaFriend($arenaId, $userId, $_ = null) {
        $arenaId = intval($arenaId);
        $userId = intval($userId);
        if (!$arenaId || !$userId) {
            return false;
        }
        $arena = $this->arenaData ? $this->arenaData : $this->getCacheArenaById($arenaId);
        $arenaUserId = $arena['user_id'];
        if ($arenaUserId == $userId) {
            return true;
        }
        $user = $this->userSvr->getUser($arenaUserId);
        if (!isset($user['friends']) || !$user['friends']) {
            $user['friends'] = [];
        }
        if (in_array($userId, $user['friends'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 授信用户检查
     * @param $arenaId
     * @param $userId
     * @param $pwd
     */
    public function checkArenaCreditPwd($arenaId, $userId, $mark, $pwd) {
        $arenaId = intval($arenaId);
        $userId = intval($userId);
        if (!$arenaId || !$userId || !$pwd || !$mark) {
            $this->error = 40163;
            return false;
        }
        $arena = $this->getCacheArenaById($arenaId);
        if (!$arena || $arena['classify'] != ARENA_CLASSIFY_CREDIT) {
            $this->error = 40163;
            return false;
        }
        //检查当前用户是否已绑定了当前征信局
        $user = Db::name('arena_credit')->where(['arena_id' => $arenaId, 'user_id' => $userId, 'mark' => ['neq', $mark]])->find();
        if ($user) {
            $this->error = 40168;
            return false;
        }
        $ret = Db::name('arena_credit')->where(['arena_id' => $arenaId, 'mark' => $mark])->find();
        if (!$ret) {
            $this->error = 40163;
            return false;
        }
        if ($ret['user_id'] && $ret['user_id'] != $userId) {
            $this->error = 40169;
            return false;
        }
        if ($ret['code'] != $pwd) {
            $this->error = 40170;
            return false;
        }
        if (!$ret['user_id']) {
            Db::name('arena_credit')->where(['id' => $ret['id']])->update(['user_id' => $userId]);
        }
        return $ret;
    }

    /**
     * 根据比赛ID，玩法按奖池排序
     */
    public function getTopArena($playId, $rulesType = null, $whereStatus = false, $ruleId = 0) {
        $data = ''; //cache("top_arena_{$playId}");
        if (!$data) {

            $playId = intval($playId);
            $rulesType = intval($rulesType);
            if (!$playId) {
                return false;
            }
            $where = [];
            $where['play_id'] = $playId;
            $where['private'] = ARENA_DISPLAY_ALL;
            if (!$whereStatus) {
                $where['status'] = ['in', [ARENA_START, ARENA_SEAL]];
            } else {
                $where['status'] = ['not in', [ARENA_DEL, ARENA_DIS]];
            }
            $where['has_hide'] = STATUS_NO; //非隐私擂台
            $where['classify'] = ARENA_CLASSIFY_GOLD; //金币擂台
            $where['has_sys'] = 1; //系统擂台
            if ($rulesType) {
                $where['rules_type'] = $rulesType;
            }
            if ($rulesType == RULES_TYPE_OTHER && $ruleId) {
                $where['rules_id'] = $ruleId;
            }
            $data = Db::name('arena')->field('(deposit+bet_money) as total,id,mark,user_id')->where($where)->order("status asc,total desc")->find();
            if (!$data) { //如果不存在公开擂台，则取其他非隐藏擂台
                unset($where['private']);
                $data = Db::name('arena')->field('(deposit+bet_money) as total,id,mark,user_id')->where($where)->order("status asc,total desc")->find();
            }
            //cache("top_arena_{$playId}",1800);
        }
        return $data;
    }

    /**
     * 擂台投注
     * @param $arenaId 擂台ID
     * @param $money 投注金额
     * @param $userId 投注用户
     * @param $target 投注项
     * @param $item 二级投注项
     * @param $agentUserId 代理用户ID
     */
    public function betting($arenaId, $money, $userId, $target, $item = '', $agentUserId = 0, $credit_mark = '', $guid = '', $orderId) {

        $arenaId = intval($arenaId);
        $agentUserId = intval($agentUserId);
        $money = intval($money);

        if (!$arenaId && !$this->pkId) { //检查擂台ID
            $this->error = 40004;
            return false;
        }
        if (intval($money) <= 0) { //检查投注金额是否小于1
            $this->error = 40005;
            $this->errorData['min_deposit'] = MIN_DEPOSIT;
            return false;
        }


        $arena = $this->arenaData ? $this->arenaData : $this->getCacheArenaById($arenaId);
        if (!$arena) { //检查擂台是否存在
            $this->error = 40004;
            return false;
        }
        if ($arena['status'] != ARENA_START) {//检查擂台状态
            $this->error = 40006;
            return false;
        }
        //是否是征信局
        $isArenaCredit = $arena['classify'] == ARENA_CLASSIFY_CREDIT ? true : false;
        //检查擂台隐私设置
        if (!$isArenaCredit) { //金币局判断隐私设置
            $private = $arena['private'];
            if (!$this->admin_id && $private == ARENA_DISPLAY_FRIENDS && !$this->checkArenaFriend($arenaId, $userId)) { //仅好友
                $this->error = 40007;
                return false;
            } elseif (!$this->admin_id && $private == ARENA_DISPLAY_CODE && !$this->checkArenaInvite($arenaId, $userId)) { //邀请码
                $this->error = 40008;
                return false;
            }
        }

        if ($money < $arena['min_bet'] && !$this->admin_id) {
            $this->error = 40005;
            $this->errorData['min_deposit'] = $arena['min_bet'];
            return false;
        }

        if ($money > BET_MONEY_MAX && !$this->admin_id) {
            $this->error = 40055;
            $this->errorData['max_deposit'] = BET_MONEY_MAX;
            return false;
        }

        //检查投注项是否存在
        if (!$targetData = $this->checkArenaTarget($target, $item, $arena['target_list'], $arena['game_type'])) {

            $this->error = 40012;
            return false;
        }
        $playSvr = (new Play());
        $play = $playSvr->getPlay($arena['play_id']);
        if (!$play) {//检查比赛是否存在
            $this->error = 40009;
            return false;
        }

        $ruleType = $arena['rules_type']; //(new Rule())->factory($arena['game_type'])->getRuleType($arena['rules_id']);
        //检查比赛状态
        if (in_array($play['status'], [PLAT_STATUS_START, PLAT_STATUS_INTERMISSION]) || $play['play_time'] <= time()) {//检查比赛状态
            $this->error = 40010;
            return false;
        }
        if (in_array($play['status'], [PLAT_STATUS_END, PLAT_STATUS_EXC, PLAT_STATUS_SUSP, PLAT_STATUS_STATEMENT]) || $play['play_time'] <= time()) {//检查比赛状态
            $this->error = 40011;
            return false;
        }

        //获取当前投注项赔率
        $currentOdds = $targetData['item'] ? $arena['odds'][$targetData['target']][$targetData['item']] : $arena['odds'][$targetData['target']];
        
        // '胜负(让球)'，'大小(亚盘)'，'单双(亚盘)': 改为欧盘 赔率加1
        if ($ruleType == (1 || 112 || 12)) {
            $currentOdds += 1;
        }
        if (!$isArenaCredit) { //非征信局判断投注上限
            //获取当前擂台可接收的最大投注上限
            $betMaxLimit = $this->betMaxLimit($currentOdds, $targetData['deposit'], $ruleType, $arena['game_type']);
            if ($money > $betMaxLimit) {
                $this->error = 40013;
                $this->errorData['max_bet_limit'] = $betMaxLimit;
                return false;
            }
        }

        //先更新缓存数据
        $arena['bet_money'] = $arena['bet_money'] + $money;
        $arena['bet_number'] = $arena['bet_number'] + 1;

        //佣金
        $brok = (new Misc())->getPlayBrokerage($arena['game_type'], $arena['play_id'], $arena['rules_id']);
        //计算收益，投注项中的扣除当前投注可能最大收益
        $winData = $this->forWin($money, $currentOdds, $ruleType, $brok, $arena['game_type']);
        $income = $winData['win']; //可能收益，未扣除佣金
        //$income = $this->forWin($money,$currentOdds,$arena['rules_type'],false,true,$arena['game_type']);
        if ($item && $targetData['item']) {
            $arena['bet_total'][$target][$item]['money'] = $arena['bet_total'][$target][$item]['money'] + $money;
            $arena['bet_total'][$target][$item]['deposit'] = $arena['bet_total'][$target][$item]['deposit'] - $income;
        } else {
            $arena['bet_total'][$target]['money'] = $arena['bet_total'][$target]['money'] + $money;
            $arena['bet_total'][$target]['deposit'] = $arena['bet_total'][$target]['deposit'] - $income;
        }

        $targetList = $arena['target_list'];
        foreach ($targetList as $key => $val) {
            if ($val['target'] == $target && $item == $val['item']) {
                $val['money'] += $money;
                $val['deposit'] -= $income;
                $val['bonus'] = $val['bonus'] + $income + $money;
                $val['number'] += 1;
            }
            $targetList[$key] = $val;
        }
        $arena['target_list'] = $targetList;
        //更新缓存
        $this->upCacheArena($arenaId, $arena);
        $userSvr = new User();
        if ($userSvr->checkLockUser($userId)) {
            $this->error = 10005;
            return false;
        }
        $userSvr->lockUser($userId, true); //加锁
        Db::startTrans();
        $arenaBetDetailId = 0;
        $ret = [];
        try {
            //检查投注用户金币数量
            $user = Db::name("user")->where(['id' => $userId])->lock(true)->cache(false)->find();
            if (!$user) {
                $this->error = 10006;
                throw new Exception(10006);
            }
            if ($user['status'] != STATUS_ENABLED) {
                $this->error = 10023;
                throw new Exception(10023);
            }

            $userGold = $user['gold']; //用户可用金币
            if ($user['has_robot'] == 1 && $this->admin_id) { //后台补注+机器人,直接将可用金币设置成投注金币
                $userGold = $money;
            }

            $realName = '';
            //征信局判断
            if ($isArenaCredit) {
                $_temp = Db::name('arena_credit')->where([
                            'arena_id' => $arenaId,
                            'user_id' => $userId,
                            'mark' => $credit_mark
                        ])->find();
                if (!$_temp) {
                    throw new Exception(40164);
                }
                $userGold = $_temp['avail_gold']; //重置用户可用金币为授信额度
                $realName = $_temp['name'];
            }
            if ($userGold < $money) {
                $this->error = 10007;
                throw new Exception(10007);
            }

            //重新检查投注上限
            $where = [];
            $where['arena_id'] = $arenaId;
            $where['target'] = $target;
            if ($item && $targetData['item']) {
                $where['item'] = $item;
            }
            $arenaTarget = Db::name('arena_target')->where($where)->find();
            if (!$isArenaCredit && $arenaTarget['deposit'] < $income) {
                $this->error = 40013;
                $this->errorData['max_bet_limit'] = $arenaTarget['deposit'];
                throw new Exception(40013);
            }

            //单人投注上限检查,上限为累计
            $maxBet = intval($arena['max_bet']);
            if (!$isArenaCredit && $maxBet > 0) {
                $myBetTotal = 0;
                $bets = Db::name('arena_bet_detail')->where(['arena_id' => $arenaId, 'user_id' => $userId])->select();
                if ($bets) {
                    foreach ($bets as $val) {
                        $myBetTotal += $val['money'];
                    }
                }
                if ($maxBet - $myBetTotal <= 0 && !$this->admin_id) {
                    $this->error = 40014;
                    $this->errorData['number'] = $maxBet;
                    throw new Exception(40014);
                }
            }


            //更新用户数据
            //Db::name("user")->where(['id' => $userId])->setDec('gold',$money);
            if ($isArenaCredit) {
                Db::name('arena_credit')->where([
                    'arena_id' => $arenaId,
                    'user_id' => $userId
                ])->update([
                    'avail_gold' => ['exp', "avail_gold-{$money}"]
                ]);
            } else {
                $tmpMoney = $money;
                if ($user['has_robot'] == 1 && $this->admin_id) { //后台补注+机器人,不扣除金币
                    $tmpMoney = 0;
                }
                Db::name("user")->where(['id' => $userId])->update([
                    'deposit_total' => ['exp', 'deposit_total+1'],
                    'gold' => ['exp', "gold-{$tmpMoney}"],
                    'deposit_money' => ['exp', "deposit_money+{$money}"]
                ]);
            }
            //更新擂台数据
            Db::name("arena")->cache(false)->where(['id' => $arenaId])->update([
                'bet_money' => ['exp', "bet_money+{$money}"],
                'bet_number' => ['exp', "bet_number+1"],
                'bet_total' => json_encode($arena['bet_total'])
            ]);
            //更新擂台投注数据
            $where = [];
            $where['arena_id'] = $arenaId;
            $where['target'] = $target;
            if ($item && $targetData['item']) {
                $where['item'] = $item;
            }
            $sysMoney = $user['has_robot'] == 1 && $this->admin_id ? $money : 0; //后台补注+机器人投注算系统补注
            $upArenaTargetData = [
                'money' => ['exp', "money+{$money}"],
                'deposit' => ['exp', "deposit-{$income}"],
                'bonus' => ['exp', "bonus+{$income}+{$money}"],
                'sys_money' => ['exp', "sys_money+{$sysMoney}"],
                'number' => ['exp', 'number+1']
            ];
            if ($isArenaCredit) {
                unset($upArenaTargetData['deposit']); //征信局没有上限
            }
            Db::name('arena_target')->cache(false)->where($where)->update($upArenaTargetData);
            //非征信局更新此擂台其他投注的可投注额度上限
            if (!$isArenaCredit) {
                $where = [];
                $where['arena_id'] = $arenaId;
                $where['target'] = ['neq', $target];
                if ($item && $targetData['item']) {
                    Db::name('arena_target')->cache(false)->where("arena_id={$arenaId} AND ((target='{$target}' AND `item`<>'{$item}') OR (target <> '{$target}'))")->update(['deposit' => ['exp', "deposit+{$money}"],]);
                } else {
                    Db::name('arena_target')->cache(false)->where(['arena_id' => $arenaId, 'target' => ['neq', $target]])->update(['deposit' => ['exp', "deposit+{$money}"],]);
                }
            }

            $under = 0;
            if (isset($arena['odds']['under'])) {
                $under = $arena['odds']['under'];
            } elseif (isset($arena['odds']['over'])) {
                $under = $arena['odds']['over'];
            }

            //写入擂台投注数据
            $detailData = [
                'arena_id' => $arenaId,
                'odds' => $currentOdds,
                'handicap' => isset($arena['odds']['handicap']) ? $arena['odds']['handicap'] : 0,
                'under' => $under,
                'over' => $under,
                'money' => $money,
                'target' => $target,
                'item' => $item,
                'user_id' => $userId,
                'order_id' => $orderId,
                'agent_id' => $agentUserId,
                'status' => DEPOSIT_NOT_START,
                'brok' => $brok, //佣金
                'create_time' => time(),
            ];
            $arenaBetDetailId = Db::name("arena_bet_detail")->cache(false)->insertGetId($detailData);
            $ret = [
                'bet_id' => $arenaBetDetailId,
                'odds' => $currentOdds,
                'money' => $money,
                'target' => $target,
                'item' => $item,
                'brok' => $brok,
            ];
            //更新比赛总奖池
            Db::name('play')->where(['id' => $arena['play_id']])->setInc('total_prize', $money);
            //写入用户日志
            $teams = $playSvr->getTeams($arena['play_id']);
            if (count($teams) == 2) {
                $log = "{$teams[0]['name']} vs {$teams[1]['name']}";
            } else {
                $match = getMatch($play['match_id']);
                $log = $match['name'];
            }
            $alog = "{$user['nickname']}（{$target},{$item}）";

            if ($user['has_robot'] == 1 && $this->admin_id) {
                Log::sysIncome(-$money, lang(90006, ['team' => $log]), [
                    'brok' => 0,
                    'arena_id' => $arenaId,
                    'number' => $money,
                    'user_id' => $userId
                        ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_ARENA_BETTING);
            } else {

                if ($isArenaCredit) {
                    $msg = lang(90006, ['team' => "征信局,{$log}"]);
                    $alog = "{$realName}({$user['nickname']},{$target},{$item}）";
                    Log::UserFunds($userId, FUNDS_CLASSIFY_DEP, FUNDS_TYPE_GOLD, -$money, $userGold, $userGold - $money, $msg, $detailData);
                } else {
                    Log::UserFunds($userId, FUNDS_CLASSIFY_DEP, FUNDS_TYPE_GOLD, -$money, $user['gold'], $user['gold'] - $money, lang(90006, ['team' => $log]), $detailData);
                }
            }
            @Log::arenaLog($arenaId, lang(90006, ['team' => $alog]), $detailData);

            //代理检查
            $agentUserId = intval($agentUserId);
            if (!$isArenaCredit && $agentUserId > 0) {
                $agentSvr = new Agent();
                $data['explain'] = lang(90006, ['team' => $log]);
                $data['arena_id'] = $arenaId;
                $data['user_id'] = $userId;
                $data['arena_bet_detail_id'] = $arenaBetDetailId;
                $data['target'] = $target;
                $data['item'] = $item; //getBetTargetToRulesName
                $data['target_name'] = (new Rule())->factory($arena['game_type'])->getBetTargetToRulesName($arena['rules_id'], $target, $item);
                $agentSvr->upUserBetWin($money, $agentUserId, $arena['user_id'], $arenaId, $data);
            }
            Db::commit();
            $userSvr->lockUser($userId, false);
        } catch (\Exception $e) {
            $this->error = $e->getMessage(); //.$e->getFile().$e->getLine();
            $this->errorNo = $e->getCode();
            $this->cacheArena($arenaId); //失败后重新更新缓存数据
            Db::rollback();
            $userSvr->lockUser($userId, false);
            return false;
        }
        $this->resetRisk($arenaId);
        //$this->cacheArena($arenaId);
        $this->miscSvr->setCacheTotal('turnover', $money); //流水
        $this->miscSvr->setCacheTotal('betting', $money); //投注总额
        (new User)->cacheArenaBet($userId, $arena['play_id'], $arenaId, $arenaBetDetailId);

        return $ret;
    }

    /**
     * 返回擂台开奖结果
     * @param $winTarget
     * @param $arenaOddsData
     * @return string
     */
    public function getResult($winTarget, $arenaOddsData) {
        if (!$winTarget) {
            return "";
        }
        $winTarget = is_string($winTarget) ? @json_decode($winTarget, true) : $winTarget;
        return isset($winTarget['target_name']) ? $winTarget['target_name'] : '其他';
        /* if(!isset($winTarget['target'])){return "其他";}
          $target = $winTarget['target'];
          $item = isset($winTarget['item']) ? $winTarget['item'] : "其他";
          foreach($arenaOddsData as $val){
          if(($target == $val['target'] && !$item) || ($item && $item == $val['item'] && $target == $val['target'])){
          return $val['name'] ? $val['name'] : $val['target_name'];
          }
          } */
        // return '其他';
    }

    /**
     * 检查投注项
     * @param $target
     * @param $targetList
     * @param int $gameType
     * @return bool|arena\返回匹配的擂台投注项数据
     */
    public function checkArenaTarget($target, $item, $targetList, $gameType = GAME_TYPE_FOOTBALL) {
        $handle = $this->factory($gameType);
        return $handle ? $handle->checkArenaTarget($target, $targetList, $item) : false;
    }

    /**
     * 发布擂台
     * @param $deposit 保证金
     * @param $odds 赔率
     * @param $userId 擂主ID
     * @param $playId 比赛
     * @param $ruleId 玩法
     * @param $matchId 赛事
     * @param $oddsId 赔率ID
     * @param $private 隐私
     * @param $inCode 邀请码
     * @param $has_sys 是否是系统发布
     * @param $has_robot 是否是机器人发布
     */
//$deposit,$odds,$this->myUserId,$playId,$ruleId,$minBet,$maxBet,$oddsId,$private,$inCode
    //$deposit,$odds,$userId,$playId,$ruleId,$minBet=0,$maxBet = 0,$oddsId = 0,$private = 1,$inCode = '',$has_sys = 0,$has_robot = 0,$hasHide = STATUS_NO,$intro = '',$classify = ARENA_CLASSIFY_GOLD
    public function publish($data, $userId, $guid = '') {
        $deposit = isset($data['deposit']) ? floatval($data['deposit']) : 0; //押金
        $playId = isset($data['play_id']) ? intval($data['play_id']) : 0; //比赛ID
        $ruleId = isset($data['rule_id']) ? intval($data['rule_id']) : 0; //玩法ID
        $minBet = isset($data['min_bet']) ? floatval($data['min_bet']) : 0; //最低投注
        $maxBet = isset($data['max_bet']) ? floatval($data['max_bet']) : 0; //累计最高投注
        $oddsId = isset($data['odds_id']) && is_numeric($data['odds_id']) ? intval($data['odds_id']) : 0; //赔率ID
        $private = isset($data['private']) ? intval($data['private']) : 0; //隐私
        $has_sys = isset($data['has_sys']) ? intval($data['has_sys']) : 0; // 是否系统庄
        $has_robot = isset($data['has_robot']) ? intval($data['has_robot']) : 0; //是否机器人
        $hasHide = isset($data['has_hide']) ? intval($data['has_hide']) : 0; //是否隐藏
        $classify = isset($data['classify']) ? intval($data['classify']) : 0; //擂台类型
        $intro = isset($data['intro']) ? $data['intro'] : ''; //宣传语
        $odds = isset($data['odds']) ? $data['odds'] : ''; //赔率
        $companyId = isset($data['company_id']) ? intval($data['company_id']) : 0; //赔率公司
        $autoUpdateOdds = isset($data['auto_update_odds']) ? intval($data['auto_update_odds']) : 0; //是否自动更新
        $inCode = $this->getInvitationCode();
        $private = abs(intval($private));
        $has_sys_arena = 0; //是否是系统摆擂
        //判断隐私 ARENA_DISPLAY_ALL
        if (!in_array($private, [ARENA_DISPLAY_ALL, ARENA_DISPLAY_FRIENDS, ARENA_DISPLAY_CODE])) {
            $private = ARENA_DISPLAY_CODE;
        }
        if (!$has_sys && $private == ARENA_DISPLAY_ALL) { //非系统不能摆公开擂台
            $private = ARENA_DISPLAY_CODE;
        }
        if ($private != ARENA_DISPLAY_CODE || !in_array($hasHide, [STATUS_YES, STATUS_NO])) {
            $hasHide = STATUS_NO;
        }
        if (!$classify) {
            $classify = ARENA_CLASSIFY_GOLD;
        }
        if ($classify == ARENA_CLASSIFY_CREDIT) {//征信局不扣押金
            $deposit = 0;
            $minBet = 0;
            $maxBet = 0;
        }

        //只保留50个宣传语
        $intro = Stringnew::msubstr($intro, 0, 50, 'utf-8', '');

        if ($classify == ARENA_CLASSIFY_GOLD && ($minBet < 0 || $maxBet < 0)) {
            $this->error = 40150;
            return false;
        }
        if ($classify == ARENA_CLASSIFY_GOLD && $deposit < $minBet) {
            $this->error = 40152;
            return false;
        }
        $minBet = intval($minBet);
        $maxBet = intval($maxBet);
        if ($minBet && $maxBet && $minBet > $maxBet) {
            $this->error = 40153;
            return false;
        }

        if ($has_robot) { //如果是机器人,随机获取一个机器人用户
            $user = Db::name("user")->where(['has_robot' => 1])->order("RAND()")->find();
            $userId = $user['id'];  //机器 人非系统擂台
            //$has_sys_arena = 1;
        } elseif ($has_sys) { //系统摆擂台
            $userId = SYS_USER_ID;
            $has_sys_arena = 1;
        }
        if (!$userId) { //判断用户ID
            $this->error = 10022;
            return false;
        }

        if ($deposit < 1 && $classify == ARENA_CLASSIFY_GOLD) { //判断保证金是否为0
            $this->error = 40101;
            return false;
        }

        $playSvr = new Play();
        $play = $playSvr->getPlay($playId);
        if (!$play) {
            $this->error = 40103;
            return false;
        }
        if ($play['has_sys_arena']) {
            $has_sys_arena = $play['has_sys_arena'];
        }
        //判断-比赛状态
        if ($play['status'] != PLAT_STATUS_NOT_START) {
            $this->error = 40104;
            return false;
        }
        //判断-离比赛开始前多长时间不能开擂
        $sys_max_arena_open_time = intval(config("system.sys_max_arena_open_time"));
        if ($play['play_time'] - ($sys_max_arena_open_time * 60) <= time()) {
            $this->error = 40105;
            return false;
        }
        //玩家开的庄的单笔最低投注
        $sys_user_min_bet_money = intval(config("system.sys_user_min_bet_money"));
        if (!($has_robot || $has_sys) && $classify != ARENA_CLASSIFY_CREDIT && !$has_sys && $sys_user_min_bet_money && $minBet < $sys_user_min_bet_money) {
            $this->error = 40107;
            $this->errorData['text'] = "最低投注不能少于{$sys_user_min_bet_money}元";
            return false;
        }



        $itemId = $play['game_type'];
        $matchId = $play['match_id'];
        $gameId = intval($play['game_id']);
        $teams = $playSvr->getTeams($playId);
        $rulesSvr = (new Rule())->factory($itemId);
        //获取玩法类型
        $ruleType = $rulesSvr->getRuleType($ruleId);
        //检查玩法是否存在
        if (!$rulesSvr->checkRule($ruleId)) {
            $this->error = 40106;
            return false;
        }

        //自动更新
        if ($autoUpdateOdds) {

            if (!$oddsId) {
                $this->error = 40154;
                return false;
            }
            $oddsData = Db::name('odds')->where(['play_id' => $playId, 'id' => $oddsId])->find();
            if (!$oddsData) {
                $this->error = 40155;
                return false;
            }
            $companyId = $oddsData['odds_company_id'];
            $tempOdds = @json_decode($oddsData['odds'], true);
            if (isset($tempOdds['time'])) {
                $odds = $tempOdds['time'];
            } else {
                $odds = $tempOdds['init'];
            }

            if ($itemId != GAME_TYPE_FOOTBALL) {
                $odds = $rulesSvr->parseOddsTableToOddsData($odds);
            }

            $odds = $this->_parseOddsDataToArenaOdds($odds);
        }

        //检查最低保证金
        $minDeposit = $play['min_deposit'] ? $play['min_deposit'] : $playSvr->getMinDeposit($play['game_type'], $ruleId);

        if (!($has_robot || $has_sys) && $classify == ARENA_CLASSIFY_GOLD && $deposit < $minDeposit) {
            $this->error = 40102;
            $this->errorData = ['number' => $minDeposit];
            return false;
        }

        //生成擂台唯一码
        $arenaId = 0;
        $mark = uniqidReal('a', 14);
        $handle = $this->factory($itemId);
        //赔率数据格式及数值检查
        $ret = $handle->checkPublishOdds($odds, $ruleId);
        if ($ret !== true && !is_array($ret)) {
            $this->error = 40107;
            $this->errorData = ['text' => $ret];
            return false;
        }

        if (is_array($ret)) {
            $odds = $ret;
        }
        if (count($teams) == 2) {
            $log = "{$teams[0]['name']} vs {$teams[1]['name']}";
        } else {
            $match = getMatch($play['match_id']);
            $log = $match['name'];
        }

        $userSvr = new User();
        if ($userSvr->checkLockUser($userId)) {
            $this->error = 10005;
            return false;
        }
        $userSvr->lockUser($userId, true); //加锁
        Db::startTrans();
        try {
            $user = Db::name('user')->where(['id' => $userId])->lock(true)->cache(false)->find();
            if (!$user) {
                throw new Exception(40108);
            }
            //不是机器 && 不是房主 && 不是系统用户
            if (!$user['has_robot'] && !$user['has_homeowner'] && $userId != SYS_USER_ID) {
                throw new Exception(20112);
            }

            $newOdds = $this->parseOdds($odds, $ruleId);
            if (isset($newOdds['handicap']) && $ruleType == RULES_TYPE_ASIAN && $itemId != GAME_TYPE_FOOTBALL) { //非足球转换分数
                $newOdds['handicap'] = under($newOdds['handicap'], false, false);
            }
            //擂台投注项列表
            list($bet_total, $targetData) = $handle->getTargetData($ruleId, $deposit, $newOdds);
            $inData = [];
            $inData['mark'] = $mark;
            $inData['auto_update_odds'] = $autoUpdateOdds;
            $inData['has_sys'] = $has_sys;
            $inData['has_robot'] = $has_robot;
            $inData['has_hide'] = $hasHide;
            $inData['classify'] = $classify;
            $inData['user_id'] = $userId;
            $inData['user_nickname'] = $user['nickname'];
            $inData['game_type'] = $itemId;
            $inData['game_id'] = $gameId;
            $inData['play_id'] = $playId;
            $inData['match_id'] = $matchId;
            $inData['rules_id'] = $ruleId;
            $inData['rules_type'] = $ruleType;
            $inData['company_id'] = $companyId;
            $inData['odds_id'] = $oddsId;
            $inData['odds'] = json_encode($newOdds);
            $inData['bet_total'] = json_encode($bet_total);
            $inData['deposit'] = $deposit;
            $inData['status'] = ARENA_START;
            $inData['private'] = $private;
            $inData['invit_code'] = $inCode;
            $inData['min_bet'] = $minBet;
            $inData['max_bet'] = $maxBet;
            $inData['intro'] = $intro;
            $inData['brok'] = (new Misc())->getMakerBrokerage($itemId, $playId, $ruleId); //佣金
            $inData["create_time"] = time();
            $inData["update_time"] = time();
            $arenaId = Db::name('arena')->insertGetId($inData);

            //$arenaId = $this->model->getData('id');
            foreach ($targetData as $tkey => $tval) {
                $targetData[$tkey]['arena_id'] = $arenaId;
                $targetData[$tkey]['rules_type'] = $ruleId;
            }
            Db::name('arena_target')->insertAll($targetData);
            //写入擂台押金流水
            Db::name("arena_deposit_detail")->insert([
                'arena_id' => $arenaId,
                'user_id' => $userId,
                'has_sys' => $has_sys,
                'number' => $deposit,
                'create_time' => time()
            ]);
            Db::name('odds')->where(['id' => $oddsId])->update(['odds_type' => 1]);
            Db::name("arena_odds")->insert([//擂台赔率变更列表
                'arena_id' => $arenaId,
                'mark' => md5(json_encode($newOdds)),
                'odds' => json_encode($newOdds),
                'create_time' => time(),
            ]);
            if (!$has_sys && !$has_robot) { //非系统用户或机器人
                //检查用户状态
                if ($user['status'] != STATUS_ENABLED) {
                    throw new Exception(10023);
                }
                //检查用户金币是否足够
                if ($user['gold'] < $deposit) {
                    throw new Exception(10014);
                }
                //Db::name("user")->where(['id' => $userId])->setDec("gold",$deposit);//扣除帐户金币
                Db::name("user")->where(['id' => $userId])->update([
                    'gold' => ['exp', "gold-$deposit"],
                    'arena_total' => ['exp', "arena_total+1"],
                ]); //扣除帐户金币
                Db::name("user_funds_log")->insert([
                    'user_id' => $userId,
                    'classify' => FUNDS_CLASSIFY_ARE,
                    'type' => FUNDS_TYPE_GOLD,
                    'number' => -$deposit,
                    'before_num' => $user['gold'],
                    'after_num' => $user['gold'] - $deposit,
                    'explain' => lang('90004', ['team' => $log, 'arenaId' => $arenaId]),
                    'data' => json_encode($inData),
                    'create_time' => time()
                ]);
            } else {
                Log::sysIncome(-$deposit, lang('90004', ['team' => $log, 'arenaId' => $arenaId]), [
                    'brok' => 0,
                    'has_robot' => $has_robot,
                    'arena_id' => $arenaId,
                    'number' => -$deposit,
                        ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_ARENA_START);
                @Log::sysFunds($this->admin_id, FUNDS_TYPE_GOLD, $deposit, lang('90004', ['team' => $log, 'arenaId' => $arenaId]), $inData);
            }
            //更新比赛
            $playData = [
                'arena_total' => ['exp', "arena_total+1"],
                'has_arena' => 1,
                'total_prize' => ['exp', "total_prize+$deposit"],
                'has_sys_arena' => $has_sys_arena
            ];
            if ($hasHide != STATUS_NO || $classify == ARENA_CLASSIFY_CREDIT) {
                unset($playData['has_arena']);
            }

            if ($has_sys_arena) { //如果是系统擂台
                $playData['sys_arena_total'] = ['exp', "sys_arena_total+1"];
            }

            Db::name('play')->where(['id' => $playId])->update($playData);
            if ($classify == ARENA_CLASSIFY_GOLD) {
                //将擂台同步给代理
                $agentUser = Db::name("agent_user")->where(['user_id' => $userId, 'arena_type' => AGENT_USER_ARENA_TYPE_ALL])->select();
                if ($agentUser) {
                    $inData = [];
                    foreach ($agentUser as $val) {
                        $inData[] = ['user_id' => $userId, 'agent_user_id' => $val['id'], 'arena_id' => $arenaId, 'arena_status' => ARENA_START, 'status' => STATUS_ENABLED];
                    }
                    Db::name("agent_arena")->insertAll($inData);
                }
            }
            //系统房间机器人投注
            if ($has_sys) {
                $this->arenaAndroid($play, $arenaId, $minBet);
            }
            @Log::arenaLog($arenaId, lang('90004', ['team' => $log, 'arenaId' => $arenaId]), $inData);
            Db::commit();
            $userSvr->lockUser($userId, false);
        } catch (Exception $e) {
            $this->error = $e->getMessage() . $e->getFile() . $e->getLine();
            $this->errorNo = $e->getCode();
            $userSvr->lockUser($userId, false);
            return false;
        }

        $this->miscSvr->setCacheTotal('arena'); //擂台
        if ($classify == ARENA_CLASSIFY_GOLD) {
            $this->miscSvr->setCacheTotal('turnover', $deposit); //流水
        }
        $userId && (new User())->cachePublishArena($userId, $playId, $arenaId); //缓存用户坐庄数据
        if ($userId) {
            $this->userSvr->setCacheUser($userId);
        }
        if ($arenaId) {
            $this->cacheArena($arenaId);
        }
        $playSvr->arenapublish($playId, $arenaId);
        if ($userId && $deposit > 0) {
            (new Socket())->userGold($userId, -$deposit);
        }

        return ['id' => $arenaId, 'mark' => $mark];
    }

    /**
     * 将ODDS表中的赔率解析成arena表里赔率格式
     * @param $odds
     * @return array
     */
    private function _parseOddsDataToArenaOdds($odds) {
        $result = [];
        foreach ($odds as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $result[$key . $k] = [
                        'target' => (string) $key,
                        'item' => $k,
                        'odds' => $v
                    ];
                }
            } else {
                $result[$key] = [
                    'target' => (string) $key,
                    'item' => '',
                    'odds' => $val
                ];
            }
        }
        return $result;
    }

    /**
     * 将提交的odds数据。转换成统一格式存储
     * {"home":"0.92","handicap":"-1","guest":"0.92"}
     * {"home":{"bd_1":"3","bd_2":"4","bd_3":"5"},"guest":{"bd_1":"6","bd_2":"7","bd_3":"8"}}
     * {"home":{"bd_1_0":"2","bd_2_0":"3","bd_2_1":"4","bd_3_0":"5","bd_3_1":"7","bd_3_2":"85","bd_4_0":"6","bd_4_1":"2","bd_4_2":"3","bd_4_3":"2"},"same":{"bd_0_0":"5","bd_1_1":"4","bd_2_2":"5","bd_3_3":"2","bd_4_4":"2"},"other":{"other":"4"},"guest":{"bd_1_0":"3","bd_2_0":"2","bd_2_1":"5","bd_3_0":"56","bd_3_1":"6","bd_3_2":"3","bd_4_0":"2","bd_4_1":"2","bd_4_2":"6","bd_4_3":"5"}}
     * @param $odds
     * @param $ruleId
     */

    /**
     * 修改擂台赔率
     * @param int $arenaId
     * @param $home
     * @param $guest
     * @param $same
     * @param $handicap
     * @return bool
     */
    public function modifyOdds($arenaId, $odds, $userId) {
        if (!$arenaId && !$this->pkId) {
            $this->error = 30001;
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if (!$arenaData) {
                throw new Exception(30001);
            }
            if ($arenaData['status'] == ARENA_PLAY) {
                throw new Exception(40134);
            }
            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40110);
            }
            if ($arenaData['status'] == ARENA_DIS) {
                throw new Exception(40111);
            }
            if ($arenaData['status'] == ARENA_DEL) {
                throw new Exception(40112);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_BEGIN) {
                throw new Exception(40113);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_END) {
                throw new Exception(40114);
            }
            if (!$this->admin_id && $arenaData['user_id'] != $userId) {
                throw new Exception(10000);
            }

            $handle = $this->factory($arenaData['game_type']);
            //赔率数据格式及数值检查
            /* if(!$ret = $handle->checkPublishOdds($odds,$arenaData['rules_type'])){
              throw new Exception(40107);
              } */

            $ret = $handle->checkPublishOdds($odds, $arenaData['rules_id']);
            if ($ret !== true && !is_array($ret)) {
                $this->error = 40151;
                $this->errorData = ['text' => $ret];
                return false;
            }

            if (is_array($ret)) {
                $odds = $ret;
            }

            $newOdds = $this->parseOdds($odds, $arenaData['rules_id']);
            $arenaOdds = @json_decode($arenaData['odds'], true);
            $data = [];
            $mark = md5(json_encode($newOdds));
            if ($odds && $mark != md5($arenaData['odds'])) {

                if (isset($newOdds['handicap']) && $arenaData['rules_type'] == RULES_TYPE_ASIAN && $arenaData['game_type'] != GAME_TYPE_FOOTBALL) { //非足球转换分数
                    $newOdds['handicap'] = under($newOdds['handicap'], false, false);
                }
                // if(isset($arenaOdds['handicap'])){
                //$newOdds['handicap'] = $arenaOdds['handicap']; //盘口不允许修改
                // }
                if (isset($arenaOdds['under'])) {
                    //$newOdds['under'] = $arenaOdds['under']; //大小界值不允许修改
                    //$newOdds['over'] = $arenaOdds['under']; //大小界值不允许修改
                }
                $data['odds'] = json_encode($newOdds);
                Db::name("arena_odds")->insert([
                    'arena_id' => $this->pkId,
                    'mark' => $mark,
                    'odds' => json_encode($newOdds),
                    'create_time' => time(),
                ]);
            }
            //更新赔率
            Db::name('arena')->where(["id" => $this->pkId])->update($data);
            //写入系统操作日志
            $logData = [
                'table' => 'arena',
                'id' => $arenaData['id'],
                'mark' => $arenaData['mark'],
                'before' => json_decode($arenaData['odds'], true),
                'after' => $data,
            ];
            if ($this->admin_id) {
                @Log::sysOpt($this->admin_id, lang(40115, ['arenaId' => $arenaId]), $logData);
                @Log::arenaLog($arenaId, lang(40115, ['arenaId' => $arenaId]), $logData, 2);
            } else {
                Log::UserLog($arenaData['user_id'], lang(40115, ['arenaId' => $arenaId]), $logData);
                @Log::arenaLog($arenaId, lang(40115, ['arenaId' => $arenaId]), $logData);
            }
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage(); //.$e->getFile().$e->getLine();
            Db::rollback();
            return false;
        }
        $this->resetRisk($arenaId);
        return true;
    }

    /**
     * 自己更新擂台赔率
     * @param int $arenaId
     * @param $home
     * @param $guest
     * @param $same
     * @param $handicap
     * @return bool
     */
    public function autoArenaOdds($arenaId, $odds) {
        if (!$arenaId && !$this->pkId) {
            $this->error = 30001;
            return false;
        }
        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        try {
            $arenaData = $this->findArena();
            if (!$arenaData) {
                throw new Exception(30001);
            }
            if ($arenaData['status'] == ARENA_PLAY) {
                throw new Exception(40134);
            }
            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40110);
            }
            if ($arenaData['status'] == ARENA_DIS) {
                throw new Exception(40111);
            }
            if ($arenaData['status'] == ARENA_DEL) {
                throw new Exception(40112);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_BEGIN) {
                throw new Exception(40113);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_END) {
                throw new Exception(40114);
            }
            if (!$this->admin_id) {
                throw new Exception(10000);
            }
            $this->_updateOddsDataToArenaOdds($arenaData, $odds);
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage(); //.$e->getFile().$e->getLine();
            Db::rollback();
            return false;
        }
        $this->resetRisk($arenaId);
        return true;
    }

    /**
     * 将ODDS表中ODDS更新到擂台中
     * @param $arenaData
     * @param $odds
     * @return bool
     * @throws Exception
     */
    private function _updateOddsDataToArenaOdds($arenaData, $odds) {
        $arenaId = $arenaData['id'];
        $itemId = $arenaData['game_type'];
        $handle = $this->factory($arenaData['game_type']);
        $rulesSvr = (new Rule())->factory($itemId);
        if ($itemId != GAME_TYPE_FOOTBALL) {
            $odds = $rulesSvr->parseOddsTableToOddsData($odds);
        }
        $odds = $this->_parseOddsDataToArenaOdds($odds);

        $ret = $handle->checkPublishOdds($odds, $arenaData['rules_id']);
        if ($ret !== true && !is_array($ret)) {
            throw new Exception(40151);
            $this->errorData = ['text' => $ret];
            return false;
        }

        if (is_array($ret)) {
            $odds = $ret;
        }

        $newOdds = $this->parseOdds($odds, $arenaData['rules_id']);
        $arenaOdds = @json_decode($arenaData['odds'], true);
        $data = [];
        $mark = md5(json_encode($newOdds));
        if ($odds && $mark != md5($arenaData['odds'])) {
            if (isset($arenaOdds['handicap'])) {
                //$newOdds['handicap'] = $arenaOdds['handicap']; //盘口不允许修改
            }
            if (isset($arenaOdds['under'])) {
                //$newOdds['under'] = $arenaOdds['under']; //大小界值不允许修改
                //$newOdds['over'] = $arenaOdds['under']; //大小界值不允许修改
            }
            $data['odds'] = json_encode($newOdds);
            Db::name("arena_odds")->insert([
                'arena_id' => $this->pkId,
                'mark' => $mark,
                'odds' => json_encode($newOdds),
                'create_time' => time(),
            ]);
        }
        //更新赔率
        Db::name('arena')->where(["id" => $this->pkId])->update($data);
        //写入系统操作日志
        $logData = [
            'table' => 'arena',
            'id' => $arenaData['id'],
            'mark' => $arenaData['mark'],
            'before' => json_decode($arenaData['odds'], true),
            'after' => $data,
        ];
        $message = "自动更新赔率，" . lang(40115, ['arenaId' => $arenaId]);
        @Log::sysOpt($this->admin_id, $message, $logData);
        @Log::arenaLog($arenaId, $message, $logData, 2);
    }

    private function parseOdds($odds, $ruleId) {
        $ret = [];
        if (in_array($ruleId, [RULES_TYPE_BODAN, RULES_TYPE_BODAN_COMB])) {
            foreach ($odds as $val) {
                $ret[$val['target']][$val['item']] = $val['odds'];
            }
        } else {
            foreach ($odds as $val) {
                if (in_array($val['target'], ['under', 'over'])) {
                    $val['odds'] = under($val['odds'], false, false);
                }
                $ret[$val['target']] = $val['odds'];
            }
        }
        return $ret;
    }

    /**
     * 追加擂台保证金
     * @param $arenaId
     * @param $deposit
     * @param $userId
     * @return bool
     */
    public function appendDeposit($arenaId, $deposit, $userId) {
        $arenaId = intval($arenaId);
        $deposit = intval($deposit);
        $userId = intval($userId);
        if ($deposit < 1) {
            $this->error = 40117;
            $this->errorData = ['number' => 1];
            return false;
        }
        if ((new User())->checkPlaySmallGame($userId)) { //玩游戏中不允许，防止金币计算错误
            $this->errorData = ['msg' => '，不允许追加保证金'];
            $this->error = 10036;
            return false;
        }
        Db::startTrans();
        $has_sys = 0; //是否是系统或机器人
        try {
            $arenaData = $this->findArena($arenaId);
            if (!$arenaData) {
                throw new Exception(10025);
            }
            if ($arenaData['status'] == ARENA_PLAY) {
                throw new Exception(40123);
            }
            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40118);
            }
            if ($arenaData['status'] == ARENA_DIS) {
                throw new Exception(40119);
            }
            if ($arenaData['status'] == ARENA_DEL) {
                throw new Exception(40120);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_BEGIN) {
                throw new Exception(40121);
            }
            if ($arenaData['status'] == ARENA_STATEMENT_END) {
                throw new Exception(40122);
            }

            if ($arenaData['classify'] == ARENA_CLASSIFY_CREDIT) {
                throw new Exception(40165);
            }

            //检查比赛状态
            if ($arenaData['play']['play_time'] <= time()) {
                throw new Exception(40123);
            }
            $targetList = Db::name("arena_target")->where(['arena_id' => $arenaId])->select();
            if (!$targetList) {
                throw new Exception(10026);
            }
            foreach ($targetList as $tk => $tv) {
                $tv['deposit'] += $deposit;
                $targetList[$tk] = $tv;
            }

            //更新擂台投注项列表
            Db::name("arena_target")->where(['arena_id' => $arenaId])->setInc("deposit", $deposit);
            //更新押金
            Db::name('arena')->where(["id" => $arenaId])->update([
                'deposit' => ['exp', "deposit+{$deposit}"],
                'bet_total' => $this->factory($arenaData['game_type'])->arenaTargetListToBetTotal($targetList, $arenaData['game_type'])
            ]);
            if ($this->admin_id && ($arenaData['has_robot'] || $userId == SYS_USER_ID || $arenaData['user_id'] == SYS_USER_ID)) { //如果是管理操作同时是机器人帐户
                $has_sys = 1;
                Log::sysIncome(-$deposit, lang('90005', ['arenaId' => $arenaId]), [
                    'brok' => 0,
                    'arena_id' => $arenaId,
                    'number' => -$deposit,
                        ], FUNDS_TYPE_GOLD, SYSTEM_INCOME_DEPOSIT);
                @Log::sysFunds($this->admin_id, FUNDS_TYPE_GOLD, $deposit, lang('90005', ['arenaId' => $arenaId]), $arenaData);
                @Log::arenaLog($arenaId, lang('90005', ['arenaId' => $arenaId]), $arenaData, 2, $deposit);
            } else {
                if (!$this->admin_id && $arenaData['user_id'] != $userId) { //如果非管理员并且当前擂主与当前用户不匹配时，无法追加保证金
                    throw new Exception(10000);
                }
                $user = Db::name("user")->where(["id" => $arenaData['user_id']])->lock(true)->cache(false)->find();
                if (!$user) {
                    throw new Exception(10027);
                }
                if ($user['status'] != STATUS_ENABLED) {
                    throw new Exception(10023);
                }
                if ($user['gold'] < $deposit) {
                    $this->errorData = ['number' => $deposit];
                    throw new Exception(40124, NOT_GOLD);
                }
                //扣除用户金币
                Db::name("user")->where(["id" => $arenaData['user_id']])->setDec("gold", $deposit);
                $data = ['table' => 'arena', 'id' => $arenaData['id'], 'deposit' => $deposit];
                @Log::UserFunds($arenaData['user_id']
                                , FUNDS_CLASSIFY_ADD_ARE
                                , FUNDS_TYPE_GOLD
                                , -$deposit
                                , $user['gold']
                                , $user['gold'] - $deposit
                                , lang('90005', ['arenaId' => $arenaId])
                                , $data
                );
                Log::UserLog($arenaData['user_id'], lang('90005', ['arenaId' => $arenaId]), $data);
                @Log::arenaLog($arenaId, lang('90005', ['arenaId' => $arenaId]), $data, 1, $deposit);
            }
            Db::name("play")->where(['id' => $arenaData['play_id']])->update([
                'total_prize' => ['exp', "total_prize+{$deposit}"],
            ]);

            //写入擂台押金流水
            Db::name("arena_deposit_detail")->insert([
                'arena_id' => $arenaData['id'],
                'user_id' => $has_sys ? SYS_USER_ID : $arenaData['user_id'],
                'has_sys' => $has_sys,
                'number' => $deposit,
                'create_time' => time()
            ]);
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->errorNo = $e->getCode();
            Db::rollback();
            return false;
        }
        $this->miscSvr->setCacheTotal('turnover', intval($deposit)); //流水
        $this->resetRisk($arenaId);
        if (!$has_sys && $arenaData && isset($arenaData['user_id'])) {
            (new Socket())->userGold($arenaData['user_id'], -$deposit);
            //(new Socket())->sendToUid($arenaData['user_id'],['type' => 'gold','gold' => -$deposit],"socket.to_send_gold_change");
        }
        return true;
    }

    //擂台属性修改
    public function Conf($arenaId, $data, $userId) {
        if (!$arenaId && !$this->pkId) {
            $this->error = 10025;
            return false;
        }
        $has_hide = isset($data['has_hide']) ? $data['has_hide'] : null;
        $private = isset($data['private']) ? $data['private'] : null;
        $minBet = isset($data["min_bet"]) ? $data["min_bet"] : null;
        $maxBet = isset($data["max_bet"]) ? $data["max_bet"] : null;
        $odds_id = isset($data["odds_id"]) ? $data["odds_id"] : 0;
        $auto_update_odds = isset($data["auto_update_odds"]) ? $data["auto_update_odds"] : 0;

        //玩家开的庄的单笔最低投注
        $sys_user_min_bet_money = intval(config("system.sys_user_min_bet_money"));
        //判断隐私
        if (!is_null($private) && !in_array($private, [ARENA_DISPLAY_ALL, ARENA_DISPLAY_FRIENDS, ARENA_DISPLAY_CODE])) {
            $private = ARENA_DISPLAY_ALL;
        }

        if (!is_null($minBet) && $minBet < 0 || !is_null($maxBet) && $maxBet < 0) {
            $this->error = 40150;
            return false;
        }

        if ($private != ARENA_DISPLAY_CODE) {
            $has_hide = 0;
        }

        if ($minBet && $maxBet && $minBet > $maxBet) {
            $this->error = 40153;
            return false;
        }

        /* if($minBet > $maxBet){ //如果最小投注金额大于最大投注金额，则双方交换
          $t = $maxBet;
          $maxBet = $minBet;
          $minBet = $t;
          } */

        $this->pkId = $arenaId ? $arenaId : $this->pkId;
        Db::startTrans();
        $isUpdateOdds = false;
        try {
            $arenaData = $this->findArena();
            if (!$arenaData) {
                $this->error = 10025;
            }
            if ($arenaData['classify'] !== ARENA_CLASSIFY_CREDIT && !$arenaData['has_sys'] && $sys_user_min_bet_money && $minBet < $sys_user_min_bet_money) {
                $this->errorData['text'] = "最低投注不能少于{$sys_user_min_bet_money}元";
                //修改房间失败，最低投注不能少于10元
                throw new Exception(40140);
            }

            if ($arenaData['status'] == ARENA_END) {
                throw new Exception(40126);
            }
            /* if($arenaData['classify'] == ARENA_CLASSIFY_CREDIT){
              throw new Exception(40166);
              } */
            if (in_array($arenaData['status'], [ARENA_DEL, ARENA_STATEMENT_BEGIN, ARENA_STATEMENT_END])) {
                throw new Exception(10025);
            }
            //检查比赛状态
            if ($arenaData['play']['play_time'] <= time()) {
                throw new Exception(40132);
            }
            if (!$this->admin_id && $arenaData['user_id'] != $userId) { //非管理员非擂主
                throw new Exception(10025);
            }
            $data = [];
            if ($arenaData['classify'] != ARENA_CLASSIFY_CREDIT) { //非征信局可修改下面属性
                if (!is_null($private)) {
                    $data['private'] = $private;
                }
                if (!is_null($minBet)) {
                    $data['min_bet'] = $minBet;
                }
                if (!is_null($maxBet)) {
                    $data['max_bet'] = $maxBet;
                }
                if (!is_null($has_hide)) {
                    $data['has_hide'] = $has_hide;
                }
            }
            //在创建的时候选择跟随的赔率公司，不然后期盘口会发生变化，影响结算结果
            if (!$odds_id || $arenaData['odds_id'] != $odds_id) {
                $odds_id = $arenaData['odds_id'];
            }

            if ($odds_id && $auto_update_odds) { //自动更新，并同步新赔率公司赔率
                $oddsData = Db::name('odds')->where(['play_id' => $arenaData['play_id'], 'rules_type' => $arenaData['rules_type'], 'id' => $odds_id])->find();
                if ($oddsData) {
                    $odds = @json_decode($oddsData['odds'], true);
                    $this->_updateOddsDataToArenaOdds($arenaData, $odds['time']);
                    $data['odds_id'] = $odds_id;
                    $data['company_id'] = $oddsData['odds_company_id'];
                    $isUpdateOdds = true;
                }
            }

            if ($arenaData['odds_id']) { //关联赔率ID才能自动更新
                $data['auto_update_odds'] = $auto_update_odds;
            } else {
                $data['auto_update_odds'] = 0;
            }
            if (!$data) {
                throw new Exception(10004);
            }
            Db::name('arena')->where(["id" => $this->pkId])->update($data);
            $user_id = $arenaData['user_id'];
            if ($this->admin_id) {
                @Log::sysOpt($this->admin_id, lang(90018, ['arenaId' => $arenaData['id']]), $data);
                @Log::arenaLog($arenaData['id'], lang(90018, ['arenaId' => $arenaData['id']]), $data, 2);
            } else {
                Log::UserLog($arenaData['user_id'], lang(90018, ['arenaId' => $arenaData['id']]), $data);
                @Log::arenaLog($arenaData['id'], lang(90018, ['arenaId' => $arenaData['id']]), $data, 1);
            }
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage(); //.$e->getFile().$e->getLine();
            Db::rollback();
            return false;
        }
        if ($isUpdateOdds) {
            $this->resetRisk($arenaId);
        } else {
            $this->cacheArena($arenaId);
        }
        //更新比赛
        $total = Db::name('arena')->where(['play_id' => $arenaData['play_id'], 'has_hide' => STATUS_NO, 'status' => ARENA_START])->count();
        if (!$total) {
            Db::name('play')->where(['id' => $arenaData['play_id']])->update(['has_arena' => 0]);
        } else {
            Db::name('play')->where(['id' => $arenaData['play_id']])->update(['has_arena' => 1]);
        }
        (new Play())->cacheTeams($arenaData['play_id']);
        return true;
    }

    public function getArenaShareUrl($arenaId) {
        return config("share_domain") . "share/arena/{$arenaId}.html";
    }

    /**
     * 擂台唯一地址
     * @param $arenaId
     * @param string $platform
     */
    public function getArenaUrl($arenaId, $token, $platform = 'h5') {
        $arenaUrl = config('site_source_domain') . "index.html?action=arena_info&arena_id={$arenaId}&arena_mark=40002&opt=qrcode";
        return $arenaUrl;
    }

    /**
     * 擂台征信用户唯一地址
     */
    public function getArenaUrlByCredit($arenaId, $token, $creditMark) {
        $arenaUrl = $this->getArenaUrl($arenaId, $token) . "&credit_mark={$creditMark}";
        return $arenaUrl;
    }

    /**
     * 返回擂台二维码地址
     * @param $arenaId
     */
    public function getQrCode($arenaId, $token, $platform = 'h5') {
        $domain = config('site_source_domain');
        $arena = $this->getCacheArenaById($arenaId);
        $dir = rtrim(config("assets_path"), "/") . "/attach/";
        $font = rtrim(config("assets_path"), "/") . "/fonts/msyh.ttf";
        $imgDir = "qrcode/";
        $mark = md5($domain . 'arena' . $arena['mark']);
        for ($i = 0; $i < 3; $i++) {
            $imgDir .= substr($mark, $i * 2, 2) . '/';
        }
        $qrFile = md5($domain . 'arena' . $mark) . ".png";
        if (!is_file($dir . $imgDir . $qrFile)) {
            if (!is_dir($dir . $imgDir)) {
                mkdir($dir . $imgDir, 0777, true);
            }
            $arenaUrl = $this->getArenaUrl($arenaId, $token, $platform);
            $qrCode = new QrCode();
            $qrCode->setText($arenaUrl)
                    ->setSize(235)
                    ->setPadding(10)
                    ->setErrorCorrection('high')
                    ->setImageType(QrCode::IMAGE_TYPE_PNG)
                    ->setLabel("扫一扫进入房间{$arenaId}")
                    ->setLabelFontPath($font)
                    ->setLabelFontSize(14)
                    ->save($dir . $imgDir . $qrFile);
        }
        return $imgDir . $qrFile;
    }

    /**
     * 返回征信擂台二维码地址
     * @param $arenaId
     */
    public function getQrCodeByCredit($arenaId, $token, $creditMark) {
        $domain = config('site_source_domain');
        $arena = $this->getCacheArenaById($arenaId);
        $dir = rtrim(config("assets_path"), "/") . "/attach/";
        $font = rtrim(config("assets_path"), "/") . "/fonts/msyh.ttf";
        $imgDir = "qrcode/";
        $mark = $arena['mark'];
        for ($i = 0; $i < 3; $i++) {
            $imgDir .= substr($mark, $i * 2, 2) . '/';
        }
        $qrFile = md5("{$creditMark}_{$domain}") . ".png";
        //if(!is_file($dir.$imgDir.$qrFile)){
        if (!is_dir($dir . $imgDir)) {
            mkdir($dir . $imgDir, 0777, true);
        }
        $arenaUrl = $this->getArenaUrlByCredit($arenaId, $token, $creditMark);
        $qrCode = new QrCode();
        $qrCode->setText($arenaUrl)
                ->setSize(235)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setImageType(QrCode::IMAGE_TYPE_PNG)
                ->setLabel("扫一扫进入房间{$arenaId}")
                ->setLabelFontPath($font)
                ->setLabelFontSize(14)
                ->save($dir . $imgDir . $qrFile);
        //}
        return $imgDir . $qrFile;
    }

    /**
     * 添加授信用户
     * @param $arenaId
     * @param $userId
     * @param $data
     * @return array|bool
     */
    public function addAuthUser($arenaId, $userId, $data) {
        $arenaId = intval($arenaId);
        $arena = $this->getCacheArenaById($arenaId);
        if (!$arena) {
            $this->error = 10025;
            return false;
        }
        if ($arena['classify'] != ARENA_CLASSIFY_CREDIT) {
            $this->error = 40160;
            return false;
        }
        if ($arena['user_id'] != $userId) {
            $this->error = 10005;
            return false;
        }
        $gold = intval($data['gold']);
        $phone = $data['phone'];
        $name = $data['name'];
        if (!$gold || $gold < 1) {
            $this->error = 40161;
            return false;
        }
        if (!$phone || !$name) {
            $this->error = 40162;
            return false;
        }

        Db::startTrans();
        try {
            $user = Db::name('user')->where(['id' => $arena['user_id']])->find();
            if ($user['gold'] < $data['gold']) {
                throw new Exception(10014);
            }

            $mark = uniqidReal('', 15);
            $code = Stringnew::randNumber(1000, 9999);

            Db::name('arena_credit')->insert([
                'mark' => $mark,
                'arena_id' => $arenaId,
                'user_id' => 0,
                'name' => $name,
                'mobile' => $phone,
                'gold' => $gold,
                'avail_gold' => $gold,
                'code' => $code,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            Db::name('user')->where(['id' => $arena['user_id']])->setDec('gold', $gold);
            Log::UserFunds(
                    $userId, FUNDS_CLASSIFY_CREDIT, FUNDS_TYPE_GOLD, -$gold, $user['gold'], $user['gold'] - $gold, lang('99031', ['money' => $gold]), []
            );
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        (new User())->setCacheUser($arena['user_id']);
        (new Socket())->userGold($arena['user_id'], -$gold);
        return ['mark' => $mark, 'code' => $code];
    }

    /**
     * 撤销授信用户
     * @param $arenaId
     * @param $userId
     */
    public function cancelAuthUser($arenaId, $userId, $arena_credit_id) {
        $arenaId = intval($arenaId);
        $arena = $this->getCacheArenaById($arenaId);
        if (!$arena) {
            $this->error = 10025;
            return false;
        }
        if ($arena['classify'] != ARENA_CLASSIFY_CREDIT) {
            $this->error = 40160;
            return false;
        }
        if ($arena['user_id'] != $userId && !$this->admin_id) {
            $this->error = 10005;
            return false;
        }

        Db::startTrans();
        $gold = 0;
        try {
            $arenaCredit = Db::name('arena_credit')->where(['id' => $arena_credit_id, 'arena_id' => $arenaId])->find();
            if (!$arenaCredit) {
                $this->error = 10005;
                return false;
            }
            $gold = $arenaCredit['avail_gold']; //退回
            $laveGold = max(0, $arenaCredit['gold'] - $gold); //帐户已使用

            if ($gold > $arenaCredit['gold']) { //错误或非法金币帐户
                $gold = 0;
                $laveGold = 0;
            }
            $user = Db::name('user')->where(['id' => $arena['user_id']])->find();
            Db::name('arena_credit')->where(['id' => $arena_credit_id, 'arena_id' => $arenaId])->update([
                'avail_gold' => 0,
                'gold' => $laveGold
            ]);
            Db::name('user')->where(['id' => $arena['user_id']])->setInc('gold', $gold);
            Log::UserFunds(
                    $userId, FUNDS_CLASSIFY_CREDIT, FUNDS_TYPE_GOLD, $gold, $user['gold'], $user['gold'] + $gold, lang('99033', ['money' => $gold]), []
            );
            Db::commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
        (new User())->setCacheUser($arena['user_id']);
        (new Socket())->userGold($arena['user_id'], $gold);
        return true;
    }

    /**
     * 生成投注机器人运行条件
     */
    public function arenaAndroid($play, $arenaId, $minBet) {
        $system = config('system');
        $itemId = $play['game_type'];
        $gameId = $play['game_id'];

        if (!isset($system['arena_android_on']) ||
                !isset($system['arena_android_limit'])
        ) {
            return false;
        }

        $arena_android_on = @json_decode($system['arena_android_on'], true);
        $arena_android_limit = @json_decode($system['arena_android_limit'], true);

        if (!in_array($itemId, $arena_android_on)) {
            return false;
        } //检查是否开启
        if (!isset($arena_android_limit[$itemId])) {
            return false;
        }

        $hasHot = isset($play['hot']) ? $play['hot'] : PLAY_HOT_LM;
        $arena_android_limit = $arena_android_limit[$itemId];
        if ($gameId) {
            if (!isset($arena_android_limit[$gameId])) {
                return false;
            }
            $arena_android_limit = $arena_android_limit[$gameId];
        }
        if (!isset($arena_android_limit[$hasHot])) {
            return false;
        }
        $arena_android_limit = $arena_android_limit[$hasHot];




        $min = isset($arena_android_limit['min']) ? $arena_android_limit['min'] : 0;
        $max = isset($arena_android_limit['max']) ? $arena_android_limit['max'] : 0;
        $betTotal = rand($min, $max);
        $condition = $this->betAndroid($itemId, $minBet, $play['play_time'], $betTotal, $gameId);
        if (!$condition) {
            return false;
        }
        $next_time = $condition['next_time'];
        Db::name('arena_android')->insert([
            'arena_id' => $arenaId,
            'next_time' => $next_time,
            'create_time' => time(),
            'update_time' => time(),
            'condition' => @json_encode($condition)
        ]);
    }

    public function betAndroid($itemId, $minBet, $playTime, $randBet, $gameId = 0) {
        $system = config('system');
        //$itemId = $play['game_type'];

        if (!isset($system['arena_android_on']) ||
                !isset($system['arena_android_limit']) ||
                !isset($system['arena_android_gt_rand']) ||
                !isset($system['arena_android_lt_rand']) ||
                !isset($system['arena_android_bfb_rand'])
        ) {
            return false;
        }


        $arena_android_on = @json_decode($system['arena_android_on'], true);
        $arena_android_limit = @json_decode($system['arena_android_limit'], true);
        $arena_android_gt_rand = @json_decode($system['arena_android_gt_rand'], true);
        $arena_android_lt_rand = @json_decode($system['arena_android_lt_rand'], true);
        $arena_android_bfb_rand = @json_decode($system['arena_android_bfb_rand'], true);

        if (!in_array($itemId, $arena_android_on)) {
            return false;
        } //检查是否开启

        if (!isset($arena_android_limit[$itemId]) || !isset($arena_android_gt_rand[$itemId]) || !isset($arena_android_lt_rand[$itemId]) || !isset($arena_android_bfb_rand[$itemId])) {
            return false;
        }

        $arena_android_limit = $arena_android_limit[$itemId];
        $arena_android_gt_rand = $arena_android_gt_rand[$itemId];
        $arena_android_lt_rand = $arena_android_lt_rand[$itemId];
        $arena_android_bfb_rand = $arena_android_bfb_rand[$itemId];

        if ($gameId) {
            if (!isset($arena_android_limit[$gameId]) || !isset($arena_android_gt_rand[$gameId]) || !isset($arena_android_lt_rand[$gameId]) || !isset($arena_android_bfb_rand[$gameId])) {
                return false;
            }
            $arena_android_limit = $arena_android_limit[$gameId];
            $arena_android_gt_rand = $arena_android_gt_rand[$gameId];
            $arena_android_lt_rand = $arena_android_lt_rand[$gameId];
            $arena_android_bfb_rand = $arena_android_bfb_rand[$gameId];
        }

        $hasHot = isset($play['hot']) ? $play['hot'] : PLAY_HOT_LM;
        if (!isset($arena_android_limit[$hasHot]) || !isset($arena_android_gt_rand[$hasHot]) || !isset($arena_android_lt_rand[$hasHot]) || !isset($arena_android_bfb_rand[$hasHot])) {
            return false;
        }
        $arena_android_limit = $arena_android_limit[$hasHot];
        $arena_android_gt_rand = $arena_android_gt_rand[$hasHot];
        $arena_android_lt_rand = $arena_android_lt_rand[$hasHot];
        $arena_android_bfb_rand = $arena_android_bfb_rand[$hasHot];

        //$min = isset($arena_android_limit['min']) ? $arena_android_limit['min'] : 0;
        //$max = isset($arena_android_limit['max']) ? $arena_android_limit['max'] : 0;
        //if(!$min || !$max){return false;}
        //计算剩余开赛时间的秒数
        //$play['play_time']  = time() + 300;
        if (!isset($arena_android_bfb_rand['min']) || !isset($arena_android_bfb_rand['max'])) {
            return false;
        }
        if (!$arena_android_bfb_rand['min'] || !$arena_android_bfb_rand['max']) {
            return false;
        }
        $bfb = rand($arena_android_bfb_rand['min'], $arena_android_bfb_rand['max']) / 100;

        $secondTotal = $playTime - time();
        if ($secondTotal < 60) {
            return false;
        }
        //检查剩余时间是否足够
        //$minBet = 50;
        $eachSecond = 60;
        /**
          如果随机值a/（总秒数/30）>=该比赛最低下注限制，则随机1-10个30秒进行下注(该随机值为b)，每次押注值为随机值/（总秒数/30）*随机值b*随机值c，随机值c为0.9-1.1的随机值
          如果随机值a/（总秒数/30）<该比赛最低下注限制,则计算出z值（z=比赛最低下注限制/a/（总秒数/30））,则随机1-5个z值进行下注（该随机值为b），每次押注值为随机值/（总秒数/30）*随机值b*随机值c，随机值c为0.9-1.1的随机值
          每次机器人的押注值必须为比赛最低下注限制的整数倍
         */
        //取随机数
        //$randBet = rand($min,$max);
        $second = $secondTotal / $eachSecond;
        $bet = $randBet / $second;
        $time = time();
        if ($bet > $minBet) {
            if (!isset($arena_android_gt_rand['min']) || !isset($arena_android_gt_rand['max'])) {
                return false;
            }
            //如果$second,$arena_android_gt_rand['max'] > $second 则取两者之小
            $next_interval = rand($arena_android_gt_rand['min'], min($second, $arena_android_gt_rand['max']));
            $nextBet = $bet * $next_interval * $bfb;
        } else {
            $z = $minBet / ($randBet / $second);
            if (!isset($arena_android_lt_rand['min']) || !isset($arena_android_lt_rand['max'])) {
                return false;
            }
            if (!$arena_android_lt_rand['min'] || !$arena_android_lt_rand['max']) {
                echo '7';
                return false;
            }
            $next_interval = rand($arena_android_lt_rand['min'], $arena_android_lt_rand['max']);
            $nextBet = $bet * ($next_interval * $z) * $bfb;
            //如果z 大于 $second 停止机器人
            if ($z > $second) {
                return false;
            }
        }

        //判断投注金额,如果有最低投注金限制时,押注值必须为比赛最低下注限制的整数倍
        if ($minBet) {
            $diff = (int) ($nextBet / $minBet);
            $nextBet = $diff * $minBet;
        }
        //获取下次运行时间
        $nextTime = time() + ($next_interval * $eachSecond);

        if ($nextTime - time() < 60) { //防止运行时间过短，不执行问题
            $nextTime += 60;
        }

        $result = [
            'max_bet' => $randBet //最大总投注金
            , 'next_interval' => $next_interval //下次运行时间间隔几次 随机值为b
            , 'next_bet' => $nextBet
            , 'second' => $second
            , 'next_time' => $nextTime
            , 'bfb' => $bfb
            , 'time' => $time
        ];
        return $result;
    }

    public function getError() {
        return $this->error;
    }

    public function getErrorData() {
        return $this->errorData;
    }

}
