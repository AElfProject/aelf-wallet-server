<?php

/**
 * 增加用户交易信息
 *
 */

require_once '../task.php';

class task_transSync extends task
{
    private $trans_limit = 100; //交易参数每次请求个数
    private $trans_url = '';    //获取交易详情
    private $trans_height_cahce = '';
    private $addressKey = "aelf:address:"; //app用户address

	function doRequest() {
		set_time_limit( 0 );
        $this->trans_url = "{$this->scaner_node}/api/blockChain/transactionResults";
        $this->trans_height_cahce = 'trans_height:'.$this->chainid.":".$this->isex;

		while ( true ) {
            //$this->before();
			$this->interval();
            //$this->after();
			//sleep( 1 );
		}
	}

	function interval() {
	    try {
	        $m= 0;
            $height = $this->redis()->get($this->trans_height_cahce);
            $height || $height = 1;

            //是否该进程处理
            if($height%$this->den != $this->isex){
                $this->redis()->set($this->trans_height_cahce, $height+1);
                return;
            }


            $blockey = "aelf:block:{$this->chainid}:{$height}";
            $blockinfo = $this->redis()->get($blockey);
            if(empty($blockinfo)){
                sleep(3);
                throw new Exception("waiting for block {$height}");
            }

            $pages = ceil($blockinfo['txcount']/$this->trans_limit);
            for($i=0; $i<$pages; $i++){
                $offset = $i*$this->trans_limit;
                $url = $this->trans_url."?limit={$this->trans_limit}&offset={$offset}&blockHash={$blockinfo['hash']}";
                //$url = 'http://47.74.219.55:1728/api/blockChain/transactionResults?blockHash=42db950a5e2f7cd7cb62d89adcdd3f6347c0ac0de07c93d5d2c342ebf6ca8807&offset=0&limit=10';
                //echo $url;
                $res = $this->request($url);
                if(!$res || stripos($res, '{')===false) {
                    throw new Exception("Request trans_url Error");
                }
                $list = json_decode($res, true);
                if (count($list) == 0) {
                    $msg = "block {$height} data is full";
                    throw new Exception($msg);
                }
                $list = $this->getUserAddressList($list, $blockinfo);   //该项目中存在的elf地址
                foreach ($list as $k => $transaction) {
                    $m ++;
                    /*该笔交易的转账地址或者收款地址是项目用户的则进行相关操作否则直接跳过*/
                    //if (in_array($transaction['address_from'], $userAddressList) || in_array($transaction['address_to'], $userAddressList)) {
                    //var_dump($this->addressKey.$transaction['address_from']);
                    //var_dump($this->addressKey.$transaction['address_to']);
                    //var_dump($this->redis()->get($this->addressKey.$transaction['address_from']));
                    if ($this->redis()->get($this->addressKey.$transaction['address_from']) == 1 || $this->redis()->get($this->addressKey.$transaction['address_to']) == 1) {
                            $add_status = $this->add_transaction($transaction);
                        if ($add_status) {    //如果插入成功插入消息到队列中
                            $msg = 'tx_id:'.$transaction['tx_id'].' success'.PHP_EOL;
                            $this->logScreen($msg);
                            $this->transaction_push_queue($transaction);    //插入消息到队列中
                        }
                    } else {
                        //$msg = $transaction['address_from'].'、'.$transaction['address_to'].'不在用户列表中!'.PHP_EOL;
                        //$this->logScreen($msg);
                    }
                }
            }
            $this->redis()->set($this->trans_height_cahce, $height+1);
            $msg = "total:block ".$blockinfo['height'].",trans {$m}".PHP_EOL;
            $this->logScreen($msg);
            $this->redis()->delete($blockey);   //删除区块缓存
        }catch (Exception $exception){
	        $this->logScreen($exception->getMessage(), 1);
        }

	}

    /**
     * 获取本项目中存在的elf地址
     * @param array $transactions
     * @return array
     */
	private function getUserAddressList(array $transactions, array $blockinfo){
        //整理返回数据格式 过滤method != Transfer
        array_walk($transactions, function (&$data, $key, $block){
            if(in_array($data['Transaction']['MethodName'], ['Transfer','CrossChainTransfer','CrossChainReceiveToken'])===false) {
                $data = null;
            }else {
                $params = json_decode($data['Transaction']['Params'], true);
                if(in_array($data['Transaction']['MethodName'], ['Transfer','CrossChainTransfer']) !== false){
                    $data = [
                        'tx_id' => $data['TransactionId'],
                        'chain_id' => $block['chain_id'],
                        'block_height' => $block['height'],
                        'block_hash' => $block['hash'],
                        'address_from' => $data['Transaction']['From'],
                        'address_to' => $params['to'],
                        'quantity' => $params['amount'],
                        'tx_status'=> $data['Status'],
                        'symbol' => $params['symbol'],
                        'time' => $block['time'],
                        'method' => $data['Transaction']['MethodName'],
                        'memo' => isset($params['memo'])?trim($params['memo']):'',
                        'to_chainid' => isset($params['toChainId'])?$params['toChainId']:'',
                        'from_chainid' => isset($params['fromChainId'])?$params['fromChainId']:''
                    ];
                }else {
                    //CrossChainReceiveToken跨连接收 解码transaction数据
                    $request = [
                        'transferTransactionBytes'=>$params['transferTransactionBytes']
                    ];

                    $res_json = $this->request($this->base58_url, json_encode($request));
                    $res = json_decode($res_json, true);
                    if($res['status'] == 500){
                        $data = [];
                    }else {
                        $response = $res['data'];
                        $data = [
                            'tx_id' => $data['TransactionId'],
                            'chain_id' => $block['chain_id'],
                            'block_height' => $block['height'],
                            'block_hash' => $block['hash'],
                            'address_from' => $data['Transaction']['From'],
                            'address_to' => $response['address'],
                            'quantity' => $response['amount'],
                            'tx_status' => $data['Status'],
                            'symbol' => $response['symbol'],
                            'time' => $block['time'],
                            'method' => $data['Transaction']['MethodName'],
                            'memo' => isset($response['memo']) ? trim($response['memo']) : '',
                            'to_chainid' => isset($response['to_chain_id']) ? $response['to_chain_id'] : '',
                            'from_chainid' => isset($params['fromChainId']) ? $params['fromChainId'] : ''
                        ];
                    }
                    //$data = $item;
                }
            }
        }, $blockinfo);
        $list = array_filter($transactions);

        return $list;
    }


    /**
     * 插入用户交易记录表
     * @param array $transaction
     * @return string
     */
	private function add_transaction(array $transaction) {
        $where['tx_id'] = $transaction['tx_id'];
        $where['chain_id'] = $transaction['chain_id'];
        $where['address_from'] = $transaction['address_from'];
        $where['address_to'] = $transaction['address_to'];
        $params = json_decode($transaction['params_to'], true);
        $where['symbol'] = $params['symbol'];

        $mdl_user_transaction = $this->db('index', 'user_transaction', 'master');
        $user_transaction = $mdl_user_transaction->getByWhere($where);

        /* 已插入的数据无需再次插入 */
        if (empty($user_transaction)) {
            $insertData = [];
            $insertData['tx_id'] = $transaction['tx_id'];
            $insertData['chain_id'] = $transaction['chain_id'];
            $insertData['address_from'] = $transaction['address_from'];
            $insertData['address_to'] = $transaction['address_to'];
            $insertData['tx_status'] = $transaction['tx_status'];
            $insertData['time'] = $transaction['time'];
            $insertData['time_stamp'] = strtotime($transaction['time']);
            $insertData['method'] = $transaction['method'];
            $insertData['quantity'] = $transaction['quantity'];
            $insertData['symbol'] = $transaction['symbol'];
            $insertData['block_height'] = $transaction['block_height'];
            $insertData['memo'] = $transaction['memo'];
            $insertData['to_chainid'] = $transaction['to_chainid'];
            $insertData['from_chainid'] = $transaction['from_chainid'];

            file_put_contents("intrans.txt", print_r($insertData, true), FILE_APPEND);


            return $mdl_user_transaction->insert($insertData);
        } else {
            $msg = 'tx_id:'.$transaction['tx_id'].'时间:'.date('Y-m-d H:i:s').'数据已存在无需插入'.PHP_EOL;
            $this->logScreen($msg, 1);
            return false;
        }

    }


}

$task = new task_transSync();
$task->doRequest();