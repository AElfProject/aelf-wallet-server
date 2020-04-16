<?php

/*
 @ctl_name = 邮件服务器@
*/

class ctl_smtp extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_smtp = $this->db( 'index', 'smtp' );

		$count = $mdl_smtp->getCount();

		list( $sql, $params ) = $mdl_smtp->getListSql( null, null, 'id asc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_smtp->getListBySql( $page['outSql'] );

		$this->setData( $list, 'list' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $search, 'search' );

		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->display();
	}

	public function edit_action () #act_name = 编辑#
	{
		$id = (int)get2( 'id' );

		$mdl_smtp = $this->db( 'index', 'smtp', 'master' );

		$smtp = $mdl_smtp->get( $id );

		if ( is_post() ) {
			$data = array();
			$data['name'] = trim( post( 'name' ) );
			$data['senderName'] = trim( post( 'senderName' ) );
			$data['senderEmail'] = trim( post( 'senderEmail' ) );
			$data['sign'] = trim( post( 'sign' ) );
			$data['host'] = trim( post( 'host' ) );
			$data['port'] = trim( post( 'port' ) );
			$data['username'] = trim( post( 'username' ) );
			$data['password'] = trim( post( 'password' ) );
			$data['status'] = (int)post( 'status' );

			$this->formData = $_POST;

			if ( empty( $data['name'] ) ) $this->formError[] = '请填写名称';
			if ( empty( $data['senderName'] ) ) $this->formError[] = '请填写发件人名称';
			if ( empty( $data['senderEmail'] ) ) $this->formError[] = '请填写发件人邮箱';
			if ( empty( $data['host'] ) ) $this->formError[] = '请填写SMTP主机';
			if ( empty( $data['port'] ) ) $this->formError[] = '请填写SMTP端口';

			if ( $smtp ) {
				if ( empty( $data['username'] ) && empty( $data['password'] ) ) {
					//都没有填写，则不修改
					unset( $data['username'] );
					unset( $data['password'] );
				}
				else {
					if ( empty( $data['username'] ) ) $this->formError[] = '请填写SMTP用户名';
					if ( empty( $data['password'] ) ) $this->formError[] = '请填写SMTP密码';
				}
			}
			else {
				if ( empty( $data['username'] ) ) $this->formError[] = '请填写SMTP用户名';
				if ( empty( $data['password'] ) ) $this->formError[] = '请填写SMTP密码';
			}

			if ( !$this->formError ) {
				if ( $smtp ) {
					if ( empty( $data['username'] ) && empty( $data['password'] ) ) {}
					else {
						//加密
						$data['salt'] = $this->rndStr( 15 );
						$data['username'] = authcode( $data['username'], 'e', $data['salt'] );
						$data['password'] = authcode( $data['password'], 'e', $data['salt'] );
					}

					$mdl_smtp->begin();
					$mdl_smtp->update( $data, $smtp['id'] );

					if ( !$mdl_smtp->isError() ) {
						$mdl_smtp->commit();
						$this->redis()->delete( 'smtp' );
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_smtp->rollback();
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '编辑失败';
					}
				}
				else {
					//加密
					$data['salt'] = $this->rndStr( 15 );
					$data['username'] = authcode( $data['username'], 'e', $data['salt'] );
					$data['password'] = authcode( $data['password'], 'e', $data['salt'] );

					$mdl_smtp->begin();
					$aid = $mdl_smtp->insert( $data );

					if ( !$mdl_smtp->isError() ) {
						$mdl_smtp->commit();
						$this->redis()->delete( 'smtp' );

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '创建成功';
						$this->session( 'form-success-msg', '创建成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_smtp->rollback();

						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '创建失败';
						//print_r($mdl_smtp->getErrors());exit;
					}
				}
			}
		}

		if ( !$smtp ) {
			$smtp = array( 'port' => 25 );
		}
		else {
			//密码是加密的，所以编辑的时候不要显示
			$smtp['username'] = '';
			$smtp['password'] = '';
		}

		$this->formData = array_merge( $smtp, $this->formData );
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );

		$this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
		$this->display();
	}

	public function delete_action () #act_name = 删除#
	{
		$error = 0;

		if ( is_post() ) {
			$ids = post( 'ids' );
			if ( is_array( $ids) ) {
				foreach ( $ids as $key => $value ) {
					if ( !self::_delete( trim( $value ) ) ) $error++;
				}
			}
		}
		else {
			if ( !self::_delete( get2( 'id' ) ) ) $error++;
		}

		if ( $error > 0 ) $this->session( 'form-error-msg', '有'.$error.'个删除失败' ); else $this->session( 'form-success-msg', '删除成功' );
		$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
	}

	private function _delete( $id ) {
		$id = (int)$id;

		$mdl_smtp = $this->db( 'index', 'smtp', 'master' );

		$smtp = $mdl_smtp->get( $id );

		if ( $smtp ) {
			$mdl_smtp->begin();
			$mdl_smtp->delete( $id );
			if ( !$mdl_smtp->isError() ) {
				$mdl_smtp->commit();
				$this->redis()->delete( 'smtp' );
				return true;
			}
			else {
				$mdl_smtp->rollback();
				//print_r($mdl_smtp->getErrors());exit;
				return false;
			}
		}

		return true;
	}

}

?>