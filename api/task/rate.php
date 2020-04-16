<?php

/**
 * 获取汇率数据
 */

require_once 'task.php';

class rate extends task {
	/**
	 * 当前币种
	 */
	protected $coin = '';

	function doRequest() {
		set_time_limit( 0 );

		while ( true ) {
			$this->interval();

			sleep( 10 );
			//break;
		}
	}

	function interval() {
		$url = '';
		$query = array(
            'key' => '',
        );
        $url = $url.'&'.http_build_query( $query );
        $result = json_decode( $this->request( $url ), true );
        $rates = $result['data'];
      	$this->redis()->set( 'market_elf', $rates );
        echo 'rate cache ok'.PHP_EOL;


		$url = '';
		$query = array(
            'key' => '',
        );
        $url = $url.'&'.http_build_query( $query );
        $result = json_decode( $this->request( $url ), true );
        $charts = $result['data'];
      	$this->redis()->set( 'chart_elf', $charts );
        echo 'chart cache ok'.PHP_EOL;
	}
}

$task = new rate;
$task->doRequest();