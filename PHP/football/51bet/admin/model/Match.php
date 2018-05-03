<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Model;

class Match extends BasicModel{
    protected $name = "match";

    public function upCache(){
        $limit = 100;
        $page = 0;
        $hotIds = [];
        while(true){
            $offset = $page * $limit;
            $res = $this->limit($offset,$limit)->select();
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
                return true;
            }
            $page++;
        }
        Cache("match_hot",$hotIds);
        return true;
    }
    public function upCacheOnly($id){
        $res = $this->where(['id' => $id])->find();
        if($res){
            $val = $res->toArray();
            $data[$val['id']] = $val;
            Cache("match_{$val['id']}",$val);
        }
        return true;
    }

    public function getMatchListByWhere($where , $limit=10 , $order="m.id desc" , $query=[]){
        $this->alias("m");
        $this->join("__COUNTRY__ c","m.country_id=c.id","LEFT");
        $this->field("m.*,c.name as country_name,c.first as first");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
    }

    public function getAllMatchListByWhere($where,$order="m.id desc",$groupby = ''){
        $this->alias("m");
        $this->join("__PLAY__ p","m.id=p.match_id","LEFT");
        $this->field("m.*,sum(p.arena_total) as arena_num");
        $this->order($order);
        if($groupby){
            $this->group($groupby);
        }
        return $this->where($where)->select();
    }

    public function getMatchInfoByID($id){
        $this->alias("m");
        $this->join("__COUNTRY__ c","m.country_id=c.id","LEFT");
        $this->field("m.*,c.name as country_name,c.first as first");

        return $this->where("m.id",$id)->find();
    }

    public function updateMatch($data,$id){
        return $this->where("id",$id)->update($data);
    }

    //查询赛事推荐列表
    public function getMatchRecommendListByWhere($where , $limit=10 , $order="mr.id" , $query=[]){
        $this->name("match_recommend")->alias("mr");
        $this->join("__MATCH__ m" ,"mr.match_id=m.id","LEFT");
        $this->join("__COUNTRY__ c","m.country_id=c.id","LEFT");
        $this->field("mr.id as id,m.id as match_id,m.name as name,m.logo as logo,c.name as country_name,c.first as first");
        $this->order($order);
        return $this->where($where)->paginate($limit,false,[
            'query' => $query
        ]);
    }

    public function getMatchRecommendList($where){
        $this->name("match_recommend")->alias("mr");
        $this->join("__MATCH__ m" ,"mr.match_id=m.id","LEFT");
        $this->join("__COUNTRY__ c","m.country_id=c.id","LEFT");
        $this->field("mr.id as id,m.id as match_id,m.name as name,m.logo as logo,c.name as country_name,c.first as first");
        $this->order("mr.id");
        return $this->where($where)->select();
    }



    public function deleteMatchRecommend($id){
        return $this->name("match_recommend")->where("id",$id)->delete();
    }

    public function insertMatchRecommend($data){
        return $this->name("match_recommend")->insertAll($data);
    }
}