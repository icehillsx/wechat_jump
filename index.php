<?php
/**
 * @desc: Created by PhpStorm.
 * @author: icehill
 * @date: 2018/2/13 13:11
 *
 */
/** @var 是否开启调试模式  */
define('IS_DEBUG',false);

/** 是否输出运行过程的消息 */
define('IS_SHOW_OUT',true);

/** 是否马上输出 */
define('IS_FLUSH',true);

/** 系统目录操作符*/
define('DS', '/');

require("." . DS . "jump.php");
$jump = new jump();
$jump->autoJump();//自动连续跳
//$jump->jumpOnce();//跳一次