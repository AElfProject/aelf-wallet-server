<?php

/**
 * 注册表
 * @Author: Today Nie
 * @Created: 2016-02-03
 */

class Registry {
	private static $inst;
	private static $data;

	/**
	 * 不允许实例化
	 */
	private function __construct() {}

	public static function get( $key ) {
		return self::instance()->data[$key];
	}

	public static function set( $key, $val ) {
		self::instance()->data[$key] = $val;
	}

	private static function instance() {
		if ( ! isset( self::$inst ) ) {
			self::$inst = new self();
		}
		return self::$inst;
	}
}

?>