<?php

class mdl_site extends mdl_base
{

	protected $lang = true;  //现在如果设为false，也可以在页面中选择语言修改
	protected $tableName = '#@_sites';

	public function get ( $cl = null )
	{
		if ( empty( $cl ) ) {
			if ( $this->lang ) {
				$where = "lang='".$this->getLang()."'";
			}
		}
		else {
			$where = "lang='".$cl."'";
		}
		return $this->db->selectOne(null, $this->tableName, $where);
	}

	public function update ( $data, $cl = null )
	{
		if ( empty( $cl ) ) {
			if ( $this->lang ) {
				$where = "lang='".$this->getLang()."'";
			}
		}
		else {
			$where = "lang='".$cl."'";
		}
		return $this->db->update($data, $this->tableName, $where);
	}

	public function delete ( $cl = null )
	{
		if ( empty( $cl ) ) {
			if ( $this->lang ) {
				$where = "lang='".$this->getLang()."'";
			}
		}
		else {
			$where = "lang='".$cl."'";
		}
		return $this->db->delete($this->tableName, $where);
	}

}

?>