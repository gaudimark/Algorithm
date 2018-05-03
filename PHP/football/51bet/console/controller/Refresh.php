<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/10
 * Time: 23:55
 */
namespace app\console\controller;
use app\console\logic\Basic;
use think\Db;
use think\Exception;

class Refresh extends Basic{

    public function all(){
        $list = config('refresh');
        if($list){
            foreach ($list as $key => $val){
                try{
                    $this->$key();
                    sleep(1);
                }catch (Exception $e){
                    $this->console("方法:{$key},出错,{$e->getMessage()}");
                }
            }
        }
    }

    /**
     * 更新系统配置
     */
    public function system(){
        $this->console('系统缓存更新-开始');
        $config = modelN('config')->column('value','var');
        $cache = [];
        $cache_type = $config['cache_type'];
        switch ($cache_type){
            case 'file' :
                $cache = [
                    'type' => 'file',
                    'path' => (!$config['cache_file_path'] || $config['cache_file_path'] == '/') ? CACHE_PATH : $config['cache_file_path'],
                ];
                break;
            case 'redis' :
                $cache = [
                    'type' => 'redis',
                    'host'       => $config['cache_redis_server'],
                    'port'       => $config['cache_redis_port'],
                    'password'   => $config['cache_redis_password'],
                    'timeout'    => $config['cache_redis_timeout'],
                    'expire'     => intval($config['cache_redis_expired']),
                    'persistent' => false,
                    'prefix'     => $config['cache_redis_prefix'],
                ];
                break;
            case 'memcached':
            case 'memcache':
                $cache = [
                    'type' => 'memcached',
                    'host'       => $config['cache_memcached_server'],
                    'port'       => $config['cache_memcached_port'],
                    'expire'     => $config['cache_memcached_expired'],
                    'timeout'    => $config['cache_memcached_timeout'], // 超时时间（单位：毫秒）
                    'persistent' => true,
                    'prefix'     => $config['cache_memcached_prefix'],
                ];
                break;
            case 'apc':
                $cache = [
                    'type' => 'apc',
                    'expire' => $config['cache_apc_expired'],
                    'prefix' => $config['cache_apc_prefix'],
                ];
                break;
            case 'xcache':
                $cache = [
                    'type' => 'xcache',
                    'expire' => $config['cache_xcache_expired'],
                    'prefix' => $config['cache_xcache_prefix'],
                ];
                break;
        }
        cache('system',$config);
        $this->console('系统缓存更新-成功');
    }

    /**
     * 房间玩法
     */
    public function rules(){
        $this->console('房间玩法-开始');
        $list = modelN('rules')->field("id,game_id,game_type,type,name,alias,min_deposit,explain,intro,help_intro,status,is_delete")->order("sort asc,id asc")->select();
        $data = [];
        foreach($list as $key =>$val){
            $_temp = $val->toArray();
            if(!$_temp['alias']){
                $_temp['alias'] = $_temp['name'];
            }
            $_temp['help_intro'] = $_temp['help_intro'] ? $_temp['help_intro'] : '';
            $_temp['intro'] = $_temp['intro'] ? $_temp['intro'] : '';
            //$_temp['rulesItem'] = modelToArray($val->item()->order("id asc")->select());
            $data[$_temp['game_type']][$_temp['id']] = $_temp;
        }
        if($data){
            foreach($data as $key => $val){
                cache("rules_{$key}",$data);
            }
        }
        $this->console('房间玩法更新-成功');
    }

    /**
     * 赔率公司
     */
    public function odds_company(){
        $this->console('赔率公司-开始');
        $list = modelN('odds_company')->order("id asc")->select();
        $data = [];
        if($list){
            foreach($list as $val){
                $data[$val->id] = $val->toArray();
            }
        }
        cache('odds_company',$data);
        $this->console('赔率公司更新-成功');
    }

    /**
     * 球队/队伍
     */
    public function team(){
        $this->console('球队/队伍-开始');
        $page = 0;
        $limit = 50;
        while(true){
            $offset = $page * $limit;
            $res = modelN('team')->order("id desc")->limit($offset,$limit)->select();
            $page++;
            if($res){
                foreach($res as $val){
                    $val = $val->toArray();
                    //$val['logo'] = $this->getLogo($val['game_type']);
                    if(!$val['logo']){
                        $val['logo'] = $this->getTempLogo($val['game_type']);
                    }
                    $val['rank'] = Db::name("team_rank")->where(['team_id' => $val['id']])->select();
                    Cache("team_{$val['id']}",$val);
                    $this->console($val['id']);
                }
                unset($res);
            }else{
                break;
            }
        }
        $this->console('球队/队伍更新-成功');
    }

    private function getTempLogo($itemId){
        $logo = 'common/images/';
        switch ($itemId){
            case GAME_TYPE_FOOTBALL:
                $logo .= "zuqiu.png";
                break;
            case GAME_TYPE_WCG:
                $logo .= "dianjin.png";
                break;
            case GAME_TYPE_BASKETBALL:
                $logo .= "lanqiu.png";
                break;
            case GAME_TYPE_PUCK:
                $logo .= "bingqiu.png";
                break;
            default:
                $logo .= "default.png";
                break;
        }
        return $logo;
    }

    /**
     * 赛事
     */
    public function match(){
        $this->console('赛事-开始');
        $limit = 100;
        $page = 0;
        $hotIds = [];
        while(true){
            $offset = $page * $limit;
            $res = modelN('match')->limit($offset,$limit)->select();
            if($res){
                foreach($res as $val){
                    $val = $val->toArray();
                    $data[$val['id']] = $val;
                    if($val['is_hot'] == 1){ //热门赛事
                        $hotIds[] = $val['id'];
                    }
                    Cache("match_{$val['id']}",$val);
                }
            }else{
                break;
            }
            $page++;
        }
        Cache("match_hot",$hotIds);
        $this->console('赛事更新-成功');
    }

    /**
     * 会员/用户
     */
    public function user(){
        $this->console('会员/用户-开始');
        (new \library\service\User())->setCacheAll();
        $this->console('会员/用户更新-成功');
    }

    /**
     * 会员等级配置
     */
    public function level(){
        $this->console('会员等级配置-开始');
        (new \library\service\User())->cacheLevel();
        $this->console('会员等级配置更新-成功');
    }
    /**
     * 代理用户
     */
    public function agent(){
        $this->console('代理用户-开始');
        $limit = 100;
        $page = 0;
        while(true){
            $offset = $page * $limit;
            $res = Db::name("agent_user")->limit($offset,$limit)->field("*")->select();
            if($res){
                foreach($res as $val){
                    unset($val['password']);
                    unset($val['salt']);
                    Cache("agent_{$val['id']}",$val);
                    cache("agent_{$val['mark']}",$val['id']);
                }
            }else{
                break;
            }
            $page++;
        }
        $this->console('代理用户更新-成功');
    }


    /**
     * 首页模块
     */
    public function layout(){
        $this->console('首页模块-开始');
        (new \library\service\Layout())->setCacheAll();
        $this->console('首页模块更新-成功');
    }



}