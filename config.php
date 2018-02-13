<?php
/**
 * @desc: Created by PhpStorm.
 * @author: icehill
 * @date: 2018/2/12 11:52
 *
 */
$config = [
    '1080x1920' =>
        [
            "under_game_score_y" => 300,
            "press_coefficient" => 1.392,
            "piece_base_height_1_2" => 20,    /** @var null 1/2棋子高度 */
            "piece_body_width" => 70,/** 棋子的宽度， 比截图中量到的稍微大一点比较安全，可能要调节*/
            "x1" => 500,
            "y1" => 1600,
            "x2" => 500,
            "y2" => 1602
        ],
    '2160x1080' =>
        [
            "under_game_score_y" => 300,
            "press_coefficient" => 1.392,
            "piece_base_height_1_2" => 20,
            "piece_body_width" => 70,
            "x1" => 500,
            "y1" => 1600,
            "x2" => 500,
            "y2" => 1602
        ],
];
/** @var adb路径 $adbPath */
//$adbPath = 'C:/Users/icehill/Downloads/platform-tools/adb.exe';
$adbPath = 'adb';

$tempImageName = 'autojump.png';

/** @var 缓存图片 $debugImageFilePath */
$debugImageFilePath = '.' . DS . 'test_images_screen' . DS;
