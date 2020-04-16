<?php

date_default_timezone_set( 'PRC' );

if(stripos($_SERVER['HTTP_HOST'],"localhost")===FALSE){
	//生成环境
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));
}else{
	//测试环境
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$CONFIG = ARRAY();
ob_start();
header("Content-Type:text/html;charset=utf-8;");


//管理员邮箱
define( 'ADMIN_EMAIL', '' );
//网站名称
define( 'SITE_NAME', '' );

//根目录
define('BASE_DIR', '/');

define('DEBUG', 1);

//针对IIS的Rewrite3，REQUEST_URI错误问题
if ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
	$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
}

//如果document_root是错误的路径，就用下面这段
//$_SERVER['DOCUMENT_ROOT'] = dirname( $_SERVER['SCRIPT_FILENAME'] );
//初始化可能不存在的变量,避免错误提示
empty($_SERVER['REQUIST_URI']) ? $_SERVER['REQUIST_URI']="" : null;

define('HTTP_ROOT', ( !empty($_SERVER['HTTPS']) ? 'https' : 'http' )."://".$_SERVER['HTTP_HOST'].'/'.BASE_DIR);
define('HTTP_ROOT_WWW', $_SERVER['REQUIST_URI'].'/'.BASE_DIR);
define('DOC_DIR', $_SERVER['DOCUMENT_ROOT'].'/'.BASE_DIR);
define('CORE_DIR', DOC_DIR.'core/');
define('DATA_DIR', DOC_DIR.'data/');
define('UPDATE_DIR', DOC_DIR.'data/upload/');
define('SKIN_DIR', HTTP_ROOT.'themes/');
define('HTML_DIR', DOC_DIR.'html/');
define('STATIC_DIR', HTTP_ROOT.'static/');
define('UPLOAD_PATH', '/'.BASE_DIR.'data/upload/');
define('CLASS_LEN', 3);
define('REWRITE', true);

//模板
define( 'LANGS', serialize( array( array( 'id' => 'zh-cn', 'name' => '简体中文' ), array( 'id' => 'en', 'name' => 'English' ),array( 'id' => 'ko', 'name' => '한국어.' )  ) ) );
$define_langs = unserialize( LANGS );
$langs = array();
foreach ( $define_langs as $l ) {
	$langs[] = $l['id'];
}

/*前台语言*/
$lang = empty($_GET['lang'])?null:trim( $_GET['lang'] );
if ( ! empty( $lang ) ) {
	if ( ! in_array( $lang, $langs ) ) {
		$lang = $define_langs[0]['id'];
	}
	setcookie( 'lang', $lang, time() + 60 * 60, '/' );
	//$_SESSION['lang'] = $lang;
}
else {
	//$lang = $_SESSION['lang'];
	$lang = $_COOKIE['lang'];
	if ( ! in_array( $lang, $langs ) ) {
		$lang = $define_langs[0]['id'];
		setcookie( 'lang', $lang, time() + 60 * 60, '/' );
	}
}
if ( empty( $lang ) ) {
	$lang = $define_langs[0]['id'];
	setcookie( 'lang', $lang, time() + 60 * 60, '/' );
	//$_SESSION['lang'] = $lang;
}

/*后台语言*/
$admin_lang = empty($_GET['admin_lang']) ? null : trim( $_GET['admin_lang'] );

if ( ! empty( $admin_lang ) ) {
	if ( ! in_array( $admin_lang, $langs ) ) {
		$admin_lang = $define_langs[0]['id'];
	}
	setcookie( 'admin_lang', $admin_lang, time() + 60 * 60, '/' );
	//$_SESSION['admin_lang'] = $admin_lang;
}
else {
	//$admin_lang = $_SESSION['admin_lang'];
	$admin_lang = $_COOKIE['admin_lang'];
	if ( ! in_array( $admin_lang, $langs ) ) {
		$admin_lang = $define_langs[0]['id'];
		setcookie( 'admin_lang', $admin_lang, time() + 60 * 60, '/' );
	}
}
if ( empty( $admin_lang ) ) {
	$admin_lang = $define_langs[0]['id'];
	setcookie( 'admin_lang', $admin_lang, time() + 60 * 60, '/' );
	//$_SESSION['admin_lang'] = $admin_lang;
}

$style = $lang;
/* 如果手机版和PC版不同模板
$wap = (int)$_GET['wap'];*/
$wap = empty($wap)?null:$wap;
define( 'STYLE', $style.'/'.( $wap ? 'wap/' : '' ) );
define('TPL_DIR', DOC_DIR.'themes/'.STYLE);

//加密前后辍，不能修改
$KEY_	= '^&#*&^L1Iip5M7dPkcNcQ9UXT1';
$_KEY	= '1TMYUyCsT@&^%vxCIho';

$SQL = array(
	'and',
	'or',
	'select',
	'update',
	'from',
	'where',
	'order',
	'by',
	'delete',
	'\'',
	'insert',
	'into',
	'values',
	'create',
	'table',
	'database'
);

$TPL_SM_CONFIG_DIR		= CORE_DIR."Smarty/Config_File.class.php";
$TPL_SM_CACHEING		= false;
$TPL_SM_TEMPLATE_DIR	= TPL_DIR;
$TPL_SM_COMPILE_DIR		= DATA_DIR.'tpl_compile';
$TPL_SM_CACHE_DIR		= DATA_DIR.'tpl_cache';
$TPL_SM_DELIMITER_LEFT	= '<{';
$TPL_SM_DELIMITER_RIGHT	= '}>';

require_once( CORE_DIR.'include/WebUtility.php' );
require_once( CORE_DIR.'v2.1/ParseURL.php' );
require_once( CORE_DIR.'include/global.php' );

?>
