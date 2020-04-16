<?php

/**
* URL解析
* @author Curitis Niewei
* @version 2.0
* @created 30-10-2011
*/

class ParseURL {
	private $url;
	private $url_array = array();
	private $query_array = array();

	function __construct( $url = null ) {
		$url = trim( $url );
		if ( ! isset( $url ) || empty( $url ) ) {
			$this->url = ( $_SERVER['HTTPS'] == 'on' ? 'https' : 'http' ).'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		else {
			$this->url = $url;
		}
		$this->url_array = parse_url( $this->url );
		foreach ( explode( '&', $this->url_array['query'] ) as $key => $val ) {
			$val = trim( $val );
			if ( ! empty( $val ) && strpos( $val, '=' ) ) {
				$vals = explode( '=', $val );
				if ( isset( $this->query_array[trim( $vals[0] )] ) ) {
					if ( is_array( $this->query_array[trim( $vals[0] )] ) ) $this->query_array[trim( $vals[0] )][] = trim( $vals[1] );
					else {
						$tmp = $this->query_array[trim( $vals[0] )];
						$this->query_array[trim( $vals[0] )] = array( $tmp );
						$this->query_array[trim( $vals[0] )][] = trim( $vals[1] );
					}
				}
				else $this->query_array[trim( $vals[0] )] = trim( $vals[1] );
			}
		}
	}

	/**
	* set( $key, $val ) => 添加或替换
	* set( $key ) => 删除
	* @return ParseURL
	*/
	function set( $key, $val = null ) {
		if ( $val === null || $val === '' ) {
			if ( isset( $this->query_array[$key] ) ) {
				$this->delete( $key );
			}
		}
		else {
			$this->query_array[$key] = $val;
		}
		return $this;
	}

	/**
	* 设置路径
	*/
	function setPath( $path ) {
		$this->url_array['path'] = '/'.BASE_DIR.$path;
		return $this;
	}

	/**
	* 从请求数组中获取指定key的值
	*/
	function get( $key ) {
		return $this->query_array[$key];
	}

	/**
	* 返回网络协议
	*/
	function getScheme() {
		if ( isset( $this->url_array['scheme'] ) ) {
			return $this->url_array['scheme'].'://';
		}
		else {
			return null;
		}
	}

	/**
	* 返回主机名
	*/
	function getHost() {
		return str_replace( ':', '', $this->url_array['host'] );
	}

	/**
	* 返回端口
	*/
	function getPort() {
		if ( isset( $this->url_array['port'] ) ) {
			return ':'.$this->url_array['port'];
		}
		else {
			return null;
		}
	}

	/**
	* 返回一级域名，不包括子级域名，注意：www也作为二级域名，故不会返回www.
	* 此功能暂时存在问题，因为有个问题没有解决，如下：
	* abc.com.cn
	* 可以理解为一级域名，也可以理解为二级域名
	*/
	function getDomain() {
		$full = $this->getFullDomain();
		//preg_match_all( '/(?<http>[a-zA-Z0-9]*:\/\/)?(?<subdomain>[a-zA-Z0-9\.]*)?\.(?<domain>[a-zA-Z0-9]*)\.((?<domaintype>[a-zA-Z]*)?\.?([a-zA-Z]*))+([:])?(?<port>[0-9]*)?/', $full, $arr );
		preg_match( '/^[a-zA-Z]*:\/\/?([a-zA-Z0-9\.]*):?[0-9]*?$/', $full, $matches );
		if ( ! empty( $matches[1] ) ) {
			return $matches[1];
			//$array = explode( '.', $matches[1] );
			
		}
		return false;
	}

	/**
	* 返回完整的域名，包括子级域名
	*/
	function getFullDomain() {
		return $this->getScheme().$this->getHost().$this->getPort();
	}

	/**
	* 返回路径
	*/
	function getPath() {
		return $this->url_array['path'];
	}

	/**
	* 返回完整域名 + 完整的路径
	*/
	function getBasePath() {
		return $this->getFullDomain().$this->getPath();
	}

	/**
	* 返回文件名
	*/
	function getFileName() {
		$path = $this->getPath();
		$filename = '';
		if ( $start = strrpos( $path, '/' ) ) {
			$filename = substr( $path, $start + 1, strlen( $path ) );
		}
		return $filename;
	}

	/**
	* 从请求数组中删除请求
	*/
	function delete( $key ) {
		$tmp = array();
		foreach ( $this->query_array as $k => $v ) {
			if ( $k != $key ) {
				$tmp[$k] = $v;
			}
		}
		$this->query_array = $tmp;
	}

	/**
	* 检测是否为站外链接
	*
	* @return boolean
	*/
	function checkIsOutSite() {
		$current_url_parse = new self;
		$domain = $this->getDomain();
		if ( $domain != '' && $domain != $current_url_parse->getDomain() ) {
			return true;
		}
		return false;
	}

	function getQueryString() {
		$str = '?';
		foreach ( $this->query_array as $key => $val ) {
			if ( is_array( $val ) ) {
				foreach ( $val as $v ) {
					$str .= $key.'='.$v.'&';
				}
			}
			else $str .= $key.'='.$val.'&';
		}
		return $str;
	}

	/**
	* 手动将解析对象重新组装成字符串
	*/
	function toString() {
		return $this->__toString();
	}

	/**
	* 自动将解析对象重新组装成字符串
	*/
	function __toString() {
		$str = $this->getFullDomain().$this->getPath().'?';
		foreach ( $this->query_array as $key => $val ) {
			if ( is_array( $val ) ) {
				foreach ( $val as $v ) {
					$str .= $key.'='.$v.'&';
				}
			}
			else $str .= $key.'='.$val.'&';
		}
		return $str;
	}
}
?>