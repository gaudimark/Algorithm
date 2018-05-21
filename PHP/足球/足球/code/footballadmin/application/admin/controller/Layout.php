<?php
namespace app\admin\controller;
use app\admin\logic\Basic;

class Layout extends Basic{

    public function index(){
        $list = model('Layout')->order("position asc,id asc")->paginate(10,false,['query' => input()]);
        $this->assign("list",$list);
        return $this->fetch();
    }

    public function add(){
        if($this->request->isPost()){
            $id = input("id/d");
            $title = input("post.title");
            $btime = input("post.btime");
            $etime = input("post.etime");
            $type = input("post.type/d");
            $position = input("post.position/d");
            $status = input("post.status/d");
            $inv_img = input("post.inv_img",'');

            if(!$type){
                return $this->error("请选择布局样式");
            }

            if($btime && strtotime($btime)){
                $btime = strtotime($btime);
            }
            if($etime && strtotime($etime)){
                $etime = strtotime($etime);
            }
            $where = [];
            if($id){
                $where['id'] = $id;
            }
            model("Layout")->save([
                'title' => $title,
                'btime' => $btime,
                'etime' => $etime,
                'type' => $type,
                'status' => $status,
                'position' => $position,
                'inv_img' => $inv_img,
            ],$where);
            $id = $id ? $id : model("Layout")->id;
            return $this->success("操作成功",'',['id' => $id]);
        }

        $id = input("id/d");
        if($id){
            $res = model("Layout")->where(['id' => $id])->find();
            if($res['btime']){
                $res['btime'] = date("Y-m-d H:i:s",$res['btime']);
            }
            if($res['etime']){
                $res['etime'] = date("Y-m-d H:i:s",$res['etime']);
            }
            $this->assign("res",$res);
        }
        //$items = config("items");
        $this->assign("id",$id);
        return $this->fetch();
    }

    public function detail(){
        if($this->request->isPost()){
            $id = input("id/d");
            $name = input("name/a");
            $img = input("img/a");
            $item_id = input("item_id/a");
            $item_type = input("item_type/a");
            $res = input("res/a");
            $lib_id = input("lib_id/a");
            $lib_name = input("lib_name/a");
            if(!$id){
                return $this->error("模块参数错误");
            }
            $ret = model("Layout")->where(['id' => $id])->find();
            if(!$ret){
                return $this->error("模块不存在");
            }
            if(!$name){return $this->error("项目名称不能为空");}
            if(!$img){return $this->error("项目图片不能为空");}

            if(
                ($ret['type'] == LAYOUT_TYPE_TWO && (count($name) != 2 || count($img) != 2)) ||
                ($ret['type'] == LAYOUT_TYPE_THREE && (count($name) != 3 || count($img) != 3))
            ){
                return $this->error("数据填写不完整");
            }
            $data = [];
            foreach($name as $key => $val){
                $temp = [];
                if($val && $img[$key]){
                    $temp = [
                        'name' => $val,
                        'img' => $img[$key],
                        'item_id' => $item_id[$key],
                        'item_type' => $item_type[$key],
                        'res' => $res[$key]
                    ];
                    $t = [];
                    if($item_type[$key] != 'all'){
                        foreach ($lib_id[$key] as $k => $v) {
                            $t[] = ['id' => $v, 'name' => $lib_name[$key][$k]];
                        }
                    }
                    if(!$t && $item_type[$key] != 'all'){
                        return $this->error("数据填写不完整");
                    }
                    $temp['lib'] = $t;
                }
                $data[] = $temp;
            }
            if(!$data){
                return $this->error("数据填写不完整");
            }
            model("Layout")->save(['detail' => @json_encode(array_values($data))],['id' => $id]);
            return $this->success("操作成功");
        }
        $id = input("id/d");
        $res = model("Layout")->where(['id' => $id])->find();
        if(!$res){
            return $this->error("模块不存在");
        }
        $res['detail'] = @json_decode($res['detail'],true);
        $items = config("items");
        $this->assign("res",$res);
        $this->assign("items",$items);
        return $this->fetch();
    }

    public function delete(){
        if($this->request->isPost()){
            $id = input("id");
            model("Layout")->where(['id' => $id])->delete();
            return $this->success("删除成功");
        }
    }


    /**
     * 竞技模块
     */
    public function sports(){
        $list = modelN('layout_sports')->order("sort asc,id desc")->paginate(20,false,['query' => input()]);
        foreach($list as $key => $val){
            $val = $val->toArray();
            $val['detail'] = @json_decode($val['detail'],true);
            $list[$key] = $val;
        }
        $items = config("items");
        $this->assign("items",$items);
        $this->assign("list",$list);
        return $this->fetch();
    }
    /**
     * 竞技模块
     */
    public function sports_add(){
        if($this->request->isPost()){
            $where = [];
            $id = input("id/d");
            $item_id = input("item_id/d");
            $is_hot = input("is_hot/d",0);
            $name = input("post.name");
            $type = input("post.type");
            $status = input("post.status/d");
            $lib_id = input("lib_id/a");
            $lib_name = input("lib_name/a");

            $data = [];
            if($lib_id && $lib_name) {
                foreach ($lib_id as $key => $val) {
                    if ($val) {
                        $data[$val] = [
                            'id' => $val,
                            'name' => $lib_name[$key]
                        ];
                    }
                }
            }
            if(!$data && $type != 'all'){
                return $this->error("绑定内容不能为空");
            }
            if($id){
                $where['id'] = $id;
            }
            modelN("layout_sports")->save([
                'item_id' => $item_id,
                'name' => $name,
                'type' => $type,
                'is_hot' => $is_hot,
                'detail' => json_encode($data),
                'status' => $status,
            ],$where);
            $id = $id ? $id : modelN("layout_sports")->id;
            return $this->success("操作成功");
        }

        $id = input("id/d");
        if($id){
            $res = modelN("layout_sports")->where(['id' => $id])->find();
            if($res){
                $res['detail'] = json_decode($res['detail'],true);
            }
            $this->assign("res",$res);
        }
        $items = config("items");
        $this->assign("id",$id);
        $this->assign("items",$items);
        return $this->fetch();
    }
    /**
     * 竞技模块
     */
    public function sports_delete(){
        if($this->request->isPost()){
            $id = input("id");
            modelN("layout_sports")->where(['id' => $id])->delete();
            return $this->success("删除成功");
        }
    }


    public function sports_sort(){
        if($this->request->isPost()){
            $id = input('id');
            $value = input('value');
            $data['sort'] = (int)$value;
            modelN('layout_sports')->where(['id' => $id])->update($data);
            return $this->success('更新成功');
        }
    }
}


