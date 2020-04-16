<?php

/*
 @ctl_name = 关系管理@
*/

class ctl_relation extends adminPage
{

	public function index_action () #act_name = 关系列表#
	{
		$relation	= $this->loadModel('relation');
		$data		= $relation->getChild(0);

		$this->setData($data);
		$this->display();
	}

	public function add_action () #act_name = 添加关系分类#
	{
		$parent_id	= get2('parent_id');
		$relation	= $this->loadModel('relation');

		if (is_post())
		{
			$data = $_REQUEST['data'];
			//$data		= post('data');
			if (trim($data['name']) == '') $this->sheader(null, $this->lang->category_name_can_not_empty);
			$data['id']	= $relation->getChildClassIdToInsert($parent_id);
			if ((int)$data['ordinal'] < 1) $data['ordinal'] = $relation->getChildClassOrdinal($parent_id);
			if ($relation->create($data)) $this->sheader('?con=admin&ctl=hidden/relation');
			else $this->sheader(null, $this->lang->add_category_failed);
		}
		else
		{
			$class	= $relation->get($parent_id);
			$this->setData($class);
			$this->setData($relation->getChildClassOrdinal($parent_id), 'ordinal');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑关系分类#
	{
		$id			= get2('id');
		$relation	= $this->loadModel('relation');

		if (is_post())
		{
			$data = $_REQUEST['data'];
			//$data	= post('data');
			if (trim($data['name']) == '') $this->sheader(null, $this->lang->category_name_can_not_empty);
			if ($relation->updateById($data, $id)) $this->sheader('?con=admin&ctl=hidden/relation');
			else $this->sheader(null, $this->lang->edit_category_failed);
		}
		else
		{
			$class	= $relation->get($id);
			$parent	= $relation->getParent($id);
			$this->setData($class);
			$this->setData($parent, 'parent');
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除关系分类#
	{
		$id			= get2('id');
		$relation	= $this->loadModel('relation');

		if ($relation->getChildCount($id) > 0) $this->sheader(null, $this->lang->under_sub_category_delete_all_first);
		else
		{
			$relation->deleteById($id);
			$this->sheader('?con=admin&ctl=hidden/relation');
		}
	}

}

?>