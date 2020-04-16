<?php

class http
{

	public $url;
	public $data;
	public $charset = null;
	public $content = null;

	function http ()
	{
		
	}

	private function get_http ($fsock = null)
	{
		$str = null;

		while ($buff = @fgets($fsock, 2048))
		{
			$str .= $buff;
		}

		fclose($fsock);

		$pos	= strpos($str, "\r\n\r\n");
		$head	= substr($str, 0, $pos);
		$status	= substr($head, 0, strpos($head, "\r\n"));
		$body	= substr($str, $pos + 4, strlen($str) - ($pos + 4));

		if (preg_match('/^HTTP\/\d\.\d\s([\d]+)\s.*$/', $status, $matches))
		{
			if (intval($matches[1]) / 100 == 2) return $this->content = $body;
			else return false;
		} else return false;
	}

	public function get ()
	{
		$url			= parse_url($this->url);
		$url['path']	= empty($url['path']) ? '/' : $url['path'];
		$url['query']	= empty($url['query']) ? '?' : $url['query'];
		$url['port']	= empty($url['port']) ? 80 : $url['port'];
		$host_ip		= @gethostbyname($url['host']);
		$fsock_timeout	= 2;

		if (($fsock = fsockopen($host_ip, $url['port'], $errno, $errstr, $fsock_timeout)) < 0) return false;

		$request	=  $url['path'].'?'.$url['query'];
		$out		= "GET ".$request." HTTP/1.0\r\n";
		$out		.= "Accept: */*\r\n";
		$out		.= "User-Agent: Payb-Agent\r\n";
		$out		.= "Host: ".$url['host']."\r\n";
		$out		.= "Connection: Close\r\n\r\n";

		if (!@fwrite($fsock, $out, strlen($out)))
		{
			fclose($fsock);
			return false;
		}

		return self::get_http($fsock);
	}

	public function post ()
	{
		$url			= parse_url($url);
		$url['path']	= empty($url['path']) ? '/' : $url['path'];
		$url['query']	= empty($url['query']) ? '?' : $url['query'];
		$url['port']	= empty($url['port']) ? 80 : $url['port'];
		$host_ip		= @gethostbyname($url['host']);
		$fsock_timeout	= 2;

		if (($fsock = fsockopen($host_ip, $url['port'], $errno, $errstr, $fsock_timeout)) < 0) return false;

		$request	= $url['path'].'?'.$url['query'];
		$post_data2	= http_build_query($this->data);
		$out		= "POST ".$request." HTTP/1.0\r\n";
		$out		.= "Accept: */*\r\n";
		$out		.= "Host: ".$url['host']."\r\n";
		$out		.= "User-Agent: Lowell-Agent\r\n";
		$out		.= "Content-type: application/x-www-form-urlencoded\r\n";
		$out		.= "Content-Length: ".strlen($post_data2)."\r\n";
		$out		.= "Connection: Close\r\n\r\n";
		$out		.= $post_data2."\r\n\r\n";
		unset($post_data2);
		if (!@fwrite($fsock, $out, strlen($out)))
		{
			fclose($fsock);
			return false;
		}
		return self::get_http($fsock);
	}

	public function result ()
	{
		if (!function_exists('curl_init'))
		{
			return empty($this->data) ? self::get() : self::post();
		}

		$timeout	= $abort ? 1 : 2;
		$ch			= curl_init();
		if (is_array($this->data) && $this->data)
		{
			$formdata	= http_build_query($this->data);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		$result = curl_exec($ch);

		if (false === $result && false == $abort)
		{
			empty($this->data) ? self::get() : self::post();
		}
		else $this->content = $result;

		if (isset($this->charset) && strtolower($this->charset) == 'gb2312') return g2u($this->content);
		else return $this->content;
	}

}

?>