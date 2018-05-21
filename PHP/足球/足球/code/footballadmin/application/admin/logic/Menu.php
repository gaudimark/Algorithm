<?php
namespace app\admin\logic;
class Menu{
    private $_thisModule = '';
    private $_topMenuActive = ''; //顶站菜单焦点
    private $_letfMentActiveLists = []; //当前控制器焦点
    private $req = null;
    private $controller = '';
    private $action = '';
    private $menus = [];
    private $role = [];
    private $user = [];
    private $allows = ['index','misc'];
    private $isMatch = false;
    //private $aollow

    public $topMenus = []; //顶站菜单列表
    public $leftMenus = []; //左边菜单列表
    public $topMenuActive = ''; //当前菜单焦点
    public $leftMenuActive = []; //当前菜单焦点

    public function __construct($req,$user = [],$role = []){
        $this->req = $req;
        $this->controller = strtolower($this->req->controller());
        $this->action = strtolower($this->req->action());
        $this->_thisModule = strtolower($this->req->module());
        $this->role = $role;
        //var_dump($role);
        $this->user = $user;
    }

    public function parseMenu($menuFile = ''){
        //var_dump($role);
        if($menuFile){
            $menuFile = $menuFile;
        }else{
            $menuFile = CONF_PATH . 'admin/menu.php';
        }
        if(is_file($menuFile)){
            $this->menus = include($menuFile);
        }
        $this->_parseMenu();
    }

    private function _parseMenu(){
        $temp = [];
        foreach($this->menus as $key => $menu){
            if(substr($key,0,7) == 'divider'){
                $this->topMenus[$key] = ['name' => 'divider'];
            }elseif(isset($menu['list'])){
                $result = $this->_parseMenuList($menu['list'],$key);
                if($result){
                    $temp[$key] = array_values($result);
                    $this->topMenus[$key] = [
                        'name' => $menu['name'],
                        'icon' => $menu['icon'],
                    ];
                }else{
                    unset($temp[$key]);
                }
            }
        }
        if($this->isMatch){
            $temp = $this->checkActive($temp);
        }
        $this->leftMenus = $temp;
    }

    private function _parseMenuList($menu,$key){
        if($menu && is_array($menu)){
            foreach($menu  as $mk => $list){
                if(isset($list['url']) && is_array($list['url'])){
                    $params = [];
                    if(isset($list['url'][2])){
                        $params = $list['url'][2];
                    }
                    $controller = strtolower($list['url'][0]);
                    $action = strtolower($list['url'][1]);
                    if($this->user['role_id'] != -1 && !in_array($controller,$this->allows)){
                        if(isset($this->role['limit'][$controller])){
                        }
                        if (!isset($this->role['limit'][$controller])){
                            unset($menu[$mk]);
                            continue;
                        }
                        $actionList = $this->_parseAction($this->role['limit'][$controller]);
                        if (!in_array($action, array_values($actionList))){
                            unset($menu[$mk]);
                            continue;
                        }
                    }
                    //兄弟焦点入口
                    $siblings = [];
                    if(isset($list['siblings'])){
                        $siblings = $list['siblings'];
                    }
                    if($this->controller == $controller && ($this->action == $action || in_array($this->action,$siblings))){
                        $this->topMenuActive = $key;
                        $list['active'] = 1;
                        $this->isMatch = true;
                    }elseif($this->controller == $controller){
                        if(!$this->topMenuActive){
                            $this->leftMenuActive[$key][] = $mk;
                            $this->topMenuActive = $key;
                            $list['active'] = 2;
                        }
                    }

                    $list['url'] = url("{$this->_thisModule}/{$controller}/{$action}", $params);
                    $menu[$mk] = $list;
                }elseif(isset($list['list'])){
                    if(!$this->topMenuActive) {
                        $this->leftMenuActive[$key][] = $mk;
                    }
                    $ret = $this->_parseMenuList($list['list'],$key);
                    if($ret){
                        $menu[$mk]['list'] = array_values($ret);
                    }else{
                        unset($menu[$mk]);
                    }
                }
            }
        }
        return $menu;
    }

    public function checkPrivate(){
        //var_dump($this->controller,$this->action);
        if($this->user['role_id'] == -1){return true;}
        if(!in_array($this->controller,$this->allows)){
            if (!isset($this->role['limit'][$this->controller])){
                return false;
            }
            $actionList = $this->_parseAction($this->role['limit'][$this->controller]);
            //var_dump($this->role['limit'][$this->controller]);
            if (!in_array($this->action,array_values($actionList))){
                return false;
            }
        }
        return true;
    }

    public function checkSpecifyPrivate($controller,$action){
        //var_dump($this->controller,$this->action);
        $controller = strtolower($controller);
        if($this->user['role_id'] == -1){return true;}
        if(!in_array($controller,$this->allows)){
            if (!isset($this->role['limit'][$controller])){
                return false;
            }
            $actionList = $this->_parseAction($this->role['limit'][$controller]);
            //var_dump($this->role['limit'][$this->controller]);
            if(is_array($action)){
                foreach($action as $a){
                    if (in_array(strtolower($a),array_values($actionList))){
                        return true;
                    }
                }
                return false;
            }else{
                if (!in_array(strtolower($action),array_values($actionList))){
                    return false;
                }
            }
        }
        return true;
    }




    private function _parseAction($actionList){
        $result = [];
        foreach($actionList as $val){
            if(stripos($val,",") !== false){
                $result = array_merge($result,explode(",",$val));
            }else{
                $result[] = $val;
            }
        }
        return $result;
    }

    private function checkActive($menu){
        foreach($menu as $key => $val){
            foreach($val as $vk => $vv){
                if(isset($vv['list'])){
                    foreach ($vv['list'] as $lk => $lv) {
                        if (isset($lv['active']) && $lv['active'] == 2){
                            unset($lv['active']);
                            $menu[$key][$vk]['list'][$lk] = $lv;
                        }
                    }
                }
            }
        }
        return $menu;
    }
}