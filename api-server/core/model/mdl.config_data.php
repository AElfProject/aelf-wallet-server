<?php

class mdl_config_data extends mdl_base
{

	protected $tableName = '#@_config_data';

	public function getList() {
		$list = parent::getList();
		$configs = array();
		foreach ( $list as $k => $v ) {
			$configs[$v['key']] = $v['val'];
		}
		return $configs;
	}

	public function get( $key ) {
		return parent::getByWhere( array( 'key' => $key ) );
	}

	public function insert( $key, $val ) {
		return parent::insert( array( 'key' => $key, 'val' => $val ) );
	}

	public function update( $key, $val ) {
		return parent::updateByWhere( array( 'val' => $val ), array( 'key' => $key ) );
	}

	public function delete( $key ) {
		return parent::deleteByWhere( array( 'key' => $key ) );
	}

}

?>