<?php

/**
 * 获取币种7天内的金额
 */

require_once __DIR__.'/app.php';

class app_chart extends app {
	public function doRequest() {
		$parent = trim( post( 'parent' ) );
		$address = trim( post( 'address' ) );
		$contractAddress = trim( post( 'contractAddress' ) );

		if ( empty( $parent ) ) return $this->error( __( '参数错误' ) );
		// if ( !preg_match( '/^[0-9a-zA-Z]+$/', $parent ) ) return $this->error( __( '币种格式不正确' ) );
		if ( !$address ) return $this->error( __( '地址不能为空' ) );

		$mdl_address = $this->db( 'index', 'address2' );
		$address = $mdl_address->getByWhere( array( 'address' => $address ) );

		if ( !$address ) {
			return $this->returnSuccess( '', array( 'list' => array() ) );
		}

		if ( $contractAddress ) {
			$contract = $this->db( 'index', 'contract2' )->where( array( 'address' => $contractAddress ) );
			if ( !$contract ) return $this->error( __( '币种不存在' ) );
		}

		$days = array();
		$todayEnd = strtotime( date( 'Y-m-d 23:59:59' ) );
		for ( $i = 6; $i > 0; $i-- ) {
			$days[] = strtotime( '-'.$i.' days', $todayEnd );
		}
		$days[] = $todayEnd;

		$list = array();
		foreach ( $days as $day ) {
			if ( $contract ) {
				//如果是合约币，则不用算手续费
				$sql = "select (select sum(amount) from #@_transaction_in2 a left join #@_transaction2 b on a.transactionId=b.id where a.addressId=".$address['id']." and a.contractId=".$contract['id']." and b.`time`<=".$day.") as totalIn, sum(amount) as totalOut, 0 as fee from #@_transaction_out2 e left join #@_transaction2 f on e.transactionId=f.id where e.addressId=".$address['id']." and e.contractId=".$contract['id']." and f.`time`<=".$day;
			}
			else {
				//如果是eth，要算上手续费
				$sql = "select (select sum(amount) from #@_transaction_in2 a left join #@_transaction2 b on a.transactionId=b.id where a.addressId=".$address['id']." and a.contractId=0 and b.`time`<=".$day.") as totalIn, sum(amount) as totalOut, (select sum(fee) from #@_transaction_in2 c left join #@_transaction2 d on c.transactionId=d.id where c.addressId=".$address['id']." and c.contractId=0 and d.`time`<=".$day.") as fee from #@_transaction_out2 e left join #@_transaction2 f on e.transactionId=f.id where e.addressId=".$address['id']." and e.contractId=0 and f.`time`<=".$day;
			}

			//echo str_replace( '#@_', 'cc_', $sql ); exit;

			$tmp = $mdl_address->query( $sql );
			$tmp = $tmp[0];

			$total = $tmp['totalOut'] - $tmp['totalIn'] - $tmp['fee'];

			if ( $total < 0 ) $total = 0;

			$list[] = array( 'time' => $day, 'total' => $total );
		}

		return $this->returnSuccess( '', array( 'list' => $list ) );
	}
}