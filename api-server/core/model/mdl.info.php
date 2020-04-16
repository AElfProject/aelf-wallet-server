<?php

class mdl_info extends mdl_base
{

	protected $lang = true;
	protected $tableName		= '#@_info';
	private $classTableName		= '#@_infoclass';
	private $userTableName		= '#@_user';
	//private $listField		= 'id, classId, ordinal, title, alias, url, pageTitle, keywords, description, publishedDate, source, author, intro, imageUrl, files, isApproved, isTop, isHot, isRecommended, hits, createdUserId, createdDate, lastModifiedUserId, lastModifiedDate';

	public function getListSql ($column, $where, $order)
	{
		$column[] = 'id';
		$column[] = 'classId';
		if ( $this->lang ) {
			$where['0#lang'] = $this->getLang();
			$where['1#lang'] = $this->getLang();
		}
		return $this->db->getSelectMultipleSql(array($column, array('className' => 'name'), array('userName' => 'name', 'userDisplayName' => 'displayName')), array($this->tableName, $this->classTableName, $this->userTableName), array('0#classId=1#id', '0#createdUserId=2#id'), $where, $order);
		//exit;
	}

	public function getListSqlForWeb ($column, $where, $order, $cnt = null)
	{
		$cnt = (int)$cnt;
		$column[] = 'id';
		$column[] = 'classId';
		if ( $this->lang ) $where['lang'] = $this->getLang();
		return $this->db->getSelectSql($column, $this->tableName, $where, $order, $cnt > 0 ? "0, $cnt" : '');
	}

	public function getListSqlForCompany ($column, $where, $order)
	{
		$column[] = 'id';
		$column[] = 'classId';
		if ( $this->lang ) $where['lang'] = $this->getLang();
		return $this->db->getSelectSql($column, $this->tableName, $where, $order);
	}

	public function getListBySql ($sql)
	{
		return $this->db->query($sql);
	}

	public function getIdsByTags( $tags ) {
		$column = array( 'id' );
		if ( $this->lang ) $where['lang'] = $this->getLang();

		$tags_infosId = array(0);
		if ( count( $tags ) > 0 ) {
			$sql = "select distinct info_id from #@_tags where ";
			$i = 0;
			foreach ( $tags as $tag ) {
				$sql .= ( $i > 0 ? ' or ' : '' )."title='".$tag['title']."'";
				$i++;
			}
			foreach ( $this->getListBySql( $sql ) as $val ) {
				$tags_infosId[] = (int)$val['info_id'];
			}
			return $tags_infosId;
		}
		else return $tags_infosId;
	}

	public function getListByTags( $info, $tags, $column, $where, $order = 'ordinal desc', $cnt = 10 ) {
		$column[] = 'id';
		$column[] = 'classId';
		if ( $this->lang ) $where['lang'] = $this->getLang();

		$list = array();
		$tags_infosId = $this->getIdsByTags( $tags );
		$tagWhere = $where;
		$tagWhere[] = 'id in ('.implode( ',', $tags_infosId ).')';
		$list = $this->getList( $column, $tagWhere, $order, $cnt );

		if ( count( $list ) < $cnt ) {
			$tags_infosId[] = $info['id'];
			$where[] = "classId like '".$info['classId']."%'";
			$where[] = 'id not in ('.implode( ',', $tags_infosId ).')';
			$list2 = $this->getList( $column, $where, $order, ( $cnt - count( $list ) ) );
			$list = array_merge( $list, $list2 );
		}
		return $list;
	}

	public function getHotList( $info, $column, $where, $order = 'hits desc, ordinal desc', $cnt = 10 ) {
		$column[] = 'id';
		$column[] = 'classId';
		$where[] = "id<>'".$info['id']."'";
		if ( $this->lang ) $where['lang'] = $this->getLang();

		$startDay = 0;
		$startTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $startDay, date( 'Y' ) );
		$whileWhere = $where;
		$whileWhere[] = 'lastModifiedDate>='.$startTime;
		while ( parent::getCount( $whileWhere ) < $cnt ) {
			$startDay++;
			//if ( $startDay > 10 ) break;
			$startTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $startDay, date( 'Y' ) );
			$whileWhere = $where;
			$whileWhere[] = 'lastModifiedDate>='.$startTime;
		}
		$startTime = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $startDay, date( 'Y' ) );
		$where[] = 'lastModifiedDate>='.$startTime;
		$list = $this->getList( $column, $where, $order, $cnt );
		return $list;
	}

	public function getListByClass ($class_id, $cnt, $orderBy = null, $start = 0)  //用于对外接口的输出
	{
		return $this->db->select(null, $this->tableName, array('classId' => $class_id, 'isApproved' => 1), $orderBy, $start.', '.$cnt);
	}

	public function getInfoOrdinal ($class_id)
	{
		$where = array( 'classId' => $class_id );
		if ( $this->lang ) $where['lang'] = $this->getLang();
		if ($ro = $this->db->selectOne('ordinal', $this->tableName, $where, "ordinal desc")) return $ro['ordinal'] + 10;
		else return 10;
	}

	public function getCount ($where = '')
	{
		$where .= ( empty( $where ) ? '' : ' and ' ).( $this->lang ? "lang='".$this->getLang()."'" : '' );
		return $this->db->getCount($this->tableName, $where);
	}

	public function add ($data)
	{
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		//echo $this->db->getInsertSql($data, $this->tableName);exit;
		$this->db->insert($data, $this->tableName);
		return $this->db->insert_id();
	}

	public function update ($data, $id)
	{
		$id = (int)$id;
		return $this->db->update($data, $this->tableName, array('id'=>$id));
	}

	public function updateHits ($id)
	{
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set hits=hits+1 where id='{$id}'");
	}

	public function updateReplys ($id)
	{
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set replys=replys+1 where id='{$id}'");
	}

	public function updateDownloadCount ($id)
	{
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set downloadCount=downloadCount+1 where id='{$id}'");
	}

	public function delete ($id)
	{
		return $this->db->delete($this->tableName, array('id'=>$id));
	}

}

?>