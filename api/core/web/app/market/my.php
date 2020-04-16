<?php
/**
 * 自选行情
 * User: David
 * Date: 2019/8/5
 * Time: 17:17
 */

require_once __DIR__ . '/base.php';

class app_market_my extends app_market_base
{
    public function doRequest(){
        $currency = $this->currency;
        $device = trim( post( 'device' ) );
        $udid = trim( post( 'udid' ) );
        $version = trim( post( 'version' ) );
        $sort = (int)post('sort');
        $customCoin = trim( post('customCoin') );

        if(version_compare($version, '3.9.1', '<')){
            $version = "3.9.1";
        }

        $url = $this->getConfig('market_custom_list') . "?lang=" . $this->getLang();
        $params = $this->encodeParams([
            'device' => $device,
            'udid' => $udid,
            'version' => $version,
            'currency' => $currency,
            'customCoin' => $customCoin,
            'sort' => $sort,
        ]);

        $res = $this->request($url, $params);

        $res = json_decode($res, true);
        if (isset($res['data']['list'])) {
            $this->success('', ['list' => $res['data']['list']]);
        } else {
            $this->success('', ['list' => []]);
        }

    }
    
}