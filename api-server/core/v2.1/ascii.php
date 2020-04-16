<?php

class ascii {

	/**
	 * 将ascii码转为字符串
	 * 16进制
	 */
	public function asciiDecode($sacii){
		$str = '';
		$asc_arr= str_split(strtolower($sacii),2);
		for($i=0; $i<count($asc_arr); $i++){
			$str .= chr(hexdec($asc_arr[$i]));
		}
		return mb_convert_encoding($str,'UTF-8','GB2312');
	}

	/**
	 * 将字符串转换为ascii码
	 * 16进制
	 */
	public static function asciiEncode($scill) {
		$str = '';
		$scill = mb_convert_encoding($scill,'GB2312');
		for($i=0; $i<strlen($scill); $i++){
			$temp_str = dechex(ord($scill[$i]));
			$str .= $temp_str;
		}
		return $str;
	}

	function str2hex($string){
		$hex='';
		for ($i=0; $i < strlen($string); $i++){
			$hex .= dechex(ord($string[$i]));
		}
		return $hex;
	}

	function hex2str($hex){
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}


}
