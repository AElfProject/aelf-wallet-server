<?php

/*
 @ctl_name = onchain_addr币种设置@
*/


class ctl_addr extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_addr = $this->db( 'index', 'com_addr' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );
        $search['coin'] = trim( get2( 'coin' ) );
		//if ( !preg_match( '/^[a-z]+$/', $search['s'] ) ) unset( $search['s'] );

		//查询数量
		$where = array();
		if ( $search['s'] )
			$where[] = 'address ="'.$search['s'].'"';
        if ( $search['s'] )
            $where[] = 'coin ="'.$search['coin'].'"';
		//$where[] = "(`address` like '%".$search['s']."%')";

		$count = $mdl_addr->getCount( $where );

		//list( $sql, $params ) = $mdl_addr->getListSql( null, $where, 'firstTime desc' );

		$sql = "select address, firstTime, coin from cc_com_addr";
		if ( $search['s'] )
			$sql .= ' where address ="'.$search['s'].'"';
        if ( $search['coin'] )
            $sql .= ' where coin ="'.$search['coin'].'"';
		$sql .= ' group by address order by firstTime desc';

		$count = (int)$mdl_addr->query( "select count(distinct(address)) as count from cc_com_addr")[0]['count'];

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_addr->getListBySql( $page['outSql'] );

        $mdl_address_statistics = $this->db('index', 'address_statistics');
        $coins = $mdl_address_statistics->query("select coin as name from #@_address_statistics group by coin");
        $this->setData( $coins, 'coins' );

		$this->setData( $list, 'list' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $search, 'search' );

		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl()->set( 'act' )->set( 'page' ), 'contractUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->display();
	}

	public function eos_addr_action () #act_name = eos地址列表#
	{
		$mdl_addr = $this->db( 'index', 'eos_new_account_task' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );
		//if ( !preg_match( '/^[a-z]+$/', $search['s'] ) ) unset( $search['s'] );

		//查询数量
		$where = array();
		if ( $search['s'] )
			$where[] = 'address ="'.$search['s'].'"';
		//$where[] = "(`address` like '%".$search['s']."%')";

		$count = $mdl_addr->getCount( $where );

		//list( $sql, $params ) = $mdl_addr->getListSql( null, $where, 'firstTime desc' );

		$sql = "select account_name, add_time, 'eos' as coin from cc_eos_new_account_task where 1=1";
		if ( $search['s'] )
			$sql .= ' and address ="'.$search['s'].'"';

		$sql .= ' and trans_status=2 group by account_name order by add_time desc';

		$count = (int)$mdl_addr->query( "select count(distinct(account_name)) as count from cc_eos_new_account_task where trans_status=2")[0]['count'];

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_addr->getListBySql( $page['outSql'] );

		$this->setData( $list, 'list' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $search, 'search' );

		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl()->set( 'act' )->set( 'page' ), 'contractUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->display();
	}


	public function hc_addr_action () #act_name = hc地址列表#
	{
		$mdl_addr = $this->db( 'index', 'addr_temp' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );
		//if ( !preg_match( '/^[a-z]+$/', $search['s'] ) ) unset( $search['s'] );

		//查询数量
		$where = array();
		if ( $search['s'] )
			$where[] = 'address ="'.$search['s'].'"';
		//$where[] = "(`address` like '%".$search['s']."%')";

		$count = $mdl_addr->getCount( $where );

		//list( $sql, $params ) = $mdl_addr->getListSql( null, $where, 'firstTime desc' );

		$sql = "select address, firsttime, coin from cc_addr_temp";
		if ( $search['s'] )
			$sql .= ' where address ="'.$search['s'].'"';
		$sql .= ' group by address order by firsttime desc';

		$count = (int)$mdl_addr->query( "select count(distinct(address)) as count from cc_addr_temp")[0]['count'];

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_addr->getListBySql( $page['outSql'] );

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
