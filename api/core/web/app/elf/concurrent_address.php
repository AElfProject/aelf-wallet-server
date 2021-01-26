<?php
/**
 * 获取address所有链的余额.
 * User: Jett
 * Date: 2019-09-25
 * Time: 20:24
 */
require_once __DIR__.'/base.php';

class app_elf_concurrent_address extends app_elf_base{

    function doRequest(){
        $address = trim( post( 'address' ) );
        $currency = trim( post( 'currency' ) );
        $type = post( 'type' )?intval(post( 'type' )):0;

        //添加缓存机制
        $currenyLower = strtolower($currency);
        $cacheKey = "elf:concurrent_address:{$address}:{$currenyLower}:{$type}";
        //echo $cacheKey;
        $json = $this->redis()->get($cacheKey);
        if($json && app::REDISENV){
            $this->success('', $json);
        }

        //$tokens = $this->getAddressTokensFromLocal($address);
        //var_dump($tokens);

        //获取所有链信息
        $json = $this->getAllBalance($address, $currency);
        $ossUrl = $this->getConfig( 'OSS_URL' )??$this->getConfig( 'oss_url' );

        //主链币
        $cross_info = $this->getConfig('chains');
        $cross_info_json = json_decode($cross_info, true);
        $mainCoin = array_column($cross_info_json, 'symbol','name');

        //var_dump($json);
        array_walk($json, function (&$item, $k, $item3){
            list($ossUrl, $currency, $chain_color, $mainCoin, $decimal) = $item3;
            //处理key值
            $item['color'] = isset($chain_color[$item['chainid']])?$chain_color[$item['chainid']]:'#641EB0';

            $type= ($mainCoin[$item['chainid']]==$item['symbol'])?"main":"contract";

            $item['chainid'] = $this->formatChainName($item['chainid']);
            $coin = $this->getCoin( $item['symbol'] );
            $item['logo'] =  $coin['logo'] ? $ossUrl.$coin['logo'] : $ossUrl.'elf_wallet/elf/default.png';

            $item['type'] = $type;
            $item['contractAddress'] = $item['contractAddress'];
            unset($item['contractaddress']);
            $item['name'] = $item['symbol'];
            $item['chain_id'] = $item['chainid'];
            unset($item['chainid']);

            $item['sort'] = $item['chain_id'];

            $item['balance_o'] = $item['balance'];
            $item['balance'] = $this->formatBalance($item['balance'], $decimal[$item['symbol']]);
            $item['decimals'] = $decimal[$item['symbol']];
            /*
            if($item['chain_id'] == $this->formatChainName('AELF')){
                $item['sort'] = 0;
            }
            */
            $item['sort'] = $this->orderSymbol($item['symbol']);

            //汇率
            $rate = $this->getExchangeRate( $item['symbol'], $currency );
            $item['rate'] = $rate;
            $item['issue_chain_id'] = $this->getIsuseChainId($item);
        },[$ossUrl,$currency,$this->chain_color, $mainCoin, $this->decimal]);

        //根据token获取整合数据
        if($type == 1)
        {
            $h_json = [];
            foreach($json as $item){
                if(isset($h_json[$item['symbol']])){
                    $h_json[$item['symbol']]['balance'] = bcadd($h_json[$item['symbol']]['balance'],$item['balance'],$this->decimal[$item['symbol']]);
                    $h_json[$item['symbol']]['balance_o'] += $item['balance_o'];
                }else{
                    $h_json[$item['symbol']] = $item;
                }
            }
            $json = array_values($h_json);
        }

        //排序处理
        $sort1 = array_column($json,'sort');
        $sort2 = array_column($json,'balance');
        array_multisort($sort1,SORT_DESC, $sort2, SORT_DESC, $json);

        $this->redis()->set($cacheKey, $json, 5);
        $this->returnSuccess('', $json);
    }


    private function getIsuseChainId($token){
        //获取链所有的币种
        $all_tokens = $this->getAllContract();

        $chainid = '';
        if(isset($all_tokens[$token['chain_id']])){
            foreach($all_tokens[$token['chain_id']] as $item){
                if($item['contract_address'] == $token['contractAddress'] && $item['symbol']==$token['symbol']){
                    $chainid = $item['issue_chain_id'];
                    break;
                }
            }
        }

        return $chainid;
    }


}