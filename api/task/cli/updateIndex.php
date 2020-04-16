<?php
/**
 * 更新索引.
 * User: Jett
 * Date: 2019-10-10
 * Time: 15:43
 */

require_once '../task.php';

class task_updateIndex extends task{

    private $queenName="cross_chain_index";


    //配置从配置文件读取
    private $main_chain_id = "9992731"; //主链编码后id 【AELF】
    private $issue_chain_id = "9992731";    //发行token所在chainid【AELF】

    //优化直接从配置里面读取
    private $index_url = "";  //查询索引所用url
    private $api_config;

    public function doRequest(){

        $i = 0;
        while (true){
            $api_config = json_decode($this->getConfig('api_config'), true);
            $this->api_config = $api_config;
            $this->main_chain_id = $api_config['base58_nodes']['AELF'];
            $this->issue_chain_id = $api_config['base58_nodes']['AELF'];

            //临时采用自身服务器的api端口
            $this->index_url = $api_config['history_api']['AELF']."/api/cross-chain/is-ready-to-receive";
            //var_dump($this->main_chain_id);
            //var_dump($this->issue_chain_id);
            //var_dump($this->index_url);

            $this->interval();
            $i++;
            if($i == 10) {
                $i = 0;
                echo "resting……".PHP_EOL;
                sleep(30);
            }else{
                sleep(10);
            }
        }
    }

    private function interval(){
        //获取队列值
        $len = $this->redis()->Llen($this->queenName);
        if($len){
            $queenData = $this->redis()->lPop($this->queenName);
            $this->handle($queenData);
        }else {
            echo date('Y-m-d H:i:s')." 队列为空".PHP_EOL;
            sleep(60);
        }
    }

    private function handle($txid){
        $mdl_cross_chain = $this->db('index', 'cross_chain_transaction','master');
        $trasactions = $mdl_cross_chain->getByWhere(['txid' => $txid]);
        if(!$trasactions){
            echo date('Y-m-d H:i:s')." txid{$txid}表中无数据".PHP_EOL;
            return;
        }

        //判断该交易是否成功
        if($this->transIsFaild($trasactions, $txid) == true){
            echo date('Y-m-d H:i:s')." txid{$txid}交易失败".PHP_EOL;
            return;
        }

        //获取区块链信息
        $cross_info = json_decode($this->getConfig('chains'), true);
        $cross_info2 = array_column($cross_info, "node", 'name');
        /*
        $cross_info2 = array_map(function ($data){
            return preg_replace("/^(.*?)\/$/", "$1", $data);
        }, $cross_info2);
        */

        $query = [
            //'send' => $cross_info2[$trasactions['from_chain']],
            //'receive' =>  $cross_info2[$trasactions['to_chain']],
            'send' => $this->api_config['scaner_node'][$trasactions['from_chain']],
            'receive' =>  $this->api_config['scaner_node'][$trasactions['to_chain']],
            'main_chain_id' => $this->main_chain_id,
            'issue_chain_id' => $this->issue_chain_id,
            'cross_transfer_tx_id' => $txid
        ];
        $queryStr = http_build_query($query);
        $res = $this->request($this->index_url."?".$queryStr."&no_cache=1");
        echo $this->index_url."?".$queryStr."&no_cache=1".PHP_EOL;
        //die;
        $json = json_decode($res, true);
        if(!$res || !$json){
            echo date('Y-m-d H:i:s')." 索引接口请求网络错误".PHP_EOL;
            return ;
        }

        //查看txid是否已索引
        if(isset($json['isReadyToReceive']['error']) && $json['isReadyToReceive']['error'] != 1){
            $mdl_cross_chain->update(['status'=>1], $trasactions['id']);
            echo date('Y-m-d H:i:s')." [{$trasactions['id']}]索引结果isReadyToReceive=true".PHP_EOL;
            //已索引,通知接收者
            $this->notify($trasactions);
        }else{
            echo date('Y-m-d H:i:s')." [{$trasactions['id']}]索引结果isReadyToReceive=false======>".json_encode($json['isReadyToReceive']['message']).PHP_EOL;
            $this->redis()->rPush($this->queenName, $txid);
        }
        return;
    }

    //通知推送消息
    private function notify($transaction){

        //精度计算
        $transaction['amount'] = $this->formatBalance($transaction['amount'], $this->decimal[$transaction['symbol']]);

        //更新缓存
        //清除from/to相关的缓存
        $this->updateCache(['address_from'=>$transaction['from_address'], 'address_to'=>$transaction['to_address']]);

        /*收款通知*/
        $msg = "您有一笔转账已提交，金额：%s";
        //$lang = $this->getLangByAddress($transaction['address_to']);

        $toUserInfo = $this->getUserByAddress($transaction['from_address']);
        $lang = $this->redis()->get( 'lang/' . $toUserInfo['id'] );

        $currency = $this->getCurrencyByAddress($transaction['from_address']);
        $queueData = [
            'address' => $transaction['from_address'],
            'message' => [
                'title' => ___("ELF钱包", $lang),
                'content' => ___($msg, $lang,$transaction['amount']. strtoupper($transaction['symbol'])),
                'extras'=>[
                    'txid'=>$transaction['txid'],
                    'from_chain'=>$transaction['from_chain'],
                    'to_chain'=>$transaction['to_chain'],
                    'address'=>$transaction['from_address'],
                    'time'=>$transaction['time'],
                    'amount'=>$transaction['amount'],
                    'symbol'=>$transaction['symbol'],
                    'currency'=>$currency,
                    'type'=>1   //收款到账
                ]
            ],
        ];
        echo date('Y-m-d H:i:s').PHP_EOL;
        var_dump($queueData);

        $queueName = 'transaction_push_queue';
        $this->redis()->rPush($queueName, json_encode($queueData));
    }


    private function transIsFaild($transaction) {
        $apiConfig = $this->getConfig('api_config');
        $apiConfig = json_decode($apiConfig, true);
        $this->trans_url = "{$apiConfig['scaner_node'][$transaction['from_chain']]}/api/blockChain/transactionResult";
        $url = $this->trans_url . "?transactionId={$transaction['txid']}";
        echo $url.PHP_EOL;

        $res = $this->request($url);
        $res = json_decode($res, true);

        if (isset($res['Status']) && (in_array(strtolower($res['Status']), ["failed", "notexisted"]))) {
            $mdl_cross_chain = $this->db('index', 'cross_chain_transaction','master');
            $mdl_cross_chain->update(['status'=>4], $transaction['id']);
            return true;
        }
        return false;
    }

}

$do = new task_updateIndex();
$do->doRequest();