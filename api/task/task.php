<?php

date_default_timezone_set( 'PRC' );
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));
header( 'Content-Type:text/html;charset=utf-8;' );

require_once 'data.config.php';
require_once 'base.php';

set_time_limit( 0 );

class task extends base {
	/**
	 * 当前币种
	 */
	protected $coin;
    private $start_time;    //程序开始时间
    protected $chainid;
    protected $rpc_url;
    protected $scaner_node;
    protected $api_url;
    protected $base58_url;
    protected $chain_node;
    protected $den;
    protected $isex;
    protected $decimal;


    public function __construct()
    {
        //选择不同的区块链
        /**
         * chainid  执行的当前链
         * den 进程个数 li:2
         * isex 当前进程 li: 0,1
         */
        $params = getopt('', ['chainid:','den:','isex:']);
        $this->chainid = $params['chainid']?trim($params['chainid']):'AELF';
        $this->den = $params['den']?trim($params['den']):1;
        $this->isex = $params['isex']?trim($params['isex']):0;

        $apiConfig = $this->getConfig('api_config');
        $apiConfig = json_decode($apiConfig, true);

        //scaner_node
        if(!isset($apiConfig['web_api'][$this->chainid])){
            $this->chainid = 'AELF';
        }

        $this->rpc_url = $apiConfig['web_api'][$this->chainid];
        $this->scaner_node = $apiConfig['scaner_node'][$this->chainid];

        $this->api_url = $apiConfig['history_api'][$this->chainid];
        $this->base58_url = $apiConfig['base58_url'];
        $this->chain_node = $apiConfig['base58_nodes'];

        //初始化所有代币的精度
        $cacheName = 'getAllContracts';
        //$allContract = json_decode($this->redis()->get($cacheName), true);
        $allContract =$this->redis()->get($cacheName);
        foreach ($allContract as $item){
            foreach ($item as $item2){
                $this->decimal[$item2['symbol']] = $item2['decimals'];
            }
        }
    }

    protected function success( $msg, $data = array() ) {
		echo date( 'Y-m-d H:i:s' ).' success:'.$msg.PHP_EOL;
		if ( $data ) echo json_encode( $data ).PHP_EOL;
		exit;
	}

	protected function error( $msg, $data = array() ) {
		echo date( 'Y-m-d H:i:s' ).' error:'.$msg.PHP_EOL;
		if ( $data ) echo json_encode( $data ).PHP_EOL;
		exit;
	}

	protected function msg( $msg, $data = array() ) {
		echo date( 'Y-m-d H:i:s' ).' error:'.$msg.PHP_EOL;
		if ( $data ) echo json_encode( $data ).PHP_EOL;
	}

	/**
	 * 执行某方法之前
	 *
	 * @param string key 密钥，只有正确才能执行
	 */
	public function before() {
		//$key = trim( get2( 'key' ) );

		//先不做这个限制，cron服务器会配成不允许外部访问
        $this->start_time = time();
        $this->blocks = 0;
	}

	/**
	 * 执行某方法之后
	 */
	public function after() {
        $end_time = microtime(true);
        $spendTime = round(($end_time-$this->start_time), 3);
        $msg = "本次消耗{$spendTime}s".PHP_EOL;
        $this->logScreen($msg);
	}

	/**
	 * 获取币种的设置
	 * 先获取当前所属会员分组的设置，如果没有，再获取币种的设置
	 */
	protected function getCoinSetting( $coin, $group ) {
		if ( empty( $coin ) ) return false;
		if ( empty( $group ) ) return false;

		if ( $group['coins'][$coin] ) return $group['coins'][$coin];

		return $this->getCoin( $coin );
	}

    /**
     * 日志方法
     * @param $msg
     * @param $flag = 0[default] =1 错误输出
     */
    protected function logScreen($msg, $flag=0){
        $time = date('Y-m-d H:i:s');
        if(is_array($msg)){
            $msg = print_r($msg, true);
        }
        if($flag){
            $reposmsg = "[Error]{$time}:{$msg}".PHP_EOL;
        }else{
            $reposmsg = "[Info]{$time}:{$msg}".PHP_EOL;
        }
        print $reposmsg;
    }

    /**
     * 推送消息
     * @param array $transaction
     */
    protected function transaction_push_queue(array $transaction)
    {
        print_r($transaction);
        /*如果交易成功且类型为交易的则把推送消息加入到队列*/
        if ($transaction['tx_status'] != 'Pending' && in_array($transaction['method'], ['Transfer','CrossChainTransfer','CrossChainReceiveToken'])) {

            //精度计算
            $transaction['quantity'] = $this->formatBalanceB($transaction['quantity'], $this->decimal[$transaction['symbol']]);

            //处理余额缓存
            $this->addBalanceQueue($transaction);

            sleep(5);   //增加清除缓存的时间

            //清除from/to相关的缓存
            $this->updateCache($transaction);

            $queueName = 'transaction_push_queue';

            /*转账通知*/
            if(in_array($transaction['method'], ['Transfer','CrossChainTransfer'])){
                $msg = strtolower($transaction['tx_status'])=="mined"?"您有一笔交易已发送成功，金额：%s":"您有一笔交易已发送失败，金额：%s";

                // $lang = $this->getLangByAddress($transaction['address_from']);
                $fromUserInfo = $this->getUserByAddress($transaction['address_from']);
                $lang = $this->redis()->get( 'lang/' . $fromUserInfo['id'] );

                $currency = $this->getCurrencyByAddress($transaction['address_from']);
                $queueData = [
                    'address' => $transaction['address_from'],
                    'message' => [
                        'title' => ___("ELF钱包", $lang),
                        'content' => ___($msg, $lang, $transaction['quantity']. strtoupper($transaction['symbol'])),
                        'extras'=>[
                            'txid'=>$transaction['tx_id'],
                            'address'=>$transaction['address_from'],
                            'currency'=>$currency,
                            'type'=>0
                        ]
                    ],
                ];

                $this->redis()->rPush($queueName, json_encode($queueData));
            }

            /*收款通知*/
            if(in_array($transaction['method'], ['Transfer','CrossChainReceiveToken'])){
                $msg = strtolower($transaction['tx_status'])=="mined"?"您有一笔收款到账，金额：%s":"您有一笔收款失败，金额：%s";
           //     $lang = $this->getLangByAddress($transaction['address_to']);

                $toUserInfo = $this->getUserByAddress($transaction['address_to']);
                $lang = $this->redis()->get( 'lang/' . $toUserInfo['id'] );

                $currency = $this->getCurrencyByAddress($transaction['address_to']);
                $queueData = [
                    'address' => $transaction['address_to'],
                    'message' => [
                        'title' => ___("ELF钱包", $lang),
                        'content' => ___($msg, $lang, $transaction['quantity']. strtoupper($transaction['symbol'])),
                        'extras'=>[
                            'txid'=>$transaction['tx_id'],
                            'address'=>$transaction['address_to'],
                            'currency'=>$currency,
                            'type'=>0
                        ]
                    ],
                ];

                $this->redis()->rPush($queueName, json_encode($queueData));
            }

        }
    }

    //将转账成功的address传入通道
    protected function addBalanceQueue($transaction){
        $chain_node = array_flip($this->chain_node);

        $queueName = 'balance_push_queue0';
        $json = [
            'init'=>0,
            'address'=>'',
            'symbol'=>$transaction['symbol'],
            'chainid'=>''
        ];

        //from_address
        $json['address'] = $transaction['address_from'];
        $json['chainid'] = $transaction['chain_id'];
        if($transaction['method'] == 'CrossChainReceiveToken'){
            $json['chainid'] = $chain_node[$transaction['from_chainid']];
        }
        $this->redis()->rpush($queueName, json_encode($json));

        //to_address
        if(($transaction['method'] == 'CrossChainTransfer' && $transaction['to_chainid'])
            || $transaction['method']=='Transfer'
            || $transaction['method']=='CrossChainReceiveToken'
        ) {
            //$this->chain_node = array_flip($this->chain_node);
            $json['address'] = $transaction['address_to'];
            $json['chainid'] = $transaction['chain_id'];
            if ($transaction['method'] == 'CrossChainTransfer') {
                $json['chainid'] = $chain_node[$transaction['to_chainid']];
            }
        }
        $this->redis()->rpush($queueName, json_encode($json));
    }

    /**
     * 根据用户地址判断当前的设备号
     * @param $address
     * @return string
     */
    protected function getLangByAddress($address)
    {
        $mdl_com_addr = $this->db('index', 'com_addr');
        $list = $mdl_com_addr->getList(['lang'], ['address' => $address], 'id desc', 1);
        $data = $list[0];

        if (isset($data['lang']) && $data['lang']) {
            $lang = $data['lang'];
        } else {
            $lang = 'en';
        }

        return $lang;
    }

    /**
     * 根据用户地址获取用户使用的当前币种
     * @param $address
     */
    protected function getCurrencyByAddress($address)
    {
        $mdl_com_addr = $this->db('index', 'user_address');
        $list = $mdl_com_addr->getList(['currency'], ['address' => $address], 'id desc', 1);
        $data = $list[0];
        if (isset($data['currency']) && $data['currency']) {
            $lang = $data['currency'];
        } else {
            $lang = 'RMB';
        }
        return $lang;
    }

    //排除右边多余的0
    protected  function del0($s){
        $s = number_format( $s, 8, '.', '' );
        $s = trim(strval($s));
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
        if (preg_match('#^-?\d+?\.0+$#', $s)) {
            return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);
        }
        if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {
            return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
        }
        return $s;
    }

    /**
     * 根据address获取用户信息
     * @param $address
     * @return mixed
     */
    protected function getUserByAddress($address){
        $mdl_user_address = $this->db('index', 'user_address');
        $user = $mdl_user_address->getByWhere(['address' => $address]);
        return $user;
    }

    /**
     * 扫描到address数据更新app端接口缓存
     * @param 交易信息
     */

    protected function updateCache2($transaction){
        return;

        //更新缓存txid缓存
        //$cname = "elf:transaction:{$transaction['tx_id']}";
        //$this->logScreen($cname);
        //$this->redis()->delete($cname);

        $addresses = [$transaction['address_from'], $transaction['address_to']];
        foreach($addresses as $address) {
            //更新缓存txid缓存
            $address = strtolower($address);
            /*
            $keys_address = (array)$this->redis()->keys("elf:address:{$address}*");
            $keys_coins_by_address = (array)$this->redis()->keys("elf:coins_by_address:{$address}*");
            $keys_all_balance = (array)$this->redis()->keys("elf:all_balance:{$address}*");
            $keys_assets = (array)$this->redis()->keys("elf:assets:{$address}*");
            $concurrent_address = (array)$this->redis()->keys("elf:concurrent_address:{$address}*");
            */
            //var_dump($concurrent_address);
            $keys_address = (array)$this->redisScaner("elf:address:{$address}*");
            $keys_coins_by_address = (array)$this->redisScaner("elf:coins_by_address:{$address}*");
            $keys_all_balance = (array)$this->redisScaner("elf:all_balance:{$address}*");
            $keys_assets = (array)$this->redisScaner("elf:assets:{$address}*");
            $concurrent_address = (array)$this->redisScaner("elf:concurrent_address:{$address}*");

            $arr = array_merge($keys_address, $keys_assets, $keys_coins_by_address, $concurrent_address, $keys_all_balance);
            $arr = array_filter($arr);
            var_dump($arr);
            foreach($arr as $item2) {
                $this->logScreen("clear cache:".$item2);
                $this->redis()->delete($item2);
            }
        }
    }

    /**
     * 扫描到address数据更新app端接口缓存
     * @param 交易信息
     */
    protected function updateCache($transaction)
    {
        $cross_info = $this->getConfig('chains');
        $cross_info2 = json_decode($cross_info, true);
        $cross_info3 = array_column($cross_info2,'contract_address', 'name');

        $arr = [];
        //address
        $keyStr = "elf:address:{address}:{$transaction['chain_id']}:{$cross_info3[$transaction['chain_id']]}:{$transaction['symbol']}:{lang}_10_{page}_{type}";
        $repStr = [
            'address'=>[$transaction['address_from'], $transaction['address_to']],
            'lang'=>['zh_cn','en','ko'],
            'page'=>[1,2,3,4,5],
            'type'=>[0,1,2]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        //coins_by_address
        $keyStr = "elf:coins_by_address:{address}:{$transaction['chain_id']}:elf:{currency}:{p}";
        $repStr = [
            'address'=>[$transaction['address_from'], $transaction['address_to']],
            'currency'=>['cny','usd','krw'],
            'p'=>[0,1,2]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        //all_balance
        //assets
        $keyStr = "elf:assets:{address}:{currency}:{p}";
        $repStr = [
            'address'=>[$transaction['address_from'], $transaction['address_to']],
            'currency'=>['cny','usd','krw'],
            'p'=>[0,1,2]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        //concurrent_address
        $keyStr = "elf:concurrent_address:{address}:{currency}:{type}";
        $repStr = [
            'address'=>[$transaction['address_from'], $transaction['address_to']],
            'currency'=>['cny','usd','krw'],
            'type'=>[0,1]
        ];
        $arr = array_merge($arr, $this->getLoopKeys($keyStr, $repStr));

        foreach ($arr as $k=>$item){
            $item2 = strtolower($item);
            $this->logScreen("clear cache:".$item2);
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

    //格式化余额
    protected function formatBalance($balance, $decimal){
        /*
        if(empty($decimal)){
            $decimal = 8;
        }
        bcdiv();
        $dec = str_pad('1', $decimal + 1, '0', STR_PAD_RIGHT);
        //return $this->del0($balance/$dec);
        return $this->del0(bcdiv($balance, $dec, $decimal));
        */
        return $this->del0($balance);
    }

    //格式化余额
    protected function formatBalanceB($balance, $decimal){
        if(empty($decimal)){
            $decimal = 8;
        }
        $dec = str_pad('1', $decimal + 1, '0', STR_PAD_RIGHT);
        //return $this->del0($balance/$dec);
        return $this->del0(bcdiv($balance, $dec, $decimal));

    }

}