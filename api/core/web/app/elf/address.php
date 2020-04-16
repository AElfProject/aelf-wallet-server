<?php
/**
 * 查询elf历史交易记录
 * User: David
 * Date: 2019/01/24
 * Time: 17:30 PM
 */

require_once __DIR__.'/base.php';

class app_elf_address extends app_elf_base {
    private $pageSize = 10; //交易个数

    public function doRequest(){
        $address = trim( post( 'address' ) );
        $contractAddress = trim( post( 'contractAddress' ) );
        $symbol =  trim( post( 'symbol' ) );
        $page = (int)post( 'p' );
        $currency = trim( post( 'currency' ) );
        $type = (int)post( 'type' );

        if ( empty( $address ) ) return $this->error( __( '参数错误' ) );
        if(!preg_match('/^[A-Za-z0-9]+$/i', $address)) return $this->error( __( '地址格式不正确' ) );

        $page = max(1, $page);
        --$page;

        $data = $this->getElfChain($symbol,$address, $contractAddress, $this->pageSize, $page, $currency, $type);
        if(!$data){
            $data = array(
                'count' => 0,
                'pageCount' => 0,
                'list' => array()
            );
        }

        $this->returnSuccess('', $data);

    }

}