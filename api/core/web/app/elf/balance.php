<?php
/**
 * 获取用户余额.
 * User: Jett
 * Date: 2019/6/6
 * Time: 4:39 PM
 */
require_once __DIR__.'/base.php';
require_once __DIR__.'/../Buffer.php';
class app_elf_balance extends app_elf_base
{

    public function doRequest()
    {
        $address = trim(post('address'));
        $contractAddress = trim(post('contractAddress'));
        $symbol = trim(post('symbol'));
        $From = 'CdhF6J7Ub64m3KM44GY1kpBzVeWqd96MQX2MU8HNL8BNrWFGC';
        $privKey = "541acf8b44ce79ff3510d6a9844f8e318062adc2de5edfd0b13e3e177d4e15d2";
        $currency = trim( post( 'currency' ) );
        $real =  post('real')?intval(post('real')):0;

        if ( empty( $address ) || empty($contractAddress) ) return $this->error( __( '参数错误' ) );
        if(!preg_match('/^[A-Za-z0-9]+$/i', $address)) return $this->error( __( '地址格式不正确' ) );

        //app请求余额
        if($real == 0){
            $cachekey = "elf:balance:{$this->chain}:{$address}:{$symbol}";
            $b2 = $this->redis()->get($cachekey);
            $b = ['balance'=>$b2?$b2:"0.00"];
            $data = $this->formatResult($b, $symbol, $currency);
            $this->success('', $data);
        }

        //后台脚本获取真实余额
        ###转化地址格式
        $value = $this->getBase64($address);
        $param = '{"symbol":"' . $symbol . '","owner":{"value":"' . $value . '"}}';
        try {
            $chain = $this->service('rpc')->func('blockChain/chainStatus')->params();
            if (!$chain || !$this->is_json($chain)) {
                throw new Exception("chainStatus:request error");
            }
            $chain = json_decode($chain, true);
            if($chain['Error']){
                throw new Exception("chainStatus:request error".$chain['Error']['Message']);
            }
            //构造交易参数
            $transaction = call_user_func(function ($data) {
                return json_encode($data);
            }, [
                'From' => $From,
                'To' => $contractAddress,
                'RefBlockNumber' => $chain['LongestChainHeight'],
                'RefBlockHash' => $chain['LongestChainHash'],
                'MethodName' => 'GetBalance',
                'Params' => "_param_"
            ]);
            $transaction = str_replace('"_param_"', "'" . $param . "'", $transaction);
            //发起交易，为序列化交易参数
            $res = $this->service('rpc')->func('blockChain/rawTransaction')->params($transaction, 1);
            if (!$res || !$this->is_json($res)) {
                throw new Exception("rawTransaction:request error");
            }
            $res = json_decode($res, true);
            if($res['Error']){
                throw new Exception("rawTransaction:request error:".$res['Error']['Message']);
            }
            $rawTransaction = $res['RawTransaction'];

            //获取签名参数
            $sig = $this->signature($privKey, $rawTransaction);

            //整理交易参数
            $sendTrans = [
                "RawTransaction"=> $rawTransaction,
                "Signature"=> $sig,
                //"ReturnTransaction"=> true
            ];
            $sendTrans = json_encode($sendTrans);

            $res = $this->service('rpc')->func('blockChain/executeRawTransaction')->params($sendTrans, 1);
            if (!$res || !$this->is_json($res)) {
                throw new Exception("sendRawTransaction:request error");
            }
            $res = json_decode($res, true);

            //获取所有合约币
            $decimals = $this->decimal[$symbol];
            $b['balance'] = $res['balance']??0;
            //$b['balance'] = $this->del0(number_format($b['balance'], $decimals, '.',''));
            $b['balance'] = $this->formatBalanceB($b['balance'], $decimals);

            $data = $this->formatResult($b, $symbol, $currency);
            $this->returnSuccess('', $data);

        } catch (Exception $ex) {
            //echo "Catch Exception:" . $ex->getMessage() . "\r\n";
            $this->returnError($ex->getMessage());
        }

    }

    private function getBase64(string $address):string
    {
        $buffer = Buffer::getbytes(Buffer::base58_decode($address));
        $data = array_slice($buffer, 0, -4);
        $value =  base64_encode(Buffer::tostr($data));
        return $value;
    }

    private function signature($privKey, $rawTxn){
        $ec = new \Elliptic\EC('secp256k1');
        //将参数从16进制解析成二进制字符串
        $hex = pack('H*', $rawTxn);
        //sha256加密
        $hash = hash('sha256', $hex);
        //$key = $ec->genKeyPair();
        $key = $ec->keyFromPrivate($privKey);
        $signature = $key->sign($hash, ['canonical' => true]);
        $sig = $signature->r->toString('hex', 32) . $signature->s->toString('hex', 32) . '0' . $signature->recoveryParam;
        return $sig;
    }

    private function formatResult($b, $symbol, $currency){
        //矿工费
        $fee = $this->redis()->get("coin_fee");
        if(!$fee) {
            $mdl_fee = $this->db('index', 'coin_fee');
            $fee = $mdl_fee->getList(null, array('coin' => 'elf'), 'id asc');
            $this->redis()->set("coin_fee", $fee, 1800); //30分钟
        }
        //汇率
        $rate = $this->getExchangeRate( $symbol, $currency );
        //美元汇率
        $usd_rate = $this->getExchangeRate($symbol, "USD");
        $data = [
            'TransactionId'=>"",
            'Status'=>"",
            'balance'=>$b,
            'fee'=>$fee,
            'rate'=>$rate,
            'usd_rate' => $usd_rate,
        ];
        return $data;
    }

}