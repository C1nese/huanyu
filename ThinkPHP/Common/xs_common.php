<?php


/*
* 检查是否有指定方法的权限
* app:应用,module:模块,action:方法,group:分组,args:其他参数
*/
function rbac_check_power($app,$module,$action,$group='',$args='')
{
	$appName	= $data['app'];
	$module		= $data['module'];
	$action		= $data['action'];
	$group		= $data['group'];
	$args		= $data['args'];

	if( !isset($_SESSION['_ACCESS_LIST'][ strtoupper($appName.'_'.$group) ][ strtoupper($module.'_'.$args) ][ strtoupper($action) ]) )
	{
		return false;
	}
	return true;
}



/*字符过滤url*/
function safe_replace($string) {
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','&quot;',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace('"','',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	$string = str_replace('\\','',$string);
	return $string;
}
?>