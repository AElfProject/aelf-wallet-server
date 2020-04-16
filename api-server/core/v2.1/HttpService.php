<?php

/**
* 远程访问服务
* @author Curitis Niewei
* @version 2.0
* @created 21-11-2012
*/

class HttpService {
	private $url;
	private $data;
	private $dataString;

	/**
	* URL是否存在
	*/
	private $url_status = false;

	/**
	* 返回结果
	*/
	private $result;

	function HttpService() {
		
	}

	function get() {
		if ( ! $this->url_status ) {
			$this->result = false;
		}
		else {
			$this->result = file_get_contents( $this->url.'?'.$this->dataString );
		}
		return $this->result;
	}

	function post( $opt = null ) {
		if ( ! $this->url_status ) {
			$this->result = false;
		}
		else {
			if ( function_exists( 'curl_init' ) ) {
				$this->result = $this->post_by_curl( $opt );
			}
			else {
				$this->result = $this->post_by_file_get_contents( $opt );
			}
			//bom
			if ( ord( $this->result[0] ) == 239 && ord( $this->result[1] ) == 187 && ord( $this->result[2] ) == 191 ) {
				$this->result = substr( $this->result, 3 );
			}
		}
		return $this->result;
	}

	private function post_by_curl( $opt = null ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_TIMEOUT, 5);
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		//检测opt
		if ( isset( $opt['header'] ) ) curl_setopt( $curl, CURLOPT_HEADER, $opt['header'] );
		if ( isset( $opt['nobody'] ) ) curl_setopt( $curl, CURLOPT_NOBODY, $opt['nobody'] );
		if ( isset( $opt['referer'] ) ) curl_setopt( $curl, CURLOPT_REFERER, $opt['referer'] );
		if ( isset( $opt['cookiejar'] ) ) curl_setopt( $curl, CURLOPT_COOKIEJAR, $opt['cookiejar'] );
		if ( isset( $opt['cookiefile'] ) ) curl_setopt( $curl, CURLOPT_COOKIEFILE, $opt['cookiefile'] );
		if ( isset( $opt['useragent'] ) ) curl_setopt( $curl, CURLOPT_USERAGENT, $opt['useragent'] );
		if ( isset( $opt['ssl_verifypeer'] ) ) curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, $opt['ssl_verifypeer'] );
		if ( isset( $opt['ssl_verifyhost'] ) ) curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, $opt['ssl_verifyhost'] );
		if ( isset( $opt['cookiesession'] ) ) curl_setopt( $curl, CURLOPT_COOKIESESSION, $opt['cookiesession'] );
		if ( isset( $opt['cookie'] ) ) curl_setopt( $curl, CURLOPT_COOKIE, $opt['cookie'] );
		if ( isset( $opt['cookiejar'] ) ) curl_setopt( $curl, CURLOPT_COOKIEJAR, $opt['cookiejar'] );
		if ( isset( $opt['cookiefile'] ) ) curl_setopt( $curl, CURLOPT_COOKIEFILE, $opt['cookiefile'] );

		curl_setopt( $curl, CURLOPT_URL, $this->url );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $this->dataString );

		$result = curl_exec( $curl );
		curl_close( $curl );
		return $result;
	}

	private function post_by_file_get_contents( $opt = null ) {
		return file_get_contents( $this->url, false, stream_context_create( array(
			'http' => array(
				'method' => 'POST',
				'content' => $this->dataString
			)
		) ) );
	}

	function setUrl( $url, $opt = null ) {
		$this->url = $url;
		if ( substr( $this->url, 0, 8 ) == 'https://' ) {
			if ( function_exists( 'curl_init' ) ) $headers = $this->post_by_curl( $opt );
			else $headers = $this->post_by_file_get_contents( $opt );
			$headers = explode( "\n", $headers );
		}
		else
			$headers = @get_headers( $this->url, 1 );

		if ( stristr( $headers[0], '200' ) === false ) {
			$this->url_status = false;
		}
		else {
			$this->url_status = true;
		}
	}

	function setData( array $data ) {
		$this->data = $data;
		$this->dataString = http_build_query( $data );
	}
}
?>