<?php

/*
 @ctl_name = 交易所设置@
*/

class ctl_exchange extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_exchange = $this->db( 'index', 'exchange' );

		$search = array();
		$search['s'] = trim( get2( 's' ) );

		//查询数量
		$where = array();
		if ( $search['s'] ) $where[] = "(`name` like '%".$search['s']."%')";

		$count = $mdl_exchange->getCount( $where );

		list( $sql, $params ) = $mdl_exchange->getListSql( null, $where, 'sortnum desc, id asc' );

		$pageSql = $sql;
		$pageSize = 10;
		$pageUrl = $this->parseUrl()->set( 'page' );
		$page = $this->page( $pageSql, $pageUrl, $pageSize, 10, '', $count );
		$list = $mdl_exchange->getListBySql( $page['outSql'] );

        foreach ($list as $k => $message){
            $this->is_not_json($message['name']) || $message['name'] = $this->formatShow($message['name']);
            $this->is_not_json($message['fullName']) || $message['fullName'] = $this->formatShow($message['fullName']);
            $list[$k] = $message;
        }

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

		$mdl_exchange = $this->db( 'index', 'exchange', 'master' );

		$exchange = $mdl_exchange->get( $id );

		if ( is_post() ) {
			$data = array();
			$data['sortnum'] = (int)post( 'sortnum' );
			$data['status'] = (int)post( 'status' );
			$data['exchangeId'] = (int)post( 'exchangeId' );
			$data['name'] = trim( post( 'name' ) );
			$data['fullName'] = trim( post( 'fullName' ) );

            $data['name'] = $data['name'] ? stripslashes($data['name']): '{}';
            $data['fullName'] = $data['fullName'] ? stripslashes($data['fullName']): '{}';

			$data['website'] = trim( post( 'website' ) );
			$data['desc'] = trim( post( 'desc' ) );
            $data['desc_en'] = trim( post( 'desc_en' ) );
            $data['desc_ko'] = trim( post( 'desc_ko' ) );
			$data['coins'] = trim( post( 'coins' ) );
            $data['inindex'] = intval( post( 'inindex' ) );

			$this->formData = $_POST;

			$logoDel = (int)post( 'logoDel' );
			if ( $logoDel ) $data['logo'] = '';
			if ( empty( $data['name'] ) ) $this->formError[] = '请填写交易所简称，并确保唯一';

			if ( !$this->formError ) {
				require_once 'core/v2.1/AliYun_OSS.php';

				$logoObj = $_FILES['logo'];
				if ( $logoObj['size'] > 0 ) {
					$filepath = date( 'Y-m' );
					$this->file->createdir( 'data/upload/'.$filepath );
					$allowExts = array( 'jpg', 'jpeg', 'gif', 'png' );
					$logo = $this->file->upfile( $allowExts, $logoObj, UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
					if ( $logo ) {
						$data['logo'] = $logo;
					}
				}

				if ( $exchange ) {
					$mdl_exchange->begin();
					$mdl_exchange->update( $data, $exchange['id'] );

					if ( !$mdl_exchange->isError() ) {
						$mdl_exchange->commit();
						//$this->redis()->delete( 'exchanges' );
						$ossStatus = AliYun_OSS::uploadFile( 'aelf', $data['logo'], UPDATE_DIR.$data['logo'] );
						$this->file->deletefile( UPDATE_DIR.$data['logo'] );
						if ( $logoDel || ( $data['logo'] && $exchange['logo'] ) ) {
							$this->file->deletefile( UPDATE_DIR.$exchange['logo'] );
							AliYun_OSS::delFile( 'aelf', $exchange['logo'] );
						}
						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_exchange->rollback();
						if ( $data['logo'] ) $this->file->deletefile( UPDATE_DIR.$data['logo'] );
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '编辑失败';
					}
				}
				else {
					$mdl_exchange->begin();
					$aid = $mdl_exchange->insert( $data );

					if ( !$mdl_exchange->isError() ) {
						$mdl_exchange->commit();
						//$this->redis()->delete( 'exchanges' );
						AliYun_OSS::uploadFile( 'aelf', $data['logo'], UPDATE_DIR.$data['logo'] );
						$this->file->deletefile( UPDATE_DIR.$data['logo'] );

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '创建成功';
						$this->session( 'form-success-msg', '创建成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					}
					else {
						$mdl_exchange->rollback();
						if ( $data['logo'] ) $this->file->deletefile( UPDATE_DIR.$data['logo'] );

						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '创建失败';
						//print_r($mdl_exchange->getErrors());exit;
					}
				}
			}
		}


        $this->is_not_json($exchange['name']) && $exchange['name'] = json_encode(['zh-cn'=>$exchange['name']],JSON_UNESCAPED_UNICODE);
        $this->is_not_json($exchange['fullName']) && $exchange['fullName'] =  json_encode(['zh-cn'=>$exchange['fullName']],JSON_UNESCAPED_UNICODE);
        $this->formData = array_merge( $exchange, $this->formData );
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

		$mdl_exchange = $this->db( 'index', 'exchange', 'master' );

		$exchange = $mdl_exchange->get( $id );

		if ( $exchange ) {
			require_once 'core/v2.1/AliYun_OSS.php';
			$mdl_exchange->begin();
			$mdl_exchange->delete( $id );
			if ( !$mdl_exchange->isError() ) {
				$this->file->deletefile( UPDATE_DIR.$exchange['logo'] );
				AliYun_OSS::delFile( 'aelf', $exchange['logo'] );
				$mdl_exchange->commit();
				//$this->redis()->delete( 'exchange' );
				return true;
			}
			else {
				$mdl_exchange->rollback();
				//print_r($mdl_exchange->getErrors());exit;
				return false;
			}
		}

		return true;
	}


    function is_not_json($str){
        return is_null(json_decode($str));
    }

    public function formatShow($data){
        $formatData = '';
        $data = json_decode($data,true);
        if ($data){
            $formatData = $data['zh-cn'];
        }

        return $formatData;
    }

}

?>