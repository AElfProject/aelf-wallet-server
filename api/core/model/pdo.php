<?php

class mdl_pdo {

	private $db;
	protected $lang = false;
	protected $tableName = null;

	public function __construct ( $db, $tableName, $lang = false ) {
		$this->db = $db;
		$this->tableName = '#@_'.$tableName;
		$this->lang = $lang;
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function query( $sql, $params = array() ) {
		return $this->db->query( $sql, $params );
	}

	public function sql( $sql, $params = array() ) {
		return $this->db->execute( $sql, $params );
	}

	public function exec( $sql ) {
		return $this->db->exec( $sql );
	}

	public function get( $id ) {
		$where = "id='$id'";
		
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		
		//echo $this->db->getSelectSql( null, $this->tableName, $where );exit;
		return $this->db->selectOne( null, $this->tableName, $where );
	}

	public function getSqlList() {
		return $this->db->getSqlList();
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

	public function getCount( $where = null ) {
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

	public function isError() {
		return $this->db->isError();
	}

	public function getErrors() {
		return $this->db->getErrors();
	}
    public function close() {
        return $this->db->close();
    }

	protected function getLang() {
		if ( isset( $GLOBALS['lang'] ) ) {
			$lang = $GLOBALS['lang'];
		}
		else {
			$lang = trim( $_COOKIE['lang'] );
		}
		return $lang;
	}

}

?>