<?php

/**
 * APP版本检测和升级
 */

require_once __DIR__.'/../app.php';

class app_user_upgrade extends app {
	function doRequest() {
		$mdl_version = $this->db( 'index', 'version' );

		$device = $this->basePostData['device'];
        $version = $this->basePostData['version'];
        $isStore = (int)post('is_store');

		if (empty($device) || empty($version)){
            $this->error( __( '参数错误' ) );
        }

        $key =  $device;

        if ($isStore == 1){
            $key =  $key."_store";
        }

		$appVersion = $mdl_version->getByWhere(['key' => $key] );

		if ( !$appVersion ) return $this->error( __( 'APP不存在' ) );

		$latestVersion = $appVersion['verNo'];
        $minVersion = $appVersion['min_version'];

		/* 最新版本不提示升级 */
		if (version_compare($latestVersion, $version, '<=')){
            $appVersion['status'] = 0;
        }

        /* 用户版本低于最低版本，强制升级 */
        if (!empty($minVersion) && version_compare($minVersion, $version, '>=')){
            $appVersion['is_force'] = 1;
        }

        $appVersion['intro'] =  $appVersion['intro'] ? explode('#@#@#@', $appVersion['intro']) : [];
		return $this->json( array( 'status' => 200, 'data' => $this->getKeyValue( $appVersion, array( 'key' => 'id', 'appUrl', 'intro', 'verNo', 'status', 'is_force' ) ) ), false );
	}
}