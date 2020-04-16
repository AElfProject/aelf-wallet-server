<?php
if (!get_magic_quotes_gpc())
{
	array_walk($_GET, "process_variables");
	array_walk($_POST, "process_variables");
	array_walk($_FILES, "process_variables");
	array_walk($_COOKIE, "process_variables");
	if (is_array(@$_SESSION))
	{
		array_walk($_SESSION, "process_variables");
	}
}

$gbl_con			= get2('con') ? get2('con') : 'web';
$gbl_ctl 			= get2('ctl') ? get2('ctl') : 'index';
$gbl_act 			= get2('act') ? strtolower(get2('act')) : 'index';
$gbl_ctl_name		= null;
$gbl_ctl_className	= null;
$gbl_tpl			= null;

if (strstr($gbl_ctl, '/'))
{
	$gbl_ctl	= explode('/', $gbl_ctl);
	foreach ($gbl_ctl as $key=>$value)
	{
		if ($key == count($gbl_ctl) - 1)
		{
			if (empty($value)) $value = $gbl_ctl[$key] = 'index';
			$gbl_ctl_name .= "ctl.$value.php";
			$gbl_ctl_className = $value;
		}
		else
		{
			$gbl_ctl_name	.= "$value/";
		}
		$gbl_tpl .= "$value/";
	}
}
else
{
//    echo get2('act');exit;
//    echo $gbl_ctl;exit;
	$gbl_ctl_name = "ctl.$gbl_ctl.php";
	$gbl_ctl_className = $gbl_tpl = $gbl_ctl;
	$gbl_tpl .= '/';
}

require_once(DATA_DIR.'config.inc.php');
require_once(CORE_DIR.'include/db_mysql.php');
require_once CORE_DIR."include/class.file.php";
require_once(CORE_DIR."smarty/Smarty.class.php");
require_once(CORE_DIR."corecms.php");
if ( $gbl_con == 'admin' ) {
	require_once CORE_DIR."$gbl_con/ctl.basePage.php";
}


if ( $gbl_con == 'api' ) {
    require_once CORE_DIR."$gbl_con/api.php";
}
//echo CORE_DIR."$gbl_con/$gbl_ctl_name";exit;
if (file_exists(CORE_DIR."$gbl_con/$gbl_ctl_name"))
{
	require_once(CORE_DIR."$gbl_con/$gbl_ctl_name");
	$ctl_name	= "ctl_$gbl_ctl_className";
	$ctl		= new $ctl_name();
	$mtd_name	= "{$gbl_act}_action";
	$class	= new ReflectionClass($ctl);  //使用了映射类，但没有直接读取文件进行分析速度快
	if (in_array('__call', getMethodsArray($class->getMethods())) || in_array($mtd_name, getMethodsArray($class->getMethods()))) {
		$ctl->$mtd_name();
	}
	else die('控制器不存在此动作！无法完成请求。');
}
else die('控制器不存在！无法完成请求。');

?>