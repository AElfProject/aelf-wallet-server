<?php

/*
 @ctl_name = 友情链接管理@
*/

class ctl_link extends adminPage
{

	public function index_action () #act_name = 链接列表#
	{
		$bllLink	= $this->loadModel('link');
		$where		= "";
		$order		= "createDate desc, ordinal desc";
		$pageSql	= $bllLink->getListSql(null, $where, $order);
		$pageUrl	= '?con=admin&ctl=system/link&';
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $bllLink->getListBySql($page['outSql']);

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->display();
	}

	public function add_action () #act_name = 添加链接#
	{
		$bllLink	= $this->loadModel('link');
		if (is_post())
		{
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				$data['createDate'] = time();
				if ($bllLink->add($data))
				{
					$this->sheader('?con=admin&ctl=system/link');
				}
				else
				{
					$this->sheader(null, $this->lang->add_link_failed);
				}
			}
			else
			{
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else
		{
			$this->setData($bllLink->getOrdinalForInsert(), 'ordinal');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑链接#
	{
		$id			= (int)get2('id');
		$bllLink	= $this->loadModel('link');
		$data		= $bllLink->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if (is_post())
		{
			$data	= post('data');
			if ($data = self::_filter($data))
			{
				if ($bllLink->update($data, $id))
				{
					$this->sheader('?con=admin&ctl=system/link');
				}
				else
				{
					$this->sheader(null, $this->lang->edit_link_failed);
				}
			}
			else
			{
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else
		{
			$this->setData($data, 'data');
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除链接#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_delete((int)$v);
				}
			}
		}
		else
		{
			self::_delete((int)get2('id'));
		}
		$this->sheader('?con=admin&ctl=system/link');
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllLink	= $this->loadModel('link');
		$link		= $bllLink->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists.' '.$id);
		}
		if ($bllLink->delete($id))
		{
			$this->file->deletefile(UPDATE_DIR.$link['imageUrl']);
		}
		else
		{
			$this->sheader(null, $this->lang->delete_link_failed.' '.$id);
		}
	}

	private function _filter ($data)
	{
		foreach ($data as $k=>$v)
		{
			$data[$k] = trim($v);
		}
		$data['ordinal']	= (int)$data['ordinal'];
		$data['isApproved']	= limitInt((int)$data['isApproved'], 0, 1);
		return $data;
	}

}

?>