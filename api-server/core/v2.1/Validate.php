<?php

/**
* 表单验证
* @author Curitis Niewei
* @version 2.0
* @created 07-04-2012
*/

class Validate {
	private $validate_errors_count = 0;
	private $validate_errors = array();

	function __construct( $validates, $data ) {
		foreach ( $validates as $key => $validate ) {
			$method = $validate['method'];
			if ( empty( $method ) ) {
				$method = 'required';
			}
			if ( ! $this->$method( $data[$key] ) ) {
				$this->validate_errors_count++;
				$this->validate_errors[] = $validate['message'];
			}
		}
	}

	function valid() {
		if ( $this->validate_errors_count > 0 ) {
			return false;
		}
		return true;
	}

	function getValidateErrors( $sep = '<br />' ) {
		if ( ! empty( $sep ) ) {
			return implode( $sep, $this->validate_errors );
		}
		return $this->validate_errors;
	}

	/*-------------验证函数-------------*/
	/**
	* 必填
	*/
	function required( $value ) {
		return ! empty( $value );
	}

	/**
	* 邮箱
	*/
	function email( $value ) {
		return preg_match( "/^[a-z0-9]+([_\\.-][a-z0-9]+)*" ."@"."([a-z0-9]+([\.-][a-z0-9]+)*)+"."\\.[a-z]{2,}$/", $value );
	}

	/**
	* 数字
	*/
	function numeric( $value ) {
		$tmp = (float)$value;
		$tmp .= '';
		return $tmp == $value;
	}

	/**
	* 正数
	*/
	function positive( $value ) {
		$tmp = abs( (float)$value ).'';
		return $tmp == $value;
	}
}
?>