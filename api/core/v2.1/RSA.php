<?php

class RSA {
	public static function create( $bit = 512 ) {
		$res = openssl_pkey_new( array(
			'private_key_bits' => $bit,
		) );

		$privateKey = $publicKey = '';

		openssl_pkey_export( $res, $privateKey );
		$publicKey = openssl_pkey_get_details( $res );

		$publicKey = $publicKey['key'];

		return array(
			'privateKey' => $privateKey,
			'publicKey' => $publicKey,
		);
	}

	/**
	 * 使用私钥加密
	 */
	public static function encodeByPrivateKey( $data, $privateKey ) {
		$encrypted = '';
		openssl_private_encrypt( $data, $encrypted, $privateKey );
		$encrypted = base64_encode( $encrypted );
		return $encrypted;
	}

	/**
	 * 使用公钥解密
	 */
	public static function decodeByPublicKey( $data, $publicKey ) {
		$decrypted = '';
		openssl_public_decrypt( base64_decode( $data ), $decrypted, $publicKey );
		return $decrypted;
	}

	/**
	 * 使用公钥加密
	 */
	public static function encodeByPublicKey( $data, $publicKey ) {
		$encrypted = '';
		openssl_public_encrypt( $data, $encrypted, $publicKey );
		$encrypted = base64_encode( $encrypted );
		return $encrypted;
	}

	/**
	 * 使用私钥解密
	 */
	public static function decodeByPrivateKey( $data, $privateKey ) {
		$decrypted = '';
		openssl_private_decrypt( base64_decode( $data ), $decrypted, $privateKey );
		return $decrypted;
	}
}