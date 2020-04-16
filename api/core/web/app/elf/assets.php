<?php
/**
 * 资产管理
 * User: David
 * Date: 2019/01/24
 * Time: 17:30 PM
 */

require_once __DIR__.'/base.php';

class app_elf_assets extends app_elf_base {

    public function doRequest(){

        $address = trim( post( 'address' ) );
        $currency = trim( post( 'currency' ) );
        $p = post('p')?intval(post('p')):0;

        if ( empty( $address ) ) return $this->error( __( '参数错误' ) );
        if(!preg_match('/^[A-Za-z0-9]+$/i', $address)) return $this->error( __( '地址格式不正确' ) );

        $cacheName = "elf:assets:{$address}:{$currency}:{$p}";
        $cache = $this->redis()->get($cacheName);
        if($cache && app::REDISENV) {
            return $this->success('', $cache);
        }

        $cross_info = $this->getConfig('chains');
        $cross_info_json = json_decode($cross_info, true);
        $cross =  array_column($cross_info_json, 'symbol', null);

        //获取链所有的币种
        $all_tokens = $this->getAllContract();
        //获取所有链信息
        $list = $this->getAllBalance($address, $currency);

        foreach($all_tokens as $k=>$item){
            foreach($item as $k2=>$item2){
                //
                //var_dump($item2);
                $all_tokens[$k][$k2]['chain_id'] = $this->formatChainName($item2['chain_id']);
                $all_tokens[$k][$k2]['in'] = 0;
                $all_tokens[$k][$k2]['balance'] = "0.00";
                foreach($list as $item3){
                    $item3 = (array)$item3;
                    if($item3['contract_address']==$item2['contract_address'] && $item3['symbol']==$item2['symbol']&&$item3['chain_id']==$item2['chain_id']){
                        $all_tokens[$k][$k2]['in'] = 1; //该用户在该合约存在资产
                        $all_tokens[$k][$k2]['balance'] = $this->del0($item3['balance']);
                        break;
                    }
                }
                $all_tokens[$k][$k2]['balance_o'] =  $all_tokens[$k][$k2]['balance'];
                $all_tokens[$k][$k2]['balance'] = $this->formatBalance($all_tokens[$k][$k2]['balance'], $item2['decimals']);
                //增加logo
                $coin = $this->getCoin( $item2['symbol'] );
                $ossUrl = $this->getConfig( 'OSS_URL' )??$this->getConfig( 'oss_url' );
                $all_tokens[$k][$k2]['logo'] = $coin['logo'] ? $ossUrl.$coin['logo'] : $ossUrl.'elf_wallet/elf/default.png';
                $all_tokens[$k][$k2]['sort'] = $this->orderSymbol($item2['symbol']);

                if($all_tokens[$k][$k2]['block_hash'] == 'inner' && !in_array($all_tokens[$k][$k2]['symbol'], $cross)){
                    $all_tokens[$k][$k2]['block_hash'] = '';
                    $all_tokens[$k][$k2]['tx_id'] = '';
                }

            }
            $sort = array_column($all_tokens[$k], 'sort');
            $sort2 = array_column($all_tokens[$k], 'symbol');
            array_multisort($sort, SORT_DESC,$sort2, SORT_STRING, $all_tokens[$k]);

        }
        ($all_tokens && $list) && $this->redis()->set($cacheName, $all_tokens, 5*60);
        $this->success('', $all_tokens);
    }

}