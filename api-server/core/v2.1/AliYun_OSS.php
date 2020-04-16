<?php

/**
 * 阿里云OSS
 * @Author: Today Nie
 * @Created: 2016-02-29
 */

require_once 'core/AliYun_OSS/autoload.php';
use OSS\OssClient;
use OSS\Core\OssException;

class AliYun_OSS {
	private static $inst;
	private static $keyID;
	private static $keySecret;
	/**
	 * end point一定要注意，不是完整的域名，在控制台内当鼠标移入外部域名时，变色的那部分就是end point
	 * 如果end point填写错了，即使连接上，也会提示找不到bucket
	 */
	private static $endPoint;
	private static $ossClient;

	private function AliYun_OSS() {}

	private static function instance() {
		if ( ! isset( self::$inst ) ) {
			self::$inst = new self();
			self::$inst->keyID = '';
			self::$inst->keySecret = '';
			self::$inst->endPoint = '';
			self::$inst->ossClient = new OssClient( self::$inst->keyID, self::$inst->keySecret, self::$inst->endPoint );
		}
		return self::$inst;
	}

	/**
	 * 上传本机文件至OSS
	 */
	public static function uploadFile( $bucket, $ossFilename = '', $locFilename ) {
		if ( empty( $locFilename ) ) return array( 'status' => false );
		if ( !file_exists( $locFilename ) ) return array( 'status' => false );

		if ( empty( $ossFilename ) ) $ossFilename = end( explode( DIRECTORY_SEPARATOR, $locFilename ) );

		try {
			self::instance()->ossClient->uploadFile( $bucket, $ossFilename, $locFilename );
		}
		catch ( OssException $e ) {
			return array( 'status' => false, 'locFilename' => $locFilename, 'msg' => $e->getMessage() );
		}

		return array( 'status' => true, 'locFilename' => $locFilename );
	}

	/**
	 * 删除OSS上的文件
	 * param $ossFilename 可以是文件完整路径，也可以是多个文件完整路径的数组
	 */
	public static function delFile( $bucket, $ossFilename ) {
		if ( empty( $ossFilename ) ) return array( 'status' => false );

		try {
			if ( is_array( $ossFilename ) ) self::instance()->ossClient->deleteObjects( $bucket, $ossFilename );
			else self::instance()->ossClient->deleteObject( $bucket, $ossFilename );
		}
		catch ( OssException $e ) {
			return array( 'status' => false, 'msg' => $e->getMessage() );
		}

		return array( 'status' => true );
	}

	/**
	 * 判断OSS上是否存在文件
	 * result =1表示存在文件，空表示不存在文件
	 */
	public static function fileExists( $bucket, $ossFilename ) {
		if ( empty( $ossFilename ) ) return array( 'status' => false );

		try {
			$result = self::instance()->ossClient->doesObjectExist( $bucket, $ossFilename );
		}
		catch ( OssException $e ) {
			return array( 'status' => false, 'msg' => $e->getMessage() );
		}

		return array( 'status' => true, 'result' => $result );
	}

}

?>