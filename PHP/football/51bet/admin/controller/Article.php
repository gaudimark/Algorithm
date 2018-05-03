<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;

class Article extends \app\admin\logic\Basic {

    public function help_type() {
        $limit = 10;
        $list = Db::name("help_type")->order("id desc")->paginate($limit, false);

        $this->assign("list", $list);
        return $this->fetch();
    }

    public function help_type_add() {
        if ($this->request->isPost()) {
            $id = input("post.id/d");
            $name = trim(input("post.name"));
            if (!$name) {
                $this->error("请输入标题");
            }
            $data = array();
            $data["name"] = $name;
            if (!$id) {
                if (Db::name("help_type")->insert($data)) {
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                if (Db::name("help_type")->where("id", $id)->update($data)) {
                    $this->success("修改成功");
                } else {
                    $this->error("修改失败");
                }
            }
        }

        $id = input("id/d");
        $info = array();
        if ($id) {
            $info = Db::name("help_type")->where(["id" => $id])->find();
        }
        $this->assign("info", $info);
        $this->assign("id", $id);
        return $this->fetch();
    }

    public function help_type_delete() {
        if ($this->request->isPost()) {
            $id = input("id/d");
            if (Db::name("help_type")->where("id", $id)->delete()) {
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        }
    }

    public function help() {
        $param = input("param.");
        $limit = 10;
        //$list = Db::name("help")->where(['type' => 1])->order("id desc")->paginate($limit, false);
        $list = model('help')->getHelpListByWhere(['type' => 1],18,"m.id desc",$param);
        $this->assign("list", $list);
        return $this->fetch();
    }

    public function help_add() {
        if ($this->request->isPost()) {
            $id = input("post.id/d");
            $name = trim(input("post.name"));
            $help_type = trim(input("post.help_type"));
            $content = $_POST['content'];
            if ('' == $name && $help_type!=2) {
                $this->error("请输入标题");
            }
            if ('' == $content) {
                $this->error("请输入锚点名称");
            }
            $data = array();
            $data["name"] = $name;
            $data["type_id"] = $help_type;
            $data["content"] =$content;
            $data["update_time"] = time();
            if (!$id) {
                $data["add_time"] = time();
                $data["type"] = 1;
                $data["admin_id"] = $this->admin_id;
                if (Db::name("help")->insert($data)) {
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                if (Db::name("help")->where("id", $id)->update($data)) {
                    $this->success("修改成功");
                } else {
                    $this->error("修改失败");
                }
            }
        }

        $id = input("id/d");
        $info = array();
        if ($id) {
            $info = Db::name("help")->where(["id" => $id])->find();
        }
        $help_type = Db::name('help_type')->select();
        
        $this->assign("help_type", $help_type);
        $this->assign("info", $info);
        $this->assign("id", $id);
        return $this->fetch();
    }

    public function help_delete() {
        if ($this->request->isPost()) {
            $id = input("id/d");
            if (Db::name("help")->where("id", $id)->delete()) {
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        }
    }

    public function msg() {
        $limit = 10;
        $list = Db::name("notice")->order("id desc")->paginate($limit, false);

        $this->assign("list", $list);
        return $this->fetch();
    }

    public function msg_add() {
        if ($this->request->isPost()) {
            $id = input("post.id/d");
            $content = trim(input("post.content", ''));
            if (!$content) {
                $this->error("请输入内容");
            }
            $data = array();
            $data["content"] = $content;
            $data["update_time"] = time();
            if (!$id) {

                $data["create_time"] = time();
                if (Db::name("notice")->insert($data)) {
                    $this->success("添加成功");
                } else {
                    $this->error("添加失败");
                }
            } else {
                if (Db::name("notice")->where("id", $id)->update($data)) {
                    $this->success("修改成功");
                } else {
                    $this->error("修改失败");
                }
            }
        }

        $id = input("id/d");
        $info = array();
        if ($id) {
            $info = Db::name("notice")->where(["id" => $id])->find();
        }
        $this->assign("info", $info);
        $this->assign("id", $id);
        return $this->fetch();
    }

    public function msg_delete() {
        if ($this->request->isPost()) {
            $id = input("id/d");
            if (Db::name("notice")->where("id", $id)->delete()) {
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        }
    }

}

?>