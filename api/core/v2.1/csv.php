<?php

/**
 * CSV解析
 * @Author Today Nie
 * @Date 2017-09-18
 */

class CSV {
	private static $data = array();  //所有行
	private static $kdata = array();  //指定行

	private function __construct() {}

	/**
	 * 获取所有行
	 */
	public function getAllLine( $csvFile ) {
		if ( !file_exists( $csvFile ) ) return false;

		$dataKey = md5( $csvFile );
		if ( isset( self::$data[$dataKey] ) ) return self::$data[$dataKey];

		$data = array();
		$file = fopen( $csvFile, 'r' );

		while ( $line = fgetcsv( $file ) ) {
			$data[] = $line;
		}

		self::$data[$dataKey] = $data;
		return $data;
	}

	/**
	 * 根据每一行的第一个字段获取行
	 */
	public function getByKey( $key, $csvFile ) {
		$dataKey = md5( $csvFile.'-'.$key );
		if ( isset( self::$kdata[$dataKey] ) ) return self::$kdata[$dataKey];

		$data = self::getAllLine( $csvFile );
		foreach ( $data as $val ) {
			if ( $val[0] == $key ) {
				self::$kdata[$dataKey] = $val;
				return $val;
			}
		}
	}
}