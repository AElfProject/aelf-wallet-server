<?php
/**
 * 同步块信息.
 * User: Jett
 * Date: 2019/6/26
 * Time: 1:49 PM
 */
require_once '../task.php';

class blockSync extends task
{

    //获取当前区块信息[高度]
    private $chain_status = '';
    //区块信息
    private $block_info = '';
    private $limit = 100;
    private $block_height_cache = '';
    private $wait_block = [];   //待处理交易高度

    public function doRequest()
    {
        $this->chain_status = "{$this->scaner_node}/api/blockChain/chainStatus";
        $this->block_info = "{$this->scaner_node}/api/blockChain/blockByHeight";
        $this->block_height_cache = "aelf:height:".$this->chainid;

        while (true){
            $this->before();
            $this->interval();
            $this->wait_block_handle();
            $this->after();
            //sleep(1);
        }
    }

    private function interval()
    {
        $cur_height = $this->redis()->get($this->block_height_cache);
        $cur_height || $cur_height=0;
        $hheight = $this->getHeightBlock($cur_height);
        try {
            if($hheight <= $cur_height){
                sleep(3);
                throw new Exception($cur_height." current is heighest block");
            }
            for ($i = $cur_height + 1; $i <= $hheight; $i++) {
                //获取区块信息
                $url = $this->block_info . "?blockHeight={$i}&includeTransactions=true";
                $true = 0;
                $res = "";
                for($j=0; $j<5; $j ++) { //retry 5次
                    $res = $this->request($url);
                    if ($res && strpos($res, '{') !== false) {
                        $true = 1;
                        break;
                    }
                }
                if($true == 1){
                    $res = json_decode($res);
                    $block_arr = [
                        'hash' => $res->BlockHash,
                        'height' => $i,
                        'txcount' => count($res->Body->Transactions),
                        'time'=>$res->Header->Time,
                        'chain_id'=>$res->Header->ChainId,
                    ];
                    $this->redis()->set("aelf:block:{$this->chainid}:{$i}", $block_arr, 432000); //保存5天时间

                    $msg = "checked block:{$i}";
                    $this->logScreen($msg);
                } else {
                    $this->wait_block = array_merge($this->wait_block, [$i]);
                    //throw new Exception("Block Request Error");
                    $this->logScreen("Block Request Error", 1);
                }
            }
            $this->redis()->set($this->block_height_cache, $hheight);
        } catch (Exception $ex) {
            $this->logScreen($ex->getMessage());
        }
    }

    /**
     * 异常高度处理
     */
    private function wait_block_handle()
    {
        try {
            if(count($this->wait_block) == 0) return;
            $wait_block = [];
            foreach ($this->wait_block as $i) {
                $this->logScreen("处理异常高度：".$i);
                //获取区块信息
                $url = $this->block_info . "?blockHeight={$i}&includeTransactions=true";
                $true = 0;
                $res = "";
                for($j=0; $j<5; $j ++) { //retry 5次
                    $res = $this->request($url);
                    if ($res && strpos($res, '{') !== false) {
                        $true = 1;
                        break;
                    }
                }
                if($true == 1){
                    $res = json_decode($res);
                    $block_arr = [
                        'hash' => $res->BlockHash,
                        'height' => $i,
                        'txcount' => count($res->Body->Transactions),
                        'time'=>$res->Header->Time,
                        'chain_id'=>$res->Header->ChainId,
                    ];
                    $this->redis()->set("aelf:block:{$this->chainid}:{$i}", $block_arr, 432000); //保存5天时间

                    $msg = "checked block:{$i}";
                    $this->logScreen($msg);
                } else {
                    $wait_block = array_merge($wait_block, [$i]);
                    $this->logScreen("Block Request Error2", 1);
                }
            }
            $this->wait_block = $wait_block;
        } catch (Exception $ex) {
            $this->logScreen($ex->getMessage());
        }
    }

    /**
     * 获取当前同步的高度
     */
    private function getHeightBlock($cur_height){
        //本地同步的区块高度
        $height = $cur_height+$this->limit;

        //本地缓存的区块链高度
        $cur_chain_height = $this->redis()->get("aelf:chain_height:{$this->chainid}");
        if(!$cur_chain_height || ($cur_height+$this->limit) >= $cur_chain_height){
            try {
                //获取当前区块链高度
                $chain = $this->request($this->chain_status);
                if ($chain && strpos($chain, '{') !== false) {
                    $chain = json_decode($chain, true);
                    $cur_chain_height = $chain['LongestChainHeight'];

                    if($cur_chain_height>10) { //减缓扫链高度：不高于block scanner扫链程序扫链高度
                        $cur_chain_height = $cur_chain_height - 10;
                    }
                    $this->redis()->set("aelf:chain_height:{$this->chainid}", $cur_chain_height);

                    if(($cur_height+$this->limit) >= $cur_chain_height){
                        $height = $cur_chain_height;
                    }
                }else{
                    throw new Exception("Block Request Error");
                }
            }catch (Exception $ex){
                $this->logScreen($ex->getMessage());
            }
        }
        return $height;
    }
}

$blockSync = new blockSync();
$blockSync->doRequest();