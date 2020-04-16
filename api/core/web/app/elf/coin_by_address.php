<?php
/**
 * 币种余额、行情.
 * User: David
 * Date: 2019/03/06
 * Time: 10:27 AM
 */

require_once __DIR__.'/base.php';

class app_elf_coin_by_address extends app_elf_base {

    public function doRequest(){
        $parent = $parent??'elf';
        $address = trim( post( 'address' ) );
        $currency = trim( post( 'currency' ) );
        $p = post('p')?intval(post('p')):0;

        if ( empty( $address ) ) return $this->error( __( '参数错误' ) );
        if(!preg_match('/^[A-Za-z0-9]+$/i', $address)) return $this->error( __( '地址格式不正确' ) );
        $cacheName = "elf:coins_by_address:{$address}:{$this->chain}:{$parent}:{$currency}:{$p}";
        $cache = $this->redis()->get( $cacheName );
        if ( $cache && $cache['list'] && app::REDISENV) {
            return $this->returnSuccess('', ['list' => $cache['list'], 'fee'=>$cache['fee'],'chain'=>$cache['chain']]);
        }

        //获取该地址下所有的合约币
        $tokens = $this->getAddressTokensFromLocal($address, $this->chain);
        $tokens = $this->defaultTokens($tokens, $address);
        //die($tokens);

        //获取合约币资产、行情
        if($tokens){
            $list = $this->getBalanceAddressLocal($tokens, $currency);
            $list = array_values($list);
            $list =  $list[0];
            //print_r($list);
            foreach ($list as $k=>$item){
                $item['amount_o'] = $item['amount'];
                $item['amount'] = $this->formatBalance($item['amount'], $this->decimal[$item['symbol']]);
                $item['balance_o'] = $item['balance'];
                $item['balance'] = $this->formatBalance($item['balance'], $this->decimal[$item['symbol']]);
                $list[$k] = $item;
            }
        }else{
            $list = (array)[];
        }
        //var_dump($list);

        //获取矿工费用 正序
        $mdl_fee = $this->db('index','coin_fee');
        $fee = $mdl_fee->getList(null, array('coin' => 'elf'), 'id asc');
        $fee = array_map(function ($item){
            $item['fee'] = $this->del0($item['fee']);
            return $item;
        }, $fee);

        //获取区块链所有的信息  --写到定时任务
        //$res  = $this->getAllContract();
        $chains = array_keys($res);
        $chains = array_map(function ($data){
            return $this->formatChainName($data);
        }, $chains);

        $cache = [
            'list' => $list,
            'fee'=>$fee,
            'chain'=>$chains
        ];
        $list && $this->redis()->set($cacheName, $cache, 5*60 );

        return $this->returnSuccess( '', ['list' => $this->format_elements_to_string($list), 'fee'=>$fee,'chain'=>$chains] );
    }

    /**
     *tokens为空的处理
     */
    private function defaultTokens($tokens, $address){
        if($tokens){
            return $tokens;
        }
        $cross_info = $this->getConfig('chains');
        $cross_info_json = json_decode($cross_info, true);
        $cross =  array_column($cross_info_json, null, 'name');
        $tokens[0] = [
            'address' => $address,
            'contract_address' =>$cross[$this->chain]['contract_address'],
            'symbol' => $cross[$this->chain]['symbol'],
            'chain_id' =>$cross[$this->chain]['name'],
        ];

        return $tokens;
    }

}