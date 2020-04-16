<?php

require_once 'core/web/base.php';

class corecms extends base
{

	protected $tpl;
	protected $file;
	private $classlen = CLASS_LEN;
	private $url_parses = array();
	protected $cookie;
	protected $lang;
	protected $configs = array();

	protected $formError = array();
	protected $formReturn = array();
	protected $formData = array();

	function corecms ()
	{
		$GLOBALS['system'] = &$this;
		$this->tpl = new Smarty();
		$this->file = new file();
		require_once( CORE_DIR.'v2.1/Cookie.php' );
		$this->cookie = new Cookie;
		if ( $GLOBALS['gbl_con'] == 'admin' ) {
			//$this->lang = simplexml_load_file(CORE_DIR.'lang/admin/'.$_SESSION['admin_lang'].'.xml');
			$this->lang = simplexml_load_file(CORE_DIR.'lang/admin/'.$this->getAdminLangStr().'.xml');
		}
		else {
			//$this->lang = simplexml_load_file(CORE_DIR.'lang/'.$_SESSION['lang'].'.xml');
			$this->lang = simplexml_load_file(CORE_DIR.'lang/'.$_COOKIE['lang'].'.xml');
		}
		$this->setData($this->lang, 'lang');
		$this->setData( $this->getLangStr(), 'langStr' );

		$this->tpl->config_dir		= &$GLOBALS['TPL_SM_CONFIG_DIR'];
		$this->tpl->caching			= &$GLOBALS['TPL_SM_CACHEING'];
		$this->tpl->template_dir	= &$GLOBALS['TPL_SM_TEMPLATE_DIR'];
		$this->tpl->compile_dir		= &$GLOBALS['TPL_SM_COMPILE_DIR'];
		$this->tpl->cache_dir		= &$GLOBALS['TPL_SM_CACHE_DIR'];
		$this->tpl->left_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_LEFT'];
		$this->tpl->right_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_RIGHT'];
		$this->tpl->force_compile	= false;  //为true则每次刷新页面都编译模板文件，而false后，由默认compile_check=true会自动检测模板的修改时间，若模板被修改过，会自动生成编译文件

		$mdl_config_data = $this->loadModel( 'config_data' );
		$this->configs = $mdl_config_data->getList();
		$this->setData( $this->configs, 'configs' );

		$formErrorMsg = session( 'form-error-msg' );
		$formSuccessMsg = session( 'form-success-msg' );
		$this->session( 'form-error-msg', '' );
		$this->session( 'form-success-msg', '' );
		if ( $formErrorMsg ) {
			$this->formReturn['success'] = false;
			$this->formReturn['msg'] = $formErrorMsg;
			$this->setData( $this->formReturn, 'formReturn' );
		}
		elseif ( $formSuccessMsg ) {
			$this->formReturn['success'] = true;
			$this->formReturn['msg'] = $formSuccessMsg;
			$this->setData( $this->formReturn, 'formReturn' );
		}
	}

	/**
	 * 保存日志到文件中
	 */
	protected function logFile( $data, $level = '' ) {
		if ( is_array( $data ) ) {
			if ( !isset( $data['ip'] ) ) $data['ip'] = ip();
			if ( !isset( $data['get_url'] ) ) $data['get_url'] = $_SERVER['REQUEST_URI'];
		}
		else {
			$data = array(
				'data' => $data,
				'ip' => ip(),
				'get_url' => $_SERVER['REQUEST_URI'],
			);
		}
		return file_put_contents( 'logs/web/'.date( 'Ymd' ).( $level ? '.'.$level : '' ).'.log', date( 'Y-m-d H:i:s' ).' '.json_encode( $data ).PHP_EOL, FILE_APPEND );
	}

	protected function getRouteStr() {
		return $GLOBALS['gbl_con'].'_'.$GLOBALS['gbl_ctl'].'_'.$GLOBALS['gbl_act'];
	}

	/**
	 * post的时候 if ( !$this->verifyFormKey( '', $_POST['formKey'] ) ) $this->sheader( $this->parseUrl()->toString() );
	 * get的时候 $this->setData( $this->getFormKey(), 'formKey' );
	 */

	/**
	 * 生成表单key
	 */
	protected function getFormKey( $formName ) {
		if ( empty( $formName ) ) $formName = $this->getRouteStr();

		if ( empty( $formName ) ) return false;
		$formName = preg_replace( '/[^a-zA-Z0-9_]/', '', $formName );

		//$key = 'paylinx.today.nie';

		//加入时间
		$_SESSION['form_name_'.$formName] = time();
		$data = $formName.$_SESSION['form_name_'.$formName];

		return $this->md5( $data );
	}

	/**
	 * 验证表单key
	 */
	protected function verifyFormKey( $formName = '', $data ) {
		if ( empty( $formName ) ) $formName = $this->getRouteStr();

		if ( empty( $formName ) ) return false;
		if ( empty( $data ) ) return false;
		$formName = preg_replace( '/[^a-zA-Z0-9_]/', '', $formName );

		return $this->md5( $formName.$_SESSION['form_name_'.$formName] ) == $data;
	}

	protected function getAjaxReturn() {
		$result = array( 'status' => 0, 'msg' => '', 'error' => array(), 'jump' => '' );
		if ( $this->formError ) {
			$result['status'] = 500;
			foreach ( $this->formError as $fe ) $result['error'][] = $fe;
		}
		else if ( $this->formReturn ) {
			$result['status'] = $this->formReturn['success'] ? 200 : 500;
			$result['msg'] = $this->formReturn['msg'];
			$result['jump'] = $this->formReturn['jump'];
		}
		return $result;
	}

	protected function replaceTextByArray( $text, $array, $replace = '*' ) {
		if ( empty( $text ) || empty( $array ) ) return $text;
		$badwords = array_combine( $array, array_fill( 0, count( $array ), $replace ) );
		$tmp = strtr( $text, $badwords );
		return $tmp;
	}

	function getPostCodeByAddress( $address, $lang = 'en' ) {
		$result = file_get_contents( 'http://maps.google.com/maps/api/geocode/json?address='.urlencode( $address ).'&sensor=false&language='.$lang );
		$result = json_decode( $result );
		//print_r($result);exit;
		if ( $result->status != 'OK' ) return false;

		$postCode = '';
		foreach ( $result->results[0]->address_components as $ac ) {
			if ( $ac->types[0] == 'postal_code' ) {
				$postCode = $ac->short_name;
			}
		}
		return $postCode;
	}

	function getLocDetailByAddress( $address, $lang = 'en' ) {
		$result = file_get_contents( 'http://maps.google.com/maps/api/geocode/json?address='.urlencode( $address ).'&sensor=false&language='.$lang );
		$result = json_decode( $result );
		//print_r($result);exit;
		if ( $result->status != 'OK' ) return false;

		$details = array();
		$details['address'] = $result->results[0]->formatted_address;
		$details['pos'] = array( 'lat' => $result->results[0]->geometry->location->lat, 'lng' => $result->results[0]->geometry->location->lng );
		foreach ( $result->results[0]->address_components as $ac ) {
			switch ( (string)$ac->types[0] ) {
				case 'postal_code':
					$details['postCode'] = $ac->short_name;
					break;
				case 'street_number':
					$details['streetNumber'] = $ac->short_name;
					break;
				case 'route':
					$details['route'] = $ac->short_name;
					break;
				case 'locality':
					$details['locality'] = $ac->short_name;
					break;
				case 'administrative_area_level_2':
					$details['areaLevel2'] = $ac->short_name;
					break;
				case 'administrative_area_level_1':
					$details['areaLevel1'] = $ac->short_name;
					break;
				case 'country':
					$details['country'] = $ac->short_name;
					break;
			}
		}
		return $details;
	}

	function getLangStr() {
		if ( isset( $GLOBALS['lang'] ) ) {
			$lang = $GLOBALS['lang'];
		}
		else {
			$lang = trim( $_COOKIE['lang'] );
		}
		return $lang;
	}

	function getAdminLangStr() {
		if ( isset( $GLOBALS['admin_lang'] ) ) {
			$lang = $GLOBALS['admin_lang'];
		}
		else {
			$lang = trim( $_COOKIE['admin_lang'] );
		}
		return $lang;
	}

	protected function setTpl ($cache = false, $tplTemplatePath = null, $tplCompile = null, $tplCache = null)
	{
		$GLOBALS['TPL_SM_CACHEING'] = $cache;
		if ($tplTemplatePath)
		{
			$GLOBALS['TPL_SM_TEMPLATE_DIR'] = $tplTemplatePath;
		}
		if ($tplCompile)
		{
			$GLOBALS['TPL_SM_COMPILE_DIR'] = $tplCompile;
		}
		if ($tplCache)
		{
			$GLOBALS['TPL_SM_CACHE_DIR'] = $tplCache;
		}
	}

	protected function setData ($data, $name = null)
	{
		if (!isset($name) || $name === false) $name = 'data';
		$this->tpl->assign($name, $data);
	}

	protected function display ($page = null)
	{
		$this->setData(UPLOAD_PATH, "UPLOAD_PATH");
		$this->setData(STATIC_DIR, "STATIC_PATH");

		if ($GLOBALS['gbl_con'] == 'admin')
		{
			$this->setData(HTTP_ROOT."core/common/skin/admin/", 'SKIN_PATH');
			$this->setData(HTTP_ROOT_WWW, 'http_root_www');
			if (!isset($page) || $page === false) $page = $GLOBALS['gbl_tpl'].$GLOBALS['gbl_act'];
		}
		else
		{
			$this->setData(HTTP_ROOT.'themes/'.STYLE, "SKIN_PATH");
			if (!isset($page) || $page === false) $page = right($GLOBALS['gbl_tpl'], 1, true);
			if (empty($page)) $page = 'index';
		}
		if (file_exists($this->tpl->template_dir.'/'.$page.'.htm'))
			$this->tpl->display($page.".htm");
		else {
			if ( file_exists($this->tpl->template_dir.'/404.htm') ) {
				//如果404.htm存在，则输出404
				header( 'HTTP/1.1 404 Not Found' );
				header( 'Status: 404 Not Found' );
				$this->tpl->display("404.htm");
			}
			else {
				$this->sheader(null, $this->lang->system_parse_failed_template_not_exists.'<br />'.$this->lang->template_name.$this->lang->maohao.$page.'.htm');
			}
		}
	}

	protected function fetch ($page = null)
	{
		$this->setData(UPLOAD_PATH, "UPLOAD_PATH");
		$this->setData(STATIC_DIR, "STATIC_PATH");

		if ($GLOBALS['gbl_con'] == 'admin')
		{
			$this->setData(HTTP_ROOT."core/common/skin/admin/", 'SKIN_PATH');
			if (!isset($page) || $page === false) $page = $GLOBALS['gbl_tpl'].$GLOBALS['gbl_act'];
		}
		else
		{
			$this->setData(HTTP_ROOT.'themes/'.STYLE, "SKIN_PATH");
			if (!isset($page) || $page === false) $page = right($GLOBALS['gbl_tpl'], 1, true);
			if (empty($page)) $page = 'index';
		}
		if (file_exists($this->tpl->template_dir.'/'.$page.'.htm'))
			return $this->tpl->fetch($page.".htm");
		else
			$this->sheader(null, $this->lang->system_parse_failed_template_not_exists.'<br />'.$this->lang->template_name.$this->lang->maohao.$page.'.htm');
	}

	public function sheader ($url = null, $msg = null, $time = 3000)
	{
		if (!isset($msg)) $time = 0;
		if (!isset($url)) $url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';

		ob_end_clean();
		if ($time > 0 || empty($url)) header("Location: ".HTTP_ROOT."?con=admin&ctl=common/warning&msg=".urlencode($msg)."&url=".urlencode($url));
		else header("Location: $url");
		exit;
	}

	protected function parseUrl( $url = null ) {
		if ( empty( $url ) ) {
			$url = 'current';
		}
		if ( ! isset( $this->url_parses[$url] ) ) {
			$this->url_parses[$url] = new ParseURL( $url == 'current' ? null : $url );
		}
		return clone $this->url_parses[$url];
	}

	protected function validate( $data ) {
		require_once( CORE_DIR.'v2.1/Validate.php' );
		$validate = new Validate( $this->validates, $data );
		if ( ! $validate->valid()  ) {
			$this->sheader( null, $validate->getValidateErrors() );
		}
	}

	protected function page ($sql, $pageUrl, $pageSize, $maxPage, $pageStyle = '', $count = -1)
	{
		$pageUrl		= $this->parseUrl()->set( 'page' )->set( 'perPageCount' );
		$perPageCount	= (int)get2('perPageCount');
		if ($perPageCount > 0)
		{
			$pageSize	= $perPageCount;
			$pageUrl	.= "perPageCount={$perPageCount}&";
		}
		$page			= (int)get2('page');
		$pageUrl		= $pageUrl."page=";

		if (!file_exists(TPL_DIR . '/'.$pageStyle) || empty($pageStyle))
			$pageStyle = CORE_DIR.'common/skin/pagestyle00.htm';

		$recordCount	= $count > -1 ? $count : $this->db->cnt($this->db->query($sql));
		$pageCount		= ceil($recordCount / $pageSize);
		if ( $page > $pageCount ) {
			$page = $pageCount;
		}
		if ( $page < 1 ) {
			$page = 1;
		}
		$page_l			= ceil($page - $maxPage / 2);
		$page_r			= $page_l + $maxPage;
		if ($page_l < 1) $page_l = 1;
		if ($page_r > $pageCount) $page_r = $pageCount;

		$this->setData($recordCount, 'rc');
		$this->setData($page > 1 ? 1 : 0, 'sy');
		$this->setData($page == $pageCount ? 0 : 1, 'wy');
		$this->setData($page, 'cp');
		$this->setData($pageCount, 'pc');
		$this->setData($pageSize, 'ps');
		$this->setData($pageUrl, 'url');
		$this->setData($htm, 'htm');	//针对静态页路径

		while($page_l <= $pageCount && $page_l <= $page_r)
		{
			$pg[] = array (
				'isCurrent'	=> $page_l == $page ? 1 : 0,
				'url'		=> $page_l == $page ? 'javascript:void(0);' : $pageUrl.$page_l,
				'nr'		=> $page_l
			);
			$page_l++;
		}
		$this->setData($pg, 'pg');

		$pageStr	= $this->tpl->fetch($pageStyle);
		$outSql		= $sql.' limit '.(($page - 1) * $pageSize).','.$pageSize;
		$pc			= $pageCount;

		return array(
			'pageStr'	=> $pageStr,
			'outSql'	=> $outSql,
			'pc' 		=> $pc
		);
	}

	public function loadModel ($mdl_name)
	{
		if ($mdl_info = getModel($mdl_name))
		{
			if (file_exists(CORE_DIR."model/".$mdl_info['path']))
			{
				include_once CORE_DIR."model/mdl.base.php";
				include_once CORE_DIR."model/".$mdl_info['path'];
				$mdl = new $mdl_info['classname']($this);
				return $mdl;
			}
			else die('数据模型丢失');
		} else return null;
	}

	public function loadConf ($cfg_name)
	{
		$cfg_path = DATA_DIR."conf/$cfg_name";
		if (file_exists($cfg_path))
		{
			return $this->file->readfile($cfg_path);
		}
		else return null;
	}

	public function saveConf ($cfg_name, $content)
	{
		$cfg_path = DATA_DIR."conf/$cfg_name";
		return $this->file->createfile($cfg_path, $content);
	}

	protected function chkfile ($filename)
	{
		return file_exists($filename);
	}

	protected function httpService ($url, $data = array(), $abort = false)
	{
		include_once CORE_DIR."include/class.httpService.php";
		return new httpService($url, $data, $abort);
	}

	protected function gettime ($style = 'Ymd')
	{
		return date($style, time());
	}

	public function createRnd ($length = 6)
	{
		$rnd = '';
		while (strlen($rnd) < $length)
		{
			$rnd .= mt_rand();
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		$rnd = left( $rnd, $length - 2 );

		list( $micro, $time ) = explode( ' ', microtime() );
		$rnd .= substr( $micro * 1000, 0, 2 );

		return $rnd;
	}

	protected function createHtml ($path, $content)
	{
		return $this->file->createfile($path, $content);
	}

	protected function download ($url, $filename)  //下载远程文件
	{
		include_once CORE_DIR."include/class.httpService.php";
		$httpService = new httpService($url);
		$this->file->createfile($filename, $httpService->result());
		if (abs(filesize($filename))) self::downloadByFopen($url, $filename);
	}
	
	private function downloadByFopen ($url, $filename)  //使用fopen下载远程文件
	{
		$file = @fopen($url, 'rb');

		if ($file)
		{
			$new = fopen($filename, 'wb');
			if ($filename)
			{
				while (!feof($file))
				{
					fwrite($new, fread($file, 1024 * 8), 1024 * 8);
				}
				fclose($new);
			}
			fclose($file);
		}
	}

	protected function md5 ($str)
	{
		return md5($GLOBALS['KEY_'].$str.$GLOBALS['_KEY']);
	}

	protected function get ($key, $data)
	{
		$_GET[$key] = $data;
	}

	protected function post ($key, $data)
	{
		$_POST[$key] = $data;
	}

	protected function request ($key, $data)
	{
		$_REQUEST[$key] = $data;
	}

	protected function session ($key, $data)
	{
		$_SESSION[$key] = $data;
	}

	public function chkClass ($class_id)
	{
		if (!preg_match('/^[0-9]+$/', $class_id) || strlen($class_id) < $this->classlen)
			return null;

		if (($len = strlen($class_id) % $this->classlen) != 0)
		{
			return right($class_id, $len, true);
		}

		return $class_id;
	}

	protected function dump ($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
		exit;
	}

	protected function array_splice ($array, $index, $len = 0)
	{
		if (!is_array($array)) return array();

		if (is_numeric($index)) return array_splice($array, $index, $len);

		$i = 0;
		foreach ($array as $key=>$value)
		{
			if ($key == $index) array_splice($array, $i, 1);
			$i++;
		}

		return $array;
	}

	protected function array_extend ($parentArray, $array)  //数组继承
	{
		$keyArray = array();
		$valArray = array();
		foreach ($array as $key=>$value)
		{
			$keyArray[] = $key;
			$valArray[] = $value;
		}

		foreach ($parentArray as $key=>$value)
		{
			if (is_array($value))
			{
				if (in_array($key, $keyArray)) $array[$key] = self::array_extend($value, $array[$key]);
				else $array[$key] = $value;
			}
			elseif (!in_array($key, $keyArray)) $array[$key] = $value;
		}
		return $array;
	}

	protected function upload ()
	{
		
	}

}

?>