<?php

class mdl_user extends mdl_base
{

	protected $tableName = '#@_user';
	private $roleTName = '#@_role';
	private $listField = 'id, name, displayName, isAdmin, isApproved, role, roleExtendType, groupid, createdDate, lastLoginDate';

	public function getUserById ($id)
	{
		return $this->db->selectOne(null, $this->tableName, "id='$id'");
	}

	public function getUserByName ($name)
	{
		return $this->db->selectOne(null, $this->tableName, "name='$name'");
	}

	public function chkUserName ($name)
	{
		return $this->db->getCount($this->tableName, "name='$name'");
	}

	public function getAllUserListSql ($where)
	{
		return $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', $where);
		//echo $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', $where);exit;
	}

	public function getAllUserListSqlByRole ($role)
	{
		return $this->db->getSelectMultipleSql(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id', array('0#role' => $role));
	}

	public function getAllUserList ()
	{
		return $this->db->selectMultiple(array($this->listField, array('roleName'=>'name')), array($this->tableName, $this->roleTName), '0#role=1#id');
	}

	public function getAllUserListWithoutById ($where)
	{
		return $this->db->select($this->listField, $this->tableName, $where);
	}

	public function getAllUserListBySql ($sql)
	{
		return $this->db->query($sql);
	}

	public function getUserListByName ($name)
	{
		return $this->db->select($this->listField, $this->tableName, "name like '%$name%'");
	}

	public function addUser ($data)
	{
		//echo $this->db->getInsertSql($data, $this->tableName);exit;
		return $this->db->insert($data, $this->tableName);
	}

	public function updateUserById ($data, $id)
	{
		return $this->db->update($data, $this->tableName, "id='$id'");
	}

	public function deleteUserById ($id)
	{
		return $this->db->delete($this->tableName, "id='$id'");
	}

}

?>