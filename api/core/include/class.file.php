<?php

class file
{

	function name ($fn)
	{
		$f = substr($fn, strlen($fn) - 1);
		if ($f == '/' || $f == '\\') $fn = substr($fn, 0, strlen($fn) - 1);
		$f_arr = preg_split('/\//', $fn);
		return end($f_arr);
	}

	function nameExtend( $fn, $extend, $direction = 'right' ) {
		$ext = '.'.$this->ext( $fn );
		$dir = dirname( $fn ).'/';
		$name = str_replace( array( $ext, $dir ), '', $fn );
		if ( $direction == 'right' ) $name = $name.$extend;
		else $name = $extend.$name;
		return $dir.$name.$ext;
	}

	function ext ($filename)
	{
		return end(explode('.', $filename));
	}

	function createdir($dn, $adm = 0777)
	{
		if (is_dir($dn)) return true;
		$str = '';
		if (preg_match('/\//', $dn))
		{
			$dn_arr = explode('/', $dn);
			foreach ($dn_arr as $k => $v)
			{
				$str .= $v;
				if (!is_dir($str) && $v != '')
				{
					if (!mkdir($str, $adm))
					{
						//echo $str;exit;
						//if (!mkdir($str))
						//return false;
					}
				}
				$str .= '/';
			}
		}
		return true;
	}

	function upfile ($allow_arr, $tmp_file, $tar_path, $tar_name, $ext = '')
	{
		if ( empty( $ext ) ) $ext = strtolower(end(explode('.', $tmp_file['name'])));
		if (in_array($ext, $allow_arr))
		{
			$pic		= $tar_name.'.'.$ext;
			$filename	= str_replace('//', '/', $tar_path.'/'.$pic);
			self::createdir(str_replace(end(explode('/', $filename)), '', $filename));
			if (function_exists('move_uploaded_file'))
			{
				move_uploaded_file($tmp_file['tmp_name'], $filename);
			}
			else
			{
				@copy($tmp_file['tmp_name'], $filename);
			}
		}
		else return false;
		return $pic;
	}

	function createfile ($fn, $str='', $write = true)
	{
		if (!$write && file_exists($fn)) return false;
		if (preg_match('/\//', $fn))
		{
			$filename = end(explode('/', $fn));
			$this->createdir(str_replace('/'.$filename, '', $fn));
		}
		$fp = fopen($fn, 'w');
		fwrite($fp, $str);
		fclose($fp);

		return true;
	}

	function is_empty_dir ($dn)
	{
		return $this->readdir($dn) ? false : true;
	}

	function readdir ($dn)
	{
		if ($hd = opendir($dn))
		{
			while (false !== ($fl = readdir($hd)))
			{
				if ($fl != '.' && $fl != '..')
				{
					$fs[] = $fl;
				}
			}
		}
		return $fs ? $fs : false;
	}

	function readfile ($fn)
	{
		if (file_exists($fn))
		{
			if (function_exists('file_get_contents'))
				return file_get_contents($fn);
			else
			{
				$fp		= fopen($fn, 'r');
				$str	=fread($fp, filesize($fn));
				fclose($fp);
			}
			return $str;
		}
		else
			return null;
	}

	function deletefile ($filepath)
	{
		if (is_array($filepath))
		{
			foreach ($filepath as $i=>$f)
			{
				if (file_exists($f))
				{
					unlink($f);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			if (file_exists($filepath))
			{
				unlink($filepath);
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	function clsdir ($dn)
	{
		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d) is_dir($ds.'/'.$d) ? $this->deldir($ds.'/'.$d) : $this->deletefile($ds.'/'.$d);
					}
				}
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d) is_dir($dn.'/'.$d) ? $this->deldir($dn.'/'.$d) : $this->deletefile($dn.'/'.$d);
				}
			}
		}
	}

	function deldir ($dn, $mod = false)
	{
		//删除目录    在linux下成功，window下删除不了文件夹
		/*
		  fn - 要删除的目录，支持数组
		  mod - 没有权限时是否强制删除
		*/

		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d)
							is_dir($ds.'/'.$d) ? $this->deldir($ds.'/'.$d) : $this->deletefile($ds.'/'.$d);
					}
					if($mod)
						chmod($ds, 0777);
					rmdir($ds);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d)
						is_dir($dn.'/'.$d) ? $this->deldir($dn.'/'.$d) : $this->deletefile($dn.'/'.$d);
				}
				else
				{
					if($mod)
						chmod($dn, 0777);
					rmdir($dn);
				}
			}else
				return false;
		}
	}

	function deleteFiles( $delete_files ) {
		if ( is_array( $delete_files ) ) {
			foreach ( $delete_files as $df ) {
				$this->deleteFiles( $df );
			}
		}
		else {
			@unlink( $delete_files );
		}
	}

	function readDir2( $dirpath ) {
		if ( ! is_dir( $dirpath ) ) {
			return false;
		}

		if ( $hd = opendir( $dirpath ) ) {
			$files = array();
			while ( false !== ( $file = readdir( $hd ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					$files[] = $file;
				}
			}
		}
		else {
			return false;
		}

		closedir( $hd );
		return $files;
	}

	function deleteDirs( $dir_path, $keep_first_dir = false, $level = 0 ) {
		if ( is_array( $dir_path ) ) {
			foreach ( $dir_path as $dp ) {
				$this->deleteDirs( $dp, $keep_first_dir );
			}
		}
		else {
			if ( is_dir( $dir_path ) ) {
				if ( false !== ( $files = $this->readDir2( $dir_path ) ) ) {
					foreach ( $files as $file ) {
						$file = $dir_path.'/'.$file;
						if ( is_dir( $file ) ) {
							$this->deleteDirs( $file, false, $level + 1 );
						}
						else {
							$this->deleteFiles( $file );
						}
					}
					if ( ! $keep_first_dir || $level > 0 ) {
						chmod( $dir_path, 0777 );
						rmdir( $dir_path );
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		return true;
	}

	function copyfile ($fn, $dir = '')
	{
		if ($dir!='' && !is_dir($dir))
			$this->createdir($dir);
		$dir = $dir == '' ? '' : $dir.'/';

		if (is_array($fn))
		{
			foreach ($fn as $f)
				copy($f, $dir.$this->name($f));
		}
		else
		{
			copy($fn, $dir.$this->name($fn));
		}
	}

	function movefile($fn, $odir = '', $dir = '')
	{
		$odir = $odir == '' ? '' : $odir.'/';
		if (!file_exists($odir.$fn)) return false;
		if ($dir != '' && !is_dir($dir)) $this->createdir($dir);
		$dir = $dir == '' ? '' : $dir.'/';

		if (is_array($fn))
		{
			foreach($fn as $f) $this->rnamefile($odir.$f, $dir.$f);
		} else $this->rnamefile($odir.$fn, $dir.$fn);
	}

	function copydir ($dn, $dir, $wrap = true)
	{
		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if ($wrap) $this->createdir($dir.'/'.$this->name($ds));
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d) is_dir($ds.'/'.$d) ? $this->copydir($ds.'/'.$d, $dir.'/'.($wrap ? $this->name($ds) : '')) : $this->copyfile($ds.'/'.$d, $dir.'/'.($wrap ? $this->name($ds) : ''));
					}
				} else $this->copyfile($ds, $dir);
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if ($wrap) $this->createdir($dir.'/'.$this->name($dn));
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d) is_dir($dn.'/'.$d) ? $this->copydir($dn.'/'.$d, $dir.'/'.($wrap ? $this->name($dn) : '')) : $this->copyfile($dn.'/'.$d, $dir.'/'.($wrap ? $this->name($dn) : ''));
				}
			} else $this->copyfile($dn, $dir);
		}
	}

	function movedir ($dn, $dir)
	{
		$this->copydir($dn, $dir);
		$this->deldir($dn);
	}

	function rnamefile ($fn, $nfn)
	{
		if ($nfn == '' || !file_exists($fn) || file_exists($nfn)) return false;
		rename($fn, $nfn);
		return true;
	}

	function rnamedir ($dn)
	{
		
	}

	function size ($fn)
	{
		//return Byte
		$fjsize = 0;
		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..')
					{
						if (is_dir($fn.'/'.$fl))
							$fjsize += $this->size($fn.'/'.$fl);
						else
						{
							$fjsize += filesize($fn.'/'.$fl);
						}
					}
				}
			}
		}
		else
		{
			$flsize = filesize($fn);
		}
		return $fjsize;
	}

	function info ($fn)
	{
		return array(filectime($fn), filemtime($fn));
	}

	function tree ($fn, $allow_arr = '')
	{
		$list	= array();
		$robots	= array();

		/* robots.txt
		$robots	= explode("\r\n", $this->readfile('../robots.txt'));
		$robots	= array_splice($robots, 1);
		for ($i = 0; $i < count($robots); $i++)
		{
			$robots[$i]	= str_replace('Disallow: ', '', $robots[$i]);
			$tmp		= explode('/', $robots[$i]);
			$robots[$i]	= $tmp[1];
		}
		*/

		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..' && !in_array($fl, $robots))
					{
						if (is_dir($fn.'/'.$fl)) $list[] = $this->tree($fn.'/'.$fl, $allow_arr);
						else
						{
							if (is_array($allow_arr))
							{
								if (in_array($this->ext($fl), $allow_arr)) $list[] = $fn.'/'.$fl;
							} else $list[] = $fn.'/'.$fl;
						}
					}
				}
			}
		} else return false;
		return $list;
	}

	function actionPermissionArray (& $ctlArray, $fn, $startPath = '', $noAllowDir = array())
	{
		//针对后台使用
		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..' && !in_array($fl, $noAllowDir))
					{
						if (is_dir($fn.'/'.$fl))
						{
							self::actionPermissionArray($ctlArray, $fn.'/'.$fl, $startPath, $noAllowDir);
						}
						else
						{
							if (preg_match('/ctl\.(.*)\.php/', $fn.'/'.$fl))
							{
								$ctl_name = preg_replace('/ctl\.(.*)\.php/', '$1', str_replace($startPath, '', $fn.'/'.$fl));
								if (left($ctl_name, 1) == '/') $ctl_name = left($ctl_name, 1, true);
								$ctlArray[] = array(
									$fn.'/'.$fl,
									//preg_replace('/ctl\.(.*)\.php/', '$1', str_replace($fn.'/', '', $fn.'/'.$fl))
									$ctl_name
								);
							}
						}
					}
				}
			}
		} else return false;
		return $ctlArray;
	}

	/**
	* image_src	原始图片路径
	* save_src	处理后的图片保存路径
	* width		宽度
	* height	高度
	* geometric	等比缩放
	* forcut	为裁剪先进行缩放
	* ext		导出的图片格式，0自动，1gif，2jpg，3png
	*/
	function resize( $image_src, $save_src, $width, $height, $geometric = true, $forcut = false, $ext = 0 ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		imagesavealpha( $im, true );
		$old_width = imagesx( $im );
		$old_height = imagesy( $im );

		if ( $geometric ) {
			if ( $old_width / $old_height > $width / $height ) {
				if ( $forcut == true ) {
					$width = intval( $height / $old_height * $old_width );
				}
				else {
					$height = intval( $width / $old_width * $old_height );
				}
			}
			else {
				if ( $forcut == true ) {
					$height = intval( $width / $old_width * $old_height );
				}
				else {
					$width = intval( $height / $old_height * $old_width );
				}
			}
		}

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, 0, 0, $width, $height, $old_width, $old_height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, 0, 0, $width, $height, $old_width, $old_height );
		}

		//ob_start();
		//header('Content-type: image/jpeg');
		/*switch ( $image_state[2] ) {
			case 1 : imagegif( $new, $save_src ); break;
			case 2 : imagejpeg( $new, $save_src, 90 ); break;
			case 3 : imagepng( $new, $save_src ); break;
		}*/
		$this->saveImage( $ext >= 1 && $ext <= 3 ? $ext : $image_state[2], $new, $save_src );
		//imagejpeg( $new );
		//exit;
	}

	function cutByPos( $image_src, $save_src, $width, $height, $pos = 'center,top' ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		imagesavealpha( $im, true );

		$cx = 0;
		$cy = 0;
		$posarr = explode( ',', $pos );
		if ( count( $posarr ) != 2 ) {
			$posarr = array( 'center', 'top' );
		}
		$old_width = imagesx( $im );
		$old_height = imagesy( $im );

		if ( $width >= $old_width && $height >= $old_height ) {
			$this->saveImage( $image_state[2], $im, $save_src );
			return false;
		}

		if ( $width >= $old_width ) {
			$width = $old_width;
		}
		else {
			switch ( $posarr[0] ) {
				case 'left' : $cx = 0; break;
				case 'center' : $cx = ( $old_width - $width ) / 2; break;
				case 'right' : $cx = $old_width - $width; break;
			}
		}
		if ( $height >= $old_height ) {
			$height = $old_height;
		}
		else {
			switch ( $posarr[1] ) {
				case 'top' : $cy = 0; break;
				case 'middle' : $cy = ( $old_height - $height ) / 2; break;
				case 'bottom' : $cy = $old_height - $height; break;
			}
		}

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, $cx, $cy, $width, $height, $width, $height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, $cx, $cy, $width, $height, $width, $height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	/**
	* 根据post提交来的bounds数组，处理旋转、缩放和裁剪范围计算
	* @param bound是post提交过来的边界坐标
	* @param width是最终的宽度
	* @param height是最终的高度
	* @param isNoBoundDoScale，如果没有提供bound或者bound数据不合法，是否仍然将图片缩放到width2，高度自动
	* @param fileType，输出的图片格式，2是jpg，0是默认
	*/
	function cutByPosBoundPost( $image_src, $bound, $width, $height, $isNoBoundDoScale = true, $width2 = 0, $fileType = 0 ) {
		/*
		bs数组元素：
		0 裁剪窗口中图片的宽度
		1 裁剪窗口中图片的高度
		2 裁剪窗口中裁剪区域的左上角x坐标
		3 裁剪窗口中裁剪区域的左上角y坐标
		4 裁剪窗口中裁剪区域的右下角x坐标
		5 裁剪窗口中裁剪区域的右下角y坐标
		6 裁剪窗口中图片的旋转角度
		7 裁剪窗口中图片的缩放比例
		*/
		if ( $bound[4] - $bound[2] > 0 && $bound[5] - $bound[3] > 0 && $bound[0] > 0 && $bound[1] > 0 ) {
			$boundWidth = $bound[4] - $bound[2];
			$boundHeight = $bound[5] - $bound[3];
			$photoSourceInfo = getimagesize( $image_src );
			$photoSourceWidth = $photoSourceInfo[0];
			$photoSourceHeight = $photoSourceInfo[1];
			$rate = $photoSourceWidth / $bound[0];
			//旋转
			$bound[6] = 0 - (int)$bound[6];
			if ( $bound[6] != 0 && $bound[6] % 360 != 0 ) {
				$this->rotate( $image_src, $image_src, $bound[6], array( 255, 255, 255 ) );
				$photoRotateInfo = getimagesize( $image_src );
				$photoRotateWidth = $photoRotateInfo[0];
				$photoRotateHeight = $photoRotateInfo[1];
				$bound[2] = $bound[2] - (int)( ( $photoSourceWidth / $rate - $photoRotateWidth / $rate ) / 2 );
				$bound[3] = $bound[3] - (int)( ( $photoSourceHeight / $rate - $photoRotateHeight / $rate ) / 2 );
				$bound[4] = $bound[2] + $boundWidth;
				$bound[5] = $bound[3] + $boundHeight;
			}
			//缩放
			$bound[7] = (float)$bound[7];
			if ( $bound[7] != 1 && $bound[7] >= 0.5 && $bound[7] <= 2 ) {
				if ( $photoRotateWidth ) {
					$photoScaleWidth = (int)( $photoRotateWidth * $bound[7] );
					$photoScaleHeight = (int)( $photoRotateHeight * $bound[7] );
					$bound[2] = $bound[2] - (int)( ( $photoRotateWidth / $rate - $photoScaleWidth / $rate ) / 2 );
					$bound[3] = $bound[3] - (int)( ( $photoRotateHeight / $rate - $photoScaleHeight / $rate ) / 2 );
				}
				else {
					$photoScaleWidth = (int)( $photoSourceWidth * $bound[7] );
					$photoScaleHeight = (int)( $photoSourceHeight * $bound[7] );
					$bound[2] = $bound[2] - (int)( ( $photoSourceWidth / $rate - $photoScaleWidth / $rate ) / 2 );
					$bound[3] = $bound[3] - (int)( ( $photoSourceHeight / $rate - $photoScaleHeight / $rate ) / 2 );
				}
				$bound[4] = $bound[2] + $boundWidth;
				$bound[5] = $bound[3] + $boundHeight;
				$this->resize( $image_src, $image_src, $photoScaleWidth, $photoScaleHeight, false, false, $fileType );
			}
			$this->cutByPosBound( $image_src, $image_src, array( 'x1' => (int)( $bound[2] * $rate ), 'y1' => (int)( $bound[3] * $rate ), 'x2' => (int)( $bound[4] * $rate ), 'y2' => (int)( $bound[5] * $rate ) ) );
			$this->resize( $image_src, $image_src, $width, $height, true, true, $fileType );
			return array( 'width' => $width, 'height' => $height );
		}
		else {
			$photoInfo = getimagesize( $image_src );
			$photoWidth = $photoInfo[0];
			$photoHeight = $photoInfo[1];
			if ( $isNoBoundDoScale ) {
				if ( $photoWidth > $width2 ) {
					$photoHeight *= $width2 / $photoWidth;
					$photoWidth = $width2;
				}
				$this->resize( $image_src, $image_src, $photoWidth, $photoHeight, true, false, $fileType );
				return array( 'width' => $photoWidth, 'height' => $photoHeight );
			}
			return array( 'width' => $photoWidth, 'height' => $photoHeight );
		}
	}

	/**
	* 根据指定的边界值裁剪出图片
	*/
	function cutByPosBound( $image_src, $save_src, $bounds ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		$old_width = $image_state[0];
		$old_height = $image_state[1];
		imagesavealpha( $im, true );

		$width = $bounds['x2'] - $bounds['x1'];
		$height = $bounds['y2'] - $bounds['y1'];
		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, $bounds['x1'], $bounds['y1'], $width, $height, $width, $height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, $bounds['x1'], $bounds['y1'], $width, $height, $width, $height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	function fillColor( $image_src, $save_src, $width, $height, $color ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		$old_width = $image_state[0];
		$old_height = $image_state[1];
		$x1 = $y1 = 0;
		$x1 = abs( $old_width - $width ) / 2;
		$y1 = abs( $old_height - $height ) / 2;
		imagesavealpha( $im, true );

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}
		$bg = imagecolorallocate( $new, $color[0], $color[1], $color[2] );
		imagefill( $new, 0, 0, $bg );

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, $x1, $y1, 0, 0, $old_width, $old_height, $old_width, $old_height );
		}
		else {
			imagecopyresized( $new, $im, $x1, $y1, 0, 0, $old_width, $old_height, $old_width, $old_height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	function rotate( $image_src, $save_src, $deg, $color ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		$old_width = $image_state[0];
		$old_height = $image_state[1];
		$x1 = $y1 = 0;
		$x1 = abs( $old_width - $width ) / 2;
		$y1 = abs( $old_height - $height ) / 2;
		imagesavealpha( $im, true );

		$bg = imagecolorallocate( $im, $color[0], $color[1], $color[2] );
		$new = imagerotate( $im, $deg, $bg );

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	//必须要是jpg图片才可以，tiff基本在网页上不考虑了
	function adjustPicOrientation( $pic, $pic2 = '' ) {
		if ( !function_exists( 'exif_read_data' ) ) return false;
		$exif = exif_read_data( $pic );
		if ( $exif && isset( $exif['Orientation'] ) ) {
			$orientation = $exif['Orientation'];
			if ( $orientation != 1 ) {
				$img = imagecreatefromjpeg( $pic );

				$mirror = false;
				$deg = 0;

				switch ( $orientation ) {
					case 2: $mirror = true; break;
					case 3: $deg = 180; break;
					case 4:
						$deg = 180;
						$mirror = true;
						break;
					case 5:
						$deg = 270;
						$mirror = true;
						break;
					case 6: $deg = 270; break;
					case 7:
						$deg = 90;
						$mirror = true;
						break;
					case 8: $deg = 90; break;
				}
				if ( $deg ) $img = imagerotate( $img, $deg, 0 );
				if ( $mirror ) $img = mirrorImage( $img );
				if ( empty( $pic2 ) ) $pic2 = $pic;
				imagejpeg( $img, $pic2, 90 );
			}
		}
	}

	function mirrorImage( $imgSource ) {
		$width = imagesx ( $imgSource );
		$height = imagesy ( $imgSource );

		$src_x = $width - 1;
		$src_y = 0;
		$src_width = -$width;
		$src_height = $height;

		$imgdest = imagecreatetruecolor( $width, $height );

		if ( imagecopyresampled( $imgdest, $imgSource, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height ) ) {
			return $imgdest;
		}

		return $imgSource;
	}

	function saveImage( $image_state, $im, $save_src ) {
		switch ( $image_state ) {
			case 1 : imagegif( $im, $save_src ); break;
			case 2 : imagejpeg( $im, $save_src, 90 ); break;
			case 3 : imagepng( $im, $save_src ); break;
		}
	}

}

?>