<?php

/*
 @ctl_name = 生成多语言@
*/

class ctl_gml extends adminPage
{

	public function index_action () #act_name = 表单#
	{
		if ( is_post() ) {
			$source_lang = post( 'sourceLang' );
			$target_lang = post( 'targetLang' );

			if ( empty( $source_lang ) || empty( $target_lang ) ) {
				$this->sheader( null, $this->lang->source_and_target_lang_can_not_empty );
			}

			set_time_limit(0);

			//分类
			$mdl_infoclass = $this->loadModel( 'infoClass' );
			foreach ( $mdl_infoclass->getListBySql( "select * from #@_infoclass where lang='$source_lang'" ) as $key => $category ) {
				$new_files = array();
				$data = array();

				foreach ( $category as $k => $cat ) {
					if ( !is_numeric( $k ) ) {
						$data[$k] = $cat;
					}
				}

				/*
				$data['id'] = $category['id'];
				$data['companyId'] = $category['companyId'];
				$data['ordinal'] = $category['ordinal'];
				$data['name'] = $category['name'];
				$data['alias'] = $category['alias'];
				$data['domain'] = $category['domain'];
				$data['classStyle'] = $category['classStyle'];
				$data['url'] = $category['url'];
				$data['pageTitle'] = $category['pageTitle'];
				$data['keywords'] = $category['keywords'];
				$data['description'] = $category['description'];
				$data['intro'] = $category['intro'];
				$data['content'] = $category['content'];
				$data['imageUrl'] = '';
				$data['bigImageUrl'] = '';
				$data['files'] = '';
				$data['maxLayer'] = $category['maxLayer'];
				$data['perPageCount'] = $category['perPageCount'];
				$data['defaultDisplayMode'] = $category['defaultDisplayMode'];
				$data['displayModes'] = $category['displayModes'];
				$data['extend'] = $category['extend'];
				$data['info'] = $category['info'];
				$data['other'] = $category['other'];
				$data['template'] = $category['template'];
				*/

				$data['lang'] = $target_lang;
				//缩略图
				$UPDATE_DIR = UPDATE_DIR;
				$UPDATE_DIR = str_replace('//', '/', UPDATE_DIR);
				if ( $category['imageUrl'] != '' ) {
					$image_ext = end( explode( '.', $category['imageUrl'] ) );
					$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $category['imageUrl'] );
					if ( copy( $UPDATE_DIR.$category['imageUrl'], $UPDATE_DIR.$new_image ) ) {
						$data['imageUrl'] = $new_image;
						$new_files[] = $UPDATE_DIR.$new_image;
					}
				}
				//大图
				if ( $category['bigImageUrl'] != '' ) {
					$image_ext = end( explode( '.', $category['bigImageUrl'] ) );
					$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $category['bigImageUrl'] );
					if ( copy( $UPDATE_DIR.$category['bigImageUrl'], $UPDATE_DIR.$new_image ) ) {
						$data['bigImageUrl'] = $new_image;
						$new_files[] = $UPDATE_DIR.$new_image;
					}
				}
				if ( $mdl_infoclass->insert( $data ) ) {
					//可以继续做信息和信息多图
					
				}
				else {
					if ( $new_files ) {
						$this->file->deletefile( $new_files );
					}
				}
			}

			//文章
			$mdl_info = $this->loadModel( 'info' );
			foreach ( $mdl_info->getListBySql( "select * from #@_info where lang='$source_lang'" ) as $key => $article ) {
				$new_files = array();
				$data = array();

				foreach ( $article as $k => $art ) {
					if ( !is_numeric( $k ) && strtolower($k) != 'id' ) {
						//判断非数字并且不是ID
						$data[$k] = $art;
					}
				}

				/*
				$data['classId'] = $article['classId'];
				$data['companyId'] = $article['companyId'];
				$data['ordinal'] = $article['ordinal'];
				$data['title'] = $article['title'];
				$data['titleStyle'] = $article['titleStyle'];
				$data['alias'] = $article['alias'];
				$data['url'] = $article['url'];
				$data['pageTitle'] = $article['pageTitle'];
				$data['keywords'] = $article['keywords'];
				$data['description'] = $article['description'];
				$data['publishedDate'] = $article['publishedDate'];
				$data['source'] = $article['source'];
				$data['author'] = $article['author'];
				$data['intro'] = $article['intro'];
				$data['content'] = $article['content'];
				$data['imageUrl'] = '';
				$data['bigImageUrl'] = '';
				$data['images'] = '';
				$data['files'] = '';
				$data['isApproved'] = $article['isApproved'];
				$data['isTop'] = $article['isTop'];
				$data['isHot'] = $article['isHot'];
				$data['isRecommended'] = $article['isRecommended'];
				$data['hits'] = $article['hits'];
				$data['downloadCount'] = $article['downloadCount'];
				$data['extend'] = $article['extend'];
				$data['sourceHtml'] = $article['sourceHtml'];
				*/

				$data['createdUserId'] = !$this->user ? 1 : session('admin_user_id');
				$data['createdDate'] = time();
				$data['lastModifiedUserId'] = !$this->user ? 1 : session('admin_user_id');
				$data['lastModifiedDate'] = time();
				$data['lang'] = $target_lang;

				//新增的字段
				

				//缩略图
				$UPDATE_DIR = UPDATE_DIR;
				$UPDATE_DIR = str_replace('//', '/', UPDATE_DIR);
				if ( $article['imageUrl'] != '' ) {
					$image_ext = end( explode( '.', $article['imageUrl'] ) );
					$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $article['imageUrl'] );
					if ( copy( $UPDATE_DIR.$article['imageUrl'], $UPDATE_DIR.$new_image ) ) {
						$data['imageUrl'] = $new_image;
						$new_files[] = $UPDATE_DIR.$new_image;
					}
				}
				//大图
				if ( $article['bigImageUrl'] != '' ) {
					$image_ext = end( explode( '.', $article['bigImageUrl'] ) );
					$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $article['bigImageUrl'] );
					if ( copy( $UPDATE_DIR.$article['bigImageUrl'], $UPDATE_DIR.$new_image ) ) {
						$data['bigImageUrl'] = $new_image;
						$new_files[] = $UPDATE_DIR.$new_image;
					}
				}

				if ( $newinfoid = $mdl_info->insert( $data ) ) {
					$mdl_infopic = $this->loadModel( 'infopic' );
					foreach ( $mdl_infopic->getListBySql( "select * from #@_infopic where infoId=".$article['id'] ) as $k => $pic ) {
						$new_files2 = array();
						$data = array();
						$data['infoId'] = $newinfoid;
						$data['pic'] = $pic['pic'];
						$data['picname'] = $pic['picname'];
						$data['smallpic'] = $pic['smallpic'];
						//缩略图
						$UPDATE_DIR = UPDATE_DIR;
						$UPDATE_DIR = str_replace('//', '/', UPDATE_DIR);
						if ( $pic['pic'] != '' ) {
							$image_ext = end( explode( '.', $pic['pic'] ) );
							$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $pic['pic'] );
							if ( copy( $UPDATE_DIR.$pic['pic'], $UPDATE_DIR.$new_image ) ) {
								$data['pic'] = $new_image;
								$new_files2[] = $UPDATE_DIR.$new_image;
							}
						}
						//大图
						if ( $pic['smallpic'] != '' ) {
							$image_ext = end( explode( '.', $pic['smallpic'] ) );
							$new_image = str_replace( '.'.$image_ext, '_'.$target_lang.'.'.$image_ext, $pic['smallpic'] );
							if ( copy( $UPDATE_DIR.$pic['smallpic'], $UPDATE_DIR.$new_image ) ) {
								$data['smallpic'] = $new_image;
								$new_files2[] = $UPDATE_DIR.$new_image;
							}
						}
						if ( $mdl_infopic->insert( $data ) ) {
							
						}
						else {
							if ( $new_files2 ) {
								$this->file->deletefile( $new_files2 );
							}
						}
					}
				}
				else {
					if ( $new_files ) {
						$this->file->deletefile( $new_files );
					}
				}
			}

			//后台菜单
			/*$mdl_relation = $this->loadModel( 'relation' );
			foreach ( $mdl_relation->getListBySql( "select * from #@_relation where lang='$source_lang'" ) as $key => $relation ) {
				$data = array();
				$data['id'] = $relation['id'];
				$data['ordinal'] = $relation['ordinal'];
				$data['name'] = $relation['name'];
				$data['url'] = $relation['url'];
				$data['lang'] = $target_lang;
				$mdl_relation->insert( $data );
			}*/
		}
		else {
			$this->display();
		}
	}

}

?>