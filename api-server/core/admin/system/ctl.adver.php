<?php

/*
 @ctl_name = 广告管理@
*/

class ctl_adver extends adminPage
{

	public function index_action () #act_name = 广告列表#
	{
		$mdl_adver	= $this->loadModel( 'adver' );
		$pageSql	= $mdl_adver->getListSql( null, null, 'id asc' );
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_adver->getListBySql( $page['outSql'] );

		$this->setData( $data, 'data' );
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );
		$this->display();
	}

	public function add_action () #act_name = 添加广告#
	{
		$mdl_adver = $this->loadModel( 'adver' );
		if ( is_post() ) {
			$data = post('data');
			if ( $data = self::_filter( $data ) ) {
				$image_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'swf' );
				$filepath = date( 'Y-m' );
				$this->file->createdir( 'data/upload/'.$filepath );
				if ( $_FILES['pic']['size'] > 0 ) {
					$filename = $this->file->upfile( $image_exts, $_FILES['pic'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
					if ( $filename ) {
						$data['pic'] = $filename;
					}
				}

				if ( $mdl_adver->insert( $data ) ) {
					$this->sheader( $this->parseUrl()->set( 'act' ) );
				}
				else {
					$this->file->deletefile( UPDATE_DIR.$data['pic'] );
					$this->sheader(null, $this->lang->add_adver_failed);
				}
			}
			else {
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else {
			$this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑广告#
	{
		$id			= (int)get2( 'id' );
		$mdl_adver	= $this->loadModel( 'adver' );
		$data		= $mdl_adver->get( $id );
		if ( !$data ) $this->sheader( null, $this->lang->current_record_not_exists );
		if ( is_post() ) {
			$oldData = $data;
			$data = post('data');
			if ( $data = self::_filter( $data ) ) {
				$image_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'swf' );
				$filepath = date( 'Y-m' );
				$this->file->createdir( 'data/upload/'.$filepath );
				if ( $_FILES['pic']['size'] > 0 ) {
					$filename = $this->file->upfile( $image_exts, $_FILES['pic'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
					if ( $filename ) {
						$data['pic'] = $filename;
					}
				}

				if ( $mdl_adver->update( $data, $id ) ) {
					if ( $data['pic'] ) $this->file->deletefile( UPDATE_DIR.$oldData['pic'] );
					$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
				}
				else {
					$this->file->deletefile( UPDATE_DIR.$data['pic'] );
					$this->sheader(null, $this->lang->edit_adver_failed);
				}
			}
			else {
				$this->sheader(null, $this->lang->your_submit_incomplete);
			}
		}
		else {
			$this->setData( $data, 'data' );
			$this->setData( $this->parseUrl()->set( 'act' )->set( 'id' ), 'returnUrl' );
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除广告#
	{
		if ( is_post() ) {
			$ids = post( 'ids' );
			if ( is_array( $ids ) ) {
				foreach ( $ids as $k => $v ) {
					self::_delete( (int)$v );
				}
			}
		}
		else {
			self::_delete( (int)get2( 'id' ) );
		}
		$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' ) );
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$mdl_adver	= $this->loadModel( 'adver' );
		$adver		= $mdl_adver->get( $id );
		if ( !$adver ) {
			$this->sheader( null, $this->lang->current_record_not_exists.' '.$id );
		}
		if ( $mdl_adver->delete( $id ) ) {
			$this->file->deletefile( UPDATE_DIR.$adver['pic'] );
		}
		else {
			$this->sheader( null, $this->lang->delete_link_failed.' '.$id );
		}
	}

	private function _filter ($data)
	{
		foreach ( $data as $k => $v ) {
			$data[$k] = trim( $v );
		}
		$data['width']	= (int)$data['width'];
		$data['height']	= (int)$data['height'];
		$data['marginTop']	= (int)$data['marginTop'];
		$data['marginSide']	= (int)$data['marginSide'];
		$data['isApproved']	= limitInt((int)$data['isApproved'], 0, 1);
		$data['isAutoClose']	= limitInt((int)$data['isAutoClose'], 0, 1);
		$data['isOnlyDefault']	= limitInt((int)$data['isOnlyDefault'], 0, 1);
		$data['isShowOnce']	= limitInt((int)$data['isShowOnce'], 0, 1);

		if ( empty( $data['mode'] ) || empty( $data['title'] ) ) {
			return false;
		}

		return $data;
	}

}

?>