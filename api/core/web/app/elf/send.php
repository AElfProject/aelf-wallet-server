<?php
/**
 * 发送广播
 * User: David
 * Date: 2019/03/12
 * Time: 17:07 PM
 */

require_once __DIR__.'/base.php';

class app_elf_send extends app_elf_base {
    public function doRequest(){
        $rawtx = trim( post( 'rawtx' ) );
        $txid_from = trim( post( 'txid_from' ) );
        /*RPC请求参数
        $param = [
              "jsonrpc"=>"2.0",
              "method"=>"BroadcastTransaction",
              "params"=>[
                        "rawTransaction"=>$rawtx
              ],
              "id"=> 1
        ];
        */
        if ( empty( $rawtx ) ) return $this->error( __( '参数错误' ) );

        //WebApi 请求参数
        $param = json_encode(['RawTransaction'=>$rawtx]);
        $res = $this->service('rpc')->func('blockChain/sendTransaction')->params($param,1);
        $res = json_decode($res,true);
        if ($res['TransactionId']){
            if($txid_from){
                $mdl_cross_chain_transaction = $this->db('index', 'cross_chain_transaction', 'master');
                $mdl_cross_chain_transaction->updateByWhere(['rcv_txid'=>$res['TransactionId']], ['txid'=>$txid_from]);
            }
            //$res = json_decode($res,true);
            return $this->returnSuccess('', $res['TransactionId']);
        }else{
            return $this->returnError($res['Error']['Message']);
        }

    }
}