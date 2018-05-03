<?php
/**
 * 加载配置文件
 * Class Config
 * @package app\library\behavior
 */
namespace library\behavior;
use think\Cache;
use think\Config;
use think\Db;
use think\Lang;

class AppInit{
    public function run(&$params){
        //include (ROOT_PATH."library".DS."common.php");
        Config::load(ROOT_PATH . 'library/conf/config.php');
        Lang::load(ROOT_PATH. 'library/lang/zh_cn.php');
        // 读取扩展配置文件
        if (is_dir(ROOT_PATH . 'library/conf/extra')) {
            $dir   = ROOT_PATH . 'library/conf/extra';
            $files = scandir($dir);
            foreach ($files as $file) {
                if (strpos($file, CONF_EXT)) {
                    $filename = $dir . DS . $file;
                    Config::load($filename, pathinfo($file, PATHINFO_FILENAME));
                }
            }
        }

        $confs = Config::get();
        $sys = Cache::get('system');
        if(!$sys){
            $sys = $this->upCache();
        }
        //视图替换内容
        $config = [
            'view_replace_str' => [
                '__formId__' => "form_".uniqid(),
                '__domain__'    => $confs['site_domain'],
                '__res__'    => $confs['site_source_domain'],
                '__attach__'    => $confs['site_source_domain'],
            ],
        ];
        if(isset($sys['domain_res']) && $sys['domain_res']){
            $domain_res = @json_decode($sys['domain_res']);
            $domain_res = $domain_res[0];
            if(substr($domain_res,0,7) != 'http://' || substr($domain_res,0,8) != 'https://'){
                $domain_res = "http://{$domain_res}";
            }
            $domain_res = rtrim($domain_res,"/")."/";
            $config['site_source_domain'] = $domain_res;
        }
        $domain = @json_decode($sys['territory'],true);
        $domainApi = explode(PHP_EOL,$domain['domain_api']);
        $domainSocket = explode(PHP_EOL,$domain['domain_socket']);
        $domainSocketIndex = array_rand($domainSocket);
        $domainSocket = trim($domainSocket[$domainSocketIndex]);
        $domainSocket = explode(":",$domainSocket);
        $domainRes = explode(PHP_EOL,$domain['domain_res']);
        $config['domain_api'] = $this->_parseDomain($domainApi[0]);
        $config['domain_res'] = $this->_parseDomain($domainRes[0]);
        $config['domain_socket'] =  ['server' => $domainSocket[0],'port' => $domainSocket[1]];

        Config::set($config);
        Config::set("system",$sys);
    }

    private function _parseDomain($domain){
        $domain = trim($domain);
        if(substr($domain,0,7) == 'http://' || substr($domain,0,8) == 'https://'){
            return rtrim($domain,"/")."/";
        }
        $domain = "http://{$domain}";
        return rtrim($domain,"/")."/";
    }

    protected function upCache(){
        $config = Db::name('config')->column('value','var');
        cache('system',$config);
        return $config;
    }

}