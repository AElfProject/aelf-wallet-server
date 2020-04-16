<?php

/*
 @ctl_name = 管理员邮箱@
*/

class ctl_adminemail extends adminPage
{

	protected $validates = array(
		'email' => array(
			'method' => 'email',
			'message' => ''
		)
	);

	function ctl_adminemail() {
		parent::adminPage();
		$this->validates['email']['message'] = $this->lang->email_can_not_empty;
	}

	public function index_action () #act_name = 编辑#
	{
		$mdl = $this->loadModel( 'adminemail' );
		if ( is_post() ) {
			$data = post( 'data' );
			$this->cookie->setCookie( 'adminemail_edit', $data );

			$this->validate( $data );

			if ( $mdl->update( $data ) ) {
				$this->cookie->clearArrayCookie( 'adminemail_edit', $data );
				$this->sheader( $this->parseUrl() );
			}
			else {
				$this->sheader( null, $this->lang->mailbox_setting_failed );
			}
		}
		else {
			$this->setData( $mdl->get() );
			$this->setData( $this->cookie->getCookie( 'adminemail_edit' ), 'form' );
			$this->display();
		}
	}

}

?>