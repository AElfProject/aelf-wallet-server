<?php

/**
 * 谷歌验证
 * by today at 2017-09-19
 * 用法：
		//生成secret，保存到session中
		$gaSecret = $_SESSION['gaSecret'] ? $_SESSION['gaSecret'] : $GoogleAuthenticator->getSecret();
		$_SESSION['gaSecret'] = $gaSecret;
		//得到二维码url
		HTTP_ROOT.'qrcode.php?code='.urlencode( $GoogleAuthenticator->getQRCodeUrl( 'hcash_wallet:'.$gaSecret, $gaSecret ) )

		提交的时候
		$gaSecret = $_SESSION['gaSecret'];
		$GoogleAuthenticator->verifyCode( $gaSecret, $code )
		如果验证通过，则将secret保存到会员表的字段中
 */

class GoogleAuthenticator {
	private $codeLength = 6;

	private $base32Arr = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '=' );

	/**
	 * 得到一个随机的字符串
	 */
	public function getSecret( $len = 16 ) {
		$chars = $this->base32Arr;
		unset( $chars[32] );

		$str = '';
		for ( $i = 0; $i < $len; $i++ ) $str .= $chars[array_rand( $chars )];

		return $str;
	}

	/**
	 * 得到一个验证码
	 */
	public function getCode( $secret, $time = null ) {
		if ( $time === null ) $time = floor( time() / 30 );

		$key = $this->base32Decode( $secret );
		$time = chr(0).chr(0).chr(0).chr(0).pack( 'N*', $time );
		$hm = hash_hmac( 'SHA1', $time, $key, true );
		$offset = ord( substr( $hm, -1 ) ) & 15;
		$part = substr( $hm, $offset, 4 );
		$val = unpack( 'N', $part );
		$val = $val[1];
		$val = $val & 2147483647;

		return str_pad( $val % pow( 10, $this->codeLength ), $this->codeLength, '0', STR_PAD_LEFT );
	}

	/**
	 * 获取谷歌二维码网址
	 */
	public function getQRCodeUrl( $name, $secret ) {
		return "otpauth://totp/$name?secret=$secret";
	}

	/**
	 * 验证验证码是否正确
	 */
	public function verifyCode( $secret, $code, $discrepancy = 1, $time = null ) {
		if ( $time === null ) $time = floor( time() / 30 );

		for ( $i = 0 - $discrepancy; $i <= $discrepancy; $i++ ) {
			$nCode = $this->getCode( $secret, $time + $i );
			if ( $nCode == $code ) return true;
		}

		return false;
	}

	private function base32Encode( $secret, $padding = true ) {
		if ( empty( $secret ) ) return false;

		$chars = $this->base32Arr;
		$secret = str_split( $secret );
		$bin = '';

		for ( $i = 0; $i < count( $secret ); $i++ ) $bin .= str_pad( base_convert( ord( $secret[$i] ), 10, 2 ), 8, '0', STR_PAD_LEFT );

		$binArr = str_split( $bin, 5 );
		$base32 = '';
		$i = 0;

		while ( $i < count( $binArr ) ) {
			$base32 .= $chars[base_convert( str_pad( $binArr[$i], 5, '0' ), 2, 10 )];
			$i++;
		}

		if ( $padding && ( $x = strlen( $bin ) % 40 ) != 0 ) {
			switch ( $x ) {
				case 8: $base32 .= str_repeat( $chars[32], 6 ); break;
				case 16: $base32 .= str_repeat( $chars[32], 4 ); break;
				case 24: $base32 .= str_repeat( $chars[32], 3 ); break;
				case 32: $base32 .= $chars[32]; break;
			}
		}

		return $base32;
	}

	private function base32Decode( $secret ) {
		if ( empty( $secret ) ) return false;

		$chars = $this->base32Arr;
		$base32Flipped = array_flip( $chars );
		$paddingCount = substr_count( $secret, $chars[32] );
		$allowVal = array( 6, 4, 3, 1, 0 );

		if ( !in_array( $paddingCount, $allowVal ) ) return false;
		for ( $i = 0; $i < 4; $i++ ) {
			if ( ( $paddingCount == $allowVal[$i] ) && ( substr( $secret, 0 - $allowVal[$i] ) != str_repeat( $chars[32], $allowVal[$i] ) ) ) return false;
		}

		$secret = str_replace( $chars[32], '', $secret );
		$secret = str_split( $secret );
		$bin = '';

		for ( $i = 0; $i < count( $secret ); $i = $i + 8 ) {
			$x = '';

			if ( !in_array( $secret[$i], $chars ) ) return false;

			for ( $j = 0; $j < 8; $j++ ) $x .= str_pad( base_convert( @$base32Flipped[@$secret[$i + $j]], 10, 2 ), 5, '0', STR_PAD_LEFT );

			$bit = str_split( $x, 8 );

			for ( $k = 0; $k < count( $bit ); $k++ ) $bin .= ( ( $s = chr( base_convert( $bit[$k], 2, 10 ) ) ) || ( ord( $s ) == 48 ) ? $s : '' );
		}

		return $bin;
	}
}