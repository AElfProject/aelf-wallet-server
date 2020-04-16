<?php

/*
 @ctl_name = 弹窗上传管理@
*/

class ctl_upload extends adminPage
{

	private $id;
	private $tableName;
	private $idName;
	private $picName;
	private $url;
	private $info;

	private $uploadfile;
	private $other;  //对应分类的扩展信息

	function ctl_upload ()
	{
		parent::adminPage();

		$this->id			= get2('id');
		$this->tableName	= get2('tableName');
		$this->idName		= get2('idName');  //无用
		$this->picName		= get2('picName');

		//根据请求，检测相关扩展信息，如：允许上传的扩展名等参数
		if ($this->tableName == 'info')
		{
			$bllInfo	= $this->loadModel($this->tableName);
			$info		= $bllInfo->get($this->id);
			if (!$info) $this->sheader(null, $this->lang->current_info_not_exists);

			$this->uploadfile = $info[$this->picName];
			$bllClass	= $this->loadModel('infoClass');
			$class		= $bllClass->get($info['classId']);
			if (!$class) $this->sheader(null, $this->lang->current_info_category_not_exists);

			$this->other = unserialize($class['other']);
			$this->info = $info;
			unset($bllInfo);
			unset($info);
			unset($bllClass);
			unset($class);
		}
		elseif ($this->tableName == 'infoClass')
		{
			$bllClass	= $this->loadModel($this->tableName);
			$class		= $bllClass->get($this->id);
			if (!$class) $this->sheader(null, $this->lang->current_info_category_not_exists);

			$this->uploadfile	= $class[$this->picName];
			$this->other		= unserialize($class['other']);

			unset($bllClass);
			unset($class);
		}
		else
		{
			$bll	= $this->loadModel($this->tableName);
			$model	= $bll->get($this->id);
			$this->uploadfile = $model[$this->picName];
			unset($bll);
			unset($model);
		}

		if (trim($this->other['exts']) == '') $this->other['exts'] = '';
		else
		{
			$this->other['exts'] = explode('|', $this->other['exts']);
			foreach ($this->other['exts'] as $key=>$value)
			{
				$this->other['exts'][$key] = strtolower(trim($value));
			}
		}

		//路径
		$this->url = '?con=admin&ctl=upload&act='.get2('act').'&id='.$this->id.'&tableName='.$this->tableName.'&idName='.$this->idName.'&picName='.$this->picName;
	}

	public function pic_action () #act_name = 图片上传#
	{
		$exts = array('jpg', 'jpeg', 'png', 'gif');

		if (is_post() && $_FILES)
		{
			$filepath = date('Y-m');
			$this->file->createdir('data/upload/'.$filepath);
			$filename = $this->file->upfile($exts, $_FILES['file'], UPDATE_DIR, $filepath.'/'.date('YmdHis').$this->createRnd());
			if ($filename)
			{
				$bll	= $this->loadModel($this->tableName);
				//检测并删除原图
				if ($this->uploadfile) $this->file->deletefile(UPDATE_DIR.$this->uploadfile);
				$data[$this->picName] = $filename;

				if ( $this->tableName == 'info' ) {
					//缩略图
					if ( $this->picName == 'imageUrl' ) {
						if ( $this->other['pic1width'] > 0 && $this->other['pic1height'] > 0 ) {
							$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['pic1width'], (int)$this->other['pic1height'], true, true );
							$this->file->cutByPos( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['pic1width'], (int)$this->other['pic1height'] );
						}
					}
					else if ( $this->picName == 'bigImageUrl' ) {
						if ( $this->other['pic2width'] > 0 && $this->other['pic2height'] > 0 ) {
							$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['pic2width'], (int)$this->other['pic2height'], true, true );
							$this->file->cutByPos( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['pic2width'], (int)$this->other['pic2height'] );
						}
					}
				}
				else if ( $this->tableName == 'infoClass' ) {
					//缩略图
					if ( $this->picName == 'imageUrl' ) {
						if ( $this->other['cpic1width'] > 0 && $this->other['cpic1height'] > 0 ) {
							$this->file->resize( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['cpic1width'], (int)$this->other['cpic1height'], true, true );
							$this->file->cutByPos( UPDATE_DIR.$filename, UPDATE_DIR.$filename, (int)$this->other['cpic1width'], (int)$this->other['cpic1height'] );
						}
					}
				}

				if ($bll->update($data, $this->id)) $this->sheader($this->url);
				else
				{
					$this->file->deletefile(UPDATE_DIR.$filename);
					$this->sheader(null, $this->lang->upload_pic_success_but_written_database_failed);
				}
			}
			else $this->sheader(null, $this->lang->upload_pic_failed);
		}
		else
		{
			$delete = (int)get2('delete');
			if ($delete === 1)  //删除图片
			{
				$bll		= $this->loadModel($this->tableName);
				$data[$this->picName] = '';
				if ($bll->update($data, $this->id))
				{
					if ($this->uploadfile) $this->file->deletefile(UPDATE_DIR.$this->uploadfile);
					$this->sheader($this->url);
				}
				else $this->sheader(null, $this->lang->delete_pic_failed);
			}

			$data = array();
			$data['fileName']	= $this->uploadfile;
			$data['fileExt']	= end(explode('.', $this->uploadfile));
			$data['exts']		= $exts;
			$data['intro']		= (string)$this->lang->allow_file_format.(string)$this->lang->maohao.implode(', ', $exts);
			$data['filefullname'] = UPLOAD_PATH.$this->uploadfile;

			$this->setData($this->url, 'actionUrl');
			$this->setData($this->url.'&delete=1', 'deleteUrl');
			$this->setData($data);
			$this->display('upload');
		}
	}

	public function file_action () #act_name = 附件上传#
	{
		$exts = $this->other['exts'];
		if (!is_array($exts)) $exts = array('rar', 'jpg', 'jpeg', 'gif', 'png');

		if (is_post() && $_FILES)
		{
			$filepath = date('Y-m');
			$this->file->createdir('data/upload/'.$filepath);
			$filename = $this->file->upfile($exts, $_FILES['file'], UPDATE_DIR, $filepath.'/'.date('YmdHis').$this->createRnd());
			if ($filename)
			{
				$bll	= $this->loadModel($this->tableName);
				//检测并删除原文件
				if ($this->uploadfile) $this->file->deletefile(UPDATE_DIR.$this->uploadfile);
				$data[$this->picName] = $filename;
				if ($bll->update($data, $this->id)) $this->sheader($this->url);
				else
				{
					$this->file->deletefile(UPDATE_DIR.$filename);
					$this->sheader(null, $this->lang->upload_file_success_but_written_database_failed);
				}
			}
			else $this->sheader(null, $this->lang->upload_file_failed);
		}
		else
		{
			$delete = (int)get2('delete');
			if ($delete === 1)  //删除
			{
				$bll		= $this->loadModel($this->tableName);
				$data[$this->picName] = '';
				if ($bll->update($data, $this->id))
				{
					if ($this->uploadfile) $this->file->deletefile(UPDATE_DIR.$this->uploadfile);
					$this->sheader($this->url);
				}
				else $this->sheader(null, $this->lang->delete_file_failed);
			}

			$data = array();
			$data['fileName']	= $this->uploadfile;
			$data['fileExt']	= end(explode('.', $this->uploadfile));
			$data['exts']		= $exts;
			$data['intro']		= (string)$this->lang->allow_file_format.(string)$this->lang->maohao.implode(', ', $exts);
			$data['filefullname'] = UPLOAD_PATH.$this->uploadfile;

			$this->setData($this->url, 'actionUrl');
			$this->setData($this->url.'&delete=1', 'deleteUrl');
			$this->setData($data);
			$this->display('upload');
		}
	}

}

?>