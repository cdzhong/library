<?php
/*
1.连接MySQL数据库，实现增删改查.
2.可以指定是否记录执行sql语句时的错误（使用RotateLogger类）
3.重要api: 
    query(删除、插入、更新操作返回bool, 查询返回resource)
    query_first:查询符合条件的第一条记录【一维数组】
	query_all:查询符合条件的所有记录【二维数组】
	insert_id:刚刚插入的那条记录的id
	affected_rows：插入、删除、更新操作影响的行数
	num_rows:查询出来的行数
	fetch_array:有时候为了效率考虑，不要使用query_all,一行一行地读取记录、处理
*/


require_once 'Mysql.php';

$server = '127.0.0.1';
$user = 'root';
$password = '111';
$database = 'test';

$MySQL = new MySQL($server, $user, $password, $database);
$MySQL->log_error_sql = true;   //默认记录，可以设置为false则不记录
$MySQL->log_error_sql_filename = 'error.sql';  //指定log文件名，建议是/data/wwwroot/mobapp/log/error.sql,默认是/var/tmp/error.sql
$MySQL->log_error_sql_live_days = 20;   //指定删除20天前的log文件，默认是10天

$MySQL->debug = true;  //开启数据库的debug模式，sql错误会输出到页面/控制台; 为了安全，不能把可能的错误sql让用户看到,所以默认关闭

$query = "insert into user(username) values ('jonda1xx')";
if($MySQL->query($query)) {
    echo "插入成功";
} else {
    echo "插入失败";
}
echo $MySQL->insert_id();
echo $MySQL->affected_rows();

$row = $MySQL->query_first('select * from user where id=1 limit 1');
print_r($row);

$rows = $MySQL->query_all('select * from user');
print_r($rows);

$result = $MySQL->query('select * from user');
echo $MySQL->num_rows($result);
while($row = $MySQL->fetch_array($result)) {
	//处理这一行记录
	var_dump($row);
}

//$MySQL->query("delete from user"); //delete update replace的时候需要加上where条件

?>