<?php
/**
 * 请求域名
 * Date: 2017/5/10
 * Time: 10:04
 */
namespace app\index\controller;
use app\library\logic\Basic;

class Domain extends Basic{

    public function lists(){
        $systemConf = config("system");
        $territory = @json_decode($systemConf['territory'],true);
        $outside = @json_decode($systemConf['outside'],true);
        $domain = [];
        $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if(!$ref){
            $domain = $territory;
        }else{
            $info = parse_url($ref);
            $domain_ref = explode(PHP_EOL,$territory['domain_ref']);
            foreach($domain_ref as $key => $val){
                $domain_ref[$key] = trim($val);
            }
            if(in_array(strtolower($info['host']),$domain_ref)){
                $domain = $territory;
            }else{
                $domain = $outside;
            }
        }

        $domainApi = explode(PHP_EOL,$domain['domain_api']);
        $domainSocket = explode(PHP_EOL,$domain['domain_socket']);
        //$domainDown = explode(PHP_EOL,$domain['domain_down']);
        $domainRes = explode(PHP_EOL,$domain['domain_res']);

        $data['api'] = $this->_parseDomain($domainApi[0]);
        $domainSocketIndex = array_rand($domainSocket);
        $domainSocket = trim($domainSocket[$domainSocketIndex]);
        $domainSocket = explode(":",$domainSocket);
        if(!isset($domainSocket[1])){
            $domainSocket[1] = 80;
        }

        $data['socket'] = ['server' => $domainSocket[0],'port' => $domainSocket[1]];
        //$data['down'] = $this->_parseDomain($domainDown[0]);
        $data['res'] = $this->_parseDomain($domainRes[0]);
        $data['homeowner'] = isset($systemConf['sys_homeowner_on']) ? intval($systemConf['sys_homeowner_on']) : 0; //开启申请房主
        //热更地址
        $data['app_online_update_url_lb'] = isset($systemConf['app_online_update_url_lb']) && $systemConf['app_online_update_url_lb'] ? $systemConf['app_online_update_url_lb'] : '';

        return $this->retSucc('system',$data);
    }

    private function _parseDomain($domain){
        $domain = trim($domain);
        if(substr($domain,0,7) == 'http://' || substr($domain,0,8) == 'https://'){
            return rtrim($domain,"/")."/";
        }
        $domain = "http://{$domain}";
        return rtrim($domain,"/")."/";
    }

}
