<?php

class mdl_relation extends mdl_base
{

	protected $lang = true;
	public $tableName	= '#@_relation';
	private $classlen	= CLASS_LEN;

	public function getParent ($class_id)
	{
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (empty($class_id)) return null;

		if (strlen($class_id) > $this->classlen) $class_id = right($class_id, $this->classlen, true);

		return $this->get($class_id);
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
			if ($key) $str .= ', ';
			$str .= $value['id'];
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
				if ($ro = $this->db->selectOne('id, name', $this->tableName, $where))
				{
					$classArray[$key]['id']		= $ro['id'];
					$classArray[$key]['name']	= $ro['name'];
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

	public function getChild ($class_id, $level = 6)
	{
		//�������ͬ���𱣴棬��level��ȡ��ʵ����
		static $classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$re = $this->db->select('id, ordinal, name, url, target', $this->tableName, $where, "Ordinal asc");
		foreach ( $re as $ro )
		{
			$classArray[]	= array(
				'id'		=> $ro['id'],
				'name'		=> $ro['name'],
				'sortnum'	=> $ro['ordinal'],
				'parent_id'	=> right($ro['id'], $this->classlen, true),
				'level'		=> strlen($class_id) / $this->classlen,
				'url'		=> $ro['url'],
				'target'	=> $ro['target']
			);
			$this->getChild($ro['id']);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild2 ($class_id, $level = 6)
	{
		//������зּ��𱣴�
		//static $classArray = array();
		$classArray = array();
		$class_id = $GLOBALS['system']->chkClass($class_id);

		if (strlen($class_id) / $this->classlen == $level)
			return $classArray;

		$where = "id like '$class_id".str_pad('', $this->classlen, '_')."'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		$re = $this->db->select('id, ordinal, name, url, target', $this->tableName, $where, "ordinal asc");
		foreach ( $re as $ro )
		{
			$childCount	= $this->getChildCount($ro['id']);
			$classArray[$ro['id']] = array(
				'id'		=> $ro['id'],
				'name'		=> $ro['name'],
				'sortnum'	=> $ro['ordinal'],
				'parent_id'	=> right($ro['id'], $this->classlen, true),
				'level'		=> strlen($class_id) / $this->classlen,
				'url'		=> $ro['url'],
				'target'	=> $ro['target'],
				'childCount'=> $childCount,
				'child'		=> $this->getChild2($ro['id'])
			);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
	}

	public function getChild2ForPermission ($class_id=0, $level = 6)
	{
		//������зּ��𱣴�
		//static $classArray = array();
		$classArray = array();
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
			$classArray[$ro['id']] = array(
				'id'		=> $ro['id'],
				'name'		=> $ro['name'],
				'child'		=> $this->getChild2ForPermission($ro['id'])
			);
		}

		if (is_array($classArray) && count($classArray))
			return $classArray;
		else return null;
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
		if ($ro = $this->db->selectOne('ordinal', $this->tableName, $where, "ordinal desc")) return $ro['ordinal'] + 10;
		else return 10;
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

	function deleteById ($class_id)
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

}

?>