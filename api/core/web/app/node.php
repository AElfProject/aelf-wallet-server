<?php

/**
 * 获取币种对应的节点
 */

require_once __DIR__.'/app.php';

class app_node extends app {
	public function doRequest() {

		$parent = trim( post( 'parent' ) );

		//if ( empty( $parent ) ) return $this->error( __( '参数错误' ) );
        $mdl_node = $this->db('index', 'node');
        if($parent) {
         //   if (!preg_match('/^[0-9a-zA-Z]+$/', $parent)) return $this->error(__('币种格式不正确'));
            $tmp = $mdl_node->getList(null, array('coin' => $parent, 'status' => 1), 'sortnum asc');
        }else{
            $tmp = $mdl_node->getList(null, array('status' => 1), 'sortnum asc');
        }

		$list = array();
		foreach ( $tmp as $item ) {
			$list[] = array(
			    'coin'=>$item['coin'],
				'scheme' => $item['scheme'],
				'host' => $item['host'],
				'port' => $item['port'],
				'fullUrl' => $item['fullUrl'],
			);
		}

		return $this->returnSuccess( '', array( 'list' => $list ) );
	}
}