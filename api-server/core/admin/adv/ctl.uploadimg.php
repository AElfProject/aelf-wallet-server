<?php

/*
 @ctl_name = 邮件服务器@
*/

class ctl_uploadimg extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_info = $this->db( 'index', 'info', 'master' );
		$info = $mdl_info->getByWhere( array( 'classId' => 103 ) );
		if ( is_post() ) {
			$imgObj = $_FILES['img'];
			if ( !$imgObj ) return $this->error( __( '请选择图片文件' ) );
			//file_put_contents( 'file', json_encode( $avatarObj ) );

			$file = new file();

			if ( $imgObj['size'] > 0 ) {
				$filepath = date( 'Y-m' );
				$file->createdir( 'data/upload/' . $filepath );
				$allowExts = array( 'jpg', 'jpeg', 'gif', 'png' );
				$img = $file->upfile( $allowExts, $imgObj, UPDATE_DIR, $filepath . '/' . date( 'YmdHis' ) . $this->rnd(
					10 ) );
			}

			if ( !$img ) $this->formError[] = '请填选择图片文件';

			//$file->resize( UPDATE_DIR.$img, UPDATE_DIR.$img, 700, 160 );
			if ( !$this->formError ) {
				require_once 'core/v2.1/AliYun_OSS.php';

				if ( $info ) {
					if ( $mdl_info->update( array( 'title' => $img, 'content' => $img ), $info['id'] ) ) {

						$ossStatus = AliYun_OSS::uploadFile( 'aelf', $img, UPDATE_DIR . $img );
						$file->deletefile( UPDATE_DIR . $img );
						if ( $info['avatar'] ) {
							$file->deletefile( 'data/upload/' . $img );
							AliYun_OSS::delFile( 'aelf', $info['title'] );
						}

						//send接口调用
						$this->redis()->set('advimg', $img);

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );

						//return $this->returnSuccess( __( '保存成功' ), array( 'title' => $this->getConfig( 'oss_url' ) . $img ) );
					} else {
						$file->deletefile( 'data/upload/' . $img );
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '保存失败1';
					}
				} else {
					$data = array();
					$data['isApproved'] = 1;
					$data['title'] = $img;
					$data['content'] = $img;
					$data['lang'] = 'en';
					$data['classId'] = 103;
					if ( $mdl_info->insert( $data ) ) {
						$ossStatus = AliYun_OSS::uploadFile( 'aelf', $img, UPDATE_DIR . $img );
						$file->deletefile( UPDATE_DIR . $img );
						//send接口调用
						$this->redis()->set('advimg', $img);

						$this->formReturn['success'] = true;
						$this->formReturn['msg'] = '保存成功';
						$this->session( 'form-success-msg', '保存成功' );
						$this->sheader( $this->parseUrl()->set( 'act' )->set( 'id' )->toString() );
					} else {
						$file->deletefile( 'data/upload/' . $img );
						$this->formReturn['success'] = false;
						$this->formReturn['msg'] = '保存失败2';
					}
				}
			}
		}
		$this->formData = array_merge( $info, $this->formData );
		$this->setData( $this->formData, 'formData' );

		$this->setData( $this->formError, 'formError' );
		$this->setData( $this->formReturn, 'formReturn' );
		$this->setData( $this->getConfig( 'oss_url' ), 'oss_url' );

		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->display();
	}

}

?>