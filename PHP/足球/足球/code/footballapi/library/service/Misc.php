<?php

namespace library\service;

use Endroid\QrCode\QrCode;
use think\Cache;
use think\Exception;
use think\Db;

class Misc
{


    public function setCacheTotal($key, $val = 1)
    {
        $fileName = "today_{$key}_total";
        $totals = cache($fileName);
        if (!$totals || !is_array($totals)) {
            $totals = [];
        }
        $index = date("Ymd");
        $val = intval($val);
        $total = 0;
        if (isset($totals[$index])) {
            $total = intval($totals[$index]);
        }
        $total += $val;
        $totals[$index] = $total;
        cache($fileName, $totals);
        return $total;
    }

    public function getOnline()
    {
        $where = array(
            'has_online' => 1
        );
        $count = DB::name('user')->where($where)->count();
        return $count;
    }

    /**
     * 获取指定日期的注册用户统计
     * @param $index
     * @return mixed
     */
    public function getCacheTotal($key, $index = null)
    {
        $index = intval($index);
        $fileName = "today_{$key}_total";
        $totals = cache($fileName);
        if ($index) {
            return isset($totals[$index]) ? $totals[$index] : 0;
        }
        return $totals;
    }

    public function toXls($title, $header, $lists)
    {
        $title = $title . date("YmdHis");
        set_time_limit(0);
        header('Expires: 0');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title . '.xls"');
        $excel = new \PHPExcel();
        $excel->getProperties()->setTitle($title)->setCreator("51Bet");
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->setTitle($title);
        $row = 1;
        if ($header) {
            $i = 0;
            foreach ($header as $val) {
                $excel->getActiveSheet()->setCellValueByColumnAndRow($i, $row, $val);
                $i++;
            }
            $row++;
        }
        if ($lists) {
            foreach ($lists as $list) {
                $j = 0;
                if ($header) {
                    foreach ($header as $key => $val) {
                        $v = array_key_exists($key, $list) ? $list[$key] : '';
                        $excel->getActiveSheet()->setCellValueByColumnAndRow($j, $row, $v);
                        $j++;
                    }
                } else {
                    foreach ($list as $key => $val) {
                        $excel->getActiveSheet()->setCellValueByColumnAndRow($j, $row, $val);
                        $j++;
                    }
                }
                $row++;
            }

        }
        $excel->createSheet();
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
        exit();
        return;
    }

    /**
     * 获取擂主佣金
     * @param null $item_id
     * @param null $playId
     * @param null $rule_id
     */
    public function getMakerBrokerage($item_id = null, $playId = null, $rule_id = null)
    {
        $Brokerage = cache('system.sys_maker_brok');
        return floatval($Brokerage) / 100;
    }

    /**
     * 获取玩家佣金
     * @param null $item_id
     * @param null $playId
     * @param null $rule_id
     */
    public function getPlayBrokerage($item_id = null, $playId = null, $rule_id = null)
    {
        $Brokerage = cache('system.sys_player_brok');
        return floatval($Brokerage) / 100;
    }

    /**
     * 生成二维码图片
     * @param $arenaId
     */
    public function getQrCode($text, $label = '', $hasBase64 = false)
    {
        $dir = rtrim(config("assets_path"), "/") . "/attach/";
        $font = rtrim(config("assets_path"), "/") . "/fonts/msyh.ttf";
        $imgDir = "qrcode/";
        $mark = md5($text);
        for ($i = 0; $i < 3; $i++) {
            $imgDir .= substr($mark, $i * 2, 2) . '/';
        }
        $qrFile = substr($mark, 6) . ".png";
        if (!is_file($dir . $imgDir . $qrFile)) {
            if (!is_dir($dir . $imgDir)) {
                mkdir($dir . $imgDir, 0777, true);
            }
            $qrCode = new QrCode();
            $qrCode->setText($text)
                ->setSize(235)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setImageType(QrCode::IMAGE_TYPE_PNG);
            if ($label) {
                $qrCode->setLabel($label)->setLabelFontPath($font)->setLabelFontSize(14);
            }
            if (!$hasBase64) {
                $qrCode->save($dir . $imgDir . $qrFile);
            } else {
                return base64_encode($qrCode->get());
            }
        }
        return $imgDir . $qrFile;
    }

    /**
     * 着陆页生成二维码
     */
    public function landingPageQrCode($url)
    {
        $dir = rtrim(config("assets_path"), "/") . "/attach/";
        $font = rtrim(config("assets_path"), "/") . "/fonts/msyh.ttf";
        $imgDir = "qrcode/";
        $mark = md5($url);
        for ($i = 0; $i < 3; $i++) {
            $imgDir .= substr($mark, $i * 2, 2) . '/';
        }
        $qrFile = substr($mark, 6) . ".png";
        if (!is_file($dir . $imgDir . $qrFile)) {
            if (!is_dir($dir . $imgDir)) {
                mkdir($dir . $imgDir, 0777, true);
            }
            $qrCode = new QrCode();
            $qrCode->setText($url)
                ->setSize(235)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setImageType(QrCode::IMAGE_TYPE_PNG);
            $qrCode->save($dir . $imgDir . $qrFile);
        }
        return $imgDir . $qrFile;
    }

    /**
     * 生成二维码
     */
    public function qrCode($url)
    {
        $dir = rtrim(config("assets_path"), "/") . "/attach/";
        $font = rtrim(config("assets_path"), "/") . "/fonts/msyh.ttf";
        $imgDir = "qrcode/";
        $mark = md5($url);
        for ($i = 0; $i < 3; $i++) {
            $imgDir .= substr($mark, $i * 2, 2) . '/';
        }
        $qrFile = substr($mark, 6) . ".png";
        if (!is_file($dir . $imgDir . $qrFile)) {
            if (!is_dir($dir . $imgDir)) {
                mkdir($dir . $imgDir, 0777, true);
            }
            $qrCode = new QrCode();
            $qrCode->setText($url)
                ->setSize(235)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setImageType(QrCode::IMAGE_TYPE_PNG);
            $qrCode->save($dir . $imgDir . $qrFile);
        }

        return $imgDir . $qrFile;
    }

    public static function writeLog($message, $path = '')
    {
        $fileSize = 2097152;
        $path = $path ? $path : LOG_PATH . "test_log/";
        $now = date('c');
        $destination = $path . date('Ym') . DS . date('d') . '.log';
        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($fileSize) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }
        $depr = "---------------------------------------------------------------\r\n";
        $message = is_array($message) ? var_export($message, true) : $message;
        return error_log("[{$now}] {$message}\r\n{$depr}", 3, $destination);
    }

}