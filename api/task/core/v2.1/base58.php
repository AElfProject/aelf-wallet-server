<?php

/**
 * Base58
 * @Author Today Nie
 * @Date 2018-02-06
 */

class base58 {
	private static $s = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

	public static function neoDecode( $str ) {
		$str = strrev( $str );
		$s = '';
		for ( $i = 0; $i < strlen( $str ); $i += 2 ) {
			$s .= $str[$i + 1].$str[$i];
		}
		return $s;
	}

	public static function checkDecode( $str, $head = 1, $foot = 4 ) {
		$str = bin2hex( self::bc2bin( self::decode( $str ) ) );

		if ( $head ) $str = substr( $str, $head * 2 );
		if ( $foot ) $str = substr( $str, 0, - $foot * 2 );

		return $str;
	}

	public static function checkEncode( $str, $prefix = 128, $compressed = true ) {
		$str = hex2bin( $str );
		if ( $prefix ) $str = chr( $prefix ).$str;
		if ( $compressed ) $str .= chr( 0x01 );

		$str .= substr( hash( 'sha256', hash( 'sha256', $str, true ), true ), 0, 4 );

		$base58 = self::encode( self::bin2bc( $str ) );
		for ( $i = 0; $i < strlen( $str ); $i++ ) {
			if ( $str[$i] != "\x00" ) break;
			$base58 = '1'.$base58;
		}

		return $base58;
	}

	public static function decode( $str, $len = 58 ) {
		return self::base2dec( $str, $len, self::$s );
	}

	public static function encode( $dec, $len = 58 ) {
		return self::dec2base( $dec, $len, self::$s );
	}

	public static function bc2bin( $str ) {
		return self::dec2base( $str, 256 );
	}

	public static function bin2bc( $dec ) {
		return self::base2dec( $dec, 256 );
	}

	public static function base2dec( $str, $len, $digits = '' ) {
		bcscale( 0 );

		if ( $len < 37 ) $str = strtolower( $str );
		if ( !$digits ) $digits = self::digits( $len );

		$size = strlen( $str );
		$dec = '0';
		for ( $i = 0; $i < $size; $i++ ) {
			$s = strpos( $digits, $str[$i] );
			$pow = bcpow( $len, $size - $i - 1 );
			$dec = bcadd( $dec, bcmul( $s, $pow ) );
		}

		return (string)$dec;
	}

	public static function dec2base( $dec, $len, $digits = '' ) {
		bcscale( 0 );

		if ( !$digits ) $digits = self::digits( $len );

		$str = '';
		while ( $dec > $len - 1 ) {
			$s = bcmod( $dec, $len );
			$dec = bcdiv( $dec, $len );
			$str = $digits[$s].$str;
		}
		$str = $digits[intval( $dec )].$str;
		return (string)$str;
	}

	public static function digits( $len ) {
		if ( $len > 64 ) {
			$str = '';
			for ( $i = 0; $i < 256; $i++ ) $str .= chr( $i );
		}
		else {
			$str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
		}
		$str = substr( $str, 0, $len );
		return (string)$str;
	}
}