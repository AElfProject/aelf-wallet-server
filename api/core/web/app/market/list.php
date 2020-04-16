<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2019/5/30
 * Time: 17:17
 */

require_once __DIR__ . '/base.php';

class app_market_list extends app_market_base
{

    public function doRequest(){
        $currency = $this->currency;
        $device = trim( post( 'device' ) );
        $udid = trim( post( 'udid' ) );
        $version = trim( post( 'version' ) );
        $p = (int)post( 'p' );
        $sort = (int)post('sort');
        $time = trim( post( 'time' ) );
        $coinName = trim(post('coinName'));    //关键词搜索

        if(version_compare($version, '3.9.1', '<')){
            $version = "3.9.1";
        }

        $keycache = "elf:market_list:{$coinName}:{$currency}:{$sort}:{$time}:{$p}";
        $cache = $this->redis()->get($keycache);
        if($cache && app::REDISENV) {
            $this->success('',  ['list'=>$cache]);
        }

        if (isset($sort)) {
            $url = $this->getConfig('market_price_list') . "?lang=" . $this->getLang();
            $params = $this->encodeParams([
                'device' => $device,
                'udid' => $udid,
                'version' => $version,
                'currency' => $currency,
                'p' => $p,
                'sort' => $sort,
                'topCoin' => 'elf',    //置顶币种
                'coinName' => $coinName,
            ]);
            $res = $this->request($url, $params);
            $res = json_decode($res, true);
            if (isset($res['data']['list'])) {
                $this->redis()->set($keycache, $res['data']['list'], 300);    //5mins

                $this->success('', ['list' => $res['data']['list']]);
            }
        }
        $this->returnError(__('失败'));
    }
    
}