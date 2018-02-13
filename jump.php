<?php

/**
 * @desc: Created by PhpStorm.
 * @author: icehill
 * @date: 2018/1/22 10:59
 *
 */
class jump
{
    /** @var null 配置文件 */
    public $config = null;

    /** @var null 屏幕大小 */
    public $screenSize = null;

    /** @var null adb程序路径 */
    public $adbPath = null;

    /** @var null 缓存截图名称 */
    public $tempImageName = null;

    /** @var  棋子开始位置 */
    public $swipe_x1, $swipe_y1, $swipe_x2, $swipe_y2 = null;

    /** @var  图片属性 */
    public $imageWidth, $imageHeight = null;

    /** @var null 图片保存目录 */
    public $debugImageFilePath = null;

    public function __construct()
    {
        $this->getConfig();
    }

    /**
     * @desc: 单次执行跳一跳
     * @author: icehill
     */
    public function jumpOnce()
    {
        $this->getScreenShot();
        $imagePath = "./$this->tempImageName";
        $piecePosition = $this->findPiece($imagePath);
        $piece_x = $piecePosition['piece_x'];
        $piece_y = $piecePosition['piece_y'];
        $boardPosition = $this->findBoard($imagePath, $piece_x, $piece_y);
        $board_x = $boardPosition['board_x'];
        $board_y = $boardPosition['board_y'];
        $this->showMessage(4, array('message' => $piece_x . '-' . $piece_y . '-' . $board_x . '-' . $board_y));
        $this->setButtonPosition($imagePath);
        $distance = sqrt(pow(abs($board_x - $piece_x), 2) + pow(abs($board_y - $piece_y), 2));
        $this->jump($distance);
    }

    /**
     * @desc: 连续跳一跳
     * @author: icehill
     */
    public function autoJump()
    {
        $this->showDeviceInfo();
        $count = 1;
        while (true) {
            $this->showMessage(1, array('count' => $count));
            $count++;
            $this->getScreenShot();
            if (IS_DEBUG) {
                $this->saveScreen(1, $count);
            }
            $imagePath = "./$this->tempImageName";
            $piecePosition = $this->findPiece($imagePath);
            $piece_x = $piecePosition['piece_x'];
            $piece_y = $piecePosition['piece_y'];
            $boardPosition = $this->findBoard($imagePath, $piece_x, $piece_y);
            $board_x = $boardPosition['board_x'];
            $board_y = $boardPosition['board_y'];
            $this->showMessage(4, array('message' => 'piece_x:' . $piece_x . '-' . 'piece_y:' . $piece_y . '-' . 'board_x:' . $board_x . '-' . 'board_y:' . $board_y));
            $this->setButtonPosition($imagePath);
            $distance = sqrt(pow(abs($board_x - $piece_x), 2) + pow(abs($board_y - $piece_y), 2));
            $this->jump($distance);
            //暂停多少毫秒
            $sleepSeconds = mt_rand(1500000, 2000000);
            usleep($sleepSeconds);
            if ($count % 9 == 0) {
                $randSeconds = rand(5, 25);
                $this->showMessage(4, array('message' => "打了 $count 下,休息 $randSeconds 秒"));
                sleep($randSeconds);
            }

        }
    }

    /**
     * @desc: 跳跃一定距离
     * @author: icehill
     * @param $distance
     * @return int|mixed
     */
    public function jump($distance)
    {
        $pressTime = $distance * $this->config['press_coefficient'];
        $pressTime = max($pressTime, 200);   # 设置 200ms 是最小的按压时间
        $pressTime = intval($pressTime);
        exec("$this->adbPath shell input swipe $this->swipe_x1 $this->swipe_y1 $this->swipe_x2 $this->swipe_y2 $pressTime", $out1);
        return $pressTime;
    }

    /**
     * @desc: 随机生成按压位置
     * @author: icehill
     */
    public function setButtonPosition($imagePath)
    {
        if ($this->imageHeight && $this->imageWidth) {
            $width = $this->imageWidth;
            $height = $this->imageHeight;
        } else {
            $sizeArr = getimagesize($imagePath);
            $width = $sizeArr[0];
            $height = $sizeArr[1];
        }

        $x1 = intval($width / 2);
        $y1 = intval(1584 * ($height / 1920.0));
        $x2 = rand($x1 - 50, $x1 + 50);
        $y2 = rand($y1 - 50, $y1 + 50);
        $this->swipe_x1 = rand($x1 - 50, $x1 + 50);
        $this->swipe_y1 = rand($y1 - 50, $y1 + 50);;
        $this->swipe_x2 = $x2;
        $this->swipe_y2 = $y2;
    }

    /**
     * @desc: 获取手机截图
     * @author: icehill
     * @param int $type
     */
    public function getScreenShot($type = 1)
    {
        switch ($type) {
            //方法一：使用adb截图保存并获取图片
            case 1:
                //安全模式下需要写全路径
                exec("$this->adbPath shell screencap -p /sdcard/$this->tempImageName", $out1);
                exec("$this->adbPath pull sdcard/$this->tempImageName", $out2);
                $this->showMessage(2, array('exec_out' => $out2));
                break;
            default:
                $this->showMessage(3, array('error_info' => '请使用正确的获取截图方法'));
                exit;
        }
    }

    /**
     * @desc: 获取屏幕大小(width x height)
     * @author: icehill
     */
    public function getScreenSize()
    {
        $str = [];
        $matches = [];
        //安全模式下需要写全路径
        exec("$this->adbPath shell wm size", $str);
        if ($str && preg_match_all('/\d+/', $str[0], $matches) == 2) {
            return $matches[0][0] . 'x' . $matches[0][1];
        } else {
            return '1080x1920';
        }
    }

    /**
     * @desc: 保存图片
     * @author: icehill
     * @param int $round
     * @param int $count
     */
    public function saveScreen($round = 1, $count = 0)
    {
        $img = file_get_contents("./$this->tempImageName");
        if ($count) {
            $imageName = $round . '_' . $count . '.png';
        }
        $dir = $this->debugImageFilePath . date('Y-m-d') . DS;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . $imageName, $img);
    }

    /**
     * @desc: 打印设备基本信息
     * @author: icehill
     */
    public function showDeviceInfo()
    {
        exec("$this->adbPath shell  wm size", $screen);
        exec("$this->adbPath shell getprop ro.product.model", $deviceType);
        exec("$this->adbPath shell  wm density", $density);
        $messageArray[] = 'Screen: ' . $screen[0];
        $messageArray[] = 'DeviceType: ' . $deviceType[0];
        $messageArray[] = 'Density: ' . $density[0];
        $this->showMessage(5, array('message_array' => $messageArray));
    }

    /**
     * @desc: 获取棋子位置
     * @author: icehill
     */
    public function findPiece($imagePath)
    {
        $sizeArr = getimagesize($imagePath);
        $image = imagecreatefrompng($imagePath);
        $width = $sizeArr[0];
        $height = $sizeArr[1];
        $this->imageWidth = $width;
        $this->imageHeight = $height;
        $scanStartY = 0;#扫描的起始 y 坐标
        $scanXBorder = $width / 8;
        $piece_x_sum = 0;
        $piece_x_c = 0;
        $piece_y_max = 0;
//        $piece_base_height_1_2 = 25;   //二分之一的棋子底座高度，可能要调节
//        $this->showMessage(4, array('message' => 'width:' . $width . ' height:' . $height));
        for ($h = $height / 3; $h < $height * 2 / 3; $h = $h + 50) {
            $lastPixel = $this->getImagePixel($image, 0, $h);
            for ($w = 1; $w < $width; $w++) {
                $pixel = $this->getImagePixel($image, $w, $h);
                //不是纯色的线，则记录 scan_start_y 的值，准备跳出循环
                if (($pixel[0] != $lastPixel[0]) || ($pixel[1] != $lastPixel[1]) || ($pixel[2] != $lastPixel[2])) {
                    $scanStartY = $h - 50;
                    break;
                }
            }
            if ($scanStartY) {
                break;
            }
        }
//        $this->showMessage(4, array('message' => 'scan_start_y:' . $scanStartY));
        for ($h = $scanStartY; $h < $height * 2 / 3; $h++) {
            for ($w = $scanXBorder; $w < $width - $scanXBorder; $w++) {
                $pixel = $this->getImagePixel($image, $w, $h);
                if ((50 < $pixel[0] && $pixel[0] < 60) && (53 < $pixel[1] && $pixel[1] < 63) && (95 < $pixel[2] && $pixel[2] < 110)) {
                    $piece_x_sum += $w;
                    $piece_x_c += 1;
                    $piece_y_max = max($h, $piece_y_max);
                }
            }
        }
        if (!$piece_x_c || !$piece_x_sum) {
            //fixme 再来一次
            $this->showMessage(3, array('error' => '找不到棋子坐标,请重新开始'));
//            $this->autoJump();
            exit;
        }
        $piece_x = intval($piece_x_sum / $piece_x_c);
        $piece_y = $piece_y_max - $this->config['piece_base_height_1_2'];
//        $this->showMessage(4, array('message' => 'x:' . $piece_x . ' y:' . $piece_y));
        return array(
            'piece_x' => $piece_x,
            'piece_y' => $piece_y,
        );
    }

    /**
     * @desc:获取下一个跳板坐标位置
     * @author: icehill
     */
    public function findBoard($imagePath, $piece_x, $piece_y)
    {
        $board_x = 0;
        $board_y = 0;
        if ($this->imageWidth && $this->imageHeight) {
            $width = $this->imageWidth;
            $height = $this->imageHeight;
        } else {
            $sizeArr = getimagesize($imagePath);
            $width = $sizeArr[0];
            $height = $sizeArr[1];
        }
        $image = imagecreatefrompng($imagePath);
        //判断下一个跳板在棋子的左上还是右上，缩小跳板的位置
        if ($piece_x < $width / 2) {
            $board_x_start = $piece_x;
            $board_x_end = $width;
        } else {
            $board_x_start = 0;
            $board_x_end = $piece_x;
        }
        //fixme 高度可以修改为棋子高度以上
        for ($h = intval($height / 3); $h <= intval($height * 2 / 3); $h++) {
            $lastPixel = $this->getImagePixel($image, 0, $h);
            if ($board_x || $board_y) break;
            $board_x_sum = 0;
            $board_x_c = 0;
            for ($w = intval($board_x_start); $w < intval($board_x_end); $w++) {
                $pixel = $this->getImagePixel($image, $w, $h);
                // fixme
                if (abs($w - $piece_x) < $this->config['piece_body_width']) continue;
                if ((abs($pixel[0] - $lastPixel[0]) + abs($pixel[1] - $lastPixel[1]) + abs($pixel[2] - $lastPixel[2])) > 10) {
                    $board_x_sum += $w;
                    $board_x_c += 1;
                }
            }
            if ($board_x_sum) {
                $board_x = $board_x_sum / $board_x_c;
            }
        }
        $lastPixel = $this->getImagePixel($image, $board_x, $h);
        for ($k = $h + 274; $k > $h; $k--) {
            $pixel = $this->getImagePixel($image, $board_x, $k);
            if ((abs($pixel[0] - $lastPixel[0]) + abs($pixel[1] - $lastPixel[1]) + abs($pixel[2] - $lastPixel[2])) < 10) {
                break;
            }
        }
        $board_y = intval(($h + $k) / 2);

        for ($l = $h; $l < $h + 200; $l++) {
            $pixel = $this->getImagePixel($image, $board_x, $l);
            if ((abs($pixel[0] - 245) + abs($pixel[1] - 245) + abs($pixel[2] - 245)) == 0) {
                $board_y = $l + 10;
                break;
            }
        }
        return array(
            'board_x' => $board_x,
            'board_y' => $board_y
        );
    }

    /**
     * @desc: 获取配置文件
     * @author: icehill
     */
    public function getConfig()
    {
        require("." . DS . 'config.php');
        $this->screenSize = $this->getScreenSize();
        $this->tempImageName = $tempImageName;
        if (isset($config[$this->screenSize])) {
            $this->config = $config[$this->screenSize];
        } else {
            $this->showMessage(3, array('error_info' => '暂时没有改屏幕大小的配置文件，请自己根据情况在config文件添加配置.'));
        }
        //判断是否是windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->adbPath = $adbPath;
        } else {
            $this->adbPath = 'adb';
        }
        $this->debugImageFilePath = $debugImageFilePath;
    }

    /**
     * @desc: 获取图片某一点像素
     * @author: icehill
     * @param $image
     * @return string
     */
    public function getImagePixel($image, $x, $y)
    {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return array(
            $r, $g, $b
        );
//        return "($r,$g,$b)";
    }

    /**
     * @desc: 中断或提示信息
     * @author: icehill
     * @param int $type
     * @param array $param
     */
    public function showMessage($type = 0, $param = [])
    {

        if (!IS_SHOW_OUT) {
            return;
        }
        $message = '';
        $messageArr = [];
        switch ($type) {
            case 1:
                $message = "第 " . $param['count'] . " 下\n";
                break;
            case 2://执行命令行输出
                $message = end($param['exec_out']) . "\n";
                break;
            case 3://错误信息
                $message = "error:" . $param['error_info'] . "\n";
                break;
            case 4://普通信息
                $message = $param['message'] . "\n";
                break;
            case 5:
                foreach ($param['message_array'] as $item) {
                    $messageArr[] = $item . "\n";
                }
                break;
            default:
                $message = "unknown error\n";
        }
        if (IS_SHOW_OUT) {
            if ($message) {
//                echo $message;
                //命令行乱码处理
                echo iconv("UTF-8", "gbk", $message);
            }
            if (!empty($messageArr)) {
                foreach ($messageArr as $item) {
//                    echo $item;
                    echo iconv("UTF-8", "gbk", $item);
                }
            }
        }
        if (IS_FLUSH) {
            flush();
        }
    }

}
