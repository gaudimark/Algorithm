<?php

namespace app\library\extend;
use think\Config;
class Upload{
    private $fileField = '';
    private $config = [];
    private $uploadType = '';
    private $newFileName = '';
    private $error = '';
    public function __construct($fileField = 'uploadfile'){
        $this->fileField = $fileField;
        $this->newFileName = \org\Stringnew::keyGen();
        $this->config = Config::get('system');
        $this->uploadType = $this->config['upload_type'];


    }
    public function save(){
        list($fileName,$subName,$conf) = $this->_getConfig();
        if(!$conf){$this->error = '无效上传配置文件';return false;}
        $maxSize = 0;
        if($conf && isset($conf['upload_backstage_size'])){
            $maxSize = $conf['upload_backstage_size'] * 1024;
        }

        $file = @$_FILES[$this->fileField];
        $upload = new \org\Upload([
            'maxSize' => $maxSize,
            'autoSub' => true,
            'rootPath' => $conf['bucket'],
            'subName' => $subName,
            'saveName' => $fileName,
        ],$this->uploadType,$conf);
        if(false === $upInfo = $upload->uploadOne($file)){
            $this->error = $upload->getError();
            return false;
        }
        
        switch (strtolower($this->uploadType)){
            case 'local':
                $url = get_image_thumb_url($upInfo['savepath'].$upInfo['savename']);
                break;
            default:
                $url = $upInfo['url'];
                break;
        }
        return [
            'url' => $url,
            'ext' => $upInfo['ext'],
            'original' => substr($file['name'],0,strripos($file['name'],'.')),
            'name' => $upInfo['savename'],
            'path' => $upInfo['savepath'].$upInfo['savename'],
            'ext' => $upInfo['ext'],
            'size' => $upInfo['size'],
            'upload_type' => $this->uploadType,

        ];
    }


    public function downImg($url,$ext = "jpg"){
        $imgUrl = htmlspecialchars($url);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);
        if (strpos($imgUrl, "http") !== 0) {
            $this->error = "链接不是http链接";
            return;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->error = "非法 URL";
            return;
        }
        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';
        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->error = "非法 IP";
            return;
        }
        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->error = "无效网络图片地址，链接不可用";
            return;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, ['.jpg','.png','.gif','.jpeg']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->error = "无效网络图片地址";
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();

        list($fileName,$subName,$conf) = $this->_getConfig();
        $urlInfo = pathinfo($imgUrl);
        $file = [
            'tmp_name' => $img,
            'name'  => $urlInfo['basename'],
            'extension' => $urlInfo['extension'],
            'ext' => $urlInfo['extension'],
            'size'  => strlen($img),
            'error' => 0,
        ];

        $upload = new \org\Upload([
            'autoSub' => true,
            'rootPath' => $conf['bucket'],
            'subName' => $subName,
            'saveName' => $fileName,
        ],$this->uploadType,$conf);
        if(false === $upInfo = $upload->remote($file)){
            $this->error = $upload->getError();
            return false;
        }

        switch (strtolower($this->uploadType)){
            case 'local':
                $url = $conf['domain'].$upInfo['savepath'].$upInfo['savename'];
                break;
            default:
                $url = $upInfo['url'];
                break;
        }
        return [
            'url' => $url,
            'ext' => $upInfo['ext'],
            'original' => substr($file['name'],0,strripos($file['name'],'.')),
            'name' => $upInfo['savename'],
            'path' => $upInfo['savepath'].$upInfo['savename'],
            'ext' => $upInfo['ext'],
            'size' => $upInfo['size'],
            'upload_type' => $this->uploadType,

        ];
    }

    public function base64($data,$ext,$name,$size){
        $file = [
            'tmp_name' => $data,
            'name'  => $name,
            'extension' => $ext,
            'ext' => $ext,
            'size'  => $size,
            'error' => 0,
        ];
        list($fileName,$subName,$conf) = $this->_getConfig();
        $upload = new \org\Upload([
            'autoSub' => true,
            'rootPath' => $conf['bucket'],
            'subName' => $subName,
            'saveName' => $fileName,
        ],$this->uploadType,$conf);
        if(false === $upInfo = $upload->remote($file)){
            $this->error = $upload->getError();
            return false;
        }
        switch (strtolower($this->uploadType)){
            case 'local':
                $url = $conf['domain'].$upInfo['savepath'].$upInfo['savename'];
                break;
            default:
                $url = $upInfo['url'];
                break;
        }
        return [
            'url' => $url,
            'ext' => $upInfo['ext'],
            'original' => substr($file['name'],0,strripos($file['name'],'.')),
            'name' => $upInfo['savename'],
            'path' => $upInfo['savepath'].$upInfo['savename'],
            'ext' => $upInfo['ext'],
            'size' => $upInfo['size'],
            'upload_type' => $this->uploadType,
        ];
    }

    public function getError(){
        return $this->error;
    }

    private function _getConfig(){
        $conf = [];
        $subName = '';
        $fileName = $this->newFileName;
        switch (strtolower($this->uploadType)){
            case 'local':
                $subName = getSubPath($this->newFileName,2,$this->config['upload_local_level']);
                $fileName = substr($this->newFileName,2 * $this->config['upload_local_level']);
                $conf = $this->_localConf();
                break;
            case 'qiniu' :
                $conf = $this->_qiniuConf();
                break;
            case 'upyun':
                $conf = $this->_upyunConf();
                break;
            case 'ftp' :
                $subName = getSubPath($this->newFileName,2,$this->config['upload_ftp_level']);
                $fileName = substr($this->newFileName,2 * $this->config['upload_ftp_level']);
                $conf = $this->_ftpConf();
        }
        return [$fileName,$subName,$conf];
    }

    /**
     * @param $conf
     * @return a
     */

    private function _localConf(){
        $conf = [
            'domain' => $this->config['upload_local_domain'],
            'bucket' => SITE_PATH.$this->config['upload_local_bucket'],
        ];
        return $conf;
    }

    private function _qiniuConf(){
        $conf = [
            'secretKey' => $this->config['upload_qiniu_secretkey'], //七牛服务器
            'accessKey' => $this->config['upload_qiniu_accesskey'], //七牛用户
            'domain'    => $this->config['upload_qiniu_domain'], //访问域名
            'bucket'    => $this->config['upload_qiniu_bucket'], //空间名称
            'timeout'   => $this->config['upload_qiniu_timeout'], //超时时间
        ];
        return $conf;
    }

    private function _upyunConf(){
        $conf = [
            'host'     => $this->config['upload_upyun_server'], //又拍云服务器
            'username' => $this->config['upload_upyun_username'], //又拍云用户
            'password' => $this->config['upload_upyun_password'], //又拍云密码
            'bucket'   => $this->config['upload_upyun_bucket'], //空间名称
            'timeout'  => $this->config['upload_upyun_timeout'], //超时时间
            'domain'    => $this->config['upload_upyun_domain'], //访问域名
        ];
        return $conf;
    }
    private function _ftpConf(){
        $conf = [
            'host'     => $this->config['upload_ftp_server'], //服务器
            'port'     => $this->config['upload_ftp_port'], //端口
            'timeout'  => $this->config['upload_ftp_timeout'], //超时时间
            'username' => $this->config['upload_ftp_username'], //用户名
            'password' => $this->config['upload_ftp_password'], //密码
            'bucket' => $this->config['upload_ftp_bucket'] == '/' ? '' : $this->config['upload_ftp_bucket'],
            'domain' => $this->config['upload_ftp_domain'],
        ];
        return $conf;
    }
}