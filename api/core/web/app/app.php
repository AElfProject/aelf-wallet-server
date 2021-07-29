<?php

require_once 'core/web/web.php';

use Elliptic\EC\Signature;

class app extends web {

    const completed = 6;
    const REDISENV = 1; //=0无效=1启用

	public function before() {
		$this->debug['startTime'] = microtime();

        // 公关参数改为header 获取，不加密
        foreach ($this->basePostData as $key => $data) {
            $serverKey = 'HTTP_' . strtoupper($key);
            $this->basePostData[$key] = isset($_SERVER[$serverKey]) ? trim($_SERVER[$serverKey]) : '';
        }
		if(!$_POST) {
            $_POST = json_decode(file_get_contents("php://input"),true);
        }
        if($this->checkIsDecode($_POST)) {
            foreach ( $_POST as $key => $post ) {
                $_POST[$key] = strip_tags($this->rsaDecodeFix($post));
            }

            if(!strstr($_SERVER["REQUEST_URI"],"app/elf/chain") && !strstr($_SERVER["REQUEST_URI"],"app/settings/get_currencies")){
                if(!$this->verifySignature($_POST["address"],$_POST["public_key"],$_POST["signature"]))
                {
                    $this->error( __( 'Authentication failed' ) );
                }
            }
        }
        $this->logFile($_POST, 'post');

        $chainid = post('chainid');
        if($chainid && strtolower($chainid) == "aelf"){
            $this->chain = strtoupper($chainid);
        }elseif($chainid){
            $this->chain = $chainid;
        }else{
            $this->chain = $this->default_chain;
        }

        // 兼容老版本，post方式获取
        if(!$this->basePostData['device']) $this->basePostData['device']=trim(post('device'));
        if(!$this->basePostData['version']) $this->basePostData['version']=trim(post('version'));
        if(!$this->basePostData['udid']) $this->basePostData['udid']=trim(post('udid'));
        $device =  $this->basePostData['device'];
        $version = $this->basePostData['version'];
        $udid = $this->basePostData['udid'];

        if ( $this->getConfig( 'close' ) ) {
			$white_udid = explode( ',', $this->getConfig( 'white_udid' ) );
			if( !in_array( trim( post( 'udid' ) ), $white_udid ) )
				$this->error( __( '系统升级中，请稍后使用' ) );
		}

		$black_udid = explode( ',', $this->getConfig( 'black_udid' ) );
		if ( in_array( $udid, $black_udid ) ) {
			$this->error( __( '您已被锁定，请联系管理员，谢谢' ) );
		}

		if ( !$device ) $this->error( __( '参数错误' ) );
		if ( !$udid ) $this->error( __( '参数错误' ) );
		if ( !$version ) $this->error( __( '参数错误' ) );

		if ( $device != 'iOS' && $device != 'Android' ) $this->error( __( '参数错误' ) );
	}

    /**
     * 是否需要解密
     * @param array $post
     * @return bool
     */
	private function checkIsDecode(array $post){
        //$uniqueCode = $this->getConfig('unique_code');
        $accessIp = explode(',', $this->getConfig('access_ip'));
        if(($post['demon'] ==1 || $post['test'] ==1) && in_array(ip(), $accessIp) !== false){
            return false;
        }
	    return true;
    }

    private function verifySignature($address, $pubkey, $signature){
        $pubkey = str_replace('\"','"',$pubkey);
        $signature = str_replace('\"','"',$signature);
        $ec = new \Elliptic\EC('secp256k1');
        $msg = hash('sha256',$address);
        $key = $ec->keyFromPublic(json_decode($pubkey));
        $sig = new Signature(json_decode($signature,true));
        $result = $ec->verify($msg, $sig, $key->getPublic());
        print_r($result);
        return $result;
    }

	public function after() {
		$this->debug['endTime'] = microtime();

		if ( DEBUG ) {
			$this->response['debug'] = $this->debug;
		}

		echo json_encode( $this->response );
	}

	/**
	 * 输出json数据
	 */
	protected function json( $data = array(), $exit = true ) {
		if ( !isset( $data['msg'] ) ) $data['msg'] = '';
		if ( !isset( $data['data'] ) ) $data['data'] = array();

		//记录log日志
		if($data['status'] != 200){
            $this->logFile(['data'=>$data,'post'=>$_POST], 'error');
        }

        if ( DEBUG ) {
            $data['debug'] = $this->debug;
        }

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
		$this->json( array( 'status' => 200, 'msg' => $msg, 'data' => $data ) );
		exit;
	}

	/**
	 * error
	 * 当post请求时返回json，当get请求时返回html
	 */
	protected function error( $msg, $data = array() ) {
		$this->json( array( 'status' => 500, 'msg' => $msg, 'data' => $data ) );
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
		return file_put_contents( DOC_DIR.'/logs/app/'.date( 'Ymd' ).( $level ? '.'.$level : '' ).'.log', date( 'Y-m-d H:i:s' ).' '.json_encode( $data ).PHP_EOL, FILE_APPEND );
	}

    /**
     * 获取汇率
     */
    protected function getExchangeRate( $coin, $currency ) {
        $currency = strtolower( $currency );
        $coin = strtolower( $coin );

        //if ( $currency == 'cny' ) $currency = 'rmb';
        if ( $currency == 'rmb' ) $currency = 'cny';

        $markets = $this->redis()->get( 'market_elf' );
        $markets_all = $markets['market_all'];
        $exchange_rate = $markets['exchange_rate'];

        $rate = '1';
        if(isset($exchange_rate['usd_'.$currency])){
            $rate = $exchange_rate['usd_'.$currency];
        }

        //获取
        return array(
            'price' => number_format(round($markets_all[$coin]['price_usd']*$rate, 3), 3, '.', ''),
            'increace' => $markets_all[$coin]['increase']>0?'1':'0',
            'increace2' => $markets_all[$coin]['increase'],
        );

    }

    /**
     * 获取7天汇率
     */
    protected function getExchangeRateChart( $coins, $currency ) {
        $currency = strtolower( $currency );
        if ( $currency == 'rmb' ) $currency = 'cny';

        $rates = $this->redis()->get( 'chart_elf' );
        $price_list = $rates['price_list'];
        $exchange_rate = $rates['exchange_rate'];

        $rate = '1';
        if(isset($exchange_rate['usd_'.$currency])){
            $rate = $exchange_rate['usd_'.$currency];
        }
        //$rates = $rates[$currency];

        $result = array();
		foreach ( $coins as $k=>$item ) {
		    $data = array();
            foreach ($price_list[$item] as $k2=>$item2){
                $data[$k2] = number_format(round($item2*$rate, 3), 3, '.', '');
            }
            $result[$item] = $data;
		}

        return $result;
    }

    protected function getCommonStatusText($status ,$category) {
        $s = '';

        if ($category == 'send') {
            $prefix = '转账';
        } elseif ($category == 'receive') {
            $prefix = '收款';
        }

        switch ( $status ) {
            case -1: $s = __( '失败' ); break;
            case 0: $s = __( '确认中' ); break;
            case 1: $s = __( $prefix.'成功' ); break;
            case 3: $s = __( $prefix.'已提交' ); break;
        }

        return $s;
    }

    //排除右边多余的0
    protected  function del0($s){
        $s = number_format( $s, 8, '.', '' );
        $s = trim(strval($s));
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','${1}0',$s);
        if (preg_match('#^-?\d+?\.0+$#', $s)) {
            return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);
        }
        if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {
            return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
        }
        return $s;
    }

    /**
     * 格式化所有元素为string类型
     * @param $arr
     * @return mixed
     */
    function format_elements_to_string ($arr)
    {
        foreach ($arr as $key => $val )
        {
            if (is_array ($val))
            {
                $arr[$key] = $this->format_elements_to_string ($val);
            }
            else
            {
                $arr[$key] = (string)$val;
            }
        }
        return $arr;
    }

    //判断是否是json格式数据
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 格式化多语言json数据
     * @param $data         数据
     * @param $keyArr       需要格式化的key值
     * @param string $lang  对应语言
     * @return mixed
     */
    function formatLangJsonValue($data, $keyArr , $lang = 'zh-cn'){
        if (!empty($keyArr)) {
            foreach ($keyArr as $key) {
                if (isset($data[$key])) {
                    $defaultValue = json_decode($data[$key], true)['en'];
                    $formatValue = json_decode($data[$key], true)[$lang];
                    if ($formatValue){  //如果当前语言值为空，那么默认显示英文
                        $data[$key] = $formatValue;
                    }else{
                        $data[$key] = $defaultValue ? $defaultValue : '';
                    }
                }
            }
        }

        return $data;
    }

    /*图片路径转换成OssImg完整图片路径*/
    public function imgToOssImgUrl($img) {
        $img = $img?$img:'elf_wallet/elf/default.png';
        if(stripos($img, 'http') === false){
            $img = $this->getConfig('oss_url').$img;
        }

        return $img;

    }

    //格式化余额
    protected function formatBalance($balance, $decimal){
        /*
        if(empty($decimal)){
            $decimal = 8;
        }
        $dec = str_pad('1', $decimal + 1, '0', STR_PAD_RIGHT);
        //return $this->del0($balance/$dec);
        return $this->del0(bcdiv($balance, $dec, $decimal));
        */
        return $this->del0($balance);
    }

    //格式化余额【链上查余额页面】
    protected function formatBalanceB($balance, $decimal){
        if(empty($decimal)){
            $decimal = 8;
        }
        $dec = str_pad('1', $decimal + 1, '0', STR_PAD_RIGHT);
        //return $this->del0($balance/$dec);
        return $this->del0(bcdiv($balance, $dec, $decimal));

    }

    //验证跨链交易是否完成交易
    protected function valiteCrossChainStatus($txid, $status, $method, $from=''){
        if(in_array($status, [0, 1])!==false && strtolower($method)=="crosschaintransfer"){
            $mdl_crossChain = $this->db('index', 'cross_chain_transaction');
            $txtInfo = $mdl_crossChain->getByWhere(['txid'=>$txid, 'status in(0,1)']);
            if($txtInfo && empty($txtInfo['rcv_txid']) && $from == $txtInfo['from_address']) {
                $status = 3;    //跨链转账转账已提交
            }
        }
        return $status;
    }


    /**
     * 清除缓存
     * @param 交易信息
     */
    protected function updateCache2($address, $flag=0){

        //更新缓存txid缓存
        $address = strtolower($address);
        $keys_address = [];
        if($flag == 1) {
            $keys_address = (array)$this->redis()->keys("elf:address:{$address}*");
        }

        $keys_coins_by_address = (array)$this->redis()->keys("elf:coins_by_address:{$address}*");
        $keys_all_balance = (array)$this->redis()->keys("elf:all_balance:{$address}*");
        $keys_assets = (array)$this->redis()->keys("elf:assets:{$address}*");
        $concurrent_address = (array)$this->redis()->keys("elf:concurrent_address:{$address}*");
        //var_dump($concurrent_address);

        $arr = array_merge($keys_address, $keys_assets, $keys_coins_by_address, $concurrent_address, $keys_all_balance);
        $arr = array_filter($arr);
        //var_dump($arr);
        foreach($arr as $item2) {
            //$this->logScreen("clear cache:".$item2);
            $this->redis()->delete($item2);
        }

    }

    //格式化主币ELF
    protected function formatChainName($chain_name){
        //return str_replace('AELF', 'AElf', $chain_name);
        return str_replace('AElf', 'AELF', $chain_name);
    }

    //根据chainid链名获取主要symbol
    protected function getFeeSymbol($chainid){
        //主链币
        $cross_info = $this->getConfig('chains');
        $cross_info_json = json_decode($cross_info, true);
        $mainCoin = array_column($cross_info_json, 'symbol','name');

        if(isset($mainCoin[$chainid])){
            return $mainCoin[$chainid];
        }

        return $mainCoin['AELF'];
    }

    //添加余额队列
    protected function addBalanceQueue($address, $sig=0, $symbol='', $chainid=''){
        $queueName = 'balance_push_queue';
        if($sig == 0) {
            $json = [
                'init' => 1,
                'address' => $address
            ];
            $queueName = $queueName."1";
        }else{
            $json = [
                'init'=>0,
                'address'=>$address,
                'symbol'=>$symbol,
                'chainid'=>$chainid
            ];
            $this->updateCache($address);
            $queueName = $queueName."0";
        }
        $this->redis()->rpush($queueName, json_encode($json));
    }

    /**
     * 清除缓存
     * @param $address 地址
     * @param string $chain_id 当前链：默认为空
     * @return mixed
     */
    protected function updateCache($address, $chain_id='')
    {
        $arr = [];
        //coins_by_address
        if($chain_id){
            $chains = [$chain_id];
        }else{
            $cross_info = $this->getConfig('chains');
            $cross_info2 = json_decode($cross_info, true);
            $cross_info3 = array_column($cross_info2,'name', null);
            $chains = $cross_info3;
        }
        $keyStr = "elf:coins_by_address:{address}:{chain_id}:elf:{currency}:{p}";
        $repStr = [
            'address'=>[$address],
            'chain_id'=>$chains,
            'currency'=>['cny','usd','krw'],
            'p'=>[0,1,2]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        //all_balance
        //assets
        $keyStr = "elf:assets:{address}:{currency}:{p}";
        $repStr = [
            'address'=>[$address],
            'currency'=>['cny','usd','krw'],
            'p'=>[0,1,2]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        //concurrent_address
        $keyStr = "elf:concurrent_address:{address}:{currency}:{type}";
        $repStr = [
            'address'=>[$address],
            'currency'=>['cny','usd','krw'],
            'type'=>[0,1]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        foreach ($arr as $k=>$item){
            $item2 = strtolower($item);
            //$this->logScreen("clear cache:".$item2);
            //$this->logFile($item2, 'clear');
            //var_dump($item2);
            $this->redis()->delete($item2);
        }
        return;
    }

    /**
     * 获取循环的keys
     * @param $str
     * @param $repStr
     * @return array
     */
    function getLoopKeys($str, $repStr)
    {
        $realArr = [];
        $strArr = [];
        $i = 0;
        foreach ($repStr as $k => $item) {
            $sr = [];
            if($i == 0){
                foreach ($item as $item2) {
                    $sr[] = str_replace("{" . $k . "}", $item2, $str);
                }
            }else{
                foreach ($strArr as $strKey) {
                    foreach ($item as $item2) {
                        if($i == count($repStr)-1){
                            $realArr[] = str_replace("{" . $k . "}", $item2, $strKey);
                        }else {
                            $sr[] = str_replace("{" . $k . "}", $item2, $strKey);
                        }
                    }
                }
            }
            $strArr = $sr;
            $i++;
        }
        return $realArr;
    }


}