<?php

namespace app\admin\logic;
use app\admin\controller\Config;
use think\Controller;
use think\Request;

class Basic extends Controller{
    public $admin_id = 1;
    public $admin_user = [];
    public $admin_role = [];
    public $admin_user_list = [];
    public $gamelist = []; //游戏列表
    public $ditchlist = []; //渠道列表
    public $ditchclassify = []; //渠道分组列表
    public $gameSvr = null;
    protected $_time;
    protected $_ymd;
    protected $_ymdhis;
    public $method = '';
    public $action = '';
    public $controller = '';
    public function __construct(){
        parent::__construct();
        $this->checkLogin();
        $menu = new Menu($this->request,$this->admin_user,$this->admin_role);
        $menu->parseMenu();
        $this->assign("topMenu",$menu->topMenus);
        //常用菜单
        $leftMenu = $this->_getCommonMenu($menu->leftMenus,$menu->topMenus);
        $this->assign("leftMenu",$leftMenu);
        $this->assign("menuActive",$menu->topMenuActive);
        $this->assign("leftActive",$menu->leftMenuActive);
        if(!$menu->checkPrivate()){
            return $this->error("你无权限访问和操作当前地址");
        }
        $this->_time||$this->_time = time();//存在那么不执行
        $this->_ymd||$this->_ymd = date('Y-m-d',$this->_time);//存在那么不执行
        $this->_ymdhis||$this->_ymdhis = date('Y-m-d H:i:s',$this->_time);//存在那么不执行


        $this->method = $this->request->method();
        $this->controller = $this->request->controller();
        $this->action = $this->request->action();

        $this->assign("controller",$this->controller);
        $this->assign("action",$this->action);
        $this->assign("method",$this->method);
    }

    public function checkAjax(){
        $params = input();
        if(isset($params['ajax']) || isset($params['is_ajax']) || isset($params['dialog_index'])){
            //var_dump($params);
            //$this->view->config("layout_on",false);
            //$this->view->engine->layout(false);
        }
    }

    public function checkLogin(){
        $admin_user = session("cp.user");
        $admin_id = session("cp.user_id");
        $admin_role = session("cp.role");
        $isLogin = true;
        if(!$admin_user || !$admin_id || !$admin_user){
            $isLogin = false;
        }
        if($admin_id != $admin_user['id']){
            $isLogin = false;
        }
        if($admin_user['role_id'] != -1 && $admin_user['role_id'] != $admin_role['id']){
            $isLogin = false;
        }
        if(!$isLogin){
            session(null);
            return $this->error("当前登录失败，请重新登录",url('admin/login/index'));
        }
        $user_list = session("cp.user_list");
        $this->assign("admin_id",$admin_id);
        $this->assign("admin_user",$admin_user);
        $this->assign("admin_role",$admin_role);
        $this->assign("admin_user_list",$user_list);
        $this->admin_id = $admin_id;
        $this->admin_user = $admin_user;
        $this->admin_role = $admin_role;
        $this->admin_user_list = $user_list;

        $this->assign("ditchclassify",$this->ditchclassify);
        $this->assign("ditchlist",$this->ditchlist);
    }


    public function __destruct(){
        $method = $this->request->method();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $params = $this->request->param();
        $url = $this->request->url();
        $code = md5($method.$controller.$action.json_encode($params).$url);
        if($code == session("LogViewCode")){
            return false;
        }
        session("LogViewCode",$code);
        $explain = "访问页面：{$url}";
        $data = [
            'controller' => $controller,
            'action' => $action,
            'param' => $params,
            'url' => $url,
            'ip' => $this->request->ip()
        ];
        \library\service\Log::sysView($this->admin_id,$method,$explain,$data);
    }

    public function delTemp(){
        return $this->_delFile(TEMP_PATH);
    }
    public function delLog(){
        return $this->_delFile(LOG_PATH);
    }


    public function getRoleOther($key){
        if($this->admin_user['role_id'] == -1){return true;} //超管不验证
        $otherData = isset($this->admin_role['other']) ? $this->admin_role['other'] : [];
        if($otherData && isset($otherData[$key])){
            return $otherData[$key];
        }
        return false;
    }




    private function _delFile($dir){
        if(stripos($dir,RUNTIME_PATH) === false){return;}
        $dh = opendir($dir);
        while ($file = readdir($dh)){
            if($file != '.' && $file != ".."){
                $full = $dir.$file;
                if(!is_dir($full)){
                    unlink($full);
                }else{
                    $this->_delFile("{$full}/");
                }
            }
        }
        closedir($dh);
    }
    
    //获取常用菜单
    private function _getCommonMenu($leftMenu,$topMenu){
        $menu = array();
        if($leftMenu){
            $list = commonMenu($this->admin_id,$leftMenu,$topMenu);
            if($list){
                foreach ($list as $v){
                    if($v){
                    	$leftMenu["dashboard"][1]["list"][] = $v;
		    }
                }
                $menu = $leftMenu;
            }else{
                $menu = $leftMenu;
            }
        }
        
        return $menu;
    }

}