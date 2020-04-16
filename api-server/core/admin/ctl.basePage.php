<?php

class adminPage extends corecms
{

	protected $user;
	protected $user_id;
	protected $user_shell;
	protected $user_state;
	protected $user_role;
	protected $user_action;
	protected $user_relation;
	protected $user_menu;
	private $userChk = true;
	private $doUserActionChk = true;
	private $doUserInfoChk = true;
	private $doUserHideChk = true;
	private $noActionCtl = array('common', 'ctl.basePage.php', 'ctl.default.php', 'hidden');
	private $noUserChk = array('common/login', 'common/warning', 'api');
	private $noUserActionChk = array('common', 'info', 'default', 'hidden');  //不需要验证动作权限的操作
	private $userInfoChk = array('info');
	private $userHideChk = array('hidden');  //隐藏动作

	function adminPage ()
	{
		foreach ( $_GET as $key => $get ) {
			$_GET[$key] = strip_tags( $get );
		}
		foreach ( $_POST as $key => $post )
		{
			if(is_array($post))
			{
				foreach ( $post as $_key => $_post ) {
					$_POST[$key][$_key] = strip_tags( $_post );
				}
			}
		}

		parent::corecms();
		$this->setTpl(false, CORE_DIR.'common/skin/admin', DATA_DIR.'tpl_compile/admin', DATA_DIR.'tpl_cache/admin');

		$this->user_id		= session('admin_user_id');
		$this->user_shell	= session('admin_user_shell');

		//验证
		if (is_array($GLOBALS['gbl_ctl']))
		{
			if (in_array(strtolower(implode('/', $GLOBALS['gbl_ctl'])), $this->noUserChk)) $this->userChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl'][0]), $this->noUserActionChk)) $this->doUserActionChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl'][0]), $this->userInfoChk)) $this->doUserInfoChk = true;
			else $this->doUserInfoChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl'][0]), $this->userHideChk)) $this->doUserHideChk = true;
			else $this->doUserHideChk = false;
		}
		else
		{
			if (in_array(strtolower($GLOBALS['gbl_ctl']), $this->noUserChk)) $this->userChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl']), $this->noUserActionChk)) $this->doUserActionChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl']), $this->userInfoChk)) $this->doUserInfoChk = true;
			else $this->doUserInfoChk = false;
			if (in_array(strtolower($GLOBALS['gbl_ctl']), $this->userHideChk)) $this->doUserHideChk = true;
			else $this->doUserHideChk = false;
		}

		//super master
		if ($this->user_id == '-1') {
            $u			= $this->loadModel('user');
            $user	= $u->getUserById(1);
			if ( $this->user_shell != $this->md5( $user['id'].$user['name'].$user['password'] )) {
				$this->session('admin_user_id', '');
				$this->session('admin_user_shell', '');
				$this->sheader('?con=admin&ctl=common/login&act=logout&k='.$_GET['k']);
			}
			$this->userChk = false;
		}

		if ($this->userChk)
		{
			self::userIsLogin();
			//分配角色权限
			self::setRole();
			//自身扩展
			self::userAction();
			//动作检测
			if ($this->doUserActionChk)
			{
				if (!self::chkAction((is_array($GLOBALS['gbl_ctl']) ? implode('/', $GLOBALS['gbl_ctl']) : $GLOBALS['gbl_ctl']) . '/' . $GLOBALS['gbl_act']))
					$this->sheader(null, $this->lang->no_current_operation_permissions.' @action-1');
			}
			//信息检测
			if ($this->doUserInfoChk)
			{
				$classID = '';
				$ctl = $GLOBALS['gbl_ctl'][1];

				if ($ctl == 'index')  //info/index
				{
					$classID = get2('class_id');
				}
				else  //info/class
				{
					$classID = trim(get2('parent_id')) == '' ? get2('id') : get2('parent_id');
				}

				if (empty($classID))  //如果没有分类ID，则按普通动作来验证
				{
					if (!self::chkAction("0/$ctl/".$GLOBALS['gbl_act'])) $this->sheader(null, $this->lang->no_current_operation_permissions.' @action-2');
				}
				else
				{
					if (!self::chkInfo($classID, $ctl, $GLOBALS['gbl_act'])) $this->sheader(null, $this->lang->no_current_operation_permissions.' @info');
				}
			}
			//隐藏检测
			if ($this->doUserHideChk)
			{
				$this->sheader(null, $this->lang->no_current_operation_permissions.' @H');
			}
		}
	}

    /**
     * 消息推送
     * @param $userId
     * @param string $title
     * @param string $content
     * @param array $extra
     * @return bool
     */
    protected function messagePush($userId, $title = '', $content = '', $extra = []){

        $result = false;

        $lang = $this->redis()->get( 'lang/' . $userId );
        $title = empty($title) ?  ___('托管理财钱包', $lang) : $title;

        if (!empty($userId)){
            $queueName = 'message_push_queue';
            $queueData = [
                'user_id' => $userId,
                'message' => [
                    'title' => $title,
                    'content' => $content,
                    'extras' => $extra
                ],
            ];

            $result = $this->redis()->rPush($queueName, json_encode($queueData));
        }

        return $result;
    }

	private function userIsLogin ()
	{
		if (empty($this->user_id) || empty($this->user_shell))
		{
			$this->sheader('?con=admin&ctl=common/login&act=logout&k='.$_GET['k']);
		}

		$u			= $this->loadModel('user');
		//echo $this->user_id.PHP_EOL;exit;
		$this->user	= $u->getUserById($this->user_id);
		if (!$this->user['isApproved'])  //未审核
		{
			$this->session('admin_user_id', '');
			$this->session('admin_user_shell', '');
			$this->sheader('?con=admin&ctl=common/login&act=logout&k='.$_GET['k'], $this->lang->user_not_audit);
		}
		if (!$this->user['isAdmin'])  //非管理员
		{
			$this->session('admin_user_id', '');
			$this->session('admin_user_shell', '');
			$this->sheader('?con=admin&ctl=common/login&act=logout&k='.$_GET['k'], $this->lang->user_not_admin);
		}
		if ($this->user_shell != $this->md5($this->user_id.$this->user['name'].$this->user['password']))
		{
			$this->session('admin_user_id', '');
			$this->session('admin_user_shell', '');
			$this->sheader('?con=admin&ctl=common/login&act=logout&k='.$_GET['k']);
		}
	}

	private function setRole () { }

	private function userAction ()
	{
		$roles		= $this->loadModel('role');
		$role		= $roles->get($this->user['role']);
		$this->user	= self::joinAction($role, $this->user);
		unset($role);
		unset($roles);
	}

	private function joinAction ($role, $user)
	{
		$user['action']		= unserialize($user['action']);
		$user['info']		= unserialize($user['info']);
		$user['infoClass']	= unserialize($user['infoClass']);
		$user['relation']	= unserialize($user['relation']);
		if (!is_array($user['action'])) $user['action'] = array();
		if (!is_array($user['info'])) $user['info'] = array();
		if (!is_array($user['infoClass'])) $user['infoClass'] = array();
		if (!is_array($user['relation'])) $user['relation'] = array();

		if ($user['roleExtendType'] == 0)  //不继承
		{ }
		else
		{
			if ($user['roleExtendType'] == 1)  //完全继承
			{
				$user['action']		= unserialize($role['action']);
				$user['info']		= unserialize($role['info']);
				$user['infoClass']	= unserialize($role['infoClass']);
				$user['relation']	= unserialize($role['relation']);
			}
			elseif ($user['roleExtendType'] == 2)  //合并继承
			{
				$user['action']		= array_distinct(array_merge(unserialize($role['action']), $user['action']));
				$user['info']		= array_distinct(array_merge(unserialize($role['info']), $user['info']));
				$user['infoClass']	= array_distinct(array_merge(unserialize($role['infoClass']), $user['infoClass']));
				$user['relation']	= array_distinct(array_merge(unserialize($role['relation']), $user['relation']));
			}
		}

		return $user;
	}

	//动作权限验证
	protected function chkAction ($ctl, $user = null)
	{
		if ($this->user_id == '-1') return true;
		if (!$user) $user = $this->user;

		if( strstr($ctl,'chart_reg_on') )
		{
			$user['action'][] = 'adv/chart_reg_on/index';
		}

		if( strstr($ctl,'onchain') )
		{
			$user['action'][] = 'onchain/coin/index';
			$user['action'][] = 'onchain/coin/edit';
			$user['action'][] = 'onchain/coin/contract';

			$user['action'][] = 'onchain/coin/contract_edit';
			$user['action'][] = 'onchain/coin/contract_trans_reset';
			$user['action'][] = 'onchain/addr/index';
			$user['action'][] = 'onchain/addr/eos_addr';
			$user['action'][] = 'onchain/addr/hc_addr';

			$user['action'][] = 'onchain/balance_by_address/index';
			$user['action'][] = 'onchain/balance_by_contract/index';
			$user['action'][] = 'onchain/eos_new_account/index';
		}

		if( strstr($ctl,'swallet') )
		{
			$user['action'][] = 'swallet/index/index';
			$user['action'][] = 'swallet/send/index';
		}

		if( strstr($ctl,'airdrop') )
		{
			$user['action'][] = 'adv/airdrop/hc_hx';
			$user['action'][] = 'adv/airdrop/airdrop';
		}

		$user['action'][] = 'adv/test/index';

		return in_array($ctl, $user['action']);
	}

	protected function chkInfo ($classID, $ctlName, $actName, $user = null)
	{
		if ($this->user_id == '-1') return true;
		if (!$user) $user = $this->user;
		//现在调整为排除模式，所以为了兼容以前的程序方案，在此做分类和动作的检测
		//分类过多的情况下，可以方便的选择哪个分类有权限，如果没有该分类的权限，则不存在其它动作的权限
		if (in_array($classID, $user['info'])) return false;

		/*
		//检测父级分类是否被禁用
		$bllClass		= $this->loadModel('infoClass');
		$parentClass	= $bllClass->getParentList($classID);
		foreach ($parentClass as $key=>$value)
		{
			if (in_array($value['id'], $user['info'])) return false;
			if (in_array("{$value['id']}/$ctlName/$actName", $user['infoClass'])) return false;
		}
		*/
		if (!self::chkAction("info/$ctlName/$actName", $user)) return false;  //相应动作，如果没有信息的动作权限，则不存在更详细的动作权限
		return true;
		return !in_array("$classID/$ctlName/$actName", $user['infoClass']);
	}

	private function getFileTime ($filename)
	{
		if ($this->chkfile($filename))
		{
			return $this->file->info($filename);
		}
		else return 0;
	}

	private function getActionPermissionArray ($array)
	{
		/*
		另外一种方案：
		每个控制器强制添加对自身的说明方法，该方法提供控制器的说明、控制器具有的动作等。

		本系统采用了另一种方案：
		对控制器目录遍历，读取所有控制器进行分析，取出所有动作。
		*/
		$actionList = array();

		foreach ($array as $key=>$value)
		{
			if (is_array($value[0]))
			{
				$actionList[$value[1]] = self::getActionPermissionArray($value[0]);
			}
			else
			{
				if (!in_array($value[1], $this->noActionCtl))
				{
					$CTL_CONTENT = $this->file->readfile($value[0]);
					if (preg_match_all('/function (.*)_action/U', $CTL_CONTENT, $arr))
					{
						preg_match('/\@ctl_name = (.*)@/U', $CTL_CONTENT, $ctl_name);
						preg_match_all('/function (.*)_action ?\(\) \#act_name = (.*)#/U', $CTL_CONTENT, $act_name);
						$actionList[$value[1]] = array('name' => $ctl_name[1], 'value' => array($act_name[2], $arr[1]));
					}
				}
			}
		}

		return $actionList;
	}

	protected function actionPermissionArray ($cls = false)
	{
		//$cls = true;
		$fname = DATA_DIR."conf/action.array";
		$mtime = self::getFileTime($fname);
		if (time() - $mtime[1] > 24 * 60 * 60 || $cls)
		{
			$this->file->actionPermissionArray($ctlArray, CORE_DIR."admin/", CORE_DIR."admin/", $this->noActionCtl);
			$actionArray	= self::getActionPermissionArray($ctlArray);
			//强制添加信息分类和信息的相关操作
			/*$actionArray[]	= array(
				'name'	=> $this->lang->top_category,
				'value'	=> array(
					array(
						$this->lang->top_category_list,
						$this->lang->add_top_category,
						$this->lang->info_list,
						$this->lang->add_info,
						$this->lang->info_search
					),
					array(
						'class/index',
						'class/add',
						'index/index',
						'index/add',
						'index/search'
					)
				)
			);*/
			$this->file->createfile($fname, serialize($actionArray));
			return $actionArray;
		}
		else return unserialize($this->file->readfile($fname));  //缓存
	}

	protected function infoClassPermissionArray ()
	{
		$infoClass			= $this->loadModel('infoClass');
		$infoClassArray		= $infoClass->getChildForPermission();

		return $infoClassArray;
	}

	protected function infoActionPermissionArray ()
	{
		$this->file->actionPermissionArray($ctlArray, CORE_DIR."admin/info", CORE_DIR."admin/info");
		$actionArray	= self::getActionPermissionArray($ctlArray);

		return $actionArray;
	}

	protected function relationPermissionArray ()
	{
		$relation	= $this->loadModel('relation');
		$menuArray	= $relation->getChild2ForPermission();
		$this->file->createfile($fname, serialize($menuArray));

		return $menuArray;
	}

	protected function defaultInfoClassSettings ()
	{
		return unserialize($this->loadConf('default.infoClass.settings'));
	}

	protected function getColumns ()
	{
		/*$array = unserialize($this->loadConf('info.columns'));
		$array['enrollDate'] = array( 'name' => 'Enroll Date' );
		echo serialize($array);exit;*/
		return unserialize($this->loadConf('info.columns'));
	}

	protected function saveColumns ($data)
	{
		return $this->saveConf('info.columns', serialize($data));
	}

	protected function getColumnsForCompany ()
	{
		return unserialize($this->loadConf('company.columns'));
	}

	protected function saveColumnsForCompany ($data)
	{
		return $this->saveConf('company.columns', serialize($data));
	}

	protected function getAllPermissionClass ($id, $action = 'index/index')
	{
		$mdl_user	= $this->loadModel('user');
		$user		= $mdl_user->getUserById($id);
		if ($user['roleExtendType'] > 0)
		{
			$mdl_role	= $this->loadModel('role');
			$role		= $mdl_role->get($user['role']);

			$user['info']		= unserialize($user['info']);
			$user['infoClass']	= unserialize($user['infoClass']);
			$user['action']		= unserialize($user['action']);
			if (!is_array($user['info'])) $user['info'] = array();
			if (!is_array($user['infoClass'])) $user['infoClass'] = array();
			if (!is_array($user['action'])) $user['action'] = array();
			if ($user['roleExtendType'] == 1)
			{
				$user['info']		= unserialize($role['info']);
				$user['infoClass']	= unserialize($role['infoClass']);
				$user['action']		= unserialize($role['action']);
			}
			else
			{
				$user['info']		= array_distinct(array_merge(unserialize($role['info']), $user['info']));
				$user['infoClass']	= array_distinct(array_merge(unserialize($role['infoClass']), $user['infoClass']));
				$user['action']		= array_distinct(array_merge(unserialize($role['action']), $user['action']));
			}
			unset($mdl_role);
			unset($role);
		}

		unset($mdl_user);

		$mdl_infoClass	= $this->loadModel('infoClass');
		$infoclass		= $mdl_infoClass->getChild();
		$array			= array();
		$tmp			= explode('/', $action);

		foreach ($infoclass as $key=>$value)
		{
			if (self::chkInfo($value['id'], $tmp[0], $tmp[1], $user)) $array[] = $value;
		}

		unset($user);
		return $array;
	}

    /**
     * 添加待阅读记录
     * @param $type
     */
	public function addItemRead($type){
        $mdl_item_read_list = $this->db( 'index', 'item_read_list', 'master' );
        $itemRead = $mdl_item_read_list->getByWhere(['type' => $type]);

        if (empty($itemRead)){
            $mdl_item_read_list->insert(['type' => $type, 'update_time' => time()]);
        }else{
            $mdl_item_read_list->updateByWhere(['update_time' => time()], ['type' => $type] );
        }

        $wealthRead = $mdl_item_read_list->getByWhere(['type' => 1]);

        if (empty($wealthRead)){
            $mdl_item_read_list->insert(['type' => 1, 'update_time' => time()]);
        }else{
            $mdl_item_read_list->updateByWhere(['update_time' => time()], ['type' => 1] );
        }
    }

    //消息推送给多签钱包会员
    protected function shareWalletMesssage($wid, $amount, $coin, $receiveId=''){
        $mdl_member = $this->db('index', 'member');

        $member = $mdl_member->get($wid);
        if($member['type']==2){ //多签用户
            $mdl_share_wallet = $this->db('index', 'share_wallet');
            $wallet = $mdl_share_wallet->getByWhere(array('mid'=>$wid));

            $mdl_wallet_member = $this->db('index', 'share_wallet_member');
            $wallet_member = $mdl_wallet_member->getList(array('user_id','wallet_id'), array('wallet_id'=>$wallet['id'], 'status'=>1));
            foreach($wallet_member as $val){

                //执行推送消息动作 todo
                $_mem = $mdl_member->get($val['user_id']);
                $message = ___( '您有一笔转入到账，金额%s', $this->redis()->get( 'lang/' . $val['user_id'] ), $this->del0($amount)  . strtoupper( $coin ) );
                $pushDetails = array(
                    'type' => 'swallet',
                    'path' => 'user/push/tx',
                    'params' => array(
                        'd' => $member['db'],
                        'type' => 'receive',
                        'id' => $receiveId,
                    ),
                );
                $title = $this->getWalletInfoTitle($val['wallet_id'], $this->redis()->get( 'lang/' . $val['user_id'] ));
                $this->messagePush($val['user_id'], $title, $message, $pushDetails);
            }

        }
    }

    //多签钱包推送消息标题
    protected function getWalletInfoTitle($id, $lang){
        $wallet = $this->db('index', 'share_wallet')->get($id);
        if($wallet['type'] == 1){
            //成员模式
            $model = "{$wallet['rule']}/{$wallet['member_count']}";
        }else{
            //份额模式
            $model = "{$wallet['rule']}%";
        }
        return ___('%s - %s共管模式', $lang, $wallet['name'], $model);
    }

    //排除右边多余的0
    protected  function del0($s){
        $s = number_format( $s, 8, '.', '' );
        $s = trim(strval($s));
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
        if (preg_match('#^-?\d+?\.0+$#', $s)) {
            return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);
        }
        if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {
            return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
        }
        return $s;
    }
}

?>