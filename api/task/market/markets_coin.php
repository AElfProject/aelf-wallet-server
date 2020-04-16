<?php

/**
 * 行情
 *
 * @param string currency 币种
 */
date_default_timezone_set( 'PRC' );
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));
header( 'Content-Type:text/html;charset=utf-8;' );

require_once __DIR__ . '/../data.config.php';
require_once __DIR__ . '/../base.php';
require_once __DIR__ . '/../redis.php';

class markets_coin extends base {

    function action() {

        echo date( 'Y-m-d H:i:s' ).' begin '.PHP_EOL;

        $coins = $this->getConfig('markets');
        $allCoinList = explode(',', $coins);

        foreach (array_chunk($allCoinList, 100) as $coinList){
            $coins = implode(",", $coinList);
            $allCoinInfo = $this->https_request($this->marketUrl.'all_coin_info', ['coins' =>  $coins]);

            $formatData = $this->redis()->get( 'market_all');

            if (!empty($allCoinInfo))
            {
                foreach ($allCoinInfo as $coinInfo)
                {
                    $coin = strtolower( $coinInfo['coin'] );

                    $price_usd = $coinInfo['price'];

                    $formatData[$coin] = [
                        'name' => strtoupper( $coinInfo['coin'] ),
                        'name_en' => ucfirst(strtolower( $coinInfo['en'] )),
                        'name_cn' => strtolower( $coinInfo['cn'] ),
                        'logo' =>  $coinInfo['logo'],
                        'price_usd' => $price_usd,
                        'increase' => number_format( $coinInfo['percent'], 5, '.', '' ),
                        'min_price' => $coinInfo['min24'],
                        'max_price' => $coinInfo['max24'],
                        'arrow' => $coinInfo['percent'] > 0 ? 1 : 0,
                        'vol' => $coinInfo['volume24'],
                        'circulate' => $coinInfo['circulate'],
                        'supply' => $coinInfo['supply'],
                        'date' => $coinInfo['t'],
                    ];
                }
            }
            else
            {
                //$this->sendMarketMessage('all_coin_info 接口返回数据为空,币种'.$coins);
            }

            if (!empty($formatData)) {
                /* 去除配置中已去除的币种 */
                foreach ($formatData as $coin => $data){
                    if (!in_array($coin, $allCoinList)){
                        unset($formatData[$coin]);
                    }
                }
                $this->redis()->set( 'market_all', $formatData);
            }
        }

        echo date( 'Y-m-d H:i:s' ).' end '.PHP_EOL;
    }

    //简化版的curl
    protected function https_request( $url, $data = null, $timeout = 60 ) {
        //$data是字符串，则application/x-www-form-urlencoded
        //$data是数组，则multipart/form-data

        if (!empty($data)){
            //   $url .= '?'.http_build_query($data);
        }

        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );

        if ( !empty( $data ) ) {
            curl_setopt( $curl, CURLOPT_POST, 1 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
        }
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        $output = curl_exec( $curl );
        curl_close( $curl );

        $response = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE){
            return [];
        }

        if ($response['status'] == 200)
        {
            return $response['data'];
        }
    }

}

set_time_limit( 0 );

while (true){
    $markets = new markets_coin;
    $markets->action();
}
