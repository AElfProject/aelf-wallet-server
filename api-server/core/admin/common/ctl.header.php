<?php

class ctl_header extends adminPage
{

	public function index_action ()
	{
		$define_langs = unserialize( LANGS );
		$this->setData($define_langs, 'langs');
		$this->setData(count($define_langs), 'langs_count');
		//$this->setData(trim($_SESSION['admin_lang']), 'admin_lang');
		$this->setData(trim($_COOKIE['admin_lang']), 'admin_lang');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->setData($this->user_id == '-1' ? 'Hidden' : $this->user['displayName'], 'admin_name');
		$this->display('common/header');
	}

	private function _columnChk ()
	{
		$hide = array(
			'user_changepass'	=> !$this->user ? 0 : $this->chkAction('system/user/changepass')
		);
		return $hide;
	}

}

?>