<?php
namespace app\admin\model;

use think\Model;
class Message extends \app\library\model\Message
{
    
    public function updateSysMessage($data,$id){
        return $this->where("id",$id)->update($data);
    }
    
    public function insertSysMessage($data){
        return $this->insert($data,null,true);
    }
    
    public function insertAllSysMessageDetail($data){
        return $this->name('sys_message_detail')->insertAll($data);
    }
    
    public function deleteMessage($id){
        return $this->where("id",$id)->delete();
    }
    
}

?>