<?php

/*
 @ctl_name = 网站设置@
*/

class ctl_site extends adminPage
{

	private $id = 0;

	public function ctl_site ()
	{
		parent::adminPage();

		$this->id = (int)get2('id');

		if ($this->id < 1) $this->id = 1;
	}

	public function index_action () #act_name = 基本设置#
	{
		$bll	= $this->loadModel('site');
		$site	= $bll->get();

		if (is_post())
		{
			$data = post('data');
			if ($data = self::_filter($data))
			{
				if ($bll->update($data)) $this->sheader('?con=admin&ctl=system/site');
				else $this->sheader(null, $this->lang->edit_site_setting_failed);
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			if (empty($site))
			{
				//可以在此添加创建的功能
				$site = array(
					'name' => '',
					'pageTitle' => '',
					'keywords' => '',
					'description' => '',
					'icp' => '',
					'copyright' => '',
					'contact' => ''
				);
				$bll->insert($site);
			}
			$this->setData($site);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->display();
		}
	}

	public function water_action () #act_name = 水印设置#
	{
		
	}

	public function other_action () #act_name = 其它参数#
	{
		$bll	= $this->loadModel('site');
		$site	= $bll->get();

		if (empty($site))
		{
			//可以在此添加创建的功能
		}

		if (is_post())
		{
			$data = post('data');

			if ($bll->update($data)) $this->sheader('?con=admin&ctl=system/site&act=other');
			else $this->sheader(null, $this->lang->edit_site_advanced_setting_failed);
		}
		else
		{
			$this->setData($site);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->display();
		}
	}

	private function _filter ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}

		if (empty($data['name'])) return false;
		if (empty($data['pageTitle'])) return false;

		return $data;
	}

	private function _columnChk ()
	{
		$hide = array(
			'site_index'		=> $this->chkAction('system/site/index'),
			'site_water'		=> $this->chkAction('system/site/water'),
			'site_other'		=> $this->chkAction('system/site/other')
		);
		return $hide;
	}

}

?>