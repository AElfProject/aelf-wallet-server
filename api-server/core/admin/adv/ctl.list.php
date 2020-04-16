<?php

/*
 @ctl_name = 新闻分类管理@
*/

class ctl_list extends adminPage
{

	private $refreshUrl;
	private $pcat;

	/**
	 * level 从0开始，限制当前可以创建的子类级别
	 */
	private $level;

	function ctl_list ()
	{
		parent::adminPage();

		$pid = trim( get2( 'pid' ) );
		$mdl_infoclass = $this->loadModel( 'infoClass' );
		$this->pcat = $mdl_infoclass->get( $pid );
		if ( !$this->pcat ) $this->sheader( null, '' );
		$this->level = (int)get2( 'level' );
		if ( $this->level < 0 ) $this->level = 0;
		$this->refreshUrl = '?con=admin&ctl=adv/list&pid='.$this->pcat['id'].'&level='.$this->level.'&';
	}

	public function index_action () #act_name = 列表#
	{
		$mdl_infoClass = $this->loadModel( 'infoClass' );
		$infoclass = $mdl_infoClass->getChild( $this->pcat['id'] );

		$this->setData( $infoclass );
		$this->setData( $this->parseUrl(), 'refreshUrl' );
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $this->level, 'level' );
		$this->setData( $this->pcat['id'], 'pid' );
		$this->display();
	}

	public function add_action () #act_name = 添加#
	{
		$infoclass = $this->loadModel('infoClass');

		$parent_id = trim( get2( 'parentId' ) );
		if ( empty( $parent_id ) || !preg_match( '/^'.$this->pcat['id'].'[0-9]*/', $parent_id ) ) {
			$parent_id = $this->pcat['id'];
		}
		if ( strlen( $parent_id ) / 3 > strlen( $this->pcat['id'] ) / 3 + $this->level ) {
			$this->sheader( null, '' );
		}
		$parent = $infoclass->get( $parent_id );

		if ( is_post() )
		{
			$data = post( 'data' );
			$data['id'] = $infoclass->getChildClassIdToInsert( $parent_id );
			$data['ordinal'] = $infoclass->getChildClassOrdinal( $parent_id );
			if ( empty( $data['template'] ) ) $data['template'] = $parent['template'] ? $parent['template'] : 'list';
			$data['extend'] = $parent['extend'];
			$data['info'] = $parent['info'];
			$data['other'] = $parent['other'];

			//配置
			$config = array();
			$config['extend'] = unserialize( $parent['extend'] );
			$config['other'] = unserialize( $parent['other'] );

			if ( empty( $data['name'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}

			if ( $config['extend']['hasAlias'] ) {
				$data['alias'] = strtolower( $data['alias'] );
				$data['alias'] = str_replace( ' ', '-', $data['alias'] );
				$data['alias'] = str_replace( '&', '-', $data['alias'] );
				//$data['alias'] = str_replace( '/', '', $data['alias'] );
				$data['alias'] = str_replace( '?', '-', $data['alias'] );
				$data['alias'] = str_replace( '|', '-', $data['alias'] );
				$data['alias'] = str_replace( '\\', '-', $data['alias'] );
				$data['alias'] = preg_replace( '/-{2,}/i', '-', $data['alias'] );

				if ( empty( $data['alias'] ) ) {
					$this->sheader( null, $this->lang->your_submit_incomplete );
				}

				if ( $infoclass->chkAlias( 0, $data['alias'] ) > 0 ) {
					$this->sheader( null, $this->lang->alias_has_been_occupied );
				}
			}

			//图片
			$new_files = array();
			$filepath = date( 'Y-m' );
			$this->file->createdir( 'data/upload/'.$filepath );
			$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
			$bounds = post( 'bounds' );
			$boundsIndex = -1;
			$boundsIndex++;
			if ( $_FILES['imageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['imageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['imageUrl'] = $filename;
					$new_files[] = UPDATE_DIR.$filename;
					//原图
					$sourcepic = $this->file->nameExtend( $filename, '_o' );
					@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
					$new_files[] = UPDATE_DIR.$sourcepic;

					$bs = explode(',', $bounds[$boundsIndex]);
					if ( $config['other']['cpic1width'] > 0 && $config['other']['cpic1height'] > 0 ) {
						$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $config['other']['cpic1width'], $config['other']['cpic1height'], false );
					}
				}
			}
			$boundsIndex++;
			if ( $_FILES['bigImageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['bigImageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['bigImageUrl'] = $filename;
					$new_files[] = UPDATE_DIR.$filename;
					//原图
					$sourcepic = $this->file->nameExtend( $filename, '_o' );
					@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
					$new_files[] = UPDATE_DIR.$sourcepic;

					$bs = explode(',', $bounds[$boundsIndex]);
					if ( $config['other']['cpic2width'] > 0 && $config['other']['cpic2height'] > 0 ) {
						$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $config['other']['cpic2width'], $config['other']['cpic2height'], false );
					}
				}
			}

			if ( $infoclass->create( $data ) ) {
				//自动创建栏目列表
				$column = $infoclass->getColumns( $this->user['id'], $parent_id );
				$infoclass->saveColumns( $column, $this->user['id'], $data['id'] );
				$this->sheader( $this->refreshUrl );
			}
			else {
				$this->file->deletefile( $new_files );
				$this->sheader( null, $this->lang->add_category_failed );
			}
		}
		else
		{
			$data = array();
			$data['extend'] = unserialize( $parent['extend'] );
			$data['info'] = unserialize( $parent['info'] );
			$data['other'] = unserialize( $parent['other'] );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'returnUrl' );
			$this->setData( $parent_id, 'parent_id' );
			$this->setData( $infoclass->getChildClassOrdinal( $parent_id ), 'ordinal' );
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑#
	{
		$id = get2( 'id' );
		$infoclass = $this->loadModel( 'infoClass' );
		$data = $infoclass->get( $id );

		if ( is_post() )
		{
			$oldData = $data;
			$data = post('data');
			if ( $data['ordinal'] < 0 ) $data['ordinal'] = $infoclass->getChildClassOrdinal( $infoclass->getParentId( $id ) );

			//配置
			$config = array();
			$config['extend'] = unserialize( $oldData['extend'] );
			$config['other'] = unserialize( $oldData['other'] );

			if ( empty( $data['name'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}

			if ( $config['extend']['hasAlias'] ) {
				$data['alias'] = strtolower( $data['alias'] );
				$data['alias'] = str_replace( ' ', '-', $data['alias'] );
				$data['alias'] = str_replace( '&', '-', $data['alias'] );
				//$data['alias'] = str_replace( '/', '', $data['alias'] );
				$data['alias'] = str_replace( '?', '-', $data['alias'] );
				$data['alias'] = str_replace( '|', '-', $data['alias'] );
				$data['alias'] = str_replace( '\\', '-', $data['alias'] );
				$data['alias'] = preg_replace( '/-{2,}/i', '-', $data['alias'] );

				if ( empty( $data['alias'] ) ) {
					$this->sheader( null, $this->lang->your_submit_incomplete );
				}

				if ( $infoclass->chkAlias( $id, $data['alias'] ) > 0 ) {
					$this->sheader( null, $this->lang->alias_has_been_occupied );
				}
			}

			//图片
			$new_files = array();
			$old_files = array();
			$filepath = date( 'Y-m' );
			$this->file->createdir( 'data/upload/'.$filepath );
			$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
			$bounds = post( 'bounds' );
			$boundsIndex = -1;
			$boundsIndex++;
			if ( $_FILES['imageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['imageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['imageUrl'] = $filename;
					$new_files[] = UPDATE_DIR.$filename;
					$old_files[] = $oldData['imageUrl'];
					//原图
					$sourcepic = $this->file->nameExtend( $filename, '_o' );
					@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
					$new_files[] = UPDATE_DIR.$sourcepic;
					$old_files[] = UPDATE_DIR.$this->file->nameExtend( $oldData['imageUrl'], '_o' );

					$bs = explode(',', $bounds[$boundsIndex]);
					if ( $config['other']['cpic1width'] > 0 && $config['other']['cpic1height'] > 0 ) {
						$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $config['other']['cpic1width'], $config['other']['cpic1height'], false );
					}
				}
			}
			$boundsIndex++;
			if ( $_FILES['bigImageUrl']['size'] > 0 ) {
				$filename = $this->file->upfile( $image_exts, $_FILES['bigImageUrl'], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
				if ( $filename ) {
					$data['bigImageUrl'] = $filename;
					$new_files[] = UPDATE_DIR.$filename;
					$old_files[] = $oldData['bigImageUrl'];
					//原图
					$sourcepic = $this->file->nameExtend( $filename, '_o' );
					@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
					$new_files[] = UPDATE_DIR.$sourcepic;
					$old_files[] = UPDATE_DIR.$this->file->nameExtend( $oldData['bigImageUrl'], '_o' );

					$bs = explode(',', $bounds[$boundsIndex]);
					if ( $config['other']['cpic2width'] > 0 && $config['other']['cpic2height'] > 0 ) {
						$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $config['other']['cpic2width'], $config['other']['cpic2height'], false );
					}
				}
			}

			if ( $infoclass->updateById( $data, $id ) ) {
				$this->file->deletefile( $old_files );
				$this->sheader( $this->refreshUrl );
			}
			else {
				$this->file->deletefile( $new_files );
				$this->sheader( null, $this->lang->edit_category_failed );
			}
		}
		else
		{
			$data['extend'] = unserialize( $data['extend'] );
			$data['info'] = unserialize( $data['info'] );
			$data['other'] = unserialize( $data['other'] );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'returnUrl' );
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_delete($v);
				}
			}
		}
		else
		{
			self::_delete(get2('id'));
		}
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'id' ));
	}

	private function _delete ( $id ) {
		$id = trim( $id );
		$infoclass = $this->loadModel( 'infoClass' );
		if ($infoclass->getInfoCount($id) > 0) $this->sheader(null, $this->lang->category_under_info_delete_all_first);
		if ( $infoclass->deleteById( $id ) ) {
			
		}
		else $this->sheader( null, $this->lang->delete_failed );
	}

}

?>