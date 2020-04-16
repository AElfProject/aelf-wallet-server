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
	$gbl_ctl_name = "ctl.$gbl_ctl.php";
	$gbl_ctl_className = $gbl_tpl = $gbl_ctl;
	$gbl_tpl .= '/';
}

function show404() {
	echo '404'; exit;
	require_once( CORE_DIR.'smarty/Smarty.class.php' );

	$smarty = new Smarty();
	$smarty->config_dir = $GLOBALS['TPL_SM_CONFIG_DIR'];
	$smarty->caching = $GLOBALS['TPL_SM_CACHEING'];
	$smarty->template_dir = $GLOBALS['TPL_SM_TEMPLATE_DIR'];
	$smarty->compile_dir = $GLOBALS['TPL_SM_COMPILE_DIR'];
	$smarty->cache_dir = $GLOBALS['TPL_SM_CACHE_DIR'];
	$smarty->left_delimiter = $GLOBALS['TPL_SM_DELIMITER_LEFT'];
	$smarty->right_delimiter = $GLOBALS['TPL_SM_DELIMITER_RIGHT'];
	$smarty->force_compile = false;

	$smarty->assign( 'UPLOAD_PATH', UPLOAD_PATH );
	$smarty->assign( 'STATIC_PATH', STATIC_DIR );
	$smarty->assign( 'SKIN_PATH', HTTP_ROOT.'themes/'.STYLE );

	global $lang;
	$langStr = isset( $lang ) ? $lang : $_COOKIE['lang'];

	$smarty->assign( 'http_root', HTTP_ROOT );
	$smarty->assign( 'http_root_www', HTTP_ROOT_WWW );

	header( 'HTTP/1.1 404 Not Found' );
	header( 'Status: 404 Not Found' );
	$smarty->display( '404.htm' );
	exit;
}

require_once(DATA_DIR.'config.inc.php');
require_once CORE_DIR."include/class.file.php";
require_once(CORE_DIR."base.php");
require_once(CORE_DIR."web/web.php");

/*80-83行升级避免提示*/
$path = empty($_SERVER['PATH_INFO'])?null : $_SERVER['PATH_INFO'];

$_path2018 =  explode( '.', $path );
$ext = end($_path2018);
if ( strpos( $path, '.' ) > -1 && $ext != 'html' ) show404();
if ( $ext == 'html' ) $path = substr( $path, 1, 0 - strlen( '.'.$ext ) );
else $path = substr( $path, 1 );
$pathArr = $path ? explode( '/', $path ) : array();
if ( empty( $pathArr ) ) $pathArr = array( 'index', 'index' );

$parse = array(
	'path' => array( strtolower( $pathArr[0] ), strtolower( $pathArr[1] ) ),
	'query' => array(),
);
$queryArr = array();
for ( $i = 2; $i <= count( $pathArr ) - 1; $i += 2 ) {
	$queryArr[$pathArr[$i]] = $pathArr[$i+1];
}
$queryArr = array_merge( $queryArr, $_GET );
$parse['query'] = $_GET = $queryArr;

foreach ( $parse['path'] as $s ) {
	if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $s ) ) show404();
}



$pathArr = array_filter($pathArr);
if (count($pathArr) > 2){
     $fileName = array_pop($pathArr);
     $file = 'core/web/' . implode('/', $pathArr) . '/'.$fileName.'.php';
     $className = implode('_', $pathArr) . '_' . $fileName;
}else {
    $file = 'core/web/' . $parse['path'][0] . '/' . $parse['path'][1] . '.php';
    $className = $parse['path'][0] . '_' . $parse['path'][1];
}

if ( !file_exists( $file ) ) show404();
require_once $file;
if ( !class_exists( $className ) ) show404();
$app = new $className;

$app->before();
$app->doRequest();
$app->after();

?>