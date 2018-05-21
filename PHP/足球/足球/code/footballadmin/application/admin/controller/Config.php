<?php

namespace app\admin\controller;
use library\service\Socket;
use org\Crypt;
class Config extends \app\admin\logic\Basic{
    private $model = null;
    public function __construct(){
        parent::__construct();
        $this->model = model('config');
        if(!$this->request->isPost()) {
            $setting = $this->model->column('value', 'var');

            if (isset($setting['auto_arena']) && $setting['auto_arena']) {
                $setting['auto_arena'] = @json_decode($setting['auto_arena'], true);
            } else {
                $setting['auto_arena'] = [];
            }
            if (isset($setting['sys_chip']) && $setting['sys_chip']) {
                $setting['sys_chip'] = @json_decode($setting['sys_chip'], true);
            } else {
                $setting['sys_chip'] = [];
            }
            if (isset($setting['sys_arena_min_deposit']) && $setting['sys_arena_min_deposit']) {
                $setting['sys_arena_min_deposit'] = @json_decode($setting['sys_arena_min_deposit'], true);
            } else {
                $setting['sys_arena_min_deposit'] = [];
            }
            if (isset($setting['sys_arena_min_bet_money']) && $setting['sys_arena_min_bet_money']) {
                $setting['sys_arena_min_bet_money'] = @json_decode($setting['sys_arena_min_bet_money'], true);
            } else {
                $setting['sys_arena_min_bet_money'] = [];
            }
            if (isset($setting['arena_auto_statement']) && $setting['arena_auto_statement']) {
                $setting['arena_auto_statement'] = @json_decode($setting['arena_auto_statement'], true);
            } else {
                $setting['arena_auto_statement'] = [];
            }
            if (isset($setting['territory']) && $setting['territory']) {
                $setting['territory'] = @json_decode($setting['territory'], true);
            } else {
                $setting['territory'] = [];
            }
            if (isset($setting['outside']) && $setting['outside']) {
                $setting['outside'] = @json_decode($setting['outside'], true);
            } else {
                $setting['outside'] = [];
            }

            if (isset($setting['arena_android_on']) && $setting['arena_android_on']) {
                $setting['arena_android_on'] = @json_decode($setting['arena_android_on'], true);
            } else {
                $setting['arena_android_on'] = [];
            }
            if (isset($setting['arena_android_limit']) && $setting['arena_android_limit']) {
                $setting['arena_android_limit'] = @json_decode($setting['arena_android_limit'], true);
            } else {
                $setting['arena_android_limit'] = [];
            }
            if (isset($setting['arena_android_gt_rand']) && $setting['arena_android_gt_rand']) {
                $setting['arena_android_gt_rand'] = @json_decode($setting['arena_android_gt_rand'], true);
            } else {
                $setting['arena_android_gt_rand'] = [];
            }
            if (isset($setting['arena_android_lt_rand']) && $setting['arena_android_lt_rand']) {
                $setting['arena_android_lt_rand'] = @json_decode($setting['arena_android_lt_rand'], true);
            } else {
                $setting['arena_android_lt_rand'] = [];
            }
            if (isset($setting['arena_android_bfb_rand']) && $setting['arena_android_bfb_rand']) {
                $setting['arena_android_bfb_rand'] = @json_decode($setting['arena_android_bfb_rand'], true);
            } else {
                $setting['arena_android_bfb_rand'] = [];
            }
            if ($setting['odds'] == 1) {
                $setting['odds'] = 1;
            } else {
                $setting['odds'] = 0;
            }

            $this->assign("setting", $setting);
        }
    }

    public function basic(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('basic',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('系统设置更新成功');
            }
        }
        return $this->fetch();
    }

    public function mail(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('mail',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('邮件设置更新成功');
            }
        }
        return $this->fetch();
    }
    public function attach(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('attach',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('附件设置更新成功');
            }
        }
        return $this->fetch();
    }
    public function cache(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('cache',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('缓存设置更新成功');
            }
        }
        return $this->fetch();
    }
    public function system(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('system',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('高级设置更新成功');
            }
        }
        $sms = config("sms");
        $this->assign("sms",$sms);
        return $this->fetch();
    }

    /**
     * 擂台自动结算 
     */
    public function arena_auto(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('arena_auto',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('擂台自动结算设置更新成功');
            }
        }
        return $this->fetch();
    }
    /**
     * 请求域名
     */
    public function domain(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('domain',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('请求域名设置更新成功');
            }
        }
        return $this->fetch();
    }

    /**
     * 充值
     * @return mixed
     */
    public function recharge(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('recharge',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('充值设置更新成功');
            }
        }
        $paytype = [
            PAY_TYPE_ALIPAY => '支付宝',
            PAY_TYPE_WEIXIN => '微信',
            PAY_TYPE_BANK => '银联',
            PAY_TYPE_QQ => 'QQ',
            PAY_TYPE_JD => '京东'
        ];
        $pay_list = config('pay');
        $this->assign('pay_list',$pay_list);
        $this->assign('paytype',$paytype);
        return $this->fetch();
    }

    /**
     * 提现
     * @return mixed
     */
    public function withdrawal(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('withdrawal',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('提现设置更新成功');
            }
        }
        return $this->fetch();
    }
    /**
     * 赠送
     * @return mixed
     */
    public function gift(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('gift',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('赠送设置更新成功');
            }
        }
        return $this->fetch();
    }

    /**
     * 房间机器人
     * @return mixed
     */
    public function arena_android(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('arena_android',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('房间机器人设置更新成功');
            }
        }
        $this->assign('game_list',[]);
        return $this->fetch();
    }

    public function lookbet(){

        $model = model("LookConfig");
        $list = $model->paginate();
        $this->assign("list",$list);


        return $this->fetch();
    }

    public function addLookbet(){
        $model = model("LookConfig");
        if($this->request->isPost()){
            $condition = trim(input('condition/d'));
            $max_limit = trim(input('max_limit/d'));
            $id = input('id/d');
            $where = [];
            if($id){
                $where['id'] = $id;
            }
            $data = $model->where(['condition' => $condition,"id"=>['neq',$id]])->find();
            if($data){
                return $this->error("该条件已存在");
            }


            if($model->save(['condition' => $condition,'max_limit' => $max_limit],$where)){
                return $this->success('投注单查看设置成功');
            }else{
                return $this->error('投注单查看设置失败');
            }
            
        }
        $id = input('id');
        if($id){
            $res = $model->get($id);
            $this->assign("res",$res);
        }

        $this->assign("id",$id);
        return $this->fetch('lookbet_add');
    }

    public function delLookbet(){
        $model = model("LookConfig");
        if($this->request->isPost()){
            $id = input('id/d');
            if($model->destroy($id)){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }

    public function words(){
        $where = [];
        $kw = input('kw');
        if($kw){
            $where['text'] = ['like',"%{$kw}%"];
        }
        $lists = modelN('filter_words')->where($where)->order('id desc')->paginate(20,false,input());

        $this->assign("lists",$lists);
        $this->assign("kw",$kw);
        return $this->fetch();
    }
    public function words_add(){
        if($this->request->isPost()){
            $id = input("id/d");
            $name = input('name');
            if($id){
                modelN('filter_words')->save(['text' => $name],['id' => $id]);
            }else{
                modelN('filter_words')->save(['text' => $name]);
            }
            return $this->success("操作成功");
        }
        $id = input("id/d");
        $res = [];
        if($id){
            $res = modelN('filter_words')->get($id);
        }
        $this->assign("res",$res);
        return $this->fetch();
    }

    public function words_del(){
        if($this->request->isPost()){
            $id = input('id/d');
            if(modelN('filter_words')->destroy($id)){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }


    public function words_faq_status(){
        if($this->request->isPost()){
            $id = input('id/d');
            $res = modelN('filter_words')->where(['id' => $id])->find();
            $statue = $res['faq_status'] == STATUS_ENABLED ? STATUS_DISABLED : STATUS_ENABLED;
            modelN('filter_words')->where(['id' => $id])->update(['faq_status' => $statue]);
            return $this->success('操作成功');
        }
        return $this->error('操作失败');
    }


    public function app_client(){
        $lists = modelN('app_client')->select();
       // $this->assign("ditch",cache('Ditch'));
       // $this->assign("company",cache('ditch_company'));
        $this->assign("lists",$lists);
        return $this->fetch();
    }
    public function app_client_add(){
        if($this->request->isPost()){
            $id = input("post.id");
            $data['item_id'] = input("post.item_id");
            $data['ditch_id'] = (int)input("post.ditch_id/d");
            $data['platform'] = (string)input("post.platform");
            $data['version'] = (int)input("post.version");
            $data['down_url'] = (string)input("post.down_url");
            $data['update_url'] = (string)input("post.update_url");
            $data['login_server_url'] = (string)input("post.login_server_url");
            $data['login_server_url_bak'] = (string)input("post.login_server_url_bak");
            $data['share_url'] = (string)input("post.share_url");
            if($id){
                modelN('app_client')->save($data,['id' => $id]);
                return $this->success("更新成功");
            }else{
                modelN('app_client')->save($data);
                return $this->success("添加成功");
            }
        }

        $id = input("id/d");
        $res = [];
        if($id){
            $res = modelN('app_client')->where(['id' => $id])->find();
        }
        //$this->assign("ditch",cache('Ditch'));
        $this->assign("res",$res);
        return $this->fetch();
    }

    public function app_client_del(){
        if($this->request->isPost()){
            $id = input("id/d");
            modelN('app_client')->where(['id' => $id])->delete();
            return $this->success("删除成功");
        }
    }

    public function app_update(){
        return $this->fetch();
    }
    
    //分享设置 
    public function shareIndex(){
        return $this->fetch("config/shareindex");
    }
    
    public function shareAdd(){
        if($this->request->isPost()){
            $is_edit = input("post.is_edit/d");
            $game_type = input("post.game_type/d");
            $url = trim(input("post.url"));
            $text = trim(input("post.text"));
            if(!$url){
                return $this->error("转发url不能为空！");
            }
            if(!$text){
                return $this->error("转发内容不能为空！");
            }
            $data = array();
            $data["game_type"] = $game_type;
            $data["url"] = $url;
            $data["text"] = $text;
            $setting = $this->model->column('value','var');
            $arena_share = array();
            if(isset($setting["sys_share_data"]) && $setting["sys_share_data"]){
                $arena_share = @json_decode($setting['sys_share_data'],true);
            }
            if(!$is_edit && isset($arena_share[$game_type])){
                return $this->error("对应类别数据已经存在！");
            }
            if(isset($arena_share[$game_type])){
                unset($arena_share[$game_type]);
            }
            $arena_share[$game_type] = $data;

            $res = $this->model->where(['var' => 'sys_share_data'])->find();
            if(!$res){
                $this->model->save([
                    'var' => 'sys_share_data',
                    'value' => json_encode($arena_share),
                ]);
            }else{
                $this->model->where("var","sys_share_data")->update(array("value"=>json_encode($arena_share)));
            }
            return $this->success('操作成功');
        }

        $game_type = input("game_type/d");
        $this->assign("game_type",$game_type);
        return $this->fetch("config/shareAdd");
    }
    
    public function shareDel(){
        $game_type = input("game_type/d");
        if(!$game_type){
            return $this->error("参数异常");
        }
        $setting = $this->model->column('value','var');
        $arena_share = array();
        if(isset($setting["sys_share_data"]) && $setting["sys_share_data"]){
            $arena_share = @json_decode($setting['sys_share_data'],true);
        }
        if(isset($arena_share[$game_type])){
            unset($arena_share[$game_type]);
        }
            
        if($this->model->where("var","sys_share_data")->update(array("value"=>json_encode($arena_share)))){
            return $this->success("修改成功");
        }else{
            return $this->error("修改失败");
        }
    }


    public function stop(){
        if($this->request->isPost()){
            $shop_svr_on = input('post.shop_svr_on/d');
            $shop_svr_msg = input('post.shop_svr_msg');
            $data = [
                'shop_svr_on' => (int)$shop_svr_on,
                'shop_svr_msg' => $shop_svr_msg,
            ];
            cache('SERVER_STATUS_ON_OFF',$data);
            if($shop_svr_on){
                $socket = new Socket();
                $socket->alertToAll($shop_svr_msg,1);
                return $this->success("停服推送成功");
            }else{
                return $this->success("修改成功");
            }
        }
        $this->assign('res',cache('SERVER_STATUS_ON_OFF'));
        return $this->fetch();
    }


    public function auto_seal(){
        if($this->request->isPost()){
            if(false === $this->model->saveConfig('auto_seal',input("post."))){
                return $this->error($this->model->getError(),null,$this->model->getErrorData());
            }else{
                return $this->success('自动封号更新成功');
            }
        }
        return $this->fetch();
    }

    public function black(){
        if($this->request->isPost()){
            $type = input('type');
            $content = input('content');
            $content = trim($content);
            $res = model('black_list')->where(['type' => $type])->find();
            if($res){
                model('black_list')->save(['content' => $content],['type' => $type]);
            }else{
                model('black_list')->save(['content' => $content,'type' => $type]);
            }
            return $this->success("操作成功");
        }
        $res = model('black_list')->where(['type' => BLACK_LIST_MOBILE])->find();
        $this->assign('res',$res);
        return $this->fetch();
    }

    public function whitelist(){
        if($this->request->isPost()){
            $type = input('type');
            $content = input('content');
            $content = trim($content);
            $res = model('white_list')->where(['type' => $type])->find();
            if($res){
                model('white_list')->save(['content' => $content],['type' => $type]);
            }else{
                model('white_list')->save(['content' => $content,'type' => $type]);
            }
            return $this->success("操作成功");
        }
        $res = model('white_list')->where(['type' => WHITE_LIST_WD])->find();
        $this->assign('res',$res);
        return $this->fetch();
    }
}