<?php
/**
 * 涨跌榜.
 * User: David
 * Date: 2019/5/30
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_market_increase_list extends app_market_base {

    public function doRequest(){
        $currency = $this->currency;
        $device = trim( post( 'device' ) );
        $udid = trim( post( 'udid' ) );
        $version = trim( post( 'version' ) );
        $p = (int)post( 'p' );
        $change = trim( post( 'change' ) );
        $time = trim( post( 'time' ) );

        if(version_compare($version, '3.9.1', '<')){
            $version = "3.9.1";
        }

        $keycache = "elf:market_increase_list:{$currency}:{$time}:{$change}";
        $cache = $this->redis()->get($keycache);
        if($cache && app::REDISENV) {
            $this->success('', ['list'=>$cache]);
        }

        $url = $this->getConfig('increase_list')."?lang=".$this->getLang();

        $params = $this->encodeParams([
            'device' => $device,
            'udid' => $udid,
            'version' => $version,
            'currency' => $currency,
            //'p' => $p,
            'change' => $change,
            'time' => $time
        ]);

        $res = $this->request($url, $params);

        $res = json_decode($res, true);

        if (isset($res['data']['list'])) {
            $psize = 10;
             $p || $p = 1;
            if(count($res['data']['list']) > $psize*($p-1)){
                $res['data']['list'] = array_slice($res['data']['list'], $psize*($p-1), $psize);
            }
            $this->redis()->set($keycache, $res['data']['list'], 300);    //5mins
            $this->returnSuccess('', ['list' => $res['data']['list']]);
        } else {
            $this->returnError(__( '失败' ));
        }

    }

}