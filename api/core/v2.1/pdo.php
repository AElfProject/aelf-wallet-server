<?php

/**
 * PDO操作类，兼容旧的程序方法，可以直接切换使用
 * by today at 2017-12-20
 */

class PdoByToday {

	private $host;
	private $port;
	private $name;
	private $pwd;
	private $db;
	private $pre = 'cc_';

	private $client;
	private $trans;  //是否已经存在未提交的事务
	private $errors = array();

	private $sqlList = array();

	function __construct( $host, $port, $name, $pwd, $db ) {
		$this->host = $host;
		$this->port = $port;
		$this->name = $name;
		$this->pwd = $pwd;
		$this->db = $db;

		$this->connect();
	}

	function connect() {
		try {
			$dsn = 'mysql:host='.$this->host.';'.( $this->port ? 'port='.$this->port.';' : '' ).'dbname='.$this->db.';charset=utf8';
			//echo $dsn;exit;
			$this->client = new PDO( $dsn, $this->name, $this->pwd );
			$this->client->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
		catch ( PDOException $ex ) {
			$this->client = null;
			echo 'Connect database faild.2';
			//echo $ex->getMessage();
			exit;
		}
	}

	/**
	 * 查询SQL语句，得到返回结果
	 */
	function query( $s, $p = array() ) {
		$s = str_replace('#@_', $this->pre, $s);
		$s = str_replace( '\\\\\\', '\\', $s );

		$obj = array(
			'sql' => $s,
			'params' => $p,
			'startTime' => microtime(),
		);

		try {
			$stmt = $this->client->prepare( $s );
			$stmt->execute( $p );
			$data = $stmt->fetchAll( PDO::FETCH_ASSOC );
			$stmt->closeCursor();
			unset( $stmt );

			$obj['endTime'] = microtime();
			$obj['status'] = 1;
			$this->sqlList[] = $obj;

			return $data;
		}
		catch ( PDOException $ex ) {
			$this->setError( $ex->getMessage() );

			$obj['endTime'] = microtime();
			$obj['status'] = 0;
			$this->sqlList[] = $obj;

			return false;
		}
	}

	/**
	 * 执行SQL语句，得到返回结果
	 */
	function execute( $s, $p ) {
		$s = str_replace('#@_', $this->pre, $s);
		$s = str_replace( '\\\\\\', '\\', $s );

		$obj = array(
			'sql' => $s,
			'params' => $p,
			'startTime' => microtime(),
		);

		try {
			$stmt = $this->client->prepare( $s );
			$r = $stmt->execute( $p );
			$stmt->closeCursor();
			unset( $stmt );

			$obj['endTime'] = microtime();
			$obj['status'] = 1;
			$this->sqlList[] = $obj;

			return $r;
		}
		catch ( PDOException $ex ) {
			$this->setError( $ex->getMessage() );

			$obj['endTime'] = microtime();
			$obj['status'] = 0;
			$this->sqlList[] = $obj;

			return false;
		}
	}

	function exec( $s ) {
		$s = str_replace('#@_', $this->pre, $s);
		$s = str_replace( '\\\\\\', '\\', $s );

		$obj = array(
			'sql' => $s,
			'startTime' => microtime(),
		);

		try {
			$result = $this->client->exec( $s );

			$obj['endTime'] = microtime();
			$obj['status'] = 1;
			$this->sqlList[] = $obj;

			return $result;
		}
		catch ( PDOException $ex ) {
			$this->setError( $ex->getMessage() );

			$obj['endTime'] = microtime();
			$obj['status'] = 0;
			$this->sqlList[] = $obj;

			return false;
		}
	}

	function getSqlList() {
		return $this->sqlList;
	}

	function getSelectSql( $array = null, $tableName = '', $condition = null, $order = '', $limit = '' ) {
		$fields = array();
		$where = array();
		$params = array();

		if ( empty( $tableName ) ) return false;

		//field
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) $fields[] = '`'.$value.'`';
		}
		else {
			if ( $array ) $fields[] = $array;
			else $fields[] = '*';
		}
		if ( empty( $fields ) ) $fields[] = '*';

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select '.implode( ',', $fields ).' from '.$tableName.( $where ? ' where '.implode( ' and ', $where ) : '' ).( $order ? ' order by '.$order : '' ).( $limit ? ' limit '.$limit : '' );
		return array( $sql, $params );
	}

	function select( $array = null, $tableName = '', $condition = null, $order = '', $limit = '' ) {
		list( $sql, $params ) = $this->getSelectSql( $array, $tableName, $condition, $order, $limit );
		return $this->query( $sql, $params );
	}

	function selectOne( $array = null, $tableName = '', $condition = null, $order = '' ) {
		$re	= $this->select( $array, $tableName, $condition, $order, '1' );
		return $re[0];
	}

	function getInsertSql( $array = null, $tableName = '' ) {
		$fields = array();
		$replace = array();
		$params = array();

		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				$fields[] = '`'.$key.'`';
				$replace[] = '?';
				$params[] = $value;
			}
		}

		$sql = 'insert into '.$tableName.'('.implode( ',', $fields ).') values('.implode( ',', $replace ).')';
		return array( $sql, $params );
	}

	function insert( $array = null, $tableName = '' ) {
		list( $sql, $params ) = $this->getInsertSql( $array, $tableName );
		return $this->execute( $sql, $params );
	}

	function insert_id() {
		return $this->client->lastInsertId();
	}

	function getUpdateSql( $array = null, $tableName = '', $condition = null ) {
		$fields = array();
		$where = array();
		$params = array();

		foreach ( $array as $key => $value ) {
			$fields[] = '`'.$key.'`=?';
			$params[] = $value;
		}

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'update '.$tableName.' set '.implode( ',', $fields ).( $where ? ' where '.implode( ' and ', $where ) : '' );
		return array( $sql, $params );
	}

	function update( $array = null, $tableName = '', $condition = null ) {
		list( $sql, $params ) = $this->getUpdateSql( $array, $tableName, $condition );
		return $this->execute( $sql, $params );
	}

	function getDeleteSql( $tableName = '', $condition = null ) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'delete from '.$tableName.( $where ? ' where '.implode( ' and ', $where ) : '' );
		return array( $sql, $params );
	}

	public function delete( $tableName = '', $condition = null ) {
		list( $sql, $params ) = $this->getDeleteSql( $tableName, $condition );
		return $this->execute( $sql, $params );
	}

	public function getMin( $tb, $cell, $condition ) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select min('.$cell.') as cnt from '.$tb.( $where ? ' where '.implode( ' and ', $where ) : '' );

		$re = $this->query( $sql, $params );
		return $re[0]['cnt'];
	}

	public function getMax( $tb, $cell, $condition ) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select max('.$cell.') as cnt from '.$tb.( $where ? ' where '.implode( ' and ', $where ) : '' );

		$re = $this->query( $sql, $params );
		return $re[0]['cnt'];
	}

	public function getSum($tb, $cell, $condition) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select sum('.$cell.') as cnt from '.$tb.( $where ? ' where '.implode( ' and ', $where ) : '' );

		$re = $this->query( $sql, $params );
		return $re[0]['cnt'];
	}

	public function getOne( $tb, $cell, $condition ) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select `'.$cell.'` as cell from '.$tb.( $where ? ' where '.implode( ' and ', $where ) : '' ).' limit 1';

		$re = $this->query( $sql, $params );
		return $re[0]['cell'];
	}

	public function getCount( $tb, $condition ) {
		$where = array();
		$params = array();

		if ( is_array( $condition ) ) {
			foreach ( $condition as $key => $value ) {
				if ( empty( $key ) || is_numeric( $key ) ) $where[] = $value;
				else {
					$where[] = '`'.$key.'`=?';
					$params[] = $value;
				}
			}
		}
		else {
			if ( $condition ) $where[] = $condition;
		}

		$sql = 'select count(*) as cnt from '.$tb.( $where ? ' where '.implode( ' and ', $where ) : '' );

		$re = $this->query( $sql, $params );
		return (int)$re[0]['cnt'];
	}

	public function begin() {
		if ( $this->trans ) return;
		$this->trans = true;
		$this->clearError();
		//$this->exec( 'set autocommit=0' );
		$this->client->beginTransaction();
	}

	public function rollback() {
		if ( !$this->trans ) return;
		$this->trans = false;
		$this->client->rollBack();
	}

	public function commit() {
		if ( !$this->trans ) return;
		$this->trans = false;
		$this->client->commit();
	}

	public function isError() {
		if ( $this->errors ) return true;
		return false;
	}

	public function getErrors() {
		return $this->errors;
	}

	private function setError( $msg ) {
		$this->errors[] = $msg;
	}

	private function clearError() {
		$this->errors = array();
	}

	public function close () {
		$this->client = null;
	}

	public function __destruct () {
		$this->client = null;
	}

}

?>