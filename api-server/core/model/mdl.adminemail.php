<?php

class mdl_adminemail extends mdl_base
{

	protected $tableName = '#@_admin_email';

	function get() {
		return $this->db->selectOne( null, $this->tableName );
	}

	function update( $data ) {
		return $this->db->update( $data, $this->tableName ) ;
	}

}

?>