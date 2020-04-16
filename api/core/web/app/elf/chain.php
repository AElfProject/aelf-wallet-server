<?php
/**
 * 当前链信息.
 * User: Jett
 * Date: 2019/6/22
 * Time: 4:46 PM
 */

require_once __DIR__.'/base.php';

class app_elf_chain extends app_elf_base
{

    public function doRequest(){
        try {
            $chain = $this->service('rpc')->func('blockChain/chainStatus')->params();
            if (!$chain || !$this->is_json($chain)) {
                throw new Exception("chainStatus:request error");
            }
            $chain = json_decode($chain,true);
            $chain['ChainId'] = $this->formatChainName($chain['ChainId']);
            $this->returnSuccess('', $chain);
        }catch (Exception $ex){
            $this->returnError($ex->getMessage());
        }
    }

}