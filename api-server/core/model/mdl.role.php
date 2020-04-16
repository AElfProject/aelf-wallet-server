<?php

class mdl_role extends mdl_base
{

	protected $tableName = '#@_role';
	private $listField = 'id, name, description, isSystem';

	public function get ($id)
	{
		return $this->db->selectOne(null, $this->tableName, "id='$id'");
	}

	public function getRoleList ($where=[])
	{
		return $this->db->select($this->listField, $this->tableName, $where);
	}

	public function getRoleListWithoutId ($where)
	{
		return $this->db->select($this->listField, $this->tableName, $where);
	}

	public function add ($data)
	{
		return $this->db->insert($data, $this->tableName);
	}

	public function updateById ($data, $id)
	{
		return $this->db->update($data, $this->tableName, "id='$id'");
	}

	public function deleteById ($id)
	{
		return $this->db->delete($this->tableName, "id='$id'");
	}

}

?>