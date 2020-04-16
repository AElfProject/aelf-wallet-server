<?php

/*
 @ctl_name = 版本更新管理@
*/

class ctl_upgrade extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_app_version = $this->db( 'index', 'version' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );

		//查询数量
		$where = array();
		if ( $search['s'] ) $where[] = "(`key` like '%".$search['s']."%')";

		$count = $mdl_app_version->getCount( $where );

		list( $sql, $params ) = $mdl_app_version->getListSql( null, $where, 'id desc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_app_version->getListBySql( $page['outSql'] );

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

		$mdl_version = $this->db( 'index', 'version', 'master' );
		$version = $mdl_version->get( $id );

		if ( is_post() ) {
			$data = array();
			$data['key'] = trim(post( 'key' ));
            $data['appUrl'] = trim(post( 'appUrl' ));
            $data['intro'] = trim( post( 'intro' ) );
            $data['intro_en'] = trim( post( 'intro_en' ) );
            $data['intro_ko'] = trim( post( 'intro_ko' ) );
			$data['status'] = (int)post( 'status' );
			$data['verNo'] = trim( post( 'verNo' ) );
			$data['is_force'] = trim( post( 'is_force' ) );
			$data['min_version'] = trim( post( 'min_version' ) );
			$data['upgrade_time'] = strtotime(trim( post( 'upgrade_time' ) ));

			$this->formData = $_POST;

			if ( empty( $data['key'] ) ) $this->formError[] = '请填写设备名称';
			if ( empty( $data['verNo'] ) ) $this->formError[] = '请填写版本号';
			if ( empty( $data['upgrade_time'] ) ) $this->formError[] = '请填写升级时间';

			if ( !$this->formError ) {

                if ( $version ) {
                    $data['update_time'] = time();
                    $mdl_version->begin();
                    $mdl_version->update( $data, $version['id'] );

                    if ( !$mdl_version->isError() ) {
                        $mdl_version->commit();

                        $this->formReturn['success'] = true;
                        $this->formReturn['msg'] = '保存成功';
                        $this->session( 'form-success-msg', '保存成功' );
                        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
                    }
                    else {

                        $mdl_version->rollback();
                        $this->formReturn['success'] = false;
                        $this->formReturn['msg'] = '编辑失败'.print_r($mdl_version->getErrors(), true);
                    }
                }
                else {
                    $mdl_version->begin();
                    $data['create_time'] = time();
                    $aid = $mdl_version->insert( $data );

                    if ( !$mdl_version->isError() ) {
                        $mdl_version->commit();

                        $this->formReturn['success'] = true;
                        $this->formReturn['msg'] = '创建成功';
                        $this->session( 'form-success-msg', '创建成功' );
                        $this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
                    }
                    else {
                        $mdl_version->rollback();
                        $this->formReturn['success'] = false;
                        $this->formReturn['msg'] = '创建失败';
                    }
                }


			}
		}

        $this->formData = array_merge( $version, $this->formData );
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

		$mdl_version = $this->db( 'index', 'version', 'master' );
		$version = $mdl_version->get( $id );

		if ( $version ) {
            $mdl_version->begin();
            $mdl_version->delete( $id );
			if ( !$mdl_version->isError() ) {
                $mdl_version->commit();
				return true;
			}
			else {
                $mdl_version->rollback();
				return false;
			}
		}

		return true;
	}

}

?>