<?php    //加载框架入口文件
ini_set('display_errors','On');
require 'function.php';
define('APP_NAME', 'DmsAdmin');
define('APP_PATH', 'DmsAdmin/');
define('APP_DEBUG', true);
if($_GET && key($_GET)!=='s')
{
	$_GET['s']='/User/Saleweb/usereg/rec/'.key($_GET);
}
if(!$_GET)
{
	$_GET['s']='/User/Public/login';
}
require 'ThinkPHP/ThinkPHP.php';
?>
