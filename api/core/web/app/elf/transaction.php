<?php
/**
 * 交易详情.
 * User: Jett
 * Date: 2019/6/22
 * Time: 4:46 PM
 */

require_once __DIR__.'/base.php';

class app_elf_transaction extends app_elf_base
{

    public function doRequest(){
        $txid = trim(post('txid'));
        $address = trim(post('address'));
        $currency = trim(post('currency'));
        $chainid = trim(post('chainid_c'));

        if($chainid && strtolower($chainid) == "aelf"){
            $this->chain = strtoupper($chainid);
        }else {
            $this->chain = $chainid;
        }
        try {
            if (!$txid || !$address || !$currency)  throw new Exception(__('参数错误'));
            $lang = get2( 'lang' );
            $cacheName = "elf:transaction:{$address}:{$this->chain}:{$txid}:{$currency}:{$lang}";
            $cache = $this->redis()->get($cacheName);
            //$cache = [];
            if($cache && app::REDISENV) {
                $this->success('', $cache);
            }

            $res = $this->service('rpc')->func('blockChain/transactionResult')->params(['transactionId'=>$txid]);
            if (!$res || !$this->is_json($res)) {
                throw new Exception("transactionResult:request error");
            }
            $res = json_decode($res, true);
            /*
            if($res['Error']){
                throw new Exception($res['Error']['Message']);
            }
            */

            //判断交易状态
            $status = 0;
            if ($res['Status'] == "MINED"){
                $status = 1;
            }elseif($res['Status'] == "FAILED"){
                $status = -1;
            }elseif($res['Status'] == "PENDING"){
                $status = 0;
            }elseif($res['Status'] =='NotExisted'){
                //throw new Exception("transactionResult:request error2");
                throw new Exception("transaction NotExisted");
            }
            $Transaction = $res['Transaction'];
            $category = $Transaction['From']==$address?'send':'receive';
            //$Params = json_decode($Transaction['Params'], true);
            $Params = $this->handParams($Transaction);

            //汇率
            $rate = $this->getExchangeRate($Params['symbol'], $currency);
            //美元汇率
            $usd_rate = $this->getExchangeRate($Params['symbol'], "USD");

            /*从交易记录表中获取时间和chainId，如果交易记录表中没有该条记录，再从区块上查询*/
            $userTransactionInfo = $this->db('index', 'user_transaction')->getByWhere(['tx_id' => $txid]);
            if ($userTransactionInfo) {
                $chainId = $userTransactionInfo['chain_id'];
                $time = $userTransactionInfo['time'];
            } else {
                $blockData = $this->service('rpc')->func('blockChain/blockByHeight')->params(['blockHeight' => $Transaction['RefBlockNumber']]);
                $blockData = json_decode($blockData, true);
                $chainId = $blockData['Header']['ChainId'] ?? $this->formatChainName('AELF');
                $time = $blockData['Header']['Time'] ?? date('Y-m-d H:i:s');
            }

            $from = $Params['from']?$Params['from']:$Transaction['From'];
            //跨链交易的状态
            $status_o = $status;
            if($from == $address) {
                $status = $this->valiteCrossChainStatus($txid, $status, $Transaction['MethodName'], $from);
            }

            //交易手续费
            $fee = "0.00";
            //var_dump($res);
            if($res["TransactionFee"]["Value"]){
                $res["TransactionFee"]["Value"] = array_values($res["TransactionFee"]["Value"]);
                $fee = $this->formatBalanceB($res["TransactionFee"]["Value"][0], $this->decimal[$Params['symbol']]);
            }

            $errMsg = $res['Error']?(isset($res['Error']['message'])?$res['Error']['message']:$res['Error']):'';
            $data = [
                "chain" => $chainId,
                "category" => $category,
                'symbol' => $Params['symbol'],
                "txid" => $txid,
                "amount_o" => strval($Params['amount']),
                "amount" => $this->formatBalanceB($Params['amount'], $this->decimal[$Params['symbol']]),
                "block" => $res['BlockNumber'],
                "time" => strtotime($time),
                "nonce" => "",
                "fee" => $fee,
                "feeSymbol"=>$this->getFeeSymbol($chainid),
                "gasLimit" => "",
                "gasPrice" => "",
                "gasUsed" => "",
                "status" => $status,
                "status_o"=>$status_o,
                "from" => $from,
                "to" => $Params['to'],
                "statusText" => $this->getCommonStatusText($status, $category),
                "addressList" => [],
                "timeOffset" => time(),
                "confirmations" => 0,
                "completed" => app::completed,
                'memo'=>$Params['memo']??'',
                'method'=>strtolower($Transaction['MethodName']),
                'from_chainid'=>$Params['from_chainid'],
                'to_chainid'=>$Params['to_chainid'],
                'rate'=>$rate,
                'usd_rate'=>$usd_rate,
                'err_msg'=>$errMsg
            ];

            //只有mined时才缓存数据
            if($status == 1) {
                $this->redis()->set($cacheName, $data, 3600*24);   //1天
            }
            $this->success('', $data);
        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }
    }

    //处理交易参数params
    protected function handParams(array $trans):array {
        $params = [
            'from' => '',
            'to' => '',
            'symbol' => '',
            'amount' => 0,
            'from_chainid' => '',
            'to_chainid' => '',
            'memo' => ''
        ];
        if(in_array($trans['MethodName'], ['Transfer','CrossChainTransfer','CrossChainReceiveToken']) === false){
            return $params;
        }
        $ps = json_decode($trans['Params'], true);
        if(in_array($trans['MethodName'], ['Transfer','CrossChainTransfer']) !==false){
            //同链、跨链转出
            $params = [
                'from' => $trans['From'],
                'to' => $ps['to'],
                'symbol' => $ps['symbol'],
                'amount' => $ps['amount'],
                'memo' => $ps['memo'],
                'from_chainid' => $this->chain,
                'to_chainid' => $ps['toChainId']?$this->r_nodes[$ps['toChainId']]:$this->chain
            ];
        }else{
            //跨链接收
            $request = [
                'transferTransactionBytes'=>$ps['transferTransactionBytes']
            ];
            $res_json = $this->request($this->base58_url, json_encode($request));
            $res = json_decode($res_json, true);
            if($res['status'] == 200){
                $response = $res['data'];
                $params = [
                    'from' => $trans['From'],
                    'to' => $response['address'],
                    'symbol' => $response['symbol'],
                    'amount' => $response['amount'],
                    'memo' => $response['memo'],
                    'from_chainid' => $ps['fromChainId']?$this->r_nodes[$ps['fromChainId']]:'',
                    'to_chainid' => $response['to_chain_id'] ? $this->r_nodes[$response['to_chain_id']] : ''
                ];
            }
        }
        return $params;
    }

}