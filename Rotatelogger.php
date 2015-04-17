<?php

/**
 * Filesystem-based implementation of ILogger with log rotate by date.
 *
 * @version    0.1
 * @package    php_logrotate
 *
 * @copyright  Copyright (c) 2013 Alexei Vlasov
 */
class Rotatelogger {

    /**
     * log的最大文件大小，单位是MB
     */
    const MAX_LOG_FILE_SIZE = 10;

    /**
     * Critical conditions
     * @var integer
     */
    const CRITICAL = 50;

    /**
     * Error conditions
     * @var integer
     */
    const ERROR = 40;

    /**
     * Warning conditions
     * @var integer
     */
    const WARNING = 30;

    /**
     * Informational
     * @var integer
     */
    const INFO = 20;

    /**
     * Debug-level messages
     * @var integer
     */
    const DEBUG = 10;

    /**
     * System is unusable
     * @var integer
     */
    const NOT_SET = 0;


    /**
     * Log level
     * @var integer
     */
    private $iLogLevel;

    /**
     * Log level
     * @var string
     */
    private $sLogLevel = 'ERROR';

    /**
     * Log file path
     * @var string
     */
    private $sFilePath;

    /**
     * Date format
     * @var string
     */
    private $sDateFormat;

    /**
     * Statistics of an $rLogFile
     * @var array
     */
    private $hStat;

    private $lock_fp;

    /**
     * Creates instance of RLogger.
     *
     * @param string $sFilePath path to the log file
     * @param string $sLogLevel
     * @param string $sDateFormat
     * @return \RLogger\RotateLogger
     */
    public function __construct($log_file, $log_level='ERROR', $live_days='') {
        $this->iLogLevel = $this->logLevelAsInt($log_level);
        $this->sDateFormat = 'Y-m-d_H:i:s';
        if(empty($log_file)) {
            exit('no define log_file...');
        } else {
            $this->openFile($log_file);
            $this->sFilePath = realpath($log_file);
        }

        if ($this->needRotate()) {
            $this->rotate();
        }

        if(!empty($live_days)) {  //没有定义的话，log将会永久保存
            if(intval($live_days)<5) {
                exit('live_days must 大于等于5');
            }
            $this->deleteOldFile(intval($live_days));
        }
    }

    /**
     * 删除过期的日志 
     */
    private function deleteOldFile($live_days) {
        if($this->sFilePath) {
            foreach (glob($this->sFilePath."*") as $filename) {
                if(filemtime($filename) < time()-3600*24*$live_days) {
                    unlink($filename);
                }
            }
        }
    }

    /**
     * Open the log file.
     *
     * @param $sFilePath
     * @param string $mode
     * @return null|resource
     */
    private function openFile($sFilePath, $mode='a') {
        try {
            $handler = fopen($sFilePath, $mode);
        } catch (RuntimeException $e) {
            return NULL;
        }
        if (!$handler) {
            return NULL;
        }
        $this->hStat = fstat($handler);
	fclose($handler);
	clearstatcache();
    }

    /**
     * Check if we have to rotate the log
     * @return bool
     */
    private function needRotate() {
        if (!is_file($this->sFilePath) || filesize($this->sFilePath)<self::MAX_LOG_FILE_SIZE*1024*1024 ) {
            return FALSE;
        //} elseif (intval(date('Ym')) < intval(date('Ym', filectime($this->sFilePath)))) {
        //   return FALSE;
        //}
        }
    	if(!file_exists("/tmp/rotate_log_lock.txt")){
    		fopen("/tmp/rotate_log_lock.txt", 'w');
    	}
    	$this->lock_fp = fopen("/tmp/rotate_log_lock.txt", "r+");

    	if (flock($this->lock_fp, LOCK_EX)) {  // 进行排它型锁定
            	return TRUE;
    	}else {
    		return false;
    	}
    }

    /**
     * Rotate the log.
     * Copy a $rLogFile to the new file and reopen it with mode 'w'
     */
    private function rotate() {
        copy($this->sFilePath, $this->sFilePath . "_" . date('Y-m-d_H_i_s'));
        clearstatcache(); // Drop internal php cache with file's stat
        file_put_contents($this->sFilePath, "");  //清空文件内容
        chmod($this->sFilePath, $this->hStat["mode"]); // Change CTime
	    flock($this->lock_fp, LOCK_UN);    // 释放锁定
	    fclose($this->lock_fp);
    }

    /**
     * Get log level as integer by string.
     *
     * @param $logLevel
     * @return int
     */
    private function logLevelAsInt($logLevel) {
        $hLevels = array(
            'CRITICAL' => self::CRITICAL,
            'ERROR' => self::ERROR,
            'WARNING' => self::WARNING,
            'INFO' => self::INFO,
            'DEBUG' => self::DEBUG,
            'NOT_SET'=> self::NOT_SET,
        );
        return array_key_exists($logLevel, $hLevels) ? $hLevels[$logLevel] : (int) $logLevel;
    }

    /**
     * Get log level as string by integer.
     *
     * @param $logLevel
     * @return string
     */
    private function logLevelAsString($logLevel) {
        $hLevels = array(
            self::CRITICAL => 'CRITICAL',
            self::ERROR => 'ERROR',
            self::WARNING => 'WARNING',
            self::INFO => 'INFO',
            self::DEBUG => 'DEBUG',
            self::NOT_SET => 'NOT_SET',
        );
        return array_key_exists($logLevel, $hLevels) ? $hLevels[$logLevel] : (string) $logLevel;
    }

    /**
     * Convert $oMessage to string and write to a $rLogFile
     * @param int|string $logLevel
     * @param string $oMessage
     */
    private  function log($logLevel, $oMessage) {
        $sLogMsg = $oMessage;
        if (is_array($oMessage)) {
            $sLogMsg = print_r($oMessage, true);
        } elseif (is_object($oMessage)) {
            $sLogMsg = print_r($oMessage, true);
        }
        if ($logLevel >= $this->iLogLevel) {
            $sLogLine = date($this->sDateFormat) . ' ' . $this->logLevelAsString($logLevel) . ' ' . $sLogMsg . PHP_EOL;
            error_log($sLogLine, 3, $this->sFilePath);
        }
    }

    /**
     * Alias for a log method with appending log level CRITICAL
     *
     * @param $oMessage
     * @return null|void
     */
    public function critical($oMessage) {
        $this->log(self::CRITICAL, $oMessage);
    }

    /**
     * Alias for a log method with appending log level ERROR
     *
     * @param $oMessage
     * @return null|void
     */
    public function error($oMessage) {
        $this->log(self::ERROR, $oMessage);
    }

    /**
     * Alias for a log method with appending log level WARNING
     *
     * @param $oMessage
     * @return null|void
     */
    public function warning($oMessage) {
        $this->log(self::WARNING, $oMessage);
    }

    /**
     * Alias for a log method with appending log level INFO
     *
     * @param $oMessage
     * @return null|void
     */
    public function info($oMessage) {
        $this->log(self::INFO, $oMessage);
    }

    /**
     * Alias for a log method with appending log level DEBUG
     *
     * @param $oMessage
     * @return null|void
     */
    public function debug($oMessage) {
        $this->log(self::DEBUG, $oMessage);
    }

}
?>
