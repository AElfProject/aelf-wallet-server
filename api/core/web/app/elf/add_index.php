<?php
/**
 * 增加跨链索引.
 * User: Jett
 * Date: 2019-10-10
 * Time: 15:00
 */
require_once __DIR__.'/base.php';

class app_elf_add_index extends app_elf_base{

    public function doRequest(){
        $txid = trim( post( 'txid' ) );
        $from_chain = trim( post( 'from_chain' ) );
        $to_chain = trim( post( 'to_chain' ) );
        $from_address = trim( post( 'address' ) );
        $to_address = trim( post( 'to_address' ) );
        $symbol = trim( post( 'symbol' ) );
        $amount = trim( post( 'amount' ) );
        $memo = trim( post( 'memo' ) );
        try{
            if(!$txid || !$from_chain || !$to_chain || !$from_address || !$to_address || !$symbol || !$amount){
                throw new Exception(__('参数错误'));
            }

            if(strtolower($from_chain) == "aelf"){
                $from_chain = strtoupper($from_chain);
            }
            if(strtolower($to_chain) == "aelf"){
                $to_chain = strtoupper($to_chain);
            }

            $amountFormat = str_replace(',','',$amount);
            $all_tokens = $this->getAllContract();
            $main = array_column($all_tokens[$from_chain], 'decimals','symbol');
            $decimals = pow(10,$main[$symbol]);
            $amountFormat = floor($amountFormat*$decimals)/$decimals;

            $data = [
                'txid' => $txid,
                'from_chain' => $from_chain,
                'to_chain' => $to_chain,
                'from_address' => $from_address,
                'to_address' => $to_address,
                'time' =>time(),
                'symbol' => $symbol,
                'amount' => $amountFormat,
                'memo' => $memo
            ];

            $mdl_cross_chain = $this->db('index', 'cross_chain_transaction','master');
            $res = $mdl_cross_chain->insert($data);
            if ($res) {

                //txid插入队列，定时任务扫描txid索引是否完成
                $queenName="cross_chain_index";
                $this->redis()->rPush($queenName, $txid);

                $this->returnSuccess(__('成功'));
            } else {
                $this->returnError(__('失败'));
            }

        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }

    }

}

