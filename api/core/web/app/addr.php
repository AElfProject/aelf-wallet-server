<?php

/**
 * 保存address
 */

require_once __DIR__.'/app.php';

class app_addr extends app {
	public function doRequest() {
		$parent = trim( post( 'parent' ) );
		$s = trim( post( 'address' ) );
		$udid = trim( post( 'udid' ) );
		$androidNoticeToken = trim( post( 'androidNoticeToken' ) );
		$iosNoticeToken = trim( post( 'iosNoticeToken' ) );
		$deviceInfo = trim( post( 'deviceInfo' ) );

		if ( empty( $s ) ) return $this->error( __( '参数错误' ) );
		if ( empty( $parent ) ) return $this->error( __( '参数错误' ) );

		$mdl_address = $this->db( 'index', 'addr', 'master' );

		//$address = $mdl_address->getByWhere( array( 'address' => $s, 'coin' => $parent ) );

		//if ( $address ) $mdl_address->updateByWhere( array( 'lastTime' => time(), 'lang' => $this->getLang(), 'android_notice_token' => $androidNoticeToken, 'ios_notice_token' => $iosNoticeToken, 'device_info' => $deviceInfo ), array( 'address' => $address['address'], 'coin' => $address['coin'] ) );
		//else {
			$mdl_address->insert( array( 'coin' => $parent, 'address' => $s, 'udid' => $udid, 'firsttime' => time(), 'lasttime' => time(), 'lang' => $this->getLang(), 'android_notice_token' => $androidNoticeToken, 'ios_notice_token' => $iosNoticeToken, 'device_info' => $deviceInfo ) );
		//}

		return $this->returnSuccess( '' );
	}
}