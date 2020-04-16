<?php

class ctl_main extends adminPage
{

	public function index_action ()
	{
		$this->user['lastLoginDate'] = date('Y-m-d H:i:s', $this->user['lastLoginDate']);
		$this->setData($this->user, 'user');
		$this->setData(date('Y-m-d H:i'), 'time');
		$this->setData($count, 'count');
		$this->display('common/main');
	}

}

?>