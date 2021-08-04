<?php
/**
 * Created by PhpStorm.
 * User: Jett
 * Date: 2019-10-22
 * Time: 14:31
 */

require_once __DIR__.'/base.php';

class app_elf_rcv_txid extends app_elf_base{

    public function doRequest(){
        $txid_from = trim( post( 'txid_from' ) );
        $txid_to = trim( post( 'txid_to' ) );
        $address = trim( post( 'address' ) );

        try{
            if(empty($txid_from) || empty($txid_to)){
                throw new Exception(__('参数错误'));
            }
            $mdl_cross_chain_transaction = $this->db('index', 'cross_chain_transaction', 'master');
            $mdl_cross_chain_transaction->updateByWhere(['rcv_txid'=>$txid_to], ['txid'=>$txid_from,'from_address'=>$address]);
            $this->success('');
        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }
    }
}