<?php
/**
 * 更新用户余额
 * redis 队列
 */

require_once '../task.php';

class task_updateBalance extends task
{

    private $balance_url;
    private $queueName = 'balance_push_queue';
    private $init;

    function doRequest()
    {
        $inits = getopt('', ['init:']);
        $this->init = $inits['init']?intval($inits['init']):0;

        set_time_limit(0);
        $this->interval();

        $this->balance_url = $GLOBALS['API_URL']."/app/elf/balance";
    }

    function interval()
    {
        var_dump($this->init);
        $this->queueName = $this->queueName.$this->init;
        while (true) {
            $queueSize = $this->redis()->Llen($this->queueName);
            if ($queueSize > 0) {
                $this->logScreen("queue length:{$queueSize}");
            }else{
                $this->logScreen("waiting……");
                sleep(5);
                continue;
            }
            $queueData = $this->redis()->lPop($this->queueName);
            $this->updateBalace($queueData);
        }
    }

    private function updateBalace($transaction)
    {
        $transaction_0 = $transaction;
        $transaction = json_decode($transaction, true);
        $cacheName = 'getAllContracts';
        $allContracts = $this->redis()->get($cacheName);

        if($this->init == 1) {
            if ($transaction['init'] == 1) {  //初始化余额
                $this->logScreen("init……");
                foreach ($allContracts as $item) {
                    foreach ($item as $item2) {
                        $trans = [
                            'address' => $transaction['address'],
                            'symbol' => $item2['symbol'],
                            'chainid' => $item2['chain_id'],
                            'contract_address' => $item2['contract_address']
                        ];
                        $this->logScreen("start:" . json_encode($trans));
                        $this->updateBalanceCache($trans, 1);
                    }
                    sleep(1);
                }
            } else {
                $this->redis()->rPush($this->queueName, $transaction_0);
                sleep(1);
            }
        }

        if($this->init == 0) {
            if ($transaction['init'] == 0) {
                $this->logScreen("start:" . json_encode($transaction));
                //$ct = array_column($allContracts[$transaction['chainid']], 'contract_address', 'symbol');
                $cross_info = $this->getConfig('chains');
                $cross_info2 = json_decode($cross_info, true);
                $cross_info3 = array_column($cross_info2,'contract_address', 'name');
                $trans = [
                    'address' => $transaction['address'],
                    'symbol' => $transaction['symbol'],
                    'chainid' => $transaction['chainid'],
                    //'contract_address' => $ct[$transaction['symbol']]
                    'contract_address' => $cross_info3[$transaction['chainid']]
                ];
                $this->logScreen("params:" . json_encode($trans));
                $this->updateBalanceCache($trans);
            } else {
                $this->redis()->rPush($this->queueName, $transaction_0);
                sleep(1);
            }
        }
        $this->logScreen("end.");
    }

    private function updateBalanceCache($trans, $init=0){
        $params = [
            'device' => 'Android',
            'udid' => 1,
            'version' => 3.7,
            'demon' => 1,
            'address' => $trans['address'],
            'symbol' => $trans['symbol'],
            'chainid' => $trans['chainid'],
            'contractAddress' => $trans['contract_address'],
            'real'=>1,
            'demon'=>1
        ];
        if($init == 1){
            $params['udid'] = 2;
        }

        for($i=0; $i<5; $i++) {
            $res = $this->request($this->balance_url, $params);
            if ($this->is_json($res)) {
                $res = json_decode($res, true);
                if ($res['status'] == 200) {
                    break;
                }
            }
        }

        if ($res['status'] == 200) {
            $cachekey = "elf:balance:{$trans['chainid']}:{$trans['address']}:{$trans['symbol']}";
            $bl = $res['data']['balance']['balance'];
            $this->logScreen("[{$cachekey}]:{$bl}");
            $this->redis()->set($cachekey, $bl);
        }else{
            var_dump($res);
        }
    }

    protected function request($url, $data = array())
    {
        //$data是字符串，则application/x-www-form-urlencoded
        //$data是数组，则multipart/form-data

        //$headers = array();
        //$headers[] = "Content-type: text/xml; charset=utf-8";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($headers) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            $output = array('errno' => $errno, 'error' => curl_error($curl));
            $output['detail'] = curl_getinfo($curl);
        }
        curl_close($curl);
        return $output;
    }
}

$task = new task_updateBalance();
$task->doRequest();