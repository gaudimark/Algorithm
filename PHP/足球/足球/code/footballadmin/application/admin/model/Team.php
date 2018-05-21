<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;
use think\Db;

class Team extends BasicModel{
    protected $name = 'team';
    
    public function getTeamListByWhere($where , $limit=10 , $order="t.id desc" , $query=array() ){
        $this->alias("t");
        $this->join("__COUNTRY__ c","t.country_id=c.id","left");
        $this->field("t.*,c.name as country_name");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);

    }
    
    //根据球队ID查询信息
    public function getTeamInfoByID($id){
        $this->alias("t");
        $this->join("country c","t.country_id=c.id","left");
        $this->field("t.*,c.name as country_name,c.first as country_first");
        return $this->where("t.id",$id)->find();
    }
    
    //保存数据
    public function updateTeam($data,$id){
        return $this->where("id",$id)->update($data);
    }

    /**
     * 将球队信息写入缓存
     */
    public function upCache(){
        
        $start = 1;
        $end = 100;
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $page = 0;
        $limit=100;
        while(true){
            $offset = $page * $limit;
            $res = $this->order("id desc")->limit($offset,$limit)->select();
            $page++;
            if($res){
                foreach($res as $val){
                    $val = $val->toArray();
                    //$val['logo'] = $this->getLogo($val['game_type']);
                    if(!$val['logo']){
                        $val['logo'] = $this->getLogo($val['game_type']);
                    }
                    $val['rank'] = Db::name("team_rank")->where(['team_id' => $val['id']])->select();
                   
                    Cache("team_{$val['id']}",$val);
                }
                unset($res);
            }else{
                break;
                return true;
            }
        }
        return true;
    }
    
    public function upCacheOnly($id){
        $res = $this->where(['id' => $id])->find();
        if($res){
            $val = $res->toArray();
            $val['rank'] = Db::name("team_rank")->where(['team_id' => $val['id']])->select();
            Cache("team_{$val['id']}",$val);
        }
    }

    public function getLogo($itemId){
        $logo = 'common/images/';
        switch ($itemId){
            case GAME_TYPE_FOOTBALL:
                $logo .= "zuqiu.png";
                break;
        }
        return $logo;
    }

}