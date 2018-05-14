# wechat_jump
微信跳一跳自动化程序（PHP）

## 原理说明

1. 将手机点击到《跳一跳》小程序界面；
2. 用 ADB 工具获取当前手机截图，并用 ADB 将截图 pull 上来

```shell
    adb shell screencap -p /sdcard/autojump.png
    adb pull /sdcard/autojump.png .
```

3. 计算按压时间
  * 靠棋子的颜色来识别棋子，靠底色和方块的色差来识别棋盘；

4. 用 ADB 工具点击屏幕蓄力一跳；

```shell
    adb shell input swipe x y x y time(ms)
```

### 相关配置说明

- $adbPath：adb工具的路径，命令行模式下只要能执行adb命令就无需设置，如果window下使用集成工具在网页中运行脚本，则需要补全工具路径，例如
```
    $adbPath = 'D:/adb.exe';
```

- IS_DEBUG：是否开启调试模式，开启后保存脚本运行过程的的截图信息，以便优化游戏过程中失败情形。

- IS_FLUSH: 是否立马输出信息（CLI模式下无需设置）

- 若是脚本运行超时，请在php.ini配置文件中修改max_execution_time，CLI模式下该参数默认为0

### Window下android手机操作步骤

- 安卓手机打开 USB 调试，设置》开发者选项》USB 调试
- 电脑与手机 USB 线连接，确保执行`adb devices`可以找到设备 ID
- 界面转至微信跳一跳游戏，点击开始游戏
- 运行`php index.php`，如果手机界面显示USB授权，请点击确认
- 请按照你的手机分辨率从`./config.php`找到相应的配置，若不存在，请自行调试进行设置

### Mac下android手机操作步骤
- 待补充

### Mac下ios手机操作步骤

- 待补充

### TODO

- 算法待优化
- 容易被检测到作弊，需要增加干扰，防止被检测到
- 微信跳一跳已经增加防住作弊处理，容易被检测到分数异常了，需要增加干扰。