<?php

/**
 * Redis操作类
 * by today at 2017-12-20
 */

class RedisByToday {

	private $host;
	private $port;
	private $auth;
	private $client;

	private $data;

	function __construct( $host, $port, $auth, $dbNumber = 0  ) {
		$this->host = $host;
		$this->port = $port;
		$this->auth = $auth;
        $this->dbNumber = $dbNumber;

		$this->connect();
	}

	function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
        // TODO: Implement __call() method.
    }

    function connect() {
		$this->client = new Redis();
		$this->client->connect( $this->host, $this->port );
		if ( $this->auth ) $this->client->auth( $this->auth );
        if ( $this->dbNumber ) $this->client->select( $this->dbNumber );
	}

    /**
	 * 检测key是否存在
	 */
	function exists( $key ) {
		if ( empty( $key ) ) return false;
		return $this->client->exists( $key );
	}

	/**
	 * 删除key
	 */
	function delete( $key ) {
		if ( empty( $key ) ) return false;
		if ( !$this->exists( $key ) ) return false;
		return $this->client->delete( $key );
	}

	/**
	 * 根据key获取val
	 * 返回的结果支持string和array
	 */
	function get( $key ) {
		if ( empty( $key ) ) return false;
		if ( !$this->exists( $key ) ) return false;
		$val = $this->client->get( $key );
		if ( strlen( $val ) < 2 ) return false;

		$type = substr( $val, 0, 2 );
		switch ( $type ) {
			case 's:':
				$val = substr( $val, 2 );
				break;
			case 'a:':
				$val = json_decode( substr( $val, 2 ), true );
				break;
		}
		if ( preg_match( '/^user\/[0-9]+$/', $key ) ) $val = (array)$val;
		return $val;
	}

	/**
	 * 保存，如果已经存在key，则覆盖
	 * val只支持string和array
	 */
	function set( $key, $val, $expire = 0 ) {
		if ( is_array( $val ) ) $val = 'a:'.json_encode( $val );
		else $val = 's:'.$val;

		if ( $expire > 0 ) return $this->client->setex( $key, $expire, $val );
		else return $this->client->set( $key, $val );
	}

	
	/**
     * 获取锁
     * @param  string  $key    锁标识
     * @param  int     $expire 锁过期时间
     * @return boolean
     */
    public function lock( $key, $expire = 30 ){
        $is_lock = $this->client->setnx( $key, time()+$expire );
        // 不能获取锁
        if( !$is_lock ){
            // 判断锁是否过期
            $lock_time = $this->client->get( $key );
            // 锁已过期，删除锁，重新获取
            if( time() > $lock_time ){
                $this->unlock( $key );
                $is_lock = $this->client->setnx( $key, time()+$expire );
            }
        }

        return $is_lock ? true : false;
    }

    /**
     * 释放锁
     * @param  string  $key 锁标识
     * @return boolean
     */
    public function unlock( $key ){
        // return $this->client->delete( $key );
        return $this->delete( $key );
    }
	
}

?>