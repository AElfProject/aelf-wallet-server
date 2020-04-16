<?php
/**
 * 获取等待跨链交易的数据列表.
 * User: Jett
 * Date: 2019-12-01
 * Time: 14:32
 */
require_once __DIR__.'/base.php';

class app_elf_waiting_cross_trans extends app_elf_base{

    public function doRequest(){
        $address = trim( post( 'address' ) );
        try{
            if(empty($address)){
                throw new Exception(__('参数错误'));
            }
            $mdl_cross_chain_transaction = $this->db('index', 'cross_chain_transaction');
            $list = $mdl_cross_chain_transaction->getList(['txid','from_chain','to_chain','from_address','to_address','time','amount','symbol','memo'],
                ['status'=>1,'from_address'=>$address,'rcv_txid is null']);

            foreach ($list as $k=>$item){
                $item['from_node'] = $this->_web_api[$item['from_chain']];
                $item['to_node'] = $this->_web_api[$item['to_chain']];
                $item['amount'] = $this->formatBalance($item['amount'], $this->decimal[$item['symbol']]);
                $list[$k] = $item;
            }

            $this->success('', ['list' => $list]);

        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }


    }


}