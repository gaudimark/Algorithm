<?php
namespace app\admin\model;
use app\library\model\BasicModel;
use think\Request;

class Manager extends BasicModel{
    private $updatePkId = null;
    public function _beforeInsert($model){
        $data = $model->getData();

        $user = $this->where(['username' => $data['username']])->find();
        if($user){
            $this->error = "管理员{$data['username']}已存在";
            return false;
        }

        if(!isset($data['password']) || !$data['password']){
            $this->error = "请输入密码";
            return false;
        }
        $data['salt'] = \org\Stringnew::randNumber(111111,999999);
        $data['password'] = $this->parsePassword($data['password'],$data['salt']);
        $model->data($data);
        return true;
    }

    public function _beforeUpdate($model){
        $data = $model->getData();
        unset($data['username']);
        if(isset($data['password']) && $data['password']){
            $salt = \org\Stringnew::randNumber(111111,999999);
            $data['salt'] = $salt;
            $newPassword = $this->parsePassword($data['password'],$salt);
            $data['password'] = $newPassword;
        }else{
            unset($data['password']);
        }
        $model->data($data,true); //标记记录字段
        $model->data($data);//更新数据
        return true;
    }

    public function updateUserSession($id){
        $user = $this->get($id);
        if($user){
            $user = $user->toArray();
            unset($user['password']);
            unset($user['salt']);
            session("cp.user",null);
            session("cp.user", $user);
        }
    }

    public function parsePassword($password,$salt){
        $pwd = md5($password);
        $s = md5($salt.$pwd);
        return md5($s.$password.md5($salt).$pwd);
    }

    public function upPwd($id,$oldpwd,$newpwd){
        $user = $this->get($id);
        if(!$user){
            $this->error = "获取用户数据失败";
            return false;
        }
        $user = $user->toArray();
        if($this->parsePassword($oldpwd,$user['salt']) != $user['password']){
            $this->error = "原密码输入错误";
            return false;
        }
        return $this->save(['password' => $newpwd],['id' => $id]);
    }

    public function doLogin($username,$password){
        $user = $this->where(['username' => $username])->find();

        if(!$user){
            $this->error = "用户错误，请重新输入";
            return false;
        }
        $user = $user->toArray();
        $password = $this->parsePassword($password,$user['salt']);

        if($password != $user['password']){
            $this->error = "密码错误，请重新输入";
            return false;
        }

        if($user['status'] == STATUS_DISABLED){
            $this->error = "该帐号已被禁用，无法登录";
            return false;
        }
        $role = [];
        if($user['role_id'] != -1){
            $role = $this->name("role")->where(['id' => $user['role_id']])->find();
            if (!$role){
                $this->error = "获取帐号权限失败，请重新登录";
                return false;
            }
            $role = $role->toArray();
            if($role['status'] == STATUS_DISABLED){
                $this->error = "该帐号所属角色已被禁用，无法登录";
                return false;
            }
            $role['limit'] = @json_decode($role['limit'],true);
            $role['other'] = @json_decode($role['other'],true);
        }


        $this->name("manager")->where(['id' => $user['id']])->update(['last_login_time' => time()]);
        $this->name("manager_log")->insert([
            'manager_id' => $user['id'],
            'create_time' => time(),
            'ip' => Request::instance()->ip()
        ]);
        unset($user['password']);
        unset($user['salt']);
        session("cp.user",$user);
        session("cp.user_id",$user['id']);
        session("cp.role",$role);
        //缓存管理员列表
        $list = $this->name("manager")->column('nickname','id');
        session("cp.user_list",$list);
        return true;
    }

    public function logout(){
        session("cp.user",null);
        session("cp.user_id",null);
        session("cp.role",null);
        session("cp.user_list",null);
        session(null);
        return true;
    }
}
Manager::event('before_update',function($model){
    return $model->_beforeUpdate($model);
});
Manager::event('before_insert',function($model){
    return $model->_beforeInsert($model);
});