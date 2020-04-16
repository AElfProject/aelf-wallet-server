<?php

/**
 * Bit Converter
 * 一个无符号bit取值范围是0-255
 * @Author Today Nie
 * @Date 2018-02-012
 */

class BitConverter {
	/**
	 * 将ByteArray再hex后的字符串转成dec
	 * 低位在前
	 * 由于数字会超过PHP_INT_MAX，所以只能使用bcmath，其他方法同理
	 */
	public static function ByteArrayHexToDec( $hex ) {
		$bytes = array();
		for ( $i = 0; $i < strlen( $hex ); $i = $i + 2 ) {
			$bytes[] = hexdec( $hex[$i].$hex[$i + 1] );
		}

		return self::ByteArrayToDec( $bytes );
	}

	/**
	 * 将ByteArray转成dec
	 */
	public static function ByteArrayToDec( $bytes ) {
		$balance = '0';
		for ( $i = 0; $i < count( $bytes ); $i++ ) {
			$balance = bcadd( bcmul( $bytes[$i], bcpow( 256, $i ) ), $balance );
		}

		return $balance;
	}

	/**
	 * 将dec字符串转成ByteArray再hex成字符串
	 */
	public static function DecToByteArray( $dec ) {
		$bytes = array();
		$i = 0;
		$subDec = '0';
		while ( $dec > 0 ) {
			$i++;
			$mod = bcmod( $dec, bcpow( '256', $i ) );
			$subDec = bcadd( $subDec, $mod );
			$dec = bcsub( $dec, $subDec );
			$bytes[] = bcdiv( $mod, bcpow( '256', $i - 1 ) ) + ( $i > 2 ? 1 : 0 );

			if ( $i > 100 ) break;
		}

		return $bytes;
	}

	/**
	 * ByteArray hex
	 */
	public static function ByteArrayHex( $bytes ) {
		$hex = '';
		foreach ( $bytes as $byte ) {
			$h = dechex( $byte );
			$hex .= strlen( $h ) < 2 ? '0'.$h : $h;
		}
		return $hex;
	}
}