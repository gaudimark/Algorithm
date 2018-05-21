<?php

use think\Cache;
use think\Url;
use think\Config;
use think\Route;
use think\Loader;

/**
 * 自定义安全过滤
 * @param $value
 * @return array|string
 */
function safeFilter($value) {
    if (is_array($value)) {
        foreach ($value as $key => $val) {
            if (!is_array($val)) {
                $value[$key] = str_replace("'", "&#039;", $val);
                $value[$key] = str_replace('"', "&quot;", $val);
            } else {
                $value[$key] = safeFilter($value[$key]);
            }
        }
    } else {
        //$value = htmlspecialchars($value,ENT_QUOTES);
        $value = str_replace("'", "&#039;", $value);
        $value = str_replace('"', "&quot;", $value);
    }
    return $value;
}

function abort($code, $message = '调试模式-页面不存在', $header = []) {
    if ($code instanceof Response) {
        throw new \think\exception\HttpResponseException($code);
    } else {
        throw new \think\exception\HttpException($code, $message, null, $header);
    }
}

function msubstr($str, $length, $start = 0, $charset = "utf-8", $suffix = true) {
    return \org\Stringnew::msubstr($str, $start, $length, $charset, $suffix);
}

function getSubPath($fileName, $len = 2, $level = 3, $split = '/') {
    $path = '';
    for ($i = 0; $i < $level; $i++) {
        $path .= substr($fileName, $i * 2, $len) . '/';
    }
    $path = substr($path, 0, -1);
    return $path;
}

/**
 * 写入文件内容
 * @param $filename 目录路径 如：e:/work/test.html
 * @param $writetext 写入文件内容
 * @param $openmod 打开文件类型 默认为'w'表示写入
 * @return true or false
 */
function swritefile($filename, $writetext, $openmod = 'w') {
    $dir = pathinfo($filename, PATHINFO_DIRNAME);
    if (!file_exists($dir))
        @mkdir($dir, 0775, true);
    $fp = @fopen($filename, $openmod);
    if ($fp) {
        flock($fp, 2);
        fwrite($fp, $writetext);
        fclose($fp);
        return true;
    } else {
        //runlog('error', "File: $filename write error.");
        return false;
    }
}

/**
 * 加载模型
 * @param string $name 模型名称
 * @param string $database 数据库
 * @param string $layer
 * @param bool $appendSuffix
 * @return Object
 */
function model($name = '', $layer = 'model', $appendSuffix = false, $database = null) {
    return Loader::model($name, $layer, $appendSuffix, 'library', $database);
}

function modelN($name = '', $database = 'main', $layer = 'model', $appendSuffix = false) {
    return Loader::model($name, $layer, $appendSuffix, 'library', $database);
}

function url($url = '', $vars = '', $suffix = true, $domain = false) {
    $url = strtolower($url);
    $_url = $url;
    $urlDomainDeploy = Config::get('url_domain_deploy');

    if (strpos($url, '@')) {
        // 解析域名
        list($url, $_) = explode('@', $url, 2);
        $domains = Route::rules('domain');
        if (!$urlDomainDeploy) {
            foreach ($domains as $key => $rule) {
                $bind = $rule['[bind]'][0];
                if ($_ == $key) {
                    $domain = $bind;
                    break;
                }
            }
            if (!$domain) {
                $domain = $_;
            }
            if (!$domain || $domain == 'www') {
                $domain = Config::get("default_module");
            }
            if ($domain && !$urlDomainDeploy) {
                $url = "{$domain}/{$url}";
                $domain = false;
            }
        } else {
            foreach ($domains as $key => $rule) {
                $bind = $rule['[bind]'][0];
                if ($_ == $bind || $key == $_) {
                    $domain = $key;
                    break;
                }
            }
            if (!$domain) {
                $url = $_url;
            }
        }
    }
    return Url::build($url, $vars, $suffix, $domain);
}

/**
 * 重写缓存函数，支持.号操作
 * @param $name
 * @param string $value
 * @param null $options
 * @return bool|mixed|object
 */
function cache($name, $value = '', $options = null) {

    if (is_array($options)) {
        // 缓存操作的同时初始化
        Cache::connect($options);
    } elseif (is_array($name)) {
        // 缓存初始化
        return Cache::connect($name);
    }
    if ('' === $value) {
        // 获取缓存
        if (stripos($name, ".") !== false) {
            $name = explode(".", $name);
            $result = Cache::get($name[0]);
            if (isset($result[$name[1]])) {
                return $result[$name[1]];
            } else {
                return null;
            }
        } else {
            return Cache::get($name);
        }
    } elseif (is_null($value)) {
        // 删除缓存
        return Cache::rm($name);
    } else {
        // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
        } else {
            $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
        }
        return Cache::set($name, $value, $expire);
    }
}

/**
 * 随机0-1之间的小数
 */
function randFolat() {
    return mt_rand() / mt_getrandmax();
}

function checkPhone($mobile) {
    $rex = "/^1[3|4|5|7|8]\d{9}$/";
    if (preg_match($rex, $mobile)) {
        return true;
    } else {
        return false;
    }
}

function checkEmail($email) {
    $email = strtolower($email);
    $patten = '/^[0-9a-z_][_\.0-9a-z\-]{0,31}@([0-9a-z][0-9a-z\-]{0,30}\.){1,4}[a-z]{2,4}$/i';
    preg_match($patten, $email, $match);
    if ($match) {
        return true;
    } else {
        return false;
    }
}

/**
  +----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
  +----------------------------------------------------------
 * @param string    $string   待转换的字符串
 * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string    $glue     分割符
  +----------------------------------------------------------
 * @return string   处理后的字符串
  +----------------------------------------------------------
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    if ($type == 0) {
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", $array);
    }else if ($type == 1) {
        $array = array_reverse($array);
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", array_reverse($array));
    }else if ($type == 2) {
        $array = explode($glue, $string);
        $array[0] = hideStr($array[0], $bengin, $len, 1);
        $string = implode($glue, $array);
    } else if ($type == 3) {
        $array = explode($glue, $string);
        $array[1] = hideStr($array[1], $bengin, $len, 0);
        $string = implode($glue, $array);
    } else if ($type == 4) {
        $left = $bengin;
        $right = $len;
        $tem = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i]))
                $tem[] = $i >= $left ? "*" : $array[$i];
        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}

function emoji_to_html($str) {
    $regex = '/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?|[\x{1F900}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?/u';
    $str = preg_replace_callback($regex, function($matches) {
        $str = json_encode($matches[0]);
        $str = '<em data-emoji=' . str_replace('\u', 'em:', $str) . '></em>';
        return $str;
    }, $str);
    return $str;
}

function html_to_emoji($str) {
    $str = preg_replace_callback('/<em data-emoji=\"(.*?)\"><\/em>/is', function($matches) {

        $str = $matches[0];
        $str = str_replace('em:', '\u', $str);
        return $str;
    }, $str);

    return $str;
}

function modelToArray($model, $indexKey = null) {
    if (!$model || !is_array($model)) {
        return $model;
    }
    $result = [];
    foreach ($model as $key => $val) {
        $val = $val->toArray();
        $_ = $indexKey ? $indexKey : $key;
        if (isset($val[$indexKey])) {
            $result[$val[$_]] = $val;
        } else {
            $result[$_] = $val;
        }
    }
    return $result;
}

function arrayIndex($items, $key) {
    if (!$items || !is_array($items)) {
        return $items;
    }
    $data = [];
    foreach ($items as $val) {
        $data[$val[$key]] = $val;
    }
    return $data;
}

function arrayTree($items, $primary_key, $parent_key) {
    if (!$items || !is_array($items)) {
        return $items;
    }
    $items = arrayIndex($items, 'id');
    $tree = array();
    foreach ($items as $item) {
        if (isset($items[$item[$parent_key]]) && $items[$item[$parent_key]]) {
            $items[$item[$parent_key]]['child'][] = &$items[$item[$primary_key]];
        } else {
            $tree[$item[$primary_key]] = &$items[$item[$primary_key]];
        }
    }
    return $tree;
}

function array_multi2single($items, $child_key = "child") {
    if (!is_array($items)) {
        return $items;
    }
    static $result_array = [];

    foreach ($items as $key => $val) {
        if (isset($val[$child_key]) && $val[$child_key]) {
            $child = $val[$child_key];
            unset($val[$child_key]);
            $result_array[] = $val;
            array_multi2single($child, $child_key);
        } else {
            $result_array[] = $val;
        }
    }
    return $result_array;
}

function arrayTreeToList($items, $primary_key, $level = 0) {
    if (!is_array($items)) {
        return $items;
    }
    $length = count($items);
    $count = 0;
    foreach ($items as $key => $item) {
        $count++;
        $item['_level_'] = $level;
        $item['_end_'] = ($length == $count) ? 1 : 0;
        if (isset($item['child']) && $item['child']) {
            $_level = $level + 1;
            $item['child'] = arrayTreeToList($item['child'], $primary_key, $_level);
        }
        $items[$key] = $item;
    }
    return $items;
}

function arraySelect($items, $primary_key, $text_name, $selected = null, $remove = [], $level = 0) {
    if (!is_array($items)) {
        return $items;
    }
    $option = "";
    $fill = "";
    if ($level > 0) {
        $fill = "";
        for ($i = 0; $i < $level; $i++) {
            $fill .= "&nbsp;&nbsp;"; //&nbsp;
        }
    }
    $length = count($items);
    $count = 0;
    foreach ($items as $item) {
        $count++;
        $_fill = $fill;
        if ($fill) {
            $_fill .= ($length == $count) ? "└  " : "├  ";
        }
        if (!$remove || ($remove && !in_array($item[$primary_key], $remove))) {
            $option .= '<option value="' . $item[$primary_key] . '" ' . ($selected && $selected == $item[$primary_key] ? "selected" : "") . '>' . $_fill . $item[$text_name] . '</option>';
        }
        if (isset($item['child']) && $item['child']) {
            $_level = $level + 1;
            $option .= arraySelect($item['child'], $primary_key, $text_name, $selected, $remove, $_level);
        }
    }
    return $option;
}

/**
 * 把数组的某一列做为key值返回整个数组
 * @param type $row_name
 * @param type $array
 * @return type
 */
function getArray($row_name, $array) {
    $return = array();
    if (!empty($array)) {
        foreach ($array as $value) {
            $return[$value[$row_name]] = $value;
        }
    }
    return $return;
}

/**
 * 转换擂台状态说明
 * @param $status
 * @param bool $class
 * @return string
 */
function arenaStatusToWord($status, $class = false) {
    $html = '<em class="%s">%s</em>';
    $className = '';
    $txt = '';
    if ($status == ARENA_START) {
        $txt = '投注中';
        $className = 'text-danger';
    } elseif ($status == ARENA_SEAL) {
        $txt = '已封擂';
        $className = 'text-primary';
    } elseif ($status == ARENA_PLAY) {
        $txt = '比赛进行中';
        $className = 'text-muted';
    } elseif ($status == ARENA_DIS) {
        $txt = '已取消';
        $className = 'text-info';
    } elseif ($status == ARENA_DEL) {
        $txt = '已删除';
        $className = 'text-warning';
    } elseif ($status == ARENA_STATEMENT_BEGIN) {
        $txt = '结算中';
        $className = 'text-info';
    } elseif ($status == ARENA_STATEMENT_END) {
        $txt = '结算完成';
        $className = 'text-muted';
    } elseif ($status == ARENA_STATEMENT_ERROR) {
        $txt = '结算失败';
        $className = 'text-danger';
    } elseif ($status == ARENA_END) {
        $txt = '比赛已结束';
        $className = 'text-muted';
    } else {
        $txt = '未知';
        $className = 'text-danger';
    }
    $className = $class ? $className : '';
    return sprintf($html, $className, $txt);
}

function betStatus($status, $class = false, $win_money = 0) {
    $html = '<em class="%s">%s</em>';
    $className = '';
    $txt = '';
    if ($status == DEPOSIT_WIN) {
        $txt = '已中奖';
        $className = 'label label-success';
    } elseif ($status == DEPOSIT_LOSE) {
        $txt = '未中奖';
        $className = 'text-danger';
    } elseif ($status == DEPOSIT_SAME) {
        $txt = '平手，退全部本金';
        $className = 'text-muted';
    } elseif ($status == DEPOSIT_LOST_HALF) {
        $txt = '未中奖，退一半本金';
        $className = 'text-danger';
    } elseif ($status == DEPOSIT_WIN_HALF) {
        $txt = '中奖，赢一半';
        $className = 'label label-success';
    } else {
        $txt = '未开奖';
        $className = 'text-muted';
    }
    $className = $class ? $className : '';
    return sprintf($html, $className, $txt);
}

/**
 * 擂台隐私
 * @param $status
 * @param bool $class
 * @return string
 */
function arenaDisplayToWord($status, $class = false) {
    $html = '<em class="%s">%s</em>';
    $className = '';
    $txt = '';
    if ($status == ARENA_DISPLAY_ALL) {
        $txt = '所有人';
        $className = 'text-muted';
    } elseif ($status == ARENA_DISPLAY_FRIENDS) {
        $txt = '仅好友';
        $className = 'text-danger';
    } elseif ($status == ARENA_DISPLAY_CODE) {
        $txt = '邀请码参加';
        $className = 'text-primary';
    }
    $className = $class ? $className : '';
    return sprintf($html, $className, $txt);
}

/**
 * 擂台风险值转换
 * @param $risk
 */
function arenaRisk($risk) {
    if ($risk <= 0) {
        return $risk;
    }
    if ($risk < 0.1) {
        return '轻微（10%以下）';
    } elseif ($risk < 0.2) {
        return '一般（10%-20%）';
    } elseif ($risk < 0.4) {
        return '中级（20%-40%）';
    } else {
        return '严重（40%以上）';
    }
}

/**
 * 当前可投注上限
 */
function betMaxLimit($odds, $totalBet, $rules = 0, $gameType = GAME_TYPE_FOOTBALL) {
    return (new \app\library\service\Arena())->betMaxLimit($odds, $totalBet, $rules, $gameType);
}

/**
 * 投注进行占比
 */
function betProgress($betNum, $total, $percentage = false) {
    if (!$total) {
        return 0;
    }
    $num = $betNum / $total;
    if ($percentage) {
        return round($num * 100, 2) . "%";
    }
    return $num;
}

function arenaTargetProgress($arenaId, $target, $prizePoll, $betTotal, $percentage = false) {
    $num = (new \library\service\Arena())->progress($prizePoll, $betTotal, $arenaId, $target);
    if ($percentage) {
        return round($num * 100, 2) . "%";
    }
    return $num;
}

/**
 * 盘口转换
 * @param $key
 * @return number|string
 */
function handicap($key, $prefix = true, $positive = true, $itemType = GAME_TYPE_FOOTBALL) { //受让 客让主   负数 主让客
    return (new \library\service\Rule())->factory($itemType)->handicap($key, $prefix, $positive);
}

/**
 * 大小预设总分抓换
 * @param $key
 * @return number|string
 */
function under($key, $prefix = true, $positive = true) { //受让 客让主   负数 主让客
    return (new \library\service\Rule())->under($key, $prefix, $positive);
}

/**
 * 获取数据库表名，补齐前缀，
 * @param $tableName
 */
function getTrueTableName($tableName) {
    $tablePrefix = config("database.prefix");
    $tablePrefixLen = strlen($tablePrefix);
    if ($tableName && substr($tableName, 0, $tablePrefixLen) != $tablePrefix) {
        $tableName = $tablePrefix . $tableName;
    }
    return strtolower($tableName);
}

/**
 * 预计收益
 */
function forWin($money, $odds, $ruleType = 0, $brok = 0.00, $gameType = GAME_TYPE_FOOTBALL) {
    return (new \library\service\Arena())->forWin($money, $odds, $ruleType, $brok, $gameType);
}

/**
 * 获取擂台投注项取新赔率
 * @param $arenaId
 * @param $target
 * @param $item
 */
function getArenaTargetOdds($arenaId, $target, $item = null) {
    $arena = (new \library\service\Arena())->getCacheArenaById($arenaId);
    if (!$arena) {
        return 'null';
    }
    if (stripos($target, ",")) {
        list($target, $item) = explode(',', $target);
    }
    if ($item) {
        return $arena['odds'][$target][$item];
    } else {
        return $arena['odds'][$target];
    }
}

/**
 * 计算 佣金
 * @param $money
 * @return string
 */
function forBrokerage($money) {
    return (new \library\service\Arena())->forBrokerage($money);
}

/**
 * 排序图标
 * @param $sort
 * @return string
 */
function getSortIcon($sort) {
    if (!$sort) {
        return '';
    }
    if ($sort == 'desc') {
        return '<i class="icon-arrow-up text-muted"></i>';
    } else {
        return '<i class="icon-arrow-down text-muted"></i>';
    }
}

/**
 * 获取项目
 * @param $gameType
 * @return string
 */
function getSport($gameType) {
    switch ($gameType) {
        case GAME_TYPE_FOOTBALL:
            return '足球';
        default:
            return '';
    }
}

/**
 * 根据游戏ID，获取游戏信息
 * @param $game_id
 */
function getGame($game_id, $key = null) {
    $name = "game_{$game_id}";
    $data = cache($name);
    if (!is_null($key) && $data && array_key_exists($key, $data)) {
        return $data[$key];
    }
    return $data;
}

/**
 * 获取项目下游戏列表
 * @param $gameType
 */
function getSportGames($gameType, $status = null) {
    $name = "game_sport_{$gameType}";
    $data = cache($name);
    $result = [];
    if ($data) {
        foreach ($data as $val) {
            $game = getGame($val);
            if (!$game) {
                continue;
            }
            if (is_null($status)) {
                $result[$game['id']] = $game;
            } elseif ($game['status'] == STATUS_ENABLED) {
                $result[$game['id']] = $game;
            }
        }
    }
    return $result;
}

/**
 * 获取足球玩法
 * @param $type
 * @return string
 */
function getRulesFootballType($type) {
    return (new \library\service\Rule())->getRuleTypeText(GAME_TYPE_FOOTBALL, $type);
}

/**
 * 获取足球玩法图标
 * @param $type
 * @return string
 */
function getFootballRulesIcon($type) {
    return (new \library\service\Rule())->getRuleIcon(GAME_TYPE_FOOTBALL, $type);
}

/**
 * 获取玩法对应名称
 * @param $gameType
 * @param $ruleType
 * @return string
 */
function getRuleTypeText($gameType, $ruleType) {
    return (new \library\service\Rule())->getRuleTypeText($gameType, $ruleType);
}

function getPlayStatus($status, $play_time = null) {
    $txt = '';
    switch ($status) {
        case PLAT_STATUS_NOT_START:
            $txt = '未开始';
            break;
        case PLAT_STATUS_START:
            $txt = '进行中';
            break;
        case PLAT_STATUS_INTERMISSION:
            $txt = '中场休息';
            break;
        case PLAT_STATUS_END:
            $txt = '已结束';
            break;
        case PLAT_STATUS_EXC:
            $txt = '延期';
            break;
        case PLAT_STATUS_SUSP:
            $txt = '停赛';
            break;
        case PLAT_STATUS_WAIT:
            $txt = '待定';
            break;
        case PLAT_STATUS_CUT:
            $txt = '腰斩';
            break;
        case PLAT_STATUS_STATEMENT_BEGIN:
            $txt = '结算中';
            break;
        case PLAT_STATUS_STATEMENT:
            $txt = '已结算';
            break;
    }
    if ($play_time && $play_time < time() && $status == PLAT_STATUS_NOT_START) {
        $txt = '进行中';
    }
    return $txt;
}

function getArenaStatus($status) {
    switch ($status) {
        case ARENA_START:
            return '投注中';
            break;
        case ARENA_SEAL:
            return '已封擂';
            break;
        case ARENA_PLAY:
            return '比赛进行中';
            break;
        case ARENA_END:
            return '比赛已结束';
            break;
        case ARENA_DIS:
            return '已取消';
            break;
        case ARENA_STATEMENT_BEGIN:
            return '结算中';
            break;
        case ARENA_STATEMENT_END:
            return '结算完成';
            break;
        case ARENA_DEL:
            return '已删除';
            break;
    }
    return $status;
}

/**
 * 根据球队ID从缓存中获取球队信息
 * @param $team_id
 * @param $key
 */
function getTeam($team_id, $key = null, $field = []) {
    $team = cache("team_{$team_id}");
    if (!$team) {
        return false;
    }
    if ($key && !isset($team[$key])) {
        return false;
    }
    if (isset($team['rank'])) {
        //$rank = $team['rank'][0];
        foreach ($team['rank'] as $k => $rank) {
            //if(!isset($rank['match_id']) || $rank['match_id'] != $team['match_id']){continue;}
            if ($rank['match_id']) {
                if ($rank['match_id'] == $rank['last_match_id']) {
                    $rank['match_name'] = $rank['last_match_name'];
                } else {
                    $rank['match_name'] = getMatch($rank['match_id'], 'name');
                }
            }
            if ($rank['fifa_season']) {
                $rank['season'] = $rank['fifa_season'];
            }
            unset($rank['create_time']);
            unset($rank['update_time']);
            $team['rank'][$k] = $rank;
        }
    }
    unset($team['create_time']);
    unset($team['update_time']);

    //$team['logo_big'] = get_image_thumb_url($team['logo']);
    $team['logo_big'] = get_image_thumb_url($team['logo']);
    //$team['logo'] = get_image_thumb_url('',90,90);
    $team['logo'] = get_image_thumb_url($team['logo'], 90, 90);
    if ($field) {
        $data = [];
        foreach ($field as $val) {
            if (isset($team[$val])) {
                $data[$val] = $team[$val];
            }
        }
        $team = $data;
    }


    return $key ? $team[$key] : $team;
}

/**
 * 根据球队ID从缓存中获取球队信息
 * @param $team_id
 * @param $key
 */
function getMatch($match_id, $key = null, $field = []) {
    $match = cache("match_{$match_id}");
    if (!$match) {
        return false;
    }
    if ($key && !isset($match[$key])) {
        return false;
    }
    if (isset($match['logo'])) {
        $match['logo'] = get_image_thumb_url($match['logo']);
    }

    if ($field) {
        $data = [];
        foreach ($field as $val) {
            if (isset($match[$val])) {
                $data[$val] = $match[$val];
            }
        }
        $match = $data;
    }

    return $key ? $match[$key] : $match;
}

/**
 * 根据用户ID获取用户信息
 * @param $team_id
 * @param $key
 */
function getUser($user_id, $key = null, $unset = true, $okArr = []) {
    $user = cache("user_{$user_id}");
    if (!$user) {
        return false;
    }
    //if($key && !isset($user[$key])){return false;}
    $user['avatar'] = getUserAvatar($user['avatar'], $user_id);
    if ($unset) {
        unset($user['create_time']);
        unset($user['update_time']);
        unset($user['login_fail_number']);
        unset($user['login_fail_time']);
    }
    if ($okArr) {
        $result = [];
        foreach ($okArr as $k => $val) {
            if (isset($user[$val])) {
                $result[$val] = $user[$val];
            } else {
                $result[$val] = '';
            }
        }
        $user = $result;
    }
    return $key ? $user[$key] : $user;
}

function getUserField($userId, $fields = null) {
    $fields = $fields ? $fields : ['id', 'nickname', 'avatar', 'friends',
        'arena_total', 'deposit_total', 'win_total', 'most_win', 'record',
        'bet_view_total', 'profit'
    ];
    return getUser($userId, null, false, $fields);
}

/**
 * 根据用户昵称获取用户信息
 * @param $team_id
 * @param $key
 */
function getUserByNickname($nickname, $key = null, $unset = true, $okArr = []) {
    if (!$nickname) {
        return false;
    }
    $nickname = md5(base64_encode($nickname));
    $userId = cache("user_{$nickname}");
    if (!$userId) {
        return false;
    }
    return getUser($userId, $key = null, $unset = true, $okArr = []);
}

function getUserAvatar($avatar, $user_id = 0) {

    if ($avatar) {
        $avatar = get_image_thumb_url($avatar);
    } else {
        $avatar = $user_id == SYS_USER_ID ? config("site_source_domain") . "common/images/systempic.png" :
                config("site_source_domain") . "attach/avatar/images/1.png";
    }
    return formatNull($avatar); //."?__=".time();
}

/**
 * 根据日期从缓存中获取有擂台的赛事信息
 * @param $date 20160901
 */
function getRecommendMatch($date, $gameType = GAME_TYPE_FOOTBALL) {
    $match = cache("recommend_match_{$date}");
    if (!isset($match[$gameType])) {
        return false;
    }
    return $match[$gameType];
}

/**
 * 足球
 * @param $game_type
 * @param $rule
 * @param $key
 * @return mixed|string|Config
 */
function getRule($game_type, $rule, $key) {
    $rules = config("rules." . $game_type);
    if (!isset($rules['list'])) {
        return $key;
    }
    $rules = $rules['list'];
    if (!isset($rules[$rule])) {
        return "";
    }
    if (is_array($key)) {
        $key = implode(",", $key);
    }
    if (stripos($key, ',') !== false) {
        list($_, $target) = explode(",", $key);
        if (!isset($rules[$rule][$target])) {
            return $key;
        }
        $rules = $rules[$rule][$target];
    } else {
        if (!isset($rules[$rule][$key])) {
            return $key;
        }
        $rules = $rules[$rule][$key];
    }
    if (is_array($rules)) {
        return $rules[0];
    }
    return $rules;
}

/**
 * 根据ID从缓存中获取规则、玩法数据
 * @param $rule_id
 */
function getRuleData($gameType, $rule_id = null, $key = null, $isDelete = false, $status = null, $game_id = null) {
    static $ruleSvrList;
    if (!$ruleSvrList || !isset($ruleSvrList[$gameType])) {
        $ruleSvrList[$gameType] = (new \library\service\Rule())->factory($gameType);
    }
    $ruleSvr = $ruleSvrList[$gameType];
    if (is_null($ruleSvr)) {
        return '';
    }
    $rulesData = $ruleSvr->rulesListAll(); //cache('rules');
    if (!$rulesData) {
        return null;
    }
    //if(!isset($rulesData[$gameType])){return '';}
    // $rulesData = $rulesData[$gameType];
    // if(!$rulesData){return null;}

    if (!is_null($rule_id) && isset($rulesData[$rule_id])) {
        $rulesData = $rulesData[$rule_id];
        if ($key && isset($rulesData[$key])) {
            $rulesData = $rulesData[$key];
        }
    }


    if ($rulesData && is_array($rulesData)) {
        foreach ($rulesData as $key => $val) {
            if ($isDelete && $val['is_delete']) {
                unset($rulesData[$key]);
            }
            if ($status && $val['status'] == STATUS_DISABLED) {
                unset($rulesData[$key]);
            }
            if ($game_id && $val['game_id'] != $game_id) {
                unset($rulesData[$key]);
            }
        }
    }

    if (!is_array($rulesData)) {
        $rulesData = parseRuleName($rulesData, [], [
            '#team_home_name#' => '主队',
            '#team_guest_name#' => '客队',
        ]);
    }
    return $rulesData;
}

/**
 * 判断主，客，平，其它
 * */
function getRuleTeam($key = '') {
    if ($key) {
        if (strtolower($key) == 'home') {
            return "主队";
        } elseif (strtolower($key) == "guest") {
            return "客队";
        } elseif (strtolower($key) == "same") {
            return "平";
        } elseif (strtolower($key) == "other") {
            return "其它";
        } else {
            return "";
        }
    } else {
        return '';
    }
}

/**
 * 根据玩法生成空赔率数据表
 * @param $gameType
 * @param $ruleType
 * @return array|bool
 */
function getDefaultOdds($gameType, $ruleType, $play_id = 0) {
    return (new \library\service\Rule())->factory($gameType)->getDefaultOdds($ruleType, $play_id);
}

function enUrl($params = []) {
    if (!is_array($params) || !$params) {
        return $params;
    }
    //$params = http_build_query($params);
    return \org\Crypt::encrypt($params, URL_ENCODE_KEY);
}

function deUrl($url) {
    $params = \org\Crypt::decrypt($url, URL_ENCODE_KEY);
    //$params = parse_str($params);
    return $params;
}

function getArenaDomain($mark, $gameType = '') {
    $url = '';
    switch ($gameType) {
        case GAME_TYPE_FOOTBALL:
            $url = url("arena/info@football", ['mark' => $mark]);
            break;
        default:
            $url = url('index/index@www');
    }
    return $url;
}

/**
 * 根据比例生成缩略图片
 * @param $sourceImageURL
 * @param int $thumbWidth
 * @param int $thumbHeight
 * @return string
 */
function get_image_thumb_url($sourceImageURL, $maxWidth = 0, $maxHeight = 0, $type = 3) {
    $url = config("site_source_domain");
    // $url = "__RES_DOMAIN__";
    $domain = config("system.site_domain");
    $sourceImageURL = str_replace($url, "", $sourceImageURL);
    $sourceImageURL = str_replace($domain, "", $sourceImageURL);

    if (!$maxWidth && !$maxHeight) {
        if ($sourceImageURL) {
            return substr($sourceImageURL, 0, 7) != 'common/' && substr($sourceImageURL, 0, 7) != 'attach/' ? "{$url}attach/{$sourceImageURL}" : "{$url}{$sourceImageURL}";
        } elseif (!$sourceImageURL) {
            return $url . "common/images/default.png";
        }
    }
    if (!$sourceImageURL) {
        $sourceImageURL = "common/images/default.png";
    } elseif (substr($sourceImageURL, 0, 7) != 'common/' && substr($sourceImageURL, 0, 7) != 'attach/') {
        $sourceImageURL = "attach/{$sourceImageURL}";
    }
    $imageInfo = pathinfo($sourceImageURL);
    $dirname = $imageInfo['dirname'] . "/";
    $extension = '';
    if (isset($imageInfo['extension'])) {
        $extension = $imageInfo['extension'];
    }
    $fileName = $imageInfo['filename'];
    $code = substr(md5($type . md5($fileName) . md5($maxWidth . $maxHeight)), 8, 10);
    $url = $url . "{$dirname}{$fileName}_{$code}_{$type}_{$maxWidth}_{$maxHeight}.{$extension}"; //;http://res.51bet.com/attach/team_logo/c8/75/02/1911471319504122_848172f315_2_90_90.jpg

    return $url;
}

function show404() {
    return abort(404);
}

function errPage($message, $title = '', $back = '') {
    $vars = [
        'action' => '',
        'title' => $title ? $title : '出错啦！',
        'error' => [
            'message' => $message,
            //'title' => $title ? $title : '出错啦！',
            'back' => $back ? $back : config("system.site_domain"),
    ]];
    return view("../51bet/library/view/mobile_error.html", $vars, [], 404);
}

function errAgentPage($message, $title = '', $back = '', $isMobile = true) {
    $vars = [
        'action' => '',
        'title' => $title ? $title : '出错啦！',
        'error' => [
            'message' => $message,
            'back' => $back ? $back : config("system.site_domain"),
            'isAgent' => 1,
    ]];
    $page = $isMobile == DEVICE_MOBILE ? 'mobile_agent_error' : 'pc_agent_error';
    return view("../application/library/view/{$page}.html", $vars, [], 404);
}

/**
 * 生成唯一标识
 * @param int $len 长度
 * @param string $prefix 前缀
 */
function getUniqueMark() {
    return md5(uniqid(md5(microtime(true)), true));
}

/* * *
 * 玩法是否分组，如波胆等
 */

function hasRulesPacket($rule) {
    if (in_array($rule, [RULES_TYPE_BODAN, RULES_TYPE_BODAN_COMB])) {
        return true;
    } else {
        return false;
    }
}

/**
 * 处理波胆数据，用于页面展示
 * 获取波胆相同比分的数据标识
 * @param $odds
 */
function getBodanSameScore() {
    $same = [];
    $rules = config("rules." . GAME_TYPE_FOOTBALL);
    $rules = $rules['list'][RULES_TYPE_BODAN];
    foreach ($rules as $key => $val) {
        if (($key != 'other' && $val[1] == $val[2])) {
            $same[$key] = $key;
        }
    }
    return $same;
}

/**
 * 返回用户当前结算未查看的数据
 * @param $user_id
 * @param bool $clear True时清空数量
 * @return int
 */
function getMyBetOrder($user_id, $clear = false) {
    $orderTotal = cache("arena_bet_order_total");
    if (isset($orderTotal[$user_id])) {
        if ($clear) {
            $orderTotal[$user_id] = 0;
            cache("arena_bet_order_total", $orderTotal);
        }
        return $orderTotal[$user_id];
    }
    return 0;
}

/**
 * 返回用户擂台当前投注未查看的数据
 * @param $user_id
 * @param bool $clear True时清空数量
 * @return int
 */
function getMyArenaNewbetOrder($user_id, $clear = false) {
    $orderTotal = cache("arena_bet_order_new");
    if (isset($orderTotal[$user_id])) {
        if ($clear) {
            $orderTotal[$user_id] = 0;
            cache("arena_bet_order_new", $orderTotal);
        }
        return $orderTotal[$user_id];
    }
    return 0;
}

/**
 * 返回用户当前结算未查看的中奖数据
 * @param $user_id
 * @param bool $clear True时清空数量
 * @return int
 */
function getMyBetWinOrder($user_id, $clear = false) {
    $orderTotal = cache("arena_bet_order_win");
    if (isset($orderTotal[$user_id])) {
        if ($clear) {
            $orderTotal[$user_id] = [];
            cache("arena_bet_order_win", $orderTotal);
        }
        return $orderTotal[$user_id];
    }
    return [];
}

/**
 * 格式化数字，不四舍五入
 * @param $number
 * @param int $dec 小数位
 * @param boll $format 是否千位分组
 */
function numberFormat($number, $dec = 2, $format = false) {
    $temp = 10;
    $number = (double) $number;
    if ($dec) {
        $arr = explode(".", $number);
        $new_dec = "";
        if (isset($arr[1]))
            $new_dec = substr($arr[1], 0, $dec);
        if ($new_dec)
            $number = $arr[0] . "." . $new_dec;
    }
    //$number = (float)$number;
    $number = $format ? ($dec ? number_format($number, $dec) : number_format($number) ) : $number;
    return $number;
}

/**
 * 投注选项时说明
 * @param $arenaId
 * @param $target
 * @return string
 */
function getRuleTips($arenaId, $target, $gameType = GAME_TYPE_FOOTBALL) {
    $arenaSvr = new \library\service\Arena();
    return $arenaSvr->getRuleTips($arenaId, $target, $gameType);
}

/**
 * 获取当前投注项赔率
 * @param $oddsList
 * @param $target
 * @param null $item
 */
function getOdds($oddsList, $target, $item = null) {
    if (stripos($target, ",") !== false) {
        list($target, $item) = explode(",", $target);
    }
    if ($item) {
        return $oddsList[$target][$item];
    } else {
        return $oddsList[$target];
    }
}

/**
 * 获取用户对帖子是否点赞
 * user_id
 * thread_id
 * */
function getUserThreadZan($user_id, $thread_id) {
    $status = cache("forum_thread_zan");
    if (isset($status[$user_id][$thread_id])) {
        return $status[$user_id][$thread_id];
    }
    return 0;
}

/**
 * 解析圈子表情
 * @param $face
 */
function parseForumFace($message) {
    $url = config("site_source_domain") . "common/plugins/face/face";
    return preg_replace('/\[em_([0-9]*)\]/', '<img src="' . $url . '/$1.png"  class="thread-face" border="0" />', $message);
}

//关键词替换
function keyword_replace($str) {
    $keywords = file_get_contents(CONF_PATH . "keyword.txt");
    $keyword_array = explode("\n", $keywords);
    foreach ($keyword_array as $k) {
        $str = str_replace(trim($k), "", $str);
        if (mb_strlen($str, "UTF-8") <= 0)
            break;
    }
    return $str;
}

/**
 * 计算支付奖金
 */
function getPayBonus($deposit, $money, $betTotal) {
    //擂主押金+总投注金额-当前投注项总投注金额-最大可投注金额（本金额已扣除了投注者本金）
    return $betTotal['bonus'];
}

function getWcgTeamName($play_id, $rule_id, $key = 0, $teamName = null, $odds_id = 0) {

    if (getRuleData(GAME_TYPE_WCG, $rule_id, 'type') == RULES_TYPE_OU) {
        return $key ? '小' : '大';
    } else {

        return getPlayRule($play_id, $rule_id, $key, $teamName, $odds_id);
    }
}

/**
 * 获取自定义玩法名称
 * @param $play_id
 * @param null $rule_id
 * @param int $key
 * @param null $teamName
 * @param int $odds_id
 * @return null
 */
function getPlayRule($play_id, $rule_id = null, $key = 0, $teamName = null, $odds_id = 0, $gameType = '') {
    if (!$play_id) {
        return $teamName;
    }
    $data = cache("play_all_rules_detail_{$play_id}");
    if (!$data) {
        //$data = (new \library\service\Play())->upRulesDetailCacheByPlayId($play_id);
        $data = (new \library\service\Play())->upAllRulesDetailCacheByPlayId($play_id);
        if (!$data) {
            return $teamName;
        }
    }
    if (!$rule_id) {
        return $data;
    }
    if (!isset($data[$rule_id])) {
        if (!$gameType) {
            if ($data && isset($data['game_id'])) {
                $game = getGame($data['game_id'], 'game_type');
            } else {
                $play = (new \library\service\Play())->getPlay($play_id);
                $gameType = $play['game_type'];
            }
        }
        $rule = getRuleData($gameType, $rule_id);
        if ($rule) {
            $explain = @json_decode($rule['explain'], true);
            if (in_array('home', $explain)) {
                return $teamName;
            } else {
                return $explain[$key];
            }
        }
    } else {
        if (isset($data[$rule_id])) {
            $data = $data[$rule_id];
        } else {
            $data = $data[$rule_id];
        }
        return $data['rules_explain'][$key];
    }
}

/**
 * 获取自定义玩法名称(包含前台用户添加的玩法)
 * @param $play_id
 * @param null $rule_id
 * @param int $key
 * @param null $teamName
 * @param int $odds_id
 * @return null
 */
function getAllPlayRule($play_id, $rule_id = null, $key = 0, $teamName = null, $odds_id = 0, $gameType = '') {
    if (!$play_id) {
        return $teamName;
    }
    $data = []; //cache("play_all_rules_detail_{$play_id}");
    if (!$data) {
        $data = (new \library\service\Play())->upAllRulesDetailCacheByPlayId($play_id);
        if (!$data) {
            return $teamName;
        }
    }
    if (!$rule_id) {
        return $data;
    }
    if (!isset($data[$rule_id])) {
        if (!$gameType) {
            if ($data && isset($data['game_id'])) {
                $game = getGame($data['game_id'], 'game_type');
            } else {
                $play = (new \library\service\Play())->getPlay($play_id);
                $gameType = $play['game_type'];
            }
        }
        $rule = getRuleData($gameType, $rule_id);
        if ($rule) {
            $explain = @json_decode($rule['explain'], true);
            if (in_array('home', $explain)) {
                return $teamName;
            } else {
                return $explain[$key];
            }
        }
    } else {
        if (isset($data[$rule_id][$odds_id])) {
            $data = $data[$rule_id][$odds_id];
        } else {
            $data = $data[$rule_id][0];
        }
        return $data['rules_explain'][$key];
    }
}

/**
 * 解析替换玩法、规则名称中的特殊字符
 * @param $ruleName
 * @param $play
 * @param array $replace
 * @return mixed
 */
function parseRuleName($ruleName, $teams = [], $replace = []) {
    if ($teams) {
        $ruleName = str_replace('#team_guest_name#', $teams[0]['name'], $ruleName);
        $ruleName = str_replace('#team_home_name#', $teams[1]['name'], $ruleName);
    }
    if ($replace) {
        foreach ($replace as $key => $val) {
            $ruleName = str_replace($key, $val, $ruleName);
        }
    }
    return $ruleName;
}

function parseRuleNameDefault($ruleName) {
    return parseRuleName($ruleName, '', [
        '#team_home_name#' => '主队',
        '#team_guest_name#' => '客队',
    ]);
}

/**
 * 格式化null输出
 * @param $value
 * @param string $type
 * @return array|int|string
 */
function formatNull($value, $type = 'string') {

    if (is_null($value) && $type == 'string') {
        $value = '';
    } elseif (is_null($value) && $type == 'number') {
        $value = 0;
    } elseif (is_null($value) && $type == 'array') {
        $value = [];
    }
    return $value;
}

function getItemValue($itemId, $data = []) {
    $indexRec = config("index_rec.{$itemId}");
    $ret = '';
    if ($indexRec) {
        foreach ($data as $val) {
            if (isset($indexRec[$val['type']]) && in_array($val['value'], $indexRec[$val['type']])) {
                $ret = "{$val['type']}_{$val['value']}";
                break;
            }
        }
    }
    return $ret;
}

function uniqidReal($prefix = '', $length = 13) {
    if (function_exists('random_bytes')) {
        $bytes = random_bytes(ceil($length / 2));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
    } else {
        throw new \think\Exception('No cryptographically secure random function available');
    }
    return $prefix . substr(bin2hex($bytes), 0, $length);
}

/**
 * 计算比赛时长
 * @param $match_time
 * @param $play_time
 * @return float|int|string
 */
function getMatchRunTime($match_time, $play_time) {
    if (!$match_time || trim($match_time) == '' || is_null($match_time)) {
        $match_time = 0;
        $long = time() - $play_time;
        $min = floor($long / 60);
        if ($min <= 45) {
            $match_time = "{$min}'";
        } elseif ($min <= 60) {
            $match_time = "45+'";
        } elseif ($min <= 105) {
            $match_time = (($min - 15) . "'");
        } else {
            $match_time = "90+'";
        }
        if ($match_time < 0) {
            $match_time = 0;
        }
    }
    return $match_time;
}

/**
 * 根据IP返回所在区域信息
 * @param $ip
 */
function getLocationByIp($ip) {
    if (!trim($ip)) {
        return '-';
    }
    return \org\Convertip::get($ip);
}

//数据列表排序组装
function dataSort($text, $opt, $url, $param, $defaultSort = null) {
    $sortValue = isset($_GET['sort_value']) ? $_GET['sort_value'] : '';
    $sortOpt = input('sort_opt');
    $sortValue = strtolower($sortValue);
    $className = '';
    $str = '';
    if (isset($param['page'])) {
        unset($param['page']);
    }
    if (isset($param['sort_opt'])) {
        unset($param['sort_opt']);
    }
    if (isset($param['sort_value'])) {
        unset($param['sort_value']);
    }
    $param['sort_opt'] = $opt;
    if ($sortOpt == $opt) {
        if ($sortValue == 'asc') {
            $str = 'desc';
            $className = 'fa-arrow-up';
        } elseif ($sortValue == 'desc') {
            $str = 'asc';
            $className = 'fa-arrow-down';
        }
        if ($str) {
            $param['sort_value'] = $str;
        }
    }

    if (!$className && $defaultSort) {
        if ($defaultSort == 'asc') {
            $str = 'desc';
            $className = 'fa-arrow-up';
        } elseif ($defaultSort == 'desc') {
            $str = 'asc';
            $className = 'fa-arrow-down';
        }
        if ($str) {
            $param['sort_value'] = $str;
        }
    }

    $param = http_build_query($param);
    if (strpos($url, '?') !== false) {
        $url .= "&{$param}";
    } else {
        $url .= "?{$param}";
    }

    return '<a href="' . $url . '" title="按' . $text . '排序" class="text-primary">' . $text . '<i class="fa ' . $className . '"></i></a>';
}

function getPlatformById($platformId) {
    switch ($platformId) {
        case PLATFORM_IOS:
            return 'IOS';
        case PLATFORM_ANDROID:
            return 'Android';
        case PLATFORM_H5:
            return 'H5';
        default:
            return '未知';
    }
}

/**
 * 判断是亚盘还是欧盘
 * @param $ruleType
 */
function chkAsianRule($itemType, $ruleType) {
    static $asianRules;
    if (!$asianRules) {
        $asianRules = (new \library\service\Rule())->asianRules;
    }
    if (!isset($asianRules[$itemType])) {
        return '';
    }
    if (in_array($ruleType, $asianRules[$itemType])) {
        return true;
    }
    return false;
}

/**
 * 获取比赛预测地址
 */
function getPlayDopeUrl($token, $playId) {
    $domain = config("share_domain"); //(new Oauth())->getDomain($this->token);
    return $domain . "share/play_dope.html?token={$token}&play_id={$playId}";
}

function formatTime($time) {
    $h = floor($time / 3600);
    $m = floor(($time % 3600) / 60);
    $s = $time - $h * 3600 - $m * 60;
    $result = '';
    if ($h) {
        $result .= $h . "小时";
    }
    if ($m) {
        $result .= $m . "分";
    }
    if ($s) {
        $result .= $s . "秒";
    }
    return $result;
}

function xmlToArray($xml) {
    try {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    } catch (\Exception $e) {
        return false;
    }
}

function arrayToXml($arr) {
    $xml = "<xml>";
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}

function arraySort($a, $b) {
    if ($a == $b)
        return 0;
    return ($a < $b) ? -1 : 1;
}

function getDeviceType($type) {
    switch ($type) {
        case 1 :
            return 'ios';
        case 2 :
            return 'android';
        case 3 :
            return 'h5';
        case 4 :
            return 'pc';
        default :
            return 'h5';
    }
    return 'h5';
}

function getActivityClassifyName($classify) {
    switch ($classify) {
        case ACTIVITY_TYPE_RECHARGE:
            return '充值活动';
        default:
            return '未知活动';
    }
}

function getApiDomain($http = true) {
    $systemConf = config("system");
    $territory = @json_decode($systemConf['territory'], true);
    $outside = @json_decode($systemConf['outside'], true);
    $domain = [];
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (!$ref) {
        $domain = $territory;
    } else {
        $info = parse_url($ref);
        $domain_ref = explode(PHP_EOL, $territory['domain_ref']);
        foreach ($domain_ref as $key => $val) {
            $domain_ref[$key] = trim($val);
        }
        if (in_array(strtolower($info['host']), $domain_ref)) {
            $domain = $territory;
        } else {
            $domain = $outside;
        }
    }

    $domainApi = explode(PHP_EOL, $domain['domain_api']);
    $domain = $domainApi[0];
    $domain = trim($domain);
    if (stripos($domain, '|') !== false) {
        $domain = explode("|", $domain);
        $domain = $domain[0];
    }

    if ($http) {
        //https://api.tczssh.com/|https://api.jrwtop.com/|https://api.sdxc56.com/
        if (substr($domain, 0, 7) == 'http://' || substr($domain, 0, 8) == 'https://') {
            return rtrim($domain, "/") . "/";
        }
        $domain = "http://{$domain}";
        return rtrim($domain, "/") . "/";
    } else {
        if (substr($domain, 0, 7) == 'http://') {
            $domain = substr($domain, 7);
        } elseif (substr($domain, 0, 8) == 'https://') {
            $domain = substr($domain, 8);
        }
    }
    return rtrim($domain, "/");
}

function getPayType($type) {
    switch ($type) {
        case 'alipay':
            return '支付宝';
        case 'weixin':
        case 'wxpay':
            return '微信';
        case 'qq':
        case 'qqpay':
            return 'QQ钱包';
        case 'jd':
        case 'jdpay':
            return '京东';
        case 'un':
        case 'unionpay':
            return '银行卡';
    }
    return '';
}
