##########################包含3个类：curl, log, mysql############################





##########################如何跟CI一起工作#######################################
全局自动加载：
2种方式：autoload.php  MY_Controller.php【其他的控制器继承自MY_Controller】

按需手动加载【一般在控制器里面添加】：
$this->load->library('curl');

/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in the system/libraries folder
| or in your application/libraries folder.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'session', 'xmlrpc');
*/

$autoload['libraries'] = array('curl');




##########################也可以独立地工作，比如一些小型系统，或者是脚本。。。#######################################
只要require就ok啦。。。