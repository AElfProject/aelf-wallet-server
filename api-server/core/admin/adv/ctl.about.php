<?php

/*
 @ctl_name = 关于我们分类管理@
*/

class ctl_about extends adminPage
{

	private $refreshUrl;

	function ctl_about ()
	{
		parent::adminPage();

		$this->refreshUrl = '?con=admin&ctl=adv/about&';
	}

	public function index_action () #act_name = 列表#
	{
		$mdl_infoClass = $this->loadModel( 'infoClass' );
		$infoclass = $mdl_infoClass->getChild( '101' );

		$this->setData( $infoclass );
		$this->display();
	}

	public function add_action () #act_name = 添加#
	{
		$infoclass = $this->loadModel('infoClass');

		$parent_id = '101';
		$parent = $infoclass->get( $parent_id );

		if ( is_post() )
		{
			$data = post( 'data' );
			$data['id'] = $infoclass->getChildClassIdToInsert( $parent_id );
			$data['ordinal'] = $infoclass->getChildClassOrdinal( $parent_id );
			$data['template'] = 'content';
			$data['extend'] = $parent['extend'];
			$data['info'] = $parent['info'];
			$data['other'] = $parent['other'];

			$data['alias'] = strtolower( $data['alias'] );
			$data['alias'] = str_replace( ' ', '-', $data['alias'] );
			$data['alias'] = str_replace( '&', '-', $data['alias'] );
			//$data['alias'] = str_replace( '/', '', $data['alias'] );
			$data['alias'] = str_replace( '?', '-', $data['alias'] );
			$data['alias'] = str_replace( '|', '-', $data['alias'] );
			$data['alias'] = str_replace( '\\', '-', $data['alias'] );
			$data['alias'] = preg_replace( '/-{2,}/i', '-', $data['alias'] );

			if ( empty( $data['name'] ) || empty( $data['alias'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}

			if ( $infoclass->chkAlias( 0, $data['alias'] ) > 0 ) {
				$this->sheader( null, $this->lang->alias_has_been_occupied );
			}

			if ( $infoclass->create( $data ) ) {
				//自动创建栏目列表
				$column = $infoclass->getColumns( $this->user['id'], $parent_id );
				$infoclass->saveColumns( $column, $this->user['id'], $data['id'] );
				$this->sheader( $this->refreshUrl );
			}
			else $this->sheader( null, $this->lang->add_category_failed );
		}
		else
		{
			$data = array( 'extend' => unserialize( $parent['extend'] ) );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'refreshUrl' );
			$this->setData( $parent_id, 'parent_id' );
			$this->setData( $infoclass->getChildClassOrdinal( $parent_id ), 'ordinal' );
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑#
	{
		$id = get2( 'id' );
		$infoclass = $this->loadModel( 'infoClass' );

		if ( is_post() )
		{
			$data = post('data');
			if ( $data['ordinal'] < 0 ) $data['ordinal'] = $infoclass->getChildClassOrdinal( $infoclass->getParentId( $id ) );

			$data['alias'] = strtolower( $data['alias'] );
			$data['alias'] = str_replace( ' ', '-', $data['alias'] );
			$data['alias'] = str_replace( '&', '-', $data['alias'] );
			//$data['alias'] = str_replace( '/', '', $data['alias'] );
			$data['alias'] = str_replace( '?', '-', $data['alias'] );
			$data['alias'] = str_replace( '|', '-', $data['alias'] );
			$data['alias'] = str_replace( '\\', '-', $data['alias'] );
			$data['alias'] = preg_replace( '/-{2,}/i', '-', $data['alias'] );

			if ( empty( $data['name'] ) || empty( $data['alias'] ) ) {
				$this->sheader( null, $this->lang->your_submit_incomplete );
			}
			if ( $infoclass->chkAlias( $id, $data['alias'] ) > 0 ) {
				$this->sheader( null, $this->lang->alias_has_been_occupied );
			}

			if ( $infoclass->updateById( $data, $id ) ) $this->sheader( $this->refreshUrl );
			else $this->sheader( null, $this->lang->edit_category_failed );
		}
		else
		{
			$data = $infoclass->get( $id );
			$data['extend'] = unserialize( $data['extend'] );
			$this->setData( $data );
			$this->setData( $this->refreshUrl, 'refreshUrl' );
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除#
	{
		$id = (int)get2( 'id' );
		$infoclass = $this->loadModel( 'infoClass' );
		if ($infoclass->getInfoCount($id) > 0) $this->sheader(null, $this->lang->category_under_info_delete_all_first);
		if ( $infoclass->deleteById( $id ) ) {
			$this->sheader( $this->refreshUrl );
		}
		else $this->sheader( null, $this->lang->delete_failed );
	}

}

?>