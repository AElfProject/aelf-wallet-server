<?php

/*
 @ctl_name = 信息管理@
*/

class ctl_index extends adminPage
{

	private $class_id;
	private $where;  //条件
	private $pageUrl;  //分页地址
	private $returnUrl;
	private $pageSize;
	private $search;  //搜索
	private $order;  //排序
	private $orderStr;
	private $noback;  //不返回 用于单独将指定分类放到其它栏目下
	private $noback2;  //不返回 编辑后不返回列表

	function ctl_index ()
	{
		parent::adminPage();

		$this->noback = (int)get2( 'noback' );
		$this->noback2 = (int)get2( 'noback2' );
		//搜索处理
		$this->class_id		= get2('class_id');
		$order	= array(
			'order'		=> trim(get2('order')),
			'ordertype'	=> trim(get2('ordertype'))
		);
		if ($order['order'] == '') $order['order'] = 'ordinal';
		if ($order['ordertype'] != 'desc' && $order['ordertype'] != 'asc') $order['ordertype'] = 'desc';
		$search	= array(
			'withSubItems'	=> limitInt(get2('withSubItems'), 0, 1),
			'publishedDate'	=> get2('publishedDate'),
			'isApproved'	=> get2('isApproved'),
			'type'			=> get2('type'),
			'keyword'		=> get2('keyword'),
			'creator'		=> get2('creator'),
            'pub_from'		=> get2('pub_from'),
            'pub_to'		=> get2('pub_to')
		);

		$where	= array();
		if ($this->class_id != '')
		{
			if ($search['withSubItems'] > 0) $where[] = "t0.classId like '{$this->class_id}%'";
			else $where[] = "t0.classId='".$this->class_id."'";
		}
		switch ($search['publishedDate'])
		{
			case 'today'	: $where[] = "t0.publishedDate='".date('m/d/Y', time())."'"; break;
			case 'week'		:
				$tmpArr		= getIntervalDays('week');
				$where[]	= "t0.publishedDate>='".date('m/d/Y', $tmpArr[0])."' and t0.publishedDate<='".date('m/d/Y', $tmpArr[1])."'";
				break;
			case 'month'	:
				$tmpArr		= getIntervalDays('month');
				$where[]	= "t0.publishedDate>='".date('m/d/Y', $tmpArr[0])."' and t0.publishedDate<='".date('m/d/Y', $tmpArr[1])."'";
				break;
			case 'year'		: $where[] = "t0.publishedDate like '%".date('Y', time())."%'"; break;
		}
		if($search['pub_from']) $where[] = "t0.publishedDate>='".$search['pub_from']."'"; 
		if($search['pub_to']) $where[] = "t0.publishedDate<='".$search['pub_to']."'"; 
        if($search['creator']) $where[] = "t0.createdUserId=".$search['creator'];
		if ($search['isApproved'] == '1' || $search['isApproved'] == '0') $where[] = 't0.isApproved='.$search['isApproved'];
		if ($search['keyword'] != '')
		{
			switch ($search['type'])
			{
				case 'title'		: $where[] = "t0.title like '%{$search['keyword']}%'"; break;
				case 'createUser'	: $where[] = "(t2.name like '%{$search['keyword']}%' or t2.displayName like '%{$search['keyword']}%')"; break;
				case 'keywords'		: $where[] = "t0.keywords like '%{$search['keyword']}%'"; break;
				case 'description'	: $where[] = "t0.description like '%{$search['keyword']}%'"; break;
				case 'content'		: $where[] = "t0.content like '%{$search['keyword']}%'"; break;
			}
		}

		$this->where	= $where;
		$this->order	= $order;
		if ($this->order['order'] != '')
		{
			$this->orderStr = 't0.'.$this->order['order'].' '.$this->order['ordertype'];
		}
		$this->pageUrl		= '?con=admin&ctl=info/&class_id='.$this->class_id.'&withSubItems='.$search['withSubItems'].'&publishedDate='.$search['publishedDate'].'&isApproved='.$search['isApproved'].'&type='.$search['type'].'&keyword='.$search['keyword'].'&order='.$order['order'].'&ordertype='.$order['ordertype'].'&creator='.$search['creator'].'&pub_from='.$search['pub_from'].'&pub_to='.$search['pub_to'].'&noback='.$this->noback.'&noback2='.$this->noback2.'&';
		$this->returnUrl	= $this->pageUrl.'&perPageCount='.get2('perPageCount').'&'.'page='.get2('page').'&';
		$this->search		= $search;
	}

	public function index_action () #act_name = 信息列表#
	{
		$column		= self::_column();
		$info		= $this->loadModel('info');

		$pageSql	= $info->getListSql($column['columnField'], $this->where, $this->orderStr);
		$pageUrl	= $this->pageUrl;
		$pageSize	= 20;
		$maxPage	= 10;

		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		//echo $page['outSql'];exit;
		$data = $info->getListBySql($page['outSql']);

		if (trim($this->class_id) == '')
		{
			foreach ($data as $key=>$value)
			{
				$data[$key]['hideColumn'] = self::_columnChkForInfo($value['classId']);
			}
		}

		$hideColumn	= self::_columnChk($this->class_id);

		$infoclasslist = $this->getAllPermissionClass();
		$this->setData($data, 'data');
		$this->setData($column, 'column');
		$this->setData($hideColumn, 'hideColumn');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->class_id, 'class_id');
		$this->setData($this->search, 'search');
		$this->setData($infoclasslist, 'infoclasslist');
		$this->setData($this->returnUrl, 'refreshUrl');
		$this->setData($this->order, 'order');
		$this->setData($this->noback, 'noback');
		$this->setData($this->noback2, 'noback2');
		$this->display();
	}

	public function add_action () #act_name = 添加信息#
	{
		$this->class_id	= get2('class_id');
		$infoclass		= $this->loadModel('infoClass');
		if ($this->user_id == '-1')
		{
			$infoclasslist	= $infoclass->getChild();
		}
		else
		{
			$infoclasslist	= $this->getAllPermissionClass(session('admin_user_id'), 'index/add');
		}
		$class			= $infoclass->get($this->class_id);
		$this->class_id	= $class['id'];
		$class['info']	= unserialize($class['info']);
		$info			= $this->loadModel('info');
		$class['other']	= unserialize($class['other']);

		if (is_post())
		{
			if ($data = self::_filter(post('data')))
			{
				if ($data['ordinal'] < 1) $data['ordinal'] = $info->getInfoOrdinal($this->class_id);
				$data['createdUserId']	= !$this->user ? 1 : session('admin_user_id');
				$data['createdDate']	= time();
				$data['lastModifiedUserId']	= !$this->user ? 1 : session('admin_user_id');
				$data['lastModifiedDate']	= time();
				if (trim($data['publishedDate']) == '') $data['publishedDate'] = date('m/d/Y', time());
				$tags = $data['tags'];

				//处理内容中出现的图片，保存至images字段
				$con_img		= self::_getImg($data['content']);
				$data['images']	= implode(',', $con_img);

				$upload_error = 0;
				$new_files = array();
				$upload_files = array( 'imageUrl', 'bigImageUrl', 'files' );
				$upload_size = 0;
				foreach ( $upload_files as $uf ) {
					$upload_size += (int)$_FILES[$uf]['size'];
				}
				$bounds = post( 'bounds' );
				$boundsIndex = -1;
				if ( $upload_size > 0 ) {
					$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
					$file_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'zip' );
					if ( !empty($class['other']['exts']) ) {
						$file_exts = explode( '|', $class['other']['exts'] );
					}
					$filepath = date( 'Y-m' );
					$this->file->createdir( 'data/upload/'.$filepath );
				}
				foreach ( $upload_files as $key => $upload_file ) {
					if ( $upload_file != 'files' && $_FILES[$upload_file] ) $boundsIndex++;
					if ( $_FILES[$upload_file]['size'] > 0 ) {
						$filename = $this->file->upfile( $upload_file == 'files' ? $file_exts : $image_exts, $_FILES[$upload_file], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
						if ( $filename ) {
							$data[$upload_file] = $filename;
							if ( $upload_file == 'files' ) $data['filename'] = $_FILES[$upload_file]['name'];

							//图片尺寸控制
							if ( $upload_file == 'imageUrl' ) {
								//原图
								$sourcepic = $this->file->nameExtend( $filename, '_o' );
								@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
								$new_files[] = UPDATE_DIR.$sourcepic;

								$bs = explode(',', $bounds[$boundsIndex]);
								if ( $class['other']['pic1width'] > 0 && $class['other']['pic1height'] > 0 ) {
									$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $class['other']['pic1width'], $class['other']['pic1height'], false );
									/*
									$this->file->resize( , UPDATE_DIR.$filename, $bs[0], $bs[1], true, true );
									$this->file->cutByPosBound( UPDATE_DIR.$filename, UPDATE_DIR.$filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$class['other']['pic1width'], (int)$class['other']['pic1height'], true, true );
									*/
								}
							}
							else if ( $upload_file == 'bigImageUrl' ) {
								//原图
								$sourcepic = $this->file->nameExtend( $filename, '_o' );
								@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
								$new_files[] = UPDATE_DIR.$sourcepic;

								if ( $data['imageUrl'] == '' ) {
									$bs = explode(',', $bounds[$boundsIndex - 1]);
									$imageUrlName = $this->file->nameExtend( $filename, '_s' );
									@copy( UPDATE_DIR.$filename, UPDATE_DIR.$imageUrlName );
									if ( $class['other']['pic1width'] > 0 && $class['other']['pic1height'] > 0 ) {
										$this->file->cutByPosBoundPost( UPDATE_DIR.$imageUrlName, $bs, $class['other']['pic1width'], $class['other']['pic1height'], false );
										/*
										$this->file->resize( UPDATE_DIR.$imageUrlName, UPDATE_DIR.$imageUrlName, $bs[0], $bs[1], true, true );
										$this->file->cutByPosBound( UPDATE_DIR.$imageUrlName, UPDATE_DIR.$imageUrlName, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
										$this->file->resize( UPDATE_DIR.$imageUrlName, UPDATE_DIR.$imageUrlName, (int)$class['other']['pic1width'], (int)$class['other']['pic1height'], true, true );
										*/
									}
									$data['imageUrl'] = $imageUrlName;
								}
								$bs = explode(',', $bounds[$boundsIndex]);
								if ( $class['other']['pic2width'] > 0 && $class['other']['pic2height'] > 0 ) {
									$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $class['other']['pic2width'], $class['other']['pic2height'], false );
									/*
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, $bs[0], $bs[1], true, true );
									$this->file->cutByPosBound( UPDATE_DIR.$filename, UPDATE_DIR.$filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$class['other']['pic2width'], (int)$class['other']['pic2height'], true, true );
									*/
								}
							}

							//删除原图
							$new_files[] = UPDATE_DIR.$filename;
						}
						else {
							$upload_error++;
						}
					}
				}

				if ($info_id = $info->add($this->array_splice($data, 'tags', 1)))
				{
					//处理Tags
					$tagBll = $this->loadModel('tags');
					$tagBll->deleteByInfoId($info_id);
					foreach (explode(',', $tags) as $key=>$value)
					{
						$tag = array();
						$tag['info_id']	= $info_id;
						$tag['title']	= trim($value);
						if ($tag['title'] != '') $tagBll->add($tag);
					}
					unset($tags);
					unset($tagBll);

					//处理多图
					$mdl_infopic = $this->loadModel( 'infopic' );
					$pics = $_FILES['pics'];
					$picsname = $_POST['picsname'];
					$pics_sortnum = $_POST['pics_sortnum'];
					if ( $pics ) {
						//附件
						$exts = array( 'jpg', 'jpeg', 'gif', 'png' );
						$filepath = date( 'Y-m' );
						$this->file->createdir( 'data/upload/'.$filepath );
						foreach ( $pics['name'] as $key => $name ) {
							$boundsIndex++;
							if ( $pics['size'][$key] > 0 ) {
								$ext = strtolower( end( explode( '.', $name ) ) );
								$filename = '';
								if ( in_array( $ext, $exts ) ) {
									$rnd = $this->createRnd();
									$pic = $filepath.'/'.date( 'YmdHis' ).$rnd.'.'.$ext;
									$filename = str_replace( '//', '/', UPDATE_DIR.'/'.$pic );
									$this->file->createdir( str_replace( end( explode( '/', $filename ) ), '', $filename ) );
									if ( function_exists( 'move_uploaded_file' ) ) {
										move_uploaded_file( $pics['tmp_name'][$key], $filename );
									}
									else {
										@copy( $pics['tmp_name'][$key], $filename );
									}

									//图片尺寸控制
									if ( $class['other']['infopic1width'] > 0 && $class['other']['infopic1height'] > 0 ) {
										//生成缩略图
										$smallpic = $filepath.'/'.date( 'YmdHis' ).$rnd.'s.'.$ext;
										$smallfilename = str_replace( '//', '/', UPDATE_DIR.'/'.$smallpic );
										@copy( $filename, $smallfilename );
										$this->file->resize( $smallfilename, $smallfilename, (int)$class['other']['infopic1width'], (int)$class['other']['infopic1height'], true, true );
										$this->file->cutByPos( $smallfilename, $smallfilename, (int)$class['other']['infopic1width'], (int)$class['other']['infopic1height'] );
									}

									//原图
									$sourcepic = $this->file->nameExtend( $pic, '_o' );
									$sourceFileName = str_replace( '//', '/', UPDATE_DIR.'/'.$sourcepic );
									@copy( $filename, $sourceFileName );

									$bs = explode(',', $bounds[$boundsIndex]);
									if ( $class['other']['infopic2width'] > 0 && $class['other']['infopic2height'] > 0 ) {
										$this->file->cutByPosBoundPost( $filename, $bs, $class['other']['infopic2width'], $class['other']['infopic2height'], false );
										/*
										$this->file->resize( $filename, $filename, $bs[0], $bs[1], true, true );
										$this->file->cutByPosBound( $filename, $filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
										$this->file->resize( $filename, $filename, (int)$class['other']['infopic2width'], (int)$class['other']['infopic2height'], true, true );
										*/
									}

									$pics_sortnum[$key] = (int)$pics_sortnum[$key];
									$attach_data = array(
										'sortnum' => $pics_sortnum[$key] > 0 ? $pics_sortnum[$key] : $mdl_infopic->getMax( 'sortnum', array( 'infoId' => $info_id ) ) + 10,
										'infoId' => $info_id,
										'picname' => $picsname[$key] ? $picsname[$key] : $name,
										'pic' => $pic,
										'smallpic' => empty( $smallpic ) ? $pic : $smallpic
									);
									if ( ! $mdl_infopic->insert( $attach_data ) ) {
										$upload_error++;
									}
									/*
									if ( $key == 0 ) {
										//第一张图作为缩略图
										$new_pic_name = str_replace( '//', '/', UPDATE_DIR.'/'.str_replace( $rnd, $rnd.'c', $smallpic ) );
										@copy( $smallfilename, $new_pic_name );
										$info->update( array( 'imageUrl' => str_replace( $rnd, $rnd.'c', $smallpic ) ), $info_id );
									}
									*/
								}
								else {
									$upload_error++;
								}
							}
						}
					}

					//self::_html($info_id);
					$this->sheader($this->returnUrl, $upload_error > 0 ? $this->lang->add_info_success_but_upload_pic_has_error : null);
				}
				else {
					if ( $new_files ) {
						$this->file->deletefile( $new_files );
					}
					$this->sheader(null, $this->lang->add_info_failed);
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$this->setData($infoclasslist, 'infoclasslist');
			$this->setData($this->class_id, 'class_id');
			$this->setData($this->returnUrl, 'returnUrl');
			$this->setData($class, 'class');
			$this->setData($info->getInfoOrdinal($this->class_id), 'ordinal');
			$this->setData($this->noback, 'noback');
			$this->setData($this->noback2, 'noback2');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑信息#
	{
		$this->class_id	= get2('class_id');
		//允许查看信息列表的分类
		$infoclass		= $this->loadModel('infoClass');
		if ($this->user_id == '-1')
		{
			$infoclasslist	= $infoclass->getChild();
		}
		else
		{
			$infoclasslist	= $this->getAllPermissionClass(session('admin_user_id'), 'index/add');
		}
		$class			= $infoclass->get($this->class_id);
		$positionStr	= $infoclass->getParentNameStr($this->class_id, ' -&gt; ');
		$this->class_id	= $class['id'];
		$class['info']	= unserialize($class['info']);
		$class['other']	= unserialize($class['other']);
		$info			= $this->loadModel('info');
		$id				= (int)get2('id');
		$data			= $info->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);

		if (is_post())
		{
			$infodata = $data;
			if ($data = self::_filterForEdit(post('data')))
			{
				if ($data['ordinal'] < 1) $data['ordinal'] = $info->getInfoOrdinal($this->class_id);
				$data['lastModifiedUserId']	= !$this->user ? 1 : session('admin_user_id');
				$data['lastModifiedDate']	= time();
				$tags = $data['tags'];

				//处理内容中出现的图片，保存至images字段
				$con_img		= self::_getImg($data['content']);
				$data['images']	= implode(',', $con_img);

				$upload_error = 0;
				$old_files = array();
				$new_files = array();
				$upload_files = array( 'imageUrl', 'bigImageUrl', 'files' );
				$upload_size = 0;
				foreach ( $upload_files as $uf ) {
					$upload_size += (int)$_FILES[$uf]['size'];
				}
				$bounds = post( 'bounds' );
				$boundsIndex = -1;
				if ( $upload_size > 0 ) {
					$image_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
					$file_exts = array( 'jpg', 'jpeg', 'png', 'gif', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'zip' );
					if ( !empty($class['other']['exts']) ) {
						$file_exts = explode( '|', $class['other']['exts'] );
					}
					$filepath = date( 'Y-m' );
					$this->file->createdir( 'data/upload/'.$filepath );
				}
				foreach ( $upload_files as $key => $upload_file ) {
					if ( $upload_file != 'files' && $_FILES[$upload_file] ) $boundsIndex++;
					if ( $_FILES[$upload_file]['size'] > 0 ) {
						$filename = $this->file->upfile( $upload_file == 'files' ? $file_exts : $image_exts, $_FILES[$upload_file], UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
						if ( $filename ) {
							$data[$upload_file] = $filename;
							if ( $upload_file == 'files' ) $data['filename'] = $_FILES[$upload_file]['name'];

							//图片尺寸控制
							if ( $upload_file == 'imageUrl' ) {
								$sourcepic = $this->file->nameExtend( $filename, '_o' );
								@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
								$new_files[] = UPDATE_DIR.$sourcepic;
								$old_files[] = UPDATE_DIR.$this->file->nameExtend( $infodata[$upload_file], '_o' );

								$bs = explode(',', $bounds[$boundsIndex]);
								if ( $class['other']['pic1width'] > 0 && $class['other']['pic1height'] > 0 ) {
									$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $class['other']['pic1width'], $class['other']['pic1height'], false );
									/*
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, $bs[0], $bs[1], true, true );
									$this->file->cutByPosBound( UPDATE_DIR.$filename, UPDATE_DIR.$filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$class['other']['pic1width'], (int)$class['other']['pic1height'], true, true );
									*/
								}
							}
							else if ( $upload_file == 'bigImageUrl' ) {
								$sourcepic = $this->file->nameExtend( $filename, '_o' );
								@copy( UPDATE_DIR.$filename, UPDATE_DIR.$sourcepic );
								$new_files[] = UPDATE_DIR.$sourcepic;
								$old_files[] = UPDATE_DIR.$this->file->nameExtend( $infodata[$upload_file], '_o' );

								$bs = explode(',', $bounds[$boundsIndex]);
								if ( $class['other']['pic2width'] > 0 && $class['other']['pic2height'] > 0 ) {
									$this->file->cutByPosBoundPost( UPDATE_DIR.$filename, $bs, $class['other']['pic2width'], $class['other']['pic2height'], false );
									/*
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, $bs[0], $bs[1], true, true );
									$this->file->cutByPosBound( UPDATE_DIR.$filename, UPDATE_DIR.$filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
									$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$class['other']['pic2width'], (int)$class['other']['pic2height'], true, true );
									*/
								}
							}

							$old_files[] = UPDATE_DIR.$infodata[$upload_file];
							$new_files[] = UPDATE_DIR.$filename;
						}
						else {
							$upload_error++;
						}
					}
				}

				if ($info->update($this->array_splice($data, 'tags', 1), $id))
				{
					//删除原图
					if ( $old_files ) {
						$this->file->deletefile( $old_files );
					}

					//处理Tags
					$tagBll = $this->loadModel('tags');
					$tagBll->deleteByInfoId($id);
					foreach (explode(',', $tags) as $key=>$value)
					{
						$tag = array();
						$tag['info_id']	= $id;
						$tag['title']	= trim($value);
						if ($tag['title'] != '') $tagBll->add($tag);
					}
					unset($tags);
					unset($tagBll);

					//处理多图
					$mdl_infopic = $this->loadModel( 'infopic' );
					$pics = $_FILES['pics'];
					$picsname = $_POST['picsname'];
					$pics_old_id = $_POST['pics_old_id'];
					$pics_sortnum = $_POST['pics_sortnum'];
					$pics_old_key = 0;
					foreach ( $pics_old_id as $key => $poi ) {
						$poi = (int)$poi;
						$pics_sortnum[$key] = (int)$pics_sortnum[$key];
						if ( $poi > 0 ) {
							$mdl_infopic->update( array( 'sortnum' => $pics_sortnum[$key] ), $poi );
							$pics_old_key++;
						}
					}
					if ( $pics ) {
						//附件
						$exts = array( 'jpg', 'jpeg', 'gif', 'png' );
						$filepath = date( 'Y-m' );
						$this->file->createdir( 'data/upload/'.$filepath );
						foreach ( $pics['name'] as $key => $name ) {
							$boundsIndex++;
							if ( $pics['size'][$key] > 0 ) {
								$ext = strtolower( end( explode( '.', $name ) ) );
								$filename = '';
								if ( in_array( $ext, $exts ) ) {
									$pic = $filepath.'/'.date( 'YmdHis' ).$this->createRnd().'.'.$ext;
									$filename = str_replace( '//', '/', UPDATE_DIR.'/'.$pic );
									$this->file->createdir( str_replace( end( explode( '/', $filename ) ), '', $filename ) );
									if ( function_exists( 'move_uploaded_file' ) ) {
										move_uploaded_file( $pics['tmp_name'][$key], $filename );
									}
									else {
										@copy( $pics['tmp_name'][$key], $filename );
									}

									//图片尺寸控制
									if ( $class['other']['infopic1width'] > 0 && $class['other']['infopic1height'] > 0 ) {
										//生成缩略图
										$smallpic = $filepath.'/'.date( 'YmdHis' ).$this->createRnd().'s.'.$ext;
										$smallfilename = str_replace( '//', '/', UPDATE_DIR.'/'.$smallpic );
										@copy( $filename, $smallfilename );
										$this->file->resize( $smallfilename, $smallfilename, (int)$class['other']['infopic1width'], (int)$class['other']['infopic1height'], true, true );
										$this->file->cutByPos( $smallfilename, $smallfilename, (int)$class['other']['infopic1width'], (int)$class['other']['infopic1height'] );
									}

									//原图
									$sourcepic = $this->file->nameExtend( $pic, '_o' );
									$sourceFileName = str_replace( '//', '/', UPDATE_DIR.'/'.$sourcepic );
									@copy( $filename, $sourceFileName );

									$bs = explode(',', $bounds[$boundsIndex]);
									if ( $class['other']['infopic2width'] > 0 && $class['other']['infopic2height'] > 0 ) {
										$this->file->cutByPosBoundPost( $filename, $bs, $class['other']['infopic2width'], $class['other']['infopic2height'], false );
										/*
										$this->file->resize( $filename, $filename, $bs[0], $bs[1], true, true );
										$this->file->cutByPosBound( $filename, $filename, array( 'x1' => $bs[2], 'y1' => $bs[3], 'x2' => $bs[4], 'y2' => $bs[5] ) );
										$this->file->resize( $filename, $filename, (int)$class['other']['infopic2width'], (int)$class['other']['infopic2height'], true, true );
										*/
									}

									$attach_data = array(
										'sortnum' => $pics_sortnum[$key+$pics_old_key] > 0 ? $pics_sortnum[$key+$pics_old_key] : $mdl_infopic->getMax( 'sortnum', array( 'infoId' => $id ) ) + 10,
										'infoId' => $id,
										'picname' => $picsname[$key] ? $picsname[$key] : $name,
										'pic' => $pic,
										'smallpic' => empty( $smallpic ) ? $pic : $smallpic
									);
									if ( ! $mdl_infopic->insert( $attach_data ) ) {
										$upload_error++;
									}
								}
								else {
									$upload_error++;
								}
							}
						}
					}

					//self::_html($id);
					$this->sheader( $this->noback2 ? $this->parseUrl() : $this->returnUrl, $upload_error > 0 ? $this->lang->edit_info_success_but_upload_pic_has_error : null);
				}
				else {
					if ( $new_files ) {
						$this->file->deletefile( $new_files );
					}
					$this->sheader(null, $this->lang->edit_info_failed);
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			if ($data['titleStyle'] != '')
			{
				if (preg_match('/font-weight:bold;/', $data['titleStyle'])) $data['chkTitleBold'] = true;
				if (preg_match('/font-style:italic;/', $data['titleStyle'])) $data['chkTitleItalic'] = true;
				if (preg_match('/color:(.*);/U', $data['titleStyle'], $arr)) $data['color'] = $arr[1];
			}

			//Tags
			$tagBll	= $this->loadModel('tags');
			$tags	= array();
			foreach ($tagBll->getListByInfoId($data['id']) as $key=>$value)
			{
				$tags[] = $value['title'];
			}
			$data['tags'] = implode(',', $tags);
			unset($tagBll);

			$this->setData($data);
			$this->setData($this->class_id, 'class_id');
			$this->setData($class, 'class');
			//* 不需要多图注释掉
			$mdl_infopic = $this->loadModel( 'infopic' );
			$this->setData($mdl_infopic->getList( $data['id'] ), 'pic_list');
			$this->setData($this->parseUrl()->set( 'id' )->set( 'act', 'deletepic' ), 'deletepicurl');
			//*/
			$this->setData($positionStr, 'positionStr');
			$this->setData($this->returnUrl, 'returnUrl');
			$this->setData($this->noback, 'noback');
			$this->setData($this->noback2, 'noback2');
			$this->display();
		}
	}

	public function deletepic_action () #act_name = 删除图片#
	{
		$id = (int)get2( 'id' );
		$info_id = (int)get2( 'infoid' );
		$mdl_infopic = $this->loadModel( 'infopic' );
		if ( $pic = $mdl_infopic->get( $id ) ) {
			$mdl_infopic->delete( $id );
			$this->file->deletefile( UPDATE_DIR.$pic['pic'] );
			$this->file->deletefile( UPDATE_DIR.$pic['smallpic'] );
			$this->file->deletefile( UPDATE_DIR.$this->file->nameExtend( $pic['pic'], '_o' ) );
		}
		$this->sheader( $this->parseUrl()->set( 'act', 'edit' )->set( 'id', $info_id )->set( 'infoid' ) );
	}


	public function delete_action () #act_name = 删除信息#
	{
		if (is_post())  //批量删除
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $key=>$value) self::_delete(trim($value));
			}
		}
		else
		{
			self::_delete(get2('id'));
		}
		$this->sheader($this->returnUrl);
	}

	public function preview_action () #act_name = 预览信息#
	{
		$id		= (int)get2('id');
		$info	= $this->loadModel('info');
		$data	= $info->get($id);
		if (!$data) $this->sheader(null, $this->lang->current_record_not_exists);
		if (empty($data['pageTitle'])) $data['pageTitle'] = $data['title'];
		$this->setData($data);
		$this->display();
	}

	public function search_action () #act_name = 信息搜索#
	{
		$user		= $this->loadModel('user');
		$user_list	= $user->getAllUserListWithoutById();
		
		$infoclasslist	= $this->getAllPermissionClass();

		$this->setData($infoclasslist, 'infoclasslist');
		$this->setData($user_list, 'user_list');
		$this->display();
	}

	public function move_action () #act_name = 信息转移#
	{
		$ids = post('ids');
		if (is_array($ids))
		{
			$bllInfo		= $this->loadModel('info');
			$targetClass	= $this->chkClass(get2('targetClass'));
			if ($targetClass == '') $this->sheader(null, $this->lang->not_specify_target_category);
			foreach ($ids as $key=>$value)
			{
				$data	= array();
				$id		= (int)$value;
				$info	= $bllInfo->get($id);
				if (!$info) $this->sheader(null, $this->lang->current_record_not_exists.' '.$id);
				if ($this->chkInfo($info['classId'], 'index', 'move'))
				{
					$data['classId'] = $targetClass;
					if (!$bllInfo->update($data, $info['id'])) $this->sheader(null, $this->lang->transfer_info_failed.' '.$id);
				}
				else $this->sheader(null, $this->lang->no_permission_to_transfer_info.' '.$id);
			}
		}
		$this->sheader($this->pageUrl.'perPageCount='.get2('perPageCount').'&page='.get2('page').'&');
	}

	public function copy_action () #act_name = 信息复制#
	{
		$ids = post('ids');
		if (is_array($ids))
		{
			$bllInfo		= $this->loadModel('info');
			$targetClass	= $this->chkClass(get2('targetClass'));
			if ($targetClass == '') $this->sheader(null, $this->lang->not_specify_target_category);
			foreach ($ids as $key=>$value)
			{
				$data	= array();
				$id		= (int)$value;
				$info	= $bllInfo->get($id);
				if (!$info) $this->sheader(null, $this->lang->current_record_not_exists.' '.$id);
				if ($this->chkInfo($info['classId'], 'index', 'copy'))
				{
					$data['classId']			= $targetClass;
					$data['companyId']			= $info['companyId'];
					$data['ordinal']			= $info['ordinal'];
					$data['title']				= $info['title'];
					$data['titleStyle']			= $info['titleStyle'];
					$data['alias']				= $info['alias'];
					$data['url']				= $info['url'];
					$data['pageTitle']			= $info['pageTitle'];
					$data['keywords']			= $info['keywords'];
					$data['description']		= $info['description'];
					$data['publishedDate']		= $info['publishedDate'];
					$data['source']				= $info['source'];
					$data['author']				= $info['author'];
					$data['intro']				= $info['intro'];
					$data['content']			= $info['content'];
					$data['imageUrl']			= $info['imageUrl'];
					$data['bigImageUrl']		= $info['bigImageUrl'];
					$data['images']				= $info['images'];
					$data['files']				= $info['files'];
					$data['isApproved']			= $info['isApproved'];
					$data['isTop']				= $info['isTop'];
					$data['isHot']				= $info['isHot'];
					$data['isRecommended']		= $info['isRecommended'];
					$data['hits']				= $info['hits'];
					$data['createdUserId']		= $this->user_id == '-1' ? 1 : $this->user_id;
					$data['createdDate']		= $info['createdDate'];
					$data['lastModifiedUserId']	= $info['lastModifiedUserId'];
					$data['lastModifiedDate']	= $info['lastModifiedDate'];
					$data['extend']				= $info['extend'];
					$data['sourceHtml']			= $info['sourceHtml'];

					if (!$bllInfo->add($data)) $this->sheader(null, $this->lang->copy_info_failed.' '.$id);
				}
				else $this->sheader(null, $this->lang->no_permission_to_copy_info.' '.$id);
			}
		}
		$this->sheader($this->pageUrl.'perPageCount='.get2('perPageCount').'&page='.get2('page').'&');
	}

	public function state_action () #act_name = 状态设置#
	{
		$state = (int)get2('state');
		if ($state > 0 && $state < 9)
		{
			if (is_post())  //批量调整状态
			{
				$ids = post('ids');
				if (is_array($ids))
				{
					foreach ($ids as $key=>$value) self::_state(trim($value));
				}
			}
			else
			{
				self::_state(get2('id'));
			}
		}
		$this->sheader($this->pageUrl.'perPageCount='.get2('perPageCount').'&page='.get2('page').'&');
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$bllInfo	= $this->loadModel('info');
		$info		= $bllInfo->get($id);
		//if ($info) $this->sheader(null, "Information does not exist  {$id}");
		if ( $info ) {
			if ($this->chkInfo($info['classId'], 'index', 'delete'))
			{
				if ($bllInfo->delete($id))
				{
					//删除tags
					$tag = $this->loadModel('tags');
					$tag->deleteByInfoId($info['id']);
					//删除图片、内容中的图片和附件
					$this->file->deletefile(UPDATE_DIR.$info['imageUrl']);
					$this->file->deletefile(UPDATE_DIR.$info['bigImageUrl']);
					$this->file->deletefile( UPDATE_DIR.$this->file->nameExtend( $info['imageUrl'], '_o' ) );
					$this->file->deletefile( UPDATE_DIR.$this->file->nameExtend( $info['bigImageUrl'], '_o' ) );
					if (!empty($info['images']))
					{
						foreach (explode(',', $info['images']) as $k=>$v)
						{
							$this->file->deletefile(UPDATE_DIR.$v);
						}
					}
					$this->file->deletefile(UPDATE_DIR.$info['files']);

					//多图
					$mdl_infopic = $this->loadModel( 'infopic' );
					foreach ( $mdl_infopic->getList( $id ) as $pic ) {
						$this->file->deletefile( UPDATE_DIR.$pic['pic'] );
						$this->file->deletefile( UPDATE_DIR.$pic['smallpic'] );
						$this->file->deletefile( UPDATE_DIR.$this->file->nameExtend( $pic['pic'], '_o' ) );
					}
					$mdl_infopic->deleteByInfoId( $id );
				}
				else $this->sheader(null, $this->lang->delete_info_failed.' '.$id);
			}
			else $this->sheader(null, $this->lang->no_permission_to_delete_info.' '.$id);
		}
	}

	private function _state ($id)
	{
		$state		= (int)get2('state');
		$id			= (int)$id;
		$bllInfo	= $this->loadModel('info');
		$info		= $bllInfo->get($id);
		if (!$info) $this->sheader(null, $this->lang->current_record_not_exists.' '.$id);
		if ($this->chkInfo($info['classId'], 'index', 'state'))
		{
			$data = array();
			switch ($state)
			{
				case 1 : $data['isApproved'] = 0; break;
				case 2 : $data['isApproved'] = 1; break;
				case 3 : $data['isTop'] = 0; break;
				case 4 : $data['isTop'] = 1; break;
				case 5 : $data['isRecommended'] = 0; break;
				case 6 : $data['isRecommended'] = 1; break;
				case 7 : $data['isHot'] = 0; break;
				case 8 : $data['isHot'] = 1; break;
			}
			if (!$bllInfo->update($data, $id)) $this->sheader(null, $this->lang->adjust_info_state_failed.' '.$id);
		}
		else $this->sheader(null, $this->lang->no_permission_to_adjust_info_state.' '.$id);
		//self::_html($id);
	}

	private function _column ()
	{
		$columnArray	= array();
		$infoclass		= $this->loadModel('infoClass');
		$column			= $infoclass->getColumns(session('admin_user_id'), $this->class_id);
		if (empty($column)) $column = $this->getColumns();

		foreach ($column as $key=>$value)
		{
			if ($value['show'])
			{
				$columnArray['columnField'][]	= $key;
				$columnArray['columnName'][]	= $value['name'];
			}
		}
		$columnArray['count'] = count($columnArray['columnField']);

		return $columnArray;
	}

	private function _filter ($data)
	{
		//filter可以对提交的数据进行检测，如果该分类不允许修改某字段，则splice，，但本系统没有处理此问题，请注意。
		$data['classId']		= $this->chkClass($data['class_id']);
		$data['ordinal']		= (int)$data['ordinal'];
		$data['isApproved']		= limitInt($data['isApproved'], 0, 1);
		$data['isTop']			= limitInt($data['isTop'], 0, 1);
		$data['isRecommended']	= limitInt($data['isRecommended'], 0, 1);
		$data['isHot']			= limitInt($data['isHot'], 0, 1);
		if ( isset( $data['hits'] ) ) $data['hits'] = (int)$data['hits'];
		$titleStyle				= '';
		$reg = $this->loadModel('reg');
		if (post('color') != '' && $reg->chkColor(post('color'))) $titleStyle .= "color:".post('color').';';
		if (post('chkTitleBold') == 1) $titleStyle .= ' font-weight:bold;';
		if (post('chkTitleItalic') == 1) $titleStyle .= ' font-style:italic;';
		$data['titleStyle']		= $titleStyle;

		return $this->array_splice($data, 'class_id', 1);
	}

	private function _filterForEdit ($data)
	{
		//filter可以对提交的数据进行检测，如果该分类不允许修改某字段，则splice，，但本系统没有处理此问题，请注意。
		$data['ordinal']		= (int)$data['ordinal'];
		$data['isApproved']		= limitInt($data['isApproved'], 0, 1);
		$data['isTop']			= limitInt($data['isTop'], 0, 1);
		$data['isRecommended']	= limitInt($data['isRecommended'], 0, 1);
		$data['isHot']			= limitInt($data['isHot'], 0, 1);
		if ( isset( $data['hits'] ) ) $data['hits'] = (int)$data['hits'];
		$titleStyle				= '';
		$reg = $this->loadModel('reg');
		if (post('color') != '' && $reg->chkColor(post('color'))) $titleStyle .= "color:".post('color').';';
		if (post('chkTitleBold') == 1) $titleStyle .= ' font-weight:bold;';
		if (post('chkTitleItalic') == 1) $titleStyle .= ' font-style:italic;';
		$data['titleStyle']		= $titleStyle;

		return $this->array_splice($data, 'classId', 1);  //去除对classId的修改
	}

	private function _getImg ($str)
	{
		preg_match_all("/<img\b[^<>]*?\bsrc[\s\t\r\n]*=[\s\t\r\n]*[\"']?[\s\t\r\n]*(?<imgUrl>[^\s\t\r\n\"'<>]*)[^<>]*?\/?[\s\t\r\n]*>/iu", str_replace('\\', '', $str), $array);
		foreach ($array['imgUrl'] as $key=>$value)  //去除目录信息
		{
			$list[$key] = str_replace(UPLOAD_PATH, '', $value);
		}
		return array_distinct($list);
	}

	private function _columnChk ($classID)
	{
		$bll	= $this->loadModel('infoClass');
		$class	= $bll->get($classID);
		$extend	= unserialize($class['info']);

		$hide = array(
			'info_add'				=> $this->class_id ? $this->chkInfo($classID, 'index', 'add') : 1,
			'class_columnSetting'	=> $this->chkInfo($classID, 'class', 'columnSetting'),
			'pic'					=> $extend['hasImageUrl'] ? $this->chkAction('upload/pic') : $extend['hasImageUrl'],
			'pic2'					=> $extend['hasBigImageUrl'] ? $this->chkAction('upload/pic') : $extend['hasBigImageUrl'],
			'file'					=> $extend['hasFiles'] ? $this->chkAction('upload/file') : $extend['hasFiles'],
			'info_edit'				=> $this->chkInfo($classID, 'index', 'edit'),
			'info_delete'			=> $this->chkInfo($classID, 'index', 'delete'),
			'info_preview'			=> $this->chkInfo($classID, 'index', 'preview')
		);
		return $hide;
	}

	private function _columnChkForInfo ($classID)  //每条信息都需要检测的权限，如通过搜索进入
	{
		$bll	= $this->loadModel('infoClass');
		$class	= $bll->get($classID);
		$extend	= unserialize($class['info']);

		$hide = array(
			'info_edit'				=> $this->chkInfo($classID, 'index', 'edit'),
			'info_delete'			=> $this->chkInfo($classID, 'index', 'delete'),
			'info_preview'			=> $this->chkInfo($classID, 'index', 'preview'),
			'pic'					=> $extend['hasImageUrl'] ? $this->chkAction('upload/pic') : $extend['hasImageUrl'],
			'pic2'					=> $extend['hasBigImageUrl'] ? $this->chkAction('upload/pic') : $extend['hasBigImageUrl'],
			'file'					=> $extend['hasFiles'] ? $this->chkAction('upload/file') : $extend['hasFiles']
		);
		return $hide;
	}

}

?>