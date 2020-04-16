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
     * 币种-包含未开启
     * 从主索引库中获取
     */
    protected $allCoins;

    /**
     *  交易所
     */
    protected $exchanges;

    /**
	 * 钱包
	 * 从主索引库中获取
	 */
	protected $wallets;

	/**
	 * SMTP列表
	 * 从主索引库中获取
	 */
	protected $smtp;

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
	 * 模板引擎
	 */
	protected $smarty;

	/**
	 * 请求处理结果
	 */
	protected $response;

	/**
	 * p2p可选时长
	 */
	protected $p2pDays;

	protected $coinExchanges;

	/**
	 * 输出json数据
	 */
	protected function json( $data = array(), $exit = true ) {
		if ( !isset( $data['msg'] ) ) $data['msg'] = '';
		if ( !isset( $data['data'] ) ) $data['data'] = array();
		if ( $exit ) {
			echo json_encode( $data );
			exit;
		}
		else {
			$this->response = $data;
		}
	}

	/**
	 * success
	 * 当post请求时返回json，当get请求时返回html
	 */
	protected function success( $msg, $data = array() ) {
		if ( is_post() ) $this->json( array( 'status' => 200, 'msg' => $msg, 'data' => $data ) );

		$this->smarty()->assign( 'msg', $msg );
		$this->smarty()->display( 'success.htm' );
		exit;
	}

	/**
	 * error
	 * 当post请求时返回json，当get请求时返回html
	 */
	protected function error( $msg, $data = array() ) {
		if ( is_post() ) $this->json( array( 'status' => 500, 'msg' => $msg, 'data' => $data ) );

		$this->smarty()->assign( 'msg', $msg );
		$this->smarty()->display( 'error.htm' );
		exit;
	}

	/**
	 * return success
	 */
	protected function returnSuccess( $msg, $data = array() ) {
		$this->json( array( 'status' => 200, 'msg' => $msg, 'data' => $data ), false );
	}

	/**
	 * return error
	 */
	protected function returnError( $msg, $data = array() ) {
		$this->json( array( 'status' => 500, 'msg' => $msg, 'data' => $data ), false );
	}

	/**
	 * 保存日志到文件中
	 */
	protected function logFile( $data, $level = '' ) {
		if ( is_array( $data ) ) {
			if ( !isset( $data['ip'] ) ) $data['ip'] = ip();
			if ( !isset( $data['get_url'] ) ) $data['get_url'] = $_SERVER['REQUEST_URI'];
		}
		else {
			$data = array(
				'data' => $data,
				'ip' => ip(),
				'get_url' => $_SERVER['REQUEST_URI'],
			);
		}
		return file_put_contents( 'logs/app/'.date( 'Ymd' ).( $level ? '.'.$level : '' ).'.log', date( 'Y-m-d H:i:s' ).' '.json_encode( $data ).PHP_EOL, FILE_APPEND );
	}

	/**
	 * 从数组中获取需要的key和对应的value
	 */
	protected function getKeyValue( $data, $keyArr ) {
		if ( empty( $keyArr ) ) return array();
		$tmp = array();
		foreach ( $keyArr as $key => $ka ) {
			if ( is_numeric( $key ) ) {
				if ( isset( $data[$ka] ) ) $tmp[$ka] = $data[$ka];
			}
			else {
				if ( isset( $data[$key] ) ) $tmp[$ka] = $data[$key];
			}
		}
		return $tmp;
	}

	/**
	 * 获取redis对象
	 */
	protected function redis( $key = '' ) {
		if ( empty( $key ) ) $key = 'index';
		if ( !$this->redis[$key] ) {
			require_once 'core/v2.1/redis.php';
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
			require_once 'core/v2.1/pdo.php';
			$this->db[$conn][$type] = new PdoByToday( $setting['host'], $setting['port'], $setting['name'], $setting['pwd'], $setting['db'] );
		}

		if ( !isset($this->mdl[$conn][$type][$tb]) ) {
			//如果没有实例化过对此表的操作对象，则先实例化
			require_once 'core/model/pdo.php';
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
		$configs = $this->redis()->get( 'configs' );
		if ( !$configs ) {
			//从数据库中生成
			$mdl_config = $this->db( 'index', 'config_data' );
			$data = $mdl_config->getList();
			$configs = array();
			foreach ( $data as $item ) {
				$configs[$item['key']] = $item['val'];
			}
			$this->redis()->set( 'configs', $configs, 5 * 60 );
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
				unset( $coins[$item['name']]['blockNumber'] );
				unset( $coins[$item['name']]['userCount'] );
				unset( $coins[$item['name']]['balance'] );
				unset( $coins[$item['name']]['contractBalance'] );
				unset( $coins[$item['name']]['currentBlockNumber'] );
				unset( $coins[$item['name']]['freeAddrCount'] );
			}
			$this->redis()->set( 'coins', $coins, 5 * 60 );
		}
		$this->coins = $coins;
		return $this->coins;
	}

    /**
     * 获取币种
     */
    protected function getAllCoins() {
        if ( $this->allCoins ) return $this->allCoins;

        //先从redis中查找，过期时间为5分钟
        $coins = $this->redis()->get( 'coins_all' );

        if ( !$coins ) {
            //从数据库中生成
            $mdl_coin = $this->db( 'index', 'coin' );
            $data = $mdl_coin->getList();
            $coins = array();
            foreach ( $data as $item ) {
                $coins[$item['name']] = $item;
                unset( $coins[$item['name']]['blockNumber'] );
                unset( $coins[$item['name']]['userCount'] );
                unset( $coins[$item['name']]['balance'] );
                unset( $coins[$item['name']]['contractBalance'] );
                unset( $coins[$item['name']]['currentBlockNumber'] );
                unset( $coins[$item['name']]['freeAddrCount'] );
            }
            $this->redis()->set( 'all_coins', $coins, 5 * 60 );
        }

        $this->allCoins = $coins;

        return $this->allCoins;
    }

	/**
	 * 获取指定币种
	 */
	protected function getCoin( $name, $all = false ) {
		if ($all){
		    $coins = $this->getAllCoins();
        }else{
            $coins = $this->getCoins();
        }
		return $coins[$name];
	}

	protected function getExchange($exchangeId){
        $exchangeList =  $this->redis()->get( 'market_exchange_vol_all');
        return $exchangeList[$exchangeId];
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
	 * 获取smtp列表
	 */
	protected function getSmtp() {
		if ( $this->smtp ) return $this->smtp;

		//先从redis中查找，过期时间为5分钟
		$smtp = $this->redis()->get( 'smtp' );
		if ( !$smtp ) {
			//从数据库中生成
			$mdl_smtp = $this->db( 'index', 'smtp' );
			$data = $mdl_smtp->getList( null, array( 'status' => 1 ) );
			$smtp = array();
			foreach ( $data as $item ) {
				$smtp[] = $item;
			}
			$this->redis()->set( 'smtp', $smtp, 5 * 60 );
		}
		$this->smtp = $smtp;
		return $this->smtp;
	}

	/**
	 * 获取IP详情
	 */
	protected function getIpDetail( $ip ) {
		$detail = $this->request( 'http://freegeoip.net/json/'.$ip );
		$detail = json_decode( $detail, true );
		return array( 'country' => $detail['country_name'], 'city' => $detail['city'] );
	}

	protected function md5( $str ) {
		return md5( $GLOBALS['KEY_'].$str.$GLOBALS['_KEY'] );
	}

	public function __construct() {
		unset( $_GET['con'] );
		unset( $_GET['ctl'] );
		unset( $_GET['act'] );
	}

	/**
	 * 发送短信
	 */
	protected function sendsms( $mobile, $content ) {
		$mobile = trim( $mobile );
		$content = trim( $content );
		if ( empty( $mobile ) || empty( $content ) ) return false;

		if( substr( $mobile , 0 , 2 ) == "86" )
			$content = '【】'.$content;
		else
			$content = '[]'.$content;
		$url = '';
		$data = array(
			'username' => '',
			'password_md5' =>'',
			'apikey' => '',
			'mobile' => $mobile,  //多个号码以,隔开
			'content' => urlencode( $content ),
			'encode' => 'UTF-8',
		);

		$result = $this->request( $url, $data );
		//echo $result;exit;
		$check = explode( ':', $result );
		return $check[0] == 'success' ? true : $result;
	}

	/**
	 * 根据CSV生成国家列表
	 * 只在短信接口方更新了国家列表之后调用，其他时候禁用此方法
	 */
	protected function getSmsCountryCode() {
		exit;
		require_once 'core/v2.1/csv.php';
		$file = 'core/country_mobile_area_code.csv';

		$list = CSV::getAllLine( $file );
		$data = '<?php'.PHP_EOL;
		$data .= '$smsCountryCode = array();'.PHP_EOL;
		foreach ( $list as $item ) {
			$data .= '$smsCountryCode[\''.$item[4].'\'] = array( \'name\' => \''.str_replace( '\'', '\\\'', $item[2] ).'\', \'en\' => \''.str_replace( '\'', '\\\'', $item[1] ).'\', \'short\' => \''.$item[3].'\' );'.PHP_EOL;
		}
		$data .= '?>';
		file_put_contents( 'core/country_mobile_area_code.php', $data );
	}
	
	/**
	 * 发送邮件
	 */
	protected function sendmail( $email, $subject, $content ) {
		if ( empty( $email ) || empty( $subject ) || empty( $content ) )
			return false;

		if(substr($email,-7, strlen($email)) === '163.com' || substr($email,-7, strlen($email)) === '126.com' )
		{
			$mdl_smtp = $this->db( 'index', 'smtp' );
			$smtp = $mdl_smtp->getByWhere( array( 'name' => '163' ) );

			if ( !$smtp ) {
				$list = $this->getSmtp();
				if ( !$list ) return false;

				$smtp = $list[time() % count( $list )];
				if ( !$smtp ) return false;
			}
			return smtp_mail( $email, $subject, $content, $smtp['host'], $smtp['port'], authcode( $smtp['username'], 'd', $smtp['salt'] ), authcode( $smtp['password'], 'd', $smtp['salt'] ), $smtp['senderEmail'], $smtp['senderName'] );
		}
        elseif(substr($email,-6, strlen($email)) === 'QQ.com' || substr($email,-6, strlen($email)) === 'qq.com' || substr($email,-7, strlen($email)) === '139.com')
		{
			$mdl_smtp = $this->db( 'index', 'smtp' );
			$smtp = $mdl_smtp->getByWhere( array( 'name' => 'QQ' ) );

			if ( !$smtp ) {
				$list = $this->getSmtp();
				if ( !$list ) return false;

				$smtp = $list[time() % count( $list )];
				if ( !$smtp ) return false;
			}
			return smtp_mail( $email, $subject, $content, $smtp['host'], $smtp['port'], authcode( $smtp['username'], 'd', $smtp['salt'] ), authcode( $smtp['password'], 'd', $smtp['salt'] ), $smtp['senderEmail'], $smtp['senderName'] );
		}
		else
		{
			$list = $this->getSmtp();
			if ( !$list ) return false;

			$smtp = $list[time() % count( $list )];
			if ( !$smtp ) return false;
			return smtp_mail( $email, $subject, $content, $smtp['host'], $smtp['port'], authcode( $smtp['username'], 'd', $smtp['salt'] ), authcode( $smtp['password'], 'd', $smtp['salt'] ), $smtp['senderEmail'], $smtp['senderName'] );
		}
	}

    /**
     * 加入消息队列
     */
    protected function addQueue( $queue = array() ) {
        if ( !$queue ) return false;

        if ( $queue['type'] == 'mail' || $queue['type'] == 'sms' ) {
            $queueName = $queue['type'].'_send_queue';
            return $this->redis()->rPush($queueName, json_encode($queue));
        }

        /*
        if ( $queue['type'] == 'mail' ) {
            return $this->sendmail( $queue['receive'], $queue['subject'], $queue['body'] );
        }

        if ( $queue['type'] == 'sms' ) {
            return $this->sendsms( $queue['receive'], $queue['body'] );
        }

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

        */
    }

    /**
     * 消息推送
     * @param $userId
     * @param string $title
     * @param string $content
     * @param array $extra
     * @return bool
     */
    protected function messagePush($userId, $title, $content = '', $extra = []){

        $result = false;
        $lang = $this->redis()->get( 'lang/' . $userId );
        $title = empty($title) ?  ___('钱包', $lang) : $title;
        
        if (!empty($userId)){
            $queueName = 'message_push_queue';
            $queueData = [
                'user_id' => $userId,
                'message' => [
                    'title' => $title,
                    'content' => $content,
                    'extras' => $extra
                ],
            ];

            $result = $this->redis()->rPush($queueName, json_encode($queueData));
        }

        return $result;
    }

	protected function request( $url, $data = array() ) {
		//$data是字符串，则application/x-www-form-urlencoded
		//$data是数组，则multipart/form-data

		//$headers = array();
		//$headers[] = "Content-type: text/xml; charset=utf-8";

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		if ( $headers ) curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );

		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
		$output = curl_exec( $curl );
		$errno = curl_errno( $curl );

		if ( $errno ) {
			$output = array( 'errno' => $errno, 'error' => curl_error( $curl ) );
			$output['detail'] = curl_getinfo( $curl );
		}
		curl_close( $curl );
		return $output;
	}


	protected function smarty() {
		if ( !$this->smarty ) {
			$this->smarty = new Smarty();
			$this->smarty->config_dir = $GLOBALS['TPL_SM_CONFIG_DIR'];
			$this->smarty->caching = $GLOBALS['TPL_SM_CACHEING'];
			$this->smarty->template_dir = $GLOBALS['TPL_SM_TEMPLATE_DIR'];
			$this->smarty->compile_dir = $GLOBALS['TPL_SM_COMPILE_DIR'];
			$this->smarty->cache_dir = $GLOBALS['TPL_SM_CACHE_DIR'];
			$this->smarty->left_delimiter = $GLOBALS['TPL_SM_DELIMITER_LEFT'];
			$this->smarty->right_delimiter = $GLOBALS['TPL_SM_DELIMITER_RIGHT'];
			$this->smarty->force_compile = false; 
		}
		return $this->smarty;
	}

	protected function rnd( $len = 6 ) {
		$rnd = '';
		while ( strlen( $rnd ) < $len ) {
			$rnd .= mt_rand();
		}
		$rnd = substr( $rnd, 0, $len );

		return $rnd;
	}

	protected function rndStr( $len = 10, $symbol = true ) {
		$randStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ( $symbol ) $randStr .= '!@#$%^&*()<>?_+';
		$rnd = '';
		while ( strlen( $rnd ) < $len ) {
			$rnd .= $randStr[mt_rand( 0, strlen( $randStr ) - 1 )];
		}
		$rnd = substr( $rnd, 0, $len );

		return $rnd;
	}


	/**
	 * 隐藏电话号码
	 */
	protected function hidePhone( $phone ) {
		if ( empty( $phone ) ) return '';
		if ( strlen( $phone ) == 11 ) {
			return substr( $phone, 0, 3 ).'****'.substr( $phone, strlen( $phone ) - 4 );
		}
		else {
			return substr( $phone, 0, 3 ).'****'.substr( $phone, strlen( $phone ) - 2 );
		}
	}

	/**
	* 取汉字的第一个字的首字母
	* @param type $str
	* @return string|null
	*/
	protected function getFirstChar($s){
		$s0 = mb_substr($s,0,3);
		$s = iconv('UTF-8','gb2312', $s0);
		if (ord($s0)>128) {
			$asc=ord($s{0})*256+ord($s{1})-65536;
			if($asc>=-20319 and $asc<=-20284)return "A";
			if($asc>=-20283 and $asc<=-19776)return "B";
			if($asc>=-19775 and $asc<=-19219)return "C";
			if($asc>=-19218 and $asc<=-18711)return "D";
			if($asc>=-18710 and $asc<=-18527)return "E";
			if($asc>=-18526 and $asc<=-18240)return "F";
			if($asc>=-18239 and $asc<=-17760)return "G";
			if($asc>=-17759 and $asc<=-17248)return "H";
			if($asc>=-17247 and $asc<=-17418)return "I";
			if($asc>=-17417 and $asc<=-16475)return "J";
			if($asc>=-16474 and $asc<=-16213)return "K";
			if($asc>=-16212 and $asc<=-15641)return "L";
			if($asc>=-15640 and $asc<=-15166)return "M";
			if($asc>=-15165 and $asc<=-14923)return "N";
			if($asc>=-14922 and $asc<=-14915)return "O";
			if($asc>=-14914 and $asc<=-14631)return "P";
			if($asc>=-14630 and $asc<=-14150)return "Q";
			if($asc>=-14149 and $asc<=-14091)return "R";
			if($asc>=-14090 and $asc<=-13319)return "S";
			if($asc>=-13318 and $asc<=-12839)return "T";
			if($asc>=-12838 and $asc<=-12557)return "W";
			if($asc>=-12556 and $asc<=-11848)return "X";
			if($asc>=-11847 and $asc<=-11056)return "Y";
			if($asc>=-11055 and $asc<=-10247)return "Z";
		}else if(ord($s)>=48 and ord($s)<=57){
			switch(iconv_substr($s,0,1,'utf-8')){
				case 1:return "#";
				case 2:return "#";
				case 3:return "#";
				case 4:return "#";
				case 5:return "#";
				case 6:return "#";
				case 7:return "#";
				case 8:return "#";
				case 9:return "#";
				case 0:return "#";
			}
		}else if(ord($s)>=65 and ord($s)<=90){
			return substr($s,0,1);
		}else if(ord($s)>=97 and ord($s)<=122){
			return strtoupper(substr($s,0,1));
		}
		else
		{
			return iconv_substr($s0,0,1,'utf-8');
		}
	}


	/**
     * 将数组转成xml对象
     */
    protected function array2Xml($arrayObj, $xmlDoc = null, $ele = null, $rootName = '')
    {
        if (!isset($xmlDoc)) {
            $xmlDoc = new DOMDocument();
            $xmlDoc->formatOutput = true;
        }
        if (!isset($ele)) {
            $ele = $xmlDoc->createElement($rootName);
            $xmlDoc->appendChild($ele);
        }

        foreach ($arrayObj as $key => $val) {
            /*if ( !is_string( $key ) && is_array( $val ) ) {
                $this->array2Xml( $val, $xmlDoc, $ele );
                continue;
            }*/

            $elex = $xmlDoc->createElement(is_string($key) ? $key : substr($rootName, 0, strlen($rootName) - 1));
            $ele->appendChild($elex);
            if (is_array($val)) {
                $this->array2Xml($val, $xmlDoc, $elex, $key);
            } else {
                $elexText = $xmlDoc->createCDATASection($val);
                $elex->appendChild($elexText);
            }
        }
        return $xmlDoc;
    }

    /**
     * 将xml对象转成数组
     */
    protected function xml2Array($xmlObj)
    {
        $result = array();
        $array = $xmlObj;
        if (get_class($array) == 'SimpleXMLElement') {
            $array = get_object_vars($xmlObj);
        }
        if (is_array($array)) {
            if (count($array) <= 0) {
                return trim(strval($xmlObj));
            }
            foreach ($array as $key => $val) {
                $result[$key] = $this->xml2Array($val);
            }
            return $result;
        } else {
            return trim(strval($array));
        }
    }

    /**
     * @param $currency
     * @param string $mainCurrency
     * @return int
     */
    protected function getRate($currency, $mainCurrency = 'usd')
    {
        $currency = strtolower( $currency );

        if ($currency == 'rmb'){
            $currency = 'cny';
        }

        if ($currency == $mainCurrency){
            return 1;
        }else{
            $key = $mainCurrency."_" . $currency;
            $formatData = $this->redis()->get('currency_rate');

            return isset($formatData[$key]) ? $formatData[$key] : 0;
        }
    }

    protected function initPhone( &$area, &$phone ) {
    	if ( $area == '61' || $area == '44' ) {
    		if ( substr( $phone, 0, 1 ) == '0' ) $phone = substr( $phone, 1 );
    	}
    }

    //获取所有的链
    protected function getChainList() {

        $cacheName = 'chan_list_cache';
        $cache = $this->redis()->get($cacheName);

        if ($cache) {
            return $cache;
        }

        $chainList = $this->db('index', 'chain')->getList();

        if ($chainList) {
            $chainList = array_column($chainList, 'name', 'chainid');
            $this->redis()->set($cacheName, $chainList, 5 * 60);
        } else {
            $chainList = [];
        }

        return $chainList;

    }

    /*图片路径转换成OssImg完整图片路径*/
    public function imgToOssImgUrl($img) {

        if(stripos($img, 'http') === false){
            $img = $this->getConfig('oss_url').$img;
        }

        return $img;

    }

}
