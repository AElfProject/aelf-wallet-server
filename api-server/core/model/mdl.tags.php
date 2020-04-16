<?php

class mdl_tags extends mdl_base
{

	protected $tableName = '#@_tags';

	public function getListByInfoId ($info_id)
	{
		return $this->db->select(null, $this->tableName, "info_id='$info_id'");
	}

	public function add ($data)
	{
		$this->db->insert($data, $this->tableName);
		return $this->db->insert_id();
	}

	public function deleteByInfoId ($info_id)
	{
		return $this->db->delete($this->tableName, "info_id='$info_id'");
	}

}

?>