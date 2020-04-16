<?php

require_once 'core/v2.1/pdo.php';
require_once 'pdo.php';

abstract class mdl_base {

	protected $db;
	protected $lang = false;
	protected $tableName = null;

	public function mdl_base () {
		$setting = $GLOBALS['DB_LIST']['index'];
		$this->db = new PdoByToday( $setting['host'], $setting['port'], $setting['name'], $setting['pwd'], $setting['db'] );
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function sql( $sql ) {
		return $this->db->execute( $sql );
	}

	public function get( $id ) {
		$where = "id='$id'";
		
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		
		//echo $this->db->getSelectSql( null, $this->tableName, $where );exit;
		return $this->db->selectOne( null, $this->tableName, $where );
	}

	public function getByWhere( $where, $colunms = array() ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->selectOne( $colunms, $this->tableName, $where );
	}

	public function getList( $column = null, $where = null, $order = null, $cnt = null ) {
		$cnt = (int)$cnt;
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->select( $column, $this->tableName, $where, $order, $cnt > 0 ? "0, $cnt" : '' );
	}

	public function getListSql ( $column = null, $where = null, $order = null ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			if ( isset( $where ) ) {
				if ( is_array( $where ) ) {
					$where[] = "lang='".$this->getLang()."'";
				}
				else {
					$where .= " and lang='".$this->getLang()."'";
				}
			}
			else {
				$where = "lang='".$this->getLang()."'";
			}
		}
		return $this->db->getSelectSql( $column, $this->tableName, $where, $order );
	}

	public function getListBySql ( $sql ) {
		return $this->db->query( $sql );
	}

	public function insert ( $data ) {
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		//echo $this->db->getInsertSql( $data, $this->tableName );exit;
		$this->db->insert( $data, $this->tableName );
		return $this->db->insert_id();
	}

	public function update( $data, $id ) {
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		//echo $this->db->getUpdateSql( $data, $this->tableName, "id='$id'" );exit;
		$where = "id='$id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->update( $data, $this->tableName, $where );
	}

	public function updateByWhere( $data, $where ) {
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->update( $data, $this->tableName, $where );
	}

	public function delete ( $id ) {
		$where = "id='$id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->delete( $this->tableName, $where );
	}

	public function deleteByWhere( $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->delete( $this->tableName, $where );
	}

	public function getMin( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getMin( $this->tableName, $field, $where );
	}

	public function getMax( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getMax( $this->tableName, $field, $where );
	}

	public function getSum( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getSum( $this->tableName, $field, $where );
	}

	public function getOne( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getOne( $this->tableName, $field, $where );
	}

	public function getCount( $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getCount( $this->tableName, $where );
	}

	public function begin() {
		return $this->db->begin();
	}

	public function rollback() {
		return $this->db->rollback();
	}

	public function commit() {
		return $this->db->commit();
	}

	public function error() {
		return $this->db->error();
	}

	public function errno() {
		return $this->db->errno();
	}

	public function isError() {
		return $this->db->isError();
	}

	public function getErrors() {
		return $this->db->getErrors();
	}

	protected function getLang() {
		if ( $GLOBALS['gbl_con'] == 'admin' ) {
			//$lang = trim( $_SESSION['admin_lang'] );
			if ( isset( $GLOBALS['admin_lang'] ) ) {
				$lang = $GLOBALS['admin_lang'];
			}
			else {
				$lang = trim( $_COOKIE['admin_lang'] );
			}
		}
		else {
			//$lang = trim( $_SESSION['lang'] );
			if ( isset( $GLOBALS['lang'] ) ) {
				$lang = $GLOBALS['lang'];
			}
			else {
				$lang = trim( $_COOKIE['lang'] );
			}
		}
		return $lang;
	}

}

?>