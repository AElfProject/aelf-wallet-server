<?php

/**
 * 更新用户交易信息
 *
 */

require_once '../task.php';

class task_updateTrans extends task
{

    private $trans_url = "";//交易详情api url

    function doRequest()
    {
        set_time_limit(0);
        $this->trans_url = "{$this->scaner_node}/api/blockChain/transactionResult";

        while (true) {
            $this->interval();
            sleep(10);
            //break;
        }
    }

    function interval()
    {
        $list = $this->getUserTransaction();
        if($list) {
            foreach ($list as $k => $transaction) {
                $this->updateTransaction($transaction);
            }
        }else{
            $this->logScreen("waiting……");
            sleep(10);
        }
    }

    /**
     * 获取未完成的数据
     * @return mixed
     */
    private function getUserTransaction()
    {
        $where['tx_status'] = "Pending";
        $mdl_user_transaction = $this->db('index', 'user_transaction');
        $userTransaction = $mdl_user_transaction->getList(null, $where, 'update_time asc', 100);

        return $userTransaction;
    }

    private function updateTransaction(array $transaction)
    {

        $this->logScreen("start……");

        $apiConfig = $this->getConfig('api_config');
        $apiConfig = json_decode($apiConfig, true);
        $this->trans_url = "{$apiConfig['web_api'][$transaction['chain_id']]}/api/blockChain/transactionResult";
        $url = $this->trans_url . "?transactionId={$transaction['tx_id']}";

        $res = $this->request($url);
        $res = json_decode($res, true);

        if (isset($res['Status'])) {
            $tx_status = $res['Status'];
            $updateData['tx_status'] = $tx_status;
        }

        $updateData['update_time'] = time();
        $mdl_user_transaction = $this->db('index', 'user_transaction');

        if ($mdl_user_transaction->updateByWhere($updateData, ['id' => $transaction['id']])) {
            if ($tx_status == 'Mined') {    //如果状态为成功则发推送
                $transaction['tx_status'] = 'Mined';
                $msg =  'id:' . $transaction['id'] . 'update data success'. PHP_EOL;
                $this->logScreen($msg);
                $this->transaction_push_queue($transaction);
            }

        }

        $this->logScreen("end.");

    }
}

$task = new task_updateTrans();
$task->doRequest();