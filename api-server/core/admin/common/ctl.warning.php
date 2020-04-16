<?php

class ctl_warning extends adminPage
{

	public function index_action ()
	{
		$msg = urldecode(get2('msg'));
		$url = urldecode(get2('url'));

		if (empty($url)) $url = 'javascript:window.history.back(-1);';

		$this->setData($msg, 'msg');
		$this->setData($url, 'url');
		$this->display('common/warning');
	}

}

?>