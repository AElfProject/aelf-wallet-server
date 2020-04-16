<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2019/5/30
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_market_trade_kline extends app_market_base {

    public function doRequest(){
        $currency = $this->currency;
        $device = trim( post( 'device' ) );
        $udid = trim( post( 'udid' ) );
        $version = trim( post( 'version' ) );
        $name = trim( post( 'name' ) );
        $type = trim( post( 'type' ) );
        $time = trim( post( 'time' ) );

        if(version_compare($version, '3.9.1', '<')){
            $version = "3.9.1";
        }

        $keycache = "elf:trade_kline:{$name}:{$currency}:{$type}:{$time}";
        $cache = $this->redis()->get($keycache);
        if($cache && app::REDISENV) {
            $this->success('', ['list' =>$cache]);
        }

        $url = $this->getConfig('market_trade_kline');
        $params = $this->encodeParams([
            'currency' => $currency,
            'device' => $device,
            'udid' => $udid,
            'version' => $version,
            'name' => $name,
            'type' => $type,
            'time' => $time
        ]);

        $res = $this->request($url, $params);

        $res = json_decode($res, true);

        if (isset($res['data']['list'])) {

            $expire = 600;
            if($time > 2){    //一周
                $expire = 3600*24;
            }

            $this->redis()->set($keycache, $res['data']['list'], $expire);    //1小时

            $this->returnSuccess('', ['list' => $res['data']['list']]);
        } else {
            $this->returnError(__( '失败' ));
        }

    }

}