<?php
/**
 * +----------------------------------------------------------------------
 * | Author:  jiayin <caojiayin1984@gmail.com>
 * +----------------------------------------------------------------------
 * | 2015/04/29 10:36
 * +----------------------------------------------------------------------
 * | thumb.php
 * +----------------------------------------------------------------------
 * | 生成缓存文件
 * +----------------------------------------------------------------------
 **/
error_reporting(E_ALL);
define('STATIC_PATH',dirname(__FILE__).'/');
define('WATER_THUMB_ON',false);
define('WATER_FILE',false);

class Thumb{
    private $imageNotFound = 'common/images/default.png';
    private $mimeList = array('image/png' => 'png','image/jpeg' => 'jpg','image/gif' => 'gif');
    private $imageType = 'jpg';
    private $width = 0; //接收图片裁剪宽度
    private $height = 0;//接收图片裁剪高度
    private $cutWidth = 0;//真实图片裁剪宽度
    private $cutHeight = 0;//真实图片裁剪高度
    private $sourceWidth = 0; //图片真实宽度
    private $sourceHeight = 0;//图片真实高度
    private $srcX = 0;//裁剪图片位移X
    private $srcY = 0;//裁剪图片位移Y
    private $sourceImage = null;//图片信息
    private $imageInfo = null;//图片信息
    private $imageSize = null;//图片信息

    private $thumbName = '';
    private $thumbType = 2;
    private $filename = '';

    public function __construct($filename){
        $this->thumbName = STATIC_PATH.ltrim($filename,"/");
        $this->imageInfo = pathinfo($filename);
        if(is_string($this->imageInfo)){
            $this->display(STATIC_PATH.$this->imageNotFound);
        }
        preg_match("/([a-zA-Z0-9]+)_([a-zA-Z0-9]+)_([0-9])_([0-9]+)_([0-9]+)/i",$this->imageInfo['filename'],$matchs);
        if(!$matchs){
            //$this->display(STATIC_PATH.$this->imageNotFound);
            $this->error('Invalid image');
        }
        list($a,$filename,$code,$type,$width,$height) = $matchs;
        $this->_verifyImages($filename,$code,$type,$width,$height);
        $this->width = $width;
        $this->height = $height;
        $this->thumbType = $type > 4 ? 1 : $type;
        $this->filename = $filename;
        $this->sourceImage = STATIC_PATH.ltrim($this->imageInfo['dirname'],"/").'/'.$filename.'.'.$this->imageInfo['extension'];
        if(!is_file($this->sourceImage)){
            $this->sourceImage = STATIC_PATH.$this->imageNotFound;
           // $this->thumbName = STATIC_PATH.$this->imageNotFound;
        }
        $this->imageSize = $this->getImageInfo($this->sourceImage);
        $this->imageType = $this->mimeList[$this->imageSize['mime']] ? strtolower($this->mimeList[$this->imageSize['mime']]) : strtolower($this->imageInfo['extension']);
        $this->_init();
    }

    public function reSize($quality = 95){
        if(class_exists('Imagick')){
            $this->reImageick($quality);
        }else{
            $this->reDefSize($quality);
        }
    }

    public function reImageick($quality = 95){

    }

    public function reDefSize($quality = 95){
        $srcX1 = $this->srcX;
        $srcY1 = $this->srcY;
        if($this->thumbType == 2){$this->srcX = $this->srcY = 0;}
        @ini_set("memory_limit", "600M");
        $createFun = 'imagecreatefrom' . ($this->imageType == 'jpg' ? 'jpeg' : $this->imageType);
        $srcImg = $createFun($this->sourceImage);
        $thumbImg = $this->createImage($srcImg,$this->imageType,$this->srcX,$this->srcY,$this->cutWidth,$this->cutHeight,$this->sourceWidth,$this->sourceHeight);

        $thumbImg1 = null;
        if($this->thumbType == 2){ //需要再次居中裁剪
            //创建缩略图
            if ($this->imageType != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg1 = imagecreatetruecolor($this->width, $this->height);
            else
                $thumbImg1 = imagecreate($this->width, $this->height);
            if ('gif' == $this->imageType || 'png' == $this->imageType) {
                imagealphablending($thumbImg1, false);//取消默认的混色模式
                imagesavealpha($thumbImg1,true);//设定保存完整的 alpha 通道信息
                $background_color = imagecolorallocate($thumbImg1, 255, 255, 255); //  指派一个绿色
                imagefilledrectangle($thumbImg1,0,0,$this->width,$this->height,$background_color);
                imagecolortransparent($thumbImg1, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }
            imagecopy($thumbImg1, $thumbImg, 0, 0, $srcX1, $srcY1, $this->cutWidth, $this->cutHeight);
            $thumbImg = $thumbImg1;
            $thumbImg1 = null;
        }
        $thumbImg = $this->_water($thumbImg);
        // 生成图片
        $imageFun = 'image' . ($this->imageType == 'jpg' ? 'jpeg' : $this->imageType);
        header('Content-Type: image/'.$this->imageType);
        if($this->imageType == 'jpg' || $this->imageType == 'jpeg'){
            @$imageFun($thumbImg, $this->thumbName,$quality);
        }else{
            @$imageFun($thumbImg, $this->thumbName);
        }
        //$imageFun($thumbImg, $this->thumbName);
        $imageFun($thumbImg);
        imagedestroy($thumbImg);
        imagedestroy($srcImg);
        @imagedestroy($thumbImg1);
    }
    private function createImage($srcImg,$type,$srcX,$srcY,$cutWidth,$cutHeight,$srcWidth,$srcHeight){
        //创建缩略图
        imagesavealpha($srcImg,true);
        if ($type != 'gif' && function_exists('imagecreatetruecolor')){
            $thumbImg = imagecreatetruecolor($cutWidth, $cutHeight);
        }
        else
            $thumbImg = imagecreate($cutWidth, $cutHeight);

        if ('gif' == $type || 'png' == $type) {
            imagealphablending($thumbImg, false);//取消默认的混色模式
            imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
            $background_color = imagecolorallocate($thumbImg, 255, 255, 255); //  指派一个绿色
            imagefilledrectangle($thumbImg,0,0,$cutWidth,$cutHeight,$background_color);
            imagecolortransparent($thumbImg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            //imagefill($thumbImg,0,0,$background_color);
        }
        // 复制图片
        if (function_exists("ImageCopyResampled")){
            imagecopyresampled($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $cutWidth, $cutHeight, $srcWidth, $srcHeight);
        }else{
            imagecopyresized($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $cutWidth, $cutHeight, $srcWidth, $srcHeight);
        }
        // 对jpeg图形设置隔行扫描
        if ('jpg' == $type || 'jpeg' == $type)
            imageinterlace($thumbImg, 1);
        return $thumbImg;
    }

    private function _water($img){
        if(!WATER_THUMB_ON || !WATER_FILE){return $img;}
        if(!is_file(WATER_FILE)){return $img;}
        $info = $this->getImageInfo(WATER_FILE);
        $type = $info['type'];
        $createFun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' :$type);
        $water = $createFun(WATER_FILE);
        $img = imagecreatetruecolor(200, 200);
        imagesavealpha($img,true);
        $color=imagecolorallocate($img,255,255,255);

        imagecolortransparent($img,$color);
        imagefill($img,0,0,$color);

        $textcolor=imagecolorallocate($img,0,0,0);
        imagettftext($img, 50, 0, 10, 100, $textcolor, "simsun.ttc", "测试");
        header('Content-Type: image/png');
        imagepng($img);exit;
        //imagealphablending($water, false);//取消默认的混色模式
        //imagesavealpha($water,true);//设定保存完整的 alpha 通道信息
        //$background_color = imagecolorallocate($water, 0, 0, 0); //  指派一个绿色
        //imagefilledrectangle($water,0,0,$info['width'],$info['height'],$background_color);
        // imagecolortransparent($water, $background_color);  //  设置为透明色
        //imagecopy($dst_im,$src_im,$dst_info[0]-$src_info[0]-10,$dst_info[1]-$src_info[1]-10,0,0,$src_info[0],$src_info[1]);
        //imagecopymerge($img, $water, 0, 0, 150, 150, $this->cutWidth, $this->cutHeight,50);
        $imageFun = 'image' . ($this->imageType == 'jpg' ? 'jpeg' : $this->imageType);
        imagecopymerge($img, $im, 10, 10, 0, 0,$this->width, $this->height,80);
        header('Content-Type: image/'.$this->imageType);
        $imageFun($img);exit;
        return $img;
    }

    private function _init(){
        if($this->width == 0 || $this->height == 0){
            $this->thumbType = 3;
        }
        if($this->thumbType == 1){
            //进行等比缩放，不裁剪
            $this->srcX = 0;
            $this->srcY = 0;
            $crown = $this->sourceWidth / $this->sourceHeight;    //原图宽高比
            $this->cutWidth = $this->height * $crown;
            $this->cutHeight = $this->width / $crown;
        }elseif($this->thumbType == 2){
            //进行等比缩放，居中裁剪
            $this->srcX = 0;
            $this->srcY = 0;
            $scale = $this->width / $this->height;// 计算缩放比例
            $crown = $this->sourceWidth / $this->sourceHeight;    //原图宽高比
            if($scale / $crown >= 1){
                $this->cutWidth = $this->width;
                $this->cutHeight = $this->width / $crown;
                $this->srcY = ($this->cutHeight-$this->height) / 2;
            }else{
                $this->cutWidth = $this->height * $crown;
                $this->cutHeight = $this->height;
                $this->srcX = ($this->cutWidth-$this->width) / 2;
            }
        }elseif($this->thumbType == 3){
            //限定缩略图的宽最多为<$maxWidth>，高最多为<$maxHeight>，进行等比缩放，不裁剪
            $this->srcX = 0;
            $this->srcY = 0;
            $crown = $this->sourceWidth / $this->sourceHeight;    //原图宽高比

            if($this->sourceWidth > $this->sourceHeight){
                $this->cutHeight = $this->height;
                $this->cutWidth = $this->height * $crown;
            }else{

                $this->cutWidth = $this->width;
                $this->cutHeight = $this->width / $crown;
            }
        }elseif($this->thumbType == 4){
            //进行缩放，不裁剪,会拉伸
            $this->cutWidth = $this->width;
            $this->cutHeight = $this->height;
        }
    }

    private function _verifyImages($filename,$code,$type,$width,$height){
        $_code = substr(md5($type.md5($filename).md5($width.$height)),8,10);
        //$_code = substr(MD5(MD5($filename.$type).MD5(MD5($width).$height)),8,10);
        if($_code != $code){
            //$this->display(STATIC_PATH.$this->imageNotFound);
            $this->error('Invalid image');
        }
    }

    /**
     * 获取图片信息
     * @param $img
     * @return array|bool
     */
    private function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            $this->sourceWidth = $imageInfo[0];
            $this->sourceHeight = $imageInfo[1];
            return $info;
        } else {
            return false;
        }
    }
    public function retDefault(){
        $imageFile = STATIC_PATH.$this->imageNotFound;
        header('Content-Type: image/'.$this->imageType);
    }

    /**
     * 输出图片
     * @param $image
     */
    private function display($image){
        header('Content-Type: image/jpg');
        echo file_get_contents($image);
        exit;
    }
    private function error($message){
        exit('Error : '.$message);
    }
}
$thumb = new Thumb($_GET['url']);
$thumb->reSize();