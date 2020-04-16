<?php

/**
 * 图片加解密
 * @Author Today Nie
 * @Date 2018-04-24
 */

class image {
	private function __construct() {}

	public static function base64( $file ) {
		if ( !file_exists( $file ) ) return false;

		return 'data:'.getimagesize( $file )['mime'].';base64,'.chunk_split( base64_encode( file_get_contents( $file ) ) );
	}

	/**
	 * 加密
	 */
	public static function encode( $file, $salt ) {
		if ( !file_exists( $file ) ) return false;
		$salt = dechex( $salt );

		$encode = authcode( self::base64( $file ), 'e', $salt );

		file_put_contents( $file, $encode );

		return true;
	}

	/**
	 * 解密
	 */
	public static function decode( $file, $salt ) {
		if ( !file_exists( $file ) ) return false;
		$salt = dechex( $salt );

		$body = file_get_contents( $file );
		$decode = authcode( $body, 'd', $salt );

		file_put_contents( $file, $decode );

		return true;
	}

	public static function decodeFromMemory( $body, $salt ) {
		$salt = dechex( $salt );
		return authcode( $body, 'd', $salt );
	}
}