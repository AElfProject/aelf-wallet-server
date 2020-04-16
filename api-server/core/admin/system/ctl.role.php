<?php

/*
 @ctl_name = 角色管理@
*/

class ctl_role extends adminPage
{

	public function index_action () #act_name = 角色列表#
	{
		$role = $this->loadModel('role');
		$this->setData($role->getRoleList());

		$this->setData($this->user_id, 'user_id');
		$this->setData(self::_columeChk(), 'hideColumn');
		$this->display();
	}

	public function add_action () #act_name = 添加角色#
	{
		$role	= $this->loadModel('role');

		if (is_post())
		{
			$data = $_POST['data'];
			if (trim($data['name']) == '') $this->sheader(null, $this->lang->role_name_can_not_empty);

			$extendBy = (int)$_POST['extendBy'];
			if ($extendBy > 0)
			{
				$extendRole = $role->get($extendBy);
				$data['action']		= $extendRole['action'];
				$data['infoClass']	= $extendRole['infoClass'];
				$data['relation']	= $extendRole['relation'];
			}
			unset($data['con']);
			unset($data['ctl']);
			unset($data['act']);

			if ($role->add($data)) $this->sheader('?con=admin&ctl=system/role');
			else $this->sheader(null, $this->lang->add_role_failed);
		}
		else
		{
			if ($this->user_id == -1)
			{
				$extendBy	= $role->getRoleListWithoutId("id<>'$id'");
			}
			else
			{
				$extendBy	= $role->getRoleListWithoutId("id<>'$id' and isSuper=0");
			}
			$this->setData($extendBy, 'extendBy');
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->chkAction('system/role/authorize'), 'authorize');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑角色#
	{
		$id		= (int)get2('id');
		$role	= $this->loadModel('role');
		$data	= $role->get($id);

		if ($data['isSystem'] && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->current_role_no_right_modify);
		}

		if (is_post())
		{
			$data = $_POST['data'];
			unset($data['con']);
			unset($data['ctl']);
			unset($data['act']);

			if (trim($data['name']) == '') $this->sheader(null, $this->lang->role_name_can_not_empty);

			$data['isSystem'] = (int)$data['isSystem'];
			$data['isSuper'] = (int)$data['isSuper'];

			$extendBy = (int)$_POST['extendBy'];
			if ($extendBy > 0)
			{
				$extendRole = $role->get($extendBy);
				$data['action']		= $extendRole['action'];
				$data['infoClass']	= $extendRole['infoClass'];
				$data['relation']	= $extendRole['relation'];
			}

			if ($role->updateById($data, $id)) $this->sheader('?con=admin&ctl=system/role');
			else $this->sheader(null, $this->lang->edit_role_failed);
		}
		else
		{
			if ($this->user_id == -1)
			{
				$extendBy	= $role->getRoleListWithoutId("id<>'$id'");
			}
			else
			{
				$extendBy	= $role->getRoleListWithoutId("id<>'$id' and isSuper=0");
			}
			$this->setData($data);
			$this->setData($extendBy, 'extendBy');
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->chkAction('system/role/authorize'), 'authorize');
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除角色#
	{
		$id		= (int)get2('id');
		$roles	= $this->loadModel('role');

		$role	= $roles->get($id);
		if ($role['isSystem'] || $role['isSuper']) $this->sheader(null, $this->lang->system_role_can_not_delete);

		if ($roles->deleteById($id)) $this->sheader('?con=admin&ctl=system/role');
		else $this->sheader(null, $this->lang->delete_role_failed);
	}

	public function authorize_action () #act_name = 角色授权#
	{
		$id		= (int)get2('id');
		$role	= $this->loadModel('role');
		$data	= $role->get($id);

		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if ($this->user_id != '-1')
		{
			if ($data['isSystem'] || $data['isSuper']) $this->sheader(null, $this->lang->system_role_can_not_authorize);
		}
		unset($data);

		if (is_post())
		{
			$data = array(
				'action'	=> serialize($_POST['authorize']),
				'info'		=> serialize($_POST['info']),
				'infoClass'	=> serialize($_POST['infoClass']),
				'relation'	=> serialize($_POST['relation'])
			);
			if ($role->updateById($data, $id)) $this->sheader('?con=admin&ctl=system/role');
			else $this->sheader(null, $this->lang->authorization_role_failed);
		}
		else
		{
			$data				= $role->get($id);
			$data['action']		= unserialize($data['action']);
			$data['info']		= unserialize($data['info']);  //信息分类权限
			$data['infoClass']	= unserialize($data['infoClass']);  //信息分类的排除权限
			$data['relation']	= unserialize($data['relation']);
			$action		= $this->actionPermissionArray();
			$infoClass	= $this->infoClassPermissionArray();
			$infoAction	= $this->infoActionPermissionArray();
			$relation	= $this->relationPermissionArray();

			foreach ($action as $key=>$value)
			{
				foreach ($value['value'][1] as $subkey=>$sub)
				{
					if (in_array($key.'/'.$sub, $data['action'])) $action[$key]['value'][2][$subkey] = true;
				}
			}
			foreach ($infoClass as $key=>$value)
			{
				//分类总权限
				$infoClass[$key]['istrue'] = in_array($value['id'], $data['info']) ? true : false;

				//分类详细权限
				$infoClass[$key]['action'] = $infoAction;
				foreach ($infoAction as $subkey=>$sub)
				{
					foreach ($sub['value'][1] as $kk=>$ss)
					{
						if (in_array($value['id'].'/'.$subkey.'/'.$ss, $data['infoClass'])) $infoClass[$key]['action'][$subkey]['value'][2][$kk] = true;
					}
				}
			}
			foreach ($relation as $key=>$value)
			{
				if ( in_array( $value['id'], $data['relation'] ) ) $relation[$key]['istrue'] = true;
				foreach ($value['child'] as $subkey=>$sub)
				{
					if (in_array($sub['id'], $data['relation'])) $relation[$key]['child'][$subkey]['istrue'] = true;
				}
			}

			$this->setData($data);
			$this->setData($action, 'action');
			$this->setData($infoClass, 'infoClass');
			$this->setData($infoAction, 'infoAction');
			$this->setData($relation, 'relation');
			$this->display();
		}
	}

	private function _columeChk ()
	{
		$hide = array(
			'role_add'				=> $this->chkAction('system/role/add'),
			'role_edit'				=> $this->chkAction('system/role/edit'),
			'role_delete'			=> $this->chkAction('system/role/delete'),
			'role_authorize'		=> $this->chkAction('system/role/authorize')
		);
		return $hide;
	}

}

?>