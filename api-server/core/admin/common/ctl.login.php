<?php

class ctl_login extends adminPage
{

	public function index_action ()
	{
		$this->setData( get2( 'k' ), 'k' );

		$this->setData( $this->getFormKey( 'admin_login' ), 'formKey' );

		$define_langs = unserialize( LANGS );
		$this->setData($define_langs, 'langs');
		$this->setData(count($define_langs), 'langs_count');
		//$this->setData(trim($_SESSION['admin_lang']), 'admin_lang');
		$this->setData(trim($_COOKIE['admin_lang']), 'admin_lang');
		$this->display('common/login');
	}

	public function login_action ()
	{

		$name = trim(post('name'));
		$pass = trim(post('pass'));
		$code = trim(post('code'));
		$verifyCode = trim(post('verifyCode'));

		if ( !$this->verifyFormKey( 'admin_login', $_POST['formKey'] ) ) $this->sheader( null, '表单Token验证失败，请重新提交' );
		//if ( empty( $code ) ) $this->sheader( null, '请输入验证码' );

        if ( strtolower( $verifyCode ) != strtolower( $_SESSION['yzm'] ) ) $this->sheader(null, $this->lang->verification_code_error);

		if (strlen($pass) < 6) $this->sheader(null, $this->lang->username_password_not_correct);

//		$GoogleAuthenticator = new GoogleAuthenticator;
		$pass = $this->md5($pass);
		$u = $this->loadModel('user');
		if ($user = $u->getUserByName($name))
		{
//			if ( !$GoogleAuthenticator->verifyCode( $user['gaSecret'], $code ) )
//				$this->sheader( null, '您输入的动态验证码不正确，请重新填写' );
			if ($pass == $user['password'])
			{
                $this->session('admin_user_id', $user['id']);
			    if($user['role'] == 1){
                    $this->session('admin_user_id', -1);
                }

				$this->session('admin_user_shell', $this->md5($user['id'].$user['name'].$user['password']));

				$data = array(
					'lastLoginIP'	=> ip(),
					'lastLoginDate'	=> time(),
					'loginCount'	=> $user['loginCount'] + 1
				);
				$u->updateUserById($data, $user['id']);

				$this->sheader('?con=admin&ctl=default');
			} else $this->sheader(null, $this->lang->username_password_not_correct);
		} else $this->sheader(null, $this->lang->username_password_not_correct);
	}

	public function logout_action ()
	{
		$this->session('admin_user_id', '');
		$this->session('admin_user_shell', '');
		echo '<script>window.parent.window.location = "?con=admin&ctl=common/login&k='.get2( 'k' ).'";</script>';
		exit;
	}

}

?>