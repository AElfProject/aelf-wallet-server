<?php
/**
 * 根据address获取当前链的tokens.
 * User: Jett
 * Date: 2019-12-10
 * Time: 14:06
 */

require_once __DIR__.'/base.php';

class app_elf_tokens extends app_elf_base{

    public function doRequest() {
        $address = trim( post( 'address' ) );
        $limit = post('address' )?intval(post('address' )):100;
        $page = post('page')?intval(post['page']):0;
        $currency = trim( post( 'currency' ) );

        $type = post('type')?intval(post('type')):0;

        if($type == 0) {
            $tokens = $this->getAddressTokens($address, $limit, $page);
            //echo $tokens;

            $this->success('', $tokens);
        }else{
            $cross_info = $this->getConfig('chains');
            $my_tokens = [];
            foreach(json_decode($cross_info, true) as $item){
                $my_tokens[] = [
                    'chain_id' => $item["name"],
                    'address'=> $address
                ];
            }

            //过滤重复的token数据
            $tokens = array_unique($my_tokens, SORT_REGULAR);
            $tokens = array_values($tokens);

            //$params = ['param' => $tokens];
            //var_dump($params);
            $this->tokens_url = "http://127.0.0.1:8000/elf_tokens";
            $res = $this->request($this->tokens_url, json_encode($tokens));
            $res = json_decode($res, true);
            var_dump($res);
        }

    }

}