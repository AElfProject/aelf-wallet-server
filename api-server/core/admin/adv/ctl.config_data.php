<?php

/*
 @ctl_name = 设置数据@
*/

class ctl_config_data extends adminPage
{

	public function index_action () #act_name = 编辑#
	{
		$mdl_config_data = $this->loadModel( 'config_data' );
		if ( is_post() ) {
			$data = post( 'data' );
			foreach ( $data as $key => $val ) {
				$conf = $mdl_config_data->get( $key );
				if ( $conf ) $mdl_config_data->update( $key, $val );
				else $mdl_config_data->insert( $key, $val );
			}
			$this->sheader( $this->parseUrl() );
		}

		$configs = $mdl_config_data->getList();
		$this->setData( $configs, 'configs' );
		$this->display();
	}

}

?>