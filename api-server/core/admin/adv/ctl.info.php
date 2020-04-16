<?php

/*
 @ctl_name = 内容管理@
*/

class ctl_info extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_info = $this->db( 'index', 'info' );

		$where = array();
		$search = array();
		$search['cid'] = (int)get2( 'cid' );

		if ( $search['cid'] > 0 ) $where = "classId='".$search['cid']."'";

		$count = $mdl_info->getCount( $where );

		list( $sql, $params ) = $mdl_info->getListSql( null, $where, 'ordinal asc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_info->getListBySql( $page['outSql'] );

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

		$mdl_info = $this->db( 'index', 'info', 'master' );

		$info = $mdl_info->get( $id );

		if ( is_post() ) {
			$data = array();
			$data['isApproved'] = (int)post( 'isApproved' );
			$data['title'] = trim( post( 'title' ) );
			$data['content'] = trim( post( 'content' ) );
			$data['lang'] = trim( post( 'lang' ) );
			$data['classId'] = (int)get2( 'cid' );

			$this->formData = $_POST;

			if ( empty( $data['title'] ) ) $this->formError[] = '请填写标题';
			if ( empty( $data['lang'] ) ) $this->formError[] = '请选择语言';
			if ( empty( $data['content'] ) ) $this->formError[] = '请填写内容';

			if ( !$this->formError ) {
				if ( $info ) {
					$mdl_info->begin();
					$mdl_info->update( $data, $info['id'] );

					if ( !$mdl_info->isError() ) {
						$mdl_info->commit();
						$this->redis()->delete( 'info/'.$data['lang'] );
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_info->rollback();
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '编辑失败';
					}
				}
				else {
					$mdl_info->begin();
					$aid = $mdl_info->insert( $data );

					if ( !$mdl_info->isError() ) {
						$mdl_info->commit();
						$this->redis()->delete( 'info/'.$data['lang'] );

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '创建成功';
						$this->session( 'form-success-msg', '创建成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_info->rollback();

						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '创建失败';
						//print_r($mdl_smtp->getErrors());exit;
					}
				}
			}
		}

		if ( !$info ) {
			$info = array( 'isApproved' => 1, 'classId' => (int)get2( 'cid' ) );
		}
		else {
		}

		$this->formData = array_merge( $info, $this->formData );
		$this->setData( $this->formData, 'formData' );
		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );

		$this->setData( unserialize( LANGS ), 'langs' );

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

		$mdl_info = $this->db( 'index', 'info', 'master' );

		$info = $mdl_info->get( $id );

		if ( $info ) {
			$mdl_info->begin();
			$mdl_info->delete( $id );
			if ( !$mdl_info->isError() ) {
				$mdl_info->commit();
				$this->redis()->delete( 'info/'.$info['lang'] );
				return true;
			}
			else {
				$mdl_info->rollback();
				//print_r($mdl_info->getErrors());exit;
				return false;
			}
		}

		return true;
	}

}

?>