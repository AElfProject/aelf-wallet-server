<?php

/*
 @ctl_name = 更新缓存@
*/

class ctl_cls extends adminPage
{

	public function index_action () #act_name = 更新缓存列表#
	{
		$this->display();
	}

	public function docls_action () #act_name = 执行更新缓存#
	{
		$cacheName = trim(get2('cacheName'));

		if ($cacheName == 'all')
		{
			$this->actionPermissionArray(true);
			$this->sheader(null, $this->lang->all_cache_update_complete);
		}
		elseif ($cacheName == 'action')
		{
			$this->actionPermissionArray(true);
			$this->sheader(null, $this->lang->action_cache_update_complete);
		}
		else
		{
			
		}
	}

}

?>