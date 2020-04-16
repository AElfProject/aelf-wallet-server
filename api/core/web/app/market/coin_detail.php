<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2019/5/30
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_market_coin_detail extends app_market_base {

    public function doRequest(){
        $currency = $this->currency;
        $device = trim( post( 'device' ) );
        $udid = trim( post( 'udid' ) );
        $version = trim( post( 'version' ) );
        $name = trim( post( 'name' ) );

        $keycache = "elf:coin_detail:{$name}:{$currency}";
        $cache = $this->redis()->get($keycache);
        if($cache && app::REDISENV) {
            $this->success('', $cache);
        }

        if(version_compare($version, '3.9.1', '<')){
            $version = "3.9.1";
        }

        $url = $this->getConfig('market_coin_detail');
        $params = $this->encodeParams([
            'currency' => $currency,
            'device' => $device,
            'udid' => $udid,
            'version' => $version,
            'name' => $name,
        ]);

        $res = $this->request($url, $params);
        $res = json_decode($res, true);

        if (isset($res['data'])) {

            //增加汇率
            $markets = $this->redis()->get( 'market_elf' );
            $exchange_rate = $markets['exchange_rate'];
            $res['data'] = array_merge($res['data'], $exchange_rate);

            $this->redis()->set($keycache, $res['data'], 3600);    //1小时
            $this->returnSuccess('', $res['data']);
        } else {
            $this->returnError(__( '失败' ));
        }

    }

}