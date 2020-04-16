<?php

class mdl_infoClass extends mdl_base
{

	protected $lang = true;
	protected $tableName = '#@_infoclass';
	private $classlen = CLASS_LEN;

	public function getByAlias( $alias ) {
		$where = "alias='$alias'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		if ( $ro = $this->db->selectOne( null, $this->tableName, $where ) ) {
			return $ro;
		}
		else return null;
	}

	public function getParent ($class_id)
	{
		return $this->get($this->getParentId($class_id));
	}

	public function getParentId ($class_id)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);
		if (empty($class_id)) return null;
		if (strlen($class_id) > $this->classlen) $class_id = right($class_id, $this->classlen, true);
		return $class_id;
	}

	public function getParentList ($class_id)
	{
		$parentCnt	= 0;
		$classArray	= array();
		$class_id	= $GLOBALS['system']->chkClass($class_id);

		if (empty($class_id)) return null;

		$parentCnt	= strlen($class_id) / $this->classlen;

		for ($i = 1; $i <= $parentCnt; $i++)
			$classArray[]['id']	= substr($class_id, 0, $this->classlen * $i);

		return $classArray;
	}

	public function getParentListStr ($class_id)
	{
		$classArray	= $this->getParentList($class_id);

		if (!is_array($classArray)) return null;

		foreach ($classArray as $key=>$value)
		{
			if ($key > 0) $str .= '\', \'';
			$str .= $value['id'];
		}

		return $str;
	}

	public function getParentNameStr ($class_id, $split_str = ', ')
	{
		$str	= $this->getParentListStr($class_id);
		$where = "id in ('$str')";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$list	= $this->db->select('name', $this->tableName, $where, "id asc");

		$str	= '';
		foreach ($list as $key=>$value)
		{
			if ($key > 0) $str .= $split_str;
			if ($key == count($list) - 1)
				$str .= '<font color="#FF3300">'.$value['name'].'</font>';
			else $str .= $value['name'];
		}

		return $str;
	}

	public function getParentListArray ($class_id)
	{
		$class_id	= $GLOBALS['system']->chkClass($class_id);

		if (empty($class_id)) return null;

		$classArrayOld	= $this->getParentList($class_id);
		$classStr		= $this->getParentListStr($class_id);
		$classArray		= array();

		if (is_array($classArrayOld))
		{
			foreach ($classArrayOld as $key=>$value)
			{
				$where = "id='{$value['id']}'";
				if ( $this->lang ) {
					$where .= " and lang='".$this->getLang()."'";
				}
				if ($ro = $this->db->selectOne(null, $this->tableName, $where))
				{
					$classArray[$key] = $ro;
				}
				else break;
			}
			return $classArray;
		} else return null;
	}

	public function getChildCount ($class_id)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);
		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->getCount($this->tableName, $where);
	}

	public function getInfoCount ($class_id, $isApproved = -1)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);

		$result = $this->db->query("select count(*) as cnt from #@_info where".($isApproved >= 0 ? " isApproved='{$isApproved}' and" : "")." classId like '$class_id%'");
		return $result[0]['cnt'];
	}

	public function getFirstChild( $class_id ) {
		$where = "id like '$class_id".str_pad( '', $this->classlen, '_' )."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->selectOne( null, $this->tableName, $where, "ordinal asc" );
	}

	public function getChild ($class_id, $level = 20)
	{
		static $classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$re = $this->db->select('id, ordinal, name, url, imageUrl, files, defaultDisplayMode, extend, alias', $this->tableName, $where, "Ordinal asc");
		foreach ( $re as $ro )
		{
			$classArray[]	= array(
				'id'					=> $ro['id'],
				'name'					=> $ro['name'],
				'sortnum'				=> $ro['ordinal'],
				'parent_id'				=> right($ro['id'], $this->classlen, true),
				'level'					=> strlen($class_id) / $this->classlen,
				'hasSon'				=> $this->getChildCount($ro['id']) > 0 ? 1 : 0,
				'url'					=> $ro['url'],
				'imageUrl'				=> $ro['imageUrl'],
				'alias'					=> $ro['alias'],
				'files'					=> $ro['files'],
				'defaultDisplayMode'	=> $ro['defaultDisplayMode'],
				'extend'				=> $ro['extend']
			);
			$this->getChild($ro['id'], $level);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChildForPermission ($class_id=0, $level = 20)
	{
		static $classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		/*
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		*/
		$re = $this->db->select('id, name', $this->tableName, $where, "ordinal asc");
		foreach ( $re as $ro )
		{
			$classArray[]	= array(
				'id'	=> $ro['id'],
				'name'	=> $ro['name'],
				'level'	=> strlen($class_id) / $this->classlen
			);
			$this->getChildForPermission($ro['id'], $level);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild3 ($class_id, $level = 20)
	{
		//static $classArray = array();
		$classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$re = $this->db->select('id, name, alias, domain, classStyle, pageTitle, imageUrl', $this->tableName, $where, "ordinal asc");
		foreach ( $re as $ro )
		{
			$childCount	= $this->getChildCount($ro['id']);
			$classArray[$ro['id']] = array(
				'id'			=> $ro['id'],
				'name'			=> $ro['name'],
				'alias'			=> $ro['alias'],
				'domain'		=> $ro['domain'],
				'classStyle'	=> $ro['classStyle'],
				'pageTitle'		=> $ro['pageTitle'],
				'imageUrl'		=> $ro['imageUrl'],
				'parent_id'		=> right($ro['id'], $this->classlen, true),
				'level'			=> strlen($class_id) / $this->classlen,
				'childCount'	=> $childCount,
				'child'			=> $this->getChild2($ro['id'], $level)
			);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild2 ($class_id, $level = 20)
	{
		//static $classArray = array();
		$classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$re = $this->db->select('id, ordinal, name, url, defaultDisplayMode', $this->tableName, $where, "ordinal asc");
		foreach ( $re as $ro )
		{
			$childCount	= $this->getChildCount($ro['id']);
			$classArray[$ro['id']] = array(
				'id'					=> $ro['id'],
				'name'					=> $ro['name'],
				'sortnum'				=> $ro['ordinal'],
				'parent_id'				=> right($ro['id'], $this->classlen, true),
				'level'					=> strlen($class_id) / $this->classlen,
				'url'					=> $ro['url'],
				'defaultDisplayMode'	=> $ro['defaultDisplayMode'],
				'childCount'			=> $childCount,
				'child'					=> $this->getChild2($ro['id'], $level)
			);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild4 ($class_id, $level = 20, $show_in_nav = null, $fields = array())
	{
		//static $classArray = array();
		$classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( isset( $show_in_nav ) ) {
			$where .= " and showInNav=".(int)$show_in_nav;
		}
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}

		$fields[] = 'id';
		$fields[] = 'name';
		$fields[] = 'alias';
		$re = $this->db->select( implode( ',', $fields ) , $this->tableName, $where, "ordinal asc" );
		foreach ( $re as $ro ) {
			$childCount	= $this->getChildCount($ro['id']);
			$classArray[$ro['id']] = array(
				'parent_id'		=> right($ro['id'], $this->classlen, true),
				'level'			=> strlen($class_id) / $this->classlen,
				'childCount'	=> $childCount,
				'child'			=> $this->getChild4($ro['id'], $level)
			);
			foreach ( $fields as $field ) {
				$classArray[$ro['id']][$field] = $ro[$field];
			}
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild5( $class_id, $cnt ) {
		$class_id = $GLOBALS['system']->chkClass($class_id);

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->select('id, name, alias', $this->tableName, $where, "ordinal asc", $cnt);
	}

	public function getChildClassIdToInsert ($parent_id)
	{
		$parent_id	= $GLOBALS['system']->chkClass($parent_id);

		$where = "id like '$parent_id".str_pad('', $this->classlen, '_')."'";
		if ($ro = $this->db->selectOne('id', $this->tableName, $where, "id desc")) return $parent_id.(right($ro['id'], $this->classlen) + 1);
		else return $parent_id.'1'.str_pad( '', $this->classlen - 2, '0' ).'1';
	}

	public function getChildClassOrdinal ($parent_id)
	{
		$parent_id	= $GLOBALS['system']->chkClass($parent_id);

		$where = "id like '$parent_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		if ($ro = $this->db->selectOne('ordinal', $this->tableName, $where, "ordinal desc", '1')) return $ro['ordinal'] + 10;
		else return 10;
	}

	public function chkAlias ($class_id, $alias)
	{
		$where = "alias='$alias' and id<>'$class_id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->getCount($this->tableName, $where);
	}

	public function create ($data)
	{
		if ( $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		return $this->db->insert($data, $this->tableName);
	}

	public function updateById ($data, $class_id)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);

		$where = "id='$class_id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->update($data, $this->tableName, $where);
	}

	public function update ($data, $class_id)
	{
		return self::updateById($data, $class_id);
	}

	public function deleteById ($class_id)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if ($this->getChildCount($class_id) < 1)
		{
			$where = "id='$class_id'";
			if ( $this->lang ) {
				$where .= " and lang='".$this->getLang()."'";
			}
			return $this->db->delete($this->tableName, $where);
		}
		else return false;
	}

	public function getColumns ($userid, $class_id)
	{
		if ( ! $userid || $userid == -1 ) $userid = 2;
		$class_id	= $GLOBALS['system']->chkClass($class_id);
		$data		= unserialize($GLOBALS['system']->loadConf("columns/$userid.$class_id.columns"));
		return $data;
	}

	public function saveColumns ($data, $userid, $class_id)
	{
		if ( ! $userid || $userid == -1 ) $userid = 2;
		return $GLOBALS['system']->saveConf("columns/$userid.$class_id.columns", serialize($data));
	}

	public function getColumnsForCompany ($userid, $class_id)
	{
		if ( ! $userid || $userid == -1 ) $userid = 2;
		$class_id	= $GLOBALS['system']->chkClass($class_id);
		$data		= unserialize($GLOBALS['system']->loadConf("columns/$userid.$class_id.columns"));
		if (is_array($data) || strlen($class_id) <= $this->classlen) return $data;
		else return self::getColumns($userid, self::getParentId($class_id));
	}

	public function saveColumnsForCompany ($data, $userid, $class_id)
	{
		if ( ! $userid || $userid == -1 ) $userid = 2;
		return $GLOBALS['system']->saveConf("columns/$userid.$class_id.columns", serialize($data));
	}

}

?>