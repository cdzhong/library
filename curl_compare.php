<?php
set_time_limit(0);

require_once 'Curl.php';

for ($i=0; $i < 100; $i++)   
{   
  $urls[] = "http://www.baidu.com/s?wd=".mt_rand(10000,20000);  
}

$curl = new Curl();

$t = microtime(true);  
foreach ($urls as $key => $url) {
	$response = $curl->get($url);
	//var_dump($response);
}
$e = microtime(true);
echo "传统浏览器模型：".($e-$t)."\n";  


####这是并发http get示例，就像现代的浏览器那样，可以并发多个请求，默认最多10个，而且支持长连接，pipeline####
$t = microtime(true);  
$contents =$curl->gets($urls);
//var_dump($contents);  //$contents数组是与$urls数组一一对应的
$e = microtime(true);
echo "现代浏览器模型：".($e-$t)."\n";  

//测试结果：
//我本地的服务器(windows)： 传统浏览器模型：125.498869896 现代浏览器模型：60.1092028618
//测试机服务器(linux)：传统浏览器模型：48.950429916382  现代浏览器模型：4.1609859466553

//curl_multi_xx完胜curl_xx
//我的机器cpu、内存不比测试机差，原因就在于测试机网速快？更可能在于curl库在Linux下面实现更优秀
?>