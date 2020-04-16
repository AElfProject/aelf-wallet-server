<?php

/**
 Image adapter
 @Author: Curitis Niewei
 
 @param: method -> Adapter method (cut, fill)
 @return: New Image Url
 */


function smarty_modifier_image( $string, $width, $height, $method = 'fill', $pos = 'center,top', $type = 'static' ) {
	$noImage = false;
	if ( empty( $string ) || ! file_exists( UPDATE_DIR.$string ) || ! in_array( $method, array( 'cut', 'fill' ) ) || ! in_array( $pos, array( 'left,top', 'center,top', 'right,top', 'left,middle', 'center,middle', 'right,middle', 'left,bottom', 'center,bottom', 'right,bottom' ) ) ) {
		$noImage = true;
		$string = 'no-image.gif';
		if ( ! file_exists( UPDATE_DIR.$string ) ) {
			return '';
		}
		$method = 'cut';
		//return $string;
	}

	$width = (int)$width;
	$height = (int)$height;

	if ( $width <= 0 || $height <= 0 ) {
		return $string;
	}

	$file = new file;
	$newImageDir = UPDATE_DIR.'thumbnails/';
	$newImageUrl = $file->nameExtend( $string, "_{$width}x{$height}_{$method}_".str_replace( ',', '_', $pos ).( $type == 'range' ? '_range' : '' ) );

	if ( file_exists( $newImageDir.$newImageUrl ) ) {
		return 'thumbnails/'.$newImageUrl;
	}

	$image_state = getimagesize( UPDATE_DIR.$string );
	switch ( $image_state[2] ) {
		case 1 : $im = imagecreatefromgif( UPDATE_DIR.$string ); break;
		case 2 : $im = imagecreatefromjpeg( UPDATE_DIR.$string ); break;
		case 3 : $im = imagecreatefrompng( UPDATE_DIR.$string ); break;
	}
	$old_width = $image_state[0];
	$old_height = $image_state[1];

	if ( $old_width == $width && $old_height == $height ) {
		return $string;
	}

	if ( $type == 'range' ) {
		//使用range的时候，method要使用cut
		if ( $old_width > $old_height ) {
			if ( $old_width > $width ) {
				$height = (int)( $old_height * ( $width / $old_width ) );
			}
		}
		else {
			if ( $old_height > $height ) {
				$width = (int)( $old_width * ( $height / $old_height ) );
			}
		}
	}

	$newImagePath = $file->name( $newImageUrl );
	$newImagePath = str_replace( $newImagePath, '', $newImageUrl );
	$file->createdir( $newImageDir.$newImagePath, 0777 );
	if ( $method == 'fill' ) {
		$file->resize( UPDATE_DIR.$string, $newImageDir.$newImageUrl, $width, $height );
		$file->fillColor( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height, array( 255, 255, 255 ) );
	}
	elseif ( $method == 'cut' ) {
		$file->resize( UPDATE_DIR.$string, $newImageDir.$newImageUrl, $width, $height, true, true );
		$file->cutByPos( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height, $pos );
	}

	return 'thumbnails/'.$newImageUrl;
}
?>