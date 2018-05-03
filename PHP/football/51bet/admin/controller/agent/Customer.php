<?php
/**
 * 客服答疑
 * Date: 2017/3/9
 * Time: 10:35
 */
namespace app\admin\controller\agent;
use app\admin\logic\Basic;
use library\service\Socket;
use think\Db;
class Customer extends Basic{

    public function index(){
        //cache("KF_ASK_TOTAL",0);
        $user_type = input("user_type");
        $nickname = input("nickname");
        $opt = input("opt");
        $where = [];
        if($user_type && $nickname){
            if($user_type == 1){
                $where["u.user_number"] = (int)$nickname;
            }elseif($user_type == 2){
                $where["u.id"] = (int)$nickname;
            }elseif($user_type == 4){
                $where["u.guid"] = $nickname;
            }elseif($user_type == 3){
                $where["u.nickname"] = ["like","%$nickname%"];;
            }
        }
        $lists = Db::name('recharge_agent_kf')
            ->alias('k')->field('k.*,u.nickname,u.user_number,u.guid')
            ->join('__USER__ u','u.id=k.user_id','LEFT')->where($where)->order("k.last_question_time desc")->paginate(20,false,['query' => input()]);
        /*foreach($lists as $key => $val){
            $user = getUser($val['user_id']);
            $val['nickname'] = $user['nickname'];
            $val['user_number'] = $user['user_number'];
            $val['guid'] = isset($user['guid']) ? $user['guid'] : '';
            $lists[$key] = $val;
        }*/
        $this->assign("lists",$lists);
        $this->assign("user_type",$user_type);
        $this->assign("nickname",$nickname);
        $this->assign("opt",$opt);
        return $this->fetch();
    }

    public function reply(){
        $manager = Db::name('manager')->column('id,nickname,username','id');
        $this->assign("manager",$manager);
        if($this->request->isPost()){
            $id = input("post.id/d");
            $userId = input("post.user_id/d");
            $content = input("post.content");
            $user = getUser($userId);
            $this->assign("user",$user);
            if((new \library\service\Customer())->agentReply($id,$content,$this->admin_id)){
                $data = [
                    'id' => $id,
                    'kf_id' => $id,
                    'manager_id' => $this->admin_id,
                    'content' => $content,
                    'create_time' => time(),
                ];
                $this->assign("vo",$data);
                $this->view->config("layout_on",false);
                $this->view->engine->layout(false);
                $content = $this->fetch("reply_list");
                //推送
                (new Socket())->sendToUid($userId,['type' => 'agent_ask','time' => time()],'socket.to_send_agent_customer');
                return $this->success("回复成功",'',[
                    'content' => $content,
                ]);
            }
            return $this->error("回复失败");
        }

        $id = input("id/d");
        $user_id = input("user_id/d");
        $lists = Db::name('recharge_agent_kf_detail')->where(['kf_id' => $id])->limit(200)->order("create_time desc")->select();
        $lists = array_reverse($lists);
        $user = getUser($user_id);
        $this->assign("lists",$lists);
        $this->assign("user",$user);
        $this->assign("id",$id);
        return $this->fetch();
   }

   public function batch_reply(){
        if($this->request->isPost()){
            $ids = input('ids');
            $message = input('message');
            $ids = explode(",",$ids);
            if(!$ids){
                return $this->error("批量回复失败，未选择回复对象");
            }
            if(!$message || !trim($message)){

                return $this->error("批量回复失败，回复内容不能为空");
            }

            foreach($ids as $val){
                list($id,$userId) = explode("|",$val);
                if((new \library\service\Customer())->agentReply($id,$message,$this->admin_id)){
                    //推送
                    (new Socket())->sendToUid($userId,['type' => 'agent_ask','time' => time()],'socket.to_send_agent_customer');
                }
            }
            return $this->success("回复成功");
        }
        $ids = input('ids');
        $this->assign("ids",$ids);
        return $this->fetch();
   }

    public function total(){
        return $this->success("OK",'',[
            'total' => cache("KF_ASK_TOTAL"),
        ]);
    }

    public function del(){
        if($this->request->isPost()){
            $id = input("id/d");
            if($id){
                Db::name('recharge_agent_kf')->where(['id' => $id])->delete();
                Db::name('recharge_agent_kf_detail')->where(['kf_id' => $id])->delete();
                return $this->success("删除成功");
            }
            return $this->error("删除失败");
        }
        return $this->error("删除失败");
    }

    public function reply_del(){
        if($this->request->isPost()){
            $id = input("id/d");
            if($id){
                Db::name('recharge_agent_kf_detail')->where(['id' => $id])->delete();
                return $this->success("删除成功");
            }
            return $this->error("删除失败");
        }
        return $this->error("删除失败");
    }
}