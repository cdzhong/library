<?php
/*
1.写满一个log文件后(10MB)会自动备份，备份文件名形式是这样的：mobaaplog.log_2015-01-09_03_31_34.
2.可以设定log的级别，级别从高到低依次是critical, error, warning, info, debug,如果设定log的级别是error，
  则会记录error和critical级别的log.
3.可以指定删除几天前的log,避免log越写越多，导致磁盘被用完.
4.如果不同类型的log需要放到不同的log文件，比如数据库log和其他程序执行log要分开存放，只需要创建相应
  的log对象就可以（根据需要，指定相应的参数）
*/


define('YYKF_APP_LOG_FILE', '/data/wwwroot/mobapp/log/mobapplog.log');   //必须指定log文件的路径，单个log文件的大小为10MB,写满了会被备份
define('YYKF_APP_LOG_LEVEL', 'WARNING');  //设定记录WARNING以上级别的log 可以不传这个参数，默认是ERROR
define('YYKF_APP_LOG_LIVE_DAYS', 10);  //这表示删除10天前的log, 可以不传这个参数，默认是不删除log,如果传了这个参数，必须大于等于5


require_once 'Rotatelogger.php';

$log = new Rotatelogger(YYKF_APP_LOG_FILE, YYKF_APP_LOG_LEVEL, YYKF_APP_LOG_LIVE_DAYS);
//如果你需要记录几种log，那么久再new多几个Rotatelogger对象

$log->critical('this is critical...');
$log->error('this is error....');
$log->warning('this is warning...');
$log->info('this is info...');   //info级别的调用没用
$log->debug('this is debug...');  //debug级别的调用没用


$log->critical(array('a'=>'cccccccccccc', 'b'=>'dddddddddddddddd'));  //直接log数组


//结果是像这样的：
// 2015-01-09_14:44:19 CRITICAL this is critical...
// 2015-01-09_14:44:19 ERROR this is error....
// 2015-01-09_14:44:19 WARNING this is warning...
// 2015-01-09_14:44:19 CRITICAL Array
// (
//     [a] => cccccccccccc
//     [b] => dddddddddddddddd
// )

?>
