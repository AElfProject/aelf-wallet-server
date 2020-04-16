<?php

/*
 @ctl_name = 记录一览@
*/

require_once 'core/coin/manager.php';

class ctl_general_review extends adminPage
{

	public function index_action () #act_name = 列表#
	{


		$this->formData = $_GET;






		print_r($this->formData);
		//
		$coins = $this->db( 'index', 'coin' )->getList( null, null, 'name asc' );
		$this->setData( $coins, 'coins' );
		//
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );

		$this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
		$this->display();
	}
}

?>
