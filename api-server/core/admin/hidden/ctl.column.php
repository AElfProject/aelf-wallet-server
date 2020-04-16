<?php

class ctl_column extends adminPage
{

	public function index_action ()
	{
		/*
		此处修改的是系统默认的栏目！
		*/
		if (is_post())
		{
			$data = post();
			if ($this->saveColumns($data)) $this->sheader('?con=admin&ctl=info/', $this->lang->info_list_column_modify_success);
			else $this->sheader(null, $this->lang->info_list_column_modify_failed);
		}
		else
		{
			$column = $this->getColumns();
			$this->setData($column);
			$this->display();
		}
	}

}

?>