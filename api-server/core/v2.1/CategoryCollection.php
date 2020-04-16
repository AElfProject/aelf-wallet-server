<?php

/**
 * 多级分类集合
 * 分类表必须要有id和parentId字段，getDataBySort返回的数据中level会自动计算
 * 例子是AFN后台文章分类管理
 * @Author: Today Nie
 * @Created: 2016-11-10
 */

class CategoryCollection {
	private static $inst;
	private static $data;

	/**
	 * 不允许实例化
	 */
	private function __construct() {}

	/**
	 * 初始化数据
	 */
	public static function init( $mdl, $where = [] ) {
		$array = array();

		$data = $mdl->getList( null, $where, 'sortnum asc' );
		foreach ( $data as $item ) {
			$array[$item['id']] = $item;
			$array[$item['id']]['child'] = self::instance()->initChilds( $item['id'], $data );
		}
		//print_r( $array ); exit;

		self::instance()->data[$mdl->getTableName()] = $array;
	}

	/**
	 * 直接获取数据，通过child判断是否有子分类
	 */
	public static function get( $mdl, $id = 0 ) {
		if ( $id == 0 ) return self::instance()->data[$mdl->getTableName()];
		else return self::instance()->data[$mdl->getTableName()][$id];
	}

	/**
	 * 获取排序后的数据，level是层级，path是路径
	 */
	public static function getDataBySort( $mdl, $id = 0, & $array = [], $level = 1, $path = '0' ) {
		$allData = self::instance()->data[$mdl->getTableName()];

		$data = $id == 0 ? $allData : $allData[$id];
		if ( $id == 0 ) {
			foreach ( $data as $item ) {
				if ( $item['parentId'] == $id ) {
					$item['level'] = $level;
					$item['path'] = $path.'-'.$item['id'];
					$array[$item['id']] = $item;
					self::instance()->getDataBySort( $mdl, $item['id'], $array, $level + 1, $item['path'] );
				}
			}
		}
		else {
			foreach ( $data['child'] as $item ) {
				$array[$item] = $allData[$item];
				$array[$item]['level'] = $level;
				$array[$item]['path'] = $path.'-'.$item;
				self::instance()->getDataBySort( $mdl, $item, $array, $level + 1, $array[$item]['path'] );
			}
		}

		return $array;
	}

	/**
	 * 根据getDataBySort的结果，生成层级的数据
	 */
	public static function initLevel( $allData, $id = 0 ) {
		$list = array();
		foreach ( $allData as $item ) {
			if ( $item['parentId'] == $id ) {
				if ( $item['child'] ) $item['childDetail'] = self::initLevel( $allData, $item['id'] );
				$list[] = $item;
			}
		}
		return $list;
	}

	/**
	 * 获取指定分类的路径
	 * 可以通过这个得到所有父级分类的ID
	 */
	public static function getPath( $mdl, $id = 0 ) {
		if ( $id == 0 ) return '';

		$data = $mdl->getList( array( 'id', 'parentId' ) );
		$parentId = -1;
		$path = '';
		foreach ( $data as $item ) {
			if ( $item['id'] == $id ) {
				$parentId = $item['parentId'];
				break;
			}
		}

		if ( $parentId == -1 ) return $parentId;  //-1 当前分类不存在

		$path = $parentId;
		while ( $parentId > 0 ) {
			foreach ( $data as $item ) {
				if ( $item['id'] == $parentId ) {
					$parentId = $item['parentId'];
					$path = $parentId.'-'.$path;
				}
			}
		}
		return $path;
	}

	/**
	 * 获取指定顶级分类的ID
	 */
	public static function getTopParentId( $mdl, $id = 0 ) {
		$path = self::instance()->getPath( $mdl, $id );
		if ( empty( $path ) ) return 0;
		if ( $path == '0' ) return $id;

		$arr = explode( '-', $path );
		return (int)$arr[1];
	}

	/**
	 * 获取指定分类的所有子分类
	 */
	public static function getAllChildsId( $mdl, $id = 0, & $childs = [] ) {
		$data = self::instance()->data['basic_'.$mdl->getTableName()];
		if ( !$data ) {
			$data = $mdl->getList( array( 'id', 'parentId' ) );
			self::instance()->data['basic_'.$mdl->getTableName()] = $data;
		}

		foreach ( $data as $item ) {
			if ( $item['parentId'] == $id ) {
				$childs[] = $item['id'];
				self::instance()->getAllChildsId( $mdl, $item['id'], $childs );
			}
		}
		return $childs;
	}

	/**
	 * 获取分类的文章数量统计
	 * @param $catList 待统计的分类数组
	 * @param $where 统计条件，只支持数组
	 * @param $refresh 是否强制刷新统计数据
	 */
	public static function getArticleCnt( $mdl, $mdlCat, & $catList, $where = array(), $refresh = false, $field = 'categoryId' ) {
		$cntList = self::instance()->data['cntList'];
		if ( !$cntList || $refresh ) {
			$sql = "select count(*) as cnt, ".$field." from ".$mdl->getTableName().self::instance()->getWhereStr( $where )." group by ".$field;
			$cntList = $mdl->getListBySql( $sql );
			self::instance()->data['cntList'] = $cntList;
		}

		foreach ( $catList as $key => $item ) {
			foreach ( $cntList as $cnt ) {
				if ( $item['id'] == $cnt[$field] ) {
					$catList[$key]['cnt'] = $cnt['cnt'];
					break;
				}
			}
			$catList[$key]['cntByChilds'] = (int)$catList[$key]['cnt'] + ( $item['id'] > 0 ? self::instance()->getArticleCntByChilds( $cntList, self::instance()->getAllChildsId( $mdlCat, $item['id'] ), $field ) : 0 );
		}
	}

	/**
	 * 获取所有子分类的文章数量统计
	 */
	private static function getArticleCntByChilds( $cntList, $childs, $field = 'categoryId' ) {
		$all = 0;
		foreach ( $cntList as $cnt ) {
			if ( in_array( $cnt[$field], $childs ) ) $all += $cnt['cnt'];
		}
		return $all;
	}

	/**
	 * 数组生成sql条件
	 */
	public static function getWhereStr( $where = array() ) {
		if ( !$where ) return '';
		if ( is_array( $where ) ) {
			$tmp = array();
			foreach ( $where as $key => $val ) {
				$tmp[] = ( empty( $key ) || is_numeric( $key ) ) ? $val : '`'.$key.'`=\''.( get_magic_quotes_gpc() ? $val : mysql_real_escape_string( $val ) ).'\'';
			}
			return ' where '.implode( ' and ', $tmp );
		}
		else return ' where '.$where;
	}

	/**
	 * 初始化子分类
	 */
	private static function initChilds( $id, $data ) {
		$childs = array();
		foreach ( $data as $item ) {
			if ( $item['parentId'] == $id ) {
				$childs[] = $item['id'];
			}
		}
		return $childs;
	}

	private static function instance() {
		if ( ! isset( self::$inst ) ) {
			self::$inst = new self();
		}
		return self::$inst;
	}
}

?>