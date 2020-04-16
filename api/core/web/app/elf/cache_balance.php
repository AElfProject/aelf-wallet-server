<?php
/**
 * 重新生成账户余额.
 * User: Jett
 * Date: 2019-12-27
 * Time: 16:45
 */
require_once __DIR__.'/base.php';

class app_elf_cache_balance extends app_elf_base {


    public function doRequest(){

        $from = trim( post( 'from' ) );
        $to = trim( post( 'to' ) );

        //from\to的数据格式
        /*
        [
            'address'=>$address,
            'chainid'=>$chainid,
            'symbol'=>$symbol
        ]
        */

        try{
            if (!$from || !$to){
                throw new Exception( __( '参数错误' ) );
            }
            //新增余额队列数据
            $fromJson = json_decode(stripslashes($from), true);

            $toJson = json_decode(stripslashes($to), true);
            foreach ([$fromJson, $toJson] as $item) {
                if($item['address'] && $item['symbol'] && $item['chainid']) {
                    $this->addBalanceQueue($item['address'], 1, $item['symbol'], $item['chainid']);
                }
            }

            $this->success("");
        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }
    }

}