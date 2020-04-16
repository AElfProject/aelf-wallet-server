<?php

class ctl_verifycode extends corecms
{

	function index_action() {
		$w = (int)get2( 'w' );
		$h = (int)get2( 'h' );
		$index = (int)get2( 'i' );
		if ( $w <= 0 ) $w = 135;
		if ( $h <= 0 ) $h = 45;
		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";  //要随机产生的字符
		$s = str_split( $string );
		$font_name = DOC_DIR.'fonts/arial-bold.otf';
		$font = (int)( $h / 2 );  //字符大小
		$num = 4;  //字符数
		$str = "";
		for ( $i = 0; $i < $num; $i++ ) {
			$str .= $s[rand( 0, strlen( $string ) - 1 )];
		}

		//$img = imagecreatetruecolor( 135, 45 );  //生成画板
		$img = imagecreate( $w, $h );  //生成画板

		$bc = imagecolorallocate( $img, 255, 255, 255 );  //填充背景--第一次使用imagecolorallocate()时默认是设置画布背景的
		$fc = imagecolorallocate( $img, 0, 0, 0 );  //定义一种颜色

		for( $i = 0; $i < 10; $i++ ) {
			$brushColor = imagecolorallocate( $img, rand( 0, 255 ), rand( 0, 255 ), rand( 0, 255 ) );
			imageline( $img, rand( 0, $w ), rand( 0, $h ), rand( 0, $w ), rand( 0, $h ), $brushColor );
		}

		for ( $i = 0; $i < 100; $i++ ) {
			$brushColor = imagecolorallocate( $img, rand( 0, 255 ), rand( 0, 255 ), rand( 0, 255 ) );
			imagesetpixel( $img, rand( 0, $w ), rand( 0, $h ), $brushColor );
		}

		//imagestring( $img, 5, 10, 6, $str, $fc );
		imagettftext( $img, $font, 5, (int)( ( $w - $num * $font ) / 2 ), $font / 2 + (int)( $h / 2 ), $fc, $font_name, $str );

		$_SESSION["yzm".($index == 0 ? '' : $index)] = $str;

		//输出
		ob_end_clean();
		header( "content-type:image/jpeg" );  //使用图片的格式输出文件，记着!!
		imagejpeg( $img );
	}

}