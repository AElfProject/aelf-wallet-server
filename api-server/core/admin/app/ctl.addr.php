<?php

/*
 @ctl_name = com_addr地址管理@
*/

class ctl_addr extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_com_addr = $this->db( 'index', 'com_addr' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );


		$sql = "select address, firstTime, coin from cc_com_addr";
		if ( $search['s'] )
			$sql .= ' where address ="'.$search['s'].'"';
		$sql .= ' group by address order by firstTime desc';

		$count = (int)$mdl_com_addr->query( "select count(distinct(address)) as count from cc_addr")[0]['count'];

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );

		$list = $mdl_com_addr->getListBySql( $page['outSql'] );

		$this->setData( $list, 'list' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $search, 'search' );

		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl()->set( 'act' )->set( 'page' ), 'contractUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->display();
	}
}

?>
