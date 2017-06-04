<?php
require_once 'Curl.php';

####这是一个简单的http get示例####
$curl = new Curl();

$response = $curl->get('http://www.baidu.com/s?', array('wd'=>'ddddd')); //返回正确的话，$response是CurlResponse的一个对象
if($response) {
	//var_dump($response); $response和$response->body一样是字符串，因为$response对象有魔术方法__toString
	//echo $response->body;
	print_r($response->headers);

//返回的http头部是一个一维数组	
// Array
// (
//     [Http-Version] => 1.1
//     [Status-Code] => 200
//     [Status] => 200 OK
//     [Date] => Sat, 10 Jan 2015 03:19:41 GMT
//     [Content-Type] => text/html;charset=utf-8
//     [Transfer-Encoding] => chunked
//     [Connection] => Keep-Alive
//     [Vary] => Accept-Encoding
//     [Set-Cookie] => H_PS_PSSID=10928_10162_1424_9993_9583_10873_11071_10501_11059_11066_10922_10591_10699_10617_10701; path=/; domain=.baidu.com
//     [P3P] => CP=" OTI DSP COR IVA OUR IND COM "
//     [Cache-Control] => private
//     [Cxy_all] => baidu+04c992498ba851882d41532cd2dc5ccf
//     [X-Powered-By] => HPHP
//     [Server] => BWS/1.1
//     [BDPAGETYPE] => 3
//     [BDQID] => 0x8135dbfb001073d2
//     [BDUSERID] => 0
// )

} else {
	print_r($curl->error());  //把他log下来吧，以便后续可以分析
}

####这是并发http get示例，就像现代的浏览器那样，可以并发多个请求，默认最多10个，而且支持长连接，pipeline####
$urls = array('http://www.baidu.com', 'http://www.qq.com', 'http://www.163.com');
$contents =$curl->gets($urls);
var_dump($contents);  //$contents数组是与$urls数组一一对应的


###这个curl库默认的option 
// curl_setopt($this->request, CURLOPT_URL, $url);
// curl_setopt($this->request, CURLOPT_HEADER, true);  //将头文件的信息作为数据流输出。
// curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true); //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
// curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
// curl_setopt($this->request, CURLOPT_CONNECTTIMEOUT, 30); 在发起连接前等待的时间，如果设置为0，则无限等待
// curl_setopt($this->request, CURLOPT_TIMEOUT, 60); 设置cURL允许执行的最长秒数。
// 【发布系统的血淋淋的事实】这2个超时时间一定得设定，不然的话如果目标url一直没有返回，而且PHP的执行时间无限制的话，
// 那么会导致PHP进程永远阻塞在curl调用上，不但占用系统资源，而且不能接受别的请求，容易导致502错误


###当然也可以自定义的option
//$curl->options['HTTPHEADER'] = array('host'=>'2.2.2.2')  //CURLOPT_HTTPHEADER
//$curl->options['NOBODY'] = true;      //CURLOPT_NOBODY


###还可以有其他的http方法
//$response = $curl->post('test.com/posts', array('title' => 'Test', 'body' => 'This is a test'));
// $response = $curl->head($url, $vars = array());
// $response = $curl->get($url, $vars = array());
// $response = $curl->post($url, $vars = array());
// $response = $curl->put($url, $vars = array());
// $response = $curl->delete($url, $vars = array());


###可以通过cookie选项实现curl登录
//$curl->options['CURLOPT_COOKIEFILE', $this->cookie_file);  登录服务器往curl写入cookie
//$curl->options['CURLOPT_COOKIEJAR', $this->cookie_file);  curl读取cookie发送给目标请求
//这个库只要指定$curl->cookie=$filename即可以了。


###可以设置如下选项抓取https的页面
//$curl->options['CURLOPT_SSL_VERIFYHOST'] = 0;
//$curl->options['CURLOPT_SSL_VERIFYPEER'] = false;


$arr = ['a'=>8, 'b'=>3];
$curl->options['CURLOPT_POSTFIELDS'] = json_encode($arr);
$curl->options['CURLOPT_HTTPHEADER'] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen(json_encode($arr)));

$contents =$curl->post('http://localhost/test2.php');

?>
