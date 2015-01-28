<?php
require_once 'FileCache.php';
FileCache::$_cachedir = 'D:\appserv\www\tmp';
FileCache::set('key', array(3,4,5), 333);  // key, value, time

?>