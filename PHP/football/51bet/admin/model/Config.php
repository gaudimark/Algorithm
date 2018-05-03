<?php
namespace app\admin\model;
use think\Model;

class Config extends Model{
    protected $autoWriteTimestamp = false;
    //protected $table = 'config';
    private $_tab = '';
    private $form_field = '';

    public function saveConfig($from,$config){
        if(!isset($config['setting']) || !$config['setting']){
            $this->error = '无效更新数据';
            return false;
        }
        $data = $config['setting'];
        $method = "_{$from}";
        if (!method_exists($this, $method)){
            $this->error = '无效的数据提交';
            return false;
        }

        if (false === $this->$method($data)){
            return false;
        }
        $config = $this->column('id,value','var');
        $update = [];
        $insert = [];
        foreach($data as $key => $val){
            if(isset($config[$key])){
                $update[] = [
                    'id' => $config[$key]['id'],
                    'value' => $val
                ];
            }else{
                $insert[] = [
                    'var' => $key,
                    'value' => $val
                ];
            }
        }
        if($update){$this->saveAll($update);}
        if($insert){$this->saveAll($insert);}
        $this->upCache();
        return true;
    }


    private function _autoArena(&$data){
        $ret = $data;
        $data = [];
        $data['auto_arena'] = json_encode($ret);
    }

    private function _basic(&$data){
        if(isset($data['site_name']) && !$data['site_name']){
            $this->error = '站点名称不能为空';
            $this->_tab = 'home';
            $this->form_field = 'site_name';
            return false;
        }

    }
    private function _mail(&$data){
        if(isset($data['mail_password']) && $data['mail_password']){
            $data['mail_password'] = \org\Crypt::encrypt($data['mail_password'],DEFAULT_KEY);
        }
    }
    private function _attach(&$data){
        if(isset($data['upload_local_level']) && $data['upload_local_level'] > 5){
            $data['upload_local_level'] = 5;
        }
        if(isset($data['upload_type'])){
            $upload_type = ucfirst($data['upload_type']);
            $method = "_validUpload{$upload_type}";
            if (!method_exists($this, $method)){
                $this->error = '无效的存储方式';
                $this->_tab = 'attach';
                return false;
            }

            if (false === $this->$method($data)){
                return false;
            }
        }
    }
    private function _cache(&$data){
        if(isset($data['cache_type'])){
            $cache_type = ucfirst($data['cache_type']);
            $method = "_validCache{$cache_type}";
            if ($cache_type && !method_exists($this, $method)){
                $this->error = '无效的数据缓存类型';
                $this->_tab = 'advanced';
                return false;
            }
            if ($cache_type && false === $this->$method($data)){
                return false;
            }
        }
    }
    private function _system(&$data){

        //$data['sys_gold'] = intval($data['sys_gold']);
        //$data['sys_yuan'] = intval($data['sys_yuan']);
        $data['sys_min_deposit'] = intval($data['sys_min_deposit']);
        $data['sys_max_arena_open_time'] = intval($data['sys_max_arena_open_time']);
       // $data['sys_max_arena_unsettled'] = intval($data['sys_max_arena_unsettled']);
        $data['sys_maker_brok'] = intval($data['sys_maker_brok']);
        $data['sys_player_brok'] = intval($data['sys_player_brok']);
        $data['sys_homeowner_on'] = isset($data['sys_homeowner_on']) ? intval($data['sys_homeowner_on']) : 0;
        $data['agent_settled_on'] = isset($data['agent_settled_on']) ? intval($data['agent_settled_on']) : 0;
        $data['filter_word_faq_on'] = isset($data['filter_word_faq_on']) ? intval($data['filter_word_faq_on']) : 0;
        $data['sys_arena_min_deposit'] = isset($data['sys_arena_min_deposit']) ? json_encode($data['sys_arena_min_deposit']) : json_encode([]);
        $data['sys_arena_min_bet_money'] = isset($data['sys_arena_min_bet_money']) ? json_encode($data['sys_arena_min_bet_money']) : json_encode([]);
        $data['user_reg_word_validate'] = isset($data['user_reg_word_validate']) ? intval($data['user_reg_word_validate']) : 0;
        $data['user_login_word_validate'] = isset($data['user_login_word_validate']) ? intval($data['user_login_word_validate']) : 0;
        $data['validate_code_auto_fill'] = isset($data['validate_code_auto_fill']) ? intval($data['validate_code_auto_fill']) : 0;
        $data['user_reg_mobile_change'] = isset($data['user_reg_mobile_change']) ? intval($data['user_reg_mobile_change']) : 0;
        $data['system_report_on'] = isset($data['system_report_on']) ? intval($data['system_report_on']) : 0;
        $data['user_new_guide'] = isset($data['user_new_guide']) ? intval($data['user_new_guide']) : 0;
        $sys_chip = $data['sys_chip'];
        if($sys_chip){
            $sys_chip = array_map("intval",$sys_chip);
        }

        //$data['sys_pay_type'] = json_encode($data['sys_pay_type']);
        $data['sys_chip'] = json_encode($sys_chip);
    }

    private function _arena_auto(&$data){
        $data['arena_auto_statement'] = json_encode($data['arena_auto_statement']);
        return $data;
    }
    private function _domain(&$data){
        $data['territory'] = isset($data['territory']) ? json_encode($data['territory']) : json_encode([]);
        $data['outside'] = isset($data['outside']) ? json_encode($data['outside']) : json_encode([]);

    }
    private function _arena_android(&$data){
        $data['arena_android_on'] = isset($data['arena_android_on']) ? json_encode($data['arena_android_on']) : json_encode([]);
        $data['arena_android_limit'] = isset($data['arena_android_limit']) ? json_encode($data['arena_android_limit']) : json_encode([]);
        $data['arena_android_gt_rand'] = isset($data['arena_android_gt_rand']) ? json_encode($data['arena_android_gt_rand']) : json_encode([]);
        $data['arena_android_lt_rand'] = isset($data['arena_android_lt_rand']) ? json_encode($data['arena_android_lt_rand']) : json_encode([]);
        $data['arena_android_bfb_rand'] = isset($data['arena_android_bfb_rand']) ? json_encode($data['arena_android_bfb_rand']) : json_encode([]);
    }
    public function getErrorData(){
        return [
            'tab' => $this->_tab,
            'form_field' => $this->form_field,
        ];
    }

    public function upCache(){
        $config = $this->column('value','var');
        return cache('system',$config);
    }


}