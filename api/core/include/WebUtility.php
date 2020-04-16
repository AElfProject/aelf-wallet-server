<?php

function u2g ($s)
{ 
	return mb_convert_encoding($s, "gb2312", "utf-8");
}

function g2u ($s)
{
	return mb_convert_encoding($s, "utf-8", "gb2312");
}

function left ($str, $len, $rev = false)
{
	if($len > strlen($str))
	{
		$len = strlen($str);
	}
	if ($rev)
	{
		return right($str, strlen($str) - $len);
	}

	return substr($str, 0, $len);
}

function right ($str, $len, $rev = false)
{
	if($len > strlen($str))
	{
		$len = 0;
	}
	if ($rev)
	{
		return left($str, strlen($str) - $len);
	}

	return substr($str, strlen($str) - $len);
}

function mid ($s, $st, $l = null)
{
	if($l == null) return substr($s, $st);
	if($l > strlen($s) - $st) return substr($s, $st);
	return substr($s, $st, $l);
}

function interception ($s, $st, $se)
{
	if ($se < $st) return substr($s, $st);
	return substr($s, $st, $se - $st);
}

/**
* 自定义获取字符串长度，中文按照实际所占字符计算
* 适合小段文字，大段文字速度较慢
*/
function nwstrlen( $str ) {
	$i = 0;
	for ( $j = 0; $j < strlen( $str ); $j++ ) {
		$i++;
		$ascii = ord( substr( $str, $j, 1 ) );
		if ( $ascii >= 224 ) {
			$j += 2;
		}
		else if ( $ascii >= 192 ) {
			$j++;
		}
	}
	return $i;
}

function cutstr ($str, $len, $pad = '...')
{
	if ( strlen($str) <= $len ) return $str;

	$tmpstr = '';

	for ($i = 0; $i < $len; $i++)
	{
		$ascii = ord( substr( $str, $i, 1 ) );
		if ( $ascii >= 224 ) {
			$tmpstr .= substr($str, $i, 3);
			$i += 2;
		}
		else if ( $ascii >= 192 ) {
			$tmpstr .= substr($str, $i, 2);
			$i++;
		}
		else {
			$tmpstr .= substr($str, $i, 1);
		}
	}

	return $tmpstr.$pad;
}

function utf8substr($str, $len, $from = 0, $pad = '..')
{
	$str1 = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1',$str);
	if (strlen($str1) < strlen($str)) return $str1.$pad;
	else return $str1;
}

function utf8substr_removeHTML($str, $len, $from = 0, $pad = '..')
{
	$str	= strip_tags($str);
	$str1	= preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1',$str);
	if (strlen($str1) < strlen($str)) return $str1.$pad;
	else return $str1;
}

function rebuild_array ($arr)
{
	static $tmp = array();

	for ($i = 0; $i < count($arr); $i++)
	{
		if (is_array($arr[$i])) rebuild_array($arr[$i]);
		else $tmp[] = $arr[$i];
	}

	return $tmp;
}

//remove repeat elements
function array_distinct ($arr)
{
	$tmp_arr = array();

	for($i = 0; $i < count($arr); $i++)
	{
		if(!in_array($arr[$i], $tmp_arr)) $tmp_arr[] = $arr[$i];
	}

	return $tmp_arr;
}

//when the get_file_contents() failed! you can use this function.
function get_http ($host, $url)
{

	//$host : 'mp3.sogou.com'
	//$url  : '/music.so?query=xxx'
	$fp = fsockopen($host, 80, $errno, $errstr, 30);

	if(!$fp) echo "$errstr ($errno)<br />\n";
	else
	{
		$out	= "GET $url HTTP/1.0\r\n";
		$out	.= "Host: $host\r\n";
		$out	.= "Connection: Close\r\n\r\n";
		fputs($fp, $out);

		$str = "";
		while(!feof($fp)) $str .= fgets($fp, 128);
			fclose($fp);
	}

	return $str;
}

function ip ()
{
	$unknown = 'unknown';

	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown) )
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown))
		$ip = $_SERVER['REMOTE_ADDR'];
	$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches)?$matches[0]:$unknown;
	if ( false !== strpos($ip, ',') ) $ip = reset( explode(',', $ip) );

	return $ip;
}

function md6 ($s)
{
	global $CONFIG;
	return md5( $CONFIG['KEY_'] . strrev($s) . $CONFIG['_KEY'] );
}

function process_variables (&$val, $key)
{
	if (is_array($val))
	{
		foreach ($val as $k => $v)
		{
			process_variables($v, $k);
		}
	}
	else
	{
		$val = addslashes($val);
	}
}

function formatDate ($ymd, $date)
{
	$date = strtotime($date) ? strtotime($date) : $date;
	return date($ymd, $date);
}

function limitInt ($int, $min, $max = null)
{
	if ($max == null)
	{
		if ($int < $min) return $min;
		return $int;
	}
	if ($min >= $max) return $max;

	if ($int < $min) $int = $min;
	if ($int > $max) $int = $max;

	return $int;
}

function get2 ($key = null)
{
    if(!empty($key)){
        return empty($_GET[$key]) ? null : $_GET[$key];
    }
    return $_GET;
}

function post ($key = null)
{
	if (isset($key)) return $_POST[$key];
	return $_POST;
}

function request ($key = null)
{
	if (isset($key)) return $_REQUEST[$key];
	return $_REQUEST;
}

function files ($key = null)
{
	if (isset($key)) return $_FILES[$key];
	return $_FILES;
}

function session ($key = null)
{
	if (isset($key)) return $_SESSION[$key];
	return $_SESSION;
}

function is_post ()
{
	return $_SERVER["REQUEST_METHOD"] == "POST";
}

function getModel ($sys_name = null)
{
	if (empty($sys_name) || !isset($sys_name)) return false;

	$sys_path 		= null;
	$sys_className	= null;

	if (strpos($sys_name, '/'))
	{
		$sys_name	= explode('/', $sys_name);
		foreach ($sys_name as $key=>$value)
		{
			if ($key == count($sys_name) - 1)
			{
				if (empty($value)) $value = 'index';
				$sys_path		.= "mdl.$value.php";
				$sys_className	= "mdl_$value";
			}
			else
			{
				$sys_path	.= "$value/";
			}
		}
	}
	else
	{
		$sys_path		= "mdl.$sys_name.php";
		$sys_className	= "mdl_$sys_name";
	}

	return array(
		'path'		=> $sys_path,
		'classname'	=> $sys_className
	);
}

function warning ($msg, $url = '')
{
	include_once(CORE_DIR.'include/class.httpService.php');
	$httpService = new httpService(HTTP_ROOT."?con=admin&ctl=common/warning&msg=".urlencode($msg)."&url=".urlencode($url));
	echo $httpService->result();
	unset($httpService);
	exit;
}

function getMethodsArray ($methods)  //映射对象的方法
{
	$mArray = array();
	foreach ($methods as $meth) $mArray[] = $meth->getName();
	return $mArray;
}

function getIntervalDays ($type)
{
	switch ($type)
	{
		case 'today' :
			return array(
				mktime(0, 0, 0, date('m'), date('d'), date('y')),
				mktime(23, 59, 59, date('m'), date('d'), date('y'))
			);
			break;
		case 'week' :
			return array(
				mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('y')),
				mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('y'))
			);
			break;
		case 'lastweek' :
			return array(
				mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('y')),
				mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('y'))
			);
			break;
		case 'month' :
			return array(
				mktime(0, 0 , 0, date('m') , 1, date('y')),
				mktime(23, 59, 59, date('m'), date('t'), date('y'))
			);
		case 'lastmonth' :
			return array(
				mktime(0, 0 , 0, date('m') - 1, 1, date('y')),
				mktime(23, 59, 59, date('m'), 0, date('y'))
			);
		default : return array();
	}
}

function getFullUrl ($list, $parentUrl)  //获取完整的URL地址
{
	if (is_array($list))
	{
		$array = array();
		foreach ($list as $key=>$value)
		{
			$url = filter_url($parentUrl, $value);
			if (!empty($url)) $array[] = $url;
		}
		return $array;
	}
	else
	{
		return filter_url($parentUrl, $list);
	}
}

function filter_url ($cUrl, $url)
{
	$outUrl		= '';
	$cUrls		= parse_url($cUrl);
	$host		= ((!isset($cUrls['port']) || $cUrls['port'] == '80') ? $cUrls['host'] : $cUrls['host'].':'.$cUrls['port']);
	$baseUrl	= $host;

	$paths		= explode('/', eregi_replace("^http://", '', $cUrl));
	$cnt		= count($paths);

	for ($i = 1; $i < ($cnt - 1); $i++)
	{
		if(!ereg("[\?]", $paths[$i])) $baseUrl .= '/'.$paths[$i];
	}
	if(!ereg("[\?\.]", $paths[$n - 1]))
	{
		$baseUrl .= '/'.$paths[$n - 1];
	}

	$p	= strpos($url, "#");
	if ($p > 0) $url = substr($url, 0, $p);
	if ($url[0] == '/')
	{
		$outUrl = $host.$url;
	}
	else if ($url[0] == '.')
	{
		if (strlen($url) <= 2) return '';
		else if ($url[1] == '/') $outUrl = $baseUrl.ereg_replace('^.', '', $url);
		else $outUrl = $baseUrl.'/'.$url;
	}
	else
	{
		if (strlen($url) < 7) $outUrl = $baseUrl.'/'.$url;
		else if (eregi('^http://', $url)) $outUrl = $url;
		else $outUrl = $baseUrl.'/'.$url;
	}
	$outUrl = eregi_replace('^http://', '', $outUrl);
	$outUrl = 'http://'.eregi_replace('/{1,}', '/', $outUrl);

	return $outUrl;
}

function get_url_content ($url)
{
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_URL, $url);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
	$result = curl_exec($curlHandle);
	curl_close($curlHandle);
	return $result;
}

//发送邮件
function send_mail ( $email, $subject, $content, $header, $from ) {
	/*if ( empty( $header ) ) {
		$header = "MIME-Version: 1.0\n";
		$header .= "Content-type: text/html; charset=iso-utf-8\n";
		$header .= "X-Priority: 3\n";
		$header .= "X-MSMail-Priority: Normal\n";
		$header .= "X-Mailer: PHP/"."MIME-Version: 1.0\n";
		$header .= "From: " . $from . "\n";
		$header .= "Content-Type: text/html\n";
	}*/
	if ( empty( $header ) ) {
		return mail( $email, $subject, $content );
	}
	else return mail( $email, $subject, $content, $header );
}

//强化版的strip_tags，可以清除word带过来的标签
function strip_tags_upgrade( $content, $allowtags = '' ) {
	if ( !function_exists( 'mb_regex_encoding' ) ) return strip_tags( $content );

	mb_regex_encoding('UTF-8');
	$search = array('/‘/u', '/’/u', '/“/u', '/”/u', '/—/u');
	$replace = array('\'', '\'', '"', '"', '-');
	$content = preg_replace( $search, $replace, $content );
	$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
	if ( mb_stripos( $content, '/*' ) !== FALSE ) {
		$content = mb_eregi_replace( '#/\*.*?\*/#s', '', $content, 'm' );
	}
	$content = preg_replace( array( '/<([0-9]+)/' ), array( '< $1' ), $content );
	$content = strip_tags( $content, $allowtags );
	$content = preg_replace( array( '/^\s\s+/', '/\s\s+$/', '/\s\s+/u' ), array( '', '', ' ' ), $content );
	$search = array( '#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu', '#<(em|i)[^>]*>(.*?)</(em|i)>#isu', '#<u[^>]*>(.*?)</u>#isu' );
	$replace = array( '<b>$2</b>', '<i>$2</i>', '<u>$1</u>' );
	$content = preg_replace( $search, $replace, $content );
	$num_matches = preg_match_all( "/\<!--/u", $content, $matches );
	if ( $num_matches ) {
		$content = preg_replace( '/\<!--(.)*--\>/isu', '', $content );
	}
	return $content;
}

//简化版的curl
function https_request( $url, $data = null ) {
	//$data是字符串，则application/x-www-form-urlencoded
	//$data是数组，则multipart/form-data
	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
	if ( !empty( $data ) ) {
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
	}
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$output = curl_exec( $curl );
	curl_close( $curl );
	return $output;
}



function getUserDevice( $ua = null ) {
	if ( ! isset( $ua ) ) {
		$ua = $_SERVER['HTTP_USER_AGENT'];
	}
	$iphone = strstr( strtolower( $ua ), 'mobile' );
	$android = strstr( strtolower( $ua ), 'android' );
	$windowsPhone = strstr( strtolower( $ua ), 'phone' );
	$microMessenger = strstr( strtolower( $ua ), 'micromessenger' );

	if ( $microMessenger ) return 'wechat';

	$androidTablet = androidTablet( $ua );
	$ipad = strstr( strtolower( $ua ), 'ipad' );

	if ( $androidTablet || $ipad ) {
		return 'tablet';
	}
	elseif ( $iphone && ! $ipad || $android && ! $androidTablet || $windowsPhone ) {
		return 'mobile';
	}
	else {
		return 'desktop';
	}
}

function androidTablet( $ua ) {
	if ( strstr( strtolower( $ua ), 'android' ) ) {
		if ( ! strstr( strtolower( $ua ), 'mobile' ) ) {
			return true;
		}
	}
}

function smtp_mail( $email, $subject, $body, $host, $port, $un, $up, $fromEmail = '', $fromName = '', $attachments = '' ) {
	require_once 'core/phpmailer/class.phpmailer.php';
	require_once 'core/phpmailer/class.smtp.php';

	$mail = new PHPMailer();
	//$mail->SMTPDebug = 4;
	$mail->IsSMTP();
	$mail->Host = $host;
	$mail->Port = $port;
	if ( $port == '465' ) $mail->SMTPSecure = 'ssl';
	$mail->SMTPAuth = true;
	$mail->Username = $un;
	$mail->Password = $up;

	$mail->Sender = $fromEmail ? $fromEmail : $email;
	$mail->From = $fromEmail ? $fromEmail : $email;
	$mail->FromName = $fromName ? $fromName : $mail->From;
	if ( $fromEmail ) $mail->setFrom( $fromEmail, $fromName );
	$mail->AddReplyTo( $fromEmail ? $fromEmail : $email, $fromName ? $fromName : $mail->From );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$mail->AddAttachment( $attachment['file'], $attachment['name'] );
		}
	}

	$mail->CharSet = 'UTF-8';
	$mail->Encoding = 'base64';

	$mail->AddAddress( $email, $email );

	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AltBody = 'text/html';

	if ( $mail->Send() ) return true;
	else {
		return $mail->ErrorInfo;
	}
}

function Xml2Array( $xmlObj ) {
	$result = array();
	$array = $xmlObj;
	if ( get_class($array) == 'SimpleXMLElement' ) {
		$array = get_object_vars( $xmlObj );
	}
	if ( is_array( $array ) ) {
		if ( count( $array ) <= 0 ) {
			return trim( strval( $xmlObj ) );
		}
		foreach ( $array as $key => $val ) {
			$result[$key] = Xml2Array( $val );
		}
		return $result;
	}
	else {
		return trim( strval( $array ) );
	}
}

function is_ajax() {
	return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' );
}

function __( $key ) {
	if ( empty( $key ) ) return false;

	$args = array();
	foreach ( func_get_args() as $i => $arg ) {
		if ( $i > 0 ) $args[] = $arg;
	}

	require_once 'core/v2.1/csv.php';

	global $gbl_con, $admin_lang, $lang;

	if ( $gbl_con == 'admin' ) {
		$admin_lang = isset( $admin_lang ) ? $admin_lang : $_COOKIE['admin_lang'];
		$file = 'core/lang/admin/'.$admin_lang.'.csv';
	}
	else {
		$lang = isset( $lang ) ? $lang : $_COOKIE['lang'];
		$file = DOC_DIR.'core/lang/'.$lang.'.csv';
	}

	if ( $lang == 'zh-cn' ) return vsprintf( $key, $args );

	$line = CSV::getByKey( $key, $file );

	if ( !$line ) return vsprintf( $key, $args );
	return vsprintf( $line[1], $args );
}

function ___( $key, $lang = '' ) {
	if ( empty( $key ) ) return false;

	$args = array();
	foreach ( func_get_args() as $i => $arg ) {
		if ( $i > 1 ) $args[] = $arg;
	}

	require_once 'core/v2.1/csv.php';

	if ( empty( $lang ) ) $lang = 'zh-cn';

	if ( $lang == 'zh-cn' ) return vsprintf( $key, $args );

	$file = DOC_DIR.'core/lang/'.$lang.'.csv';
	$line = CSV::getByKey( $key, $file );

	if ( !$line ) return vsprintf( $key, $args );
	return vsprintf( $line[1], $args );
}

function getTimeStr( $time ) {
	if ( $time < 60 ) return $time.__( '秒' );
	if ( $time < 3600 ) return number_format( $time / 60, 0 ).__( '分' );
	return number_format( $time / 3600, 0 ).__( '小时' );
}

function authcode( $string, $operation = 'd', $key = '', $expiry = 0 ) {
	$ckey_length = 4;

	//密匙
	$key = md5( $key );

	$keya = md5( substr( $key, 0, 16 ) );
	$keyb = md5( substr( $key, 16, 16 ) );
	$keyc = $ckey_length ? ( $operation == 'd' ? substr( $string, 0, $ckey_length ): substr( md5( microtime() ), -$ckey_length ) ) : '';
	$cryptkey = $keya.md5( $keya.$keyc );
	$key_length = strlen( $cryptkey );
	$string = $operation == 'd' ? base64_decode( substr( $string, $ckey_length ) ) : sprintf( '%010d', $expiry ? $expiry + time() : 0 ).substr( md5( $string.$keyb ), 0, 16 ).$string;
	$string_length = strlen( $string );
	$result = '';
	$box = range( 0, 255 );
	$rndkey = array();
	for ( $i = 0; $i <= 255; $i++ ) {
		$rndkey[$i] = ord( $cryptkey[$i % $key_length] );
	}
	for ( $j = $i = 0; $i < 256; $i++ ) {
		$j = ( $j + $box[$i] + $rndkey[$i] ) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for ( $a = $j = $i = 0; $i < $string_length; $i++ ) {
		$a = ( $a + 1 ) % 256;
		$j = ( $j + $box[$a] ) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr( ord( $string[$i] ) ^ ( $box[( $box[$a] + $box[$j] ) % 256] ) );
	}
	if ( $operation == 'd' ) {
		if ( ( substr( $result, 0, 10 ) == 0 || substr( $result, 0, 10 ) - time() > 0 ) && substr( $result, 10, 16 ) == substr( md5( substr( $result, 26 ).$keyb ), 0, 16 ) ) {
			return substr( $result, 26 );
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace( '=', '', base64_encode( $result ) );
	}
}

?>