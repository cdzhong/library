<?php
 
/**
 * php文件缓存类 FileCache
 *
 * @author dafei <dafei.net@gmail.com>
 * @version 1.8.1 20120620
 */
 
    class FileCache
    {
 
        public static $_iscache   = true;        //true开启缓存，如果设置为false，则调取get和set时直接return
        public static $_cachedir  = 'D:\appserv\www\tmp\lib\cache';     //缓存文件默认存放目录
        public static $_cachetime = 3600;        //默认缓存时间
 
        /**
         * 读取缓存
         *
         * @param string $key 缓存key
         * @param string $d 缓存文件目录
         * @return $data 返回缓存内容：字符串或数组；缓存为空或过期返回 false
         */
        public static function get($key=false,$d=false)
        {
            if(empty($key) or !self::$_iscache)
            {
                return false;
            }
            $filename  = self::get_filename($key,$d);
            if(!file_exists($filename))
            {
                return false;
            }
            $data = file_get_contents($filename);
            $data = unserialize($data);
            $time = (int)$data['time'];
            $data = $data['data'];
            if($time>time())
            {
                return $data;
            }
            else
            {
                return false;
            }
        }
 
        /**
         * 写入缓存
         *
         * @param string $key 缓存key
         * @param string $value 缓存value
         * @param string $t 缓存时间 单位秒
         * @param string $d 缓存文件目录
         * @return bool 成功返回true 失败返回false
         */
        public static function set($key=false,$value=false,$t=0,$d=false)
        {
            if(empty($key) or !self::$_iscache)
            {
                return false;
            }
            $t = (int)$t ? (int)$t : self::$_cachetime;
            $filename  = self::get_filename($key,$d);
            if(!self::is_mkdir(dirname($filename)))
            {
                return false;
            }
            $data['time'] = time()+$t;
            $data['data'] = $value;
            $data = serialize($data);
            if(PHP_VERSION >= '5')
            {
                file_put_contents($filename,$data);
            }
            else
            {
                $handle = fopen($filename,'wb');
                fwrite($handle,$data);
                fclose($handle);
            }
            return true;
        }
 
        /**
         * 清除缓存
         *
         * @param string $key 缓存key
         * @param string $d 缓存文件目录
         * @return bool 成功返回 true 失败返回 false
         */
        public static function un_set($key=false,$d=false)
        {
            if(empty($key))
            {
                return false;
            }
            $filename = self::get_filename($key,$d);
            @unlink($filename);
            return true;
        }
 
        /**
         * 返回缓存文件全路径
         *
         * @param string $key 缓存key
         * @param string $d 缓存文件目录
         * @return string 缓存文件全路径
         */
        public static function get_filename($key=false,$d=false)
        {
            if(empty($key))
            {
                return false;
            }
            $dir       = empty($d) ? self::$_cachedir : $d ;
            $key_md5   = md5($key);
            $filename  = rtrim($dir,'/').'/'.substr($key_md5,0,2).'/'.substr($key_md5,2,2).'/'.substr($key_md5,4,2).'/'.$key_md5;
            return $filename;
        }
 
        /**
         * 创建目录
         *
         * @param string $dir
         * @return bool 成功返回 true 失败返回 false
         */
        public static function is_mkdir($dir='')
        {
            if(empty($dir))
            {
                return false;
            }
            if(!is_writable($dir))
            {
                if(!@mkdir($dir,0777,true))
                {
                    return false;
                }
            }
            return true;
        }
 
    }
 
?>