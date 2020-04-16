<?php

/*
 @ctl_name = 反馈管理@
*/


class ctl_feedback extends adminPage
{
	public function ctl_feedback() {
		parent::adminPage();

	}

	public function index_action () #act_name = 列表#
	{
		$mdl_member = $this->db( 'index', 'member' );
		$mdl_feedback = $this->db( 'index', 'feedback');

		$search = array();
		$search['s'] = trim( get2( 's' ) );

		$where = array();
		$count = $mdl_feedback->getCount( $where );

		$sql = "SELECT f.*, m.username, m.phoneArea, m.phone FROM #@_feedback f LEFT JOIN #@_member m ON f.userId=m.id ORDER BY f.time DESC";
		// $sql = "SELECT s.* FROM #@_feedback s ORDER BY time DESC";

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_feedback->getListBySql( $page['outSql'] );

		$this->setData( $list, 'list' );
		$this->setData( $page['pageStr'], 'pager' );

		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );
		$this->setData( $search, 'search' );
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

		if ( $error > 0 ) $this->session( 'form-error-msg', '有'.$error.'删除失败' ); else $this->session( 'form-success-msg', '删除成功' );
		$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
	}

	private function _delete( $id ) {
		$id = (int)$id;

		$mdl_feedback = $this->db( 'index', 'feedback', 'master' );

		$feedback = $mdl_feedback->get( $id );

		if ( $feedback ) {
			$files = array();
			
			$mdl_feedback->begin();
			$mdl_feedback->delete( $id );
			if ( !$mdl_feedback->isError() ) {
				$mdl_feedback->commit();
				return true;
			}
			else {
				$mdl_feedback->rollback();
				return false;
			}
		}

		return true;
	}
	
	
	public function dealwith_action() {
		$id = (int)get2( 'id' );
		$mdl_feedback = $this->db( 'index', 'feedback', 'master' );
		$feedback = $mdl_feedback->get( $id );
		if ( $feedback ) {
			if ( $mdl_feedback->update( array( 'readTime'=>time() ), $id ) ) {
				$this->session( 'form-success-msg', '操作成功' );
				echo(1);
				exit;
			}
			else {
				$this->session( 'form-success-msg', '操作失败' );
				echo(0);
				exit;
			}
		}
		else
		{
			$this->session( 'form-error-msg', '记录不存在' );
			echo(0);
			exit;
		}
	}

}

?>