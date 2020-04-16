<?php

/*
 @ctl_name = APP参数设置@
*/

class ctl_setting extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_setting = $this->db( 'index', 'config_data' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );
		if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $search['s'] ) ) unset( $search['s'] );

		//查询数量
		$where = array();
		if ( $search['s'] ) $where[] = "(`key` like '%".$search['s']."%')";

		$count = $mdl_setting->getCount( $where );

		list( $sql, $params ) = $mdl_setting->getListSql( null, $where, '`key` asc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_setting->getListBySql( $page['outSql'] );

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

		$mdl_setting = $this->db( 'index', 'config_data', 'master' );

		$setting = $mdl_setting->get( $id );

		if ( is_post() ) {
			$data = array();
			$data['key'] = trim( post( 'key' ) );

            if (stripos(post('val'), '{') !== false) {
                $data['val'] = trim(stripslashes(post('val')));
            } else {
                $data['val'] = trim(post('val'));
            }

			$data['tip'] = trim( post( 'tip' ) );

			$this->formData = $_POST;

            if (stripos($_POST['val'], '{') !== false) {
                $this->formData['val'] = stripslashes($this->formData['val']);
            }

			if ( empty( $data['key'] ) ) $this->formError[] = '请填写key';
			// if ( empty( $data['val'] ) ) $this->formError[] = '请填写值';
			if ( $data['val']  == '' ) $this->formError[] = '请填写值';

			if ( $setting ) {
				unset( $data['key'] );

				if ( !$this->formError ) {
					$mdl_setting->begin();
					$mdl_setting->update( $data, $setting['id'] );

					if ( !$mdl_setting->isError() ) {
						$mdl_setting->commit();
						$this->redis()->delete( 'configs' );
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_setting->rollback();
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '编辑失败';
					}
				}
			}
			else {
				if ( $mdl_setting->getCount( array( 'key' => $data['key'] ) ) > 0 ) $this->formError[] = 'key已经存在，请更换';
				if ( !$this->formError ) {
					$mdl_setting->begin();
					$aid = $mdl_setting->insert( $data );

					if ( !$mdl_setting->isError() ) {
						$mdl_setting->commit();
						$this->redis()->delete( 'configs' );

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '创建成功';
						$this->session( 'form-success-msg', '创建成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_setting->rollback();

						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '创建失败';
						//print_r($mdl_setting->getErrors());exit;
					}
				}
			}
		}

		if ( !$setting ) {
			$setting = array();
		}

		$this->formData = array_merge( $setting, $this->formData );
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );

		$this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
		$this->display();
	}

}

?>