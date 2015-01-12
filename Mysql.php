<?php

/**
* MySQL 数据库操作类
*
*/


class Mysql {
        /**
        * 服务器名或 ip 地址
        *
        * @var string
        */

        var $server = "localhost";

        /**
        * 数据库名
        *
        * @var string
        */

        var $database = "";

        /**
        * 用户名
        *
        * @var string
        */

        var $user = "root";

        /**
        * 用户密码
        *
        * @var string
        */

        var $password = "";

        /**
        * 是否使用持续连接
        *
        * @var bool
        */

        var $usepconnect = false;


        /**
        * MySQL link identifier
        *
        * @var resource
        */

        var $id;

        /**
        * SQL query 次数
        *
        * @var integer
        */

        var $querycount = 0;

        /**
        * 运行 SQL 请求后返回的结果集
        *
        * @var resource
        */

        var $result;

        /**
        *
        * @var array
        */

        var $record = array();

        var $rows;

        /**
        * 最后一次 INSERT 操作所返回的自增 ID
        *
        * @var integar
        */

        var $insertid;

        /**
        * 当出错时, 是否停止运行?
        *
        * @var bool 1: 停止, 0: 继续运行
        */

        var $halt = 1;

        /**
        * 错误号
        *
        * @var integer
        */

        var $errno;

        /**
        * 错误提示
        *
        * @var string
        */

        var $error;

        var $log_error_sql = true;    //是否记录错误sql

        var $log_error_sql_filename = '/var/tmp/error.sql';   //存放错误sql的文件

        var $log_error_sql_live_days = 10;

        var $rlog;  //RotateLogger对象

        var $debug = false;  //debug=true时直接输出错误信息

        /**
        * 初始化
        */

        function MySQL($server, $user, $password, $database, $port=3306, $usepconnect=false) {
                if ($usepconnect) {
                        if (!$this->id = @mysql_pconnect($server.':'.$port, $user, $password)) {
                                $this->halt("数据库链接失败");
                        }
                } else {
                        if (!$this->id = mysql_connect($server.':'.$port, $user, $password)) {
                                $this->halt("数据库链接失败");
                        }
                }

                if (!mysql_select_db($database, $this->id)) {
                        $this->halt("选择数据库失败");
                }
        }

        /**
        * 获取错误描述
        *
        * @access private
        * @return string
        */

        function geterrdesc() {
                $this->error = @mysql_error($this->id);

                return $this->error;
        }

        /**
        * 获取错误号
        *
        * @access private
        * @return integer
        */

        function geterrno() {
                $this->errno = @mysql_errno($this->id);

                return $this->errno;
        }


        /**
        * 运行 SQL 语句并返回结果集
        *
        * @access public
        * @param  $query_string string
        * @return resource |false
        */

        function query($query_string) {
                // $this->result = mysql_query($query_string, $this->id);
				if (!$this->check_sql($query_string)) {
					return false ;
				}
                //$startTime = array_sum(explode(' ', microtime()));
                $this->result = mysql_query($query_string, $this->id);
                //$endTime = array_sum(explode(' ', microtime()));

                //if($endTime - $startTime > $this->slowQueryTime) {
                //    @error_log($query_string."\t".($endTime - $startTime)."\r\n\r\n", 3, '/var/tmp/db_slow_query.log');
                //}

                if (!$this->result) {
                        $this->halt("SQL 无效: " . $query_string);
                }

                $this->querycount++;

                return $this->result;
        }

        /**
        * Fetch a result row as an associative array, a numeric array, or both.
        *
        * @access public
        * @param  $result , $result_type
        * @see mysql_fetch_array
        * @return array |false
        */

        function fetch_array($result, $result_type = MYSQL_ASSOC) {
                if (!$result) {
                        $this->halt("resource result 无效:" . $result);
                }

                $this->record = mysql_fetch_array($result, $result_type);

                return $this->record;
        }

        /**
        * Get a result row as an enumerated array
        *
        * @access public
        * @param  $result
        * @return array |false
        */

        function fetch_row($result) {
                if (!$result) {
                        $this->halt("resource result 无效:" . $result);
                }

                $this->record = mysql_fetch_row($result);

                return $this->record;
        }


        /**
        * 运行 SQL 并返回结果
        *
        * @access public
        * @param string $query_string
        * @param integer $result_type
        * @see mysql_fetch_array
        * @param bool $use_cache 是否使用 cache, 默认是使用
        * @return array |false
        */

        function query_first($query_string, $result_type = MYSQL_ASSOC) {
				if (!$this->check_sql($query_string)) {
					return false ;
				}

                $this->result = $this->query($query_string);

                $this->record = $this->fetch_array($this->result, $result_type);

                return $this->record;
        }


        /**
        * 运行 SQL 并以数组的形式返回所有结果
        *
        * @access public
        * @param string $query_string
        * @param integer $result_type
        * @return array |false
        */

        function query_all($query_string, $result_type = MYSQL_ASSOC) {

				if (!$this->check_sql($query_string)) {
					return false ;
				}

                $this->result = $this->query($query_string);

                if($this->result){

                    $records = array();
                    while($record = $this->fetch_array($this->result, $result_type)){
                         $records[] = $record;
                    }


                }

                return $records;
        }


        /**
        * Get number of rows in result
        *
        *
        *
        * returns the number of rows in a result set. This command is only valid for SELECT statements.
        *
        * @access public
        * @param  $result
        * @return integer
        */

        function num_rows($result) {
                $this->rows = mysql_num_rows($result);

                return $this->rows;
        }

        /**
        * Free result memory
        *
        * @access public
        * @param  $result
        */

        function free_result($result) {
                if (!mysql_free_result($result)) {
                        $this->halt("释放结果集失败");
                }
        }

        /**
        * Get the ID generated from the previous INSERT operation
        *
        * @access public
        * @return integer
        */

        function insert_id() {
                $this->insertid = mysql_insert_id($this->id);

                if (!$this->insertid) {
                        $this->halt("fail to get mysql_insert_id");
                }

                return $this->insertid;
        }

        /**
        * Get number of affected rows in previous MySQL operation
        *
        * @return integer returns the number of rows affected by the last INSERT, UPDATE or DELETE query associated with link_identifier
        */

        function affected_rows() {
                $this->affected_rows = mysql_affected_rows($this->id);

                return $this->affected_rows;
        }

        function data_seek($result, $i) {
                if (mysql_data_seek($result, $i)) {
                        return true;
                } else {
                        return false;
                }
        }

        /**
        * 关闭数据库连接
        *
        * @access public
        */

        function close() {
                @mysql_close($this->id);
        }

        /**
        * 建过 array 建立条件
        */

        function build_condition($condition = array(), $bool = " AND ") {
                if ($condition AND is_array($condition)) {
                        $conditions = " WHERE " . implode($bool, $condition);
                }

                return $conditions;
        }

		/**
		*
		* 验证SQL，务必确认update|delete|replace带上where条件
		*/
		function check_sql($sql) {
			if (preg_match("/^UPDATE|DELETE|REPLACE/is" , $sql )) { // 更新操作，需要有where条件
				if (!strpos($sql , "WHERE") && !strpos($sql , "where") ) {
                    $this->halt($sql."###少年，请加上where 条件吧。。");
					return false ;
				}
			}
			return true ;
		}

        /**
        * 提示出错信息并中终程序
        *
        * @access private
        * @param  $msg 提示信息
        */

        function halt($msg) {

                //nocache_header();
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                // always modified
                header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
                header("Cache-Control: post-check=0, pre-check=0", false);

                header("Pragma: no-cache"); // HTTP/1.0
                header("Content-type: text/html; charset=utf-8");

                $log = '';

                $log .= str_replace("\n", "\r\n", $msg) . "\r\n";
                $log .= "______________________________________________________________________\r\n";
                $log .= "Date: " . date("Y-m-d H:i:s") . "\r\n";
                $log .= "mysql error description: " . str_replace("\n", "\r\n", $this->geterrdesc() ). "\r\n";
                $log .= "mysql error number: " . $this->geterrno() . "\r\n";
                $log .= "Database: " . $this->database . "\r\n";
                $log .= "Server: " . $this->server . "\r\n";
                $log .= "Linkid " . $this->id . "\r\n";
                $log .= "Script: " . $_SERVER["REQUEST_URI"] . "\r\n";
                $log .= "Referer: " . $_SERVER["HTTP_REFERER"] . "\r\n\r\n\r\n";

                if($this->log_error_sql) {
                    require_once 'Rotatelogger.php';
                    if(empty($this->rlog)) {
                        $this->rlog = new Rotatelogger($this->log_error_sql_filename, 'ERROR', $this->log_error_sql_live_days);
                    }
                    $this->rlog->error($log);
                } 

                if($this->debug) {
                    echo $log;
                    if($this->log_error_sql) {
                        echo '错误信息同时保存在'.realpath($this->log_error_sql_filename).'文件里面';
                    }
                }

                if ($this->halt) {  //发生错误时不再往下执行
                    exit();
                }
        }
}

?>
