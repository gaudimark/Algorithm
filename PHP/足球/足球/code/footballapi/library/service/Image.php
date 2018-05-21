<?php
/**
 * 生成图片
 * Date: 2017/4/28
 * Time: 10:30
 */
namespace library\service;
use think\Db;

class Image{

    private $im = null;
    /**
     * 征信局用户投注生成图片
     * @param $arenaId
     */
    public function bettingByCreditArena($arenaId){
        $arenaSvr = new Arena();
        $arena  = $arenaSvr->getCacheArenaById($arenaId);// || (isset($arena['credit_bill_pic']) && $arena['credit_bill_pic'])
        if(!$arena || $arena['classify'] != ARENA_CLASSIFY_CREDIT){return false;}
        $bgImg = rtrim(config("assets_path"),"/")."/common/images/betting_table.png";
        if(!is_file($bgImg)){return false;}
        set_time_limit(0);
        $page = 1;
        $limit = 12;
        $tableHeight = 70;
        $fontSize = 12;
        $color = [25,25,25,10];
        $font = rtrim(config("assets_path"),"/").'/fonts/msyh.ttf';
        $ruleSvr = (new Rule())->factory($arena['game_type']);
        $teams = (new Play())->getTeams($arena['play_id']);
        $dir = rtrim(config("assets_path"),"/")."/attach/";
        $imgDir = "arena/";
        $mark = $arena['mark'];
        for($i = 0;$i < 3; $i++){
            $imgDir .= substr($mark,$i*2,2).'/';
        }
        if (!is_dir($dir.$imgDir)) {
            mkdir($dir.$imgDir,0777,true);
        }
        $billImg = []; //征信局账单图片
        while (true){
            $saveImageFile = $imgDir."{$arenaId}_credit_{$page}.png";
            $offset = ($page - 1) * $limit;
            $lists = Db::name('arena_credit')->where(['arena_id' => $arenaId])->limit($offset,$limit)->select();
            if(!$lists){break;}
            $this->im = imagecreatefrompng($bgImg);
            $line = 0;
            foreach($lists as $key => $val){
                //姓名
                $nameX =  11;
                $nameY = ($line * $tableHeight) + 38;
                $this->text($val['name'],$font,$fontSize,$color,[$nameX,$nameY]);
                //手机
                $mobileX = 11;
                $mobileY = ($line * $tableHeight) + 60;
                $this->text($val['mobile'],$font,10,$color,[$mobileX,$mobileY]);
                //授信额度
                $goldX = 182;
                $goldY = ($line * $tableHeight) + 53;
                $this->text(numberFormat($val['gold'],2,true),$font,$fontSize,$color,[$goldX,$goldY]);
                //投注项
                if($val['user_id']){
                    $total = Db::name('arena_bet_detail')->where(['arena_id' => $arenaId, 'user_id' => $val['user_id']])->count();
                    if($total){
                        $bet = Db::name('arena_bet_detail')->where(['arena_id' => $arenaId, 'user_id' => $val['user_id']])->find();
                        $betTarget = $ruleSvr->getBetTargetText($arena['rules_type'],$arena['play_id'],$teams,$bet['target'],$bet['item']);
                        $goldX = 337;
                        $goldY = ($line * $tableHeight) + 35;
                        $text = $betTarget['target'];
                        if($betTarget['item']){
                            $text .= "({$betTarget['item']})";
                        }
                        $this->text($text,$font,$fontSize,$color,[$goldX,$goldY]);
                        $text = "赔率 {$bet['odds']},   投注 {$bet['money']}";
                        $goldY = ($line * $tableHeight) + 55;
                        $this->text($text,$font,10,[25,25,25,50],[$goldX,$goldY]);
                        if($total > 1){
                            $text = "等{$total}项投注";
                            $goldY = ($line * $tableHeight) + 71;
                            $this->text($text,$font,10,[25,25,25,50],[$goldX,$goldY]);
                        }
                    }
                }
                //结果
                if($val['win'] > 0){
                    $text = "赢 {$val['win']}";
                }elseif($val['win'] < 0){
                    $text = "{$val['win']}";
                }else{
                    $text = "-";
                }
                $goldX = 663;
                $goldY = ($line * $tableHeight) + 50;
                $this->text($text,$font,$fontSize,$color,[$goldX,$goldY]);
                $line++;
            }
            imagepng($this->im,$dir.$saveImageFile);
            imagedestroy($this->im);
            $billImg[] = $saveImageFile;
            $page++;
        }
        Db::name('arena')->where(['id' => $arenaId])->update([
            'credit_bill_pic' => @json_encode($billImg),
        ]);
        return $billImg;
    }

    /**
     * 图像添加文字
     *
     * @param  string  $text   添加的文字
     * @param  string  $font   字体路径
     * @param  integer $size   字号
     * @param  string  $color  文字颜色
     * @param int      $locate 文字写入位置
     * @param  integer $offset 文字相对当前位置的偏移量
     * @param  integer $angle  文字倾斜角度
     *
     * @return $this
     * @throws Exception
     */
    public function text($text, $font, $size, $color = '#00000000',$locate = [0,0], $offset = 0, $angle = 0) {
        //资源检测
        if (empty($this->im)) {
            throw new Exception('没有可以被写入文字的图像资源');
        }

        if (!is_file($font)) {
            throw new Exception("不存在的字体文件：{$font}");
        }

        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);

        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;

        /* 设定文字位置 */
        if (is_array($locate)) {
            list($posx, $posy) = $locate;
            $x += $posx;
            $y += $posy;
        } else {
            throw new Exception('不支持的文字位置类型');
        }

        /* 设置偏移量 */
        if (is_array($offset)) {
            $offset        = array_map('intval', $offset);
            list($ox, $oy) = $offset;
        } else {
            $offset = intval($offset);
            $ox     = $oy     = $offset;
        }

        /* 写入文字 */
        $col = imagecolorallocatealpha($this->im, $color[0], $color[1], $color[2], $color[3]);
        imagettftext($this->im, $size, $angle, $x + $ox, $y + $oy, $col, $font, $text);
        return $this;
    }
}