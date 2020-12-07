<?php
/**
 * elf 基类.
 * User: Jett
 * Date: 2019/09/24
 * Time: 17:30 PM
 */

require_once __DIR__.'/../app.php';
//加载ECDSA算法
require_once __DIR__.'/../../../../vendor/autoload.php';

class app_elf_base extends app {

    private $service;
    private $func;
    protected $chain;
    protected $elfChainUrl = '';
    //protected $_rpc = '';    //rpc 地址
    protected $_web_api = '';    //WebApi 地址
    //protected $_api = '';  //api接口
    //protected $_bak_api = '';  //api备用接口
    //private $contract_address = '25CecrU94dmMdbhC3LWMKxtoaL4Wv8PChGvVJM6PxkHAyvXEhB';//elf合约
    private $main_symbol = 'ELF';//合约主币
    protected $_balance_url = '';
    protected $default_chain = 'AELF';    //默认选择主链
    protected $base58_url;
    protected $address_url;
    protected $tokens_url;
    protected $chain_color;
    protected $decimal;

    //不同链的节点ip
    protected $_history_api;

    //映射链信息
    protected $r_nodes = [
        '9992731'=>'AELF',
        '2750978'=>'2112',
        '2816514'=>'2113',
    ];


    public function __construct() {
        $apiConfig = $this->getConfig('api_config');
        //var_dump($apiConfig);
        $apiConfig = json_decode($apiConfig, true);
        //var_dump($apiConfig);

        /*初始化接口地址*/
        {
            $this->_web_api = $apiConfig['web_api'];    //WebApi 地址
            $this->_balance_url = $apiConfig['balance_url'];
            $this->_history_api = $apiConfig['history_api'];
            $this->base58_url = $apiConfig['base58_url'];
            $this->address_url = $apiConfig['address_url'];
            $this->tokens_url = $apiConfig['tokens_url'];
            $this->chain_color = $apiConfig['chain_color'];
            $this->r_nodes = array_flip($apiConfig["base58_nodes"]);
        }

        $cacheName = 'elf_chain_url_cache';
        $cache = $this->redis()->get( $cacheName );


        if ($cache){
            $this->elfChainUrl = $cache;
        }

        //切换链、兼容go并发传参
        /*
        $post = $_POST?$_POST:json_decode(file_get_contents("php://input"),true);;
        $chainid = $post['chainid'];
        if($post['test'] !=1) {
            $chainid = strip_tags($this->rsaDecodeFix($chainid));
        }
        if($chainid){
            $this->chain = strtoupper($chainid);
        }else{
            $this->chain = $this->default_chain;
        }
        */
        //初始化所有代币的精度

        $allContract = $this->getAllContract();
        foreach ($allContract as $item){
            foreach ($item as $item2){
                $this->decimal[$item2['symbol']] = $item2['decimals'];
            }
        }

        //var_dump($this->decimal);

    }

    public function __call($name, $arguments)
    {
        if($name == 'service' || $name == 'func'){
            $this->$name = $arguments[0];
            return $this;
        }
        if($name == 'params'){
            $params = $arguments[0];
            if($this->service == 'api'){
                if($this->chain){   //根据不同链更换api
                    $this->elfChainUrl = $this->_history_api[$this->chain];
                }else {
                    $this->elfChainUrl = $this->_history_api[$this->default_chain];
                }
            }elseif($this->service == 'rpc'){

                if($this->chain){   //根据不同链更换api
                    $this->elfChainUrl = $this->_web_api[$this->chain];
                }else {
                    $this->elfChainUrl = $this->_web_api[$this->default_chain];
                }

                $this->service = "api";
            }


            if(isset($arguments[1]) && $arguments[1] == 1){
                //post
                $result = $this->request("{$this->elfChainUrl}/{$this->service}/{$this->func}", $params, '', 1);
            }else{
                //get
                $params = http_build_query($arguments[0]);
                $params && $params = '?'.$params;
                $result = $this->request("{$this->elfChainUrl}/{$this->service}/{$this->func}{$params}");
            }
            return $result;
        }
    }

    protected function request($url, $data = array(), $type = '', $json=0)
    {
        //$data是字符串，则application/x-www-form-urlencoded
        //$data是数组，则multipart/form-data

        //$headers = array();
        //$headers[] = "Content-type: text/xml; charset=utf-8";
        if ($this->is_json($data) || $json) {
            $headers = [
                'Content-Type:application/json; v=1.0',
                //'Accept: application/json'
                'accept:text/plain; v=1.0'
            ];
        }
        //var_dump($url);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //$this->logFile($url, 'con');
        //$this->logFile($data, 'con');
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if ($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        $errno = curl_errno($curl);

        if ($type){
            return curl_getinfo($curl, $type);
        }

        if ($errno) {
            $output = array('errno' => $errno, 'error' => curl_error($curl));
            $output['detail'] = curl_getinfo($curl);
        }
        curl_close($curl);
        return $output;
    }

    //判断是否是json格式数据
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    /**
     * 获取历史交易记录
     * @param $symbol
     * @param $address
     * @param int $pageSize
     * @param string $contractAddress
     * @param int $page
     * @return bool|mixed
     */
    public function getElfChain($symbol, $address, $contractAddress,$pageSize=5, $page=1, $currency='RMB', $type){

        $lang = str_replace('-', '_', get2( 'lang' ));
        $cacheName = "elf:address:{$address}:{$this->chain}:{$contractAddress}:{$symbol}:".$lang.'_'.$pageSize.'_'.($page+1)."_".$type;
        //$method = "Transfer";
        $cache = $this->redis()->get( $cacheName );
        //$cache = [];
        if ( $cache && $cache[$page] && app::REDISENV) {
            return $cache[$page];
        }

        //判断方法
        //$this->chain = $chain_id;
//        $res = $this->service('api')->func('address/transactions')->params([
//            'symbol'=>$symbol,
//            'address'=>$address,
//            'contract_address'=>$contractAddress,
//            //'method'=>$method,
//            'limit'=>$pageSize,
//            'page'=>$page
//        ]);

        //跨链接收
        $request = [
            'symbol'=>$symbol,
            'address'=>$address,
            'contract_address'=>$contractAddress,
            'method'=>'',
            'limit'=>$pageSize,
            'page'=>$page,
            'chain_id'=>$this->chain,
            'type'=>$type
        ];
        $res_json = $this->request($this->address_url, json_encode($request));
        $res = json_decode($res_json, true);
        //print_r( $res);

        if($res['status'] == 200){
            $data = [
                'count'=>0,
                'pageCount'=>0
            ];

            //汇率
            $rate = $this->getExchangeRate($symbol, $currency);
            //美元汇率
            $usd_rate = $this->getExchangeRate($symbol, "USD");

            $data2 = array();
            $res = $res['data'];
            foreach ($res as $val) {
                if(in_array($val['method'], ['Transfer','CrossChainTransfer','CrossChainReceiveToken']) === false) {
                    continue;
                }

                $res2 = $this->getElfAddressHistory($address, $val);
                list($category, $amount, $time, $status, $from, $to, $memo, $from_chainid, $to_chainid) = $res2;

                //跨链交易的状态
                if($from == $address) {
                    $status = $this->valiteCrossChainStatus($val['tx_id'], $status, $val['method'], $from);
                }
                $data2[] = [
                    "chain" => $this->formatChainName($val["chainid"]),
                    "category" => $category,
                    'symbol' => $symbol,
                    "txid" => $val['tx_id'],
                    "amount_o" => strval($this->del0($amount)),
                    "amount" => $this->formatBalanceB($amount, $this->decimal[$symbol]),
                    "block" => $val['block'],
                    "time" => $time ? $time : time(),
                    "nonce" => "",
                    "fee" => "0.00",
                    "gasLimit" => "",
                    "gasPrice" => "",
                    "gasUsed" => "",
                    "status" => $status,
                    "from" => $from,
                    "to" => $to,
                    "statusText" => $this->getCommonStatusText($status, $category),
                    "addressList" => [],
                    "timeOffset" => time() - ($val['time'] ? $val['time'] : time()),
                    "confirmations" => 0,
                    "completed" => app::completed,
                    'memo'=>$memo,
                    'method'=>strtolower($val['method']),
                    'to_chainid'=>$to_chainid,
                    'from_chainid'=>$from_chainid,
                    'rate'=>$rate,
                    'usd_rate'=>$usd_rate
                ];
            }

            array_multisort(array_column($data2,'time'), SORT_DESC, $data2);
            $data['list'] = $data2;

            //设置缓存
            $cache = [
                $page => [
                    'count' => $data['count'],
                    'pageCount' => $data['pageCount'],
                    'list' => $data['list'],
                ],
            ];
            //$this->logFile($cache, "address");
            $data2 && $this->redis()->set($cacheName, $cache, 5 * 60);

            return $cache[$page];
        } else {
            return false;
        }

    }

    protected function getElfAddressHistory($address, $history){
        $category = "receive";
        $amount = $history['amount'];
        $time = strtotime($history['time']);
        $status = 0;
        $from = $history['from_address'];
        $to = $history['to_address'];
        $memo = $history['memo'];
        $method = $history["method"];

        if($method == 'CrossChainReceiveToken') {
            if (strtolower($to) == strtolower($address)) {
                $category = 'receive';
            }
        }else{
            if (strtolower($from) == strtolower($address)) {
                $category = 'send';
            }
        }

        //判断交易状态
        if (strtolower($history['tx_status']) == "mined"){
            $status = 1;
        }elseif(strtolower($history['tx_status']) == "failed"){
            $status = -1;
        }elseif(strtolower($history['tx_status']) == "pending"){
            $status = 0;
        }

        if($history['to_chainid']){
            $to_chainid = $this->r_nodes[$history['to_chainid']];
        }else{
            $to_chainid = $this->chain;
        }
        if($history['from_chainid']){
            $from_chainid = $this->r_nodes[$history['from_chainid']];
        }else{
            $from_chainid = $this->chain;
        }

        return [
            $category, $amount, $time, $status, $from, $to, $memo, $from_chainid, $to_chainid
        ];
    }

    /**
     * 获取地址下所有的合约币
     * @param $address
     */
    public function getAddressTokens($address, $p, $limit=10){
        //获取用户所有的币种
        $token = call_user_func(function ($data){
            if($this->is_json($data)){
                $data = json_decode($data, true);
                if($data && count($data)>0){
                    return $data;
                }
            }
            return false;
        }, $this->service('api')->func('address/tokens')->params([
            'address'=>$address,
            'nodes_info'=>"true",
            'limit'=>$limit,
            'page'=>$p
        ]));
        return $token;
    }


    /**
     * 获取余额
     * @param $合约币猎豹
     * @return array
     */
    public function getBalanceAddress($data, $currency) {
        $data = $this->getBalance($data, $currency);
        array_walk($data, function (&$item, $key, $currency){
            $item = (array)$item;
            $amount = number_format($item['balance'], $this->decimal[$item["symbol"]],'.', '');
            $amount = $amount??0;

            $coin = $this->getCoin( $item['symbol'] );
            $ossUrl = $this->getConfig( 'OSS_URL' )??$this->getConfig( 'oss_url' );
            //汇率
            $rate = $this->getExchangeRate( $item['symbol'], $currency );

            $type= ($item['symbol']==$this->main_symbol)?"main":"contract";
            $item = array(
                "amount"=> $this->del0($amount),
                "balance"=>$this->del0($amount),
                "type"=> $type,
                "chain_id"=>$this->formatChainName($item["chain_id"]),
                "name"=> $item["symbol"],
                "symbol"=> $item["symbol"],
                "decimals"=> $this->decimal[$item["symbol"]],
                "contractAddress"=> $item['contract_address'],
                "logo"=> $coin['logo'] ? $ossUrl.$coin['logo'] : $ossUrl.'elf_wallet/elf/default.png',
                "rate"=> $rate
            );
        }, $currency);
        $lt = [];
        foreach($data as $k=>$item){
            if($item['symbol'] == 'ELF'){
                $first = [$item['chain_id'],$item];
            }else {
                $lt[$item['chain_id']][] = $item;
            }
        }
        if($first) {    //排除删除ELF币的情况
            if (count($data) == 1) {
                $lt[$first[0]][] = $first[1];
            } else {
                array_unshift($lt[$first[0]], $first[1]);
            }
        }
        return $lt;
    }

    //从缓存冲获取余额列表
    public function getBalanceAddressLocal($tokens, $currency) {
        //$data = $this->getBalance($data, $currency);
        $data = [];
        foreach ($tokens as $item){
            $cachekey = "elf:balance:{$item['chain_id']}:{$item['address']}:{$item['symbol']}";
            $mt = $this->redis()->get($cachekey);
            $data = array_merge($data, [[
                "address"=>$item['address'],
                "contractAddress"=>$item['contract_address'],
                "symbol"=>$item['symbol'],
                "chainid"=>$item['chain_id'],
                "balance"=>$mt?$mt:0,
                "contract_address"=>$item['contract_address'],
                "chain_id"=>$item['chain_id']
            ]]);
        }
        //var_dump($data);
        array_walk($data, function (&$item, $key, $currency){
            $item = (array)$item;
            $amount = number_format($item['balance'], $this->decimal[$item["symbol"]],'.', '');
            $amount = $amount??0;

            $coin = $this->getCoin( $item['symbol'] );
            $ossUrl = $this->getConfig( 'OSS_URL' )??$this->getConfig( 'oss_url' );
            //汇率
            $rate = $this->getExchangeRate( $item['symbol'], $currency );

            $type= ($item['symbol']==$this->main_symbol)?"main":"contract";
            $item = array(
                "amount"=> $this->del0($amount),
                "balance"=>$this->del0($amount),
                "type"=> $type,
                "chain_id"=>$this->formatChainName($item["chain_id"]),
                "name"=> $item["symbol"],
                "symbol"=> $item["symbol"],
                "decimals"=> $this->decimal[$item["symbol"]],
                "contractAddress"=> $item['contract_address'],
                "logo"=> $coin['logo'] ? $ossUrl.$coin['logo'] : $ossUrl.'elf_wallet/elf/default.png',
                "rate"=> $rate
            );
        }, $currency);
        $lt = [];
        foreach($data as $k=>$item){
            if($item['symbol'] == 'ELF'){
                $first = [$item['chain_id'],$item];
            }else {
                $lt[$item['chain_id']][] = $item;
            }
        }
        if($first) {    //排除删除ELF币的情况
            if (count($data) == 1) {
                $lt[$first[0]][] = $first[1];
            } else {
                array_unshift($lt[$first[0]], $first[1]);
            }
        }
        return $lt;
    }

    protected function getBalance($data, $currency){

        //并发获取用户余额var
        foreach ($data as $k=>$item) {
            $item2 = [
                'address' => $item['address'],
                'contractAddress' => $item['contract_address'],
                'symbol' => $item['symbol'],
                'chainid'=>$item['chain_id']
            ];
            $restring[] = $item2;
        }
        $data2 = ['param' => $restring, 'currency' => $currency];
        $res = $this->request($this->_balance_url, json_encode($data2));
        $resArr = [];
        if($res && $this->is_json($res)) {
            $resArr = json_decode($res, true);
            foreach($resArr as $k=>$item){
                $item['contract_address'] = $item['contractAddress'];
                $item['chain_id'] = $item['chainid'];
                $resArr[$k] = $item;
            }
        }
        return $resArr;
    }


    //获取所有合约
    protected function getAllContract(){

        $cacheName = 'getAllContracts';

        $cache = $this->redis()->get($cacheName);

        $lt = [];
        if ($cache) {
            $lt = $cache;
        } else {
            $res = [];
            foreach ($this->_web_api as $k=>$item) {
                $params = [
                    'limit' => 100,
                    'page' => 0,
                    'order' => 'DESC'
                ];
                $this->chain = $k;
                $_res = $this->service('api')->func('contract/contracts')->params($params);
                $_res = json_decode($_res, true);
                $res = array_merge($res, [$_res]);
            }
        }
        foreach ($res as $item) {
            foreach ($item['transactions'] as $item2) {
                if($item2["symbol"]) {
                    $lt[$item2['chain_id']][] = $item2;
                }
            }
        }
        return $lt;
    }

    //根据address获取用户所有链应用币的余额
    protected function getAllBalance($address, $currency){

        //采用读缓存的方式获取用户余额
        return $this->getAllBalanceFromCache($address, $currency);
        /*
        $key = "elf:all_balance:{$address}:{$currency}";

        $json = $this->redis()->get($key);
        if($json) {
            return $json;
        }

        $tokens = $this->getAddressTokensFromLocal($address);
        $this->logFile($tokens, "balance_all_2");
        $json = $this->getBalance($tokens, $currency);
        $this->logFile($json, "balance_all_2");
        //添加检测程序
        if(!$json){
            //缓冲1s
            sleep(1);
            $json = $this->getBalance($tokens, $currency);
            if(!$json) {
                $this->logFile(['address'=>$address,'res_tokens'=>$tokens,'balance'=>$json], "balance_error");
            }
        }
        $json = $this->repairBalance($tokens, $json);
        $this->redis()->set($key, $json, 600);  //10分钟
        $this->logFile($json, "balance_all");
        return $json;
        */
    }

    //根据address获取用户所有链应用币的余额:（从redis缓存获取 优化）
    protected function getAllBalanceFromCache($address, $currency, $chainid=''){
        $json = [];

        $tokens = $this->getAddressTokensFromLocal($address, $chainid);
        foreach ($tokens as $item){
            $cachekey = "elf:balance:{$item['chain_id']}:{$item['address']}:{$item['symbol']}";
            $mt = $this->redis()->get($cachekey);
            $json = array_merge($json, [[
                "address"=>$item['address'],
                "contractAddress"=>$item['contract_address'],
                "symbol"=>$item['symbol'],
                "chainid"=>$item['chain_id'],
                "balance"=>$mt?$mt:0,
                "contract_address"=>$item['contract_address'],
                "chain_id"=>$item['chain_id']
            ]]);
        }

        return $json;
    }

    /**
     * 修复用户余额为空的情况
     * @param $address
     * @param $balance
     * @return
     */
    protected function repairBalance($tokens, $balance){
        $this->logFile($tokens, "balance_all_3");
        //$balance = [];
        foreach ($tokens as $item){
            $cachekey = "elf:balance:{$item['chain_id']}:{$item['address']}:{$item['symbol']}";
            $tag = 0;
            $index = 0;
            foreach ($balance as $k2=>$item2){
                if($item['address']==$item2['address'] && $item['chain_id']==$item2['chain_id'] && $item['symbol']==$item2['symbol']){
                    $tag = 1;
                    $index = $k2;
                    break;
                }
            }
            $this->logFile($tag, "balance_all_4");
            if($tag == 0){  //丢失该token的余额
                $mt = $this->redis()->get($cachekey);
                $balance = array_merge($balance, [[
                    "address"=>$item['address'],
                    "contractAddress"=>$item['contract_address'],
                    "symbol"=>$item['symbol'],
                    "chainid"=>$item['chain_id'],
                    "balance"=>$mt?$mt:0,
                    "contract_address"=>$item['contract_address'],
                    "chain_id"=>$item['chain_id']
                ]]);
                $t = [[
                    "address"=>$item['address'],
                    "contractAddress"=>$item['contract_address'],
                    "symbol"=>$item['symbol'],
                    "chainid"=>$item['chain_id'],
                    "balance"=>$mt?$mt:0,
                    "contract_address"=>$item['contract_address'],
                    "chain_id"=>$item['chain_id']
                ]];
                $this->logFile($t, "balance_all_4");
            }else{  //保存该token余额至缓存
                $this->redis()->set($cachekey, $balance[$index]['balance']);
            }
        }
        return $balance;
    }

    //从本地获取用户绑定信息
    //$chainid =0则取所有的合约地址
    protected function getAddressTokensFromLocal($address, $chainid=''){
        $mdl_bind = $this->db('index', 'bind');
        $status = 1; //1绑定2解绑
        if($chainid){   //获取单链的合约地址
            $where = [
                'chain_id'=>$chainid,
                'address'=>$address,
                'status'=>$status
            ];

        }else{
            $where = [
                'address'=>$address,
                'status'=>$status
            ];
        }
        $res = $mdl_bind->getList(['address','chain_id','contract_address','symbol'], $where);
        return $res;
    }

    /**
     * 绑定合约地址
     * @param $chain_id
     * @param $address
     * @param $contract_address
     * @return
     */
    protected function bindTokensFromLocal( $address, $chain_id='', $contract_address='', $symbol=''){
        $mdl_bind = $this->db('index', 'bind', 'master');
        if(!$chain_id && !$contract_address && !$symbol){   //初始化绑定
            $cross_info = $this->getConfig('chains');
            $cross_info2 = json_decode($cross_info, true);
            $cross_info3 = array_column($cross_info2,'symbol', 'name');
            //var_dump($cross_info2);
            foreach ($cross_info2 as $item){
                $lists = [];
                if($item['name'] == 'AELF'){ //ELF链
                    $lists = $cross_info3;
                }else {
                    $lists = [$item['symbol'], 'ELF'];
                }
                foreach ($lists as $item2) {
                    $sql = "Replace Into #@_bind SET `address`=?,`chain_id`=?,`contract_address`=?,`symbol`=?, `status`=?, `time`=?";
                    $res = $mdl_bind->sql($sql, [$address, $item['name'], $item['contract_address'], $item2, 1, time()]);
                    if (!$res) {
                        $this->logFile(['address' => $address, 'bind' => 1, 'chains' => $item], 'local_bind');
                    }
                }
            }
            return 0;
        }

        $where = [
            'address'=>$address,
            'chain_id'=>$chain_id,
            'contract_address'=>$contract_address,
            'symbol'=>$symbol,
            'status'=>1
        ];

        $count = $mdl_bind->getCount($where);
        if($count > 0){
            return $count;
        }
        $sql = "Replace Into #@_bind SET `address`=?,`chain_id`=?,`contract_address`=?,`symbol`=?, `status`=?,`time`=?";
        $res = $mdl_bind->sql($sql, [$address, $chain_id, $contract_address, $symbol,1,time()]);
        if(!$res) {
            $this->logFile(['address' => $address, 'bind' => 0, 'chains' => [
                'address'=>$address,
                'chain_id'=>$chain_id,
                'contract_address'=>$contract_address,
                'symbol'=>$symbol
            ] ], 'local_bind');
        }

        return 0;
    }

    /**
     * 解绑合约币
     * @param $address
     * @param string $chain_id
     * @param string $contract_address
     * @param string $symbol
     */
    protected function unbindTokensFromLocal( $address, $chain_id='', $contract_address='', $symbol=''){
        $mdl_bind = $this->db('index', 'bind', 'master');
        $res = $mdl_bind->updateByWhere(['status'=>2], [
            'address'=>$address,
            'chain_id'=>$chain_id,
            'contract_address'=>$contract_address,
            'symbol'=>$symbol
        ]);

        if(!$res) {
            $this->logFile(['address' => $address, 'bind' => -1, 'chains' => [
                'address'=>$address,
                'chain_id'=>$chain_id,
                'contract_address'=>$contract_address,
                'symbol'=>$symbol
            ] ], 'local_bind');
        }
        return;
    }

    //排序处理
    protected function orderSymbol($symbol){
        $sort = 0;
        switch ($symbol){
            case 'ELF':$sort = 10;break;
            case 'EPC':$sort = 9;break;
            case 'EDA':$sort = 8;break;
            case 'EDB':$sort = 7;break;
            case 'EDC':$sort = 6;break;
            case 'EDD':$sort = 5;break;
            default:$sort=0;
        }

        return $sort;
    }


}