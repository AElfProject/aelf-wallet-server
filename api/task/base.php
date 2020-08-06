<?php

/**
 * @Author Today Nie
 * @Date 2017-12-20
 */

class base {

	/**
	 * 默认加密key
	 */
	protected $defaultEncodeKey = 'Aa2goJbqeWm%$fBRP';

	/**
	 * redis
	 */
	protected $redis = array();

	/**
	 * 缓存db连接
	 */
	protected $db = array(
		'index' => null,  //主索引库
	);

	/**
	 * 缓存db操作对象
	 */
	protected $mdl;

	/**
	 * 配置参数
	 * 从主索引库中获取
	 */
	protected $configs;

	/**
	 * 数据库索引
	 * 从主索引库中获取
	 */
	protected $databases;

	/**
	 * 币种
	 * 从主索引库中获取
	 */
	protected $coins;

	/**
	 * 钱包
	 * 从主索引库中获取
	 */
	protected $wallets;

	/**
	 * 站点基本信息
	 * 从主索引库中获取
	 */
	protected $sites;

	/**
	 * 会员分组
	 */
	protected $groups;

	/**
	 * SMTP列表
	 * 从主索引库中获取
	 */
	protected $smtp;

	/**
	 * 保存日志到文件中
	 */
	protected function logFile( $data, $level = '' ) {
		return file_put_contents( 'logs/queue/'.date( 'Ymd' ).( $level ? '.'.$level : '' ).'.log', date( 'Y-m-d H:i:s' ).' '.json_encode( $data ).PHP_EOL, FILE_APPEND );
	}

	/**
	 * 获取redis对象
	 */
	protected function redis( $key = '' ) {
		if ( empty( $key ) ) $key = 'index';
		if ( !$this->redis[$key] ) {
			require_once 'redis.php';
			$setting = $GLOBALS['REDIS_LIST'][$key];
			if ( !$setting ) {
				echo 'Connect redis faild.';
				exit;
			}
			$this->redis[$key] = new RedisByToday( $setting['host'], $setting['port'], $setting['auth'], $setting['dbNumber'] );
		}
		return $this->redis[$key];
	}

	/**
	 * 获取db操作对象
	 *
	 * 默认从slave库中读取
	 */
	protected function db( $conn, $tb, $type = 'slave' ) {
		if ( empty( $tb ) ) return false;
		if ( empty( $conn ) ) return false;
		if ( $type != 'master' && $type != 'slave' ) $type = 'slave';


		$setting = $GLOBALS['DB_LIST'][$conn];
		if ( !$setting ) {
			echo 'Connect database faild.';
			exit;
		}

		if ( $type == 'slave' && !empty( $setting['slaves'] ) ) {
			//根据当前时间戳%从库数量，随机读取
			$setting = $setting['slaves'][( time() % count( $setting['slaves'] ) )];
		}
		else $type = 'master';

        if ( !isset($this->db[$conn][$type]) ) {
            //如果没有连接，先建立连接
            require_once 'pdo.php';
            $this->db[$conn][$type] = new PdoByToday( $setting['host'], $setting['port'], $setting['name'], $setting['pwd'], $setting['db'] );
        }else{
            /** @var  $dbConnect  PdoByToday */
            $dbConnect = $this->db[$conn][$type];

            if (!$dbConnect->pdo_ping()){
                echo PHP_EOL.date('Y-m-d H:i:s').': db 连接已超时，重新建立连接'.PHP_EOL;
                $this->db[$conn][$type] = new PdoByToday( $setting['host'], $setting['port'], $setting['name'], $setting['pwd'], $setting['db'] );
                //require_once 'mdl.pdo.php';
                $this->mdl[$conn][$type] = [];
                //$this->mdl[$conn][$type][$tb] = new mdl_pdo( $this->db[$conn][$type], $tb );
            }
        }

		if ( !$this->mdl[$conn][$type][$tb] ) {
			//如果没有实例化过对此表的操作对象，则先实例化
			require_once 'mdl.pdo.php';
			$this->mdl[$conn][$type][$tb] = new mdl_pdo( $this->db[$conn][$type], $tb );
		}

		return $this->mdl[$conn][$type][$tb];
	}

	/**
	 * 获取所有配置参数
	 */
	protected function getConfigs() {
		if ( $this->configs ) return $this->configs;

		//先从redis中查找，过期时间为5分钟
		$configs = $this->redis()->get( 'elf:configs' );
		if ( !$configs ) {
			//从数据库中生成
			$mdl_config = $this->db( 'index', 'config_data' );
			$data = $mdl_config->getList();
			$configs = array();
			foreach ( $data as $item ) {
				$configs[$item['key']] = $item['val'];
			}
			$this->redis()->set( 'elf:configs', $configs, 5 * 60 );
		}
		$this->configs = $configs;
		return $this->configs;
	}

	/**
	 * 获取指定配置参数
	 */
	protected function getConfig( $key ) {
		if ( empty( $key ) ) return false;

		$configs = $this->getConfigs();
		return $configs[$key];
	}

	/**
	 * 获取数据库索引
	 */
	protected function getDatabases() {
		if ( $this->databases ) return $this->databases;

		//先从redis中查找，过期时间为5分钟
		$databases = $this->redis()->get( 'databases' );
		if ( !$databases ) {
			//从数据库中生成
			$mdl_database = $this->db( 'index', 'database' );
			$data = $mdl_database->getList();
			$databases = array();
			foreach ( $data as $item ) {
				$databases[$item['id']] = $item;
			}
			$this->redis()->set( 'databases', $databases, 5 * 60 );
		}
		$this->databases = $databases;
		return $this->databases;
	}

	/**
	 * 获取指定数据库索引
	 */
	protected function getDatabase( $id ) {
		$databases = $this->getDatabases();
		return $databases[$id];
	}

	/**
	 * 获取币种
	 */
	protected function getCoins() {
		if ( $this->coins ) return $this->coins;

		//先从redis中查找，过期时间为5分钟
		$coins = $this->redis()->get( 'coins' );
		if ( !$coins ) {
			//从数据库中生成
			$mdl_coin = $this->db( 'index', 'coin' );
			$data = $mdl_coin->getList( null, array( 'status' => 1 ), 'sortnum asc, id asc' );
			$coins = array();
			foreach ( $data as $item ) {
				$coins[$item['name']] = $item;
			}
			$this->redis()->set( 'coins', $coins, 5 * 60 );
		}
		$this->coins = $coins;
		return $this->coins;
	}

	/**
	 * 获取指定币种
	 */
	protected function getCoin( $name ) {
		$coins = $this->getCoins();
		return $coins[$name];
	}

	/**
	 * 获取币种
	 */
	protected function getWallets() {
		if ( $this->wallets ) return $this->wallets;

		//先从redis中查找，过期时间为5分钟
		$wallets = $this->redis()->get( 'wallets' );
		if ( !$wallets ) {
			//从数据库中生成
			$mdl_wallet = $this->db( 'index', 'wallet' );
			$data = $mdl_wallet->getList( null, null, 'userCount asc' );
			$wallets = array();
			foreach ( $data as $item ) {
				$wallets[$item['id']] = $item;
			}
			$this->redis()->set( 'wallets', $wallets, 5 * 60 );
		}
		$this->wallets = $wallets;
		return $this->wallets;
	}

	/**
	 * 获取指定币种
	 *
	 * @param coin 如果为数字则取出指定ID的钱包，如果为字符串则取出相应币种的所有钱包列表
	 */
	protected function getWallet( $coin, $where = array() ) {
		$wallets = $this->getWallets();

		if ( is_numeric( $coin ) ) return array( $wallets[$coin]['id'] => $wallets[$coin] );

		$list = array();
		foreach ( $wallets as $item ) {
			if ( $item['coin'] == $coin ) {
				if ( $where ) {
					$ok = true;
					foreach ( $where as $kw => $ki ) {
						if ( $item[$kw] != $ki ) {
							$ok = false;
							break;
						}
					}
					if ( $ok ) $list[$item['id']] = $item;
				}
				else {
					$list[$item['id']] = $item;
				}
				$list[$item['id']]['coinDetail'] = $this->getCoin( $coin );
			}
		}
		return $list;
	}

	/**
	 * 获取站点列表
	 */
	protected function getSites() {
		if ( $this->sites ) return $this->sites;

		//先从redis中查找
		$sites = $this->redis()->get( 'sites' );
		if ( !$sites ) {
			//从数据库中生成
			$mdl_site = $this->db( 'index', 'sites' );
			$data = $mdl_site->getList( array( 'lang', 'name', 'pageTitle', 'keywords', 'description' ) );
			$sites = array();
			foreach ( $data as $item ) {
				$sites[$item['lang']] = $item;
			}
			$this->redis()->set( 'sites', $sites );
		}
		$this->sites = $sites;
		return $this->sites;
	}

	/**
	 * 获取指定站点
	 */
	protected function getSite( $lang ) {
		$sites = $this->getSites();
		return $sites[$lang];
	}

	/**
	 * 获取会员分组列表
	 */
	protected function getGroups() {
		if ( $this->groups ) return $this->groups;

		//先从redis中查找
		$groups = $this->redis()->get( 'groups' );
		if ( !$groups ) {
			//从数据库中生成
			$mdl_member_group = $this->db( 'index', 'member_group' );
			$mdl_member_group_coin = $this->db( 'index', 'member_group_coin' );
			$data = $mdl_member_group->getList();
			$dataCoins = $mdl_member_group_coin->getList( null, array( 'status' => 1 ) );
			$groups = array();
			foreach ( $data as $item ) {
				$groups[$item['id']] = $item;
				foreach ( $dataCoins as $dc ) {
					if ( $dc['groupId'] == $item['id'] ) {
						$groups[$item['id']]['coins'][$dc['coin']] = $dc;
					}
				}
			}
			$this->redis()->set( 'groups', $groups, 10 * 60 );
		}
		$this->groups = $groups;
		return $this->groups;
	}

	/**
	 * 获取指定会员分组
	 */
	protected function getGroup( $id ) {
		$groups = $this->getGroups();
		return $groups[$id];
	}

	/**
	 * 获取语言列表
	 */
	protected function getLangs() {
		$tmp = unserialize( LANGS );
		$langs = array();
		foreach ( $tmp as $t ) {
			$langs[$t['id']] = $t;
		}
		return $langs;
	}

	/**
	 * 获取当前语言
	 */
	protected function getLang() {
		global $gbl_con, $admin_lang, $lang;

		if ( $gbl_con == 'admin' ) {
			$lang = isset( $admin_lang ) ? $admin_lang : $_COOKIE['admin_lang'];
		}
		else {
			$lang = isset( $lang ) ? $lang : $_COOKIE['lang'];
		}

		$langs = $this->getLangs();
		if ( !isset( $langs[$lang] ) ) {
			reset( $langs );
			$lang = key( $langs );
		}
		return $lang;
	}

	/**
	 * 加入消息队列
	 */
	protected function addQueue( $queue = array() ) {
		if ( !$queue ) return false;

		if ( $queue['user'] ) {
			$db = $this->getDatabase( $queue['user']['db'] );
			$queue['userId'] = $queue['user']['id'];
			unset( $queue['user'] );
			$mdl_queue = $this->db( $db['alias'], 'message_queue', 'master' );
		}
		else {
			$mdl_queue = $this->db( 'index', 'message_queue', 'master' );
		}

		return $mdl_queue->insert( $queue );
	}

	protected function rnd( $len = 6 ) {
		$rnd = '';
		while ( strlen( $rnd ) < $len ) {
			$rnd .= mt_rand();
		}
		$rnd = substr( $rnd, 0, $len );

		return $rnd;
	}

	protected function rndStr( $len = 10 ) {
		$randStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()<>?_+';
		$rnd = '';
		while ( strlen( $rnd ) < $len ) {
			$rnd .= $randStr[mt_rand( 0, strlen( $randStr ) - 1 )];
		}
		$rnd = substr( $rnd, 0, $len );

		return $rnd;
	}

	protected function request( $url, $data = array(), $type = '', $json=0 ) {
		//$data是字符串，则application/x-www-form-urlencoded
		//$data是数组，则multipart/form-data

		$headers = array();
		//$headers[] = "Content-type: text/xml; charset=utf-8";

        if ($this->is_json($data) || $json) {
            $headers = [
                'Content-Type:application/json; v=1.0',
                //'Accept: application/json'
                'accept:text/plain; v=1.0'
            ];
        }

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		if ( $headers ) curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 60); //超时时间


        if($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
		$output = curl_exec( $curl );
		$errno = curl_errno( $curl );
		if ( $errno ) {
			$output = array( 'errno' => $errno, 'error' => curl_error( $curl ) );
			$output['detail'] = curl_getinfo( $curl );
		}
		curl_close( $curl );
		return $output;
	}

	function request_by_curl($remote_server, $post_string) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $remote_server);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

    protected function msgToDingTalk( $msg ) {
        $url = 'https://oapi.dingtalk.com/robot/send?access_token=access_token';
        $data = array( 'msgtype' => 'text', 'text' => array( 'content' => $msg ) );
        $data_string = json_encode( $data );
        $this->request_by_curl( $url, $data_string );
    }

    //判断是否是json格式数据
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


}

function authcode( $string, $operation = 'd', $key = '', $expiry = 0 ) {
	if ( empty( $key ) ) return $string;
	
	$ckey_length = 4;

	//密匙
	$key = md5( $key );

	$keya = md5( substr( $key, 0, 16 ) );
	$keyb = md5( substr( $key, 16, 16 ) );
	$keyc = $ckey_length ? ( $operation == 'd' ? substr( $string, 0, $ckey_length ): substr( md5( microtime() ), -$ckey_length ) ) : '';
	$cryptkey = $keya.md5( $keya.$keyc );
	$key_length = strlen( $cryptkey );
	$string = $operation == 'd' ? base64_decode( substr( $string, $ckey_length ) ) : sprintf( '%010d', $expiry ? $expiry + time() : 0 ).substr( md5( $string.$keyb ), 0, 16 ).$string;
	$string_length = strlen( $string );
	$result = '';
	$box = range( 0, 255 );
	$rndkey = array();
	for ( $i = 0; $i <= 255; $i++ ) {
		$rndkey[$i] = ord( $cryptkey[$i % $key_length] );
	}
	for ( $j = $i = 0; $i < 256; $i++ ) {
		$j = ( $j + $box[$i] + $rndkey[$i] ) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for ( $a = $j = $i = 0; $i < $string_length; $i++ ) {
		$a = ( $a + 1 ) % 256;
		$j = ( $j + $box[$a] ) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr( ord( $string[$i] ) ^ ( $box[( $box[$a] + $box[$j] ) % 256] ) );
	}
	if ( $operation == 'd' ) {
		if ( ( substr( $result, 0, 10 ) == 0 || substr( $result, 0, 10 ) - time() > 0 ) && substr( $result, 10, 16 ) == substr( md5( substr( $result, 26 ).$keyb ), 0, 16 ) ) {
			return substr( $result, 26 );
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace( '=', '', base64_encode( $result ) );
	}
}

function ___( $key, $lang = '' ) {
	if ( empty( $key ) ) return false;

	$args = array();
	foreach ( func_get_args() as $i => $arg ) {
		if ( $i > 1 ) $args[] = $arg;
	}

	require_once 'core/csv.php';

	if ( empty( $lang ) ) $lang = 'zh-cn';

	if ( $lang == 'zh-cn' ) return vsprintf( $key, $args );

	$file = __DIR__.'/lang/'.$lang.'.csv';
	$line = CSV::getByKey( $key, $file );

	if ( !$line ) return vsprintf( $key, $args );
	return vsprintf( $line[1], $args );
}
