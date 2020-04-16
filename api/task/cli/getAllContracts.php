<?php
/**
 * 获取所有合约
 */
require_once '../task.php';

class getAllContracts extends task
{
    public function doRequest()
    {
        while (true){
            $this->interval();
            echo 'sleep......'.PHP_EOL;
            sleep(1 * 60);
        }
    }

    private function interval()
    {
        $cacheName = 'getAllContracts';

        $param = [
            'limit'=> 50,
            'page'=>0,
            'order'=>'DESC'
        ];

        $apiConfig = $this->getConfig('api_config');
        $apiConfig = json_decode($apiConfig, true);

        $res = [];
        foreach ($apiConfig['history_api'] as $k=>$item) {
            $i = 0;
            while (true) {
                $url = "{$item}/api/contract/contracts";

                $param['page'] = $i;
                $params = http_build_query($param);
                $params && $params = '?' . $params;
                $url = "{$url}{$params}";
                echo $url . PHP_EOL;

                $_res = $this->request($url);
                $_res = json_decode($_res, true);
                if(empty($_res['transactions'])){
                    break;
                }
                $res = array_merge($res, [$_res]);
                $i++;
            }
        }

        $lt = [];
        foreach ($res as $item) {
            foreach ($item['transactions'] as $item2) {
                if($item2["symbol"]) {
                    $lt[$item2['chain_id']][] = $item2;
                }
            }
        }
        $this->redis()->set($cacheName, $lt);
        echo date('Y-m-d H:i:s')."更新缓存".PHP_EOL;

    }
}

$blockSync = new getAllContracts();
$blockSync->doRequest();