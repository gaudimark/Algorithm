<?php
namespace app\admin\model;
use library\service\Play;
use think\Model;
use app\library\model\BasicModel;

class Rules extends BasicModel{
    public $rulesItem = [];
    public function __construct(array $data = []){
        parent::__construct($data);
    }

    public function _beforeInsert($model){
        $data = $model->getData();
        if(!isset($data['rules_item']) || !$data['rules_item']){
            $this->error = '选项列表不能为空';
            return false;
        }
        $this->rulesItem = $data['rules_item'];
        unset($data['rules_item']);
        $model->data($data);
        return true;
    }

    public function _afterInsert($model){
        $data = $model->getData();
        $dataList = [];
        foreach($this->rulesItem['name'] as $key => $item){
            $dataList[] = [
                'rules_id' => $data['id'],
                'name' => $item,
                'min' => $this->rulesItem['min'][$key],
                'max' => $this->rulesItem['max'][$key],
            ];
        }
        $this->name("rules_item")->insertAll($dataList);
        $this->upCache();
    }

    public function _beforeUpdate($model){
        $data = $model->getData();
        $res = $model->get($data['id']);
        if(!$res['is_edit'] && (!isset($data['rules_item']) || !$data['rules_item'])){
            $this->error = '选项列表不能为空';
            return false;
        }
        $this->rulesItem = $data['rules_item'];
        unset($data['rules_item']);
        $model->data($data);
        return true;
    }
    public function _afterUpdate($model){
        $data = $model->getData();
        if($this->rulesItem){
            $dataList = [];
            foreach($this->rulesItem['name'] as $key => $item){
                if(isset($this->rulesItem['id'][$key])){
                    $this->name("rules_item")->where(["id" => $this->rulesItem['id'][$key]])->update([
                        'name' => $item,
                        'min' => $this->rulesItem['min'][$key],
                        'max' => $this->rulesItem['max'][$key],
                    ]);
                }else {
                    $dataList[] = [
                        'rules_id' => $data['id'],
                        'name' => $item,
                        'min' => $this->rulesItem['min'][$key],
                        'max' => $this->rulesItem['max'][$key],
                    ];
                }
            }
            $this->name("rules_item")->insertAll($dataList);
        }
        $this->upCache();
    }

    public function upCache(){
        $list = $this->field("id,game_id,game_type,type,name,alias,min_deposit,explain,intro,help_intro,status,is_delete")->order("sort asc,id asc")->select();
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
        //$result = cache('rules',$data);
        //$this->upRulesDetailCache();
        return $data;
    }
    
    public function upRulesDetailCache(){

        $limit = 100;
        $page = 0;
        while (true){
            $offset = $page * $limit;
            $list = $this->name("play_rules_detail")->limit($offset,$limit)->field("play_id")->group("play_id")->select();
            if(!$list){break;}
            foreach($list as $key =>$val){
                $playId = $val->play_id;
                $list = $this->name("play_rules_detail")->where(['play_id' => $playId])->field("game_id,rules_id,rules_explain,odds_id")->order("id asc")->select();
                $data = [];
                foreach($list as $key =>$val){
                    $_temp = $val->toArray();
                    $data[$val["rules_id"]][$val['odds_id']] = array("game_id" => $_temp["game_id"], "rules_explain" => json_decode($_temp["rules_explain"], true));
                }
                cache("play_rules_detail_{$playId}",$data);
            }
            $page++;
        }
    }

    public function upRulesDetailCacheByPlayId($playId){
        return (new Play())->upAllRulesDetailCacheByPlayId($playId);
    }

    public function item(){
       return $this->hasMany("rulesItem");
    }


}

/*Rules::event('before_insert',function($model){
    return $model->_beforeInsert($model);
});
Rules::event('after_insert',function($model){
    return $model->_afterInsert($model);
});
Rules::event('before_update',function($model){
    return $model->_beforeUpdate($model);
});
Rules::event('after_update',function($model){
    return $model->_afterUpdate($model);
});*/