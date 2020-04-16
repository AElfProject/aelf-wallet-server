<?php
/**
 * 系统消息
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_transaction_notice extends app_user_base {

    public function doRequest(){
        $p = (int)post( 'p' );
        $address = $this->user['address'];

        $sql = "
        select sum(bb.aa) as cnt from (
            select count(id) as aa from cc_user_transaction where ((address_from='{$address}' and
            status_from !=2) or (address_to='{$address}' and status_to !=2)) and
            method='Transfer'
            union all
            select count(id) as aa from cc_user_transaction where address_from='{$address}' and
            method='CrossChainTransfer' and status_from !=2
            union all
            select count(id) as aa from cc_user_transaction where address_to='{$address}' and
            method='CrossChainReceiveToken' and status_to !=2
        ) as bb;
        ";
        //$where[] = "( (address_from = '".$address."' and status_from !=2 ) or (address_to = '".$address."' and status_to !=2 ))";

        $mdl_user_transaction = $this->db( 'index', 'user_transaction', 'master');

        $count = $mdl_user_transaction->query( $sql );
        $count = $count?$count[0]['cnt']:0;


        $page = $p ? $p : 1;
        $pageSize = 10;

        $sql = "select * from #@_user_transaction where ( (address_from = '".$address."' and status_from !=2 ) or (address_to = '".$address."' and status_to !=2 )) order by id desc limit ".($page - 1) * $pageSize.",".$pageSize." ";

        $sql = "
            select * from cc_user_transaction where ((address_from='{$address}' and status_from !=2) or (address_to='{$address}' and status_to !=2))  and method='Transfer'
            union 
            select * from cc_user_transaction where address_from='{$address}'   and method='CrossChainTransfer' and status_from !=2
            union 
            select * from cc_user_transaction where address_to='{$address}'   and method='CrossChainReceiveToken' and status_to !=2
            order by id desc limit ".($page - 1) * $pageSize.",".$pageSize;

        //echo $sql;

        $list = $mdl_user_transaction->query( $sql );

        $data2 = [];

        foreach ($list as $k => $transaction) {

            $res = $this->formatTransaction($address, $transaction);
            list($category, $amount, $time, $status, $from, $to, $is_read) = $res;

            //跨链交易的状态
            $status_o = $status;
            if($address == $from) {
                $status = $this->valiteCrossChainStatus($transaction['tx_id'], $status, $transaction['method'], $from);
            }

            $data2[] = [
                "id" => $transaction['id'],
                "chain" => $this->formatChainName($transaction['chain_id']),
                "category" => $category,
                'symbol' => $transaction['symbol'],
                "txid" => $transaction['tx_id'],
                "amount_o" => strval($this->del0($amount)),
                "amount" => $this->formatBalanceB($amount, $this->decimal[$transaction['symbol']]),
                "block" => $transaction['block_height'] ? intval($transaction['block_height']) : '',
                "time" => $time ? intval($time) : time(),
                "nonce" => "",
                "fee" => "0.00",
                "feeSymbol"=>$this->getFeeSymbol($transaction['chain_id']),
                "gasLimit" => "",
                "gasPrice" => "",
                "gasUsed" => "",
                "status" => $status,
                "status_o" => $status_o,
                "from" => $from,
                "to" => $to,
                "statusText" => $this->getCommonStatusText($status, $category),
                "addressList" => [],
                "timeOffset" => time() - ($transaction['time'] ? $transaction['time'] : time()),
                "confirmations" => 0,
                "completed" => app::completed,
                "is_read" => $is_read,
                "from_chainid" =>$this->getChain($transaction['from_chainid'], $transaction['chain_id']),
                "to_chainid" =>$this->getChain($transaction['to_chainid'], $transaction['chain_id']),
            ];

        }

        $unread_count = $this->unReadNoticeCount();
        $this->returnSuccess('', ['count' => $count, 'unread_count' => $unread_count, 'list' => $data2]);
    }


    public function formatTransaction($address, $transaction) {
        $category = "receive";
        $amount = $transaction['quantity'];
        $time = strtotime($transaction['time']);
        $status = 0;
        $from = $transaction['address_from'];
        $to = $transaction['address_to'];
        $is_read = 0;

        if(in_array($transaction['method'], ["CrossChainTransfer", "Transfer"])) {
            if (strtolower($from) == strtolower($address)) {
                $category = 'send';
                $is_read = $transaction['status_from'];
            }
        }else{
            if (strtolower($to) == strtolower($address)) {
                $category = 'receive';
                $is_read = $transaction['status_to'];
            }
        }

        //判断交易状态
        if ($transaction['tx_status'] == "Mined"){
            $status = 1;
        }elseif($transaction['tx_status'] == "Failed"){
            $status = -1;
        }

        return [
            $category, $amount, $time, $status, $from, $to,$is_read
        ];
    }


    private function getChain($chainid, $defaultChain){
        if($chainid){
            return $this->formatChainName($this->r_nodes[$chainid]);
        }
        return $this->formatChainName($defaultChain);
    }

}