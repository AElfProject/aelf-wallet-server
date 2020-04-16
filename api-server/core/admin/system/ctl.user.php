<?php

/*
 @ctl_name = 用户管理@
*/

class ctl_user extends adminPage
{

	public function index_action () #act_name = 用户列表#
	{
		$mdl_user = $this->db( 'index', 'user' );

		$search = array();
		$search['keyword'] = trim( get2( 'keyword' ) );
		$search['role'] = (int)get2( 'role' );
		// if ( !preg_match( '/^[a-zA-Z0-9_@.]+$/', $search['keyword'] ) ) unset( $search['keyword'] );

		//查询数量
		$where = array();
		if ( $search['role'] > 0 ) $where[] = "role=".$search['role'];
		if ( $search['keyword'] ) $where[] = "(`name` like '%".$search['keyword']."%')";

		$count = $mdl_user->getCount( $where );

		list( $sql, $params ) = $mdl_user->getListSql( null, $where, '`id` desc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_user->getListBySql( $page['outSql'] );
		
		$roles	= $this->db('index', 'role');
		$rolelist = $roles->getList();
		
		foreach( $list as $key=>$val )
		{
			$role = $roles->get($val['role']);
			$list[$key]['roleName'] = $role['name'];
		}

		$this->setData($list);
		$this->setData($page['pageStr'], 'pager');
		$this->setData($rolelist, 'rolelist');
		$this->setData($search, 'search');
		$this->display();
	}

	public function add_action () #act_name = 添加用户#
	{
		$user = $this->db('index', 'user', 'master');

		if (is_post())
		{
			$data = post('data');
			$this->formData = $data;
			if ($data = $this->_filter($data))
			{

				unset($data['code']);
				if ( !$this->formError ) {
					if ($user->insert($data)) 
					{
						unset($_SESSION['gaSecret']);
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '操作成功';
						$this->session( 'form-success-msg', '操作成功' );
						$this->sheader('?con=admin&ctl=system/user');
					}
					else 
					{
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '操作失败'.serialize($user->getErrors());
						// $this->sheader(null, $this->lang->add_user_failed.' '.json_encode( $user->getErrors() ) );
					}
				}
				else
				{
                    $this->formReturn['success'] = false;
                    $this->formReturn['msg'] = '操作失败'.serialize($user->getErrors());
				}
			}
			else 
			{
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}

		$role = $this->db('index', 'role');
		$roles		= $role->getList();
		$this->setData($roles, 'roles');
		$this->setData($this->user_id, 'user_id');
		
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );
		$this->display();
	}

	public function edit_action () #act_name = 编辑用户#
	{
		$id		= (int)get2('id');
		// $user	= $this->loadModel('user');
		$user	= $this->db('index', 'user', 'master');
		$user_info	= $user->get($id);

		if ($user_info['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->no_permission_to_edit_this_user);
		}

		//$GoogleAuthenticator = new GoogleAuthenticator;
		if (is_post())
		{
			$data = post('data');
			
			if ($data = $this->_filterForEdit($data))
			{
//				$code = $data['code'];
//				if ( empty( $code ) )
//					$this->formError[] = '请输入验证码';
//				$gaSecret = $user_info['gaSecret'];
//				if ( empty( $gaSecret ) )
//					$this->formError[] = '您的验证已过期，请重新填写';
//				if ( !$GoogleAuthenticator->verifyCode( $gaSecret, $code ) )
//					$this->formError[] = '您输入的验证码不正确，请重新填写';
//				$data['gaSecret']		= $gaSecret;
				//
				// print_r($data);exit();
				unset($data['code']);
				if ( !$this->formError ) {
					if ($user->update($data, $id)) 
					{
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '操作成功';
						$this->session( 'form-success-msg', '操作成功' );
						$this->sheader('?con=admin&ctl=system/user');
						// $this->sheader('?con=admin&ctl=system/user');
					}
					else 
					{
//						$gaSecret = $_SESSION['gaSecret'] ? $_SESSION['gaSecret'] : $GoogleAuthenticator->getSecret();
//						$_SESSION['gaSecret'] = $gaSecret;
//						$this->setData( $gaSecret, 'gaSecret' );
//						$this->setData( HTTP_ROOT.'qrcode.php?code='.urlencode( $GoogleAuthenticator->getQRCodeUrl( 'hcash_wallet:'.$gaSecret, $gaSecret ) ), 'gaQRCodeUrl' );

						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '操作失败'.serialize($user->getErrors());
						// $this->sheader(null, $this->lang->edit_user_failed);
					}
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
//			$gaSecret = $_SESSION['gaSecret'] ? $_SESSION['gaSecret'] : $GoogleAuthenticator->getSecret();
//			$_SESSION['gaSecret'] = $gaSecret;
//			$this->setData( $gaSecret, 'gaSecret' );
//			$this->setData( HTTP_ROOT.'qrcode.php?code='.urlencode( $GoogleAuthenticator->getQRCodeUrl( 'hcash_wallet:'.$gaSecret, $gaSecret ) ), 'gaQRCodeUrl' );

		}
		// $role = $this->loadModel('role');
		$role = $this->db('index', 'role');
		$roles		= $role->getList();
		
		$this->setData( $user_info['gaSecret'], 'gaSecret' );
		//$this->setData( HTTP_ROOT.'qrcode.php?code='.urlencode( $GoogleAuthenticator->getQRCodeUrl( 'hcash_wallet:'.$user_info['gaSecret'], $user_info['gaSecret'] ) ), 'gaQRCodeUrl' );
		$this->setData($user_info, 'data');
		$this->setData($roles, 'roles');
		$this->setData($this->user_id, 'user_id');
		
		$this->formData = array_merge( $user_info, $this->formData );
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );
		
		$this->display();
	}

	public function delete_action () #act_name = 删除用户#
	{
		$id		= (int)get2('id');
		// $user	= $this->loadModel('user');
		$user	= $this->db('index', 'user', 'master');
		$data	= $user->get($id);
		if ($data['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->current_user_no_right_delete);
		}
		if ($user->delete($id)) $this->sheader('?con=admin&ctl=system/user');
		else $this->sheader(null, $this->lang->delete_user_failed);
	}

	public function authorize_action () #act_name = 用户授权#
	{
		$id		= (int)get2('id');
		// $user	= $this->loadModel('user');
		$user	= $this->db('index', 'user');
		$data	= $user->getUserById($id);
		if ($data['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->no_permission_to_authorize_this_user);
		}

		if ($data['roleExtendType'] == 1 && $data['role'] > 0) $this->sheader(null, $this->lang->complete_inherited_role_so_can_not_separately_authorize);

		if (is_post())
		{
			//是否需要在保存时去除角色的权限，这样修改继承方式时不会有影响
			$data = array(
				'action'	=> serialize(post('authorize')),
				'info'		=> serialize(post('info')),
				'infoClass'	=> serialize(post('infoClass')),
				'relation'	=> serialize(post('relation'))
			);
			if ($user->updateUserById($data, $id)) $this->sheader('?con=admin&ctl=system/user');
			else $this->sheader(null, $this->lang->authorization_user_failed);
		}
		else
		{
			$data['action']		= unserialize($data['action']);
			$data['info']		= unserialize($data['info']);  //信息分类权限
			$data['infoClass']	= unserialize($data['infoClass']);  //信息分类的排除权限
			$data['relation']	= unserialize($data['relation']);
			$action		= $this->actionPermissionArray();
			$infoClass	= $this->infoClassPermissionArray();
			$infoAction	= $this->infoActionPermissionArray();
			$relation	= $this->relationPermissionArray();

			if (!is_array($data['action'])) $data['action'] = array();
			if (!is_array($data['infoClass'])) $data['infoClass'] = array();
			if (!is_array($data['relation'])) $data['relation'] = array();

			//继承角色权限
			if ($data['roleExtendType'] == 0)  //不继承
			{ }
			else
			{
				// $roles	= $this->loadModel('role');
				$roles	= $this->db('index', 'role');
				$role	= $roles->get($data['role']);
				if ($data['roleExtendType'] == 1)  //完全继承
				{
					$data['action']		= unserialize($role['action']);
					$data['info']		= unserialize($role['info']);
					$data['infoClass']	= unserialize($role['infoClass']);
					$data['relation']	= unserialize($role['relation']);
				}
				elseif ($data['roleExtendType'] == 2)  //合并继承
				{
					$data['action']		= array_distinct(array_merge(unserialize($role['action']), $data['action']));
					$data['info']		= array_distinct(array_merge(unserialize($role['info']), $data['info']));
					$data['infoClass']	= array_distinct(array_merge(unserialize($role['infoClass']), $data['infoClass']));
					$data['relation']	= array_distinct(array_merge(unserialize($role['relation']), $data['relation']));
				}
			}

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

	public function changepass_action () #act_name = 修改口令#
	{
		$user = & $this->user;
		if (!$user)
		{
			$this->sheader(null, $this->lang->current_login_user_can_not_changepass);
		}

		if (is_post())
		{
			$data = post('data');
			foreach ($data as $key=>$value)
			{
				$data[$key] = trim($value);
			}
			if ($data['newpassword'] == '' || strlen($data['newpassword']) < 6 || $data['newpassword'] != $data['newpassword2'])
			{
				$this->sheader(null, $this->lang->password_length_error_or_not_match);
			}

			if ($this->md5($data['oldpassword']) == $user['password'])
			{
				// $bll = $this->loadModel('user');
				$bll = $this->db('index', 'user');
				if ($bll->updateUserById(array('password' => $this->md5($data['newpassword'])), $user['id'])) $this->sheader('?con=admin&ctl=common/main', $this->lang->password_change_success);
				else $this->sheader(null, $this->lang->change_password_failed);
			}
			else $this->sheader(null, $this->lang->original_password_not_correct);
		}
		else
		{
			$data = $user;
			$this->setData($data);
			$this->display();
		}
	}

	private function _filter ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}
		if ($data['name'] == '' || $data['displayName'] == '' || $data['password'] == '' || $data['password2'] == '' || strlen($data['password']) < 6 || $data['password'] != $data['password2']) return false;

		$reg = $this->loadModel('reg');
		if (!$reg->chkMail($data['email'])) return false;

		$data['password']	= $this->md5($data['password']);
		$data['isApproved']	= (int)$data['isApproved'];
		$data['groupid']	= (int)$data['groupid'];
		$data['role']		= (int)$data['role'];
		$data['createdDate']	= time();

		return $this->array_splice($data, 'password2');
	}

	private function _filterForEdit ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}
		if ($data['displayName'] == '') return false;

		$reg = $this->loadModel('reg');
		if (!$reg->chkMail($data['email'])) return false;

		if ($data['password'] != '')
		{
			if (strlen($data['password']) < 6 || $data['password'] != $data['password2']) $this->sheader(null, $this->lang->password_length_error_or_not_match);
			$data['password']	= $this->md5($data['password']);
		}
		else
		{
			$data = $this->array_splice($data, 'password');
		}

		$data['isAdmin']		= (int)$data['isAdmin'];
		$data['isApproved']		= (int)$data['isApproved'];
		$data['groupid']		= (int)$data['groupid'];
		$data['role']			= (int)$data['role'];
		$data['roleExtendType']	= (int)$data['roleExtendType'];

		return $this->array_splice($data, 'password2');
	}

	private function _out ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key]['lastLoginDate']			= date('Y-m-d', $value['lastLoginDate']);
			$data[$key]['createdDate']				= date('Y-m-d', $value['createdDate']);
			$data[$key]['lastModifiedDate']			= date('Y-m-d', $value['lastModifiedDate']);
			$data[$key]['lastPasswordChangedDate']	= date('Y-m-d', $value['lastPasswordChangedDate']);
		}

		return $data;
	}

	private function _columnChk ()
	{
		$hide = array(
			'user_add'				=> $this->chkAction('system/user/add'),
			'user_edit'				=> $this->chkAction('system/user/edit'),
			'user_delete'			=> $this->chkAction('system/user/delete'),
			'user_authorize'		=> $this->chkAction('system/user/authorize')
		);
		return $hide;
	}

}

?>