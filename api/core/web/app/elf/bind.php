<?php
/**
 * 绑定解绑账户.
 * User: Jett
 * Date: 2019/6/1
 * Time: 4:49 PM
 */
require_once __DIR__.'/base.php';
class app_elf_bind extends app_elf_base
{
    public function doRequest(){

        $address = trim(post('address'));
        $contract_address = trim(post('contract_address'));
        $symbol = trim(post('symbol'));
        $flag = trim(post('flag'));
        $init = post('init')?intval(post('init')):0;

        if(!$address || !$symbol){
            return $this->error( __( '参数错误' ) );
        }
        if($symbol == 'ELF' && empty($contract_address)){
            //获取主链合约币地址
            $all_tokens = $this->getAllContract();
            $main = array_column($all_tokens['AELF'], 'contract_address','symbol');
            $contract_address = $main['ELF'];
        }

        $res = 0;
        if($flag == 1){ //绑定
            if($init) {
                $res = $this->bindTokensFromLocal($address);
            }else{
                $res = $this->bindTokensFromLocal($address, $this->chain, $contract_address, $symbol);
            }
        }elseif ($flag == 2){   //解绑
            //$res = $this->service('api')->func('address/unbind-token')->params($param,1);
            $this->unbindTokensFromLocal($address, $this->chain, $contract_address, $symbol);
        }

        try{
            if($res > 0){
                throw new Exception(__('不可重复绑定'));
            }
            if($init) {
                $this->updateCache($address);
            }else{
                $this->updateCache($address, $this->chain);
            }
            //成功
            $this->success(__('成功'));

        }catch (Exception $ex){
            $this->error($ex->getMessage());
        }

    }


}