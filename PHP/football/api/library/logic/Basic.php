<?php
namespace app\library\logic;
use think\Cache;
use think\captcha\Captcha;
use think\Controller;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;

class Basic extends Controller{
    public $token = '';
    public $platform = 'h5'; //来源平台，默认为h5
    
    public function __construct(){
        parent::__construct();
        $this->wLog([
            'url' => $this->request->baseUrl(),
            'root' => $this->request->root(),
            'path' => $this->request->path(),
            'ip' => $this->request->ip(),
            'get' => input('get.'),
            'post' => input('post.'),
        ]);
    }

    /**
     * 返回错误提示
     * @param $mark
     * @param $code
     * @param array $vars
     * @throws HttpResponseException
     */
    public function retErr($mark,$code,$vars = [],$data = [],$ret = [],$returnArray = false){
        $markArr = config("mark");
        if(stripos($mark,".") === false){
            $mark = isset($markArr[$mark]) ? $markArr[$mark] : $mark;
        }else{
            list($a,$b) = explode(".",$mark);
            $mark = isset($markArr[$a][$b]) ? $markArr[$a][$b] : '';
        }
        $ret['mark'] = $mark;
        return $this->resultNew($data,$code,lang("{$code}",$vars),'',[],$ret,$returnArray);
    }

    /**
     * 返回成功提示
     * @param $mark
     * @param array $data
     * @param string $msg
     * @param array $ret
     * @param array $vars
     * @throws HttpResponseException
     */
    public function retSucc($mark,$data = [],$msg = '',$ret = [],$vars = [],$returnArray = false){
        $markArr = config("mark");
        if(stripos($mark,".") === false){
            $mark = isset($markArr[$mark]) ? $markArr[$mark] : $mark;
        }else{
            list($a,$b) = explode(".",$mark);
            $mark = isset($markArr[$a][$b]) ? $markArr[$a][$b] : '';
        }
        $ret['mark'] = $mark;
        $msg = is_numeric($msg) ? lang($msg,$vars) :$msg;
        return $this->resultNew($data,0,$msg,'',[],$ret,$returnArray);
    }


    public function resultNew($data, $code = 0, $msg = '', $type = '', array $header = [],$ret = [],$returnArray = false)
    {
        $csrf = md5(time().Request::instance()->ip().DEFAULT_KEY);
        Cache::set("CSRF_{$this->token}",$csrf,3600);
        $result = array_merge([
            'code' => $code,
            'msg'  => $msg,
            'time' => $_SERVER['REQUEST_TIME'],
            'csrf' => $csrf, //防止重复提交数据
        ],$ret);
        $result['data'] = $data;
        $this->wLog($result);
        if($returnArray){
            return $result;
        }
        $type     = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 防止重复提交数据
     * 防止xxs攻击
     * @param $csrf
     */
    public function checkCsrf($csrf){
        if($csrf != Cache::get("CSRF_{$this->token}")){
            return false;
        }
        return true;
    }

    /**
     * 生成验证码
     * @param int $length
     * @return mixed
     */
    public function getCaptcha($length = 6,$codeSet = ''){
        $codeSet = $codeSet ? $codeSet : '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $capt = new Captcha(['seKey' => DEFAULT_KEY,'length' => $length,'codeSet' => $codeSet]);
        return $capt->entry();
    }

    /**
     * 验证验证码
     * @param $code
     * @return bool
     */
    public function checkCaptcha($code){
        $capt = new Captcha(['seKey' => DEFAULT_KEY]);
        return $capt->check($code);
    }

    public static function wLog($data){
        $fileSize = 2097152;
        $path = LOG_PATH."api/";
        $now         = date('c');
        $destination = $path . date('Ym') . DS . date('d') . '.log';

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($fileSize) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }
        $depr = "---------------------------------------------------------------\r\n";
        $message = var_export($data,true);
        return error_log("[{$now}] {$message}\r\n{$depr}", 3, $destination);
    }

}