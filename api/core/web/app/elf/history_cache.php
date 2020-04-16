
<?php
/**
 * 重新生成交易记录缓存.
 * User: David
 * Date: 2019/01/24
 * Time: 17:30 PM
 */

require_once __DIR__.'/base.php';

class app_elf_history_cache extends app_elf_base {

    public function doRequest(){

        $address = trim( post( 'address' ) );
        $contractAddress = trim( post( 'contractAddress' ) );
        $symbol = trim( post( 'symbol' ) );

        //清除并生成新的缓存
        $pagesize = 10;
        $page = 1;
        $lang = str_replace('-', '_', get2( 'lang' ));
        $cacheName = "trans:onchain_elf_history:{$this->chain}:{$address}:{$contractAddress}:{$symbol}:".$lang.'_'.$pagesize.'_'.$page;
        $this->redis()->delete( $cacheName );

        $this->getElfChain($symbol, $address, $contractAddress, $pagesize, $page);

    }


}