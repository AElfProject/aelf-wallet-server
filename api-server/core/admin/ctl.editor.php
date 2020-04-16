<?php

/*
 @ctl_name = 编辑器管理@
 
 信息分类权限分配中涉及动作太多，所以将编辑器上传文件单独拿出来，以后其它的涉及编辑器与服务器端的操作，也可以放在这里
*/

class ctl_editor extends adminPage
{

	public function pic_action () #act_name = 图片上传#
	{
		$fn		= get2('CKEditorFuncNum');
		$exts	= array('jpg', 'jpeg', 'png', 'gif');

		if (is_post() && $_FILES)
		{
			$filepath	= date('Y-m');

			$this->file->createdir('data/upload/'.$filepath);
			$filename = $this->file->upfile($exts, $_FILES['upload'], UPDATE_DIR, $filepath.'/'.date('YmdHis').$this->createRnd());
			if ($filename)
			{
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$fn.', \''.UPLOAD_PATH.$filename.'\', \'\');</script>';
				exit;
			}
			else die('<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$fn.', \'\', \''.$this->lang->upload_pic_failed.'\');</script>');
		}
	}

}

?>